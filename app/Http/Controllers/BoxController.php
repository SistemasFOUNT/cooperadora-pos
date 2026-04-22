<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Sale;
use App\Services\PDFTicket;
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
                    ->where('type', 'venta_producto')
                    ->sum('total'),
                'cuotas_tecnicaturas' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('type', 'cuota_tecnicatura')
                    ->sum('total'),
                'bonos_grado' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('type', 'bono_grado')
                    ->sum('total'),
                'prestaciones_clinicas' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('type', 'prestacion_clinica')
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
            'productos_activos' => Product::where('is_active', true)
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
        return DB::table('items_venta')
                ->join('ventas', 'items_venta.sale_id', '=', 'ventas.id')
                ->join('productos', 'items_venta.product_id', '=', 'productos.id')
                ->where('ventas.punto_venta_id', $this->puntoVenta->id)
                ->whereMonth('ventas.created_at', Carbon::now()->month)
                ->selectRaw('productos.name, SUM(items_venta.quantity) as cantidad_vendida')
                ->groupBy('productos.id', 'productos.name')
                ->orderByDesc('cantidad_vendida')
                ->limit(10)
                ->get();
    }

    private function getCajerosPerformance()
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
                  ->whereMonth('created_at', Carbon::now()->month)
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

    public function reportesDiario()
    {
        return view('box.reportes.diario', [
            'title' => 'Reporte Diario'
        ]);
    }

    public function reportesMovimientos()
    {
        return view('box.reportes.movimientos', [
            'title' => 'Movimientos de Caja'
        ]);
    }

    public function reportesVentas()
    {
        return view('box.reportes.ventas', [
            'title' => 'Reportes de Ventas'
        ]);
    }

    public function reportesInventario()
    {
        return view('box.reportes.inventario', [
            'title' => 'Reporte de Inventario'
        ]);
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
        $ticket = new PDFTicket($datos);
        $ticket->generar();

        return response($ticket->obtenerPDF(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $ticket->obtenerNombreArchivo() . '"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }
}
