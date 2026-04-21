<?php

namespace App\Services\Postgrado;

use App\Models\Sale;
use App\Models\Student;
use App\Models\PuntoVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio específico para las operaciones de Postgrado
 */
class PostgradoService
{
    private $puntoVenta;

    public function __construct()
    {
        $this->puntoVenta = PuntoVenta::where('codigo', 'POSTGRADO')->first();
    }

    /**
     * Procesar matrícula de estudiante de postgrado
     */
    public function procesarMatricula($estudianteData, $cursoData, $periodo = null)
    {
        DB::beginTransaction();

        try {
            // Verificar si el estudiante ya existe
            $estudiante = Student::where('dni', $estudianteData['dni'])->first();

            if (!$estudiante) {
                // Crear nuevo estudiante
                $estudiante = Student::create([
                    'name' => $estudianteData['name'],
                    'email' => $estudianteData['email'],
                    'dni' => $estudianteData['dni'],
                    'phone' => $estudianteData['phone'] ?? null,
                    'address' => $estudianteData['address'] ?? null,
                    'tipo' => 'postgrado',
                    'carrera' => $cursoData['nombre'],
                    'status' => 'active'
                ]);
            }

            // Calcular monto según tipo de curso
            $monto = $this->calcularArancel($cursoData['tipo']);

            // Crear registro de matrícula
            $matricula = Sale::create([
                'user_id' => auth()->id(),
                'student_id' => $estudiante->id,
                'punto_venta_id' => $this->puntoVenta->id,
                'total' => $monto,
                'tipo' => 'matricula',
                'periodo_academico' => $periodo ?? $this->getPeriodoActual(),
                'metodo_pago' => 'transferencia', // Por defecto para postgrado
                'observaciones' => "Matrícula en {$cursoData['nombre']} - Período {$periodo}"
            ]);

            // Generar asiento contable
            $this->generarAsientoMatricula($matricula);

            DB::commit();

            return [
                'success' => true,
                'matricula' => $matricula,
                'estudiante' => $estudiante,
                'mensaje' => 'Matrícula procesada exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mensaje' => 'Error al procesar la matrícula'
            ];
        }
    }

    /**
     * Calcular arancel según tipo de programa
     */
    private function calcularArancel($tipo)
    {
        $aranceles = [
            'especializacion' => 50000,
            'maestria' => 75000,
            'doctorado' => 100000,
            'diplomado' => 25000,
            'curso' => 15000
        ];

        return $aranceles[$tipo] ?? 25000;
    }

    /**
     * Obtener período académico actual
     */
    public function getPeriodoActual()
    {
        $mes = Carbon::now()->month;
        $año = Carbon::now()->year;

        if ($mes >= 3 && $mes <= 7) {
            return "{$año}-1";
        } else {
            return "{$año}-2";
        }
    }

    /**
     * Generar asiento contable para matrícula
     */
    private function generarAsientoMatricula($matricula)
    {
        if ($this->puntoVenta) {
            return $this->puntoVenta->generarAsientoVenta(
                $matricula->total,
                "Matrícula #{$matricula->id} - Postgrado",
                'transferencia'
            );
        }

        return null;
    }

    /**
     * Generar certificado de finalización
     */
    public function generarCertificado($estudianteId, $cursoNombre, $nota = null, $fechaFinalizacion = null)
    {
        $estudiante = Student::findOrFail($estudianteId);
        $fecha = $fechaFinalizacion ?? Carbon::now();

        $certificado = [
            'estudiante' => $estudiante,
            'curso' => $cursoNombre,
            'fecha_emision' => $fecha->format('d/m/Y'),
            'nota' => $nota,
            'numero_certificado' => $this->generarNumeroCertificado(),
            'tipo' => 'finalizacion_curso'
        ];

        // Registrar en ventas como certificado
        Sale::create([
            'user_id' => auth()->id(),
            'student_id' => $estudianteId,
            'punto_venta_id' => $this->puntoVenta->id,
            'total' => 0, // Sin costo el certificado
            'tipo' => 'certificado',
            'metodo_pago' => 'gratuito',
            'observaciones' => "Certificado de {$cursoNombre} - Nota: {$nota}"
        ]);

        return $certificado;
    }

    /**
     * Generar número único de certificado
     */
    private function generarNumeroCertificado()
    {
        $año = Carbon::now()->year;
        $secuencial = Sale::where('punto_venta_id', $this->puntoVenta->id)
                         ->where('tipo', 'certificado')
                         ->whereYear('created_at', $año)
                         ->count() + 1;

        return "CERT-PG-{$año}-" . str_pad($secuencial, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener estadísticas académicas
     */
    public function getEstadisticasAcademicas()
    {
        $periodoActual = $this->getPeriodoActual();

        return [
            'matriculas_periodo' => Sale::where('punto_venta_id', $this->puntoVenta->id)
                                      ->where('tipo', 'matricula')
                                      ->where('periodo_academico', $periodoActual)
                                      ->count(),
            'ingresos_periodo' => Sale::where('punto_venta_id', $this->puntoVenta->id)
                                    ->where('periodo_academico', $periodoActual)
                                    ->sum('total'),
            'estudiantes_activos' => Student::where('tipo', 'postgrado')
                                           ->where('status', 'active')
                                           ->count(),
            'certificados_emitidos' => Sale::where('punto_venta_id', $this->puntoVenta->id)
                                         ->where('tipo', 'certificado')
                                         ->whereMonth('created_at', Carbon::now()->month)
                                         ->count()
        ];
    }

    /**
     * Obtener cursos más demandados
     */
    public function getCursosMasDemandados($limite = 10)
    {
        return Sale::where('punto_venta_id', $this->puntoVenta->id)
            ->where('tipo', 'matricula')
            ->whereYear('created_at', Carbon::now()->year)
            ->join('students', 'sales.student_id', '=', 'students.id')
            ->selectRaw('students.carrera as curso, COUNT(*) as matriculas')
            ->groupBy('students.carrera')
            ->orderByDesc('matriculas')
            ->limit($limite)
            ->get();
    }

    /**
     * Procesar pago de cuota mensual
     */
    public function procesarCuotaMensual($estudianteId, $curso, $mesPago, $monto)
    {
        $estudiante = Student::findOrFail($estudianteId);

        $pago = Sale::create([
            'user_id' => auth()->id(),
            'student_id' => $estudianteId,
            'punto_venta_id' => $this->puntoVenta->id,
            'total' => $monto,
            'tipo' => 'cuota_mensual',
            'metodo_pago' => 'transferencia',
            'observaciones' => "Cuota {$mesPago} - {$curso}"
        ]);

        // Generar asiento contable
        $this->generarAsientoCuota($pago);

        return $pago;
    }

    /**
     * Generar asiento contable para cuota
     */
    private function generarAsientoCuota($pago)
    {
        if ($this->puntoVenta) {
            return $this->puntoVenta->generarAsientoVenta(
                $pago->total,
                "Cuota mensual #{$pago->id} - Postgrado",
                'transferencia'
            );
        }

        return null;
    }

    /**
     * Obtener horarios de atención
     */
    public function getHorariosAtencion()
    {
        return [
            'lunes' => ['apertura' => '09:00', 'cierre' => '17:00'],
            'martes' => ['apertura' => '09:00', 'cierre' => '17:00'],
            'miercoles' => ['apertura' => '09:00', 'cierre' => '17:00'],
            'jueves' => ['apertura' => '09:00', 'cierre' => '17:00'],
            'viernes' => ['apertura' => '09:00', 'cierre' => '17:00'],
            'sabado' => ['apertura' => null, 'cierre' => null], // Cerrado
            'domingo' => ['apertura' => null, 'cierre' => null]  // Cerrado
        ];
    }

    /**
     * Verificar estado de pagos de un estudiante
     */
    public function verificarEstadoPagos($estudianteId)
    {
        $pagos = Sale::where('student_id', $estudianteId)
                   ->where('punto_venta_id', $this->puntoVenta->id)
                   ->orderBy('created_at', 'desc')
                   ->get();

        $totalPagado = $pagos->sum('total');
        $ultimoPago = $pagos->first();

        return [
            'pagos' => $pagos,
            'total_pagado' => $totalPagado,
            'ultimo_pago' => $ultimoPago,
            'estado' => $this->determinarEstadoPago($pagos)
        ];
    }

    /**
     * Determinar estado de pago del estudiante
     */
    private function determinarEstadoPago($pagos)
    {
        $ultimoPago = $pagos->first();

        if (!$ultimoPago) {
            return 'sin_pagos';
        }

        $diasSinPagar = Carbon::now()->diffInDays($ultimoPago->created_at);

        if ($diasSinPagar <= 30) {
            return 'al_dia';
        } elseif ($diasSinPagar <= 60) {
            return 'atrasado';
        } else {
            return 'moroso';
        }
    }
}
