<?php

namespace App\Services\Box;

use App\Models\Sale;
use App\Models\Product;
use App\Models\User;
use App\Models\PuntoVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio específico para las operaciones del BOX Cooperadora
 */
class BoxService
{
    private $puntoVenta;

    public function __construct()
    {
        $this->puntoVenta = PuntoVenta::where('codigo', 'BOX')->first();
    }

    /**
     * Procesar venta específica del BOX con descuentos y promociones
     */
    public function procesarVenta($productos, $cliente, $descuentoTipo = null, $cajero = null)
    {
        DB::beginTransaction();

        try {
            $total = 0;
            $descuentoPorcentaje = $this->getDescuentoPorTipo($descuentoTipo);

            foreach ($productos as $producto) {
                $subtotal = $producto['precio'] * $producto['cantidad'];
                $total += $subtotal;
            }

            // Aplicar descuento
            $descuentoMonto = $total * ($descuentoPorcentaje / 100);
            $totalFinal = $total - $descuentoMonto;

            // Crear venta
            $venta = Sale::create([
                'user_id' => $cajero ?? auth()->id(),
                'student_id' => $cliente['id'] ?? null,
                'punto_venta_id' => $this->puntoVenta->id,
                'total' => $totalFinal,
                'descuento_tipo' => $descuentoTipo,
                'descuento_porcentaje' => $descuentoPorcentaje,
                'descuento_monto' => $descuentoMonto,
                'metodo_pago' => 'efectivo', // Por defecto
                'observaciones' => $this->generarObservaciones($descuentoTipo, $descuentoPorcentaje)
            ]);

            // Registrar productos de la venta
            foreach ($productos as $producto) {
                $venta->products()->attach($producto['id'], [
                    'quantity' => $producto['cantidad'],
                    'price' => $producto['precio']
                ]);
            }

            // Generar asiento contable automático
            $this->generarAsientoContable($venta);

            DB::commit();

            return [
                'success' => true,
                'venta' => $venta,
                'mensaje' => 'Venta procesada exitosamente en BOX Cooperadora'
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mensaje' => 'Error al procesar la venta'
            ];
        }
    }

    /**
     * Obtener descuento según tipo de cliente
     */
    private function getDescuentoPorTipo($tipo)
    {
        $descuentos = [
            'estudiante' => 10,
            'docente' => 15,
            'personal' => 20,
            'ninguno' => 0
        ];

        return $descuentos[$tipo] ?? 0;
    }

    /**
     * Generar observaciones para la venta
     */
    private function generarObservaciones($tipo, $porcentaje)
    {
        if ($porcentaje > 0) {
            return "Descuento aplicado: {$tipo} ({$porcentaje}%)";
        }

        return "Venta regular - BOX Cooperadora";
    }

    /**
     * Generar asiento contable automático
     */
    private function generarAsientoContable($venta)
    {
        if ($this->puntoVenta) {
            return $this->puntoVenta->generarAsientoVenta(
                $venta->total,
                "Venta #{$venta->id} - BOX Cooperadora",
                'efectivo'
            );
        }

        return null;
    }

    /**
     * Obtener productos populares del BOX
     */
    public function getProductosPopulares($limite = 10)
    {
        return DB::table('sale_product')
            ->join('sales', 'sale_product.sale_id', '=', 'sales.id')
            ->join('products', 'sale_product.product_id', '=', 'products.id')
            ->where('sales.punto_venta_id', $this->puntoVenta->id)
            ->whereMonth('sales.created_at', Carbon::now()->month)
            ->selectRaw('products.name, products.price, SUM(sale_product.quantity) as cantidad_vendida')
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderByDesc('cantidad_vendida')
            ->limit($limite)
            ->get();
    }

    /**
     * Obtener estadísticas de cajeros
     */
    public function getEstadisticasCajeros()
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->selectRaw('
                users.name,
                COUNT(*) as total_ventas,
                SUM(sales.total) as monto_total,
                AVG(sales.total) as promedio_venta,
                MAX(sales.total) as venta_maxima
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('monto_total')
            ->get();
    }

    /**
     * Generar reporte de ventas del día
     */
    public function getReporteVentasDelDia()
    {
        $ventas = Sale::whereDate('created_at', Carbon::today())
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->with(['user', 'student', 'products'])
            ->orderBy('created_at', 'desc')
            ->get();

        $resumen = [
            'total_ventas' => $ventas->count(),
            'monto_total' => $ventas->sum('total'),
            'promedio_venta' => $ventas->avg('total'),
            'venta_maxima' => $ventas->max('total'),
            'venta_minima' => $ventas->min('total'),
            'descuentos_aplicados' => $ventas->sum('descuento_monto')
        ];

        return [
            'ventas' => $ventas,
            'resumen' => $resumen
        ];
    }

    /**
     * Configurar horarios específicos del BOX
     */
    public function getHorarios()
    {
        return [
            'lunes' => ['apertura' => '08:00', 'cierre' => '18:00'],
            'martes' => ['apertura' => '08:00', 'cierre' => '18:00'],
            'miercoles' => ['apertura' => '08:00', 'cierre' => '18:00'],
            'jueves' => ['apertura' => '08:00', 'cierre' => '18:00'],
            'viernes' => ['apertura' => '08:00', 'cierre' => '18:00'],
            'sabado' => ['apertura' => '08:00', 'cierre' => '12:00'],
            'domingo' => ['apertura' => null, 'cierre' => null] // Cerrado
        ];
    }

    /**
     * Verificar si el BOX está abierto en este momento
     */
    public function estaAbierto()
    {
        $ahora = Carbon::now();
        $diaSemana = strtolower($ahora->format('l'));
        $diaSemanaEsp = $this->traducirDia($diaSemana);

        $horarios = $this->getHorarios();

        if (!isset($horarios[$diaSemanaEsp]) ||
            is_null($horarios[$diaSemanaEsp]['apertura'])) {
            return false;
        }

        $apertura = Carbon::createFromTimeString($horarios[$diaSemanaEsp]['apertura']);
        $cierre = Carbon::createFromTimeString($horarios[$diaSemanaEsp]['cierre']);

        return $ahora->between($apertura, $cierre);
    }

    /**
     * Traducir día de la semana al español
     */
    private function traducirDia($dia)
    {
        $dias = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miercoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo'
        ];

        return $dias[$dia] ?? 'lunes';
    }
}
