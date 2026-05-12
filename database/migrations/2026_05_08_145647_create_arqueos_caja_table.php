<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arqueos_caja', function (Blueprint $table) {
            $table->id();

            // Punto de venta al que pertenece el arqueo
            $table->foreignId('punto_venta_id')->constrained('puntos_venta');

            // Usuario que realiza el arqueo
            $table->foreignId('user_id')->constrained('users');

            // Fecha/hora del arqueo
            $table->datetime('fecha_arqueo');

            // Período que abarca (de cuándo a cuándo)
            $table->datetime('periodo_desde')->nullable();
            $table->datetime('periodo_hasta')->nullable();

            // Totales calculados automáticamente desde ventas del período
            $table->decimal('total_efectivo_calculado', 12, 2)->default(0);
            $table->decimal('total_tarjeta_calculado', 12, 2)->default(0);
            $table->decimal('total_transferencia_calculado', 12, 2)->default(0);
            $table->decimal('total_calculado', 12, 2)->default(0);

            // Totales declarados por el operador (conteo físico)
            $table->decimal('total_efectivo_declarado', 12, 2)->default(0);
            $table->decimal('total_tarjeta_declarado', 12, 2)->default(0);
            $table->decimal('total_transferencia_declarado', 12, 2)->default(0);
            $table->decimal('total_declarado', 12, 2)->default(0);

            // Diferencia (declarado - calculado)
            $table->decimal('diferencia', 12, 2)->default(0);

            // Cantidad de transacciones incluidas
            $table->integer('cantidad_transacciones')->default(0);

            // Estado del arqueo
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->datetime('cerrado_at')->nullable();

            // Observaciones
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arqueos_caja');
    }
};
