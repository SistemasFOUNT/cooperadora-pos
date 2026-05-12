<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\PuntoVenta;
use App\Models\CuentaContable;
use App\Models\AsientoContable;
use App\Models\MovimientoContable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class TestFlujContable extends Command
{
    protected $signature = 'contable:test-flujo';
    protected $description = 'Valida que el flujo contable automático está funcionando correctamente';

    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║  TEST DEL FLUJO CONTABLE AUTOMÁTICO    ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->line('');

        // 1. Validar configuración base
        $this->info('PASO 1: Validando configuración base...');

        $punto = PuntoVenta::first();
        if (!$punto) {
            $this->error('❌ No hay puntos de venta. Imposible continuar.');
            return self::FAILURE;
        }
        $this->line("✓ Punto de venta encontrado: {$punto->nombre}");

        $cuentas = CuentaContable::count();
        if ($cuentas === 0) {
            $this->error('❌ No hay cuentas contables. Necesitas crear el plan de cuentas.');
            return self::FAILURE;
        }
        $this->line("✓ {$cuentas} cuentas contables disponibles");

        // 2. Verificar que el evento está registrado
        $this->line('');
        $this->info('PASO 2: Verificando registro de listener...');

        $listeners = Event::getListeners('App\Events\SaleCreated');
        if (empty($listeners)) {
            $this->warn('⚠️  GenerarAsientoVenta listener NO ESTÁ REGISTRADO');
            $this->warn('   Esto significa que los asientos NO se generarán automáticamente');
            $this->warn('   Solución: Verifica que EventServiceProvider esté en bootstrap/providers.php');
            return self::FAILURE;
        }
        $this->line('✓ Listener GenerarAsientoVenta ESTÁ registrado');

        // 3. Contar asientos existentes
        $this->line('');
        $this->info('PASO 3: Verificando estado actual de asientos...');

        $asientosAnteriores = AsientoContable::count();
        $this->line("✓ Asientos actuales en BD: {$asientosAnteriores}");

        // 4. Crear una venta de prueba
        $this->line('');
        $this->info('PASO 4: Simulando creación de venta...');

        // Obtener primeros modelos disponibles
        $user = \App\Models\User::first();
        $paymentMethod = \App\Models\PaymentMethod::first();

        if (!$user || !$paymentMethod) {
            $this->error('❌ Faltan usuarios o métodos de pago para crear venta de prueba');
            return self::FAILURE;
        }

        // Crear venta de prueba
        $ventaPrueba = Sale::create([
            'sale_number' => 'TEST-' . date('YmdHis'),
            'user_id' => $user->id,
            'punto_venta_id' => $punto->id,
            'payment_method_id' => $paymentMethod->id,
            'subtotal' => 1000,
            'tax' => 210,
            'total' => 1210,
            'type' => 'product_sale',
            'status' => 'completed'
        ]);

        $this->line("✓ Venta de prueba creada: {$ventaPrueba->sale_number}");

        // 5. Verificar que se generó el asiento
        $this->line('');
        $this->info('PASO 5: Verificando generación automática de asiento...');

        sleep(1); // Pequeña pausa para asegurar que se procese el evento

        $asientosNuevos = AsientoContable::count();
        $diferencia = $asientosNuevos - $asientosAnteriores;

        if ($diferencia > 0) {
            $this->line("✓ {$diferencia} asiento(s) generado(s) automáticamente");

            // Obtener el asiento generado
            $asiento = AsientoContable::latest('id')->first();

            $this->line('');
            $this->info('DETALLE DEL ASIENTO GENERADO:');
            $this->line("  Número: {$asiento->numero_asiento}");
            $this->line("  Concepto: {$asiento->concepto}");
            $this->line("  Fecha: {$asiento->fecha_asiento}");
            $this->line("  Total DEBE: \${$asiento->total_debe}");
            $this->line("  Total HABER: {$asiento->total_haber}");
            $this->line("  Movimientos: {$asiento->movimientos()->count()}");

            // Detallar movimientos
            $this->line('');
            $this->info('MOVIMIENTOS:');
            foreach ($asiento->movimientos as $mov) {
                $tipo = $mov->debe > 0 ? 'DEBE' : 'HABER';
                $monto = $mov->debe > 0 ? $mov->debe : $mov->haber;
                $cuenta = $mov->cuenta->nombre;
                $codigo = $mov->cuenta->codigo;
                $this->line("  [{$tipo}] {$codigo} - {$cuenta}: \${$monto}");
            }

            // Verificar que balancee
            if ($asiento->verificarBalance()) {
                $this->line('');
                $this->info('✅ ASIENTO BALANCEADO (DEBE = HABER)');
            } else {
                $this->error('');
                $this->error('❌ ASIENTO NO BALANCEA - Error crítico');
                return self::FAILURE;
            }

        } else {
            $this->error('❌ NO se generó asiento automáticamente');
            $this->error('   El evento SaleCreated no se disparó correctamente');
            return self::FAILURE;
        }

        // 6. Resumen final
        $this->line('');
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║         ✅ TEST EXITOSO                ║');
        $this->info('║  Flujo Contable Automático Operativo   ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->line('');
        $this->info('El sistema está listo para generar asientos automáticamente.');
        $this->info('Cada venta creará un asiento con doble entrada en el plan de cuentas.');

        return self::SUCCESS;
    }
}
