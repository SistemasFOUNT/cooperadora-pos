<?php

namespace App\Console\Commands;

use App\Models\PuntoVenta;
use App\Models\CuentaContable;
use Illuminate\Console\Command;

class ConfigurarPuntosVenta extends Command
{
    protected $signature = 'puntos-venta:configurar';
    protected $description = 'Configura los puntos de venta con sus cuentas contables correspondientes';

    public function handle()
    {
        $this->info('Configurando puntos de venta...');

        $puntosVenta = [
            [
                'codigo' => 'BOX',
                'nombre' => 'Box Cooperadora',
                'descripcion' => 'Punto de venta principal de la cooperadora',
                'cuentas' => [
                    'caja' => '1.1.1.01.001', // Caja Box
                    'ventas' => '4.1.1.00.000', // BOX COOPERADORA (Ingresos)
                    'deudores' => '1.1.2.01.001', // Alumnos Grado
                    'fondo_fijo' => '1.1.1.05.001' // Fondo Fijo Box
                ]
            ],
            [
                'codigo' => 'POSTGRADO',
                'nombre' => 'Secretaría Postgrado',
                'descripcion' => 'Punto de venta para servicios de postgrado',
                'cuentas' => [
                    'caja' => '1.1.1.01.002', // Caja Postgrado
                    'ventas' => '4.1.3.00.000', // SECRETARIA POSTGRADO (Ingresos)
                    'deudores' => '1.1.2.01.004', // Alumnos Postgrado
                    'fondo_fijo' => '1.1.1.05.002' // Fondo Fijo Postgrado
                ]
            ],
            [
                'codigo' => 'ODONTO',
                'nombre' => 'Centro Odontológico',
                'descripcion' => 'Punto de venta del centro odontológico',
                'cuentas' => [
                    'caja' => '1.1.1.01.003', // Caja Odontologico
                    'ventas' => '4.1.2.00.000', // CENTRO ODONTOLOGICO (Ingresos)
                    'deudores' => '1.1.2.01.006', // Pacientes C. Odontológico
                    'fondo_fijo' => '1.1.1.05.003' // Fondo Fijo C. Odontologico
                ]
            ]
        ];

        foreach ($puntosVenta as $puntoData) {
            $this->info("Configurando {$puntoData['nombre']}...");

            // Buscar las cuentas contables
            $cuentaCaja = CuentaContable::where('codigo', $puntoData['cuentas']['caja'])->first();
            $cuentaVentas = CuentaContable::where('codigo', $puntoData['cuentas']['ventas'])->first();
            $cuentaDeudores = CuentaContable::where('codigo', $puntoData['cuentas']['deudores'])->first();
            $cuentaFondoFijo = CuentaContable::where('codigo', $puntoData['cuentas']['fondo_fijo'])->first();

            if (!$cuentaCaja) {
                $this->error("No se encontró la cuenta de caja: {$puntoData['cuentas']['caja']}");
                continue;
            }

            // Crear o actualizar el punto de venta
            $puntoVenta = PuntoVenta::updateOrCreate(
                ['codigo' => $puntoData['codigo']],
                [
                    'nombre' => $puntoData['nombre'],
                    'descripcion' => $puntoData['descripcion'],
                    'activo' => true,
                    'cuenta_caja_id' => $cuentaCaja?->id,
                    'cuenta_ventas_id' => $cuentaVentas?->id,
                    'cuenta_deudores_id' => $cuentaDeudores?->id,
                    'cuenta_fondo_fijo_id' => $cuentaFondoFijo?->id,
                ]
            );

            $this->info("✅ {$puntoVenta->nombre} configurado correctamente");
            $this->line("   - Caja: {$cuentaCaja->codigo} - {$cuentaCaja->nombre}");
            if ($cuentaVentas) {
                $this->line("   - Ventas: {$cuentaVentas->codigo} - {$cuentaVentas->nombre}");
            }
            if ($cuentaDeudores) {
                $this->line("   - Deudores: {$cuentaDeudores->codigo} - {$cuentaDeudores->nombre}");
            }
            if ($cuentaFondoFijo) {
                $this->line("   - Fondo Fijo: {$cuentaFondoFijo->codigo} - {$cuentaFondoFijo->nombre}");
            }
            $this->line('');
        }

        $this->info('✅ Configuración de puntos de venta completada.');
        $this->info('Total puntos de venta: ' . PuntoVenta::count());

        // Mostrar resumen
        $this->table(
            ['Código', 'Nombre', 'Cuenta Caja', 'Cuenta Ventas'],
            PuntoVenta::with(['cuentaCaja', 'cuentaVentas'])->get()->map(function ($pv) {
                return [
                    $pv->codigo,
                    $pv->nombre,
                    $pv->cuentaCaja ? $pv->cuentaCaja->codigo . ' - ' . $pv->cuentaCaja->nombre : 'N/A',
                    $pv->cuentaVentas ? $pv->cuentaVentas->codigo . ' - ' . $pv->cuentaVentas->nombre : 'N/A',
                ];
            })->toArray()
        );

        return 0;
    }
}
