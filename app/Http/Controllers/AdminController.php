<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\PuntoVenta;
use App\Models\Product;
use App\Models\Student;
use App\Models\User;
use App\Models\CuentaContable;
use App\Models\MovimientoContable;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OwenIt\Auditing\Models\Audit;

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

        $estadisticas_generales = [
            'total_productos' => Product::count(),
            'total_estudiantes' => Student::count(),
            'total_puntos_venta' => PuntoVenta::count(),
            'usuarios_activos' => User::where('role', '!=', 'admin')->count(),
        ];

        // Obtener autorizaciones pendientes (temporal - implementar modelo después)
        $pendientes_autorizacion = 0; // TODO: Implementar cuando tengamos modelo de autorizaciones

        return view('admin.dashboard', [
            'estadisticas' => $estadisticas,
            'estadisticas_generales' => $estadisticas_generales,
            'pendientes_autorizacion' => $pendientes_autorizacion,
            'user' => auth()->user(),
        ]);
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
     * Auditoría interna con filtros por evento, modelo, usuario y fecha.
     */
    public function auditoriaIndex(Request $request)
    {
        $query = Audit::query()->orderByDesc('created_at');

        $evento = $request->input('evento');
        $modelo = $request->input('modelo');
        $usuarioId = $request->input('usuario_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $buscar = trim((string) $request->input('buscar', ''));

        if (!empty($evento)) {
            $query->where('event', $evento);
        }

        if (!empty($modelo)) {
            $query->where('auditable_type', $modelo);
        }

        if (!empty($usuarioId)) {
            $query->where('user_id', $usuarioId);
        }

        if (!empty($fechaDesde)) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        if ($buscar !== '') {
            $query->where(function ($subQuery) use ($buscar) {
                $subQuery->where('auditable_id', 'like', '%' . $buscar . '%')
                    ->orWhere('url', 'like', '%' . $buscar . '%')
                    ->orWhere('ip_address', 'like', '%' . $buscar . '%')
                    ->orWhere('tags', 'like', '%' . $buscar . '%');
            });
        }

        $audits = $query->paginate(50)->withQueryString();

        $eventos = Audit::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $modelos = Audit::query()
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');

        $usuariosConAuditoria = Audit::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->filter();

        $usuarios = User::query()
            ->whereIn('id', $usuariosConAuditoria)
            ->orderBy('name')
            ->get(['id', 'name']);

        $nombresUsuarios = $usuarios->pluck('name', 'id');

        return view('admin.auditoria.index', compact(
            'audits',
            'eventos',
            'modelos',
            'usuarios',
            'nombresUsuarios',
            'evento',
            'modelo',
            'usuarioId',
            'fechaDesde',
            'fechaHasta',
            'buscar'
        ));
    }

    /**
     * Detalle puntual de un registro de auditoría.
     */
    public function auditoriaShow($id)
    {
        $audit = Audit::findOrFail($id);
        $usuario = null;

        if (!empty($audit->user_id)) {
            $usuario = User::find($audit->user_id);
        }

        return view('admin.auditoria.show', compact('audit', 'usuario'));
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
    public function estadoParticular(Request $request)
    {
        [$cuentas, $cuentaSeleccionada, $movimientos, $resumen, $desde, $hasta, $buscar] = $this->obtenerEstadoParticular($request, true);

        return view('admin.cuentas.particular', compact(
            'cuentas',
            'cuentaSeleccionada',
            'movimientos',
            'resumen',
            'desde',
            'hasta',
            'buscar'
        ));
    }

    /**
     * Exportar estado de cuenta particular a PDF
     */
    public function exportarEstadoParticularPdf(Request $request)
    {
        [$cuentas, $cuentaSeleccionada, $movimientos, $resumen, $desde, $hasta, $buscar] = $this->obtenerEstadoParticular($request, false);

        if (!$cuentaSeleccionada) {
            return redirect()
                ->route('admin.cuentas.particular')
                ->with('error', 'Debes seleccionar una cuenta para exportar.');
        }

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->SetMargins(10, 10, 10);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, utf8_decode('Estado de Cuenta - Particular'), 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, utf8_decode('Cuenta: ' . $cuentaSeleccionada->codigo . ' - ' . $cuentaSeleccionada->nombre), 0, 1);
        $pdf->Cell(0, 6, utf8_decode('Periodo: ' . $desde . ' al ' . $hasta), 0, 1);
        if ($buscar) {
            $pdf->Cell(0, 6, utf8_decode('Búsqueda: ' . $buscar), 0, 1);
        }

        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 7, utf8_decode('Fecha'), 1);
        $pdf->Cell(28, 7, utf8_decode('Asiento'), 1);
        $pdf->Cell(62, 7, utf8_decode('Concepto'), 1);
        $pdf->Cell(25, 7, utf8_decode('Debe'), 1, 0, 'R');
        $pdf->Cell(25, 7, utf8_decode('Haber'), 1, 0, 'R');
        $pdf->Cell(20, 7, utf8_decode('Saldo'), 1, 1, 'R');

        $pdf->SetFont('Arial', '', 8);
        foreach ($movimientos as $movimiento) {
            $pdf->Cell(30, 7, optional($movimiento->asiento->fecha_asiento)->format('d/m/Y') ?? '-', 1);
            $pdf->Cell(28, 7, utf8_decode($movimiento->asiento->numero_asiento ?? '-'), 1);
            $pdf->Cell(62, 7, utf8_decode(mb_strimwidth($movimiento->descripcion ?: '-', 0, 38, '...')), 1);
            $pdf->Cell(25, 7, number_format($movimiento->debe, 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(25, 7, number_format($movimiento->haber, 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(20, 7, number_format($movimiento->saldo_acumulado, 2, ',', '.'), 1, 1, 'R');
        }

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, utf8_decode('Saldo inicial: $' . number_format($resumen['saldo_inicial'], 2, ',', '.')), 0, 1);
        $pdf->Cell(0, 6, utf8_decode('Total debe: $' . number_format($resumen['total_debe'], 2, ',', '.')), 0, 1);
        $pdf->Cell(0, 6, utf8_decode('Total haber: $' . number_format($resumen['total_haber'], 2, ',', '.')), 0, 1);
        $pdf->Cell(0, 6, utf8_decode('Saldo actual: $' . number_format($resumen['saldo_actual'], 2, ',', '.')), 0, 1);

        return response($pdf->Output('S', 'estado-cuenta-particular.pdf'))
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Exportar estado de cuenta particular a Excel compatible
     */
    public function exportarEstadoParticularExcel(Request $request)
    {
        [$cuentas, $cuentaSeleccionada, $movimientos, $resumen, $desde, $hasta, $buscar] = $this->obtenerEstadoParticular($request, false);

        if (!$cuentaSeleccionada) {
            return redirect()
                ->route('admin.cuentas.particular')
                ->with('error', 'Debes seleccionar una cuenta para exportar.');
        }

        $filename = 'estado-cuenta-' . $cuentaSeleccionada->codigo . '.xls';

        $contenido = implode("\t", [
            'Fecha', 'Asiento', 'Concepto', 'Debe', 'Haber', 'Saldo'
        ]) . "\n";

        foreach ($movimientos as $movimiento) {
            $contenido .= implode("\t", [
                optional($movimiento->asiento->fecha_asiento)->format('d/m/Y') ?? '-',
                $movimiento->asiento->numero_asiento ?? '-',
                str_replace(["\t", "\n", "\r"], ' ', $movimiento->descripcion ?: '-'),
                number_format($movimiento->debe, 2, '.', ''),
                number_format($movimiento->haber, 2, '.', ''),
                number_format($movimiento->saldo_acumulado, 2, '.', ''),
            ]) . "\n";
        }

        $contenido .= "\n";
        $contenido .= "Saldo inicial\t" . number_format($resumen['saldo_inicial'], 2, '.', '') . "\n";
        $contenido .= "Total debe\t" . number_format($resumen['total_debe'], 2, '.', '') . "\n";
        $contenido .= "Total haber\t" . number_format($resumen['total_haber'], 2, '.', '') . "\n";
        $contenido .= "Saldo actual\t" . number_format($resumen['saldo_actual'], 2, '.', '') . "\n";

        return response($contenido, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Redirección de búsqueda para estado de cuenta particular
     */
    public function buscarEstadoParticular(Request $request)
    {
        $validated = $request->validate([
            'cuenta_id' => 'required|integer|exists:cuentas_contables,id',
            'buscar' => 'nullable|string|max:100',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);

        return redirect()->route('admin.cuentas.particular', [
            'cuenta_id' => $validated['cuenta_id'],
            'buscar' => $validated['buscar'] ?? null,
            'desde' => $validated['desde'] ?? Carbon::today()->startOfMonth()->toDateString(),
            'hasta' => $validated['hasta'] ?? Carbon::today()->toDateString(),
        ]);
    }

    private function obtenerEstadoParticular(Request $request, bool $paginado): array
    {
        $cuentas = CuentaContable::activas()
            ->imputables()
            ->orderBy('codigo')
            ->get();

        $cuentaId = $request->input('cuenta_id');
        $desde = $request->input('desde', Carbon::today()->startOfMonth()->toDateString());
        $hasta = $request->input('hasta', Carbon::today()->toDateString());
        $buscar = trim((string) $request->input('buscar', ''));

        $cuentaSeleccionada = null;
        $movimientos = $paginado ? collect() : collect();
        $resumen = null;

        if (!$cuentaId) {
            return [$cuentas, null, $movimientos, null, $desde, $hasta, $buscar];
        }

        $cuentaSeleccionada = CuentaContable::activas()->imputables()->find($cuentaId);

        if (!$cuentaSeleccionada) {
            return [$cuentas, null, collect(), null, $desde, $hasta, $buscar];
        }

        $consulta = MovimientoContable::where('cuenta_id', $cuentaSeleccionada->id)
            ->whereHas('asiento', function ($q) use ($desde, $hasta, $buscar) {
                $q->whereDate('fecha', '>=', $desde)
                  ->whereDate('fecha', '<=', $hasta);

                if ($buscar !== '') {
                    $q->where(function ($subQuery) use ($buscar) {
                        $subQuery->where('numero', 'like', '%' . $buscar . '%')
                            ->orWhere('concepto', 'like', '%' . $buscar . '%')
                            ->orWhere('observaciones', 'like', '%' . $buscar . '%');
                    });
                }
            })
            ->with(['asiento.usuario'])
            ->orderBy('id', 'asc');

        $totalDebe = (float) (clone $consulta)->sum('debe');
        $totalHaber = (float) (clone $consulta)->sum('haber');
        $saldoInicial = (float) $cuentaSeleccionada->saldo_inicial;

        $saldoActual = $cuentaSeleccionada->naturaleza === 'deudor'
            ? $saldoInicial + $totalDebe - $totalHaber
            : $saldoInicial + $totalHaber - $totalDebe;

        $movimientosBase = $paginado
            ? $consulta->paginate(20)->withQueryString()
            : $consulta->get();

        $saldoAcumulado = $saldoInicial;
        $movimientosCalculados = method_exists($movimientosBase, 'getCollection')
            ? $movimientosBase->getCollection()
            : $movimientosBase;

        $movimientosCalculados = $movimientosCalculados->map(function ($mov) use ($cuentaSeleccionada, &$saldoAcumulado) {
            $delta = $cuentaSeleccionada->naturaleza === 'deudor'
                ? ((float) $mov->debe - (float) $mov->haber)
                : ((float) $mov->haber - (float) $mov->debe);

            $saldoAcumulado += $delta;
            $mov->saldo_acumulado = $saldoAcumulado;

            return $mov;
        });

        if ($paginado && method_exists($movimientosBase, 'setCollection')) {
            $movimientosBase->setCollection($movimientosCalculados);
            $movimientos = $movimientosBase;
        } else {
            $movimientos = $movimientosCalculados;
        }

        $resumen = [
            'saldo_inicial' => $saldoInicial,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'saldo_actual' => $saldoActual,
            'movimientos' => $movimientosCalculados->count(),
        ];

        return [$cuentas, $cuentaSeleccionada, $movimientos, $resumen, $desde, $hasta, $buscar];
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
        return PuntoVenta::activo()
            ->select('id', 'codigo', 'nombre')
            ->get()
            ->map(function($punto) {
                $total_ingresos = Sale::where('punto_venta_id', $punto->id)->sum('total');
                $total_ventas = Sale::where('punto_venta_id', $punto->id)->count();

                return (object) [
                    'id' => $punto->id,
                    'codigo' => $punto->codigo,
                    'nombre' => $punto->nombre,
                    'total_ingresos' => $total_ingresos,
                    'total_ventas' => $total_ventas
                ];
            });
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
