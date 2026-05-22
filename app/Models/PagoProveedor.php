<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoProveedor extends Model
{
    protected $table = 'pagos_proveedores';

    protected $fillable = [
        'punto_venta_id',
        'proveedor_id',
        'user_id',
        'fecha_pago',
        'tipo_comprobante',
        'numero_comprobante',
        'fecha_comprobante',
        'concepto',
        'monto',
        'observaciones',
        'comprobante_path',
        'estado',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'fecha_comprobante' => 'date',
        'monto' => 'decimal:2',
    ];

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
