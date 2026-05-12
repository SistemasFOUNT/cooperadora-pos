<?php

namespace App\Console\Commands;

use App\Models\AsientoContable;
use App\Models\CuentaContable;
use App\Models\MovimientoContable;
use App\Models\PuntoVenta;
use App\Models\Sale;
use App\Services\ContabilidadService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RegularizarAsientosHistoricos extends Command
{
    protected $signature = 'contable:regularizar-asientos
        {--punto= : Codigo de punto de venta (BOX|POSTGRADO|ODONTO)}
        {--desde= : Fecha desde (Y-m-d)}
        {--hasta= : Fecha hasta (Y-m-d)}
        {--solo-ventas : Solo regulariza ventas sin asiento}
        {--solo-egresos : Solo regulariza egresos de movimientos_caja sin asiento}
        {--dry-run : Simula sin grabar cambios}';

    protected $description = 'Genera asientos faltantes para ventas y egresos historicos, sincronizando estado de cuentas con caja/arqueos';

    public function __construct(private readonly ContabilidadService $contabilidad)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $punto = $this->option('punto') ? strtoupper((string) $this->option('punto')) : null;
        $desde = $this->option('desde') ? Carbon::parse((string) $this->option('desde'))->startOfDay() : null;
        $hasta = $this->option('hasta') ? Carbon::parse((string) $this->option('hasta'))->endOfDay() : null;
        $soloVentas = (bool) $this->option('solo-ventas');
        $soloEgresos = (bool) $this->option('solo-egresos');
        $dryRun = (bool) $this->option('dry-run');

        if ($soloVentas && $soloEgresos) {
            $this->error('No puedes usar --solo-ventas y --solo-egresos al mismo tiempo.');
            return self::FAILURE;
        }

        $this->info('=== REGULARIZACION CONTABLE HISTORICA ===');
        $this->line('Punto: ' . ($punto ?: 'TODOS'));
        $this->line('Desde: ' . ($desde ? $desde->toDateTimeString() : 'SIN LIMITE'));
        $this->line('Hasta: ' . ($hasta ? $hasta->toDateTimeString() : 'SIN LIMITE'));
        $this->line('Modo: ' . ($dryRun ? 'DRY-RUN' : 'APLICAR CAMBIOS'));

        $resumen = [
            'ventas_detectadas' => 0,
            'ventas_creadas' => 0,
            'ventas_error' => 0,
            'egresos_detectados' => 0,
            'egresos_creados' => 0,
            'egresos_error' => 0,
        ];

        if (!$soloEgresos) {
            $this->regularizarVentas($punto, $desde, $hasta, $dryRun, $resumen);
        }

        if (!$soloVentas) {
            $this->regularizarEgresosCaja($punto, $desde, $hasta, $dryRun, $resumen);
        }

        $this->newLine();
        $this->info('=== RESUMEN ===');
        $this->line('Ventas sin asiento detectadas: ' . $resumen['ventas_detectadas']);
        $this->line('Asientos de ventas creados: ' . $resumen['ventas_creadas']);
        $this->line('Errores en ventas: ' . $resumen['ventas_error']);
        $this->line('Egresos sin asiento detectados: ' . $resumen['egresos_detectados']);
        $this->line('Asientos de egresos creados: ' . $resumen['egresos_creados']);
        $this->line('Errores en egresos: ' . $resumen['egresos_error']);

        return self::SUCCESS;
    }

    private function regularizarVentas(?string $punto, ?Carbon $desde, ?Carbon $hasta, bool $dryRun, array &$resumen): void
    {
        $query = Sale::query()
            ->where('status', 'completed')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('asientos_contables')
                    ->whereColumn('asientos_contables.referencia_id', 'ventas.id')
                    ->where('asientos_contables.referencia_tipo', 'Sale');
            });

        if ($punto) {
            $puntoVenta = PuntoVenta::where('codigo', $punto)->first();
            if (!$puntoVenta) {
                $this->warn("Punto de venta {$punto} no encontrado para regularizacion de ventas.");
                return;
            }
            $query->where('punto_venta_id', $puntoVenta->id);
        }

        if ($desde) {
            $query->where('fecha_venta', '>=', $desde);
        }
        if ($hasta) {
            $query->where('fecha_venta', '<=', $hasta);
        }

        $ventas = $query->orderBy('id')->get();
        $resumen['ventas_detectadas'] = $ventas->count();

        if ($ventas->isEmpty()) {
            $this->line('No hay ventas pendientes de asiento.');
            return;
        }

        $this->line("Ventas pendientes detectadas: {$ventas->count()}");

        foreach ($ventas as $venta) {
            try {
                if ($dryRun) {
                    $this->line("[DRY-RUN] Venta {$venta->sale_number} (ID {$venta->id}) -> crear asiento");
                    continue;
                }

                $asiento = $this->contabilidad->generarAsientoVenta($venta);

                if ($asiento) {
                    $resumen['ventas_creadas']++;
                } else {
                    $resumen['ventas_error']++;
                    $this->warn("No se pudo generar asiento para venta {$venta->id}");
                }
            } catch (\Throwable $e) {
                $resumen['ventas_error']++;
                $this->error("Error venta {$venta->id}: {$e->getMessage()}");
            }
        }
    }

    private function regularizarEgresosCaja(?string $punto, ?Carbon $desde, ?Carbon $hasta, bool $dryRun, array &$resumen): void
    {
        if (!DB::getSchemaBuilder()->hasTable('movimientos_caja')) {
            $this->warn('La tabla movimientos_caja no existe. Se omite regularizacion de egresos.');
            return;
        }

        $query = DB::table('movimientos_caja as mc')
            ->where('mc.type', 'expense')
            ->where('mc.status', 'completed')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('asientos_contables')
                    ->whereColumn('asientos_contables.referencia_id', 'mc.id')
                    ->where('asientos_contables.referencia_tipo', 'CashMovement');
            });

        if ($punto) {
            $puntoVenta = PuntoVenta::where('codigo', $punto)->first();
            if (!$puntoVenta) {
                $this->warn("Punto de venta {$punto} no encontrado para regularizacion de egresos.");
                return;
            }
            $query->where('mc.branch_id', $puntoVenta->id);
        }

        if ($desde) {
            $query->where('mc.movement_datetime', '>=', $desde);
        }
        if ($hasta) {
            $query->where('mc.movement_datetime', '<=', $hasta);
        }

        $egresos = $query->orderBy('mc.id')->get();
        $resumen['egresos_detectados'] = $egresos->count();

        if ($egresos->isEmpty()) {
            $this->line('No hay egresos de caja pendientes de asiento.');
            return;
        }

        $this->line("Egresos pendientes detectados: {$egresos->count()}");

        foreach ($egresos as $mov) {
            try {
                $puntoVenta = PuntoVenta::find($mov->branch_id);
                if (!$puntoVenta) {
                    $resumen['egresos_error']++;
                    $this->warn("Egreso {$mov->id} sin punto de venta asociado (branch_id {$mov->branch_id}).");
                    continue;
                }

                $cuentaCaja = $this->obtenerCuentaCaja($puntoVenta);
                $cuentaGasto = $this->obtenerCuentaGastoPorConcepto((string) $mov->concept);

                if (!$cuentaCaja || !$cuentaGasto) {
                    $resumen['egresos_error']++;
                    $this->warn("Egreso {$mov->id} sin cuentas contables configuradas.");
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY-RUN] Egreso {$mov->id} ({$mov->concept}) -> DEBE {$cuentaGasto->codigo} / HABER {$cuentaCaja->codigo}");
                    continue;
                }

                DB::transaction(function () use ($mov, $cuentaCaja, $cuentaGasto) {
                    $fecha = Carbon::parse($mov->movement_datetime);
                    $numero = $this->generarNumeroAsiento($fecha);

                    $asiento = AsientoContable::create([
                        'numero' => $numero,
                        'fecha' => $fecha,
                        'concepto' => 'Regularizacion egreso caja: ' . ($mov->concept ?: 'Sin concepto'),
                        'observaciones' => $mov->notes,
                        'tipo' => 'pago',
                        'estado' => 'confirmado',
                        'total_debe' => $mov->amount,
                        'total_haber' => $mov->amount,
                        'usuario_id' => $mov->user_id,
                        'referencia_tipo' => 'CashMovement',
                        'referencia_id' => $mov->id,
                    ]);

                    MovimientoContable::create([
                        'asiento_id' => $asiento->id,
                        'cuenta_id' => $cuentaGasto->id,
                        'debe' => $mov->amount,
                        'haber' => 0,
                        'concepto' => $mov->concept ?: 'Egreso de caja',
                        'referencia' => $mov->movement_number,
                    ]);

                    MovimientoContable::create([
                        'asiento_id' => $asiento->id,
                        'cuenta_id' => $cuentaCaja->id,
                        'debe' => 0,
                        'haber' => $mov->amount,
                        'concepto' => $mov->concept ?: 'Egreso de caja',
                        'referencia' => $mov->movement_number,
                    ]);
                });

                $resumen['egresos_creados']++;
            } catch (\Throwable $e) {
                $resumen['egresos_error']++;
                $this->error("Error egreso {$mov->id}: {$e->getMessage()}");
            }
        }
    }

    private function obtenerCuentaCaja(PuntoVenta $puntoVenta): ?CuentaContable
    {
        if ($puntoVenta->cuenta_caja_id) {
            return CuentaContable::find($puntoVenta->cuenta_caja_id);
        }

        return CuentaContable::where('codigo', 'like', '1.1.1.01.%')
            ->where('nombre', 'like', '%' . $puntoVenta->codigo . '%')
            ->first()
            ?? CuentaContable::where('codigo', '1.1.1.01.000')->first();
    }

    private function obtenerCuentaGastoPorConcepto(string $concepto): ?CuentaContable
    {
        $conceptoLower = mb_strtolower($concepto);

        $map = [
            'insumo' => 'Insumos',
            'laboratorio' => 'Laboratorio',
            'proveedor' => 'Proveedores',
            'servicio' => 'Servicios',
            'honorario' => 'Honorarios',
            'sueldo' => 'Sueldos',
            'impuesto' => 'Impuestos',
            'iva' => 'Impuestos',
        ];

        foreach ($map as $keyword => $likeNombre) {
            if (str_contains($conceptoLower, $keyword)) {
                $cuenta = CuentaContable::where('tipo', 'gasto')
                    ->where('nombre', 'ilike', '%' . $likeNombre . '%')
                    ->first();
                if ($cuenta) {
                    return $cuenta;
                }
            }
        }

        return CuentaContable::where('tipo', 'gasto')->orderBy('codigo')->first();
    }

    private function generarNumeroAsiento(Carbon $fecha): string
    {
        $contador = AsientoContable::whereMonth('fecha', $fecha->month)
            ->whereYear('fecha', $fecha->year)
            ->max('id') ?? 0;

        return $fecha->format('ym') . str_pad((string) ($contador + 1), 5, '0', STR_PAD_LEFT);
    }
}
