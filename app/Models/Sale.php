<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table = 'ventas';

    protected $fillable = [
        'sale_number',
        'branch_id',
        'user_id',
        'student_id',
        'payment_method_id',
        'sale_datetime',
        'type',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'fiscal_document_type',
        'fiscal_document_number',
        'cae',
        'cae_expiry',
        'status',
        'notes',
        'additional_data',
    ];

    protected $casts = [
        'sale_datetime' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cae_expiry' => 'datetime',
        'additional_data' => 'array',
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
     * Relación con estudiante
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relación con método de pago
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Relación con items de venta
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Relación con movimientos de caja
     */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }
}
