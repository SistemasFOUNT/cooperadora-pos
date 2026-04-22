<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Mostrar lista de productos
     */
    public function index(): View
    {
        $productos = Product::orderBy('name')->paginate(15);

        return view('productos.index', compact('productos'));
    }

    /**
     * Mostrar formulario para crear nuevo producto
     */
    public function create(): View
    {
        return view('productos.create');
    }

    /**
     * Almacenar nuevo producto
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'barcode' => 'nullable|string|unique:products',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Product::create($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Mostrar producto específico
     */
    public function show(Product $producto): View
    {
        return view('productos.show', compact('producto'));
    }

    /**
     * Mostrar formulario para editar producto
     */
    public function edit(Product $producto): View
    {
        return view('productos.edit', compact('producto'));
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, Product $producto): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'barcode' => 'nullable|string|unique:products,barcode,' . $producto->id,
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $producto->update($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Eliminar producto
     */
    public function destroy(Product $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    /**
     * Gestionar categorías
     */
    public function categorias(): View
    {
        $categorias = Product::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('productos.categorias', compact('categorias'));
    }

    /**
     * Vista de inventario
     */
    public function inventario(): View
    {
        $productos = Product::where('is_active', true)
            ->orderBy('stock', 'asc')
            ->get();

        return view('productos.inventario', compact('productos'));
    }

    /**
     * Actualizar stock de producto
     */
    public function actualizarStock(Request $request, Product $producto): RedirectResponse
    {
        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
            'motivo' => 'nullable|string|max:255',
        ]);

        $stockAnterior = $producto->stock;
        $producto->update(['stock' => $validated['stock']]);

        $diferencia = $validated['stock'] - $stockAnterior;
        $accion = $diferencia > 0 ? 'aumentó' : 'disminuyó';

        return redirect()->route('productos.inventario')
            ->with('success', "Stock {$accion} de {$stockAnterior} a {$validated['stock']} unidades.");
    }

    /**
     * Activar/desactivar producto
     */
    public function toggleActivo(Product $producto): RedirectResponse
    {
        $producto->update(['is_active' => !$producto->is_active]);

        $status = $producto->is_active ? 'activado' : 'desactivado';

        return redirect()->route('productos.index')
            ->with('success', "Producto {$status} exitosamente.");
    }

    /**
     * Búsqueda AJAX de productos
     */
    public function buscar(Request $request)
    {
        $term = $request->get('term');

        $productos = Product::where('is_active', true)
            ->where(function($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                      ->orWhere('barcode', 'LIKE', "%{$term}%")
                      ->orWhere('category', 'LIKE', "%{$term}%");
            })
            ->limit(10)
            ->get();

        return response()->json($productos->map(function($producto) {
            return [
                'id' => $producto->id,
                'text' => "{$producto->name} - \$" . number_format($producto->price, 2),
                'name' => $producto->name,
                'price' => $producto->price,
                'stock' => $producto->stock,
                'category' => $producto->category,
            ];
        }));
    }
}
