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
        Schema::create('documentos_legales', function (Blueprint $table) {
            $table->id();

            // Referencias
            $table->foreignId('cliente_deudor_id')->constrained('clientes_deudores');
            $table->foreignId('financiamiento_id')->nullable()->constrained('financiamientos_odontologia');

            // Identificación del Documento
            $table->enum('tipo_documento', ['compromiso_pago', 'pagare', 'actualizacion_datos', 'cancelacion', 'novacion']);
            $table->string('numero_documento', 30)->unique();

            // Contenido y Archivo
            $table->string('archivo_pdf_path', 500);
            $table->bigInteger('archivo_pdf_size');
            $table->string('hash_documento', 64);

            // Fechas
            $table->datetime('fecha_emision');
            $table->datetime('fecha_firma')->nullable();
            $table->date('fecha_vencimiento')->nullable();

            // Testigos y Firmantes
            $table->string('testigo_1_nombre', 100)->nullable();
            $table->string('testigo_1_dni', 10)->nullable();
            $table->string('testigo_2_nombre', 100)->nullable();
            $table->string('testigo_2_dni', 10)->nullable();
            $table->foreignId('empleado_presente_id')->constrained('users');

            // Estado del Documento
            $table->enum('estado', ['generado', 'impreso', 'pendiente_firma', 'firmado', 'anulado', 'vencido'])->default('generado');

            // Observaciones
            $table->text('observaciones')->nullable();
            $table->string('motivo_anulacion')->nullable();

            // Control de Impresiones
            $table->tinyInteger('cantidad_impresiones')->default(0);
            $table->datetime('ultima_impresion')->nullable();

            // Auditoria
            $table->foreignId('usuario_generacion_id')->constrained('users');

            $table->timestamps();

            // Índices
            $table->index('cliente_deudor_id');
            $table->index('financiamiento_id');
            $table->index('numero_documento');
            $table->index('tipo_documento');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_legales');
    }
};
