<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ArqueoCaja extends Model
{
    protected $table = 'arqueos_caja';

    protected $fillable = [
        'punto_venta_id',
        'user_id',
        'fecha_arqueo',
        'periodo_desde',
        'periodo_hasta',
        'total_efectivo_calculado',
        'total_tarjeta_calculado',
        'total_transferencia_calculado',
        'total_calculado',
        'total_efectivo_declarado',
        'total_tarjeta_declarado',
        'total_transferencia_declarado',
        'total_declarado',
        'diferencia',
        'cantidad_transacciones',
        'estado',
        'cerrado_at',
        'observaciones',
    ];

    protected $casts = [
        'fecha_arqueo'                   => 'datetime',
        'periodo_desde'                  => 'datetime',
        'periodo_hasta'                  => 'datetime',
        'cerrado_at'                     => 'datetime',
        'total_efectivo_calculado'       => 'decimal:2',
        'total_tarjeta_calculado'        => 'decimal:2',
        'total_transferencia_calculado'  => 'decimal:2',
        'total_calculado'                => 'decimal:2',
        'total_efectivo_declarado'       => 'decimal:2',
        'total_tarjeta_declarado'        => 'decimal:2',
        'total_transferencia_declarado'  => 'decimal:2',
        'total_declarado'                => 'decimal:2',
        'diferencia'                     => 'decimal:2',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────────

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopePorPuntoVenta($query, int $puntoVentaId)
    {
        return $query->where('punto_venta_id', $puntoVentaId);
    }

    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'abierto');
    }

    public function scopeCerrados($query)
    {
        return $query->where('estado', 'cerrado');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function estaAbierto(): bool
    {
        return $this->estado === 'abierto';
    }

    public function hayDiferencia(): bool
    {
        return $this->diferencia != 0;
    }

    public function claseDiferencia(): string
    {
        if ($this->diferencia > 0) return 'text-success';
        if ($this->diferencia < 0) return 'text-danger';
        return 'text-muted';
    }

    public function textoDiferencia(): string
    {
        if ($this->diferencia > 0) return 'Sobrante';
        if ($this->diferencia < 0) return 'Faltante';
        return 'Exacto';
    }
}
