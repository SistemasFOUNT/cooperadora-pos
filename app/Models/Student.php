<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->reinscripcion == now()->year;
    }

    /**
     * Obtener el estado académico del estudiante
     */
    public function obtenerEstadoAcademico()
    {
        $anioActual = now()->year;
        
        if ($this->reinscripcion == $anioActual) {
            return 'cursando';
        } elseif ($this->reinscripcion < $anioActual) {
            return 'posible_egresado_o_abandono';
        } else {
            return 'futuro';
        }
    }

    /**
     * Calcular cuotas adeudadas (aproximación basada en meses desde reinscripción)
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

        // Si está al día académicamente, no calculamos deuda histórica
        if ($this->estaAlDiaAcademicamente()) {
            return [
                'cantidad' => 0,
                'monto_total' => 0,
                'detalle' => 'Cursando año actual'
            ];
        }

        // Calcular meses desde el año de reinscripción hasta ahora
        $anioReinscripcion = $this->reinscripcion;
        $anioActual = now()->year;
        $mesActual = now()->month;
        
        // Estimación: desde marzo del año de reinscripción hasta diciembre del año anterior al actual
        // (asumiendo que las clases van de marzo a diciembre)
        $mesesEstimados = 0;
        
        for ($ano = $anioReinscripcion; $ano < $anioActual; $ano++) {
            // 10 meses por año académico (marzo-diciembre)
            $mesesEstimados += 10;
        }
        
        // Si estamos en el año actual, agregar meses del año actual hasta ahora
        if ($mesActual >= 3) { // Si ya pasó marzo
            $mesesEstimados += min($mesActual - 2, 10); // Desde marzo hasta el mes actual, máximo 10
        }

        $cuotaMensual = $this->configuracionCarrera->cuota_mensual ?? 0;
        $montoTotal = $mesesEstimados * $cuotaMensual;

        return [
            'cantidad' => $mesesEstimados,
            'monto_total' => $montoTotal,
            'detalle' => "Desde {$anioReinscripcion} hasta " . now()->format('Y')
        ];
    }
}
