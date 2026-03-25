<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'name',
        'code',
        'type',
        'requires_authorization',
        'commission_percentage',
        'settlement_days',
        'configuration',
        'is_active',
    ];

    protected $casts = [
        'requires_authorization' => 'boolean',
        'commission_percentage' => 'decimal:2',
        'settlement_days' => 'integer',
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con ventas
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope para métodos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
