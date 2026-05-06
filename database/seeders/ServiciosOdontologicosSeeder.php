<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ServiciosOdontologicosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reclasificar café como producto
        $cafe = Product::where('name', 'Café con Leche')->first();
        if ($cafe) {
            $cafe->update(['type' => 'product']);
            $this->command->info('✅ Café reclasificado como producto físico');
        }

        // 2. Crear servicios odontológicos reales
        $serviciosOdontologicos = [
            [
                'code' => 'ODO001',
                'name' => 'Consulta Odontológica General',
                'description' => 'Revisión y diagnóstico completo',
                'type' => 'service',
                'category' => 'dental_treatment',
                'price' => 8500.00,
                'cost' => 0.00,
                'is_active' => true,
                'track_stock' => false
            ],
            [
                'code' => 'ODO002',
                'name' => 'Limpieza Dental Profunda',
                'description' => 'Profilaxis y limpieza profesional',
                'type' => 'service',
                'category' => 'dental_treatment',
                'price' => 15000.00,
                'cost' => 0.00,
                'is_active' => true,
                'track_stock' => false
            ],
            [
                'code' => 'ODO003',
                'name' => 'Extracción Dental Simple',
                'description' => 'Extracción de pieza dental',
                'type' => 'service',
                'category' => 'dental_treatment',
                'price' => 12000.00,
                'cost' => 0.00,
                'is_active' => true,
                'track_stock' => false
            ],
            [
                'code' => 'ODO004',
                'name' => 'Radiografía Intraoral',
                'description' => 'Radiografía dental diagnóstica',
                'type' => 'service',
                'category' => 'dental_treatment',
                'price' => 6000.00,
                'cost' => 0.00,
                'is_active' => true,
                'track_stock' => false
            ],
            [
                'code' => 'ODO005',
                'name' => 'Empaste Dental',
                'description' => 'Restauración con composite',
                'type' => 'service',
                'category' => 'dental_treatment',
                'price' => 18000.00,
                'cost' => 0.00,
                'is_active' => true,
                'track_stock' => false
            ]
        ];

        foreach ($serviciosOdontologicos as $servicio) {
            $existe = Product::where('code', $servicio['code'])->first();
            if (!$existe) {
                Product::create($servicio);
                $this->command->info("✅ Creado: {$servicio['name']}");
            } else {
                $this->command->warn("⚠️ Ya existe: {$servicio['name']}");
            }
        }

        $this->command->info('');
        $this->command->info('📊 RESUMEN FINAL:');
        $this->command->info('Productos físicos: ' . Product::where('type', 'product')->count());
        $this->command->info('Servicios odontológicos: ' . Product::where('type', 'service')->count());
    }
}
