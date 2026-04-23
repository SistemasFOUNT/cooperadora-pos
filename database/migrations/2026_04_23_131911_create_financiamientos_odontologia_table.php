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
        Schema::create('financiamientos_odontologia', function (Blueprint $table) {
            $table->id();

            // Referencias
            $table->foreignId('cliente_deudor_id')->constrained('clientes_deudores');
            $table->foreignId('venta_id')->nullable()->constrained('ventas');

            // Identificación
            $table->string('numero_financiamiento', 20)->unique();

            // Montos y Condiciones
            $table->decimal('monto_total', 12, 2);
            $table->tinyInteger('cantidad_cuotas');
            $table->decimal('monto_cuota', 12, 2);
            $table->decimal('tasa_interes_anual', 5, 2)->default(0.00);

            // Fechas
            $table->date('fecha_inicio');
            $table->date('fecha_primera_cuota');
            $table->date('fecha_ultima_cuota');

            // Estado del Financiamiento
            $table->enum('estado', ['pendiente_documentacion', 'activo', 'completado', 'cancelado', 'ejecutado'])->default('pendiente_documentacion');

            // Servicios Financiados (JSON)
            $table->json('servicios_detalle');

            // Observaciones
            $table->text('observaciones')->nullable();
            $table->string('motivo_cancelacion')->nullable();

            // Auditoria
            $table->foreignId('usuario_creacion_id')->constrained('users');
            $table->foreignId('supervisor_aprobacion_id')->nullable()->constrained('users');
            $table->timestamp('fecha_aprobacion')->nullable();

            $table->timestamps();

            // Índices
            $table->index('cliente_deudor_id');
            $table->index('estado');
            $table->index('numero_financiamiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financiamientos_odontologia');
    }
};
