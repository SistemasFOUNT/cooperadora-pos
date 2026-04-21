<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OdontoController extends Controller
{
    private $puntoVenta;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('punto_venta');
        $this->middleware(function ($request, $next) {
            $this->puntoVenta = PuntoVenta::where('codigo', 'ODONTO')->first();
            return $next($request);
        });
    }

    /**
     * Dashboard principal del Centro Odontológico
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Verificar que el usuario sea del Centro Odontológico
        if (!$user->isAdmin() && $user->punto_venta_id != $this->puntoVenta->id) {
            abort(403, 'No tienes acceso al Centro Odontológico.');
        }

        $estadisticas = $this->getEstadisticas();

        return view('odonto.dashboard', compact('estadisticas'));
    }

    /**
     * Supervisión administrativa del Centro Odontológico
     * Solo accesible para admin
     */
    public function adminSupervision()
    {
        $datos_supervision = [
            'estadisticas_clinicas' => $this->getEstadisticas(),
            'ingresos_del_mes' => $this->getIngresosDelMes(),
            'pacientes_activos' => $this->getPacientesActivos(),
            'servicios_pendientes' => $this->getServiciosPendientes(),
            'radiografias_pendientes' => $this->getRadiografiasPendientes(),
        ];

        return view('admin.supervision.odonto', compact('datos_supervision'));
    }

    /**
     * Ingresos y Egresos detallados para admin
     */
    public function adminIngresosEgresos()
    {
        $fechaHoy = Carbon::today();

        $ingresos_egresos = [
            'ingresos_hoy' => [
                'servicios_odontologicos' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'servicio_odontologico')
                    ->sum('total'),
                'radiografias' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'radiografia')
                    ->sum('total'),
                'medicamentos_insumos' => Sale::whereDate('created_at', $fechaHoy)
                    ->where('punto_venta_id', $this->puntoVenta->id)
                    ->where('tipo', 'medicamento_insumo')
                    ->sum('total'),
            ],
            'egresos_hoy' => [
                'insumos_clinicos' => 0, // TODO: Implementar modelo de pagos
                'equipamiento' => 0,
                'proveedores_medicos' => 0,
            ],
            'detalle_transacciones' => Sale::with(['user', 'items'])
                ->whereDate('created_at', $fechaHoy)
                ->where('punto_venta_id', $this->puntoVenta->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
        ];

        return view('admin.ingresos-egresos.odonto', compact('ingresos_egresos'));
    }

    /**
     * Libro Caja específico para Centro Odontológico
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

        return view('admin.libro-caja.odonto', compact('movimientos_caja'));
    }

    /**
     * Gestión de pacientes
     */
    public function pacientes()
    {
        $pacientes = Student::where('tipo', 'paciente')
                          ->orWhere('carrera', 'LIKE', '%odontología%')
                          ->paginate(15);

        return view('odonto.pacientes.index', compact('pacientes'));
    }

    /**
     * Agenda de citas
     */
    public function agenda()
    {
        $citas = $this->getCitasDelDia();
        $proximasCitas = $this->getProximasCitas();

        return view('odonto.agenda.index', compact('citas', 'proximasCitas'));
    }

    /**
     * POS específico del Centro Odontológico
     */
    public function pos()
    {
        $productos = Product::where('active', true)
                          ->where('categoria', 'odontologia')
                          ->orWhere('punto_venta_id', $this->puntoVenta->id)
                          ->get();

        return view('odonto.pos', compact('productos'));
    }

    /**
     * Gestión de tratamientos
     */
    public function tratamientos()
    {
        $tratamientos = [
            'activos' => $this->getTratamientosActivos(),
            'finalizados' => $this->getTratamientosFinalizados(),
            'pendientes' => $this->getTratamientosPendientes()
        ];

        return view('odonto.tratamientos.index', compact('tratamientos'));
    }

    /**
     * Inventario de materiales odontológicos
     */
    public function inventario()
    {
        $materiales = Product::where('categoria', 'material_odontologico')
                           ->orWhere('punto_venta_id', $this->puntoVenta->id)
                           ->with('stock')
                           ->paginate(20);

        return view('odonto.inventario.index', compact('materiales'));
    }

    /**
     * Historiales clínicos
     */
    public function historiales()
    {
        $historiales = Sale::where('punto_venta_id', $this->puntoVenta->id)
                         ->where('tipo', 'tratamiento')
                         ->with(['student', 'user'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return view('odonto.historiales.index', compact('historiales'));
    }

    /**
     * Facturación de tratamientos
     */
    public function facturacion()
    {
        $facturacion = [
            'del_dia' => $this->getFacturacionDelDia(),
            'del_mes' => $this->getFacturacionDelMes(),
            'por_tratamiento' => $this->getFacturacionPorTratamiento()
        ];

        return view('odonto.facturacion.index', compact('facturacion'));
    }

    /**
     * Reportes clínicos
     */
    public function reportes()
    {
        $reportes = [
            'tratamientos_mes' => $this->getTratamientosMes(),
            'pacientes_frecuentes' => $this->getPacientesFrecuentes(),
            'materiales_utilizados' => $this->getMaterialesUtilizados()
        ];

        return view('odonto.reportes.index', compact('reportes'));
    }

    /**
     * Configuración específica del Centro Odontológico
     */
    public function configuracion()
    {
        $configuracion = [
            'punto_venta' => $this->puntoVenta,
            'horarios_atencion' => $this->getHorariosAtencion(),
            'especialidades' => $this->getEspecialidades(),
            'precios_tratamientos' => $this->getPreciosTratamientos()
        ];

        return view('odonto.configuracion', compact('configuracion'));
    }

    // Métodos privados auxiliares
    private function getEstadisticas()
    {
        return [
            'citas_hoy' => $this->getCitasDelDia()->count(),
            'tratamientos_activos' => $this->getTratamientosActivos()->count(),
            'ingresos_mes' => Sale::whereMonth('created_at', Carbon::now()->month)
                                ->where('punto_venta_id', $this->puntoVenta->id)
                                ->sum('total'),
            'pacientes_atendidos_mes' => Sale::whereMonth('created_at', Carbon::now()->month)
                                           ->where('punto_venta_id', $this->puntoVenta->id)
                                           ->distinct('student_id')
                                           ->count('student_id')
        ];
    }

    private function getCitasDelDia()
    {
        // Simulación de citas - se podría tener una tabla específica
        return collect([
            ['paciente' => 'Juan Pérez', 'hora' => '09:00', 'tratamiento' => 'Limpieza', 'doctor' => 'Dr. García'],
            ['paciente' => 'María López', 'hora' => '10:30', 'tratamiento' => 'Empaste', 'doctor' => 'Dra. Martínez'],
            ['paciente' => 'Carlos Ruiz', 'hora' => '14:00', 'tratamiento' => 'Extracción', 'doctor' => 'Dr. García']
        ]);
    }

    private function getProximasCitas()
    {
        return collect([
            ['fecha' => '2026-04-21', 'paciente' => 'Ana Gómez', 'tratamiento' => 'Control'],
            ['fecha' => '2026-04-22', 'paciente' => 'Luis Torres', 'tratamiento' => 'Prótesis'],
            ['fecha' => '2026-04-23', 'paciente' => 'Elena Vega', 'tratamiento' => 'Ortodoncia']
        ]);
    }

    private function getTratamientosActivos()
    {
        return collect([
            ['paciente' => 'Pedro Sánchez', 'tratamiento' => 'Ortodoncia', 'progreso' => '60%'],
            ['paciente' => 'Laura Díaz', 'tratamiento' => 'Implante', 'progreso' => '30%'],
            ['paciente' => 'Miguel Herrera', 'tratamiento' => 'Endodoncia', 'progreso' => '80%']
        ]);
    }

    private function getTratamientosFinalizados()
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
                  ->where('tipo', 'tratamiento_finalizado')
                  ->whereMonth('created_at', Carbon::now()->month)
                  ->with(['student'])
                  ->get();
    }

    private function getTratamientosPendientes()
    {
        return collect([
            ['paciente' => 'Rosa Morales', 'tratamiento' => 'Limpieza', 'prioridad' => 'Media'],
            ['paciente' => 'Alberto Ramos', 'tratamiento' => 'Urgencia', 'prioridad' => 'Alta']
        ]);
    }

    private function getFacturacionDelDia()
    {
        return Sale::whereDate('created_at', Carbon::today())
                  ->where('punto_venta_id', $this->puntoVenta->id)
                  ->sum('total');
    }

    private function getFacturacionDelMes()
    {
        return Sale::whereMonth('created_at', Carbon::now()->month)
                  ->where('punto_venta_id', $this->puntoVenta->id)
                  ->sum('total');
    }

    private function getFacturacionPorTratamiento()
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
                  ->whereMonth('created_at', Carbon::now()->month)
                  ->join('sale_product', 'sales.id', '=', 'sale_product.sale_id')
                  ->join('products', 'sale_product.product_id', '=', 'products.id')
                  ->selectRaw('products.name as tratamiento, SUM(sale_product.price * sale_product.quantity) as ingreso')
                  ->groupBy('products.id', 'products.name')
                  ->orderByDesc('ingreso')
                  ->get();
    }

    private function getTratamientosMes()
    {
        return Sale::whereMonth('created_at', Carbon::now()->month)
                  ->where('punto_venta_id', $this->puntoVenta->id)
                  ->selectRaw('DATE(created_at) as fecha, COUNT(*) as cantidad')
                  ->groupBy('fecha')
                  ->orderBy('fecha')
                  ->get();
    }

    private function getPacientesFrecuentes()
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
                  ->whereMonth('created_at', Carbon::now()->month)
                  ->join('students', 'sales.student_id', '=', 'students.id')
                  ->selectRaw('students.name, COUNT(*) as visitas, SUM(sales.total) as total_gastado')
                  ->groupBy('students.id', 'students.name')
                  ->orderByDesc('visitas')
                  ->limit(10)
                  ->get();
    }

    private function getMaterialesUtilizados()
    {
        return DB::table('sale_product')
                ->join('sales', 'sale_product.sale_id', '=', 'sales.id')
                ->join('products', 'sale_product.product_id', '=', 'products.id')
                ->where('sales.punto_venta_id', $this->puntoVenta->id)
                ->where('products.categoria', 'material_odontologico')
                ->whereMonth('sales.created_at', Carbon::now()->month)
                ->selectRaw('products.name, SUM(sale_product.quantity) as cantidad_usada')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('cantidad_usada')
                ->get();
    }

    private function getHorariosAtencion()
    {
        return [
            'lunes_viernes' => '08:00 - 20:00',
            'sabados' => '08:00 - 14:00',
            'domingos' => 'Solo urgencias 10:00 - 14:00'
        ];
    }

    private function getEspecialidades()
    {
        return [
            'Odontología General',
            'Ortodoncia',
            'Endodoncia',
            'Cirugía Oral',
            'Periodoncia',
            'Prótesis Dental',
            'Odontopediatría',
            'Implantología'
        ];
    }

    private function getPreciosTratamientos()
    {
        return [
            'consulta' => 3000,
            'limpieza' => 5000,
            'empaste' => 8000,
            'extraccion' => 6000,
            'endodoncia' => 25000,
            'corona' => 40000,
            'implante' => 80000
        ];
    }
}
