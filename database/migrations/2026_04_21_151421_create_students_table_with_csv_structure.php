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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Campos del CSV
            $table->string('apellido');
            $table->string('nombre');
            $table->string('dni')->unique();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('legajo')->unique();
            $table->string('plan'); // Plan de estudios (78919, 20_TECAD, etc.)
            $table->integer('ingreso'); // Año de ingreso
            $table->integer('reinscripcion'); // Año académico actual

            // Campos adicionales del sistema
            $table->string('carrera'); // Identificador de carrera
            $table->date('fecha_inscripcion'); // Fecha de matrícula (basada en 'ingreso')
            $table->enum('estado', ['activo', 'inactivo', 'graduado', 'abandono'])->default('activo');
            $table->text('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->json('datos_adicionales')->nullable();
            $table->timestamps();

            $table->index(['carrera', 'estado']);
            $table->index(['legajo']);
            $table->index(['dni']);
            $table->index(['activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
