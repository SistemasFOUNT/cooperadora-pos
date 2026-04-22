<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Ejecutar el seeder.
     */
    public function run(): void
    {
        // Productos de laboratorio y papelería
        Product::create([
            'code' => 'LIB001',
            'name' => 'Cuaderno Universitario A4',
            'description' => 'Cuaderno de 80 hojas rayado, tamaño A4, tapa dura',
            'type' => 'product',
            'category' => 'laboratory',
            'price' => 850.00,
            'cost' => 550.00,
            'stock' => 45,
            'min_stock' => 10,
            'track_stock' => true,
            'barcode' => '7891234567890',
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'LIB002',
            'name' => 'Lapicera BIC Azul',
            'description' => 'Lapicera esferográfica BIC cristal azul',
            'type' => 'product',
            'category' => 'laboratory',
            'price' => 120.00,
            'cost' => 80.00,
            'stock' => 120,
            'min_stock' => 20,
            'track_stock' => true,
            'barcode' => '7891234567891',
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'LIB003',
            'name' => 'Resma A4 75gr',
            'description' => 'Resma de papel A4 75 gramos, 500 hojas',
            'type' => 'product',
            'category' => 'laboratory',
            'price' => 1200.00,
            'cost' => 850.00,
            'stock' => 25,
            'min_stock' => 5,
            'track_stock' => true,
            'barcode' => '7891234567892',
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'LIB004',
            'name' => 'Lápiz HB Faber Castell',
            'description' => 'Lápiz de grafito HB Faber Castell',
            'type' => 'product',
            'category' => 'laboratory',
            'price' => 95.00,
            'cost' => 65.00,
            'stock' => 80,
            'min_stock' => 15,
            'track_stock' => true,
            'barcode' => '7891234567893',
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'LIB005',
            'name' => 'Corrector Liquid Paper',
            'description' => 'Corrector líquido blanco con pincel',
            'type' => 'product',
            'category' => 'laboratory',
            'price' => 180.00,
            'cost' => 120.00,
            'stock' => 35,
            'min_stock' => 8,
            'track_stock' => true,
            'barcode' => '7891234567894',
            'is_active' => true,
        ]);

        // Productos de cafetería (otros)
        Product::create([
            'code' => 'CAF001',
            'name' => 'Café con Leche',
            'description' => 'Café con leche caliente, azúcar a gusto',
            'type' => 'service',
            'category' => 'other',
            'price' => 250.00,
            'cost' => 120.00,
            'stock' => 0,
            'min_stock' => 0,
            'track_stock' => false,
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'CAF002',
            'name' => 'Empanada de Carne',
            'description' => 'Empanada horneada rellena de carne cortada a cuchillo',
            'type' => 'product',
            'category' => 'other',
            'price' => 350.00,
            'cost' => 200.00,
            'stock' => 20,
            'min_stock' => 5,
            'track_stock' => true,
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'CAF003',
            'name' => 'Agua Mineral 500ml',
            'description' => 'Agua mineral sin gas, botella de 500ml',
            'type' => 'product',
            'category' => 'other',
            'price' => 180.00,
            'cost' => 100.00,
            'stock' => 48,
            'min_stock' => 10,
            'track_stock' => true,
            'barcode' => '7891234567895',
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'CAF004',
            'name' => 'Sandwich de Jamón y Queso',
            'description' => 'Sandwich de jamón y queso en pan de miga',
            'type' => 'product',
            'category' => 'other',
            'price' => 420.00,
            'cost' => 250.00,
            'stock' => 12,
            'min_stock' => 3,
            'track_stock' => true,
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'CAF005',
            'name' => 'Gaseosa Cola 500ml',
            'description' => 'Gaseosa cola 500ml, bien fría',
            'type' => 'product',
            'category' => 'other',
            'price' => 220.00,
            'cost' => 130.00,
            'stock' => 36,
            'min_stock' => 8,
            'track_stock' => true,
            'barcode' => '7891234567896',
            'is_active' => true,
        ]);

        // Tratamientos odontológicos
        Product::create([
            'code' => 'ODON001',
            'name' => 'Consulta Odontológica',
            'description' => 'Consulta odontológica general con diagnóstico',
            'type' => 'treatment',
            'category' => 'dental_treatment',
            'price' => 2500.00,
            'cost' => 0.00,
            'stock' => 0,
            'min_stock' => 0,
            'track_stock' => false,
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'ODON002',
            'name' => 'Limpieza Dental',
            'description' => 'Profilaxis dental completa con flúor',
            'type' => 'treatment',
            'category' => 'dental_treatment',
            'price' => 3200.00,
            'cost' => 0.00,
            'stock' => 0,
            'min_stock' => 0,
            'track_stock' => false,
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'ODON003',
            'name' => 'Obturación Simple',
            'description' => 'Obturación de una superficie con amalgama',
            'type' => 'treatment',
            'category' => 'dental_treatment',
            'price' => 2800.00,
            'cost' => 300.00,
            'stock' => 0,
            'min_stock' => 0,
            'track_stock' => false,
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'ODON004',
            'name' => 'Extracción Dental Simple',
            'description' => 'Extracción dental simple con anestesia local',
            'type' => 'treatment',
            'category' => 'dental_treatment',
            'price' => 1800.00,
            'cost' => 150.00,
            'stock' => 0,
            'min_stock' => 0,
            'track_stock' => false,
            'is_active' => true,
        ]);

        // Productos de farmacia básica
        Product::create([
            'code' => 'FARM001',
            'name' => 'Ibuprofeno 400mg',
            'description' => 'Ibuprofeno 400mg caja por 20 comprimidos',
            'type' => 'product',
            'category' => 'other',
            'price' => 580.00,
            'cost' => 380.00,
            'stock' => 15,
            'min_stock' => 3,
            'track_stock' => true,
            'barcode' => '7891234567897',
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'FARM002',
            'name' => 'Alcohol en Gel 250ml',
            'description' => 'Alcohol en gel antibacterial 250ml',
            'type' => 'product',
            'category' => 'other',
            'price' => 320.00,
            'cost' => 200.00,
            'stock' => 28,
            'min_stock' => 6,
            'track_stock' => true,
            'barcode' => '7891234567898',
            'is_active' => true,
        ]);

        // Cuotas estudiantiles
        Product::create([
            'code' => 'CUOTA001',
            'name' => 'Cuota Mensual Tecnicatura',
            'description' => 'Cuota mensual para carreras de tecnicatura',
            'type' => 'fee',
            'category' => 'student_fee',
            'price' => 5500.00,
            'cost' => 0.00,
            'stock' => 0,
            'min_stock' => 0,
            'track_stock' => false,
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'CUOTA002',
            'name' => 'Cuota Mensual Grado',
            'description' => 'Cuota mensual para carreras de grado',
            'type' => 'fee',
            'category' => 'student_fee',
            'price' => 7200.00,
            'cost' => 0.00,
            'stock' => 0,
            'min_stock' => 0,
            'track_stock' => false,
            'is_active' => true,
        ]);

        Product::create([
            'code' => 'POSTGRADO001',
            'name' => 'Cuota Postgrado',
            'description' => 'Cuota mensual para carreras de postgrado',
            'type' => 'fee',
            'category' => 'postgraduate_fee',
            'price' => 12500.00,
            'cost' => 0.00,
            'stock' => 0,
            'min_stock' => 0,
            'track_stock' => false,
            'is_active' => true,
        ]);
    }
}
