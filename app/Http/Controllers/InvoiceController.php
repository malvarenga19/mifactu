<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceType;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Listado de facturas con filtros y paginación.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'invoiceType'])
            ->orderBy('id', 'desc');

        // Filtro búsqueda por correlativo, código de generación o cliente
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('correlative', 'like', "%{$search}%")
                  ->orWhere('generation_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por tipo de documento
        if ($request->filled('invoice_type_id')) {
            $query->where('invoice_type_id', $request->invoice_type_id);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por estado MH
        if ($request->filled('status_mh')) {
            $query->where('status_mh', $request->status_mh);
        }

        // Filtro por rango de fechas
        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        $perPage   = $request->input('per_page', 15);
        $invoices  = $query->paginate($perPage)->withQueryString();
        $invoiceTypes = InvoiceType::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'invoiceTypes'));
    }

    /**
     * Formulario para crear una nueva factura.
     */
    public function create()
    {
        $customers    = Customer::orderBy('name')->get();
        $invoiceTypes = InvoiceType::orderBy('name')->get();
        $products     = Product::orderBy('name')->get();

        return view('invoices.create', compact('customers', 'invoiceTypes', 'products'));
    }

    /**
     * Guardar nueva factura con sus ítems.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'invoice_type_id'  => 'required|exists:invoice_types,id',
            'issue_date'       => 'required|date',
            'payment_method'   => 'required|in:cash,credit_card,bank_transfer,credit',
            'payment_status'   => 'required|in:pending,paid,overdue',
            'due_date'         => 'nullable|date|after_or_equal:issue_date',
            'note'             => 'nullable|string|max:500',

            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.description'  => 'required|string|max:255',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.exento'       => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            /** @var InvoiceType $invoiceType */
            $invoiceType = InvoiceType::lockForUpdate()->findOrFail($request->invoice_type_id);

            // ── Validar stock suficiente para todos los ítems ──────────────
            // Se agrupan por producto para manejar el caso de un mismo producto
            // en varias líneas (ej: 2 filas del mismo producto).
            $stockNeeded = [];
            foreach ($request->items as $item) {
                $pid = $item['product_id'];
                $stockNeeded[$pid] = ($stockNeeded[$pid] ?? 0) + $item['quantity'];
            }

            $stockErrors = [];
            $productsMap = Product::lockForUpdate()
                ->whereIn('id', array_keys($stockNeeded))
                ->get()
                ->keyBy('id');

            foreach ($stockNeeded as $pid => $needed) {
                $product = $productsMap[$pid] ?? null;
                if (!$product) continue;
                if ($product->stock < $needed) {
                    $stockErrors[] = "«{$product->name}»: stock disponible {$product->stock}, requerido {$needed}.";
                }
            }

            if (!empty($stockErrors)) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'Stock insuficiente para los siguientes productos:' . implode('<br>• ', $stockErrors));
            }

            // ── Incrementar correlativo ────────────────────────────────────
            $nextCorrelative = $invoiceType->last_correlative + 1;
            $invoiceType->update(['last_correlative' => $nextCorrelative]);

            $correlative    = str_pad($nextCorrelative, 8, '0', STR_PAD_LEFT);
            $generationCode = strtoupper(Str::uuid());

            // ── Calcular montos ────────────────────────────────────────────
            $montoExento  = 0;
            $montoGravado = 0;
            $ivaRate      = 0.13;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                if (!empty($item['exento'])) {
                    $montoExento += $lineTotal;
                } else {
                    $montoGravado += $lineTotal;
                }
            }

            $montoIva = round($montoGravado * $ivaRate / (1 + $ivaRate), 2);
            $subtotal  = $montoExento + $montoGravado;
            $total     = $subtotal;

            // Retenciones si el cliente retiene IVA
            $customer    = Customer::findOrFail($request->customer_id);
            $ivaRetenido = 0;
            $isrRetenido = 0;
            if ($customer->retains_iva) {
                $ivaRetenido = round($montoGravado * $ivaRate / (1 + $ivaRate), 2);
                $total -= $ivaRetenido;
            }

            // ── Crear cabecera de factura ──────────────────────────────────
            $invoice = Invoice::create([
                'customer_id'      => $request->customer_id,
                'invoice_type_id'  => $invoiceType->id,
                'generation_code'  => $generationCode,
                'correlative'      => $correlative,
                'issue_date'       => $request->issue_date,
                'payment_method'   => $request->payment_method,
                'payment_status'   => $request->payment_status,
                'due_date'         => $request->due_date,
                'monto_exento'     => $montoExento,
                'monto_gravado'    => $montoGravado,
                'monto_iva'        => $montoIva,
                'iva_retenido'     => $ivaRetenido,
                'isr_retenido'     => $isrRetenido,
                'subtotal'         => $subtotal,
                'total_amount'     => $total,
                'status'           => 'draft',
                'status_mh'        => 'draft',
                'note'             => $request->note,
            ]);

            // ── Guardar ítems, descontar stock y registrar movimientos ─────
            foreach ($request->items as $item) {
                $lineTotal = round($item['quantity'] * $item['unit_price'], 2);

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total'       => $lineTotal,
                    'exento'      => !empty($item['exento']),
                ]);

                // Descontar stock y registrar movimiento
                $product     = $productsMap[$item['product_id']];
                $stockBefore = $product->stock;
                $product->stock -= $item['quantity'];
                $product->save();

                // Actualizar el objeto en el map para que la siguiente línea
                // del mismo producto parta del stock ya reducido
                $productsMap[$item['product_id']] = $product;

                InventoryMovement::create([
                    'product_id'     => $product->id,
                    'type'           => 'salida',
                    'quantity'       => $item['quantity'],
                    'note'           => "Factura #{$correlative}",
                    'stock_before'   => $stockBefore,
                    'stock_after'    => $product->stock,
                    'reference_type' => Invoice::class,
                    'reference_id'   => $invoice->id,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('invoices.index')
                ->with('success', "Factura #{$correlative} creada exitosamente.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al guardar la factura: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de una factura.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'invoiceType', 'items.product']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Eliminar (solo borradores).
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Solo se pueden eliminar facturas en borrador.');
        }

        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Borrador eliminado.');
    }
}