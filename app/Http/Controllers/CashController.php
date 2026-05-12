<?php

namespace App\Http\Controllers;

use App\Models\ArqueoCaja;
use App\Models\PuntoVenta;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CashController extends Controller
{
    /**
     * Codigos válidos de puntos de venta
     */
    private const CAJAS = [
        'BOX'       => 'BOX Cooperadora',
        'POSTGRADO' => 'Postgrado',
        'ODONTO'    => 'Centro Odontológico',
    ];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PANEL SELECTOR DE CAJA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Panel principal: el admin elige sobre qué caja hacer el arqueo
     */
    public function index()
    {
        $cajas = [];

        foreach (self::CAJAS as $codigo => $nombre) {
            $puntoVenta = PuntoVenta::where('codigo', $codigo)->first();

            if (!$puntoVenta) {
                continue;
            }

            $ultimoArqueo = ArqueoCaja::where('punto_venta_id', $puntoVenta->id)
                ->orderBy('fecha_arqueo', 'desc')
                ->first();

            $ventasHoy = Sale::where('punto_venta_id', $puntoVenta->id)
                ->whereDate('created_at', Carbon::today())
                ->count();

            $recaudacionHoy = Sale::where('punto_venta_id', $puntoVenta->id)
                ->whereDate('created_at', Carbon::today())
                ->sum('total');

            $cajas[$codigo] = [
                'codigo'         => $codigo,
                'nombre'         => $nombre,
                'punto_venta'    => $puntoVenta,
                'ultimo_arqueo'  => $ultimoArqueo,
                'ventas_hoy'     => $ventasHoy,
                'recaudacion_hoy'=> $recaudacionHoy,
                'total_arqueos'  => ArqueoCaja::where('punto_venta_id', $puntoVenta->id)->count(),
            ];
        }

        return view('admin.arqueo.index', compact('cajas'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HISTORIAL DE ARQUEOS DE UNA CAJA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lista de arqueos de una caja específica
     */
    public function caja(string $codigo)
    {
        $codigo = strtoupper($codigo);
        abort_unless(array_key_exists($codigo, self::CAJAS), 404);

        $puntoVenta = PuntoVenta::where('codigo', $codigo)->firstOrFail();

        $arqueos = ArqueoCaja::where('punto_venta_id', $puntoVenta->id)
            ->with('user')
            ->orderBy('fecha_arqueo', 'desc')
            ->paginate(20);

        $resumen = [
            'total_arqueos'      => ArqueoCaja::where('punto_venta_id', $puntoVenta->id)->count(),
            'arqueos_con_diff'   => ArqueoCaja::where('punto_venta_id', $puntoVenta->id)->where('diferencia', '!=', 0)->count(),
            'suma_diferencias'   => ArqueoCaja::where('punto_venta_id', $puntoVenta->id)->sum('diferencia'),
        ];

        return view('admin.arqueo.caja', [
            'puntoVenta' => $puntoVenta,
            'nombre_caja'=> self::CAJAS[$codigo],
            'codigo'     => $codigo,
            'arqueos'    => $arqueos,
            'resumen'    => $resumen,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREAR ARQUEO
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Formulario para nuevo arqueo — precalcula los totales del período
     */
    public function crear(string $codigo)
    {
        $codigo = strtoupper($codigo);
        abort_unless(array_key_exists($codigo, self::CAJAS), 404);

        $puntoVenta = PuntoVenta::where('codigo', $codigo)->firstOrFail();

        // Período por defecto: hoy
        $periodoDesde = request('periodo_desde', Carbon::today()->format('Y-m-d') . ' 00:00:00');
        $periodoHasta = request('periodo_hasta', Carbon::now()->format('Y-m-d H:i:s'));

        // Calcular totales desde ventas del período
        $ventas = Sale::where('punto_venta_id', $puntoVenta->id)
            ->whereBetween('created_at', [$periodoDesde, $periodoHasta])
            ->get();

        $totalesCalculados = $this->calcularTotalesPorMetodo($ventas);

        return view('admin.arqueo.crear', [
            'puntoVenta'         => $puntoVenta,
            'nombre_caja'        => self::CAJAS[$codigo],
            'codigo'             => $codigo,
            'periodo_desde'      => $periodoDesde,
            'periodo_hasta'      => $periodoHasta,
            'totales_calculados' => $totalesCalculados,
            'cantidad_ventas'    => $ventas->count(),
        ]);
    }

    /**
     * Guardar nuevo arqueo
     */
    public function guardar(Request $request, string $codigo)
    {
        $codigo = strtoupper($codigo);
        abort_unless(array_key_exists($codigo, self::CAJAS), 404);

        $request->validate([
            'periodo_desde'              => 'required|date',
            'periodo_hasta'              => 'required|date|after_or_equal:periodo_desde',
            'total_efectivo_declarado'   => 'required|numeric|min:0',
            'total_tarjeta_declarado'    => 'required|numeric|min:0',
            'total_transferencia_declarado' => 'required|numeric|min:0',
            'observaciones'              => 'nullable|string|max:1000',
        ]);

        $puntoVenta = PuntoVenta::where('codigo', $codigo)->firstOrFail();

        // Recalcular totales del sistema para el período indicado
        $ventas = Sale::where('punto_venta_id', $puntoVenta->id)
            ->whereBetween('created_at', [$request->periodo_desde, $request->periodo_hasta])
            ->get();

        $totalesCalculados = $this->calcularTotalesPorMetodo($ventas);

        $efectivoDeclarado      = (float) $request->total_efectivo_declarado;
        $tarjetaDeclarada       = (float) $request->total_tarjeta_declarado;
        $transferenciaDeclarada = (float) $request->total_transferencia_declarado;
        $totalDeclarado         = $efectivoDeclarado + $tarjetaDeclarada + $transferenciaDeclarada;
        $diferencia             = $totalDeclarado - $totalesCalculados['total'];

        $diferenciasPorMetodo = [
            'efectivo' => round($efectivoDeclarado - $totalesCalculados['efectivo'], 2),
            'tarjeta' => round($tarjetaDeclarada - $totalesCalculados['tarjeta'], 2),
            'transferencia' => round($transferenciaDeclarada - $totalesCalculados['transferencia'], 2),
        ];

        $metodosConDiferencia = array_filter(
            $diferenciasPorMetodo,
            fn ($valor) => abs((float) $valor) >= 0.01
        );

        $arqueo = ArqueoCaja::create([
            'punto_venta_id'                 => $puntoVenta->id,
            'user_id'                        => Auth::id(),
            'fecha_arqueo'                   => Carbon::now(),
            'periodo_desde'                  => $request->periodo_desde,
            'periodo_hasta'                  => $request->periodo_hasta,
            'total_efectivo_calculado'       => $totalesCalculados['efectivo'],
            'total_tarjeta_calculado'        => $totalesCalculados['tarjeta'],
            'total_transferencia_calculado'  => $totalesCalculados['transferencia'],
            'total_calculado'                => $totalesCalculados['total'],
            'total_efectivo_declarado'       => $efectivoDeclarado,
            'total_tarjeta_declarado'        => $tarjetaDeclarada,
            'total_transferencia_declarado'  => $transferenciaDeclarada,
            'total_declarado'                => $totalDeclarado,
            'diferencia'                     => $diferencia,
            'cantidad_transacciones'         => $ventas->count(),
            'estado'                         => 'abierto',
            'observaciones'                  => $request->observaciones,
        ]);

        $redirect = redirect()
            ->route('admin.arqueo.show', [$codigo, $arqueo->id])
            ->with('success', 'Arqueo de caja registrado correctamente.');

        if (!empty($metodosConDiferencia)) {
            $partes = [];
            foreach ($metodosConDiferencia as $metodo => $valor) {
                $signo = $valor > 0 ? '+' : '';
                $partes[] = ucfirst($metodo) . ': ' . $signo . number_format((float) $valor, 2, ',', '.');
            }

            $redirect->with('warning', 'Se detectaron diferencias por método de pago: ' . implode(' | ', $partes));
        }

        return $redirect;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VER DETALLE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Detalle de un arqueo específico
     */
    public function show(string $codigo, int $id)
    {
        $codigo = strtoupper($codigo);
        abort_unless(array_key_exists($codigo, self::CAJAS), 404);

        $puntoVenta = PuntoVenta::where('codigo', $codigo)->firstOrFail();

        $arqueo = ArqueoCaja::where('punto_venta_id', $puntoVenta->id)
            ->with('user', 'puntoVenta')
            ->findOrFail($id);

        // Ventas del período del arqueo para mostrar detalle
        $ventas = Sale::where('punto_venta_id', $puntoVenta->id)
            ->whereBetween('created_at', [$arqueo->periodo_desde, $arqueo->periodo_hasta])
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $diferenciasPorMetodo = [
            'efectivo' => round((float) $arqueo->total_efectivo_declarado - (float) $arqueo->total_efectivo_calculado, 2),
            'tarjeta' => round((float) $arqueo->total_tarjeta_declarado - (float) $arqueo->total_tarjeta_calculado, 2),
            'transferencia' => round((float) $arqueo->total_transferencia_declarado - (float) $arqueo->total_transferencia_calculado, 2),
        ];

        $metodosConDiferencia = array_filter(
            $diferenciasPorMetodo,
            fn ($valor) => abs((float) $valor) >= 0.01
        );

        return view('admin.arqueo.show', [
            'puntoVenta'  => $puntoVenta,
            'nombre_caja' => self::CAJAS[$codigo],
            'codigo'      => $codigo,
            'arqueo'      => $arqueo,
            'ventas'      => $ventas,
            'diferencias_por_metodo' => $diferenciasPorMetodo,
            'metodos_con_diferencia' => $metodosConDiferencia,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CERRAR ARQUEO
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cerrar un arqueo (ya no se puede modificar)
     */
    public function cerrar(string $codigo, int $id)
    {
        $codigo = strtoupper($codigo);
        abort_unless(array_key_exists($codigo, self::CAJAS), 404);

        $puntoVenta = PuntoVenta::where('codigo', $codigo)->firstOrFail();

        $arqueo = ArqueoCaja::where('punto_venta_id', $puntoVenta->id)
            ->findOrFail($id);

        abort_if($arqueo->estado === 'cerrado', 422, 'El arqueo ya está cerrado.');

        $arqueo->update([
            'estado'     => 'cerrado',
            'cerrado_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('admin.arqueo.show', [$codigo, $arqueo->id])
            ->with('success', 'Arqueo cerrado correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcula los totales de ventas agrupados por método de pago.
     * Usa el campo additional_data->metodo_pago guardado al momento de la venta.
     */
    private function calcularTotalesPorMetodo($ventas): array
    {
        $efectivo      = 0;
        $tarjeta       = 0;
        $transferencia = 0;
        $metodosPagoMap = $this->obtenerMapaMetodosPago();

        foreach ($ventas as $venta) {
            $metodo = null;

            // 1. Intentar leer del campo additional_data (donde BoxController guarda el método)
            if (!empty($venta->additional_data['metodo_pago'])) {
                $metodo = $venta->additional_data['metodo_pago'];
            }
            // 2. Fallback: clasificar por payment_method_id contra metodos_pago (si existe)
            elseif (!empty($venta->payment_method_id) && isset($metodosPagoMap[$venta->payment_method_id])) {
                $metodo = $metodosPagoMap[$venta->payment_method_id];
            }

            $total = (float) $venta->total;

            match ($metodo) {
                'tarjeta'       => $tarjeta       += $total,
                'transferencia' => $transferencia += $total,
                default         => $efectivo      += $total,  // efectivo o sin clasificar
            };
        }

        return [
            'efectivo'      => round($efectivo, 2),
            'tarjeta'       => round($tarjeta, 2),
            'transferencia' => round($transferencia, 2),
            'total'         => round($efectivo + $tarjeta + $transferencia, 2),
        ];
    }

    /**
     * Construye un mapa [id_metodo_pago => categoria] usando la tabla metodos_pago.
     * Categorías: efectivo, tarjeta, transferencia.
     */
    private function obtenerMapaMetodosPago(): array
    {
        if (!Schema::hasTable('metodos_pago')) {
            return [];
        }

        $rows = DB::table('metodos_pago')->select('id', 'name', 'type')->get();
        $map = [];

        foreach ($rows as $row) {
            $nombre = strtolower((string) ($row->name ?? ''));
            $type = strtolower((string) ($row->type ?? ''));

            if (str_contains($nombre, 'transfer') || $type === 'transfer') {
                $map[$row->id] = 'transferencia';
                continue;
            }

            if (str_contains($nombre, 'tarjeta') || str_contains($nombre, 'card') || $type === 'card') {
                $map[$row->id] = 'tarjeta';
                continue;
            }

            $map[$row->id] = 'efectivo';
        }

        return $map;
    }
}

