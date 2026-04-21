<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero eliminar la tabla existente
        DB::statement('DROP TABLE IF EXISTS students CASCADE');

        // Crear la tabla con nombres de campos en español
        Schema::create('estudiantes', function (Blueprint $table) {
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

            // Campos adicionales del sistema (en español)
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

            // Clave foránea hacia configuracion_cuotas_carreras
            $table->foreign('carrera')->references('tipo_carrera')->on('configuracion_cuotas_carreras');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
