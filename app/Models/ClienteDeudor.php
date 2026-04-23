<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteDeudor extends Model
{
    protected $table = 'clientes_deudores';

    protected $fillable = [
        'dni',
        'cuil_cuit',
        'apellido',
        'nombre',
        'fecha_nacimiento',
        'telefono_principal',
        'telefono_secundario',
        'email',
        'domicilio_calle',
        'domicilio_numero',
        'domicilio_piso',
        'domicilio_depto',
        'localidad',
        'codigo_postal',
        'provincia',
        'estado_civil',
        'profesion',
        'lugar_trabajo',
        'telefono_trabajo',
        'ingresos_mensuales',
        'referencia_nombre',
        'referencia_telefono',
        'referencia_relacion',
        'limite_credito',
        'calificacion_crediticia',
        'observaciones',
        'estado',
        'motivo_suspension',
        'usuario_registro_id'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'ingresos_mensuales' => 'decimal:2',
        'limite_credito' => 'decimal:2'
    ];

    /**
     * Relación con usuario que registró al cliente
     */
    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_registro_id');
    }

    /**
     * Relación con financiamientos
     */
    public function financiamientos(): HasMany
    {
        return $this->hasMany(FinanciamientoOdontologia::class);
    }

    /**
     * Relación con documentos legales
     */
    public function documentosLegales(): HasMany
    {
        return $this->hasMany(DocumentoLegal::class);
    }

    /**
     * Obtener el nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->apellido}, {$this->nombre}";
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
     * Calcular la deuda total actual
     */
    public function deudaTotal()
    {
        return $this->financiamientos()
            ->whereIn('estado', ['activo', 'pendiente_documentacion'])
            ->with('cuotas')
            ->get()
            ->sum(function ($financiamiento) {
                return $financiamiento->cuotas
                    ->whereIn('estado', ['pendiente', 'vencida'])
                    ->sum('monto_cuota');
            });
    }

    /**
     * Calcular el límite disponible
     */
    public function limiteDisponible()
    {
        return $this->limite_credito - $this->deudaTotal();
    }

    /**
     * Verificar si el cliente puede acceder a crédito
     */
    public function puedeAccederCredito($monto)
    {
        return $this->estado === 'activo' &&
               $this->limiteDisponible() >= $monto &&
               !$this->tieneFinanciamientosMorosos();
    }

    /**
     * Verificar si tiene financiamientos morosos
     */
    public function tieneFinanciamientosMorosos()
    {
        return $this->financiamientos()
            ->whereHas('cuotas', function ($query) {
                $query->where('estado', 'vencida')
                      ->where('fecha_vencimiento', '<', now()->subDays(10));
            })
            ->exists();
    }

    /**
     * Scope para buscar por DNI
     */
    public function scopePorDni($query, $dni)
    {
        return $query->where('dni', $dni);
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
