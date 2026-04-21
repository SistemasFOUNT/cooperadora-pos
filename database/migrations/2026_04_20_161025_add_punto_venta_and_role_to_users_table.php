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
        Schema::table('users', function (Blueprint $table) {
            // Agregar relación con punto de venta
            $table->foreignId('punto_venta_id')->nullable()->constrained('puntos_venta')->after('branch_id');

            // Agregar rol del usuario
            $table->enum('role', ['admin', 'usuario_box', 'usuario_postgrado', 'usuario_odonto'])
                  ->default('usuario_box')
                  ->after('punto_venta_id');

            // Agregar permisos específicos (JSON)
            $table->json('permisos')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['punto_venta_id']);
            $table->dropColumn(['punto_venta_id', 'role', 'permisos']);
        });
    }
};
