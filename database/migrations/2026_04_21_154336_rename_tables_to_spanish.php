<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renombrar tablas principales al español
        $renames = [
            'students' => 'estudiantes',
            'career_fee_config' => 'configuracion_cuotas_carreras',
            'sales' => 'ventas',
            'products' => 'productos',
            'employees' => 'empleados',
            'branches' => 'sucursales',
            'payment_methods' => 'metodos_pago',
            'sale_items' => 'items_venta',
            'cash_movements' => 'movimientos_caja',
            'stock_movements' => 'movimientos_stock',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && !Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir nombres en inglés
        $renames = [
            'estudiantes' => 'students',
            'configuracion_cuotas_carreras' => 'career_fee_config',
            'ventas' => 'sales',
            'productos' => 'products',
            'empleados' => 'employees',
            'sucursales' => 'branches',
            'metodos_pago' => 'payment_methods',
            'items_venta' => 'sale_items',
            'movimientos_caja' => 'cash_movements',
            'movimientos_stock' => 'stock_movements',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && !Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }
};
