<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // ProductController.php
    public function index(Request $request)
    {
        // Si es petición AJAX, devolver solo los productos
        if ($request->ajax()) {
            $perPage = $request->input('per_page', 10);
            $allowedPerPage = [10, 25, 50, 100];

            if (! in_array($perPage, $allowedPerPage)) {
                $perPage = 10;
            }

            $query = Product::with(['category', 'supplier']);

            // Búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtro por categoría
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filtro por stock bajo
            if ($request->filled('low_stock')) {
                $query->lowStock();
            }

            $products = $query->orderBy('id', 'desc')->paginate($perPage);
            $products->appends(request()->query());

            return view('products.partials.table', compact('products'))->render();
        }

        // Si no es AJAX, cargar la página completa
        $perPage = $request->input('per_page', 10);
        $allowedPerPage = [10, 25, 50, 100];

        if (! in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $query = Product::with(['category', 'supplier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('low_stock')) {
            $query->lowStock();
        }

        $products = $query->orderBy('id', 'desc')->paginate($perPage);
        $products->appends(request()->query());

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $suppliers = Supplier::all();

        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric',
            'sale_price' => 'required|numeric',
            'stock' => 'nullable|integer',
            'min_stock' => 'nullable|integer',
            'image_path' => 'nullable|image|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'code' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('image_path')) {
                $validated['image_path'] = $request->file('image_path')
                    ->store('product_images', 'public');
            }

            Product::create($validated);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($validated['image_path'])) {
                Storage::disk('public')->delete($validated['image_path']);
            }

            throw $e;
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $suppliers = Supplier::all();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric',
            'sale_price' => 'required|numeric',
            'stock' => 'nullable|integer',
            'min_stock' => 'nullable|integer',
            'image_path' => 'nullable|image|max:2048', // 👈 FIX
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'code' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image_path')) { // 👈 FIX
            // borrar imagen vieja
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            // guardar nueva
            $validated['image_path'] = $request->file('image_path')
                ->store('product_images', 'public');
        } else {
            // evitar borrar el path existente
            unset($validated['image_path']); // 👈 CLAVE
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
