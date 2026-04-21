<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renombrar tablas principales al español
        Schema::rename('students', 'estudiantes');
        Schema::rename('career_fee_config', 'configuracion_cuotas_carreras');
        Schema::rename('sales', 'ventas');
        Schema::rename('products', 'productos');
        Schema::rename('employees', 'empleados');
        Schema::rename('branches', 'sucursales');
        Schema::rename('payment_methods', 'metodos_pago');
        Schema::rename('sale_items', 'items_venta');
        Schema::rename('cash_movements', 'movimientos_caja');
        Schema::rename('stock_movements', 'movimientos_stock');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir nombres en inglés
        Schema::rename('estudiantes', 'students');
        Schema::rename('configuracion_cuotas_carreras', 'career_fee_config');
        Schema::rename('ventas', 'sales');
        Schema::rename('productos', 'products');
        Schema::rename('empleados', 'employees');
        Schema::rename('sucursales', 'branches');
        Schema::rename('metodos_pago', 'payment_methods');
        Schema::rename('items_venta', 'sale_items');
        Schema::rename('movimientos_caja', 'cash_movements');
        Schema::rename('movimientos_stock', 'stock_movements');
    }
};
