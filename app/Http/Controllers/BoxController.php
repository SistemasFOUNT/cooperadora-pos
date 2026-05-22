<?php

namespace App\Http\Controllers;

use App\Models\CuotaEstudiantil;
use App\Models\Student;
use App\Models\PuntoVenta;
use App\Models\Proveedor;
use App\Models\PagoProveedor;
use App\Models\Sale;
use App\Models\Product;
use App\Services\PDFTicket;
use App\Services\PDFFactura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

            if (!$this->puntoVenta) {
                abort(500, 'El punto de venta BOX no está configurado.');
            }

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
                'pagos_proveedores' => PagoProveedor::whereDate('fecha_pago', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('estado', 'registrado')
                    ->sum('monto'),
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
                'total_egresos' => PagoProveedor::whereBetween('fecha_pago', [$fechaDesde, $fechaHasta])
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('estado', 'registrado')
                    ->sum('monto'),
                'saldo_periodo' => Sale::whereBetween('created_at', [$fechaDesde, $fechaHasta])
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->sum('total')
                    - PagoProveedor::whereBetween('fecha_pago', [$fechaDesde, $fechaHasta])
                        ->where('punto_venta_id', $this->puntoVenta->id)
                        ->where('estado', 'registrado')
                        ->sum('monto'),
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
        // Cargar estudiantes de tecnicaturas para el dropdown
        $estudiantes = Student::whereIn('carrera', ['tecnicatura_protesis', 'tecnicatura_asistencia'])
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get()
            ->map(function ($est) {
                return [
                    'id' => $est->id,
                    'nombre' => "{$est->nombre} {$est->apellido}",
                    'carrera' => $est->carrera,
                    'dni' => $est->dni
                ];
            });

        return view('box.cobros.cuotas', [
            'title' => 'Cobros - Cuotas Estudiantiles',
            'estudiantesJson' => json_encode($estudiantes)
        ]);
    }

    /**
     * AJAX: Obtiene cuotas pendientes de un estudiante con cálculo de recargo
     * Retorna: cuotas vencidas + cuota del mes actual
     */
    public function buscarCuotasPorEstudiante(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id'
        ]);

        $estudiante = Student::findOrFail($request->estudiante_id);

        // Obtener cuotas: vencidas + mes actual (no futuras)
        $mesActual = now()->month;
        $cuotas = CuotaEstudiantil::where('estudiante_id', $estudiante->id)
            ->where('anio', 2026)
            ->whereIn('estado', ['pendiente', 'vencida'])
            ->where(function ($query) use ($mesActual) {
                // Solo vencidas O cuota del mes actual
                $query->where('fecha_vencimiento', '<', now()->startOfDay())
                      ->orWhere('numero_cuota', $mesActual);
            })
            ->orderBy('numero_cuota')
            ->get()
            ->map(function ($cuota) {
                // Calcular recargo si está vencida
                $recargo = $cuota->calcularRecargo();

                return [
                    'id' => $cuota->id,
                    'periodo' => $cuota->periodo,
                    'numero_cuota' => $cuota->numero_cuota,
                    'vencimiento' => $cuota->fecha_vencimiento->format('d/m/Y'),
                    'importe' => (float) $cuota->monto_cuota,
                    'recargo' => (float) $recargo,
                    'total' => (float) $cuota->monto_cuota + (float) $recargo,
                    'vencida' => $cuota->esta_vencida,
                    'dias_mora' => $cuota->dias_mora,
                ];
            });

        return response()->json([
            'estudiante' => [
                'id' => $estudiante->id,
                'nombre' => "{$estudiante->nombre} {$estudiante->apellido}",
                'carrera' => $estudiante->carrera,
                'dni' => $estudiante->dni
            ],
            'cuotas' => $cuotas
        ]);
    }

    /**
     * AJAX: Registra el pago de una o más cuotas
     */
    public function registrarPagoCuota(Request $request)
    {
        $request->validate([
            'cuota_ids' => 'required|array|min:1',
            'cuota_ids.*' => 'exists:cuotas_estudiantiles,id',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'tipo_comprobante' => 'nullable|in:ticket,factura_local,factura_fiscal',
            'numero_comprobante' => 'nullable|string|max:50',
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_documento' => 'nullable|string|max:50',
            'cliente_direccion' => 'nullable|string|max:255',
            'cliente_condicion_iva' => 'nullable|string|max:50',
            'mixto_metodo_1' => 'nullable|in:efectivo,tarjeta,transferencia',
            'mixto_monto_1' => 'nullable|numeric|min:0',
            'mixto_metodo_2' => 'nullable|in:efectivo,tarjeta,transferencia',
            'mixto_monto_2' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $cuotas = CuotaEstudiantil::whereIn('id', $request->cuota_ids)->get();
            $totalPagado = 0;
            $comprobantes = [];
            $tipoComprobante = $request->tipo_comprobante ?? 'factura_local';
            $subtotal = 0;
            $recargoTotal = 0;
            $recargosPorCuota = [];
            $totalesBrutosPorCuota = [];

            foreach ($cuotas as $cuota) {
                $recargo = $cuota->calcularRecargo();
                $recargosPorCuota[$cuota->id] = (float) $recargo;
                $subtotal += (float) $cuota->monto_cuota;
                $recargoTotal += (float) $recargo;
                $totalesBrutosPorCuota[$cuota->id] = (float) $cuota->monto_cuota + (float) $recargo;
                $comprobantes[] = $cuota->periodo;
            }

            $totalBruto = round((float) $subtotal + (float) $recargoTotal, 2);
            $totalSolicitado = round((float) ($request->input('total') ?? $totalBruto), 2);

            if ($totalSolicitado > $totalBruto + 0.01) {
                throw new \InvalidArgumentException('El total cobrado no puede ser mayor al total calculado.');
            }

            $descuentoTotal = round(max(0, $totalBruto - $totalSolicitado), 2);
            $totalPagado = round($totalBruto - $descuentoTotal, 2);

            $descuentosPorCuota = [];
            if ($descuentoTotal > 0 && $totalBruto > 0) {
                $acumuladoDescuento = 0.0;
                $cantidadCuotas = $cuotas->count();

                foreach ($cuotas->values() as $index => $cuota) {
                    $esUltima = $index === ($cantidadCuotas - 1);
                    if ($esUltima) {
                        $descuentoCuota = round($descuentoTotal - $acumuladoDescuento, 2);
                    } else {
                        $proporcion = ($totalesBrutosPorCuota[$cuota->id] ?? 0) / $totalBruto;
                        $descuentoCuota = round($descuentoTotal * $proporcion, 2);
                        $acumuladoDescuento += $descuentoCuota;
                    }

                    $maxDescuentoCuota = (float) ($totalesBrutosPorCuota[$cuota->id] ?? 0);
                    $descuentosPorCuota[$cuota->id] = max(0, min($descuentoCuota, $maxDescuentoCuota));
                }
            }

            foreach ($cuotas as $cuota) {
                $cuota->registrarPago([
                    'recargo' => (float) ($recargosPorCuota[$cuota->id] ?? 0),
                    'descuento' => (float) ($descuentosPorCuota[$cuota->id] ?? 0),
                    'metodo_pago' => $request->metodo_pago,
                    'numero_comprobante' => $request->numero_comprobante,
                    'usuario_cobro_id' => auth()->id(),
                ]);
            }

            $vuelto = 0;
            $detallesPago = [
                'tipo_comprobante' => $tipoComprobante,
            ];

            if ($request->metodo_pago === 'efectivo') {
                $montoRecibido = (float) ($request->input('monto_recibido') ?? 0);
                $vuelto = max(0, $montoRecibido - $totalPagado);
                $detallesPago['vuelto'] = $vuelto;
            } elseif ($request->metodo_pago === 'mixto') {
                $metodo1 = $request->input('mixto_metodo_1');
                $metodo2 = $request->input('mixto_metodo_2');
                $monto1 = (float) ($request->input('mixto_monto_1') ?? 0);
                $monto2 = (float) ($request->input('mixto_monto_2') ?? 0);

                if (!$metodo1 || !$metodo2) {
                    throw new \InvalidArgumentException('Debe seleccionar dos medios para el pago mixto.');
                }

                if ($metodo1 === $metodo2) {
                    throw new \InvalidArgumentException('En pago mixto los medios deben ser diferentes.');
                }

                if ($monto1 <= 0 || $monto2 <= 0) {
                    throw new \InvalidArgumentException('En pago mixto ambos montos deben ser mayores a 0.');
                }

                if (abs(($monto1 + $monto2) - $totalPagado) > 0.01) {
                    throw new \InvalidArgumentException('La suma de los montos mixtos debe coincidir con el total.');
                }

                $detallesPago['componentes'] = [
                    ['metodo' => $metodo1, 'monto' => $monto1],
                    ['metodo' => $metodo2, 'monto' => $monto2],
                ];
            }

            $estudianteId = (int) ($cuotas->first()->estudiante_id ?? 0);
            $estudiante = $estudianteId > 0 ? Student::find($estudianteId) : null;

            $clienteNombre = trim((string) ($request->cliente_nombre ?: (($estudiante?->nombre ?? '') . ' ' . ($estudiante?->apellido ?? ''))));
            if ($clienteNombre === '') {
                $clienteNombre = 'CONSUMIDOR FINAL';
            }

            $clienteDocumento = trim((string) ($request->cliente_documento ?: ($estudiante->dni ?? '00000000')));
            if ($clienteDocumento === '') {
                $clienteDocumento = '00000000';
            }

            $clienteDireccion = trim((string) ($request->cliente_direccion ?: ($estudiante->direccion ?? 'TUCUMAN')));
            if ($clienteDireccion === '') {
                $clienteDireccion = 'TUCUMAN';
            }

            $codigoMetodoPago = match ($request->metodo_pago) {
                'efectivo' => 'EFE',
                'tarjeta' => 'TDC',
                'transferencia' => 'TRA',
                'mixto' => match ($detallesPago['componentes'][0]['metodo'] ?? 'efectivo') {
                    'tarjeta' => 'TDC',
                    'transferencia' => 'TRA',
                    default => 'EFE',
                },
                default => 'EFE',
            };

            $paymentMethodId = \App\Models\PaymentMethod::where('code', $codigoMetodoPago)->value('id')
                ?? \App\Models\PaymentMethod::active()->value('id');

            if (!$paymentMethodId) {
                throw new \RuntimeException('No hay métodos de pago activos configurados.');
            }

            $saleNumber = 'CUO-BOX-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(substr(md5((string) microtime(true)), 0, 6));

            $detalleComprobante = $cuotas->flatMap(function ($cuota) use ($recargosPorCuota) {
                $recargo = (float) ($recargosPorCuota[$cuota->id] ?? 0);

                $items = [[
                    'codigo' => 'CUOTA',
                    'descripcion' => 'Cuota ' . $cuota->periodo,
                    'cantidad' => 1,
                    'precio_unitario' => (float) $cuota->monto_cuota,
                    'total' => (float) $cuota->monto_cuota,
                ]];

                if ($recargo > 0) {
                    $items[] = [
                        'codigo' => 'RECARGO',
                        'descripcion' => 'Recargo por mora cuota ' . $cuota->periodo,
                        'cantidad' => 1,
                        'precio_unitario' => $recargo,
                        'total' => $recargo,
                    ];
                }

                return $items;
            })->values()->toArray();

            $venta = Sale::create([
                'sale_number' => $saleNumber,
                'punto_venta_id' => $this->puntoVenta->id,
                'usuario_id' => auth()->id(),
                'student_id' => $estudianteId > 0 ? $estudianteId : null,
                'payment_method_id' => $paymentMethodId,
                'fecha_venta' => now(),
                'type' => 'student_fee',
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => $descuentoTotal,
                'total' => $totalPagado,
                'status' => 'completed',
                'notes' => $request->observaciones,
                'additional_data' => [
                    'origen' => 'cuotas_estudiantiles',
                    'periodos' => $comprobantes,
                    'metodo_pago' => $request->metodo_pago,
                    'descuento' => $descuentoTotal,
                    'detalles_pago' => $detallesPago,
                    'detalle_comprobante' => $detalleComprobante,
                ],
            ]);

            $ultimoNumero = \App\Models\Factura::where('punto_venta_id', $this->puntoVenta->id)
                ->where('tipo', 'local')
                ->orderByDesc('numero')
                ->lockForUpdate()
                ->value('numero') ?? 0;

            $nuevoNumero = $ultimoNumero + 1;
            $mapTipoComprobante = [
                'factura_local' => 'B',
                'factura_fiscal' => 'B',
                'ticket' => null,
            ];

            $factura = \App\Models\Factura::create([
                'sale_id' => $venta->id,
                'punto_venta_id' => $this->puntoVenta->id,
                'tipo' => 'local',
                'tipo_comprobante' => $mapTipoComprobante[$tipoComprobante] ?? null,
                'numero' => $nuevoNumero,
                'numero_completo' => sprintf('%08d', $nuevoNumero),
                'fecha_emision' => now(),
                'datos_cliente' => [
                    'nombre' => $clienteNombre,
                    'documento' => $clienteDocumento,
                    'direccion' => $clienteDireccion,
                    'condicion_iva' => $request->cliente_condicion_iva ?: 'consumidor_final',
                ],
                'cuit_cliente' => $clienteDocumento,
                'razon_social_cliente' => $clienteNombre,
                'subtotal' => $subtotal,
                'iva' => 0,
                'total' => $totalPagado,
                'estado' => 'emitida',
                'observaciones' => $request->observaciones,
                'created_by' => auth()->id(),
            ]);

            $snapshotComprobante = [
                'numero_comprobante' => $factura->numero_completo,
                'fecha_emision' => $factura->fecha_emision?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                'tipo_comprobante' => $tipoComprobante,
                'metodo_pago' => $request->metodo_pago,
                'detalles_pago' => $detallesPago,
                'cliente' => [
                    'nombre' => $clienteNombre,
                    'documento' => $clienteDocumento,
                    'direccion' => $clienteDireccion,
                    'condicion_iva' => $request->cliente_condicion_iva ?: 'consumidor_final',
                ],
                'subtotal' => (float) $subtotal,
                'recargo_total' => (float) $recargoTotal,
                'descuento' => (float) $descuentoTotal,
                'total' => (float) $totalPagado,
                'items' => $detalleComprobante,
            ];

            $additionalData = is_array($venta->additional_data) ? $venta->additional_data : [];
            $additionalData['comprobante_snapshot'] = $snapshotComprobante;
            $additionalData['detalle_comprobante'] = $detalleComprobante;
            $venta->additional_data = $additionalData;
            $venta->save();

            DB::commit();

            $numeroTicket = 'CUOTA-' . now()->format('YmdHis') . '-' . auth()->id();
            $datosTicket = [
                'numero_ticket' => $numeroTicket,
                'carrito' => $cuotas->map(function ($cuota) use ($recargosPorCuota) {
                    $recargo = (float) ($recargosPorCuota[$cuota->id] ?? 0);

                    return [
                        'nombre' => $cuota->periodo,
                        'cantidad' => 1,
                        'precio' => (float) $cuota->monto_cuota + (float) $recargo,
                    ];
                })->toArray(),
                'subtotal' => $subtotal,
                'descuento' => $descuentoTotal,
                'total' => $totalPagado,
                'metodo_pago' => $request->metodo_pago,
                'detalles_pago' => $detallesPago,
            ];

            if ($tipoComprobante === 'factura_local' || $tipoComprobante === 'factura_fiscal') {
                $datosTicket['cliente'] = [
                    'nombre' => $request->cliente_nombre,
                    'documento' => $request->cliente_documento,
                    'direccion' => $request->cliente_direccion,
                    'condicion_iva' => $request->cliente_condicion_iva,
                ];
            }

            $nombreArchivo = 'comprobante-cuotas-' . now()->format('YmdHis') . '.pdf';
            $rutaRelativa = 'comprobantes/cuotas/' . $nombreArchivo;

            if ($tipoComprobante === 'factura_local' || $tipoComprobante === 'factura_fiscal') {
                $pdfFactura = new PDFFactura();
                $respuestaPdf = $pdfFactura->generarSimple([
                    'numero_factura' => $factura->numero_completo,
                    'cliente_nombre' => $clienteNombre,
                    'cliente_documento' => $clienteDocumento,
                    'cliente_direccion' => $clienteDireccion,
                    'cliente_condicion_iva' => $request->cliente_condicion_iva ?: 'consumidor_final',
                    'metodo_pago' => $request->metodo_pago,
                    'subtotal' => $subtotal,
                    'descuento' => $descuentoTotal,
                    'total' => $totalPagado,
                    'observaciones' => $request->observaciones,
                    'productos' => $cuotas->flatMap(function ($cuota) use ($recargosPorCuota) {
                        $recargo = (float) ($recargosPorCuota[$cuota->id] ?? 0);

                        $items = [[
                            'nombre' => 'Cuota ' . $cuota->periodo,
                            'cantidad' => 1,
                            'precio' => (float) $cuota->monto_cuota,
                            'total' => (float) $cuota->monto_cuota,
                        ]];

                        if ((float) $recargo > 0) {
                            $items[] = [
                                'nombre' => 'Recargo por mora cuota ' . $cuota->periodo,
                                'cantidad' => 1,
                                'precio' => (float) $recargo,
                                'total' => (float) $recargo,
                            ];
                        }

                        return $items;
                    })->toArray(),
                ]);

                Storage::disk('public')->put($rutaRelativa, $respuestaPdf->getContent());
            } else {
                $pdfTicket = new PDFTicket();
                $pdf = $pdfTicket->generar($datosTicket);
                Storage::disk('public')->put($rutaRelativa, $pdf->Output('S'));
            }

            $urlPdf = Storage::url($rutaRelativa);

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado correctamente',
                'datos' => [
                    'cuotas_pagadas' => count($cuotas),
                    'total_pagado' => $totalPagado,
                    'periodos' => $comprobantes,
                    'factura_id' => $factura->id,
                    'sale_id' => $venta->id,
                    'numero_comprobante_generado' => $factura->numero_completo,
                    'tipo_comprobante' => $tipoComprobante,
                    'pdf_url' => $urlPdf,
                    'vuelto' => $vuelto,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar pago: ' . $e->getMessage()
            ], 500);
        }
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
        $proveedores = Proveedor::where('punto_venta_id', $this->puntoVenta->id)
            ->where('activo', true)
            ->orderBy('razon_social')
            ->get();

        $pagos = PagoProveedor::with(['proveedor', 'usuarioRegistro'])
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->paginate(15);

        return view('box.pagos.proveedores', [
            'title' => 'Pagos a Proveedores',
            'proveedores' => $proveedores,
            'pagos' => $pagos,
        ]);
    }

    public function registrarProveedor(Request $request)
    {
        $request->validate([
            'razon_social' => 'required|string|max:255',
            'cuit' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
        ]);

        Proveedor::create([
            'punto_venta_id' => $this->puntoVenta->id,
            'razon_social' => trim((string) $request->input('razon_social')),
            'cuit' => $request->input('cuit'),
            'telefono' => $request->input('telefono'),
            'email' => $request->input('email'),
            'direccion' => $request->input('direccion'),
            'activo' => true,
        ]);

        return back()->with('success', 'Proveedor registrado correctamente.');
    }

    public function registrarPagoProveedor(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|integer|exists:proveedores,id',
            'tipo_comprobante' => 'required|in:factura,recibo,boleta,remito,otro',
            'numero_comprobante' => 'required|string|max:50',
            'fecha_comprobante' => 'nullable|date',
            'fecha_pago' => 'required|date',
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:1000',
            'comprobante_archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $proveedor = Proveedor::where('id', (int) $request->input('proveedor_id'))
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->where('activo', true)
            ->firstOrFail();

        $rutaComprobante = null;
        if ($request->hasFile('comprobante_archivo')) {
            $rutaComprobante = $request->file('comprobante_archivo')
                ->store('comprobantes/proveedores', 'public');
        }

        PagoProveedor::create([
            'punto_venta_id' => $this->puntoVenta->id,
            'proveedor_id' => $proveedor->id,
            'user_id' => Auth::id(),
            'fecha_pago' => Carbon::parse($request->input('fecha_pago'))->startOfDay(),
            'tipo_comprobante' => $request->input('tipo_comprobante'),
            'numero_comprobante' => trim((string) $request->input('numero_comprobante')),
            'fecha_comprobante' => $request->filled('fecha_comprobante')
                ? Carbon::parse($request->input('fecha_comprobante'))->toDateString()
                : null,
            'concepto' => trim((string) $request->input('concepto')),
            'monto' => (float) $request->input('monto'),
            'observaciones' => $request->input('observaciones'),
            'comprobante_path' => $rutaComprobante,
            'estado' => 'registrado',
        ]);

        return back()->with('success', 'Pago a proveedor registrado correctamente.');
    }

    public function descargarComprobantePagoProveedor(PagoProveedor $pago)
    {
        if ($pago->punto_venta_id !== $this->puntoVenta->id) {
            abort(403, 'No tienes acceso a este comprobante.');
        }

        if (!$pago->comprobante_path || !Storage::disk('public')->exists($pago->comprobante_path)) {
            return back()->withErrors(['error' => 'El comprobante no está disponible.']);
        }

        $rutaAbsoluta = storage_path('app/public/' . $pago->comprobante_path);

        if (!file_exists($rutaAbsoluta)) {
            return back()->withErrors(['error' => 'No se encontró el archivo de comprobante.']);
        }

        $extension = pathinfo($pago->comprobante_path, PATHINFO_EXTENSION);
        $nombre = 'comprobante_' . $pago->id . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $pago->proveedor->razon_social) . '.' . $extension;

        return response()->download($rutaAbsoluta, $nombre);
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
     * Endpoint unificado de facturación para compatibilidad con rutas actuales.
     */
    public function generarFactura(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'tipo' => 'nullable|in:local,arca',
        ]);

        $sale = Sale::findOrFail((int) $request->input('venta_id'));

        if ($sale->punto_venta_id != $this->puntoVenta->id) {
            abort(403, 'No tienes acceso a esta venta.');
        }

        // Normalizar nombres de campos para los métodos existentes.
        $request->merge([
            'cliente_domicilio' => $request->input('cliente_domicilio', $request->input('cliente_direccion')),
            'cliente_condicion_iva' => $request->input('cliente_condicion_iva', $request->input('condicion_iva')),
        ]);

        $tipo = (string) $request->input('tipo', 'local');
        $respuesta = $tipo === 'arca'
            ? $this->generarFacturaARCA($request, $sale)
            : $this->generarFacturaLocal($request, $sale);

        $payload = $respuesta->getData(true);
        $status = $respuesta->getStatusCode();

        if (($payload['success'] ?? false) && isset($payload['factura_id'])) {
            $payload['factura_url'] = route('box.facturas.ver', $payload['factura_id']);
        }

        if (!isset($payload['message'])) {
            $payload['message'] = $payload['mensaje']
                ?? $payload['error']
                ?? 'Operación completada.';
        }

        return response()->json($payload, $status);
    }

    /**
     * Compatibilidad: procesa venta con el flujo unificado de pago/factura.
     */
    public function procesarVenta(Request $request)
    {
        $carrito = $request->input('carrito', []);

        if (is_string($carrito)) {
            $carrito = json_decode($carrito, true) ?: [];
        }

        if (!is_array($carrito) || count($carrito) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Debe enviar al menos un item para procesar la venta.'
            ], 422);
        }

        $datosCliente = $request->input('datosCliente', [
            'nombre' => $request->input('cliente_nombre', 'Cliente Genérico'),
            'documento' => $request->input('cliente_documento', '00000000'),
            'direccion' => $request->input('cliente_direccion', ''),
            'condicionIva' => $request->input('cliente_condicion_iva', 'consumidor_final'),
        ]);

        if (is_array($datosCliente)) {
            $datosCliente = json_encode($datosCliente);
        }

        $payload = [
            'carrito' => json_encode($carrito),
            'datosCliente' => $datosCliente,
            'metodoPago' => $request->input('metodoPago', $request->input('metodo_pago', 'efectivo')),
            'tipoComprobante' => $request->input('tipoComprobante', $request->input('tipo_comprobante', 'ticket')),
            'subtotal' => (float) $request->input('subtotal', 0),
            'descuento' => (float) $request->input('descuento', 0),
            'totalFinal' => (float) $request->input('totalFinal', $request->input('total', 0)),
            'observaciones' => $request->input('observaciones', ''),
        ];

        $requestAdaptado = new Request($payload);
        $requestAdaptado->setUserResolver(fn () => $request->user());

        return $this->procesarPagoConFactura($requestAdaptado);
    }

    /**
     * Compatibilidad: alias de generación de ticket PDF para cobros.
     */
    public function generarTicketGeneral(Request $request)
    {
        return $this->generarTicketPDF($request);
    }

    /**
     * Genera ticket PDF en formato blob para frontend.
     */
    public function generarTicketPDF(Request $request)
    {
        $carrito = $request->input('carrito', []);

        if (is_string($carrito)) {
            $carrito = json_decode($carrito, true) ?: [];
        }

        if (!is_array($carrito) || count($carrito) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No hay items en el carrito para generar el ticket.'
            ], 422);
        }

        $items = collect($carrito)->map(function ($item) {
            return [
                'nombre' => (string) ($item['nombre'] ?? $item['name'] ?? $item['descripcion'] ?? 'Item'),
                'cantidad' => (int) ($item['cantidad'] ?? $item['quantity'] ?? 1),
                'precio' => (float) ($item['precio'] ?? $item['price'] ?? $item['precio_unitario'] ?? 0),
            ];
        })->toArray();

        $datosTicket = [
            'numero_ticket' => 'BOX-' . now()->format('YmdHis') . '-' . auth()->id(),
            'carrito' => $items,
            'subtotal' => (float) $request->input('subtotal', 0),
            'descuento' => (float) $request->input('descuento', 0),
            'total' => (float) $request->input('totalFinal', $request->input('total', 0)),
            'metodo_pago' => (string) $request->input('metodoPago', $request->input('metodo_pago', 'efectivo')),
            'tipo_comprobante' => (string) $request->input('tipoComprobante', $request->input('tipo_comprobante', 'ticket')),
            'detalles_pago' => [
                'vuelto' => (float) $request->input('vuelto', 0),
            ],
        ];

        try {
            $pdf = (new PDFTicket())->generar($datosTicket);

            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="ticket-box.pdf"');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el ticket PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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

                $detalleComprobante = collect($venta->items)->map(function ($item) {
                    return [
                        'codigo' => $item->product_code ?? 'N/A',
                        'descripcion' => $item->product_name ?? 'Producto',
                        'cantidad' => (int) ($item->quantity ?? 1),
                        'precio_unitario' => (float) ($item->unit_price ?? 0),
                        'total' => (float) ($item->total ?? 0),
                    ];
                })->values()->toArray();

                $snapshotComprobante = [
                    'numero_comprobante' => $factura->numero_completo,
                    'fecha_emision' => $factura->fecha_emision?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'tipo_comprobante' => $datos['tipo_comprobante'] ?? (empty($factura->tipo_comprobante) ? 'ticket' : 'factura_local'),
                    'metodo_pago' => $datos['metodo_pago'] ?? 'efectivo',
                    'detalles_pago' => is_array($venta->additional_data['detalles_pago'] ?? null) ? $venta->additional_data['detalles_pago'] : [],
                    'cliente' => [
                        'nombre' => $datos['cliente_nombre'] ?? 'CONSUMIDOR FINAL',
                        'documento' => $datos['cliente_documento'] ?? '00000000',
                        'direccion' => $datos['cliente_direccion'] ?? 'TUCUMAN',
                        'condicion_iva' => $datos['cliente_condicion_iva'] ?? 'consumidor_final',
                    ],
                    'subtotal' => (float) ($datos['subtotal'] ?? 0),
                    'recargo_total' => (float) max(0, ((float) ($datos['total'] ?? 0) + (float) ($datos['descuento'] ?? 0)) - (float) ($datos['subtotal'] ?? 0)),
                    'descuento' => (float) ($datos['descuento'] ?? 0),
                    'total' => (float) ($datos['total'] ?? 0),
                    'items' => $detalleComprobante,
                ];

                $additionalData = is_array($venta->additional_data) ? $venta->additional_data : [];
                $additionalData['detalle_comprobante'] = $detalleComprobante;
                $additionalData['comprobante_snapshot'] = $snapshotComprobante;
                $venta->additional_data = $additionalData;
                $venta->save();

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
        $factura->load(['sale', 'sale.items', 'sale.items.product', 'sale.user', 'sale.paymentMethod']);

        // Verificar que la factura pertenece al punto de venta correcto
        if ($factura->sale->punto_venta_id != $this->puntoVenta->id) {
            abort(403, 'No tienes acceso a esta factura.');
        }

        $formato = $request->input('formato', 'html'); // html, pdf

        if ($formato === 'pdf') {
            return $this->generarPDFFactura($factura);
        }

        $detalleComprobante = collect();
        $additionalData = is_array($factura->sale->additional_data ?? null) ? $factura->sale->additional_data : [];
        $snapshot = is_array($additionalData['comprobante_snapshot'] ?? null) ? $additionalData['comprobante_snapshot'] : null;

        if (is_array($snapshot['items'] ?? null) && count($snapshot['items']) > 0) {
            $detalleComprobante = collect($snapshot['items']);
        } elseif ($factura->sale && $factura->sale->items->count() > 0) {
            $detalleComprobante = $factura->sale->items->map(function ($item) {
                return [
                    'codigo' => $item->product_code ?? ($item->product->code ?? 'N/A'),
                    'descripcion' => $item->product_name ?? ($item->product->name ?? 'Producto'),
                    'cantidad' => (int) ($item->quantity ?? 1),
                    'precio_unitario' => (float) ($item->unit_price ?? 0),
                    'total' => (float) ($item->total ?? 0),
                ];
            });
        } elseif (is_array($additionalData['detalle_comprobante'] ?? null)) {
            $detalleComprobante = collect($additionalData['detalle_comprobante']);
        } else {
            $detalleReconstruido = $this->reconstruirDetalleCuotasDesdePeriodos($factura, $additionalData);

            if (!empty($detalleReconstruido)) {
                $detalleComprobante = collect($detalleReconstruido);
            } else {
                $subtotal = (float) ($factura->subtotal ?? 0);
                $recargo = max(0, (float) $factura->total - $subtotal);

                $fallback = [[
                    'codigo' => 'CUOTA',
                    'descripcion' => 'Cuotas estudiantiles',
                    'cantidad' => 1,
                    'precio_unitario' => $subtotal,
                    'total' => $subtotal,
                ]];

                if ($recargo > 0) {
                    $fallback[] = [
                        'codigo' => 'RECARGO',
                        'descripcion' => 'Recargo por mora',
                        'cantidad' => 1,
                        'precio_unitario' => $recargo,
                        'total' => $recargo,
                    ];
                }

                $detalleComprobante = collect($fallback);
            }
        }

        return view('box.facturas.ver', compact('factura', 'detalleComprobante'));
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

            $factura->loadMissing(['sale', 'sale.items', 'sale.items.product', 'sale.paymentMethod']);
            $additionalData = is_array($factura->sale->additional_data ?? null) ? $factura->sale->additional_data : [];
            $snapshot = is_array($additionalData['comprobante_snapshot'] ?? null) ? $additionalData['comprobante_snapshot'] : null;

            if ($factura->tipo === 'local' && is_array($snapshot['items'] ?? null) && count($snapshot['items']) > 0) {
                $pdfService = new \App\Services\PDFFactura();

                $productos = collect($snapshot['items'])->map(function ($item) {
                    $cantidad = (float) ($item['cantidad'] ?? 1);
                    $totalItem = (float) ($item['total'] ?? 0);
                    $precioUnitario = (float) ($item['precio_unitario'] ?? ($cantidad > 0 ? ($totalItem / $cantidad) : 0));

                    return [
                        'codigo' => (string) ($item['codigo'] ?? 'N/A'),
                        'nombre' => (string) ($item['descripcion'] ?? 'Item'),
                        'cantidad' => $cantidad,
                        'precio' => $precioUnitario,
                        'total' => $totalItem,
                    ];
                })->toArray();

                return $pdfService->generarSimple([
                    'numero_factura' => $snapshot['numero_comprobante'] ?? ($factura->numero_completo ?? sprintf('%08d', $factura->numero)),
                    'cliente_nombre' => $snapshot['cliente']['nombre'] ?? ($factura->datos_cliente['nombre'] ?? 'CONSUMIDOR FINAL'),
                    'cliente_documento' => $snapshot['cliente']['documento'] ?? ($factura->datos_cliente['documento'] ?? '00000000'),
                    'cliente_direccion' => $snapshot['cliente']['direccion'] ?? ($factura->datos_cliente['direccion'] ?? 'TUCUMAN'),
                    'cliente_condicion_iva' => $snapshot['cliente']['condicion_iva'] ?? ($factura->datos_cliente['condicion_iva'] ?? 'consumidor_final'),
                    'metodo_pago' => $snapshot['metodo_pago'] ?? 'efectivo',
                    'subtotal' => (float) ($snapshot['subtotal'] ?? $factura->subtotal ?? 0),
                    'descuento' => (float) ($snapshot['descuento'] ?? 0),
                    'total' => (float) ($snapshot['total'] ?? $factura->total),
                    'observaciones' => $factura->observaciones,
                    'productos' => $productos,
                ]);
            }

            if ($factura->tipo === 'local' && !empty($factura->tipo_comprobante)) {
                $pdfService = new \App\Services\PDFFactura();

                $productos = $factura->sale->items->map(function ($item) {
                    return [
                        'codigo' => (string) ($item->product_code ?? ($item->product->code ?? 'N/A')),
                        'nombre' => (string) ($item->product_name ?? ($item->product->name ?? 'Item')),
                        'cantidad' => (float) ($item->quantity ?? 1),
                        'precio' => (float) ($item->unit_price ?? 0),
                        'total' => (float) ($item->total ?? 0),
                    ];
                })->toArray();

                if (empty($productos) && is_array($additionalData['detalle_comprobante'] ?? null)) {
                    $productos = collect($additionalData['detalle_comprobante'])->map(function ($item) {
                        $cantidad = (float) ($item['cantidad'] ?? 1);
                        $totalItem = (float) ($item['total'] ?? 0);
                        $precioUnitario = (float) ($item['precio_unitario'] ?? ($cantidad > 0 ? ($totalItem / $cantidad) : 0));

                        return [
                            'codigo' => (string) ($item['codigo'] ?? 'N/A'),
                            'nombre' => (string) ($item['descripcion'] ?? 'Item'),
                            'cantidad' => $cantidad,
                            'precio' => $precioUnitario,
                            'total' => $totalItem,
                        ];
                    })->toArray();
                }

                if (empty($productos)) {
                    $detalleReconstruido = $this->reconstruirDetalleCuotasDesdePeriodos($factura, $additionalData);
                    if (!empty($detalleReconstruido)) {
                        $productos = collect($detalleReconstruido)->map(function ($item) {
                            $cantidad = (float) ($item['cantidad'] ?? 1);
                            $totalItem = (float) ($item['total'] ?? 0);
                            $precioUnitario = (float) ($item['precio_unitario'] ?? ($cantidad > 0 ? ($totalItem / $cantidad) : 0));

                            return [
                                'codigo' => (string) ($item['codigo'] ?? 'N/A'),
                                'nombre' => (string) ($item['descripcion'] ?? 'Item'),
                                'cantidad' => $cantidad,
                                'precio' => $precioUnitario,
                                'total' => $totalItem,
                            ];
                        })->toArray();
                    }
                }

                if (!empty($productos)) {
                    return $pdfService->generarSimple([
                        'numero_factura' => $factura->numero_completo ?? sprintf('%08d', $factura->numero),
                        'cliente_nombre' => $factura->datos_cliente['nombre'] ?? 'CONSUMIDOR FINAL',
                        'cliente_documento' => $factura->datos_cliente['documento'] ?? '00000000',
                        'cliente_direccion' => $factura->datos_cliente['direccion'] ?? 'TUCUMAN',
                        'cliente_condicion_iva' => $factura->datos_cliente['condicion_iva'] ?? 'consumidor_final',
                        'metodo_pago' => (string) ($additionalData['metodo_pago'] ?? 'efectivo'),
                        'subtotal' => (float) ($factura->subtotal ?? 0),
                        'descuento' => (float) ($additionalData['descuento'] ?? 0),
                        'total' => (float) $factura->total,
                        'observaciones' => $factura->observaciones,
                        'productos' => $productos,
                    ]);
                }
            }

            if ($factura->tipo === 'local' && empty($factura->tipo_comprobante)) {
                $codigoMetodo = strtoupper((string) ($factura->sale->paymentMethod->code ?? ''));
                $metodoPago = match ($codigoMetodo) {
                    'TDC' => 'tarjeta',
                    'TRA' => 'transferencia',
                    default => 'efectivo',
                };

                $datosCliente = is_array($factura->datos_cliente) ? $factura->datos_cliente : [];
                $itemsTicket = $factura->sale->items->map(function ($item) {
                    return [
                        'nombre' => $item->product_name ?? ($item->product->name ?? 'Item'),
                        'cantidad' => (int) ($item->quantity ?? 1),
                        'precio' => (float) ($item->unit_price ?? 0),
                    ];
                })->toArray();

                if (empty($itemsTicket) && is_array($additionalData['detalle_comprobante'] ?? null)) {
                    $itemsTicket = collect($additionalData['detalle_comprobante'])
                        ->map(function ($item) {
                            return [
                                'nombre' => (string) ($item['descripcion'] ?? 'Item'),
                                'cantidad' => (int) ($item['cantidad'] ?? 1),
                                'precio' => (float) ($item['precio_unitario'] ?? $item['total'] ?? 0),
                            ];
                        })->toArray();
                }

                if (empty($itemsTicket)) {
                    $itemsTicket[] = [
                        'nombre' => 'Cobro de cuota',
                        'cantidad' => 1,
                        'precio' => (float) $factura->total,
                    ];
                }

                $datosTicket = [
                    'numero_ticket' => $factura->numero_completo ?? ('TICKET-' . $factura->id),
                    'carrito' => $itemsTicket,
                    'subtotal' => (float) ($factura->subtotal ?? $factura->sale->subtotal ?? $factura->total),
                    'descuento' => (float) ($factura->sale->discount_amount ?? 0),
                    'total' => (float) $factura->total,
                    'metodo_pago' => $metodoPago,
                    'tipo_comprobante' => 'ticket',
                    'detalles_pago' => is_array($factura->sale->additional_data['detalles_pago'] ?? null)
                        ? $factura->sale->additional_data['detalles_pago']
                        : [],
                    'cliente' => [
                        'nombre' => $datosCliente['nombre'] ?? 'CONSUMIDOR FINAL',
                        'documento' => $datosCliente['documento'] ?? '',
                        'direccion' => $datosCliente['direccion'] ?? '',
                    ],
                ];

                $pdfTicket = new \App\Services\PDFTicket();
                $pdf = $pdfTicket->generar($datosTicket);

                return $pdf->stream('ticket-' . ($factura->numero_completo ?? $factura->id) . '.pdf');
            }

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

    private function reconstruirDetalleCuotasDesdePeriodos(\App\Models\Factura $factura, array $additionalData): array
    {
        $periodos = collect($additionalData['periodos'] ?? [])
            ->filter(fn ($periodo) => is_string($periodo) && trim($periodo) !== '')
            ->values();

        if ($periodos->isEmpty()) {
            return [];
        }

        $subtotal = (float) ($factura->subtotal ?? 0);
        $recargoTotal = max(0, (float) $factura->total - $subtotal);
        $cantidadPeriodos = $periodos->count();

        if ($cantidadPeriodos <= 0) {
            return [];
        }

        $cuotaBase = round($subtotal / $cantidadPeriodos, 2);
        $recargoBase = round($recargoTotal / $cantidadPeriodos, 2);
        $acumuladoCuotas = 0.0;
        $acumuladoRecargos = 0.0;
        $detalle = [];

        foreach ($periodos as $index => $periodo) {
            $esUltimo = ($index === $cantidadPeriodos - 1);

            $montoCuota = $esUltimo
                ? round($subtotal - $acumuladoCuotas, 2)
                : $cuotaBase;
            $acumuladoCuotas += $montoCuota;

            $montoRecargo = $esUltimo
                ? round($recargoTotal - $acumuladoRecargos, 2)
                : $recargoBase;
            $acumuladoRecargos += $montoRecargo;

            $detalle[] = [
                'codigo' => 'CUOTA',
                'descripcion' => 'Cuota de ' . $periodo,
                'cantidad' => 1,
                'precio_unitario' => $montoCuota,
                'total' => $montoCuota,
            ];

            if ($montoRecargo > 0) {
                $detalle[] = [
                    'codigo' => 'RECARGO',
                    'descripcion' => 'Recargo por mora cuota ' . $periodo,
                    'cantidad' => 1,
                    'precio_unitario' => $montoRecargo,
                    'total' => $montoRecargo,
                ];
            }
        }

        return $detalle;
    }
}
