<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'branch_id',
        'user_id',
        'sale_id',
        'movement_number',
        'movement_datetime',
        'type',
        'concept',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'notes',
    ];

    protected $casts = [
        'movement_datetime' => 'datetime',
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Relación con sucursal
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con venta
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
