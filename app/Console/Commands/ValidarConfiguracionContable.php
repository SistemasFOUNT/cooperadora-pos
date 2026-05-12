<?php

namespace App\Console\Commands;

use App\Models\PuntoVenta;
use App\Models\CuentaContable;
use Illuminate\Console\Command;

class ValidarConfiguracionContable extends Command
{
    protected $signature = 'contable:validar-configuracion';
    protected $description = 'Valida que todas las cuentas contables estén configuradas correctamente para el funcionamiento automático';

    public function handle(): int
    {
        $this->info('Validando configuración contable...');
        $this->line('');

        $errores = [];
        $advertencias = [];

        // 1. Validar cuentas globales
        $this->info('✓ Cuentas globales requeridas:');

        $codigosCuentasGlobales = [
            '1101' => 'Caja General',
            '1301' => 'Deudores por Tarjeta',
            '2101' => 'IVA por Pagar',
            '4100' => 'Ventas Generales',
            '4101' => 'Ventas de Productos',
            '4102' => 'Cuotas Estudiantiles',
            '4103' => 'Prestaciones Clínicas',
            '4104' => 'Servicios Diversos',
        ];

        foreach ($codigosCuentasGlobales as $codigo => $nombre) {
            $cuenta = CuentaContable::where('codigo', $codigo)->first();
            if ($cuenta) {
                $this->line("  ✓ {$codigo}: {$nombre}");
            } else {
                $this->line("  ✗ {$codigo}: {$nombre} [NO EXISTE]");
                $errores[] = "Falta crear cuenta {$codigo}: {$nombre}";
            }
        }

        $this->line('');

        // 2. Validar configuración por punto de venta
        $this->info('✓ Configuración por Punto de Venta:');

        $puntos = PuntoVenta::all();

        if ($puntos->isEmpty()) {
            $advertencias[] = "No hay puntos de venta configurados";
        }

        foreach ($puntos as $punto) {
            $this->line("  📍 {$punto->nombre}:");

            if ($punto->cuenta_caja_id) {
                $cuentaCaja = CuentaContable::find($punto->cuenta_caja_id);
                if ($cuentaCaja) {
                    $this->line("    ✓ Caja: {$cuentaCaja->codigo} - {$cuentaCaja->nombre}");
                } else {
                    $this->line("    ✗ Caja ID {$punto->cuenta_caja_id}: NO EXISTE");
                    $errores[] = "{$punto->nombre}: Cuenta caja ID {$punto->cuenta_caja_id} no existe";
                }
            } else {
                $advertencias[] = "{$punto->nombre}: No tiene cuenta de caja asignada (usará 1101)";
            }

            if ($punto->cuenta_ventas_id) {
                $cuentaVentas = CuentaContable::find($punto->cuenta_ventas_id);
                if ($cuentaVentas) {
                    $this->line("    ✓ Ventas: {$cuentaVentas->codigo} - {$cuentaVentas->nombre}");
                } else {
                    $this->line("    ✗ Ventas ID {$punto->cuenta_ventas_id}: NO EXISTE");
                    $errores[] = "{$punto->nombre}: Cuenta ventas ID {$punto->cuenta_ventas_id} no existe";
                }
            } else {
                $advertencias[] = "{$punto->nombre}: No tiene cuenta de ventas asignada (usará según tipo)";
            }
        }

        $this->line('');

        // 3. Resumen
        if (!empty($errores)) {
            $this->error('❌ ERRORES CRÍTICOS encontrados:');
            foreach ($errores as $error) {
                $this->line("  • {$error}");
            }
            $this->line('');
        }

        if (!empty($advertencias)) {
            $this->warn('⚠️  ADVERTENCIAS:');
            foreach ($advertencias as $adv) {
                $this->line("  • {$adv}");
            }
            $this->line('');
        }

        if (empty($errores)) {
            $this->info('✅ CONFIGURACIÓN VÁLIDA - Sistema contable operativo');
            return self::SUCCESS;
        } else {
            $this->error('❌ CONFIGURACIÓN INCOMPLETA - Sistema contable NO está listo');
            return self::FAILURE;
        }
    }
}
