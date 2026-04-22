<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'categoria',
        'precio',
        'costo',
        'stock',
        'stock_minimo',
        'seguir_inventario',
        'codigo_barras',
        'datos_adicionales',
        'activo',
        'imagen',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'costo' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'seguir_inventario' => 'boolean',
        'activo' => 'boolean',
        'datos_adicionales' => 'array',
    ];

    /**
     * Relación con items de venta
     */
    public function itemsVenta(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Verificar si el producto está activo
     */
    public function estaActivo(): bool
    {
        return $this->activo;
    }

    /**
     * Verificar si el producto tiene stock bajo
     */
    public function tieneStockBajo(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    /**
     * Obtener el precio formateado
     */
    public function getPrecioFormateadoAttribute(): string
    {
        return '$' . number_format($this->precio, 2);
    }

    /**
     * Obtener el costo formateado
     */
    public function getCostoFormateadoAttribute(): string
    {
        return '$' . number_format($this->costo, 2);
    }

    /**
     * Calcular el margen de ganancia
     */
    public function getMargenGananciaAttribute(): float
    {
        if ($this->costo == 0) {
            return 100;
        }
        
        return (($this->precio - $this->costo) / $this->costo) * 100;
    }

    /**
     * Scope para productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_minimo');
    }

    /**
     * Scope para productos por categoría
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }
}