<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AsientoContable extends Model
{
    protected $table = 'asientos_contables';

    protected $fillable = [
        'numero',
        'fecha',
        'concepto',
        'observaciones',
        'tipo',
        'estado',
        'total_debe',
        'total_haber',
        'usuario_id',
        'referencia_tipo',
        'referencia_id',
        'notas'
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_debe' => 'decimal:2',
        'total_haber' => 'decimal:2',
    ];

    // Relaciones
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoContable::class, 'asiento_id');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'referencia_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Aliases compatibles con el código existente
    public function getNumeroAsientoAttribute()
    {
        return $this->attributes['numero'] ?? null;
    }

    public function setNumeroAsientoAttribute($value): void
    {
        $this->attributes['numero'] = $value;
    }

    public function getFechaAsientoAttribute()
    {
        return isset($this->attributes['fecha']) ? Carbon::parse($this->attributes['fecha']) : null;
    }

    public function setFechaAsientoAttribute($value): void
    {
        $this->attributes['fecha'] = $value;
    }

    public function getReferenciaVentaIdAttribute()
    {
        return $this->attributes['referencia_id'] ?? null;
    }

    public function setReferenciaVentaIdAttribute($value): void
    {
        $this->attributes['referencia_id'] = $value;
    }

    // Scopes
    public function scopeDelMes($query, $mes = null, $año = null)
    {
        $mes = $mes ?? now()->month;
        $año = $año ?? now()->year;
        return $query->whereYear('fecha', $año)
                     ->whereMonth('fecha', $mes);
    }

    public function scopeBalanceado($query)
    {
        return $query->whereRaw('total_debe = total_haber');
    }
}
