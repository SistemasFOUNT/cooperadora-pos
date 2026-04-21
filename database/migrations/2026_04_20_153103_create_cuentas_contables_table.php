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
        Schema::create('cuentas_contables', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 15)->unique(); // 1.1.1.01.001
            $table->string('nombre'); // Descripción de la cuenta
            $table->enum('tipo', ['activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto'])
                  ->nullable(); // Se determinará automáticamente por código
            $table->enum('naturaleza', ['deudor', 'acreedor'])
                  ->nullable(); // Se determinará automáticamente por tipo
            $table->unsignedBigInteger('cuenta_padre_id')->nullable();
            $table->integer('nivel'); // 1, 2, 3, 4, 5 basado en los puntos del código
            $table->boolean('es_imputable')->default(true); // Si permite movimientos
            $table->boolean('activa')->default(true);
            $table->decimal('saldo_inicial', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('cuenta_padre_id')->references('id')->on('cuentas_contables')->onDelete('cascade');
            $table->index(['codigo']);
            $table->index(['tipo']);
            $table->index(['activa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_contables');
    }
};
