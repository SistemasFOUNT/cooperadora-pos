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
        Schema::create('puntos_venta', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique(); // BOX, POSTGRADO, ODONTO
            $table->string('nombre'); // Box Cooperadora, Secretaría Postgrado, Centro Odontológico
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);

            // Cuentas contables asociadas
            $table->unsignedBigInteger('cuenta_caja_id')->nullable();
            $table->unsignedBigInteger('cuenta_ventas_id')->nullable();
            $table->unsignedBigInteger('cuenta_deudores_id')->nullable();
            $table->unsignedBigInteger('cuenta_fondo_fijo_id')->nullable();

            $table->timestamps();

            $table->foreign('cuenta_caja_id')->references('id')->on('cuentas_contables');
            $table->foreign('cuenta_ventas_id')->references('id')->on('cuentas_contables');
            $table->foreign('cuenta_deudores_id')->references('id')->on('cuentas_contables');
            $table->foreign('cuenta_fondo_fijo_id')->references('id')->on('cuentas_contables');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_venta');
    }
};
