<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('punto_venta_id')->constrained('puntos_venta');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users');

            $table->datetime('fecha_pago');
            $table->enum('tipo_comprobante', ['factura', 'recibo', 'boleta', 'remito', 'otro']);
            $table->string('numero_comprobante', 50);
            $table->date('fecha_comprobante')->nullable();

            $table->string('concepto');
            $table->decimal('monto', 12, 2);
            $table->text('observaciones')->nullable();
            $table->string('comprobante_path')->nullable();
            $table->enum('estado', ['registrado', 'anulado'])->default('registrado');
            $table->timestamps();

            $table->index(['punto_venta_id', 'fecha_pago']);
            $table->index(['proveedor_id', 'fecha_pago']);
            $table->index(['estado']);
            $table->index(['tipo_comprobante', 'numero_comprobante']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_proveedores');
    }
};
