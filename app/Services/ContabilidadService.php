<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\AsientoContable;
use App\Models\MovimientoContable;
use App\Models\CuentaContable;
use App\Models\PuntoVenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContabilidadService
{
    /**
     * Generar asiento contable para una venta
     * Asiento estándar:
     *   DEBE: Caja (o deudores según forma de pago)
     *   HABER: Ventas (según tipo de operación)
     */
    public function generarAsientoVenta(Sale $venta): ?AsientoContable
    {
        try {
            DB::beginTransaction();

            $punto = $venta->puntoVenta;
            if (!$punto) {
                Log::warning("Venta {$venta->id}: punto de venta no encontrado");
                return null;
            }

            // Crear el asiento
            $asiento = AsientoContable::create([
                'numero' => $this->generarNumeroAsiento(),
                'fecha' => $venta->fecha_venta ?? now(),
                'concepto' => "Venta - {$venta->tipo}",
                'observaciones' => null,
                'tipo' => 'venta',
                'referencia_tipo' => 'Sale',
                'referencia_id' => $venta->id,
                'usuario_id' => $venta->usuario_id,
                'estado' => 'confirmado',
                'total_debe' => $venta->total,
                'total_haber' => $venta->total,
            ]);

            // Determinar cuenta de destino según forma de pago
            $cuentaDestino = $this->obtenerCuentaPorFormaPago($venta);

            // Obtener cuenta de ventas según tipo de operación
            $cuentaVentas = $this->obtenerCuentaVentasPorTipo($venta->type, $punto);

            if (!$cuentaDestino || !$cuentaVentas) {
                Log::warning("Venta {$venta->id}: cuentas contables no configuradas");
                DB::rollBack();
                return null;
            }

            // Movimiento DEBE (origen del efectivo)
            MovimientoContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => $cuentaDestino->id,
                'debe' => $venta->total,
                'haber' => 0,
                'concepto' => "Ingreso - {$venta->tipo}",
                'referencia' => "Venta #{$venta->sale_number}"
            ]);

            // Movimiento HABER (ingreso por venta)
            MovimientoContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => $cuentaVentas->id,
                'debe' => 0,
                'haber' => $venta->total,
                'concepto' => "Venta - {$venta->type}",
                'referencia' => "Venta #{$venta->sale_number}"
            ]);

            // Registrar impuesto si aplica
            if ($venta->tax_amount > 0) {
                $this->registrarImpuesto($asiento, $venta);
            }

            DB::commit();

            Log::info("Asiento contable {$asiento->numero_asiento} generado para venta {$venta->id}");

            return $asiento;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generando asiento para venta {$venta->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener cuenta según forma de pago
     */
    private function obtenerCuentaPorFormaPago(Sale $venta): ?CuentaContable
    {
        $punto = $venta->puntoVenta;

        if ($venta->paymentMethod && $venta->paymentMethod->codigo === 'tarjeta') {
            // Deudores por tarjeta
            return CuentaContable::where('codigo', '1301')->first();
        }

        // Por defecto, usar caja del punto de venta
        if ($punto->cuenta_caja_id) {
            return CuentaContable::find($punto->cuenta_caja_id);
        }

        // Fallback: caja general
        return CuentaContable::where('codigo', '1101')->first();
    }

    /**
     * Obtener cuenta de ventas según tipo de operación
     */
    private function obtenerCuentaVentasPorTipo(?string $tipo, PuntoVenta $punto): ?CuentaContable
    {
        // Si el punto de venta tiene cuenta de ventas asignada, usarla
        if ($punto->cuenta_ventas_id) {
            return CuentaContable::find($punto->cuenta_ventas_id);
        }

        // Asignar según tipo de operación
        $mapaTipos = [
            'product_sale' => '4101', // Ventas de productos
            'student_fee' => '4102', // Cuotas estudiantiles
            'treatment' => '4103',    // Prestaciones clínicas
            'service_sale' => '4104', // Servicios diversos
        ];

        $codigoCuenta = $mapaTipos[$tipo] ?? '4100'; // Ventas generales

        return CuentaContable::where('codigo', $codigoCuenta)->first();
    }

    /**
     * Registrar impuesto (IVA)
     */
    private function registrarImpuesto(AsientoContable $asiento, Sale $venta): void
    {
        // Cuenta de IVA (pasivo)
        $cuentaIVA = CuentaContable::where('codigo', '2101')->first();

        if ($cuentaIVA) {
            MovimientoContable::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => $cuentaIVA->id,
                'debe' => 0,
                'haber' => $venta->tax_amount,
                'concepto' => "IVA por cobrar",
                'referencia' => "Venta #{$venta->sale_number}"
            ]);

            // Ajustar totales del asiento
            $asiento->update([
                'total_haber' => $asiento->total_haber + $venta->tax_amount
            ]);
        }
    }

    /**
     * Generar número de asiento secuencial
     */
    private function generarNumeroAsiento(): string
    {
        $hoy = now();
        $mes = $hoy->format('m');
        $año = $hoy->format('y');

        // Contar asientos del mes
        $contador = AsientoContable::whereMonth('fecha', $hoy->month)
                        ->whereYear('fecha', $hoy->year)
                                    ->max('id') ?? 0;

        $numero = str_pad($contador + 1, 5, '0', STR_PAD_LEFT);
        return "{$año}{$mes}{$numero}";
    }

    /**
     * Obtener balance de cuenta contable
     */
    public function obtenerSaldoCuenta(CuentaContable $cuenta, $desde = null, $hasta = null): array
    {
        $query = MovimientoContable::where('cuenta_id', $cuenta->id);

        if ($desde) {
            $query->whereDate('fecha', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('fecha', '<=', $hasta);
        }

        $totalDebe = $query->clone()->sum('debe');
        $totalHaber = $query->clone()->sum('haber');

        $saldo = $cuenta->naturaleza === 'deudor'
            ? $cuenta->saldo_inicial + $totalDebe - $totalHaber
            : $cuenta->saldo_inicial + $totalHaber - $totalDebe;

        return [
            'cuenta_id' => $cuenta->id,
            'cuenta_codigo' => $cuenta->codigo,
            'cuenta_nombre' => $cuenta->nombre,
            'saldo_inicial' => $cuenta->saldo_inicial,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'saldo_actual' => $saldo,
            'naturaleza' => $cuenta->naturaleza
        ];
    }

    /**
     * Generar balance de comprobación
     */
    public function obtenerBalanceComprobacion($desde = null, $hasta = null): array
    {
        $cuentas = CuentaContable::activas()->get();

        $totalDebe = 0;
        $totalHaber = 0;
        $balance = [];

        foreach ($cuentas as $cuenta) {
            $saldo = $this->obtenerSaldoCuenta($cuenta, $desde, $hasta);

            // Solo incluir cuentas con movimiento
            if ($saldo['total_debe'] > 0 || $saldo['total_haber'] > 0 || $saldo['saldo_actual'] != 0) {
                $balance[] = $saldo;
                $totalDebe += $saldo['total_debe'];
                $totalHaber += $saldo['total_haber'];
            }
        }

        return [
            'asientos' => $balance,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'balanceado' => abs($totalDebe - $totalHaber) < 0.01
        ];
    }

    /**
     * Libro Diario - Registro cronológico de todos los asientos
     */
    public function obtenerLibroDiario($desde = null, $hasta = null): array
    {
        $query = AsientoContable::with(['movimientos.cuenta', 'usuario', 'venta']);

        if ($desde) {
            $query->whereDate('fecha', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('fecha', '<=', $hasta);
        }

        $asientos = $query->orderBy('fecha', 'asc')
                         ->orderBy('numero', 'asc')
                         ->get();

        $libro = [];
        $saldoAcumulado = 0;

        foreach ($asientos as $asiento) {
            $registro = [
                'numero_asiento' => $asiento->numero_asiento,
                'fecha' => $asiento->fecha_asiento,
                'concepto' => $asiento->concepto,
                'punto_venta' => 'General',
                'usuario' => $asiento->usuario->name ?? 'Sistema',
                'movimientos' => [],
                'total_debe' => $asiento->total_debe,
                'total_haber' => $asiento->total_haber,
            ];

            foreach ($asiento->movimientos as $mov) {
                $registro['movimientos'][] = [
                    'cuenta_codigo' => $mov->cuenta->codigo,
                    'cuenta_nombre' => $mov->cuenta->nombre,
                    'debe' => $mov->debe,
                    'haber' => $mov->haber,
                    'descripcion' => $mov->descripcion,
                ];
            }

            $libro[] = $registro;
        }

        return [
            'periodo_desde' => $desde,
            'periodo_hasta' => $hasta,
            'asientos_totales' => count($libro),
            'asientos' => $libro
        ];
    }

    /**
     * Libro Caja - Movimientos de efectivo por punto de venta
     */
    public function obtenerLibroCaja($puntoVentaId = null, $desde = null, $hasta = null): array
    {
        $query = MovimientoContable::whereHas('asiento')
                                   ->whereHas('cuenta', function ($q) {
                                       // Cuentas de caja (códigos 1101, 1102, etc - comenzando con 1)
                                       $q->whereRaw('codigo LIKE ?', ['1%']);
                                   })
                                   ->with(['asiento', 'cuenta']);

        if ($puntoVentaId) {
            $query->whereHas('asiento', function ($q) use ($puntoVentaId) {
                $q->where('punto_venta_id', $puntoVentaId);
            });
        }

        if ($desde) {
            $query->whereHas('asiento', function ($q) use ($desde) {
                $q->whereDate('fecha', '>=', $desde);
            });
        }
        if ($hasta) {
            $query->whereHas('asiento', function ($q) use ($hasta) {
                $q->whereDate('fecha', '<=', $hasta);
            });
        }

        $movimientos = $query->orderBy('created_at', 'asc')->get();

        $libro = [];
        $saldoCaja = 0;

        foreach ($movimientos as $mov) {
            $asiento = $mov->asiento;

            if ($mov->debe > 0) {
                $saldoCaja += $mov->debe;
                $tipo = 'INGRESO';
                $monto = $mov->debe;
            } else {
                $saldoCaja -= $mov->haber;
                $tipo = 'EGRESO';
                $monto = $mov->haber;
            }

            $libro[] = [
                'fecha' => $asiento->fecha_asiento,
                'numero_asiento' => $asiento->numero_asiento,
                'concepto' => $asiento->concepto,
                'tipo' => $tipo,
                'monto' => $monto,
                'descripcion' => $mov->descripcion,
                'saldo_acumulado' => $saldoCaja,
                'referencia' => $mov->referencia,
            ];
        }

        return [
            'periodo_desde' => $desde,
            'periodo_hasta' => $hasta,
            'punto_venta_id' => $puntoVentaId,
            'movimientos_totales' => count($libro),
            'saldo_final' => $saldoCaja,
            'movimientos' => $libro
        ];
    }

    /**
     * Libro Banco - Movimientos de cuentas bancarias
     */
    public function obtenerLibroBanco($desde = null, $hasta = null): array
    {
        $query = MovimientoContable::whereHas('cuenta', function ($q) {
                                       // Cuentas bancarias (códigos 1201, 1202, etc - comenzando con 12)
                                       $q->whereRaw('codigo BETWEEN ? AND ?', ['1200', '1299']);
                                   })
                                   ->with(['asiento', 'cuenta']);

        if ($desde) {
            $query->whereHas('asiento', function ($q) use ($desde) {
                $q->whereDate('fecha', '>=', $desde);
            });
        }
        if ($hasta) {
            $query->whereHas('asiento', function ($q) use ($hasta) {
                $q->whereDate('fecha', '<=', $hasta);
            });
        }

        $movimientos = $query->orderBy('created_at', 'asc')->get();

        // Agrupar por banco/cuenta
        $libros = [];

        foreach ($movimientos as $mov) {
            $cuentaCodigo = $mov->cuenta->codigo;
            $cuentaNombre = $mov->cuenta->nombre;
            $asiento = $mov->asiento;

            if (!isset($libros[$cuentaCodigo])) {
                $libros[$cuentaCodigo] = [
                    'cuenta_codigo' => $cuentaCodigo,
                    'cuenta_nombre' => $cuentaNombre,
                    'saldo' => 0,
                    'movimientos' => []
                ];
            }

            if ($mov->debe > 0) {
                $libros[$cuentaCodigo]['saldo'] += $mov->debe;
                $tipo = 'DEPÓSITO';
                $monto = $mov->debe;
            } else {
                $libros[$cuentaCodigo]['saldo'] -= $mov->haber;
                $tipo = 'RETIRO';
                $monto = $mov->haber;
            }

            $libros[$cuentaCodigo]['movimientos'][] = [
                'fecha' => $asiento->fecha_asiento,
                'numero_asiento' => $asiento->numero_asiento,
                'concepto' => $asiento->concepto,
                'tipo' => $tipo,
                'monto' => $monto,
                'descripcion' => $mov->descripcion,
                'saldo_acumulado' => $libros[$cuentaCodigo]['saldo'],
                'referencia' => $mov->referencia,
            ];
        }

        return [
            'periodo_desde' => $desde,
            'periodo_hasta' => $hasta,
            'cuentas_totales' => count($libros),
            'libros' => array_values($libros)
        ];
    }

    /**
     * Resumen de Caja - Estado actual de todas las cajas
     */
    public function obtenerResumenCaja($desde = null, $hasta = null): array
    {
        $cajas = CuentaContable::whereRaw('codigo LIKE ?', ['1%'])->get();

        $resumen = [];
        $totalIngresos = 0;
        $totalEgresos = 0;

        foreach ($cajas as $caja) {
            $query = MovimientoContable::where('cuenta_id', $caja->id);

            if ($desde) {
                $query->whereHas('asiento', function ($q) use ($desde) {
                        $q->whereDate('fecha', '>=', $desde);
                });
            }
            if ($hasta) {
                $query->whereHas('asiento', function ($q) use ($hasta) {
                        $q->whereDate('fecha', '<=', $hasta);
                });
            }

            $ingresos = $query->clone()->sum('debe');
            $egresos = $query->clone()->sum('haber');

            $resumen[] = [
                'codigo' => $caja->codigo,
                'nombre' => $caja->nombre,
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'saldo' => $ingresos - $egresos,
            ];

            $totalIngresos += $ingresos;
            $totalEgresos += $egresos;
        }

        return [
            'periodo_desde' => $desde,
            'periodo_hasta' => $hasta,
            'resumen_cajas' => $resumen,
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'saldo_total' => $totalIngresos - $totalEgresos,
        ];
    }
}

