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
}
