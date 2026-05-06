<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Student extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table = 'estudiantes';

    protected $fillable = [
        // Campos del CSV
        'apellido',
        'nombre',
        'dni',
        'email',
        'telefono',
        'legajo',
        'plan',
        'ingreso',
        'reinscripcion',

        // Campos adicionales del sistema
        'carrera', // Referencia a career_type en CareerFeeConfig
        'fecha_inscripcion',
        'estado',
        'direccion',
        'activo',
        'datos_adicionales',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
        'ingreso' => 'integer',
        'reinscripcion' => 'integer',
        'activo' => 'boolean',
        'datos_adicionales' => 'array',
    ];

    /**
     * Relación con la configuración de carrera
     */
    public function configuracionCarrera()
    {
        return $this->belongsTo(CareerFeeConfig::class, 'carrera', 'tipo_carrera');
    }

    /**
     * Relación con ventas (pagos de cuotas)
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope para estudiantes activos (usando activo)
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para estudiantes por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Obtener nombre completo
     */
    public function obtenerNombreCompleto()
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    /**
     * Verificar si está activo
     */
    public function estaActivo()
    {
        return $this->activo && $this->estado === 'activo';
    }

    /**
     * Obtener años de estudios
     */
    public function obtenerAniosEstudio()
    {
        return now()->year - $this->ingreso;
    }

    /**
     * Obtener cuota mensual desde la carrera
     */
    public function obtenerCuotaMensual()
    {
        return $this->configuracionCarrera?->cuota_mensual ?? 0;
    }

    /**
     * Verificar si el estudiante está al día con el año académico
     */
    public function estaAlDiaAcademicamente()
    {
        return $this->reinscripcion == $this->obtenerAnioAcademicoActual();
    }

    /**
     * Obtener el año académico actual (considera que inicia 1/4 y termina 31/3)
     */
    public function obtenerAnioAcademicoActual()
    {
        $fechaActual = now();
        $anioActual = $fechaActual->year;

        // Si estamos antes del 1 de abril, el año académico es el anterior
        if ($fechaActual->month < 4) {
            return $anioActual - 1;
        }

        return $anioActual;
    }

    /**
     * Obtener el estado académico del estudiante
     */
    public function obtenerEstadoAcademico()
    {
        $anioAcademicoActual = $this->obtenerAnioAcademicoActual();

        if ($this->reinscripcion == $anioAcademicoActual) {
            return 'cursando';
        } elseif ($this->reinscripcion < $anioAcademicoActual) {
            return 'posible_egresado_o_abandono';
        } else {
            return 'futuro';
        }
    }

    /**
     * Calcular cuotas adeudadas considerando año académico (abril-marzo)
     */
    public function calcularCuotasAdeudadas()
    {
        if (!$this->configuracionCarrera) {
            return [
                'cantidad' => 0,
                'monto_total' => 0,
                'detalle' => 'Sin carrera asignada'
            ];
        }

        $anioAcademicoActual = $this->obtenerAnioAcademicoActual();

        // Si está al día académicamente, no calculamos deuda histórica
        if ($this->reinscripcion >= $anioAcademicoActual) {
            return [
                'cantidad' => 0,
                'monto_total' => 0,
                'detalle' => 'Cursando año académico actual'
            ];
        }

        // Calcular meses desde el año de reinscripción hasta el año académico actual
        $anioReinscripcion = $this->reinscripcion;
        $fechaActual = now();
        $mesesAdeudados = 0;

        // Calcular años académicos completos transcurridos
        $aniosCompletos = $anioAcademicoActual - $anioReinscripcion;

        if ($aniosCompletos > 1) {
            // Años académicos completos (12 meses cada uno)
            $mesesAdeudados += ($aniosCompletos - 1) * 12;
        }

        // Calcular meses del último año académico incompleto
        if ($aniosCompletos >= 1) {
            // Desde abril del año de reinscripción hasta marzo del siguiente
            $mesesAdeudados += 12;

            // Meses del año académico actual (desde abril hasta el mes actual)
            if ($fechaActual->month >= 4) {
                $mesesDelAnioActual = $fechaActual->month - 3; // Desde abril (mes 4) hasta el mes actual
            } else {
                // Si estamos en enero-marzo, contamos desde abril del año anterior
                $mesesDelAnioActual = $fechaActual->month + 9; // 9 meses del año anterior + meses del actual
            }
            $mesesAdeudados += $mesesDelAnioActual;
        } else {
            // Solo calcular meses dentro del mismo año académico
            $inicioAnioAcademico = Carbon::parse($anioReinscripcion . '-04-01');
            $mesesTranscurridos = $inicioAnioAcademico->diffInMonths($fechaActual);
            $mesesAdeudados = min($mesesTranscurridos, 12); // Máximo 12 meses por año académico
        }

        $cuotaMensual = $this->configuracionCarrera->cuota_mensual ?? 0;
        $montoTotal = $mesesAdeudados * $cuotaMensual;

        return [
            'cantidad' => $mesesAdeudados,
            'monto_total' => $montoTotal,
            'detalle' => "Desde año académico {$anioReinscripcion} hasta " . $this->obtenerAnioAcademicoActual(),
            'anio_academico_actual' => $anioAcademicoActual
        ];
    }

    /**
     * Última venta en POSTGRADO
     */
    public function ultimaVentaPostgrado()
    {
        return $this->hasOne(Sale::class)->where('punto_venta_id', 2)->latest();
    }

    /**
     * Todas las ventas en POSTGRADO
     */
    public function ventasPostgrado()
    {
        return $this->hasMany(Sale::class)->where('punto_venta_id', 2);
    }

    /**
     * Verificar si el estudiante ha participado en POSTGRADO
     */
    public function participaEnPostgrado()
    {
        return $this->ventas()->where('punto_venta_id', 2)->exists();
    }
}
