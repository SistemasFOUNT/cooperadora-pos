<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PaymentMethod;
use App\Models\Student;
use App\Models\CareerFeeConfig;
use App\Models\PagoProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PostgradoController extends Controller
{
    private $puntoVenta;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('punto_venta');
        $this->middleware(function ($request, $next) {
            $this->puntoVenta = PuntoVenta::where('codigo', 'POSTGRADO')->first();
            return $next($request);
        });
    }

    /**
     * Dashboard principal de Postgrado
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Verificar que el usuario sea de Postgrado
        if (!$user->isAdmin() && $user->punto_venta_id != $this->puntoVenta->id) {
            abort(403, 'No tienes acceso a la Secretaría de Postgrado.');
        }

        $estadisticas = $this->getEstadisticas();

        return view('postgrado.dashboard', compact('estadisticas'));
    }

    /**
     * Supervisión administrativa del punto de venta Postgrado
     * Solo accesible para admin
     */
    public function adminSupervision()
    {
        $datos_supervision = [
            'estadisticas_academicas' => $this->getEstadisticas(),
            'ingresos_del_mes' => $this->getIngresosDelMes(),
            'estudiantes_activos' => $this->getEstudiantesActivos(),
            'honorarios_pendientes' => $this->getHonorariosPendientes(),
        ];

        return view('admin.supervision.postgrado', compact('datos_supervision'));
    }

    /**
     * Ingresos y Egresos detallados para admin
     */
    public function adminIngresosEgresos()
    {
        $fechaHoy = Carbon::today();
        $egresosQuery = PagoProveedor::whereDate('fecha_pago', $fechaHoy)
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->where('estado', 'registrado');

        $egresosHonorarios = (clone $egresosQuery)
            ->whereRaw('LOWER(concepto) LIKE ?', ['%honorario%'])
            ->sum('monto');

        $egresosGastos = (clone $egresosQuery)
            ->whereRaw('LOWER(concepto) LIKE ?', ['%gasto%'])
            ->sum('monto');

        $egresosTotales = (clone $egresosQuery)->sum('monto');

        $ingresos_egresos = [
            'ingresos_hoy' => [
                'cuotas_postgrado' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('type', 'student_fee')
                    ->sum('total'),
                'cursos_especializados' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('type', 'service_sale')
                    ->sum('total'),
            ],
            'egresos_hoy' => [
                'honorarios_dictantes' => $egresosHonorarios,
                'gastos_operativos' => $egresosGastos,
                'proveedores_academicos' => max(0, $egresosTotales - $egresosHonorarios - $egresosGastos),
            ],
            'detalle_transacciones' => Sale::with(['user', 'items'])
                ->whereDate('created_at', $fechaHoy)
                ->where('punto_venta_id', $this->puntoVenta->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
        ];

        return view('admin.ingresos-egresos.postgrado', compact('ingresos_egresos'));
    }

    /**
     * Libro Caja específico para Postgrado
     */
    public function adminLibroCaja()
    {
        $fechaDesde = request('fecha_desde', Carbon::today()->subDays(30)->format('Y-m-d'));
        $fechaHasta = request('fecha_hasta', Carbon::today()->format('Y-m-d'));

        $totalIngresos = Sale::whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->sum('total');

        $totalEgresos = PagoProveedor::whereBetween('fecha_pago', [$fechaDesde, $fechaHasta])
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->where('estado', 'registrado')
            ->sum('monto');

        $movimientos_caja = [
            'resumen_periodo' => [
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'saldo_periodo' => $totalIngresos - $totalEgresos,
            ],
            'movimientos_detalle' => Sale::with(['user'])
                ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
                ->where('punto_venta_id', $this->puntoVenta->id)
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
        ];

        return view('admin.libro-caja.postgrado', compact('movimientos_caja'));
    }

    /**
     * Gestión de estudiantes que PARTICIPAN en postgrado
     * Incluye estudiantes de grado/tecnicatura que toman cursos de postgrado
     */
    public function estudiantes()
    {
        // Estudiantes que han tenido ALGUNA operación en POSTGRADO
        $estudiantes = Student::whereHas('ventas', function ($query) {
            $query->where('punto_venta_id', $this->puntoVenta->id);
        })
            ->where('activo', true)
            ->with(['ultimaVentaPostgrado' => function ($query) {
                $query->where('punto_venta_id', $this->puntoVenta->id)
                    ->latest();
            }])
            ->orderBy('apellido', 'asc')
            ->paginate(15);

        return view('postgrado.estudiantes.index', compact('estudiantes'));
    }

    /**
     * POS específico de Postgrado con opción de venta rápida
     */
    public function pos()
    {
        // Productos/servicios específicos de POSTGRADO
        $productos = Product::where('is_active', true)
            ->whereIn('type', [
                'cuota_maestria',
                'cuota_doctorado',
                'cuota_especialidad',
                'cuota_diplomatura',
                'derecho_inscripcion',
                'derecho_titulo',
                'curso_extension',
                'congreso',
                'seminario',
                'evento_academico'
            ])
            ->orWhere('category', 'postgrado')
            ->get();

        $estudiantes = Student::where('activo', true)
            ->whereHas('ventas', function ($query) {
                $query->where('punto_venta_id', $this->puntoVenta->id);
            })
            ->orderBy('apellido', 'asc')
            ->take(100) // Límite para performance
            ->get();

        return view('postgrado.pos', compact('productos', 'estudiantes'));
    }

    /**
     * Procesa una venta de Postgrado con persistencia real.
     */
    public function procesarVenta(Request $request)
    {
        $request->validate([
            'carrito' => 'required|array|min:1',
            'carrito.*.id' => 'required|integer|exists:products,id',
            'carrito.*.cantidad' => 'required|integer|min:1',
            'carrito.*.precio' => 'required|numeric|min:0',
            'metodoPago' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'tipoComprobante' => 'required|in:ticket,factura_local,factura_fiscal',
            'subtotal' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'totalFinal' => 'required|numeric|min:0',
            'datosCliente' => 'nullable|array',
            'observaciones' => 'nullable|string',
            'montoRecibido' => 'nullable|numeric|min:0',
            'mixtoMetodo1' => 'nullable|in:efectivo,tarjeta,transferencia',
            'mixtoMetodo2' => 'nullable|in:efectivo,tarjeta,transferencia',
            'mixtoMonto1' => 'nullable|numeric|min:0',
            'mixtoMonto2' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $metodoPago = (string) $request->input('metodoPago');
            $tipoComprobante = (string) $request->input('tipoComprobante');
            $subtotal = round((float) $request->input('subtotal', 0), 2);
            $descuento = round((float) $request->input('descuento', 0), 2);
            $totalFinal = round((float) $request->input('totalFinal', 0), 2);

            if ($descuento > $subtotal + 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'El descuento no puede superar al subtotal.'
                ], 422);
            }

            if (abs(($subtotal - $descuento) - $totalFinal) > 0.05) {
                return response()->json([
                    'success' => false,
                    'message' => 'El total final no coincide con subtotal y descuento.'
                ], 422);
            }

            if ($metodoPago === 'efectivo') {
                $montoRecibido = round((float) $request->input('montoRecibido', 0), 2);
                if ($montoRecibido + 0.01 < $totalFinal) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El monto recibido es menor al total.'
                    ], 422);
                }
            }

            if ($metodoPago === 'mixto') {
                $metodo1 = $request->input('mixtoMetodo1');
                $metodo2 = $request->input('mixtoMetodo2');
                $monto1 = round((float) $request->input('mixtoMonto1', 0), 2);
                $monto2 = round((float) $request->input('mixtoMonto2', 0), 2);

                if (!$metodo1 || !$metodo2 || $metodo1 === $metodo2 || $monto1 <= 0 || $monto2 <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos de pago mixto incompletos o invalidos.'
                    ], 422);
                }

                if (abs(($monto1 + $monto2) - $totalFinal) > 0.01) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La suma del pago mixto debe coincidir con el total.'
                    ], 422);
                }
            }

            $codigoMetodoPago = match ($metodoPago) {
                'efectivo' => 'EFE',
                'tarjeta' => 'TDC',
                'transferencia' => 'TRA',
                'mixto' => 'EFE',
                default => 'EFE',
            };

            $paymentMethodId = PaymentMethod::where('code', $codigoMetodoPago)->value('id');
            if (!$paymentMethodId) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No hay metodo de pago configurado para el codigo seleccionado.'
                ], 500);
            }

            $saleNumber = 'PG' . str_pad((string) $this->puntoVenta->id, 3, '0', STR_PAD_LEFT)
                . '-' . now()->format('YmdHis')
                . '-' . strtoupper(substr(bin2hex(\random_bytes(3)), 0, 6));

            $datosCliente = $request->input('datosCliente', []);
            if (!is_array($datosCliente)) {
                $datosCliente = [];
            }

            $detallePago = [];
            if ($metodoPago === 'efectivo') {
                $montoRecibido = round((float) $request->input('montoRecibido', 0), 2);
                $detallePago['monto_recibido'] = $montoRecibido;
                $detallePago['vuelto'] = max(0, round($montoRecibido - $totalFinal, 2));
            }

            if ($metodoPago === 'mixto') {
                $detallePago['componentes'] = [
                    [
                        'metodo' => $request->input('mixtoMetodo1'),
                        'monto' => round((float) $request->input('mixtoMonto1', 0), 2),
                    ],
                    [
                        'metodo' => $request->input('mixtoMetodo2'),
                        'monto' => round((float) $request->input('mixtoMonto2', 0), 2),
                    ],
                ];
            }

            $venta = Sale::create([
                'sale_number' => $saleNumber,
                'punto_venta_id' => $this->puntoVenta->id,
                'usuario_id' => auth()->id(),
                'payment_method_id' => $paymentMethodId,
                'fecha_venta' => now(),
                'type' => 'student_fee',
                'subtotal' => $subtotal,
                'discount_amount' => $descuento,
                'total' => $totalFinal,
                'fiscal_document_type' => $tipoComprobante,
                'status' => 'completed',
                'notes' => $request->input('observaciones', ''),
                'additional_data' => [
                    'origen' => 'postgrado.cobros',
                    'metodo_pago' => $metodoPago,
                    'tipo_comprobante' => $tipoComprobante,
                    'detalles_pago' => $detallePago,
                    'cliente' => [
                        'nombre' => $datosCliente['nombre'] ?? 'Consumidor Final',
                        'documento' => $datosCliente['documento'] ?? '00000000',
                        'direccion' => $datosCliente['direccion'] ?? '',
                        'condicion_iva' => $datosCliente['condicionIva'] ?? 'consumidor_final',
                    ],
                ],
            ]);

            $carrito = $request->input('carrito', []);
            foreach ($carrito as $item) {
                $producto = Product::find((int) $item['id']);
                if (!$producto) {
                    throw new \RuntimeException('Producto no encontrado durante la persistencia de la venta.');
                }

                $cantidad = (int) $item['cantidad'];
                $precio = round((float) $item['precio'], 2);
                $subtotalItem = round($cantidad * $precio, 2);

                $venta->items()->create([
                    'product_id' => $producto->id,
                    'product_code' => $producto->code ?? ($item['codigo'] ?? 'SIN-CODIGO'),
                    'product_name' => $producto->name ?? ($item['nombre'] ?? 'Concepto'),
                    'quantity' => $cantidad,
                    'unit_price' => $precio,
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'subtotal' => $subtotalItem,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'total' => $subtotalItem,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado correctamente en Postgrado.',
                'data' => [
                    'venta_id' => $venta->id,
                    'sale_number' => $venta->sale_number,
                    'total' => (float) $venta->total,
                    'metodo_pago' => $metodoPago,
                    'tipo_comprobante' => $tipoComprobante,
                    'ticket_url' => route('postgrado.ticket', $venta->id),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PostgradoController::procesarVenta error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo registrar el pago de Postgrado.',
            ], 500);
        }
    }

    /**
     * Descarga/visualiza ticket PDF de una venta de Postgrado.
     */
    public function descargarTicket(Sale $sale)
    {
        if ((int) $sale->punto_venta_id !== (int) $this->puntoVenta->id) {
            abort(403, 'No tienes acceso a este comprobante.');
        }

        $sale->load(['items']);

        if ($sale->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'La venta no tiene items para generar ticket.'
            ], 422);
        }

        $additionalData = is_array($sale->additional_data) ? $sale->additional_data : [];
        $cliente = is_array($additionalData['cliente'] ?? null) ? $additionalData['cliente'] : null;
        $detallesPago = is_array($additionalData['detalles_pago'] ?? null) ? $additionalData['detalles_pago'] : [];

        $carrito = $sale->items->map(function ($item) {
            return [
                'nombre' => (string) ($item->product_name ?? 'Concepto'),
                'cantidad' => (int) ($item->quantity ?? 1),
                'precio' => (float) ($item->unit_price ?? 0),
            ];
        })->values()->toArray();

        $datosTicket = [
            'numero_ticket' => 'PG-' . $sale->id,
            'carrito' => $carrito,
            'subtotal' => (float) ($sale->subtotal ?? 0),
            'descuento' => (float) ($sale->discount_amount ?? 0),
            'total' => (float) ($sale->total ?? 0),
            'metodo_pago' => (string) ($additionalData['metodo_pago'] ?? 'efectivo'),
            'tipo_comprobante' => (string) ($additionalData['tipo_comprobante'] ?? ($sale->fiscal_document_type ?? 'ticket')),
            'cliente' => $cliente,
            'detalles_pago' => $detallesPago,
        ];

        try {
            $pdf = (new \App\Services\PDFTicket())->generar($datosTicket);

            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="ticket-postgrado-' . $sale->id . '.pdf"');
        } catch (\Throwable $e) {
            Log::error('PostgradoController::descargarTicket error', [
                'sale_id' => $sale->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el ticket de Postgrado.',
            ], 500);
        }
    }

    /**
     * Matrículas y pagos
     */
    public function matriculas()
    {
        $matriculas = Sale::where('punto_venta_id', $this->puntoVenta->id)
            ->where('type', 'student_fee')
            ->with(['student', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('postgrado.matriculas.index', compact('matriculas'));
    }

    /**
     * Gestión de cursos y especializaciones
     */
    public function cursos()
    {
        $cursos = [
            'activos' => $this->getCursosActivos(),
            'proximos' => $this->getCursosProximos(),
            'finalizados' => $this->getCursosFinalizados()
        ];

        return view('postgrado.cursos.index', compact('cursos'));
    }

    /**
     * Certificados y constancias
     */
    public function certificados()
    {
        $certificados = Sale::where('punto_venta_id', $this->puntoVenta->id)
            ->where('type', 'service_sale')
            ->with(['student'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('postgrado.certificados.index', compact('certificados'));
    }

    /**
     * Reportes académicos
     */
    public function reportes()
    {
        $fechaDesde = request('fecha_desde', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $fechaHasta = request('fecha_hasta', Carbon::today()->format('Y-m-d'));

        $ventasPeriodo = Sale::whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->where('punto_venta_id', $this->puntoVenta->id);

        $topProductos = SaleItem::query()
            ->join('ventas', 'items_venta.sale_id', '=', 'ventas.id')
            ->where('ventas.punto_venta_id', $this->puntoVenta->id)
            ->whereBetween('ventas.created_at', [$fechaDesde, $fechaHasta])
            ->selectRaw('items_venta.product_id, items_venta.product_name as nombre, SUM(items_venta.quantity) as cantidad, SUM(items_venta.total) as monto_total')
            ->groupBy('items_venta.product_id', 'items_venta.product_name')
            ->orderByDesc('cantidad')
            ->limit(15)
            ->get();

        $reportes = [
            'matriculas_mes' => $this->getMatriculasMes(),
            'cursos_demandados' => $this->getCursosDemandados(),
            'ingresos_por_curso' => $this->getIngresosPorCurso(),
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta,
            ],
            'totales' => [
                'ventas' => (clone $ventasPeriodo)->count(),
                'ingresos' => (clone $ventasPeriodo)->sum('total'),
                'ticket_promedio' => (clone $ventasPeriodo)->avg('total') ?? 0,
            ],
            'top_productos' => $topProductos,
        ];

        return view('postgrado.reportes.index', compact('reportes'));
    }

    /**
     * Configuración específica de Postgrado
     */
    public function configuracion()
    {
        $configuracion = [
            'punto_venta' => $this->puntoVenta,
            'periodos_academicos' => $this->getPeriodosAcademicos(),
            'aranceles' => $this->getAranceles()
        ];

        return view('postgrado.configuracion', compact('configuracion'));
    }

    /**
     * Configuración de carreras específicas de Postgrado
     */
    public function carreras()
    {
        // Solo mostrar carreras de postgrado - excluyendo explícitamente grado y tecnicaturas
        $carreras = CareerFeeConfig::whereNotIn('tipo_carrera', [
            'grado_odontologia',
            'tecnicatura_protesis',
            'tecnicatura_asistencia'
        ])
            ->orderBy('nombre_carrera')
            ->get();

        return view('postgrado.carreras.index', compact('carreras'));
    }

    // Métodos privados auxiliares
    private function getEstadisticas()
    {
        return [
            'matriculas_mes' => Sale::whereMonth('created_at', Carbon::now()->month)
                ->where('punto_venta_id', $this->puntoVenta->id)
                ->where('type', 'student_fee')
                ->count(),
            'ingresos_mes' => Sale::whereMonth('created_at', Carbon::now()->month)
                ->where('punto_venta_id', $this->puntoVenta->id)
                ->sum('total'),
            'estudiantes_activos' => Student::where('activo', true)
                ->count(), // Remover filtro por tipo inexistente
            'cursos_activos' => $this->getCursosActivos()->count()
        ];
    }

    private function getMatriculasMes()
    {
        return Sale::whereMonth('created_at', Carbon::now()->month)
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->where('type', 'student_fee')
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as cantidad')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    private function getCursosActivos()
    {
        // Simulación de cursos activos - se podría tener una tabla específica
        return collect([
            ['nombre' => 'Especialización en Ortodoncia', 'estudiantes' => 25, 'inicio' => '2026-03-01'],
            ['nombre' => 'Maestría en Implantología', 'estudiantes' => 15, 'inicio' => '2026-02-15'],
            ['nombre' => 'Curso de Endodoncia Avanzada', 'estudiantes' => 30, 'inicio' => '2026-04-01']
        ]);
    }

    private function getCursosProximos()
    {
        return collect([
            ['nombre' => 'Diplomado en Periodoncia', 'fecha_inicio' => '2026-05-15', 'cupos' => 20],
            ['nombre' => 'Especialización en Cirugía Oral', 'fecha_inicio' => '2026-06-01', 'cupos' => 15]
        ]);
    }

    private function getCursosFinalizados()
    {
        return collect([
            ['nombre' => 'Curso de Radiología Dental', 'graduados' => 28, 'fecha_fin' => '2026-03-30'],
            ['nombre' => 'Especialización en Prótesis', 'graduados' => 12, 'fecha_fin' => '2026-02-28']
        ]);
    }

    private function getCursosDemandados()
    {
        return collect([
            ['curso' => 'Ortodoncia', 'solicitudes' => 45],
            ['curso' => 'Implantología', 'solicitudes' => 35],
            ['curso' => 'Endodoncia', 'solicitudes' => 30]
        ]);
    }

    private function getIngresosPorCurso()
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->join('sale_product', 'sales.id', '=', 'sale_product.sale_id')
            ->join('products', 'sale_product.product_id', '=', 'products.id')
            ->selectRaw('products.name as curso, SUM(sale_product.price * sale_product.quantity) as ingreso')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('ingreso')
            ->get();
    }

    private function getPeriodosAcademicos()
    {
        return [
            '2026-1' => 'Marzo - Julio 2026',
            '2026-2' => 'Agosto - Diciembre 2026'
        ];
    }

    private function getAranceles()
    {
        return [
            'especialización' => 50000,
            'maestría' => 75000,
            'doctorado' => 100000,
            'diplomado' => 25000
        ];
    }

    /**
     * Mostrar formulario para crear estudiante
     */
    public function estudiantesCrear()
    {
        return view('postgrado.estudiantes.crear', [
            'carreras' => CareerFeeConfig::whereNotIn('type', ['grado', 'tecnicatura'])->get(),
            'sectionTitle' => 'Agregar Estudiante de Postgrado'
        ]);
    }

    /**
     * Mostrar formulario para importar estudiantes desde CSV
     */
    public function estudiantesImportar()
    {
        return view('postgrado.estudiantes.importar', [
            'sectionTitle' => 'Importar Estudiantes de Postgrado'
        ]);
    }

    /**
     * Gestionar cuotas de carreras de postgrado
     */
    public function carrerasCuotas()
    {
        $cuotas = CareerFeeConfig::whereNotIn('type', ['grado', 'tecnicatura'])
            ->with(['fees' => function ($query) {
                $query->orderBy('due_date');
            }])
            ->get();

        return view('postgrado.carreras.cuotas', [
            'cuotas' => $cuotas,
            'sectionTitle' => 'Gestionar Cuotas de Postgrado'
        ]);
    }

    /**
     * Venta rápida para eventos (congresos, seminarios)
     * No requiere registro de estudiante
     */
    public function ventaRapida(Request $request)
    {
        $validated = $request->validate([
            'productos' => 'required|array',
            'cliente_nombre' => 'required|string|max:255',
            'cliente_apellido' => 'required|string|max:255',
            'cliente_email' => 'nullable|email',
            'cliente_telefono' => 'nullable|string|max:20',
            'tipo_evento' => 'required|string', // congreso, seminario, etc.
            'notas' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Calcular total
            $total = 0;
            foreach ($validated['productos'] as $producto) {
                $total += $producto['precio'] * $producto['cantidad'];
            }

            // Crear venta sin estudiante asociado
            $venta = Sale::create([
                'sale_number' => $this->generarNumeroVentaPostgrado(),
                'punto_venta_id' => $this->puntoVenta->id,
                'usuario_id' => auth()->id(),
                'student_id' => null, // Sin estudiante asociado
                'payment_method_id' => 1, // Efectivo por defecto
                'fecha_venta' => now(),
                'tipo' => $validated['tipo_evento'],
                'subtotal' => $total,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => $total,
                'status' => 'completed',
                'notes' => $validated['notas'],
                'additional_data' => [
                    'venta_rapida' => true,
                    'cliente' => [
                        'nombre' => $validated['cliente_nombre'],
                        'apellido' => $validated['cliente_apellido'],
                        'email' => $validated['cliente_email'],
                        'telefono' => $validated['cliente_telefono'],
                    ]
                ]
            ]);

            // Agregar productos a la venta
            foreach ($validated['productos'] as $producto) {
                $venta->products()->attach($producto['id'], [
                    'quantity' => $producto['cantidad'],
                    'price' => $producto['precio']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'venta_id' => $venta->id,
                'numero_venta' => $venta->sale_number,
                'cliente' => $validated['cliente_apellido'] . ', ' . $validated['cliente_nombre'],
                'total' => number_format($total, 2),
                'mensaje' => 'Venta registrada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===========================================
    // MÉTODOS PARA TODAS LAS RUTAS DEL MENÚ
    // ===========================================

    public function carrerasCrear()
    {
        return view('postgrado.carreras.crear');
    }

    // Programas específicos
    public function maestrias()
    {
        return view('postgrado.programas.maestrias');
    }
    public function doctorados()
    {
        return view('postgrado.programas.doctorados');
    }
    public function especialidades()
    {
        return view('postgrado.programas.especialidades');
    }
    public function diplomaturas()
    {
        return view('postgrado.programas.diplomaturas');
    }

    // Estudiantes por programa
    public function estudiantesMaestrias()
    {
        return view('postgrado.estudiantes.maestrias');
    }
    public function estudiantesDoctorados()
    {
        return view('postgrado.estudiantes.doctorados');
    }
    public function estudiantesEspecialidades()
    {
        return view('postgrado.estudiantes.especialidades');
    }
    public function estudiantesDiplomaturas()
    {
        return view('postgrado.estudiantes.diplomaturas');
    }
    public function estudiantesCursos()
    {
        return view('postgrado.estudiantes.cursos');
    }

    // Inscripciones
    public function inscripcionesCrear()
    {
        return view('postgrado.inscripciones.crear');
    }
    public function inscripcionesImportar()
    {
        return view('postgrado.inscripciones.importar');
    }
    public function inscripcionesEstado()
    {
        return view('postgrado.inscripciones.estado');
    }

    // Cobros por programa
    public function cobrosMaestrias()
    {
        return view('postgrado.cobros.maestrias');
    }
    public function cobrosDoctorados()
    {
        return view('postgrado.cobros.doctorados');
    }
    public function cobrosEspecialidades()
    {
        return view('postgrado.cobros.especialidades');
    }
    public function cobrosDiplomaturas()
    {
        return view('postgrado.cobros.diplomaturas');
    }
    public function cobrosCursos()
    {
        return view('postgrado.cobros.cursos');
    }

    // Derechos
    public function derechosInscripcion()
    {
        return view('postgrado.derechos.inscripcion');
    }
    public function derechosExamenes()
    {
        return view('postgrado.derechos.examenes');
    }
    public function derechosTitulos()
    {
        return view('postgrado.derechos.titulos');
    }

    // Certificados
    public function certificadosEmitir()
    {
        return view('postgrado.certificados.emitir');
    }
    public function certificadosHistorial()
    {
        return view('postgrado.certificados.historial');
    }
    public function certificadosPlantillas()
    {
        return view('postgrado.certificados.plantillas');
    }
    public function titulos()
    {
        return view('postgrado.titulos.index');
    }

    // Reportes específicos
    public function reportesEstudiantes()
    {
        return view('postgrado.reportes.estudiantes');
    }
    public function reportesRecaudacion()
    {
        return view('postgrado.reportes.recaudacion');
    }
    public function reportesMatriculas()
    {
        return view('postgrado.reportes.matriculas');
    }
    public function reportesInscripciones()
    {
        return view('postgrado.reportes.inscripciones');
    }
    public function reportesCertificados()
    {
        return view('postgrado.reportes.certificados');
    }
    public function reportesPagos()
    {
        return view('postgrado.reportes.pagos');
    }
    public function reportesTitulos()
    {
        return view('postgrado.reportes.titulos');
    }

    private function getIngresosDelMes()
    {
        return Sale::whereMonth('created_at', Carbon::now()->month)
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->sum('total');
    }

    private function getEstudiantesActivos()
    {
        return Student::where('activo', true)
            ->count();
    }

    private function getHonorariosPendientes()
    {
        // Placeholder: sin modelo de honorarios implementado aún
        return 0;
    }

    /**
     * Generar número de venta para POSTGRADO
     */
    private function generarNumeroVentaPostgrado()
    {
        $prefix = 'PG-' . date('Ymd') . '-';
        $lastSale = Sale::where('sale_number', 'LIKE', $prefix . '%')
            ->where('punto_venta_id', $this->puntoVenta->id)
            ->orderBy('sale_number', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = intval(substr($lastSale->sale_number, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * API para buscar estudiantes de postgrado
     */
    public function buscarEstudiantes(Request $request)
    {
        $term = $request->get('term');

        $estudiantes = Student::where('activo', true)
            ->whereHas('ventas', function ($query) {
                $query->where('punto_venta_id', $this->puntoVenta->id);
            })
            ->where(function ($query) use ($term) {
                $query->where('apellido', 'LIKE', "%{$term}%")
                    ->orWhere('nombre', 'LIKE', "%{$term}%")
                    ->orWhere('dni', 'LIKE', "%{$term}%")
                    ->orWhere('legajo', 'LIKE', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'apellido', 'nombre', 'dni', 'legajo']);

        return response()->json($estudiantes);
    }
}
