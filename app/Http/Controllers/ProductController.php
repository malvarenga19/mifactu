<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier', 'equivalents']);

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

        // Filtro por categoría
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por stock bajo
        if ($request->boolean('low_stock')) {
            $query->whereRaw('stock <= min_stock');
        }

        // Paginación
        $perPage = $request->input('per_page', 10);
        $products = $query->orderBy('id', 'desc')->paginate($perPage);

        // Para AJAX
        if ($request->ajax()) {
            return view('products.partials.table', compact('products'))->render();
        }

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'suppliers'));
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

        // Crear producto
        $product = Product::create($validated);

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
