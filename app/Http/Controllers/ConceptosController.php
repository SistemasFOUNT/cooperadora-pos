<?php

namespace App\Http\Controllers;

use App\Models\CareerFeeConfig;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConceptosController extends Controller
{
    // Tipos de carrera que pertenecen a postgrado (no deben verse en BOX)
    const TIPOS_POSTGRADO = ['postgrado'];

    // Tipos de carrera de grado: solo manejan precio de bono (sin cuotas mensuales)
    const TIPOS_SOLO_BONO = ['grado_odontologia'];

    /**
     * Pantalla principal de Conceptos: precios de productos, cuotas y bonos.
     * Solo muestra carreras que NO sean de postgrado.
     */
    public function index()
    {
        $carreras  = CareerFeeConfig::whereNotIn('tipo_carrera', self::TIPOS_POSTGRADO)
                        ->orderBy('nombre_carrera')
                        ->get();

        $productos = Product::where('is_active', true)->orderBy('name')->get();

        return view('box.conceptos.index', compact('carreras', 'productos'));
    }

    /**
     * Vista de edición completa de productos (nombre, código, precio, stock).
     */
    public function editarProductos()
    {
        $productos = Product::orderBy('name')->get();

        return view('box.inventario.productos', compact('productos'));
    }

    /**
     * Actualizar todos los datos de un producto.
     */
    public function actualizarProductoCompleto(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'code'  => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $product->update([
            'name'  => $validated['name'],
            'code'  => $validated['code'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'] ?? $product->stock,
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => "Producto \"{$product->name}\" actualizado correctamente.",
        ]);
    }

    /**
     * Eliminar un producto.
     */
    public function eliminarProducto(Product $product)
    {
        $nombre = $product->name;
        $product->delete();

        return response()->json([
            'success' => true,
            'mensaje' => "Producto \"{$nombre}\" eliminado correctamente.",
        ]);
    }

    /**
     * Actualizar los valores de una carrera (cuota mensual, bono, interés, vencimiento).
     * Para carreras "solo bono" (grado), solo actualiza cuota_bono.
     */
    public function actualizarCarrera(Request $request, CareerFeeConfig $carrera)
    {
        $esSoloBono = in_array($carrera->tipo_carrera, self::TIPOS_SOLO_BONO);

        if ($esSoloBono) {
            $validated = $request->validate([
                'cuota_bono' => 'required|numeric|min:0',
                'bono_inicio_cobro' => 'nullable|date',
                'bono_fin_cobro' => 'nullable|date|after_or_equal:bono_inicio_cobro',
            ]);
            $carrera->update([
                'cuota_bono' => $validated['cuota_bono'],
                'bono_inicio_cobro' => $validated['bono_inicio_cobro'] ?? null,
                'bono_fin_cobro' => $validated['bono_fin_cobro'] ?? null,
            ]);
        } else {
            $validated = $request->validate([
                'cuota_mensual'      => 'required|numeric|min:0',
                'cuota_bono'         => 'required|numeric|min:0',
                'cuota_inscripcion'  => 'required|numeric|min:0',
                'bono_inicio_cobro'  => 'nullable|date',
                'bono_fin_cobro'     => 'nullable|date|after_or_equal:bono_inicio_cobro',
                'dia_vencimiento_1'  => 'required|integer|min:1|max:28',
                'dia_vencimiento_2'  => 'required|integer|min:1|max:31|gte:dia_vencimiento_1',
                'porcentaje_recargo_1' => 'required|numeric|min:0|max:100',
                'porcentaje_recargo_2' => 'required|numeric|min:0|max:100',
                'porcentaje_recargo_3' => 'required|numeric|min:0|max:100',
            ]);

            // Compatibilidad con la lógica legacy que usa un único día y porcentaje.
            $validated['dia_vencimiento'] = $validated['dia_vencimiento_1'];
            $validated['porcentaje_recargo'] = $validated['porcentaje_recargo_3'];
            $validated['dias_gracia'] = 0;
            $carrera->update($validated);
        }

        return response()->json([
            'success' => true,
            'mensaje' => "Configuración de {$carrera->nombre_carrera} actualizada correctamente.",
        ]);
    }

    /**
     * Actualizar el precio de un producto.
     */
    public function actualizarProducto(Request $request, Product $product)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $product->update(['price' => $validated['price']]);

        return response()->json([
            'success' => true,
            'mensaje' => "Precio de \"{$product->name}\" actualizado correctamente.",
        ]);
    }

    /**
     * Actualizar precio de múltiples productos en lote.
     */
    public function actualizarProductosLote(Request $request)
    {
        $validated = $request->validate([
            'productos'         => 'required|array',
            'productos.*.id'    => 'required|exists:productos,id',
            'productos.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['productos'] as $item) {
                Product::where('id', $item['id'])->update(['price' => $item['price']]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Precios actualizados correctamente.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando precios en lote', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => 'Error al actualizar los precios.',
            ], 500);
        }
    }
}
