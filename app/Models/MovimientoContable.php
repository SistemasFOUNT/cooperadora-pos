<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoContable extends Model
{
    protected $table = 'movimientos_contables';

    protected $fillable = [
        'asiento_id',
        'cuenta_id',
        'debe',
        'haber',
        'concepto',
        'referencia'
    ];

    protected $casts = [
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
    ];

    // Relaciones
    public function asiento(): BelongsTo
    {
        return $this->belongsTo(AsientoContable::class, 'asiento_id');
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_id');
    }

    // Alias compatible con el código existente
    public function getDescripcionAttribute()
    {
        return $this->attributes['concepto'] ?? null;
    }

    public function setDescripcionAttribute($value): void
    {
        $this->attributes['concepto'] = $value;
    }

    // Scopes
    public function scopeDebitos($query)
    {
        return $query->where('debe', '>', 0);
    }

    public function scopeCreditos($query)
    {
        return $query->where('haber', '>', 0);
    }
}
