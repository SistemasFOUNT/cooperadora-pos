<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Student;
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
                    ->where('tipo', 'cuota_postgrado')
                    ->sum('total'),
                'cursos_especializados' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'curso_especializado')
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
     * Gestión de estudiantes de postgrado
     */
    public function estudiantes()
    {
        $estudiantes = Student::where('tipo', 'postgrado')
                            ->orWhere('carrera', 'LIKE', '%postgrado%')
                            ->orWhere('carrera', 'LIKE', '%especialización%')
                            ->orWhere('carrera', 'LIKE', '%maestría%')
                            ->orWhere('carrera', 'LIKE', '%doctorado%')
                            ->paginate(15);

        return view('postgrado.estudiantes.index', compact('estudiantes'));
    }

    /**
     * POS específico de Postgrado
     */
    public function pos()
    {
        $productos = Product::where('active', true)
                          ->where('categoria', 'postgrado')
                          ->orWhere('punto_venta_id', $this->puntoVenta->id)
                          ->get();

        return view('postgrado.pos', compact('productos'));
    }

    /**
     * Matrículas y pagos
     */
    public function matriculas()
    {
        $matriculas = Sale::where('punto_venta_id', $this->puntoVenta->id)
                        ->where('tipo', 'matricula')
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
                          ->where('tipo', 'certificado')
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

    // Métodos privados auxiliares
    private function getEstadisticas()
    {
        return [
            'matriculas_mes' => Sale::whereMonth('created_at', Carbon::now()->month)
                                  ->where('punto_venta_id', $this->puntoVenta->id)
                                  ->where('tipo', 'matricula')
                                  ->count(),
            'ingresos_mes' => Sale::whereMonth('created_at', Carbon::now()->month)
                                ->where('punto_venta_id', $this->puntoVenta->id)
                                ->sum('total'),
            'estudiantes_activos' => Student::where('status', 'active')
                                          ->where('tipo', 'postgrado')
                                          ->count(),
            'cursos_activos' => $this->getCursosActivos()->count()
        ];
    }

    private function getMatriculasMes()
    {
        return Sale::whereMonth('created_at', Carbon::now()->month)
                  ->where('punto_venta_id', $this->puntoVenta->id)
                  ->where('tipo', 'matricula')
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
}
