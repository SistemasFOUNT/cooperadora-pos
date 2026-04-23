<?php

namespace App\Http\Controllers;

use App\Models\ClienteDeudor;
use App\Models\FinanciamientoOdontologia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClienteDeudorController extends Controller
{
    /**
     * Buscar cliente por DNI
     */
    public function buscarPorDni(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|max:10'
        ]);

        $cliente = ClienteDeudor::where('dni', $request->dni)->first();

        if ($cliente) {
            return response()->json([
                'encontrado' => true,
                'cliente' => $cliente,
                'historial_financiamientos' => $cliente->financiamientos()
                    ->with('cuotas')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
                'deuda_actual' => $cliente->deudaTotal(),
                'limite_disponible' => $cliente->limiteDisponible(),
                'puede_acceder_credito' => $cliente->estado === 'activo'
            ]);
        }

        return response()->json(['encontrado' => false]);
    }

    /**
     * Registrar nuevo cliente deudor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|max:10|unique:clientes_deudores',
            'apellido' => 'required|string|max:100',
            'nombre' => 'required|string|max:100',
            'telefono_principal' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'domicilio_calle' => 'required|string|max:255',
            'domicilio_numero' => 'required|string|max:10',
            'domicilio_piso' => 'nullable|string|max:10',
            'domicilio_depto' => 'nullable|string|max:10',
            'localidad' => 'required|string|max:100',
            'provincia' => 'required|string|max:50',
            'codigo_postal' => 'nullable|string|max:10',
            'telefono_secundario' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'estado_civil' => 'nullable|in:soltero,casado,divorciado,viudo,concubinato',
            'profesion' => 'nullable|string|max:100',
            'lugar_trabajo' => 'nullable|string|max:255',
            'telefono_trabajo' => 'nullable|string|max:20',
            'ingresos_mensuales' => 'nullable|numeric|min:0',
            'referencia_nombre' => 'nullable|string|max:100',
            'referencia_telefono' => 'nullable|string|max:20',
            'referencia_relacion' => 'nullable|string|max:50',
            'limite_credito' => 'nullable|numeric|min:0|max:500000',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cliente = ClienteDeudor::create([
                ...$request->all(),
                'usuario_registro_id' => Auth::id(),
                'limite_credito' => $request->limite_credito ?? 50000.00,
                'estado' => 'activo'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cliente registrado exitosamente',
                'cliente' => $cliente
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar datos del cliente
     */
    public function update(Request $request, $id)
    {
        $cliente = ClienteDeudor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|max:10|unique:clientes_deudores,dni,' . $id,
            'apellido' => 'required|string|max:100',
            'nombre' => 'required|string|max:100',
            'telefono_principal' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'domicilio_calle' => 'required|string|max:255',
            'domicilio_numero' => 'required|string|max:10',
            'localidad' => 'required|string|max:100',
            'provincia' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cliente->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'cliente' => $cliente
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar disponibilidad crediticia
     */
    public function validarCredito(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes_deudores,id',
            'monto_solicitado' => 'required|numeric|min:0'
        ]);

        $cliente = ClienteDeudor::find($request->cliente_id);
        $montoSolicitado = $request->monto_solicitado;

        // Verificaciones
        $puedeAcceder = $cliente->puedeAccederCredito($montoSolicitado);
        $deudaActual = $cliente->deudaTotal();
        $disponible = $cliente->limiteDisponible();

        $observaciones = [];

        if ($cliente->estado !== 'activo') {
            $observaciones[] = 'Cliente no activo';
        }

        if ($montoSolicitado > $disponible) {
            $observaciones[] = 'Monto excede límite disponible';
        }

        if ($cliente->tieneFinanciamientosMorosos()) {
            $observaciones[] = 'Cliente tiene financiamientos morosos';
        }

        return response()->json([
            'aprobado' => $puedeAcceder,
            'monto_solicitado' => $montoSolicitado,
            'limite_credito' => $cliente->limite_credito,
            'deuda_actual' => $deudaActual,
            'disponible' => $disponible,
            'observaciones' => $observaciones,
            'requiere_supervision' => $montoSolicitado > 100000
        ]);
    }

    /**
     * Listar clientes con filtros
     */
    public function index(Request $request)
    {
        $query = ClienteDeudor::with(['financiamientos']);

        // Filtros
        if ($request->has('dni')) {
            $query->where('dni', 'like', '%' . $request->dni . '%');
        }

        if ($request->has('nombre')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->nombre . '%')
                  ->orWhere('apellido', 'like', '%' . $request->nombre . '%');
            });
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $clientes = $query->paginate(20);

        return response()->json($clientes);
    }

    /**
     * Obtener detalles del cliente
     */
    public function show($id)
    {
        $cliente = ClienteDeudor::with([
            'financiamientos.cuotas',
            'documentosLegales',
            'usuarioRegistro'
        ])->findOrFail($id);

        return response()->json([
            'cliente' => $cliente,
            'resumen_crediticio' => [
                'deuda_total' => $cliente->deudaTotal(),
                'limite_disponible' => $cliente->limiteDisponible(),
                'financiamientos_activos' => $cliente->financiamientos()->where('estado', 'activo')->count(),
                'tiene_mora' => $cliente->tieneFinanciamientosMorosos()
            ]
        ]);
    }

    // =============================================================================
    // MÉTODOS ESPECÍFICOS PARA EL SISTEMA DE CUOTAS INTERNAS
    // =============================================================================

    /**
     * Buscar clientes para autocompletado en el modal de cuotas internas
     */
    public function buscar(Request $request)
    {
        try {
            $termino = $request->input('termino');

            if (strlen($termino) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ingrese al menos 3 caracteres para buscar'
                ]);
            }

            $clientes = ClienteDeudor::where(function($query) use ($termino) {
                $query->where('dni', 'LIKE', "%{$termino}%")
                    ->orWhere('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('apellido', 'ILIKE', "%{$termino}%")
                    ->orWhereRaw("CONCAT(nombre, ' ', apellido) ILIKE ?", ["%{$termino}%"]);
            })
            ->where('estado', 'activo')
            ->orderBy('apellido')
            ->limit(10)
            ->get();

            // Calcular datos adicionales para cada cliente
            $clientesConDatos = $clientes->map(function($cliente) {
                return [
                    'id' => $cliente->id,
                    'dni' => $cliente->dni,
                    'nombre' => $cliente->nombre,
                    'apellido' => $cliente->apellido,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono,
                    'limite_credito' => $cliente->limite_credito,
                    'deuda_total_actual' => $cliente->deudaTotal(),
                    'limite_disponible' => $cliente->limiteDisponible(),
                ];
            });

            return response()->json([
                'success' => true,
                'clientes' => $clientesConDatos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cliente específico para validación en cuotas internas
     */
    public function obtener($id)
    {
        try {
            $cliente = ClienteDeudor::with(['financiamientos' => function($query) {
                $query->where('estado', '!=', 'cancelado');
            }])->findOrFail($id);

            // Agregar datos calculados
            $clienteConDatos = [
                'id' => $cliente->id,
                'dni' => $cliente->dni,
                'nombre' => $cliente->nombre,
                'apellido' => $cliente->apellido,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
                'direccion' => $cliente->direccion,
                'limite_credito' => $cliente->limite_credito,
                'deuda_total_actual' => $cliente->deudaTotal(),
                'limite_disponible' => $cliente->limiteDisponible(),
                'tiene_mora' => $cliente->tieneFinanciamientosMorosos(),
                'estado' => $cliente->estado
            ];

            return response()->json([
                'success' => true,
                'cliente' => $clienteConDatos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }
    }
}
