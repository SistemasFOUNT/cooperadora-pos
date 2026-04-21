<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BoxController extends Controller
{
    private $puntoVenta;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('punto_venta');
        $this->middleware(function ($request, $next) {
            $this->puntoVenta = PuntoVenta::where('codigo', 'BOX')->first();
            return $next($request);
        });
    }

    /**
     * Dashboard principal del BOX
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Verificar que el usuario sea de BOX
        if (!$user->isAdmin() && $user->punto_venta_id != $this->puntoVenta->id) {
            abort(403, 'No tienes acceso al BOX Cooperadora.');
        }

        $estadisticas = $this->getEstadisticas();

        return view('box.dashboard', compact('estadisticas'));
    }

    /**
     * Supervisión administrativa del punto de venta BOX
     * Solo accesible para admin
     */
    public function adminSupervision()
    {
        // Este método solo es accesible a través del middleware 'admin'
        $datos_supervision = [
            'ventas_del_dia' => $this->getVentasDelDia(),
            'productos_mas_vendidos' => $this->getProductosMasVendidos(10),
            'resumen_financiero' => $this->getResumenFinanciero(),
            'alertas_sistema' => $this->getAlertasSistema(),
            'estadisticas_completas' => $this->getEstadisticas(),
        ];

        return view('admin.supervision.box', compact('datos_supervision'));
    }

    /**
     * Ingresos y Egresos detallados para admin
     */
    public function adminIngresosEgresos()
    {
        $fechaHoy = Carbon::today();

        $ingresos_egresos = [
            'ingresos_hoy' => [
                'ventas_productos' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'venta_producto')
                    ->sum('total'),
                'cuotas_tecnicaturas' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'cuota_tecnicatura')
                    ->sum('total'),
                'bonos_grado' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'bono_grado')
                    ->sum('total'),
                'prestaciones_clinicas' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'prestacion_clinica')
                    ->sum('total'),
            ],
            'egresos_hoy' => [
                'pagos_proveedores' => 0, // TODO: Implementar modelo de pagos
                'sueldos_contratados' => 0,
                'otros_pagos' => 0,
            ],
            'detalle_transacciones' => Sale::with(['user', 'items.product'])
                ->whereDate('created_at', $fechaHoy)
                ->where('punto_venta_id', $this->puntoVenta->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
        ];

        return view('admin.ingresos-egresos.box', compact('ingresos_egresos'));
    }

    /**
     * Libro Caja específico para BOX
     */
    public function adminLibroCaja()
    {
        $fechaDesde = request('fecha_desde', Carbon::today()->subDays(30)->format('Y-m-d'));
        $fechaHasta = request('fecha_hasta', Carbon::today()->format('Y-m-d'));

        $movimientos_caja = [
            'resumen_periodo' => [
                'total_ingresos' => Sale::whereBetween('created_at', [$fechaDesde, $fechaHasta])
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->sum('total'),
                'total_egresos' => 0, // TODO: Implementar modelo de egresos
                'saldo_periodo' => Sale::whereBetween('created_at', [$fechaDesde, $fechaHasta])
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->sum('total'),
            ],
            'movimientos_detalle' => Sale::with(['user'])
                ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
                ->where('punto_venta_id', $this->puntoVenta->id)
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
        ];

        return view('admin.libro-caja.box', compact('movimientos_caja'));
    }

    /**
     * POS específico del BOX
     */
    public function pos()
    {
        $productos = Product::where('active', true)
                          ->where('punto_venta_id', $this->puntoVenta->id)
                          ->orWhereNull('punto_venta_id')
                          ->get();

        return view('box.pos', compact('productos'));
    }

    /**
     * Gestión de productos específicos del BOX
     */
    public function productos()
    {
        $productos = Product::where('punto_venta_id', $this->puntoVenta->id)
                          ->orWhereNull('punto_venta_id')
                          ->paginate(15);

        return view('box.productos.index', compact('productos'));
    }

    /**
     * Ventas del día - BOX
     */
    public function ventasDelDia()
    {
        $ventas = Sale::whereDate('created_at', Carbon::today())
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->with(['user', 'products'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('box.ventas.dia', compact('ventas'));
    }

    /**
     * Reportes específicos del BOX
     */
    public function reportes()
    {
        $reportes = [
            'ventas_mes' => $this->getVentasMes(),
            'productos_mas_vendidos' => $this->getProductosMasVendidos(),
            'cajeros_performance' => $this->getCajerosPerformance()
        ];

        return view('box.reportes.index', compact('reportes'));
    }

    /**
     * Configuración específica del BOX
     */
    public function configuracion()
    {
        $configuracion = [
            'punto_venta' => $this->puntoVenta,
            'horarios' => $this->getHorarios(),
            'descuentos' => $this->getDescuentos()
        ];

        return view('box.configuracion', compact('configuracion'));
    }

    // Métodos privados auxiliares
    private function getEstadisticas()
    {
        return [
            'ventas_hoy' => Sale::whereDate('created_at', Carbon::today())
                              ->where('punto_venta_id', $this->puntoVenta->id)
                              ->sum('total'),
            'ventas_mes' => Sale::whereMonth('created_at', Carbon::now()->month)
                              ->where('punto_venta_id', $this->puntoVenta->id)
                              ->sum('total'),
            'productos_activos' => Product::where('active', true)
                                         ->where('punto_venta_id', $this->puntoVenta->id)
                                         ->count(),
            'cajeros_activos' => \App\Models\User::where('role', 'usuario_box')
                                              ->where('punto_venta_id', $this->puntoVenta->id)
                                              ->where('status', 'active')
                                              ->count()
        ];
    }

    private function getVentasMes()
    {
        return Sale::whereMonth('created_at', Carbon::now()->month)
                  ->where('punto_venta_id', $this->puntoVenta->id)
                  ->selectRaw('DATE(created_at) as fecha, SUM(total) as total')
                  ->groupBy('fecha')
                  ->orderBy('fecha')
                  ->get();
    }

    private function getProductosMasVendidos()
    {
        return DB::table('sale_product')
                ->join('sales', 'sale_product.sale_id', '=', 'sales.id')
                ->join('products', 'sale_product.product_id', '=', 'products.id')
                ->where('sales.punto_venta_id', $this->puntoVenta->id)
                ->whereMonth('sales.created_at', Carbon::now()->month)
                ->selectRaw('products.name, SUM(sale_product.quantity) as cantidad_vendida')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('cantidad_vendida')
                ->limit(10)
                ->get();
    }

    private function getCajerosPerformance()
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
                  ->whereMonth('created_at', Carbon::now()->month)
                  ->join('users', 'sales.user_id', '=', 'users.id')
                  ->selectRaw('users.name, COUNT(*) as total_ventas, SUM(sales.total) as monto_total')
                  ->groupBy('users.id', 'users.name')
                  ->orderByDesc('monto_total')
                  ->get();
    }

    private function getHorarios()
    {
        // Horarios específicos del BOX
        return [
            'lunes_viernes' => '08:00 - 18:00',
            'sabados' => '08:00 - 12:00',
            'domingos' => 'Cerrado'
        ];
    }

    private function getDescuentos()
    {
        // Descuentos específicos del BOX
        return [
            'estudiantes' => 10,
            'docentes' => 15,
            'personal' => 20
        ];
    }
}
