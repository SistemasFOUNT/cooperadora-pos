<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Sale;
use App\Services\PDFTicket;
use App\Services\PDFFactura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                    ->where('type', 'product_sale')
                    ->sum('total'),
                'cuotas_tecnicaturas' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('type', 'student_fee')
                    ->sum('total'),
                'bonos_grado' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('type', 'student_fee')
                    ->sum('total'),
                'prestaciones_clinicas' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('type', 'treatment')
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
        $productos = Product::where('is_active', true)
                          ->get();

        return view('box.pos', compact('productos'));
    }

    /**
     * Gestión de productos específicos del BOX
     */
    public function productos()
    {
        $productos = Product::where('is_active', true)
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
                    ->with(['user', 'items.product'])
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
            'productos_activos' => Product::where('is_active', true)
                                         ->count(),
            'cajeros_activos' => \App\Models\User::where('punto_venta_id', $this->puntoVenta->id)
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

    private function getProductosMasVendidos($limit = 10)
    {
        return DB::table('items_venta')
                ->join('ventas', 'items_venta.sale_id', '=', 'ventas.id')
                ->join('productos', 'items_venta.product_id', '=', 'productos.id')
                ->where('ventas.punto_venta_id', $this->puntoVenta->id)
                ->whereMonth('ventas.created_at', Carbon::now()->month)
                ->selectRaw('productos.name, SUM(items_venta.quantity) as cantidad_vendida')
                ->groupBy('productos.id', 'productos.name')
                ->orderByDesc('cantidad_vendida')
                ->limit($limit)
                ->get();
    }

    private function getCajerosPerformance()
    {
        return Sale::where('ventas.punto_venta_id', $this->puntoVenta->id)
                  ->whereMonth('ventas.created_at', Carbon::now()->month)
                  ->join('users', 'ventas.usuario_id', '=', 'users.id')
                  ->selectRaw('users.name, COUNT(*) as total_ventas, SUM(ventas.total) as monto_total')
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

    // ===== NUEVOS MÉTODOS PARA EL MENÚ ESPECÍFICO DE BOX =====

    public function cobrosProductos()
    {
        // SOLO productos físicos (NO servicios odontológicos)
        $productos = Product::active()
            ->where('type', 'product') // Solo productos físicos
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('box.cobros.productos', [
            'title' => 'Cobros - Productos Físicos',
            'productos' => $productos
        ]);
    }

    public function cobrosOdontologia()
    {
        // SOLO servicios odontológicos (NO productos físicos)
        $servicios = Product::active()
            ->where('type', 'service') // Solo servicios odontológicos
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('box.cobros.odontologia', [
            'title' => 'Cobros - Servicios Odontológicos',
            'servicios' => $servicios
        ]);
    }

    public function cobrosCuotas()
    {
        return view('box.cobros.cuotas', [
            'title' => 'Cobros - Cuotas Estudiantiles'
        ]);
    }

    public function cobrosBonos()
    {
        return view('box.cobros.bonos', [
            'title' => 'Cobros - Bonos Estudiantiles'
        ]);
    }

    public function cobrosOtros()
    {
        return view('box.cobros.otros', [
            'title' => 'Otros Cobros'
        ]);
    }

    public function inventarioIngresos()
    {
        return view('box.inventario.ingresos', [
            'title' => 'Ingreso de Productos'
        ]);
    }

    public function pagosProveedores()
    {
        return view('box.pagos.proveedores', [
            'title' => 'Pagos a Proveedores'
        ]);
    }

    public function pagosAsignaciones()
    {
        return view('box.pagos.asignaciones', [
            'title' => 'Pagos de Asignaciones'
        ]);
    }

    public function reportesDiario(Request $request)
    {
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));
        $cajero_id = $request->get('cajero_id');

        // Obtener ventas del día con relaciones
        $query = Sale::with(['user', 'items.product', 'paymentMethod'])
            ->whereDate('created_at', $fecha)
            ->where('punto_venta_id', $this->puntoVenta->id);

        if ($cajero_id) {
            $query->where('usuario_id', $cajero_id);
        }

        $ventas = $query->orderBy('created_at', 'desc')->get();

        // Estadísticas del día
        $estadisticas = [
            'total_ventas' => $ventas->count(),
            'total_recaudado' => $ventas->sum('total'),
            'total_productos_vendidos' => $ventas->sum(function($venta) {
                return $venta->items->sum('quantity');
            }),
            'ticket_promedio' => $ventas->count() > 0 ? $ventas->sum('total') / $ventas->count() : 0,
            'primera_venta' => $ventas->max('created_at'),
            'ultima_venta' => $ventas->min('created_at')
        ];

        // Productos más vendidos del día
        $productos_vendidos = $ventas->flatMap->items
            ->groupBy('product_id')
            ->map(function ($items) {
                return [
                    'producto' => $items->first()->product_name ?? $items->first()->product->name ?? 'Producto sin nombre',
                    'codigo' => $items->first()->product_code ?? $items->first()->product->code ?? 'N/A',
                    'cantidad_total' => $items->sum('quantity'),
                    'ingreso_total' => $items->sum('total'),
                    'precio_promedio' => $items->avg('unit_price'),
                    'ventas_count' => $items->count()
                ];
            })
            ->sortByDesc('cantidad_total');

        // Métodos de pago utilizados
        $metodos_pago = $ventas->groupBy('payment_method_id')
            ->map(function ($ventas_metodo) {
                return [
                    'metodo' => $ventas_metodo->first()->paymentMethod->name ?? 'Efectivo',
                    'cantidad_transacciones' => $ventas_metodo->count(),
                    'monto_total' => $ventas_metodo->sum('total'),
                    'porcentaje' => 0 // Se calculará en la vista
                ];
            });

        // Obtener lista de cajeros para filtro
        $cajeros = \App\Models\User::where('punto_venta_id', $this->puntoVenta->id)
            ->orWhere('role', 'admin')
            ->get();

        return view('box.reportes.diario', compact(
            'ventas',
            'estadisticas',
            'productos_vendidos',
            'metodos_pago',
            'cajeros',
            'fecha',
            'cajero_id'
        ));
    }

    public function reportesMovimientos(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fecha_hasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

        // Movimientos de ventas
        $ventas = Sale::whereBetween('created_at', [$fecha_desde, $fecha_hasta])
                     ->where('punto_venta_id', $this->puntoVenta->id)
                     ->with(['user', 'items.product'])
                     ->orderBy('created_at', 'desc')
                     ->get();

        // Estadísticas del período
        $estadisticas = [
            'total_movimientos' => $ventas->count(),
            'total_ingresos' => $ventas->sum('total'),
            'promedio_diario' => $ventas->sum('total') / max(1, Carbon::parse($fecha_desde)->diffInDays($fecha_hasta) + 1),
            'movimiento_mayor' => $ventas->max('total'),
            'movimiento_menor' => $ventas->where('total', '>', 0)->min('total'),
        ];

        // Resumen por día
        $movimientos_por_dia = $ventas->groupBy(function($venta) {
            return Carbon::parse($venta->created_at)->format('Y-m-d');
        })->map(function($ventas_dia) {
            return [
                'cantidad' => $ventas_dia->count(),
                'total' => $ventas_dia->sum('total'),
                'promedio' => $ventas_dia->avg('total')
            ];
        });

        return view('box.reportes.movimientos', compact(
            'ventas',
            'estadisticas',
            'movimientos_por_dia',
            'fecha_desde',
            'fecha_hasta'
        ));
    }

    public function reportesVentas(Request $request)
    {
        $periodo = $request->input('periodo', 'mes_actual');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');

        // Determinar rango de fechas según período
        switch($periodo) {
            case 'hoy':
                $fecha_desde = $fecha_hasta = Carbon::today()->format('Y-m-d');
                break;
            case 'semana_actual':
                $fecha_desde = Carbon::now()->startOfWeek()->format('Y-m-d');
                $fecha_hasta = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'mes_actual':
                $fecha_desde = Carbon::now()->startOfMonth()->format('Y-m-d');
                $fecha_hasta = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'ano_actual':
                $fecha_desde = Carbon::now()->startOfYear()->format('Y-m-d');
                $fecha_hasta = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
            default:
                if (!$fecha_desde) $fecha_desde = Carbon::now()->startOfMonth()->format('Y-m-d');
                if (!$fecha_hasta) $fecha_hasta = Carbon::now()->format('Y-m-d');
                break;
        }

        // Consulta de ventas
        $ventas = Sale::whereBetween('created_at', [$fecha_desde, $fecha_hasta])
                     ->where('punto_venta_id', $this->puntoVenta->id)
                     ->with(['user', 'items.product'])
                     ->orderBy('created_at', 'desc')
                     ->get();

        // Análisis de ventas por período
        $analisis_ventas = [
            'total_ventas' => $ventas->count(),
            'total_ingresos' => $ventas->sum('total'),
            'ticket_promedio' => $ventas->count() > 0 ? $ventas->sum('total') / $ventas->count() : 0,
            'mejor_dia' => $ventas->groupBy(function($v) {
                    return Carbon::parse($v->created_at)->format('Y-m-d');
                })->sortByDesc(function($ventas_dia) {
                    return $ventas_dia->sum('total');
                })->keys()->first(),
        ];

        // Ventas por día (gráfico)
        $ventas_por_dia = $ventas->groupBy(function($venta) {
            return Carbon::parse($venta->created_at)->format('Y-m-d');
        })->map(function($ventas_dia, $fecha) {
            return [
                'fecha' => $fecha,
                'cantidad' => $ventas_dia->count(),
                'total' => $ventas_dia->sum('total')
            ];
        })->values();

        // Top productos vendidos
        $top_productos = $ventas->flatMap->items
                               ->groupBy('product_id')
                               ->map(function($items) {
                                   return [
                                       'producto' => $items->first()->product_name ?? $items->first()->product->name ?? 'Producto',
                                       'cantidad_total' => $items->sum('quantity'),
                                       'ingreso_total' => $items->sum('total'),
                                       'ventas_count' => $items->count()
                                   ];
                               })
                               ->sortByDesc('ingreso_total')
                               ->take(10);

        return view('box.reportes.ventas', compact(
            'ventas',
            'analisis_ventas',
            'ventas_por_dia',
            'top_productos',
            'periodo',
            'fecha_desde',
            'fecha_hasta'
        ));
    }

    public function reportesInventario(Request $request)
    {
        $categoria = $request->input('categoria', 'todas');
        $stock_minimo = $request->input('stock_minimo', false);

        // Consulta base de productos físicos únicamente (excluye servicios)
        $query = Product::where('is_active', true)
                       ->where('type', 'product'); // Solo productos físicos, no servicios

        if ($categoria !== 'todas') {
            $query->where('category', $categoria);
        }

        if ($stock_minimo) {
            $query->whereRaw('stock <= min_stock');
        }

        $productos = $query->orderBy('category')
                          ->orderBy('name')
                          ->get();

        // Estadísticas de inventario (solo productos físicos)
        $estadisticas = [
            'total_productos' => $productos->count(),
            'valor_total_inventario' => $productos->sum(function($p) {
                return $p->stock * $p->cost;
            }),
            'productos_bajo_stock' => Product::whereRaw('stock <= min_stock')
                                           ->where('is_active', true)
                                           ->where('type', 'product')
                                           ->count(),
            'productos_sin_stock' => Product::where('stock', '<=', 0)
                                           ->where('is_active', true)
                                           ->where('type', 'product')
                                           ->count(),
        ];

        // Movimientos recientes de stock
        $movimientos_recientes = DB::table('movimientos_stock')
                                  ->join('productos', 'movimientos_stock.product_id', '=', 'productos.id')
                                  ->select(
                                      'productos.name as producto_name',
                                      'productos.code as producto_code',
                                      'movimientos_stock.*'
                                  )
                                  ->orderBy('movimientos_stock.created_at', 'desc')
                                  ->limit(20)
                                  ->get();

        // Análisis por categoría
        $analisis_categorias = $productos->groupBy('category')
                                       ->map(function($productos_cat) {
                                           return [
                                               'cantidad_productos' => $productos_cat->count(),
                                               'valor_total' => $productos_cat->sum(function($p) {
                                                   return $p->stock * $p->cost;
                                               }),
                                               'stock_total' => $productos_cat->sum('stock'),
                                               'productos_bajo_stock' => $productos_cat->filter(function($p) {
                                                   return $p->stock <= $p->min_stock;
                                               })->count()
                                           ];
                                       });

        // Productos más vendidos (últimos 30 días)
        $productos_mas_vendidos = DB::table('items_venta')
                                    ->join('ventas', 'items_venta.sale_id', '=', 'ventas.id')
                                    ->join('productos', 'items_venta.product_id', '=', 'productos.id')
                                    ->where('ventas.punto_venta_id', $this->puntoVenta->id)
                                    ->where('ventas.created_at', '>=', Carbon::now()->subDays(30))
                                    ->selectRaw('productos.name, productos.code, SUM(items_venta.quantity) as cantidad_vendida, SUM(items_venta.total) as ingresos')
                                    ->groupBy('productos.id', 'productos.name', 'productos.code')
                                    ->orderByDesc('cantidad_vendida')
                                    ->limit(10)
                                    ->get();

        // Obtener categorías para filtro
        $categorias = Product::where('is_active', true)
                            ->where('type', 'product')
                            ->distinct()
                            ->pluck('category')
                            ->filter();

        return view('box.reportes.inventario', compact(
            'productos',
            'estadisticas',
            'movimientos_recientes',
            'analisis_categorias',
            'productos_mas_vendidos',
            'categorias',
            'categoria',
            'stock_minimo'
        ));
    }

    // ===== MÉTODOS DE FACTURACIÓN =====

    /**
     * Generar factura local para una venta
     */
    public function generarFacturaLocal(Request $request, Sale $sale)
    {
        try {
            $facturationService = app(\App\Services\FacturacionService::class);

            $datos_cliente = [
                'nombre' => $request->input('cliente_nombre', 'Consumidor Final'),
                'cuit' => $request->input('cliente_cuit'),
                'domicilio' => $request->input('cliente_domicilio'),
                'condicion_iva' => $request->input('cliente_condicion_iva', 'Consumidor Final')
            ];

            $factura = $facturationService->generarFacturaLocal($sale, $datos_cliente);

            return response()->json([
                'success' => true,
                'factura_id' => $factura->id,
                'numero_factura' => $factura->numero_completo,
                'mensaje' => 'Factura local generada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar factura ARCA para una venta
     */
    public function generarFacturaARCA(Request $request, Sale $sale)
    {
        $request->validate([
            'cliente_nombre' => 'required|string|max:255',
            'cliente_cuit' => 'required|string|size:13', // 00-00000000-0
            'tipo_comprobante' => 'required|in:A,B,C'
        ]);

        try {
            $facturationService = app(\App\Services\FacturacionService::class);

            $datos_cliente = [
                'nombre' => $request->input('cliente_nombre'),
                'cuit' => $request->input('cliente_cuit'),
                'domicilio' => $request->input('cliente_domicilio'),
                'condicion_iva' => $request->input('cliente_condicion_iva'),
                'email' => $request->input('cliente_email')
            ];

            $factura = $facturationService->generarFacturaARCA(
                $sale,
                $datos_cliente,
                $request->input('tipo_comprobante')
            );

            return response()->json([
                'success' => true,
                'factura_id' => $factura->id,
                'numero_factura' => $factura->numero_completo,
                'cae' => $factura->cae,
                'fecha_vto_cae' => $factura->fecha_vto_cae,
                'qr_arca' => $factura->qr_arca,
                'mensaje' => $factura->estado === 'autorizada' ? 'Factura ARCA autorizada exitosamente' : 'Factura generada pero pendiente de autorización'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modal para datos del cliente en facturación
     */
    public function modalCliente($ventaId)
    {
        $venta = \App\Models\Sale::findOrFail($ventaId);

        // Verificar que la venta pertenece al punto de venta correcto
        if ($venta->punto_venta_id != $this->puntoVenta->id) {
            abort(403, 'No tienes acceso a esta venta.');
        }

        return view('box.facturas.modal-cliente', compact('venta'));
    }

    /**
     * Procesar pago y generar factura directamente (mejora UX)
     */
    public function procesarPagoConFactura(Request $request)
    {
        try {
            Log::info('procesarPagoConFactura: Datos recibidos', $request->all());

            // DECODIFICAR JSON que viene del frontend
            $datosCliente = json_decode($request->input('datosCliente', '{}'), true);
            $carrito = json_decode($request->input('carrito', '[]'), true);

            // Obtener método de pago y tipo de comprobante
            $metodoPago = $request->input('metodoPago', 'efectivo');
            $tipoComprobante = $request->input('tipoComprobante', 'factura_local');

            // Datos COMPLETOS para todos los medios de pago
            $datos = [
                'cliente_nombre' => $datosCliente['nombre'] ?? 'Cliente Genérico',
                'cliente_documento' => $datosCliente['documento'] ?? '00000000',
                'cliente_direccion' => $datosCliente['direccion'] ?? '',
                'cliente_condicion_iva' => $datosCliente['condicionIva'] ?? 'consumidor_final',
                'metodo_pago' => $metodoPago,
                'tipo_comprobante' => $tipoComprobante,
                'total' => (float) $request->input('totalFinal', 0),
                'subtotal' => (float) $request->input('subtotal', 0),
                'descuento' => (float) $request->input('descuento', 0),
                'observaciones' => $request->input('observaciones', ''),
                'productos' => []
            ];

            // Procesar productos del carrito
            foreach ($carrito as $item) {
                $producto = \App\Models\Product::find($item['id'] ?? $item['producto_id'] ?? null);
                $datos['productos'][] = [
                    'id' => $producto->id ?? ($item['id'] ?? $item['producto_id'] ?? null),
                    'code' => $producto->code ?? ($item['code'] ?? ''),
                    'nombre' => $producto->name ?? 'Producto',
                    'cantidad' => (int) ($item['quantity'] ?? $item['cantidad'] ?? 1),
                    'precio' => (float) ($item['price'] ?? $item['precio_unitario'] ?? 0),
                    'total' => (float) ($item['quantity'] ?? $item['cantidad'] ?? 1) * (float) ($item['price'] ?? $item['precio_unitario'] ?? 0)
                ];
            }

            Log::info('procesarPagoConFactura: Datos procesados', $datos);

            // ── Guardar venta + factura en BD ──────────────────────────────
            $ventaId  = null;
            $facturaId = null;

            try {
                \DB::beginTransaction();

                // 1. Resolver método de pago obligatorio para ventas
                $codigoMetodoPago = match ($datos['metodo_pago']) {
                    'efectivo' => 'EFE',
                    'tarjeta' => 'TDC',
                    'transferencia' => 'TRA',
                    'mixto' => 'EFE',
                    default => 'EFE',
                };

                $paymentMethodId = \App\Models\PaymentMethod::where('code', $codigoMetodoPago)->value('id');

                if (!$paymentMethodId) {
                    throw new \RuntimeException("No existe método de pago configurado para código {$codigoMetodoPago}");
                }

                // 2. Generar número de venta único (campo NOT NULL)
                $saleNumber = 'PV' . str_pad((string) $this->puntoVenta->id, 3, '0', STR_PAD_LEFT)
                    . '-' . now()->format('YmdHis')
                    . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

                // 3. Crear la venta
                $venta = \App\Models\Sale::create([
                    'sale_number' => $saleNumber,
                    'punto_venta_id' => $this->puntoVenta->id,
                    'usuario_id'     => auth()->id(),
                    'payment_method_id' => $paymentMethodId,
                    'type'           => 'product_sale',
                    'subtotal'       => $datos['subtotal'],
                    'discount_amount'=> $datos['descuento'],
                    'total'          => $datos['total'],
                    'fecha_venta'    => now(),
                    'status'         => 'completed',
                    'notes'          => $datos['observaciones'],
                    'additional_data'=> [
                        'metodo_pago'    => $datos['metodo_pago'],
                        'tipo_comprobante'=> $datos['tipo_comprobante'],
                        'descuento'      => $datos['descuento'],
                    ],
                ]);

                $ventaId = $venta->id;

                // 4. Guardar items de la venta
                foreach ($datos['productos'] as $item) {
                    if (empty($item['id'])) {
                        throw new \RuntimeException('Producto inválido en carrito: falta ID para guardar item de venta.');
                    }

                    $venta->items()->create([
                        'product_id'   => $item['id'],
                        'product_code' => $item['code'] ?: 'SIN-CODIGO',
                        'product_name' => $item['nombre'],
                        'quantity'     => $item['cantidad'],
                        'unit_price'   => $item['precio'],
                        'subtotal'     => $item['total'],
                        'total'        => $item['total'],
                    ]);
                }

                // 5. Calcular próximo número de factura para este punto de venta
                // PostgreSQL no permite FOR UPDATE sobre agregaciones (MAX),
                // por eso se toma el último registro bloqueando fila.
                $ultimoNumero = \App\Models\Factura::where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'local')
                    ->orderByDesc('numero')
                    ->lockForUpdate()
                    ->value('numero') ?? 0;

                $nuevoNumero = $ultimoNumero + 1;

                // 6. Determinar tipo de comprobante para el campo tipo_comprobante
                $tipoCompMap = [
                    'ticket'        => null,
                    'factura_local' => 'B',
                    'factura_fiscal'=> 'B',
                ];

                // 7. Crear registro en facturas
                $factura = \App\Models\Factura::create([
                    'sale_id'          => $venta->id,
                    'punto_venta_id'   => $this->puntoVenta->id,
                    'tipo'             => 'local',
                    'tipo_comprobante' => $tipoCompMap[$datos['tipo_comprobante']] ?? null,
                    'numero'           => $nuevoNumero,
                    'numero_completo'  => sprintf('%08d', $nuevoNumero),
                    'fecha_emision'    => now(),
                    'datos_cliente'    => [
                        'nombre'       => $datos['cliente_nombre'],
                        'documento'    => $datos['cliente_documento'],
                        'direccion'    => $datos['cliente_direccion'],
                        'condicion_iva'=> $datos['cliente_condicion_iva'],
                    ],
                    'subtotal'         => $datos['subtotal'],
                    'total'            => $datos['total'],
                    'estado'           => 'emitida',
                    'observaciones'    => $datos['observaciones'],
                    'created_by'       => auth()->id(),
                ]);

                $facturaId = $factura->id;
                $datos['numero_factura'] = $factura->numero_completo;
                $datos['factura_id']     = $factura->id;

                \DB::commit();
                Log::info('procesarPagoConFactura: Venta y factura guardadas', [
                    'venta_id'   => $ventaId,
                    'factura_id' => $facturaId,
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                Log::error('procesarPagoConFactura: Error guardando en BD', ['error' => $e->getMessage()]);
                return response()->json([
                    'error' => 'No se pudo guardar la venta/factura en base de datos. Operación cancelada.',
                    'detalle' => $e->getMessage(),
                ], 500);
            }

            // ── Generar PDF desde el registro persistido ───────────────────
            // Esto garantiza consistencia total entre factura inicial y reimpresión.
            $facturaPersistida = \App\Models\Factura::with([
                'sale',
                'sale.items',
                'sale.items.product',
                'sale.paymentMethod',
            ])->findOrFail($facturaId);

            return $this->generarPDFFactura($facturaPersistida);

        } catch (\Exception $e) {
            Log::error('procesarPagoConFactura: Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error generando factura'], 500);
        }
    }

    public function verFactura(Request $request, \App\Models\Factura $factura)
    {
        // Cargar relaciones necesarias para evitar problemas de datos
        $factura->load(['sale', 'sale.items', 'sale.items.product', 'sale.user']);

        // Verificar que la factura pertenece al punto de venta correcto
        if ($factura->sale->punto_venta_id != $this->puntoVenta->id) {
            abort(403, 'No tienes acceso a esta factura.');
        }

        $formato = $request->input('formato', 'html'); // html, pdf

        if ($formato === 'pdf') {
            return $this->generarPDFFactura($factura);
        }

        return view('box.facturas.ver', compact('factura'));
    }

    /**
     * Listar facturas generadas
     */
    public function listarFacturas(Request $request)
    {
        $filtros = $request->only(['fecha_desde', 'fecha_hasta', 'tipo', 'estado']);

        $query = \App\Models\Factura::whereHas('sale', function($q) {
            $q->where('punto_venta_id', $this->puntoVenta->id);
        })->with(['sale', 'sale.user']);

        // Aplicar filtros
        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_emision', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_emision', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        $facturas = $query->orderBy('fecha_emision', 'desc')
                         ->paginate(25);

        return view('box.facturas.lista', compact('facturas', 'filtros'));
    }

    /**
     * Anular una factura
     */
    public function anularFactura(Request $request, \App\Models\Factura $factura)
    {
        $request->validate([
            'motivo' => 'required|string|max:500'
        ]);

        // Verificar que la factura pertenece al punto de venta correcto
        if ($factura->sale->punto_venta_id != $this->puntoVenta->id) {
            abort(403, 'No tienes acceso a esta factura.');
        }

        // No se puede anular una factura ya anulada
        if ($factura->estado === 'anulada') {
            return back()->withErrors(['error' => 'La factura ya está anulada.']);
        }

        try {
            $factura->anular($request->input('motivo'));

            return back()->with('success', 'Factura anulada exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al anular la factura: ' . $e->getMessage()]);
        }
    }

    /**
     * Generar PDF de la factura
     */
    private function generarPDFFactura(\App\Models\Factura $factura)
    {
        try {
            \Log::info('generarPDFFactura: Iniciando generación PDF', ['factura_id' => $factura->id]);

            $pdfService = new \App\Services\PDFFactura();
            \Log::info('generarPDFFactura: Servicio PDF creado');

            $pdf = $pdfService->generar($factura);
            \Log::info('generarPDFFactura: PDF generado exitosamente');

            $response = $pdf->descargar();
            \Log::info('generarPDFFactura: Response preparada para descarga');

            return $response;

        } catch (\Exception $e) {
            \Log::error('generarPDFFactura: Error', [
                'message' => $e->getMessage(),
                'factura_id' => $factura->id ?? 'null',
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Modal para datos del cliente (facturación)
     */
    public function modalFacturacion(Sale $sale)
    {
        return view('box.facturas.modal-cliente', compact('sale'));
    }

    // Métodos auxiliares privados existentes...

    private function getVentasDelDia()
    {
        return Sale::whereDate('created_at', Carbon::today())
                  ->where('punto_venta_id', $this->puntoVenta->id)
                  ->count();
    }

    private function getResumenFinanciero()
    {
        return [
            'ingresos_dia' => Sale::whereDate('created_at', Carbon::today())
                                 ->where('punto_venta_id', $this->puntoVenta->id)
                                 ->sum('total'),
            'ingresos_mes' => Sale::whereMonth('created_at', Carbon::now()->month)
                                 ->where('punto_venta_id', $this->puntoVenta->id)
                                 ->sum('total'),
        ];
    }

    private function getAlertasSistema()
    {
        return [
            'productos_bajo_stock' => Product::whereRaw('stock <= min_stock')
                                           ->where('is_active', true)
                                           ->count(),
            'facturas_pendientes' => \App\Models\Factura::where('estado', 'pendiente_arca')
                                                       ->whereHas('sale', function($q) {
                                                           $q->where('punto_venta_id', $this->puntoVenta->id);
                                                       })
                                                       ->count(),
        ];
    }
}
