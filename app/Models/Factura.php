<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Factura extends Model
{
    protected $fillable = [
        'sale_id',
        'punto_venta_id',
        'tipo',
        'tipo_comprobante',
        'punto_venta',
        'numero',
        'numero_completo',
        'fecha_emision',
        'fecha_vto_cae',
        'datos_cliente',
        'cuit_cliente',
        'razon_social_cliente',
        'subtotal',
        'iva',
        'total',
        'cae',
        'qr_arca',
        'respuesta_arca',
        'estado',
        'observaciones',
        'created_by'
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'fecha_vto_cae' => 'date',
        'datos_cliente' => 'array',
        'respuesta_arca' => 'array',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    // Relaciones
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getNumeroCompletoAttribute()
    {
        if ($this->tipo === 'arca' && $this->punto_venta) {
            return sprintf('%04d-%08d', $this->punto_venta, $this->numero);
        }

        return sprintf('%08d', $this->numero);
    }

    public function getEsARCAAttribute(): bool
    {
        return $this->tipo === 'arca';
    }

    public function getEsLocalAttribute(): bool
    {
        return $this->tipo === 'local';
    }

    public function getEsAutorizadaAttribute(): bool
    {
        return in_array($this->estado, ['emitida', 'autorizada']);
    }

    public function getRequiereCAEAttribute(): bool
    {
        return $this->tipo === 'arca' && empty($this->cae);
    }

    // Scopes
    public function scopeLocales($query)
    {
        return $query->where('tipo', 'local');
    }

    public function scopeARCA($query)
    {
        return $query->where('tipo', 'arca');
    }

    public function scopeAutorizadas($query)
    {
        return $query->whereIn('estado', ['emitida', 'autorizada']);
    }

    public function scopePorPuntoVenta($query, $puntoVentaId)
    {
        return $query->where('punto_venta_id', $puntoVentaId);
    }

    // Métodos
    public function anular($motivo = null)
    {
        $this->estado = 'anulada';
        $this->observaciones = $motivo;
        $this->save();
    }

    public function formatearFecha($formato = 'd/m/Y'): string
    {
        return $this->fecha_emision->format($formato);
    }

    public function formatearTotal(): string
    {
        return '$ ' . number_format($this->total, 2, ',', '.');
    }
}
