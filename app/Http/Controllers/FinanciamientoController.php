<?php

namespace App\Http\Controllers;

use App\Models\FinanciamientoOdontologia;
use App\Models\ClienteDeudor;
use App\Models\CuotaFinanciamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanciamientoController extends Controller
{
    /**
     * Crear nuevo financiamiento
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_deudor_id' => 'required|exists:clientes_deudores,id',
            'monto_total' => 'required|numeric|min:1000',
            'cantidad_cuotas' => 'required|integer|in:3,6,12',
            'tasa_interes_anual' => 'numeric|min:0|max:50',
            'fecha_primera_cuota' => 'required|date|after:today',
            'servicios_detalle' => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            $cliente = ClienteDeudor::find($request->cliente_deudor_id);

            // Validar crédito
            if (!$cliente->puedeAccederCredito($request->monto_total)) {
                throw new \Exception('Cliente no puede acceder al crédito solicitado');
            }

            // Calcular cuota
            $montoCuota = $this->calcularMontoCuota(
                $request->monto_total,
                $request->cantidad_cuotas,
                $request->tasa_interes_anual ?? 0
            );

            // Calcular fecha última cuota
            $fechaUltimaCuota = Carbon::parse($request->fecha_primera_cuota)
                ->addMonths($request->cantidad_cuotas - 1);

            // Crear financiamiento
            $financiamiento = FinanciamientoOdontologia::create([
                'cliente_deudor_id' => $request->cliente_deudor_id,
                'monto_total' => $request->monto_total,
                'cantidad_cuotas' => $request->cantidad_cuotas,
                'monto_cuota' => $montoCuota,
                'tasa_interes_anual' => $request->tasa_interes_anual ?? 0,
                'fecha_inicio' => now(),
                'fecha_primera_cuota' => $request->fecha_primera_cuota,
                'fecha_ultima_cuota' => $fechaUltimaCuota,
                'servicios_detalle' => $request->servicios_detalle,
                'usuario_creacion_id' => Auth::id(),
                'supervisor_aprobacion_id' => $request->monto_total > 100000 ? null : Auth::id(),
                'estado' => 'pendiente_documentacion'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Financiamiento creado exitosamente',
                'financiamiento_id' => $financiamiento->id,
                'numero_financiamiento' => $financiamiento->numero_financiamiento,
                'requiere_aprobacion' => $request->monto_total > 100000
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear financiamiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular monto de cuota con interés
     */
    private function calcularMontoCuota($montoTotal, $cantidadCuotas, $tasaAnual = 0)
    {
        if ($tasaAnual == 0) {
            return $montoTotal / $cantidadCuotas;
        }

        $tasaMensual = $tasaAnual / 12 / 100;
        $factor = pow(1 + $tasaMensual, $cantidadCuotas);

        return $montoTotal * ($tasaMensual * $factor) / ($factor - 1);
    }

    /**
     * Obtener planes de financiamiento disponibles
     */
    public function obtenerPlanes(Request $request)
    {
        $request->validate([
            'monto_total' => 'required|numeric|min:1000'
        ]);

        $montoTotal = $request->monto_total;

        $planes = [
            [
                'cuotas' => 3,
                'tasa_interes' => 0,
                'monto_cuota' => round($montoTotal / 3, 2),
                'total_a_pagar' => $montoTotal,
                'descripcion' => '3 cuotas sin interés'
            ],
            [
                'cuotas' => 6,
                'tasa_interes' => 5.0,
                'monto_cuota' => round($this->calcularMontoCuota($montoTotal, 6, 5.0), 2),
                'total_a_pagar' => round($this->calcularMontoCuota($montoTotal, 6, 5.0) * 6, 2),
                'descripcion' => '6 cuotas con 5% interés anual'
            ],
            [
                'cuotas' => 12,
                'tasa_interes' => 8.0,
                'monto_cuota' => round($this->calcularMontoCuota($montoTotal, 12, 8.0), 2),
                'total_a_pagar' => round($this->calcularMontoCuota($montoTotal, 12, 8.0) * 12, 2),
                'descripcion' => '12 cuotas con 8% interés anual'
            ]
        ];

        // Generar fechas de vencimiento
        $fechaInicio = Carbon::now()->addDays(30); // Primera cuota en 30 días

        foreach ($planes as &$plan) {
            $plan['vencimientos'] = [];
            for ($i = 0; $i < $plan['cuotas']; $i++) {
                $plan['vencimientos'][] = $fechaInicio->copy()->addMonths($i)->format('d/m/Y');
            }
        }

        return response()->json([
            'planes' => $planes,
            'monto_total' => $montoTotal
        ]);
    }

    /**
     * Aprobar financiamiento (supervisores)
     */
    public function aprobar(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'nullable|string'
        ]);

        try {
            $financiamiento = FinanciamientoOdontologia::findOrFail($id);

            $financiamiento->update([
                'estado' => 'activo',
                'supervisor_aprobacion_id' => Auth::id(),
                'fecha_aprobacion' => now(),
                'observaciones' => $request->observaciones
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Financiamiento aprobado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar financiamiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar financiamientos
     */
    public function index(Request $request)
    {
        $query = FinanciamientoOdontologia::with(['clienteDeudor', 'cuotas']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('cliente_dni')) {
            $query->whereHas('clienteDeudor', function($q) use ($request) {
                $q->where('dni', 'like', '%' . $request->cliente_dni . '%');
            });
        }

        if ($request->has('numero_financiamiento')) {
            $query->where('numero_financiamiento', 'like', '%' . $request->numero_financiamiento . '%');
        }

        $financiamientos = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($financiamientos);
    }

    /**
     * Obtener detalles del financiamiento
     */
    public function show($id)
    {
        $financiamiento = FinanciamientoOdontologia::with([
            'clienteDeudor',
            'cuotas' => function($query) {
                $query->orderBy('numero_cuota');
            },
            'documentosLegales',
            'usuarioCreacion',
            'supervisorAprobacion'
        ])->findOrFail($id);

        return response()->json([
            'financiamiento' => $financiamiento,
            'resumen' => [
                'monto_pagado' => $financiamiento->cuotas->where('estado', 'pagada')->sum('monto_pagado'),
                'monto_pendiente' => $financiamiento->montoPendiente(),
                'cuotas_pagadas' => $financiamiento->cuotas->where('estado', 'pagada')->count(),
                'cuotas_vencidas' => $financiamiento->cuotas->where('estado', 'vencida')->count(),
                'proxima_cuota' => $financiamiento->proximaCuota()
            ]
        ]);
    }

    /**
     * Cancelar financiamiento
     */
    public function cancelar(Request $request, $id)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:255'
        ]);

        try {
            $financiamiento = FinanciamientoOdontologia::findOrFail($id);

            if ($financiamiento->estado === 'completado') {
                throw new \Exception('No se puede cancelar un financiamiento completado');
            }

            $financiamiento->update([
                'estado' => 'cancelado',
                'motivo_cancelacion' => $request->motivo_cancelacion
            ]);

            // Cancelar cuotas pendientes
            $financiamiento->cuotas()->whereIn('estado', ['pendiente', 'vencida'])
                ->update(['estado' => 'condonada']);

            return response()->json([
                'success' => true,
                'message' => 'Financiamiento cancelado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar financiamiento: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================================================================
    // MÉTODO ESPECÍFICO PARA CUOTAS INTERNAS DESDE EL MÓDULO DE PAGOS
    // =============================================================================

    /**
     * Crear financiamiento desde el modal de cuotas internas
     */
    public function crear(Request $request)
    {
        try {
            $request->validate([
                'cliente_deudor_id' => 'required|exists:clientes_deudores,id',
                'venta_original' => 'required|array',
                'venta_original.total' => 'required|numeric|min:100',
                'cantidad_cuotas' => 'required|integer|in:3,6,9,12',
                'fecha_primera_cuota' => 'required|date',
                'monto_total_con_interes' => 'required|numeric',
                'valor_cada_cuota' => 'required|numeric',
                'detalle_tratamiento' => 'required|string'
            ]);

            DB::beginTransaction();

            $cliente = ClienteDeudor::find($request->cliente_deudor_id);

            // Validar límite de crédito
            $deudaTotal = $cliente->deudaTotal() + $request->monto_total_con_interes;
            if ($cliente->limite_credito > 0 && $deudaTotal > $cliente->limite_credito) {
                throw new \Exception('El financiamiento excede el límite de crédito del cliente');
            }

            // Calcular tasa de interés según cantidad de cuotas
            $tasasInteres = [
                3 => 30,   // 30%
                6 => 45,   // 45%
                9 => 60,   // 60%
                12 => 75   // 75%
            ];

            $tasaInteres = $tasasInteres[$request->cantidad_cuotas];

            // Crear financiamiento
            $financiamiento = FinanciamientoOdontologia::create([
                'cliente_deudor_id' => $request->cliente_deudor_id,
                'monto_original' => $request->venta_original['total'],
                'monto_total_con_interes' => $request->monto_total_con_interes,
                'cantidad_cuotas' => $request->cantidad_cuotas,
                'monto_por_cuota' => $request->valor_cada_cuota,
                'tasa_interes_aplicada' => $tasaInteres,
                'fecha_primera_cuota' => $request->fecha_primera_cuota,
                'fecha_ultima_cuota' => Carbon::parse($request->fecha_primera_cuota)
                    ->addMonths($request->cantidad_cuotas - 1),
                'detalle_servicios' => $request->detalle_tratamiento,
                'estado' => 'activo',
                'empleado_responsable_id' => Auth::id(),
                'datos_venta_original' => json_encode($request->venta_original)
            ]);

            // Generar cuotas automáticamente
            $fechaCuota = Carbon::parse($request->fecha_primera_cuota);

            for ($i = 1; $i <= $request->cantidad_cuotas; $i++) {
                CuotaFinanciamiento::create([
                    'financiamiento_id' => $financiamiento->id,
                    'numero_cuota' => $i,
                    'monto_cuota' => $request->valor_cada_cuota,
                    'fecha_vencimiento' => $fechaCuota->copy(),
                    'estado' => 'pendiente'
                ]);

                $fechaCuota->addMonth();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Financiamiento creado exitosamente',
                'financiamiento' => [
                    'id' => $financiamiento->id,
                    'monto_total_con_interes' => $financiamiento->monto_total_con_interes,
                    'cantidad_cuotas' => $financiamiento->cantidad_cuotas,
                    'valor_cuota' => $financiamiento->monto_por_cuota,
                    'fecha_primera_cuota' => $financiamiento->fecha_primera_cuota,
                    'estado' => $financiamiento->estado
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear financiamiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información de un financiamiento específico
     */
    public function obtener($id)
    {
        try {
            $financiamiento = FinanciamientoOdontologia::with([
                'clienteDeudor',
                'cuotas' => function($query) {
                    $query->orderBy('numero_cuota');
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'financiamiento' => $financiamiento
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Financiamiento no encontrado'
            ], 404);
        }
    }
}
