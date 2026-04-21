<?php

namespace App\Services\Odonto;

use App\Models\Sale;
use App\Models\Student;
use App\Models\Product;
use App\Models\PuntoVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio específico para las operaciones del Centro Odontológico
 */
class OdontoService
{
    private $puntoVenta;

    public function __construct()
    {
        $this->puntoVenta = PuntoVenta::where('codigo', 'ODONTO')->first();
    }

    /**
     * Registrar nuevo paciente
     */
    public function registrarPaciente($pacienteData)
    {
        try {
            // Verificar si el paciente ya existe
            $paciente = Student::where('dni', $pacienteData['dni'])->first();

            if ($paciente) {
                throw new \Exception('El paciente ya está registrado en el sistema');
            }

            $paciente = Student::create([
                'name' => $pacienteData['name'],
                'email' => $pacienteData['email'],
                'dni' => $pacienteData['dni'],
                'phone' => $pacienteData['phone'],
                'address' => $pacienteData['address'] ?? null,
                'birth_date' => $pacienteData['birth_date'] ?? null,
                'tipo' => 'paciente',
                'carrera' => 'N/A',
                'status' => 'active',
                'observaciones_medicas' => $pacienteData['observaciones_medicas'] ?? null
            ]);

            return [
                'success' => true,
                'paciente' => $paciente,
                'mensaje' => 'Paciente registrado exitosamente'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mensaje' => 'Error al registrar el paciente'
            ];
        }
    }

    /**
     * Agendar cita médica
     */
    public function agendarCita($pacienteId, $fecha, $hora, $tratamiento, $doctor = null, $observaciones = null)
    {
        // Simulación de agenda - en una implementación real esto iría a una tabla de citas
        $cita = [
            'id' => uniqid(),
            'paciente_id' => $pacienteId,
            'fecha' => $fecha,
            'hora' => $hora,
            'tratamiento' => $tratamiento,
            'doctor' => $doctor ?? 'Dr. Asignado',
            'estado' => 'programada',
            'observaciones' => $observaciones,
            'created_at' => Carbon::now()
        ];

        // En una implementación real, esto se guardaría en una tabla de citas
        // Por ahora lo simulamos creando un registro en sales con tipo 'cita'
        $citaRecord = Sale::create([
            'user_id' => auth()->id(),
            'student_id' => $pacienteId,
            'punto_venta_id' => $this->puntoVenta->id,
            'total' => 0, // Las citas no tienen costo inicial
            'tipo' => 'cita_programada',
            'metodo_pago' => 'pendiente',
            'observaciones' => "Cita: {$tratamiento} - {$fecha} {$hora} - Dr: {$doctor}"
        ]);

        return [
            'success' => true,
            'cita' => $cita,
            'registro' => $citaRecord,
            'mensaje' => 'Cita agendada exitosamente'
        ];
    }

    /**
     * Procesar tratamiento odontológico
     */
    public function procesarTratamiento($pacienteId, $tratamientoData, $materialesUsados = [])
    {
        DB::beginTransaction();

        try {
            $paciente = Student::findOrFail($pacienteId);

            // Calcular costo del tratamiento
            $costoTratamiento = $this->calcularCostoTratamiento($tratamientoData['tipo']);
            $costoMateriales = $this->calcularCostoMateriales($materialesUsados);
            $costoTotal = $costoTratamiento + $costoMateriales;

            // Crear registro del tratamiento
            $tratamiento = Sale::create([
                'user_id' => auth()->id(),
                'student_id' => $pacienteId,
                'punto_venta_id' => $this->puntoVenta->id,
                'total' => $costoTotal,
                'tipo' => 'tratamiento',
                'metodo_pago' => 'efectivo', // Por defecto
                'observaciones' => $this->generarObservacionesTratamiento($tratamientoData, $materialesUsados)
            ]);

            // Registrar materiales utilizados
            foreach ($materialesUsados as $material) {
                $producto = Product::find($material['producto_id']);
                if ($producto) {
                    $tratamiento->products()->attach($producto->id, [
                        'quantity' => $material['cantidad'],
                        'price' => $material['precio_unitario']
                    ]);
                }
            }

            // Generar asiento contable
            $this->generarAsientoTratamiento($tratamiento);

            // Actualizar historial del paciente
            $this->actualizarHistorialPaciente($pacienteId, $tratamientoData, $tratamiento->id);

            DB::commit();

            return [
                'success' => true,
                'tratamiento' => $tratamiento,
                'costo_total' => $costoTotal,
                'mensaje' => 'Tratamiento procesado exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mensaje' => 'Error al procesar el tratamiento'
            ];
        }
    }

    /**
     * Calcular costo de tratamiento según tipo
     */
    private function calcularCostoTratamiento($tipo)
    {
        $precios = [
            'consulta' => 3000,
            'limpieza' => 5000,
            'empaste' => 8000,
            'extraccion' => 6000,
            'endodoncia' => 25000,
            'corona' => 40000,
            'implante' => 80000,
            'ortodoncia_consulta' => 5000,
            'cirugia_oral' => 15000,
            'protesis_parcial' => 35000,
            'protesis_completa' => 60000
        ];

        return $precios[$tipo] ?? 5000;
    }

    /**
     * Calcular costo de materiales utilizados
     */
    private function calcularCostoMateriales($materiales)
    {
        $costoTotal = 0;

        foreach ($materiales as $material) {
            $costoTotal += $material['precio_unitario'] * $material['cantidad'];
        }

        return $costoTotal;
    }

    /**
     * Generar observaciones detalladas del tratamiento
     */
    private function generarObservacionesTratamiento($tratamientoData, $materiales)
    {
        $observaciones = "Tratamiento: {$tratamientoData['tipo']}\n";
        $observaciones .= "Descripción: {$tratamientoData['descripcion']}\n";

        if (!empty($materiales)) {
            $observaciones .= "Materiales utilizados:\n";
            foreach ($materiales as $material) {
                $observaciones .= "- {$material['nombre']}: {$material['cantidad']} unidades\n";
            }
        }

        if (isset($tratamientoData['notas'])) {
            $observaciones .= "Notas adicionales: {$tratamientoData['notas']}";
        }

        return $observaciones;
    }

    /**
     * Generar asiento contable para tratamiento
     */
    private function generarAsientoTratamiento($tratamiento)
    {
        if ($this->puntoVenta) {
            return $this->puntoVenta->generarAsientoVenta(
                $tratamiento->total,
                "Tratamiento #{$tratamiento->id} - Centro Odontológico",
                'efectivo'
            );
        }

        return null;
    }

    /**
     * Actualizar historial médico del paciente
     */
    private function actualizarHistorialPaciente($pacienteId, $tratamientoData, $tratamientoId)
    {
        $paciente = Student::find($pacienteId);

        if ($paciente) {
            $historialActual = $paciente->observaciones_medicas ?? '';
            $nuevaEntrada = "\n[" . Carbon::now()->format('d/m/Y H:i') . "] " .
                           $tratamientoData['tipo'] . " - ID: {$tratamientoId}";

            $paciente->update([
                'observaciones_medicas' => $historialActual . $nuevaEntrada
            ]);
        }
    }

    /**
     * Obtener estadísticas del centro odontológico
     */
    public function getEstadisticasClinicas()
    {
        return [
            'tratamientos_mes' => Sale::where('punto_venta_id', $this->puntoVenta->id)
                                    ->where('tipo', 'tratamiento')
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->count(),
            'ingresos_mes' => Sale::where('punto_venta_id', $this->puntoVenta->id)
                                ->whereMonth('created_at', Carbon::now()->month)
                                ->sum('total'),
            'pacientes_nuevos_mes' => Student::where('tipo', 'paciente')
                                           ->whereMonth('created_at', Carbon::now()->month)
                                           ->count(),
            'urgencias_mes' => Sale::where('punto_venta_id', $this->puntoVenta->id)
                                 ->where('tipo', 'urgencia')
                                 ->whereMonth('created_at', Carbon::now()->month)
                                 ->count()
        ];
    }

    /**
     * Obtener materiales más utilizados
     */
    public function getMaterialesMasUtilizados($limite = 10)
    {
        return DB::table('sale_product')
            ->join('sales', 'sale_product.sale_id', '=', 'sales.id')
            ->join('products', 'sale_product.product_id', '=', 'products.id')
            ->where('sales.punto_venta_id', $this->puntoVenta->id)
            ->where('products.categoria', 'material_odontologico')
            ->whereMonth('sales.created_at', Carbon::now()->month)
            ->selectRaw('products.name, SUM(sale_product.quantity) as cantidad_usada, products.stock')
            ->groupBy('products.id', 'products.name', 'products.stock')
            ->orderByDesc('cantidad_usada')
            ->limit($limite)
            ->get();
    }

    /**
     * Obtener tratamientos más realizados
     */
    public function getTratamientosMasRealizados($limite = 10)
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
            ->where('tipo', 'tratamiento')
            ->whereMonth('created_at', Carbon::now()->month)
            ->selectRaw('
                SUBSTRING_INDEX(observaciones, " - ", 1) as tratamiento,
                COUNT(*) as cantidad,
                SUM(total) as ingresos_generados
            ')
            ->groupByRaw('SUBSTRING_INDEX(observaciones, " - ", 1)')
            ->orderByDesc('cantidad')
            ->limit($limite)
            ->get();
    }

    /**
     * Gestionar inventario de materiales odontológicos
     */
    public function verificarInventario()
    {
        $materialesBajos = Product::where('categoria', 'material_odontologico')
                                ->where('stock', '<=', 10)
                                ->get();

        $alertas = [];
        foreach ($materialesBajos as $material) {
            $alertas[] = [
                'producto' => $material->name,
                'stock_actual' => $material->stock,
                'estado' => $material->stock <= 5 ? 'critico' : 'bajo',
                'sugerencia' => 'Solicitar reposición urgente'
            ];
        }

        return $alertas;
    }

    /**
     * Programar cita de control
     */
    public function programarControl($pacienteId, $diasParaControl = 7)
    {
        $fechaControl = Carbon::now()->addDays($diasParaControl);

        return $this->agendarCita(
            $pacienteId,
            $fechaControl->format('Y-m-d'),
            '10:00',
            'Control post-tratamiento',
            'Dr. Asignado',
            'Cita de control programada automáticamente'
        );
    }

    /**
     * Obtener horarios de atención del centro
     */
    public function getHorariosAtencion()
    {
        return [
            'lunes' => ['apertura' => '08:00', 'cierre' => '20:00'],
            'martes' => ['apertura' => '08:00', 'cierre' => '20:00'],
            'miercoles' => ['apertura' => '08:00', 'cierre' => '20:00'],
            'jueves' => ['apertura' => '08:00', 'cierre' => '20:00'],
            'viernes' => ['apertura' => '08:00', 'cierre' => '20:00'],
            'sabado' => ['apertura' => '08:00', 'cierre' => '14:00'],
            'domingo' => ['apertura' => '10:00', 'cierre' => '14:00'] // Solo urgencias
        ];
    }

    /**
     * Verificar si hay citas de urgencia disponibles
     */
    public function verificarUrgenciasDisponibles()
    {
        $horariosUrgencia = [
            'lunes_viernes' => ['19:00', '19:30', '20:00'],
            'sabado' => ['13:00', '13:30', '14:00'],
            'domingo' => ['10:00', '11:00', '12:00', '13:00']
        ];

        $hoy = strtolower(Carbon::now()->format('l'));
        $disponibles = [];

        // Lógica simplificada para mostrar horarios disponibles
        if (in_array($hoy, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])) {
            $disponibles = $horariosUrgencia['lunes_viernes'];
        } elseif ($hoy === 'saturday') {
            $disponibles = $horariosUrgencia['sabado'];
        } elseif ($hoy === 'sunday') {
            $disponibles = $horariosUrgencia['domingo'];
        }

        return $disponibles;
    }
}
