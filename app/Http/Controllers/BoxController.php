<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Sale;
use App\Services\PDFTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $productos = Product::active()
            ->where('type', '!=', 'fee') // Excluir cuotas estudiantiles
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('box.cobros.productos', [
            'title' => 'Cobros - Productos',
            'productos' => $productos
        ]);
    }

    public function cobrosOdontologia()
    {
        return view('box.cobros.odontologia', [
            'title' => 'Cobros - Servicios Odontológicos'
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
                                    ->selectRaw('
                                        productos.id,
                                        productos.name,
                                        productos.code,
                                        productos.stock,
                                        productos.min_stock,
                                        SUM(items_venta.quantity) as total_vendido
                                    ')
                                    ->groupBy('productos.id', 'productos.name', 'productos.code', 'productos.stock', 'productos.min_stock')
                                    ->orderByDesc('total_vendido')
                                    ->limit(10)
                                    ->get();

        // Categorías disponibles (solo de productos físicos)
        $categorias = Product::where('type', 'product')
                           ->where('is_active', true)
                           ->distinct()
                           ->pluck('category')
                           ->filter();

        // Conteo de servicios (para información adicional)
        $total_servicios = Product::where('type', 'service')
                                 ->where('is_active', true)
                                 ->count();

        return view('box.reportes.inventario', compact(
            'productos',
            'estadisticas',
            'movimientos_recientes',
            'analisis_categorias',
            'productos_mas_vendidos',
            'categorias',
            'categoria',
            'stock_minimo',
            'total_servicios'
        ));
    }

    /**
     * Generar PDF del ticket de venta
     */
    public function generarTicketPDF(Request $request)
    {
        $datosVenta = $request->validate([
            'carrito' => 'required|array',
            'carrito.*.id' => 'required|integer',
            'carrito.*.name' => 'required|string',
            'carrito.*.code' => 'required|string',
            'carrito.*.price' => 'required|numeric',
            'carrito.*.quantity' => 'required|integer',
            'subtotal' => 'required|numeric',
            'descuento' => 'required|numeric',
            'totalFinal' => 'required|numeric',
            'metodoPago' => 'required|string',
            'montoRecibido' => 'nullable|numeric',
            'vuelto' => 'nullable|numeric',
            'observaciones' => 'nullable|string'
        ]);

        // Crear el HTML del ticket
        $html = $this->generarHTMLTicket($datosVenta);

        // Usar TCPDF que viene incluido en muchas instalaciones de PHP
        // o simplemente generar un PDF básico con HTML
        return $this->generarPDFBasico($html, $datosVenta);
    }

    private function generarHTMLTicket($datos)
    {
        $fecha = now()->format('d/m/Y H:i:s');
        $cajero = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->name : 'Sistema';
        $puntoVenta = session('punto_venta_nombre', 'BOX Principal');

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Ticket de Venta</title>
            <style>
                @page { margin: 0.5cm; size: 80mm auto; }
                body {
                    font-family: "Courier New", monospace;
                    font-size: 11px;
                    margin: 0;
                    padding: 5px;
                    width: 70mm;
                    line-height: 1.2;
                }
                .header {
                    text-align: center;
                    border-bottom: 2px dashed #000;
                    padding-bottom: 8px;
                    margin-bottom: 12px;
                }
                .header h2 { margin: 0 0 3px 0; font-size: 14px; font-weight: bold; }
                .header p { margin: 1px 0; font-size: 9px; }
                .seccion { margin-bottom: 10px; }
                .titulo-seccion {
                    font-weight: bold;
                    border-bottom: 1px solid #000;
                    margin-bottom: 5px;
                    font-size: 10px;
                }
                .producto {
                    margin-bottom: 6px;
                    border-bottom: 1px dotted #ccc;
                    padding-bottom: 3px;
                }
                .producto-nombre { font-weight: bold; font-size: 10px; }
                .producto-detalle { font-size: 8px; color: #555; margin: 1px 0; }
                .producto-total { text-align: right; margin-top: 2px; font-weight: bold; }
                .totales {
                    border-top: 2px dashed #000;
                    padding-top: 8px;
                    margin-top: 10px;
                }
                .total-linea {
                    display: flex;
                    justify-content: space-between;
                    margin: 2px 0;
                    font-size: 10px;
                }
                .total-final {
                    font-weight: bold;
                    font-size: 12px;
                    border-top: 1px solid #000;
                    padding-top: 4px;
                    margin-top: 4px;
                }
                .pago {
                    border-top: 1px dashed #000;
                    padding-top: 8px;
                    margin-top: 8px;
                }
                .pago-metodo {
                    text-align: center;
                    font-weight: bold;
                    margin-bottom: 6px;
                    font-size: 10px;
                }
                .footer {
                    text-align: center;
                    border-top: 2px dashed #000;
                    padding-top: 8px;
                    margin-top: 12px;
                }
                .footer p { margin: 1px 0; font-size: 8px; }
                .observaciones {
                    border-top: 1px dotted #000;
                    padding-top: 6px;
                    margin-top: 8px;
                    font-size: 9px;
                }
                @media print {
                    body { width: auto; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>COOPERADORA</h2>
                <p>BOX - Punto de Venta</p>
                <p>' . $fecha . '</p>
                <p>Cajero: ' . htmlspecialchars($cajero) . '</p>
                <p>Punto: ' . htmlspecialchars($puntoVenta) . '</p>
            </div>

            <div class="seccion">
                <div class="titulo-seccion">DETALLE DE PRODUCTOS</div>';

        foreach ($datos['carrito'] as $item) {
            $subtotalItem = $item['price'] * $item['quantity'];
            $html .= '
                <div class="producto">
                    <div class="producto-nombre">' . htmlspecialchars($item['name']) . '</div>
                    <div class="producto-detalle">' . htmlspecialchars($item['code']) . ' x ' . $item['quantity'] . ' @ $' . number_format($item['price'], 2) . '</div>
                    <div class="producto-total">$' . number_format($subtotalItem, 2) . '</div>
                </div>';
        }

        $html .= '
            </div>

            <div class="totales">
                <div class="total-linea">
                    <span>Subtotal:</span>
                    <span>$' . number_format($datos['subtotal'], 2) . '</span>
                </div>';

        if ($datos['descuento'] > 0) {
            $html .= '
                <div class="total-linea">
                    <span>Descuento:</span>
                    <span>-$' . number_format($datos['descuento'], 2) . '</span>
                </div>';
        }

        $html .= '
                <div class="total-linea total-final">
                    <span>TOTAL:</span>
                    <span>$' . number_format($datos['totalFinal'], 2) . '</span>
                </div>
            </div>

            <div class="pago">
                <div class="pago-metodo">MÉTODO DE PAGO: ' . strtoupper($datos['metodoPago']) . '</div>';

        if ($datos['metodoPago'] === 'efectivo') {
            $html .= '
                <div class="total-linea">
                    <span>Monto recibido:</span>
                    <span>$' . number_format($datos['montoRecibido'] ?? 0, 2) . '</span>
                </div>';

            if (($datos['vuelto'] ?? 0) > 0) {
                $html .= '
                <div class="total-linea" style="font-weight: bold;">
                    <span>VUELTO:</span>
                    <span>$' . number_format($datos['vuelto'], 2) . '</span>
                </div>';
            } else {
                $html .= '
                <div style="text-align: center; font-style: italic; margin: 4px 0; font-size: 9px;">
                    Pago exacto - Sin vuelto
                </div>';
            }
        }

        if (!empty($datos['observaciones'])) {
            $html .= '
                <div class="observaciones">
                    <strong>Observaciones:</strong><br>
                    ' . htmlspecialchars($datos['observaciones']) . '
                </div>';
        }

        $html .= '
            </div>

            <div class="footer">
                <p>¡Gracias por su compra!</p>
                <p>Conserve este ticket</p>
                <p>Ticket #' . time() . '</p>
            </div>

            <div class="no-print" style="margin-top: 20px; text-align: center; border-top: 1px solid #ccc; padding-top: 10px;">
                <button onclick="window.print()" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                    🖨️ Imprimir
                </button>
                <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                    ✖️ Cerrar
                </button>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function generarPDFBasico($html, $datos)
    {
        return $this->generarConPDFTicket($datos);
    }

    private function generarConPDFTicket($datos)
    {
        // Usar la nueva clase PDFTicket
        $ticket = new PDFTicket();
        $pdf = $ticket->generar($datos);

        return $pdf->stream('ticket-' . ($datos['numero_ticket'] ?? time()) . '.pdf');
    }

    /**
     * Generar ticket general para todos los tipos de cobro
     */
    public function generarTicketGeneral(Request $request)
    {
        try {
            // Obtener datos del ticket desde el formulario
            $datosTicket = json_decode($request->input('datos_ticket'), true);

            if (!$datosTicket) {
                throw new \Exception('No se recibieron datos del ticket');
            }

            // Preparar datos para el ticket (reutilizar el formato existente)
            $items = $datosTicket['carrito'] ?? $datosTicket['items'] ?? [];

            $datos = [
                'numero_ticket' => $datosTicket['numero_ticket'] ?? 'BOX-' . Carbon::now()->format('Ymd-His'),
                'fecha' => Carbon::now(),
                'punto_venta' => 'BOX Cooperadora',
                'cajero' => Auth::user()->name ?? 'Sistema',
                'cliente' => $datosTicket['cliente'] ?? 'Cliente General',
                'metodo_pago' => ucfirst($datosTicket['metodo_pago'] ?? 'No especificado'),
                'carrito' => $items, // PDFTicket espera 'carrito'
                'items' => $items,   // Mantener compatibilidad
                'subtotal' => $datosTicket['subtotal'] ?? 0,
                'descuentos' => $datosTicket['descuento'] ?? 0,
                'descuento' => $datosTicket['descuento'] ?? 0, // Compatibilidad
                'total' => $datosTicket['total'] ?? 0,
                'tipo_modulo' => $datosTicket['tipo_modulo'] ?? 'general',
                'detalles_pago' => $datosTicket['detalles_pago'] ?? []
            ];

            // Reutilizar el método existente para generar PDF
            return $this->generarConPDFTicket($datos);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Procesar venta (para futuro uso con base de datos)
     */
    public function procesarVenta(Request $request)
    {
        try {
            // Aquí se procesaría la venta en la base de datos
            $datosVenta = $request->all();

            // Por ahora solo retornamos éxito
            return response()->json([
                'success' => true,
                'message' => 'Venta procesada correctamente',
                'numero_venta' => 'BOX-' . date('Ymd-His')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
