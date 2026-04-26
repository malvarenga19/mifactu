<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceType;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'invoiceType'])
            ->orderByDesc('issue_date')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('correlative', 'like', "%$s%")
                  ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%$s%")
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

        $invoices    = $query->paginate(15)->withQueryString();
        $invoiceTypes = InvoiceType::all();

        return view('invoices.index', compact('invoices', 'invoiceTypes'));
    }

    public function create()
    {
        $customers    = Customer::orderBy('name')->get();
        $invoiceTypes = InvoiceType::all();
        $products     = Product::orderBy('name')->get();

        return view('invoices.create', compact('customers', 'invoiceTypes', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'invoice_type_id'  => 'required|exists:invoice_types,id',
            'issue_date'       => 'required|date',
            'payment_method'   => 'required|in:cash,credit_card,bank_transfer,credit',
            'payment_status'   => 'required|in:pending,paid,overdue',
            'due_date'         => 'nullable|date|after_or_equal:issue_date',
            'note'             => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.exento'      => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {
            $invoiceType = InvoiceType::lockForUpdate()->findOrFail($request->invoice_type_id);
            $correlative = $invoiceType->code . '-' . str_pad($invoiceType->last_correlative + 1, 8, '0', STR_PAD_LEFT);
            $invoiceType->increment('last_correlative');

            // Consumidor Final (código 01): precio ya incluye IVA → gravado = total (no se desglosa base)
            // CCF y otros: precio sin IVA → gravado = total/1.13 (se desglosa base neta)
            $esCF = $invoiceType->code === '01';

            $montoExento  = 0;
            $montoGravado = 0;
            $subtotal     = 0;

            $itemsData = [];
            foreach ($request->items as $item) {
                $exento = (bool) ($item['exento'] ?? false);
                $total  = round($item['quantity'] * $item['unit_price'], 2);
                $subtotal += $total;
                if ($exento) {
                    $montoExento += $total;
                } else {
                    // CF: gravado = precio completo | CCF: gravado = precio/1.13
                    // Acumular con precisión completa, redondear al final
                    $montoGravado += $esCF ? $total : ($total / 1.13);
                }
                $itemsData[] = array_merge($item, ['total' => $total, 'exento' => $exento]);
            }

            $montoGravado = round($montoGravado, 6); // precisión intermedia
            // CF: IVA = (gravado/1.13)*0.13 = gravado*(0.13/1.13)
            // CCF: gravado ya es base neta, IVA = gravado*0.13
            $montoIva     = $esCF
                ? round($montoGravado * (0.13 / 1.13), 6)
                : round($montoGravado * 0.13, 6);
            $subtotal     = round($subtotal, 2);

            // Redondear a 2 decimales solo al guardar
            $montoGravado = round($montoGravado, 2);
            $montoIva     = round($montoIva, 2);

            $customer    = Customer::findOrFail($request->customer_id);
            $ivaRetenido = $customer->retains_iva ? round($montoIva * 0.01, 2) : 0;

            $total = round($subtotal - $ivaRetenido, 2);

            $invoice = Invoice::create([
                'customer_id'      => $request->customer_id,
                'invoice_type_id'  => $request->invoice_type_id,
                'generation_code'  => (string) Str::uuid(),
                'correlative'      => $correlative,
                'issue_date'       => $request->issue_date,
                'payment_method'   => $request->payment_method,
                'payment_status'   => $request->payment_status,
                'due_date'         => $request->due_date,
                'monto_exento'     => $montoExento,
                'monto_gravado'    => $montoGravado,
                'monto_iva'        => $montoIva,
                'iva_retenido'     => $ivaRetenido,
                'isr_retenido'     => 0,
                'subtotal'         => $subtotal,
                'total_amount'     => $total,
                'status'           => 'draft',
                'status_mh'        => 'draft',
                'note'             => $request->note,
            ]);

            foreach ($itemsData as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total'       => $item['total'],
                    'exento'      => $item['exento'],
                ]);
            }
        });

        return redirect()->route('invoices.index')->with('success', 'Factura creada correctamente.');
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

        $customers    = Customer::orderBy('name')->get();
        $invoiceTypes = InvoiceType::all();
        $products     = Product::orderBy('name')->get();
        $invoice->load('items');

        return view('invoices.edit', compact('invoice', 'customers', 'invoiceTypes', 'products'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Solo se pueden editar facturas en borrador.');
        }

        $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'invoice_type_id'  => 'required|exists:invoice_types,id',
            'issue_date'       => 'required|date',
            'payment_method'   => 'required|in:cash,credit_card,bank_transfer,credit',
            'payment_status'   => 'required|in:pending,paid,overdue',
            'due_date'         => 'nullable|date|after_or_equal:issue_date',
            'note'             => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.exento'      => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $invoiceType  = InvoiceType::findOrFail($request->invoice_type_id);
            $esCF         = $invoiceType->code === '01';

            $montoExento  = 0;
            $montoGravado = 0;
            $subtotal     = 0;

            $itemsData = [];
            foreach ($request->items as $item) {
                $exento = (bool) ($item['exento'] ?? false);
                $total  = round($item['quantity'] * $item['unit_price'], 2);
                $subtotal += $total;
                if ($exento) {
                    $montoExento += $total;
                } else {
                    $montoGravado += $esCF ? $total : ($total / 1.13);
                }
                $itemsData[] = array_merge($item, ['total' => $total, 'exento' => $exento]);
            }

            $montoGravado = round($montoGravado, 6);
            $montoIva     = $esCF
                ? round($montoGravado * (0.13 / 1.13), 6)
                : round($montoGravado * 0.13, 6);
            $subtotal     = round($subtotal, 2);

            $montoGravado = round($montoGravado, 2);
            $montoIva     = round($montoIva, 2);

            $customer     = Customer::findOrFail($request->customer_id);
            $ivaRetenido  = $customer->retains_iva ? round($montoIva * 0.01, 2) : 0;
            $total        = round($subtotal - $ivaRetenido, 2);

            $invoice->update([
                'customer_id'      => $request->customer_id,
                'invoice_type_id'  => $request->invoice_type_id,
                'issue_date'       => $request->issue_date,
                'payment_method'   => $request->payment_method,
                'payment_status'   => $request->payment_status,
                'due_date'         => $request->due_date,
                'monto_exento'     => $montoExento,
                'monto_gravado'    => $montoGravado,
                'monto_iva'        => $montoIva,
                'iva_retenido'     => $ivaRetenido,
                'subtotal'         => $subtotal,
                'total_amount'     => $total,
                'note'             => $request->note,
            ]);

            $invoice->items()->delete();

            foreach ($itemsData as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total'       => $item['total'],
                    'exento'      => $item['exento'],
                ]);
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

        $invoice->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancellation_date'   => now()->toDateString(),
        ]);

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