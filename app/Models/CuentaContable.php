<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaContable extends Model
{
    protected $table = 'cuentas_contables';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'naturaleza',
        'cuenta_padre_id',
        'nivel',
        'es_imputable',
        'activa',
        'saldo_inicial'
    ];

    protected $casts = [
        'es_imputable' => 'boolean',
        'activa' => 'boolean',
        'saldo_inicial' => 'decimal:2',
    ];

    // Relaciones
    public function padre(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_padre_id');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(CuentaContable::class, 'cuenta_padre_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoContable::class, 'cuenta_id');
    }

    // Métodos auxiliares
    public function determinarTipo()
    {
        $primerDigito = substr($this->codigo, 0, 1);

        switch ($primerDigito) {
            case '1':
                return 'activo';
            case '2':
                return 'pasivo';
            case '3':
                return 'patrimonio';
            case '4':
                return 'ingreso';
            case '5':
                return 'gasto';
            default:
                return null;
        }
    }

    public function determinarNaturaleza()
    {
        $tipo = $this->tipo ?? $this->determinarTipo();

        return in_array($tipo, ['activo', 'gasto']) ? 'deudor' : 'acreedor';
    }

    public function getSaldoActual()
    {
        $movimientos = $this->movimientos;
        $debe = $movimientos->sum('debe');
        $haber = $movimientos->sum('haber');

        if ($this->naturaleza === 'deudor') {
            return $this->saldo_inicial + $debe - $haber;
        } else {
            return $this->saldo_inicial + $haber - $debe;
        }
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopeImputables($query)
    {
        return $query->where('es_imputable', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
