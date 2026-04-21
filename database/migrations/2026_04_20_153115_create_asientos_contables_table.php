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
        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique(); // A001-00000001
            $table->date('fecha');
            $table->string('concepto');
            $table->text('observaciones')->nullable();
            $table->enum('tipo', ['venta', 'compra', 'pago', 'cobro', 'ajuste', 'apertura', 'cierre', 'manual'])
                  ->default('manual');
            $table->enum('estado', ['borrador', 'confirmado', 'anulado'])
                  ->default('borrador');
            $table->decimal('total_debe', 15, 2)->default(0);
            $table->decimal('total_haber', 15, 2)->default(0);
            $table->unsignedBigInteger('usuario_id');
            $table->string('referencia_tipo')->nullable(); // venta, compra, etc
            $table->unsignedBigInteger('referencia_id')->nullable(); // ID de la venta/compra
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users');
            $table->index(['fecha']);
            $table->index(['tipo']);
            $table->index(['estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asientos_contables');
    }
};
