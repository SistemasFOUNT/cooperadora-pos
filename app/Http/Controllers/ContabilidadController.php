<?php

namespace App\Http\Controllers;

use App\Models\CuentaContable;
use App\Models\AsientoContable;
use App\Models\MovimientoContable;
use App\Models\PuntoVenta;
use App\Services\ContabilidadService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContabilidadController extends Controller
{
    public function __construct(private ContabilidadService $contabilidad)
    {
        $this->middleware('auth');
    }

    public function planCuentas()
    {
        $cuentas = CuentaContable::with('padre', 'hijos')
                                ->orderBy('codigo')
                                ->get();

        // Organizar cuentas en estructura jerárquica
        $cuentasJerarquicas = $this->organizarJerarquia($cuentas);

        return view('contabilidad.plan-cuentas', compact('cuentasJerarquicas'));
    }

    private function organizarJerarquia($cuentas)
    {
        $cuentasPorNivel = $cuentas->groupBy('nivel');
        $resultado = [];

        // Empezar con las cuentas de nivel 1
        foreach ($cuentasPorNivel[1] ?? [] as $cuenta) {
            $resultado[] = [
                'cuenta' => $cuenta,
                'hijos' => $this->obtenerHijos($cuenta, $cuentas)
            ];
        }

        return $resultado;
    }

    private function obtenerHijos($cuentaPadre, $todasLasCuentas)
    {
        $hijos = [];

        foreach ($todasLasCuentas as $cuenta) {
            if ($cuenta->cuenta_padre_id == $cuentaPadre->id) {
                $hijos[] = [
                    'cuenta' => $cuenta,
                    'hijos' => $this->obtenerHijos($cuenta, $todasLasCuentas)
                ];
            }
        }

        return $hijos;
    }

    public function buscarCuentas(Request $request)
    {
        $busqueda = $request->get('q');

        $cuentas = CuentaContable::where('codigo', 'like', "%{$busqueda}%")
                                ->orWhere('nombre', 'like', "%{$busqueda}%")
                                ->activas()
                                ->imputables()
                                ->limit(20)
                                ->get();

        return response()->json($cuentas);
    }

    /**
     * Dashboard de contabilidad
     */
    public function dashboard()
    {
        $hoy = Carbon::today();

        // Asientos de hoy
        $asientosHoy = AsientoContable::whereDate('created_at', $hoy)->count();

        // Total de asientos en el mes
        $asientosMes = AsientoContable::whereMonth('created_at', $hoy->month)
                                       ->whereYear('created_at', $hoy->year)
                                       ->count();

        // Últimos asientos
        $ultimosAsientos = AsientoContable::latest('id')
                          ->with(['usuario', 'movimientos'])
                                          ->limit(10)
                                          ->get();

        // Balance del mes
        $balance = $this->contabilidad->obtenerBalanceComprobacion(
            $hoy->startOfMonth(),
            $hoy->endOfMonth()
        );

        return view('admin.contable.dashboard', compact(
            'asientosHoy',
            'asientosMes',
            'ultimosAsientos',
            'balance'
        ));
    }

    /**
     * Listado de asientos contables
     */
    public function asientos(Request $request)
    {
        $query = AsientoContable::with(['usuario', 'venta']);

        // Filtro por punto de venta
        if ($request->filled('punto_venta_id')) {
            $query->where('punto_venta_id', $request->punto_venta_id);
        }

        // Filtro por fecha
        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda por número de asiento
        if ($request->filled('buscar')) {
            $query->where('numero', 'like', '%' . $request->buscar . '%')
                  ->orWhere('concepto', 'like', '%' . $request->buscar . '%');
        }

        $asientos = $query->orderBy('numero', 'desc')
                          ->paginate(20);

        $puntos = PuntoVenta::all();

        return view('admin.contable.asientos', compact('asientos', 'puntos'));
    }

    /**
     * Ver detalle de un asiento
     */
    public function verAsiento(AsientoContable $asiento)
    {
        $asiento->load(['movimientos.cuenta', 'usuario', 'venta']);

        return view('admin.contable.ver-asiento', compact('asiento'));
    }

    /**
     * Balance de comprobación
     */
    public function balanceComprobacion(Request $request)
    {
        $desde = $request->input('desde', Carbon::today()->startOfMonth());
        $hasta = $request->input('hasta', Carbon::today());

        $balance = $this->contabilidad->obtenerBalanceComprobacion($desde, $hasta);

        return view('admin.contable.balance-comprobacion', compact('balance', 'desde', 'hasta'));
    }

    /**
     * Libro Mayor
     */
    public function libroMayor(Request $request)
    {
        $cuentaId = $request->input('cuenta_id');
        $desde = $request->input('desde', Carbon::today()->startOfMonth());
        $hasta = $request->input('hasta', Carbon::today());

        $cuentas = CuentaContable::activas()->get();

        if ($cuentaId) {
            $cuenta = CuentaContable::find($cuentaId);

            if (!$cuenta) {
                abort(404, 'Cuenta no encontrada');
            }

            $movimientos = MovimientoContable::where('cuenta_id', $cuentaId)
                                             ->whereBetween('created_at', [$desde, $hasta])
                                             ->with('asiento')
                                             ->orderBy('created_at', 'asc')
                                             ->paginate(50);

            $saldo = $this->contabilidad->obtenerSaldoCuenta($cuenta, $desde, $hasta);
        } else {
            $movimientos = null;
            $saldo = null;
        }

        return view('admin.contable.libro-mayor', compact(
            'cuentas',
            'cuenta',
            'movimientos',
            'saldo',
            'desde',
            'hasta'
        ));
    }

    /**
     * Estado de Cuentas
     */
    public function estadoCuentas(Request $request)
    {
        $desde = $request->input('desde', Carbon::today()->startOfMonth());
        $hasta = $request->input('hasta', Carbon::today());
        $puntoId = $request->input('punto_venta_id');

        $puntos = PuntoVenta::all();
        $cuentas = CuentaContable::activas()->get();

        $estado = [];
        foreach ($cuentas as $cuenta) {
            $saldo = $this->contabilidad->obtenerSaldoCuenta($cuenta, $desde, $hasta);
            if ($saldo['saldo_actual'] != 0) {
                $estado[] = $saldo;
            }
        }

        return view('admin.contable.estado-cuentas', compact('estado', 'desde', 'hasta', 'puntos'));
    }

    /**
     * Reporte de Ventas
     */
    public function reporteVentas(Request $request)
    {
        $desde = $request->input('desde', Carbon::today()->startOfMonth());
        $hasta = $request->input('hasta', Carbon::today());
        $puntoId = $request->input('punto_venta_id');

        $query = AsientoContable::whereBetween('fecha', [$desde, $hasta])
                    ->where('estado', 'confirmado');

        if ($puntoId) {
            $query->where('punto_venta_id', $puntoId);
        }

        $asientos = $query->with(['movimientos.cuenta'])
                         ->get();

        // Agrupar por tipo de operación
        $reportePorTipo = [];
        $totalVentas = 0;

        foreach ($asientos as $asiento) {
            foreach ($asiento->movimientos as $mov) {
                if ($mov->cuenta->codigo >= 4100 && $mov->cuenta->codigo < 4200) {
                    $tipo = $mov->cuenta->nombre;
                    if (!isset($reportePorTipo[$tipo])) {
                        $reportePorTipo[$tipo] = 0;
                    }
                    $reportePorTipo[$tipo] += $mov->haber;
                    $totalVentas += $mov->haber;
                }
            }
        }

        $puntos = PuntoVenta::all();

        return view('admin.contable.reporte-ventas', compact(
            'reportePorTipo',
            'totalVentas',
            'desde',
            'hasta',
            'puntos'
        ));
    }

    /**
     * Libro Diario - Registro cronológico de todos los asientos
     */
    public function libroDiario(Request $request)
    {
        $desde = $request->input('desde', Carbon::today()->startOfMonth());
        $hasta = $request->input('hasta', Carbon::today());

        $libroDiario = $this->contabilidad->obtenerLibroDiario($desde, $hasta);

        return view('admin.contable.libro-diario', compact('libroDiario', 'desde', 'hasta'));
    }

    /**
     * Libro Caja - Movimientos de efectivo
     */
    public function libroCaja(Request $request)
    {
        $desde = $request->input('desde', Carbon::today()->startOfMonth());
        $hasta = $request->input('hasta', Carbon::today());
        $puntoId = $request->input('punto_venta_id');

        $libroCaja = $this->contabilidad->obtenerLibroCaja($puntoId, $desde, $hasta);
        $puntos = PuntoVenta::all();

        return view('admin.contable.libro-caja', compact('libroCaja', 'puntos', 'desde', 'hasta'));
    }

    /**
     * Libro Banco - Movimientos bancarios
     */
    public function libroBanco(Request $request)
    {
        $desde = $request->input('desde', Carbon::today()->startOfMonth());
        $hasta = $request->input('hasta', Carbon::today());

        $libroBanco = $this->contabilidad->obtenerLibroBanco($desde, $hasta);

        return view('admin.contable.libro-banco', compact('libroBanco', 'desde', 'hasta'));
    }

    /**
     * Resumen de Caja - Estado actual
     */
    public function resumenCaja(Request $request)
    {
        $desde = $request->input('desde', Carbon::today()->startOfMonth());
        $hasta = $request->input('hasta', Carbon::today());

        $resumen = $this->contabilidad->obtenerResumenCaja($desde, $hasta);

        return view('admin.contable.resumen-caja', compact('resumen', 'desde', 'hasta'));
    }
}
