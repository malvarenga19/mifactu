<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceType;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'invoiceType'])
            ->orderByDesc('issue_date')
            ->orderByDesc('id');

        // ✅ FILTRO DE DÍA ACTUAL - solo cuando NO hay filtros de fecha Y NO es AJAX (carga inicial)
        if (! $request->filled('date_from') && ! $request->filled('date_to')) {
            $query->whereDate('issue_date', today());
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('correlative', 'like', "%$s%")
                    ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%$s%")
                        ->orWhere('company_name', 'like', "%$s%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('invoice_type_id')) {
            $query->where('invoice_type_id', $request->invoice_type_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        $invoices = $query->paginate(10);
        $invoiceTypes = InvoiceType::all();

        if ($request->ajax()) {
            return response()->json($invoices);
        }

        return view('invoices.index', compact('invoices', 'invoiceTypes'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $invoiceTypes = InvoiceType::all();
        $products = Product::orderBy('name')->get();

        return view('invoices.create', compact('customers', 'invoiceTypes', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_type_id' => 'required|exists:invoice_types,id',
            'issue_date' => 'required|date',
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,credit',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'note' => 'nullable|string',
            'credit_days' => 'nullable|integer|min:1|max:365|required_if:payment_method,credit',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.exento' => 'nullable|boolean',
            'items.*.non_inventory' => 'nullable|boolean',
        ]);

        // Calcular payment_status automáticamente
        $paymentStatus = $request->payment_method === 'credit' ? 'pending' : 'paid';

        // Calcular due_date si es crédito
        if ($request->payment_method === 'credit' && $request->credit_days) {
            $dueDate = Carbon::parse($request->issue_date)->addDays((int) $request->credit_days);
            $request->merge(['due_date' => $dueDate->format('Y-m-d')]);
        }

        // Validar stock SOLO para productos inventariados (non_inventory = false)
        $stockErrors = [];
        foreach ($request->items as $key => $item) {
            $isNonInventory = (bool) ($item['non_inventory'] ?? false);

            if (! $isNonInventory && ! empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if ($product && $product->stock < (int) $item['quantity']) {
                    $stockErrors["items.stock.{$key}"] =
                        "'{$product->name}': stock disponible {$product->stock}, requerido {$item['quantity']}";
                }
            }
        }

        if (! empty($stockErrors)) {
            return back()->withInput()->withErrors($stockErrors);
        }

        DB::transaction(function () use ($request, $paymentStatus) {
            $invoiceType = InvoiceType::lockForUpdate()->findOrFail($request->invoice_type_id);
            $correlative = $invoiceType->code.'-'.str_pad($invoiceType->last_correlative + 1, 8, '0', STR_PAD_LEFT);
            $invoiceType->increment('last_correlative');

            $esCF = $invoiceType->code === '01';

            // Variables para cálculos
            $subtotal = 0; // Suma de todos los precios ingresados
            $montoExento = 0;
            $baseImponible = 0; // Base real para cálculos (siempre precio/1.13 para no exentos)

            $itemsData = [];
            foreach ($request->items as $item) {
                $exento = (bool) ($item['exento'] ?? false);
                $total = round($item['quantity'] * $item['unit_price'], 2);
                $subtotal += $total;

                if ($exento) {
                    $montoExento += $total;
                } else {
                    // BASE IMPONIBLE REAL: precio / 1.13 (funciona para CF y CCF)
                    $baseImponible += $total / 1.13;
                }

                $itemsData[] = array_merge($item, [
                    'total' => $total,
                    'exento' => $exento,
                    'non_inventory' => (bool) ($item['non_inventory'] ?? false),
                ]);
            }

            // Redondear base imponible
            $baseImponible = round($baseImponible, 2);

            // Calcular IVA (13% de la base imponible)
            $montoIva = round($baseImponible * 0.13, 2);
            $ivaRetenido = 0;

            // Calcular retención (1% sobre la base imponible)
            $customer = Customer::findOrFail($request->customer_id);
            if ($customer->retains_iva && $baseImponible >= 100) {
                $ivaRetenido = round($baseImponible * 0.01, 2);
            }
            // $ivaRetenido = $customer->retains_iva ? round($baseImponible * 0.01, 2) : 0;

            // Calcular monto_gravado (el que se muestra en la factura) según el tipo
            if ($esCF) {
                // CF: El gravado mostrado es el subtotal (con IVA incluido)
                $montoGravado = $subtotal;
                // Total a pagar = Subtotal - Retención
                $totalAmount = round($subtotal - $ivaRetenido, 2);
            } else {
                // CCF: El gravado mostrado es la base imponible
                $montoGravado = $baseImponible;
                // Total a pagar = (Base + IVA) - Retención
                $totalAmount = round(($baseImponible + $montoIva) - $ivaRetenido, 2);
            }

            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'invoice_type_id' => $request->invoice_type_id,
                'generation_code' => (string) Str::uuid(),
                'correlative' => $correlative,
                'issue_date' => $request->issue_date,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'due_date' => $request->due_date,
                'credit_days' => $request->payment_method === 'credit' ? (int) $request->credit_days : null,
                'monto_exento' => $montoExento,
                'monto_gravado' => $montoGravado,
                'monto_iva' => $montoIva,
                'iva_retenido' => $ivaRetenido,
                'isr_retenido' => 0,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'status_mh' => 'draft',
                'note' => $request->note,
            ]);

            // Guardar ítems y descontar stock (solo para inventariados)
            foreach ($itemsData as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['non_inventory'] ? null : $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                    'exento' => $item['exento'],
                    'non_inventory' => $item['non_inventory'],
                ]);

                if (! $item['non_inventory'] && ! empty($item['product_id'])) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                    $stockBefore = $product->stock;
                    $product->stock -= (int) $item['quantity'];
                    $product->save();

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'salida',
                        'quantity' => (int) $item['quantity'],
                        'note' => 'Factura: '.$invoice->correlative,
                        'stock_before' => $stockBefore,
                        'stock_after' => $product->stock,
                    ]);
                }
            }
        });

        return redirect()->route('invoices.index')->with('success', 'Factura creada y stock descontado.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer.municipality.department', 'customer.country', 'invoiceType', 'items.product']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Solo se pueden editar facturas en borrador.');
        }

        $customers = Customer::orderBy('name')->get();
        $invoiceTypes = InvoiceType::all();
        $products = Product::orderBy('name')->get();
        $invoice->load('items');

        return view('invoices.edit', compact('invoice', 'customers', 'invoiceTypes', 'products'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Solo se pueden editar facturas en borrador.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_type_id' => 'required|exists:invoice_types,id',
            'issue_date' => 'required|date',
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,credit',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'note' => 'nullable|string',
            'credit_days' => 'nullable|integer|min:1|max:365|required_if:payment_method,credit',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.exento' => 'nullable|boolean',
            'items.*.non_inventory' => 'nullable|boolean',
        ]);

        // Calcular payment_status automáticamente
        $paymentStatus = $request->payment_method === 'credit' ? 'pending' : 'paid';

        // Calcular due_date si es crédito
        if ($request->payment_method === 'credit' && $request->credit_days) {
            $dueDate = Carbon::parse($request->issue_date)->addDays((int) $request->credit_days);
            $request->merge(['due_date' => $dueDate->format('Y-m-d')]);
        }

        // Validar stock ANTES de la transacción.
        $invoice->load('items');
        $stockDisponible = [];
        foreach ($invoice->items as $oldItem) {
            if (! $oldItem->non_inventory && $oldItem->product_id) {
                $stockDisponible[$oldItem->product_id] =
                    ($stockDisponible[$oldItem->product_id] ?? Product::find($oldItem->product_id)->stock)
                    + (int) $oldItem->quantity;
            }
        }

        $stockErrors = [];
        foreach ($request->items as $key => $item) {
            $isNonInventory = (bool) ($item['non_inventory'] ?? false);

            if (! $isNonInventory && ! empty($item['product_id'])) {
                $pid = $item['product_id'];
                $qty = (int) $item['quantity'];
                $disp = $stockDisponible[$pid] ?? Product::find($pid)->stock ?? 0;
                if ($disp < $qty) {
                    $product = Product::find($pid);
                    $stockErrors["items.stock.{$key}"] =
                        "'{$product->name}': stock disponible {$disp}, requerido {$qty}";
                }
            }
        }

        if (! empty($stockErrors)) {
            return back()->withInput()->withErrors($stockErrors);
        }

        DB::transaction(function () use ($request, $invoice, $paymentStatus) {
            $invoiceType = InvoiceType::findOrFail($request->invoice_type_id);
            $esCF = $invoiceType->code === '01';

            // Variables para cálculos
            $subtotal = 0;
            $montoExento = 0;
            $baseImponible = 0;

            $itemsData = [];
            foreach ($request->items as $item) {
                $exento = (bool) ($item['exento'] ?? false);
                $total = round($item['quantity'] * $item['unit_price'], 2);
                $subtotal += $total;

                if ($exento) {
                    $montoExento += $total;
                } else {
                    $baseImponible += $total / 1.13;
                }

                $itemsData[] = array_merge($item, [
                    'total' => $total,
                    'exento' => $exento,
                    'non_inventory' => (bool) ($item['non_inventory'] ?? false),
                ]);
            }

            $baseImponible = round($baseImponible, 2);
            $montoIva = round($baseImponible * 0.13, 2);

            $customer = Customer::findOrFail($request->customer_id);
            $ivaRetenido = $customer->retains_iva ? round($baseImponible * 0.01, 2) : 0;

            if ($esCF) {
                $montoGravado = $subtotal;
                $totalAmount = round($subtotal - $ivaRetenido, 2);
            } else {
                $montoGravado = $baseImponible;
                $totalAmount = round(($baseImponible + $montoIva) - $ivaRetenido, 2);
            }

            $invoice->update([
                'customer_id' => $request->customer_id,
                'invoice_type_id' => $request->invoice_type_id,
                'issue_date' => $request->issue_date,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'due_date' => $request->due_date,
                'credit_days' => $request->payment_method === 'credit' ? $request->credit_days : null,
                'monto_exento' => $montoExento,
                'monto_gravado' => $montoGravado,
                'monto_iva' => $montoIva,
                'iva_retenido' => $ivaRetenido,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'note' => $request->note,
            ]);

            // Revertir stock de ítems anteriores
            foreach ($invoice->items as $oldItem) {
                if (! $oldItem->non_inventory && $oldItem->product_id) {
                    $product = Product::lockForUpdate()->findOrFail($oldItem->product_id);
                    $stockBefore = $product->stock;
                    $product->stock += (int) $oldItem->quantity;
                    $product->save();

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'entrada',
                        'quantity' => (int) $oldItem->quantity,
                        'note' => 'Edición de factura (reverso): '.$invoice->correlative,
                        'stock_before' => $stockBefore,
                        'stock_after' => $product->stock,
                    ]);
                }
            }

            $invoice->items()->delete();

            // Guardar nuevos ítems
            foreach ($itemsData as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['non_inventory'] ? null : $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                    'exento' => $item['exento'],
                    'non_inventory' => $item['non_inventory'],
                ]);

                if (! $item['non_inventory'] && ! empty($item['product_id'])) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                    $stockBefore = $product->stock;
                    $product->stock -= (int) $item['quantity'];
                    $product->save();

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'salida',
                        'quantity' => (int) $item['quantity'],
                        'note' => 'Edición de factura: '.$invoice->correlative,
                        'stock_before' => $stockBefore,
                        'stock_after' => $product->stock,
                    ]);
                }
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Factura actualizada.');
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
        ]);

        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'La factura ya está anulada.');
        }

        DB::transaction(function () use ($request, $invoice) {
            $invoice->load('items');
            foreach ($invoice->items as $item) {
                if (! $item->non_inventory && $item->product_id) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    $stockBefore = $product->stock;
                    $qty = (int) $item->quantity;

                    $product->stock += $qty;
                    $product->save();

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'entrada',
                        'quantity' => $qty,
                        'note' => 'Anulación de factura: '.$invoice->correlative,
                        'stock_before' => $stockBefore,
                        'stock_after' => $product->stock,
                    ]);
                }
            }

            $invoice->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancellation_date' => now()->toDateString(),
            ]);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Factura anulada.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Solo se pueden eliminar facturas en borrador.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Factura eliminada.');
    }
}
