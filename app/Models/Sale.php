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
        'punto_venta_id',
        'usuario_id',
        'student_id',
        'payment_method_id',
        'fecha_venta',
        'type',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'fiscal_document_type',
        'fiscal_document_number',
        'cae',
        'cae_expiry',
        'status',
        'notes',
        'additional_data',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'cae_expiry' => 'datetime',
        'additional_data' => 'array',
    ];

    /**
     * Relación con punto de venta
     */
    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    /**
     * Relación con sucursal (mantener compatibilidad)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'punto_venta_id');
    }

    /**
     * Relación con usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
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
