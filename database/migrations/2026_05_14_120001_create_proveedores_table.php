<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('punto_venta_id')->constrained('puntos_venta');
            $table->string('razon_social');
            $table->string('cuit', 20)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['punto_venta_id', 'activo']);
            $table->unique(['punto_venta_id', 'razon_social'], 'proveedores_pv_razon_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
