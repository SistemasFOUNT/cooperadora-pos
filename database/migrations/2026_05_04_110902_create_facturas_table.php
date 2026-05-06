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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('sale_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('punto_venta_id')->nullable()->constrained('puntos_venta');

            // Tipo de factura
            $table->enum('tipo', ['local', 'arca'])->default('local');
            $table->enum('tipo_comprobante', ['A', 'B', 'C'])->nullable(); // Solo para ARCA

            // Numeración
            $table->string('punto_venta', 10)->nullable(); // Punto de venta ARCA
            $table->bigInteger('numero');
            $table->string('numero_completo')->nullable(); // Formato: 0001-00000001

            // Fechas
            $table->datetime('fecha_emision');
            $table->date('fecha_vto_cae')->nullable(); // Solo para ARCA

            // Datos del cliente
            $table->json('datos_cliente')->nullable();
            $table->string('cuit_cliente', 15)->nullable();
            $table->text('razon_social_cliente')->nullable();

            // Importes
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            // ARCA específicos
            $table->string('cae', 20)->nullable(); // Código de Autorización Electrónica
            $table->text('qr_arca')->nullable(); // URL del QR para verificación
            $table->json('respuesta_arca')->nullable(); // Respuesta completa de ARCA

            // Control
            $table->enum('estado', ['emitida', 'pendiente_arca', 'autorizada', 'rechazada', 'anulada'])->default('emitida');
            $table->text('observaciones')->nullable();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            // Índices
            $table->index(['tipo', 'numero']);
            $table->index(['fecha_emision']);
            $table->index(['estado']);
            $table->unique(['tipo', 'punto_venta_id', 'numero'], 'unique_factura_numero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
