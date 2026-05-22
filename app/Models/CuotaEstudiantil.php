<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CuotaEstudiantil extends Model
{
    protected $table = 'cuotas_estudiantiles';

    protected $fillable = [
        'estudiante_id',
        'tipo_carrera',
        'numero_cuota',
        'anio',
        'periodo',
        'monto_cuota',
        'monto_pagado',
        'recargo_mora',
        'descuento_aplicado',
        'fecha_vencimiento',
        'fecha_pago',
        'estado',
        'metodo_pago',
        'numero_comprobante',
        'factura_id',
        'usuario_cobro_id',
        'observaciones',
    ];

    protected $casts = [
        'monto_cuota'         => 'decimal:2',
        'monto_pagado'        => 'decimal:2',
        'recargo_mora'        => 'decimal:2',
        'descuento_aplicado'  => 'decimal:2',
        'fecha_vencimiento'   => 'date',
        'fecha_pago'          => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'estudiante_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function usuarioCobro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_cobro_id');
    }

    // ──────────────────────────────────────────────
    // Computed attributes
    // ──────────────────────────────────────────────

    /** Días de mora (0 si está pagada o no venció) */
    public function getDiasMoraAttribute(): int
    {
        if (in_array($this->estado, ['pagada', 'condonada'])) {
            return 0;
        }
        if ($this->fecha_vencimiento >= now()->startOfDay()) {
            return 0;
        }
        // Retorna valor absoluto (siempre positivo cuando está vencida)
        return (int) abs(now()->startOfDay()->diffInDays($this->fecha_vencimiento));
    }

    /** Total a pagar incluyendo recargo calculado al momento */
    public function getTotalAPagarAttribute(): float
    {
        return (float) $this->monto_cuota + (float) $this->recargo_mora - (float) $this->descuento_aplicado;
    }

    /** ¿Está vencida hoy? */
    public function getEstaVencidaAttribute(): bool
    {
        return $this->fecha_vencimiento < now()->startOfDay()
            && !in_array($this->estado, ['pagada', 'condonada']);
    }

    /** ¿Es futura (pago por adelantado)? */
    public function getEsAdelantoAttribute(): bool
    {
        return $this->fecha_vencimiento > now()->startOfDay()
            && $this->estado === 'pendiente';
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['pendiente', 'vencida']);
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencida')
            ->orWhere(function ($q) {
                $q->where('estado', 'pendiente')
                  ->where('fecha_vencimiento', '<', now()->startOfDay());
            });
    }

    public function scopePorAnio($query, int $anio)
    {
        return $query->where('anio', $anio);
    }

    // ──────────────────────────────────────────────
    // Helpers estáticos
    // ──────────────────────────────────────────────

    /**
     * Genera las cuotas de un año para un estudiante si no existen.
     * Usa la configuración de carrera para los montos y fechas.
     */
    public static function generarParaEstudiante(Student $estudiante, int $anio): array
    {
        $config = CareerFeeConfig::where('tipo_carrera', $estudiante->carrera)->first();

        if (!$config) {
            return [];
        }

        $meses = [
            1  => 'Enero',   2  => 'Febrero', 3  => 'Marzo',
            4  => 'Abril',   5  => 'Mayo',     6  => 'Junio',
            7  => 'Julio',   8  => 'Agosto',   9  => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $diaVenc  = $config->dia_vencimiento ?? 15;
        $creadas  = [];

        foreach ($meses as $mes => $nombreMes) {
            // No crear cuotas que ya existen
            $existe = static::where('estudiante_id', $estudiante->id)
                ->where('anio', $anio)
                ->where('numero_cuota', $mes)
                ->exists();

            if ($existe) {
                continue;
            }

            $fechaVenc = Carbon::create($anio, $mes, min($diaVenc, Carbon::create($anio, $mes)->daysInMonth));

            $cuota = static::create([
                'estudiante_id'   => $estudiante->id,
                'tipo_carrera'    => $estudiante->carrera,
                'numero_cuota'    => $mes,
                'anio'            => $anio,
                'periodo'         => "{$nombreMes} {$anio}",
                'monto_cuota'     => $config->cuota_mensual,
                'fecha_vencimiento' => $fechaVenc,
                'estado'          => 'pendiente',
            ]);

            $creadas[] = $cuota;
        }

        return $creadas;
    }

    /**
     * Calcula el recargo a aplicar en base a la configuración de carrera.
     */
    public function calcularRecargo(): float
    {
        if (!$this->esta_vencida) {
            return 0.0;
        }

        $config = CareerFeeConfig::where('tipo_carrera', $this->tipo_carrera)->first();

        if (!$config) {
            return 0.0;
        }

        $usaTramos = !is_null($config->dia_vencimiento_1)
            && !is_null($config->dia_vencimiento_2)
            && !is_null($config->porcentaje_recargo_1)
            && !is_null($config->porcentaje_recargo_2)
            && !is_null($config->porcentaje_recargo_3);

        if ($usaTramos) {
            $diaCorte1 = max(1, min(28, (int) $config->dia_vencimiento_1));
            $diaCorte2 = max($diaCorte1, min(31, (int) $config->dia_vencimiento_2));

            // Si la cuota es de un mes anterior, usa directamente el último tramo.
            if ($this->fecha_vencimiento->month !== now()->month || $this->fecha_vencimiento->year !== now()->year) {
                $porcentaje = (float) $config->porcentaje_recargo_3;
            } else {
                $diaActual = now()->day;
                if ($diaActual <= $diaCorte1) {
                    $porcentaje = (float) $config->porcentaje_recargo_1;
                } elseif ($diaActual <= $diaCorte2) {
                    $porcentaje = (float) $config->porcentaje_recargo_2;
                } else {
                    $porcentaje = (float) $config->porcentaje_recargo_3;
                }
            }

            if ($porcentaje <= 0) {
                return 0.0;
            }

            return round((float) $this->monto_cuota * ($porcentaje / 100), 2);
        }

        if ($config->porcentaje_recargo <= 0) {
            return 0.0;
        }

        // Solo aplica recargo si superó los días de gracia
        $diasGracia = $config->dias_gracia ?? 5;
        if ($this->dias_mora <= $diasGracia) {
            return 0.0;
        }

        return round((float) $this->monto_cuota * ($config->porcentaje_recargo / 100), 2);
    }

    /**
     * Registrar el pago de esta cuota.
     */
    public function registrarPago(array $datos): self
    {
        $recargo = $datos['recargo'] ?? $this->calcularRecargo();
        $descuento = (float) ($datos['descuento'] ?? 0);
        $montoPagado = max(0, ((float) $this->monto_cuota + (float) $recargo) - $descuento);

        $this->update([
            'monto_pagado'       => $montoPagado,
            'recargo_mora'       => $recargo,
            'descuento_aplicado' => $descuento,
            'fecha_pago'         => now(),
            'estado'             => 'pagada',
            'metodo_pago'        => $datos['metodo_pago'] ?? null,
            'numero_comprobante' => $datos['numero_comprobante'] ?? null,
            'factura_id'         => $datos['factura_id'] ?? null,
            'usuario_cobro_id'   => $datos['usuario_cobro_id'] ?? auth()->id(),
            'observaciones'      => $datos['observaciones'] ?? null,
        ]);

        return $this;
    }
}
