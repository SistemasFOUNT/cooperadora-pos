<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuntoVenta extends Model
{
    protected $table = 'puntos_venta';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
        'cuenta_caja_id',
        'cuenta_ventas_id',
        'cuenta_deudores_id',
        'cuenta_fondo_fijo_id'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relaciones con cuentas contables
    public function cuentaCaja(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_caja_id');
    }

    public function cuentaVentas(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_ventas_id');
    }

    public function cuentaDeudores(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_deudores_id');
    }

    public function cuentaFondoFijo(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_fondo_fijo_id');
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // Métodos auxiliares
    public function generarAsientoVenta($montoVenta, $conceptoVenta, $formaPago = 'efectivo')
    {
        // Lógica para generar asiento de venta
        // Debe: Caja (cuenta_caja_id)
        // Haber: Ventas (cuenta_ventas_id)
        return [
            'punto_venta' => $this->nombre,
            'movimientos' => [
                [
                    'cuenta_id' => $this->cuenta_caja_id,
                    'debe' => $montoVenta,
                    'haber' => 0,
                    'concepto' => "Venta - {$conceptoVenta}"
                ],
                [
                    'cuenta_id' => $this->cuenta_ventas_id,
                    'debe' => 0,
                    'haber' => $montoVenta,
                    'concepto' => "Venta - {$conceptoVenta}"
                ]
            ]
        ];
    }
}
