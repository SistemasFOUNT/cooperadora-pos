<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionOrganizacion extends Model
{
    protected $table = 'configuracion_organizacion';

    protected $fillable = [
        'razon_social',
        'denominacion_comercial',
        'cuit',
        'numero_ingresos_brutos',
        'domicilio_calle',
        'domicilio_numero',
        'domicilio_piso',
        'domicilio_depto',
        'localidad',
        'codigo_postal',
        'provincia',
        'responsable_inscripto',
        'retiene_ingresos_brutos',
        'porcentaje_retencion_iibb',
        'categoria_iva',
        'telefono',
        'email',
        'sitio_web',
        'logo_path',
        'pie_documentos'
    ];

    protected $casts = [
        'responsable_inscripto' => 'boolean',
        'retiene_ingresos_brutos' => 'boolean',
        'porcentaje_retencion_iibb' => 'decimal:2'
    ];

    /**
     * Obtener la configuración de la organización (singleton)
     */
    public static function obtener()
    {
        return self::firstOrCreate(['id' => 1]);
    }

    /**
     * Obtener el domicilio completo
     */
    public function getDomicilioCompletoAttribute()
    {
        $domicilio = "{$this->domicilio_calle} {$this->domicilio_numero}";

        if ($this->domicilio_piso) {
            $domicilio .= ", Piso {$this->domicilio_piso}";
        }

        if ($this->domicilio_depto) {
            $domicilio .= ", Depto {$this->domicilio_depto}";
        }

        return $domicilio;
    }

    /**
     * Obtener la dirección completa con localidad y provincia
     */
    public function getDireccionCompletaAttribute()
    {
        return "{$this->domicilio_completo}, {$this->localidad}, {$this->provincia}";
    }

    /**
     * Obtener el tipo de responsabilidad ante IVA formateado
     */
    public function getResponsabilidadIvaAttribute()
    {
        return match($this->categoria_iva) {
            'responsable_inscripto' => 'RESPONSABLE INSCRIPTO',
            'excento' => 'EXCENTO',
            'monotributo' => 'MONOTRIBUTO',
            default => 'NO DEFINIDO'
        };
    }
}
