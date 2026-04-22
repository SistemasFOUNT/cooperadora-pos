<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table = 'productos';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'category',
        'price',
        'cost',
        'stock',
        'min_stock',
        'track_stock',
        'barcode',
        'additional_data',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'additional_data' => 'array',
    ];

    /**
     * Relación con items de venta
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Relación con movimientos de stock
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Scope para productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock <= min_stock');
    }
}
