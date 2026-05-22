<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerFeeConfig extends Model
{
    use HasFactory;

    protected $table = 'configuracion_cuotas_carreras';

    protected $fillable = [
        'tipo_carrera',
        'nombre_carrera',
        'cuota_mensual',
        'cuota_inscripcion',
        'cuota_certificado',
        'duracion_meses',
        'activo',
        'cuotas_adicionales',
        'dia_vencimiento',
        'dias_gracia',
        'porcentaje_recargo',
        'cuota_bono',
        'bono_inicio_cobro',
        'bono_fin_cobro',
        'dia_vencimiento_1',
        'dia_vencimiento_2',
        'porcentaje_recargo_1',
        'porcentaje_recargo_2',
        'porcentaje_recargo_3',
    ];

    protected $casts = [
        'cuota_mensual'      => 'decimal:2',
        'cuota_inscripcion'  => 'decimal:2',
        'cuota_certificado'  => 'decimal:2',
        'cuota_bono'         => 'decimal:2',
        'porcentaje_recargo' => 'decimal:2',
        'porcentaje_recargo_1' => 'decimal:2',
        'porcentaje_recargo_2' => 'decimal:2',
        'porcentaje_recargo_3' => 'decimal:2',
        'duracion_meses'     => 'integer',
        'dia_vencimiento'    => 'integer',
        'dia_vencimiento_1'  => 'integer',
        'dia_vencimiento_2'  => 'integer',
        'dias_gracia'        => 'integer',
        'bono_inicio_cobro'  => 'date',
        'bono_fin_cobro'     => 'date',
        'activo'             => 'boolean',
        'cuotas_adicionales' => 'array',
    ];

    /**
     * Relación con estudiantes
     */
    public function estudiantes()
    {
        return $this->hasMany(Student::class, 'carrera', 'tipo_carrera');
    }

    /**
     * Scope para carreras activas
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Obtener configuración de una carrera específica
     */
    public static function obtenerConfigCarrera($tipoCarrera)
    {
        return static::where('tipo_carrera', $tipoCarrera)->first();
    }

    /**
     * Obtener todas las carreras disponibles para un select
     */
    public static function obtenerOpcionesCarreras()
    {
        return static::activo()
            ->pluck('nombre_carrera', 'tipo_carrera')
            ->toArray();
    }
}
