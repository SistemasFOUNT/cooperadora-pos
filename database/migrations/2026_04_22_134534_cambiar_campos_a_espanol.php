<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        // Cambiar campos en tabla ventas
        Schema::table('ventas', function (Blueprint $table) {
            // Renombrar branch_id a punto_venta_id
            if (Schema::hasColumn('ventas', 'branch_id')) {
                $table->renameColumn('branch_id', 'punto_venta_id');
            }
            // Renombrar user_id a usuario_id
            if (Schema::hasColumn('ventas', 'user_id')) {
                $table->renameColumn('user_id', 'usuario_id');
            }
            // Renombrar total_amount a total
            if (Schema::hasColumn('ventas', 'total_amount')) {
                $table->renameColumn('total_amount', 'total');
            }
            // Renombrar sale_datetime a fecha_venta si existe
            if (Schema::hasColumn('ventas', 'sale_datetime')) {
                $table->renameColumn('sale_datetime', 'fecha_venta');
            }
        });

        // La tabla users ya tiene punto_venta_id, no necesita cambios

        // Cambiar campos en tabla productos si necesario
        if (Schema::hasTable('productos')) {
            Schema::table('productos', function (Blueprint $table) {
                // Si existe branch_id, renombrarlo a punto_venta_id
                if (Schema::hasColumn('productos', 'branch_id')) {
                    $table->renameColumn('branch_id', 'punto_venta_id');
                }
            });
        }
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        // Revertir cambios en tabla ventas
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'punto_venta_id')) {
                $table->renameColumn('punto_venta_id', 'branch_id');
            }
            if (Schema::hasColumn('ventas', 'usuario_id')) {
                $table->renameColumn('usuario_id', 'user_id');
            }
            if (Schema::hasColumn('ventas', 'total')) {
                $table->renameColumn('total', 'total_amount');
            }
            if (Schema::hasColumn('ventas', 'fecha_venta')) {
                $table->renameColumn('fecha_venta', 'sale_datetime');
            }
        });

        // No revertir users ya que ya tenía punto_venta_id

        // Revertir cambios en tabla productos
        if (Schema::hasTable('productos')) {
            Schema::table('productos', function (Blueprint $table) {
                if (Schema::hasColumn('productos', 'punto_venta_id')) {
                    $table->renameColumn('punto_venta_id', 'branch_id');
                }
            });
        }
    }
};
