<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InventoryMovement::with('product');

        // Búsqueda por producto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtro por fechas
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from.' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to.' 23:59:59');
        }

        // Paginación
        $movements = $query->orderBy('id', 'desc')->paginate(20);

        if ($request->ajax()) {
            return view('inventory_movements.partials.table', compact('movements'))->render();
        }

        return view('inventory_movements.index', compact('movements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('inventory_movements.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:entrada,salida,ajuste',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $stockBefore = $product->stock;

        // Calcular nuevo stock
        if ($request->type === 'entrada') {
            $product->stock += $request->quantity;
        } elseif ($request->type === 'salida') {
            if ($product->stock < $request->quantity) {
                return back()->withErrors(['quantity' => 'No hay suficiente stock para esta salida.']);
            }
            $product->stock -= $request->quantity;
        } elseif ($request->type === 'ajuste') {
            $product->stock = $request->quantity; // En ajuste, la cantidad es el nuevo stock
        }

        $product->save();

        // Registrar movimiento
        InventoryMovement::create([
            'product_id' => $product->id,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'note' => $request->note,
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock,
        ]);

        return redirect()->route('inventory_movements.index')->with('success', 'Movimiento de inventario registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryMovement $inventoryMovement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryMovement $inventoryMovement) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryMovement $inventoryMovement) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryMovement $inventoryMovement)
    {
        //
    }
}
