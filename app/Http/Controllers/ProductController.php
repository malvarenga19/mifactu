<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'equivalents']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('equivalents', function ($q2) use ($search) {
                        $q2->where('equivalent_code', 'like', "%{$search}%");
                    });
            });
        }

        // Paginación
        $perPage = $request->input('per_page', 5);
        $products = $query->orderBy('id', 'desc')->paginate($perPage);

        // Para AJAX
        // Si es petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json($products);
        }

        $categories = Category::orderBy('name')->get('id', 'name');

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:products,code',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'location' => 'nullable|string|max:255',
            'image_path' => 'nullable|image|max:2048',
            'equivalent_codes' => 'nullable|array',
            'equivalent_codes.*' => 'string|max:255|distinct',
        ]);

        // Manejar imagen
        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('products', 'public');
        }

        $initialStock = $validated['stock'];

        // Crear producto
        $product = Product::create($validated);
        if ($initialStock > 0) {
            InventoryMovement::create([
                'product_id' => $product->id,
                'type' => 'entrada',
                'quantity' => $initialStock,
                'note' => 'Entrada inicial',
                'stock_before' => 0,
                'stock_after' => $initialStock,
            ]);
        }

        // Guardar equivalencias
        if ($request->has('equivalent_codes')) {
            foreach ($request->equivalent_codes as $code) {
                if (! empty($code)) {
                    $product->equivalents()->create([
                        'equivalent_code' => $code,
                    ]);
                }
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'supplier', 'equivalents']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $product->load('equivalents');

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:products,code,'.$product->id,
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'location' => 'nullable|string|max:255',
            'image_path' => 'nullable|image|max:2048',
            'equivalent_codes' => 'nullable|array',
            'equivalent_codes.*' => 'string|max:255|distinct',
            'remove_image' => 'nullable|boolean',
        ]);

        // Manejar imagen
        if ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image_path')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image_path')->store('products', 'public');
        }

        // Actualizar producto
        $product->update($validated);

        // Actualizar equivalencias
        if ($request->has('equivalent_codes')) {
            // Eliminar equivalencias existentes
            $product->equivalents()->delete();

            // Crear nuevas equivalencias
            foreach ($request->equivalent_codes as $code) {
                if (! empty($code)) {
                    $product->equivalents()->create([
                        'equivalent_code' => $code,
                    ]);
                }
            }
        } else {
            $product->equivalents()->delete();
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->inventoryMovements()->exists() && $product->invoiceItems()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'No se puede eliminar el producto porque tiene movimientos de inventario o ha sido vendido en facturas.');
        }
        // Eliminar imagen
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        // Las equivalencias se eliminarán automáticamente por el cascade
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
