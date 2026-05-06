<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Student;
use App\Models\CareerFeeConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'honorarios_dictantes' => 0, // TODO: Implementar modelo de pagos
                'gastos_operativos' => 0,
                'proveedores_academicos' => 0,
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
        $reportes = [
            'matriculas_mes' => $this->getMatriculasMes(),
            'cursos_demandados' => $this->getCursosDemandados(),
            'ingresos_por_curso' => $this->getIngresosPorCurso()
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

    // Gestión de carreras
    public function carreras()
    {
        return view('postgrado.carreras.index');
    }
    public function carrerasCrear()
    {
        return view('postgrado.carreras.crear');
    }
    public function carrerasCuotas()
    {
        return view('postgrado.carreras.cuotas');
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
