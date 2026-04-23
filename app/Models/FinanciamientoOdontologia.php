<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class FinanciamientoOdontologia extends Model
{
    protected $table = 'financiamientos_odontologia';

    protected $fillable = [
        'cliente_deudor_id',
        'venta_id',
        'numero_financiamiento',
        'monto_total',
        'cantidad_cuotas',
        'monto_cuota',
        'tasa_interes_anual',
        'fecha_inicio',
        'fecha_primera_cuota',
        'fecha_ultima_cuota',
        'estado',
        'servicios_detalle',
        'observaciones',
        'motivo_cancelacion',
        'usuario_creacion_id',
        'supervisor_aprobacion_id',
        'fecha_aprobacion'
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_cuota' => 'decimal:2',
        'tasa_interes_anual' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_primera_cuota' => 'date',
        'fecha_ultima_cuota' => 'date',
        'servicios_detalle' => 'array',
        'fecha_aprobacion' => 'datetime'
    ];

    /**
     * Relación con cliente deudor
     */
    public function clienteDeudor(): BelongsTo
    {
        return $this->belongsTo(ClienteDeudor::class);
    }

    /**
     * Relación con venta original
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'venta_id');
    }

    /**
     * Relación con usuario creador
     */
    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creacion_id');
    }

    /**
     * Relación con supervisor que aprobó
     */
    public function supervisorAprobacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_aprobacion_id');
    }

    /**
     * Relación con cuotas
     */
    public function cuotas(): HasMany
    {
        return $this->hasMany(CuotaFinanciamiento::class, 'financiamiento_id');
    }

    /**
     * Relación con documentos legales
     */
    public function documentosLegales(): HasMany
    {
        return $this->hasMany(DocumentoLegal::class, 'financiamiento_id');
    }

    /**
     * Generar número de financiamiento único
     */
    public static function generarNumero()
    {
        $fecha = Carbon::now();
        $count = self::whereDate('created_at', $fecha->toDateString())->count() + 1;
        return 'BOX-FIN-' . $fecha->format('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Crear cuotas automáticamente
     */
    public function generarCuotas()
    {
        $fechaCuota = Carbon::parse($this->fecha_primera_cuota);

        for ($i = 1; $i <= $this->cantidad_cuotas; $i++) {
            CuotaFinanciamiento::create([
                'financiamiento_id' => $this->id,
                'numero_cuota' => $i,
                'monto_cuota' => $this->monto_cuota,
                'fecha_vencimiento' => $fechaCuota->copy()
            ]);

            $fechaCuota->addMonth();
        }
    }

    /**
     * Calcular monto pendiente de pago
     */
    public function montoPendiente()
    {
        return $this->cuotas()
            ->whereIn('estado', ['pendiente', 'vencida'])
            ->sum('monto_cuota');
    }

    /**
     * Verificar si el financiamiento está al día
     */
    public function estaAlDia()
    {
        return !$this->cuotas()
            ->where('estado', 'vencida')
            ->where('fecha_vencimiento', '<', now())
            ->exists();
    }

    /**
     * Obtener próxima cuota a vencer
     */
    public function proximaCuota()
    {
        return $this->cuotas()
            ->where('estado', 'pendiente')
            ->orderBy('fecha_vencimiento')
            ->first();
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($financiamiento) {
            if (empty($financiamiento->numero_financiamiento)) {
                $financiamiento->numero_financiamiento = self::generarNumero();
            }
        });

        static::created(function ($financiamiento) {
            $financiamiento->generarCuotas();
        });
    }
}
