<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\PuntoVenta;
use App\Services\ContabilidadService;
use Illuminate\Console\Command;

class GenerarAsientoPrueba extends Command
{
    protected $signature = 'contable:generar-prueba {--sale-id=}';
    protected $description = 'Genera un asiento contable para una venta (útil para testing)';

    public function handle(ContabilidadService $contabilidad): int
    {
        $saleId = $this->option('sale-id');

        if (!$saleId) {
            // Obtener última venta
            $sale = Sale::latest('id')->first();
            if (!$sale) {
                $this->error('No hay ventas registradas');
                return self::FAILURE;
            }
        } else {
            $sale = Sale::find($saleId);
            if (!$sale) {
                $this->error("Venta #{$saleId} no encontrada");
                return self::FAILURE;
            }
        }

        $this->info("Generando asiento para venta #{$sale->sale_number}");
        $this->line("Total: \${$sale->total}");
        $this->line("Fecha: {$sale->created_at->format('d/m/Y H:i')}");
        $this->line('');

        try {
            $asiento = $contabilidad->generarAsientoVenta($sale);

            if ($asiento) {
                $this->info('✅ Asiento generado exitosamente');
                $this->line("Número de Asiento: {$asiento->numero_asiento}");
                $this->line("Movimientos: {$asiento->movimientos()->count()}");
                $this->line("Total DEBE: \${$asiento->total_debe}");
                $this->line("Total HABER: {$asiento->total_haber}");

                // Mostrar detalles de movimientos
                $this->line('');
                $this->info('Movimientos:');
                foreach ($asiento->movimientos as $mov) {
                    $tipo = $mov->debe > 0 ? 'DEBE' : 'HABER';
                    $monto = $mov->debe > 0 ? $mov->debe : $mov->haber;
                    $cuenta = $mov->cuenta->nombre;
                    $this->line("  • {$tipo}: \${$monto} - {$cuenta}");
                }

                return self::SUCCESS;
            } else {
                $this->error('❌ Error al generar asiento - revisa logs');
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
