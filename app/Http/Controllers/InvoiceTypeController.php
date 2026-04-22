<?php

namespace App\Http\Controllers;

use App\Models\InvoiceType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoiceTypes = InvoiceType::orderBy('code')->paginate(15);
        return view('invoice-types.index', compact('invoiceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('invoice-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:invoice_types,code',
            'name' => 'required|string|max:100|unique:invoice_types,name',
            'last_correlative' => 'required|integer|min:0'
        ]);

        try {
            InvoiceType::create([
                'code' => strtoupper($request->code),
                'name' => ucfirst($request->name),
                'last_correlative' => $request->last_correlative
            ]);

            return redirect()->route('invoice-types.index')
                ->with('success', 'Tipo de factura creado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear el tipo de factura: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceType $invoiceType)
    {
        // Cargar las facturas relacionadas
        $invoiceType->load('invoices');
        
        // Estadísticas
        $totalInvoices = $invoiceType->invoices()->count();
        $totalAmount = $invoiceType->invoices()->sum('total_amount');
        $issuedInvoices = $invoiceType->invoices()->where('status', 'issued')->count();
        $cancelledInvoices = $invoiceType->invoices()->where('status', 'cancelled')->count();
        
        return view('invoice-types.show', compact('invoiceType', 'totalInvoices', 'totalAmount', 'issuedInvoices', 'cancelledInvoices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceType $invoiceType)
    {
        return view('invoice-types.edit', compact('invoiceType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceType $invoiceType)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('invoice_types')->ignore($invoiceType->id)],
            'name' => ['required', 'string', 'max:100', Rule::unique('invoice_types')->ignore($invoiceType->id)],
            'last_correlative' => 'required|integer|min:0'
        ]);

        try {
            $invoiceType->update([
                'code' => strtoupper($request->code),
                'name' => ucfirst($request->name),
                'last_correlative' => $request->last_correlative
            ]);

            return redirect()->route('invoice-types.index')
                ->with('success', 'Tipo de factura actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el tipo de factura: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceType $invoiceType)
    {
        try {
            // Verificar si tiene facturas asociadas
            if ($invoiceType->invoices()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque tiene facturas asociadas'
                ], 422);
            }

            $invoiceType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de factura eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of invoice types for AJAX requests (para el select en creación de facturas)
     */
    public function list(Request $request)
    {
        $query = InvoiceType::orderBy('code');
        
        // Búsqueda opcional
        if ($request->has('q') && $request->q) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->q . '%')
                  ->orWhere('name', 'like', '%' . $request->q . '%');
            });
        }
        
        $invoiceTypes = $query->get(['id', 'code', 'name', 'last_correlative']);
        
        if ($request->expectsJson()) {
            return response()->json($invoiceTypes);
        }
        
        return $invoiceTypes;
    }

    /**
     * Get next correlative for a specific invoice type
     */
    public function getNextCorrelative(InvoiceType $invoiceType)
    {
        $nextCorrelative = $invoiceType->last_correlative + 1;
        $formattedCorrelative = str_pad($nextCorrelative, 8, '0', STR_PAD_LEFT);
        
        return response()->json([
            'current' => $invoiceType->last_correlative,
            'next' => $nextCorrelative,
            'formatted' => $formattedCorrelative
        ]);
    }

    /**
     * Reset correlative for a specific invoice type (solo administradores)
     */
    public function resetCorrelative(Request $request, InvoiceType $invoiceType)
    {
        $request->validate([
            'new_correlative' => 'required|integer|min:0'
        ]);

        try {
            $oldCorrelative = $invoiceType->last_correlative;
            $invoiceType->last_correlative = $request->new_correlative;
            $invoiceType->save();

            return response()->json([
                'success' => true,
                'message' => "Correlativo reiniciado de {$oldCorrelative} a {$request->new_correlative}",
                'new_correlative' => $invoiceType->last_correlative
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reiniciar correlativo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for dashboard
     */
    public function statistics()
    {
        $stats = [
            'total_types' => InvoiceType::count(),
            'total_invoices' => InvoiceType::withCount('invoices')->get()->sum('invoices_count'),
            'most_used_type' => InvoiceType::withCount('invoices')
                ->orderBy('invoices_count', 'desc')
                ->first(),
            'types_with_invoices' => InvoiceType::has('invoices')->count(),
            'types_without_invoices' => InvoiceType::doesntHave('invoices')->count(),
        ];
        
        return response()->json($stats);
    }

    /**
     * Bulk delete invoice types (solo los que no tienen facturas)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:invoice_types,id'
        ]);

        try {
            $deleted = 0;
            $errors = [];
            
            foreach ($request->ids as $id) {
                $invoiceType = InvoiceType::find($id);
                if ($invoiceType->invoices()->count() === 0) {
                    $invoiceType->delete();
                    $deleted++;
                } else {
                    $errors[] = "{$invoiceType->code} - {$invoiceType->name} (tiene facturas asociadas)";
                }
            }
            
            $message = "Se eliminaron {$deleted} tipos de factura.";
            if (!empty($errors)) {
                $message .= " No se pudieron eliminar: " . implode(', ', $errors);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deleted,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}