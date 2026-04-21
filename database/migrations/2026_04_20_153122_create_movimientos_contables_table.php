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
        Schema::create('movimientos_contables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asiento_id');
            $table->unsignedBigInteger('cuenta_id');
            $table->decimal('debe', 15, 2)->default(0);
            $table->decimal('haber', 15, 2)->default(0);
            $table->string('concepto')->nullable(); // Concepto específico del movimiento
            $table->string('referencia')->nullable(); // Referencia adicional
            $table->timestamps();

            $table->foreign('asiento_id')->references('id')->on('asientos_contables')->onDelete('cascade');
            $table->foreign('cuenta_id')->references('id')->on('cuentas_contables');
            $table->index(['asiento_id']);
            $table->index(['cuenta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_contables');
    }
};
