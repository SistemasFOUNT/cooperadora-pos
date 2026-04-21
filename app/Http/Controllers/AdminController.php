<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\PuntoVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Dashboard principal del superusuario admin
     */
    public function dashboard()
    {
        $estadisticas = [
            'box' => $this->getEstadisticasPunto('BOX'),
            'postgrado' => $this->getEstadisticasPunto('POSTGRADO'),
            'odonto' => $this->getEstadisticasPunto('ODONTO'),
        ];

        // Obtener autorizaciones pendientes (temporal - implementar modelo después)
        $pendientes_autorizacion = 0; // TODO: Implementar cuando tengamos modelo de autorizaciones

        return view('admin.dashboard', compact('estadisticas', 'pendientes_autorizacion'));
    }

    /**
     * Supervisión general de todos los puntos de venta
     */
    public function supervisionGeneral()
    {
        $box_data = [
            'ingresos_hoy' => $this->getIngresosDiaPunto('BOX'),
            'transacciones' => $this->getTransaccionesDiaPunto('BOX'),
            'activo' => true
        ];

        $postgrado_data = [
            'ingresos_hoy' => $this->getIngresosDiaPunto('POSTGRADO'),
            'transacciones' => $this->getTransaccionesDiaPunto('POSTGRADO'),
            'activo' => true
        ];

        $odonto_data = [
            'ingresos_hoy' => $this->getIngresosDiaPunto('ODONTO'),
            'transacciones' => $this->getTransaccionesDiaPunto('ODONTO'),
            'activo' => true
        ];

        $alertas = $this->getAlertas();
        $pendientes_autorizacion = 0; // TODO: Implementar

        return view('admin.supervision.general', compact(
            'box_data',
            'postgrado_data',
            'odonto_data',
            'alertas',
            'pendientes_autorizacion'
        ));
    }

    /**
     * Ingresos y Egresos consolidados
     */
    public function ingresosEgresosConsolidado()
    {
        $fechaHoy = Carbon::today();

        $consolidado = [
            'ingresos_hoy' => [
                'box' => $this->getIngresosDiaPunto('BOX'),
                'postgrado' => $this->getIngresosDiaPunto('POSTGRADO'),
                'odonto' => $this->getIngresosDiaPunto('ODONTO'),
            ],
            'total_general' => Sale::whereDate('created_at', $fechaHoy)->sum('total'),
            'comparativo_mes' => $this->getComparativoMes(),
        ];

        return view('admin.ingresos-egresos.consolidado', compact('consolidado'));
    }

    /**
     * Libro Caja consolidado
     */
    public function libroCajaConsolidado()
    {
        $fechaDesde = request('fecha_desde', Carbon::today()->subDays(30)->format('Y-m-d'));
        $fechaHasta = request('fecha_hasta', Carbon::today()->format('Y-m-d'));

        $movimientos_consolidados = Sale::with(['user', 'puntoVenta'])
            ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->orderBy('created_at', 'desc')
            ->paginate(100);

        $resumen = [
            'total_ingresos' => Sale::whereBetween('created_at', [$fechaDesde, $fechaHasta])->sum('total'),
            'total_transacciones' => Sale::whereBetween('created_at', [$fechaDesde, $fechaHasta])->count(),
        ];

        return view('admin.libro-caja.consolidado', compact(
            'movimientos_consolidados',
            'resumen',
            'fechaDesde',
            'fechaHasta'
        ));
    }

    /**
     * Autorizaciones pendientes
     */
    public function autorizacionesIndex()
    {
        // TODO: Implementar cuando tengamos modelo de autorizaciones
        $autorizaciones_pendientes = collect(); // Temporal

        return view('admin.autorizaciones.index', compact('autorizaciones_pendientes'));
    }

    /**
     * Historial de autorizaciones
     */
    public function autorizacionesHistorial()
    {
        // TODO: Implementar cuando tengamos modelo de autorizaciones
        $historial_autorizaciones = collect(); // Temporal

        return view('admin.autorizaciones.historial', compact('historial_autorizaciones'));
    }

    /**
     * Estado de cuentas general
     */
    public function estadoGeneral()
    {
        $estado_cuentas = [
            'caja_general' => Sale::sum('total'),
            'por_punto_venta' => $this->getCuentasPorPunto(),
            'pendientes_cobro' => 0, // TODO: Implementar
            'gastos_periodo' => 0, // TODO: Implementar
        ];

        return view('admin.cuentas.estado-general', compact('estado_cuentas'));
    }

    /**
     * Estado de cuenta particular
     */
    public function estadoParticular()
    {
        return view('admin.cuentas.particular');
    }

    /**
     * Reportes consolidados
     */
    public function reportesConsolidado()
    {
        $reportes = [
            'ventas_por_mes' => $this->getVentasPorMes(),
            'productos_mas_vendidos' => $this->getProductosMasVendidos(),
            'usuarios_mas_activos' => $this->getUsuariosMasActivos(),
        ];

        return view('admin.reportes.consolidado', compact('reportes'));
    }

    // ===== MÉTODOS AUXILIARES =====

    private function getEstadisticasPunto($codigo_punto)
    {
        $punto = PuntoVenta::where('codigo', $codigo_punto)->first();
        $hoy = Carbon::today();

        if (!$punto) {
            return [
                'usuarios' => 0,
                'ventas_hoy' => 0,
                'ingresos_hoy' => 0,
                'transacciones_hoy' => 0,
                'estudiantes' => 0,
                'pacientes' => 0,
                'servicios_hoy' => 0
            ];
        }

        $ventas_hoy = Sale::whereDate('created_at', $hoy)
            ->where('punto_venta_id', $punto->id)
            ->count();

        $ingresos_hoy = Sale::whereDate('created_at', $hoy)
            ->where('punto_venta_id', $punto->id)
            ->sum('total');

        return [
            'usuarios' => 0, // Temporalmente en 0 hasta que se implemente la relación
            'ventas_hoy' => $ventas_hoy,
            'ingresos_hoy' => $ingresos_hoy,
            'transacciones_hoy' => $ventas_hoy,
            'estudiantes' => 0, // TODO: Implementar según modelo
            'pacientes' => 0, // TODO: Implementar según modelo
            'servicios_hoy' => $ventas_hoy
        ];
    }

    private function getIngresosDiaPunto($codigo_punto)
    {
        $punto = PuntoVenta::where('codigo', $codigo_punto)->first();
        if (!$punto) return 0;

        return Sale::whereDate('created_at', Carbon::today())
            ->where('punto_venta_id', $punto->id)
            ->sum('total');
    }

    private function getTransaccionesDiaPunto($codigo_punto)
    {
        $punto = PuntoVenta::where('codigo', $codigo_punto)->first();
        if (!$punto) return 0;

        return Sale::whereDate('created_at', Carbon::today())
            ->where('punto_venta_id', $punto->id)
            ->count();
    }

    private function getAlertas()
    {
        $alertas = [];

        // Verificar ventas bajas
        $ingresos_box = $this->getIngresosDiaPunto('BOX');
        if ($ingresos_box < 1000) { // Threshold configurable
            $alertas[] = [
                'tipo' => 'warning',
                'punto_venta' => 'BOX',
                'mensaje' => 'Ingresos del día por debajo del promedio esperado'
            ];
        }

        return $alertas;
    }

    private function getComparativoMes()
    {
        $inicio_mes = Carbon::now()->startOfMonth();
        $mes_anterior = Carbon::now()->subMonth()->startOfMonth();
        $fin_mes_anterior = Carbon::now()->subMonth()->endOfMonth();

        $mes_actual = Sale::whereBetween('created_at', [$inicio_mes, Carbon::now()])->sum('total');
        $mes_pasado = Sale::whereBetween('created_at', [$mes_anterior, $fin_mes_anterior])->sum('total');

        return [
            'mes_actual' => $mes_actual,
            'mes_anterior' => $mes_pasado,
            'variacion' => $mes_pasado > 0 ? (($mes_actual - $mes_pasado) / $mes_pasado) * 100 : 0
        ];
    }

    private function getCuentasPorPunto()
    {
        return PuntoVenta::with(['sales' => function($query) {
            $query->select('punto_venta_id', DB::raw('SUM(total) as total_ingresos'))
                  ->groupBy('punto_venta_id');
        }])->get();
    }

    private function getVentasPorMes()
    {
        return Sale::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, SUM(total) as total')
            ->groupBy('año', 'mes')
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->limit(12)
            ->get();
    }

    private function getProductosMasVendidos()
    {
        // TODO: Implementar cuando tengamos relaciones con productos
        return collect();
    }

    private function getUsuariosMasActivos()
    {
        return Sale::selectRaw('user_id, COUNT(*) as total_ventas, SUM(total) as total_monto')
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('total_ventas', 'desc')
            ->limit(10)
            ->get();
    }
}
