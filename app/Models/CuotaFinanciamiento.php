<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CuotaFinanciamiento extends Model
{
    protected $table = 'cuotas_financiamiento';

    protected $fillable = [
        'financiamiento_id',
        'numero_cuota',
        'monto_cuota',
        'monto_pagado',
        'recargo_mora',
        'descuento_aplicado',
        'fecha_vencimiento',
        'fecha_pago',
        'estado',
        'metodo_pago',
        'numero_comprobante',
        'observaciones',
        'usuario_cobro_id'
    ];

    protected $casts = [
        'monto_cuota' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'recargo_mora' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'datetime'
    ];

    /**
     * Relación con financiamiento
     */
    public function financiamiento(): BelongsTo
    {
        return $this->belongsTo(FinanciamientoOdontologia::class, 'financiamiento_id');
    }

    /**
     * Relación con usuario que realizó el cobro
     */
    public function usuarioCobro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_cobro_id');
    }

    /**
     * Calcular días de mora
     */
    public function getDiasMoraAttribute()
    {
        if ($this->estado === 'pagada' || $this->fecha_vencimiento >= now()) {
            return 0;
        }

        return Carbon::parse($this->fecha_vencimiento)->diffInDays(now());
    }

    /**
     * Verificar si la cuota está vencida
     */
    public function getEstaVencidaAttribute()
    {
        return $this->estado !== 'pagada' && $this->fecha_vencimiento < now();
    }

    /**
     * Calcular recargo por mora automáticamente
     */
    public function calcularRecargoMora($porcentajeMensual = 2.0)
    {
        if (!$this->esta_vencida) {
            return 0;
        }

        $mesesMora = ceil($this->dias_mora / 30);
        return $this->monto_cuota * ($porcentajeMensual / 100) * $mesesMora;
    }

    /**
     * Obtener el monto total a pagar (con recargos)
     */
    public function getMontoTotalAttribute()
    {
        return $this->monto_cuota + $this->recargo_mora - $this->descuento_aplicado;
    }

    /**
     * Procesar pago de la cuota
     */
    public function procesarPago($monto, $metodoPago, $numeroComprobante = null, $usuarioId = null)
    {
        $this->monto_pagado = $monto;
        $this->fecha_pago = now();
        $this->metodo_pago = $metodoPago;
        $this->numero_comprobante = $numeroComprobante;
        $this->usuario_cobro_id = $usuarioId;

        if ($monto >= $this->monto_total) {
            $this->estado = 'pagada';
        } else {
            $this->estado = 'pagada_parcial';
        }

        $this->save();

        // Actualizar estado del financiamiento si es necesario
        $this->verificarFinanciamientoCompleto();
    }

    /**
     * Verificar si el financiamiento se completó
     */
    private function verificarFinanciamientoCompleto()
    {
        $financiamiento = $this->financiamiento;
        $cuotasPendientes = $financiamiento->cuotas()
            ->whereIn('estado', ['pendiente', 'vencida'])
            ->count();

        if ($cuotasPendientes === 0) {
            $financiamiento->update(['estado' => 'completado']);
        }
    }

    /**
     * Scope para cuotas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('estado', '!=', 'pagada')
                     ->where('fecha_vencimiento', '<', now());
    }

    /**
     * Scope para cuotas próximas a vencer
     */
    public function scopeProximasAVencer($query, $dias = 5)
    {
        return $query->where('estado', 'pendiente')
                     ->whereBetween('fecha_vencimiento', [
                         now(),
                         now()->addDays($dias)
                     ]);
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($cuota) {
            // Actualizar estado si está vencida
            if ($cuota->estado === 'pendiente' && $cuota->fecha_vencimiento < now()) {
                $cuota->estado = 'vencida';
            }

            // Calcular recargo de mora automáticamente
            if ($cuota->esta_vencida && $cuota->recargo_mora == 0) {
                $cuota->recargo_mora = $cuota->calcularRecargoMora();
            }
        });
    }
}
