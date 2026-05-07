<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas_estudiantiles', function (Blueprint $table) {
            $table->id();

            // Referencias
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->string('tipo_carrera'); // Snapshot del tipo de carrera al generar

            // Identificación
            $table->tinyInteger('numero_cuota')->comment('Número de cuota dentro del año: 1=Enero, ..., 12=Diciembre');
            $table->smallInteger('anio')->comment('Año al que corresponde la cuota');
            $table->string('periodo', 20)->comment('Ej: Marzo 2026');

            // Montos
            $table->decimal('monto_cuota', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0.00);
            $table->decimal('recargo_mora', 12, 2)->default(0.00);
            $table->decimal('descuento_aplicado', 12, 2)->default(0.00);

            // Fechas
            $table->date('fecha_vencimiento');
            $table->datetime('fecha_pago')->nullable();

            // Estado
            $table->enum('estado', [
                'pendiente',
                'pagada',
                'vencida',
                'adelantada',
                'condonada'
            ])->default('pendiente');

            // Datos del cobro
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia', 'mixto'])->nullable();
            $table->string('numero_comprobante', 50)->nullable();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();

            // Auditoría
            $table->foreignId('usuario_cobro_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['estudiante_id', 'anio']);
            $table->index(['fecha_vencimiento']);
            $table->index(['estado']);
            $table->unique(['estudiante_id', 'anio', 'numero_cuota'], 'unique_cuota_estudiante');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas_estudiantiles');
    }
};
