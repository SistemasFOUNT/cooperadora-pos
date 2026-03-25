<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_products')->only(['index', 'show']);
        $this->middleware('permission:create_products')->only(['create', 'store']);
        $this->middleware('permission:edit_products')->only(['edit', 'update']);
        $this->middleware('permission:delete_products')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Para DataTables, obtenemos todos los productos sin paginación
        // DataTables manejará la paginación, búsqueda y ordenamiento del lado del cliente
        $products = Product::orderBy('name')->get();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
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
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);

        return redirect()->route('products.index')
            ->with('success', 'Producto desactivado exitosamente.');
    }

    /**
     * Search products for POS
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');

        $products = Product::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json($products);
    }
}
