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
        Schema::create('cuotas_financiamiento', function (Blueprint $table) {
            $table->id();

            // Referencias
            $table->foreignId('financiamiento_id')->constrained('financiamientos_odontologia')->onDelete('cascade');

            // Identificación de Cuota
            $table->tinyInteger('numero_cuota');

            // Montos
            $table->decimal('monto_cuota', 10, 2);
            $table->decimal('monto_pagado', 10, 2)->default(0.00);
            $table->decimal('recargo_mora', 10, 2)->default(0.00);
            $table->decimal('descuento_aplicado', 10, 2)->default(0.00);

            // Fechas
            $table->date('fecha_vencimiento');
            $table->datetime('fecha_pago')->nullable();

            // Estado
            $table->enum('estado', ['pendiente', 'pagada', 'pagada_parcial', 'vencida', 'condonada'])->default('pendiente');

            // Método de Pago
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia', 'cheque'])->nullable();
            $table->string('numero_comprobante', 50)->nullable();

            // Observaciones
            $table->text('observaciones')->nullable();

            // Auditoria
            $table->foreignId('usuario_cobro_id')->nullable()->constrained('users');

            $table->timestamps();

            // Índices y constraints
            $table->index('financiamiento_id');
            $table->index('fecha_vencimiento');
            $table->index('estado');
            $table->unique(['financiamiento_id', 'numero_cuota']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas_financiamiento');
    }
};
