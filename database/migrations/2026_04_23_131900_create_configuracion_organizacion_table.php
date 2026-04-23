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
        Schema::create('configuracion_organizacion', function (Blueprint $table) {
            $table->id();

            // Datos Identificatorios
            $table->string('razon_social')->default('Asociación Cooperadora');
            $table->string('denominacion_comercial')->default('Facultad de Odontología - UNT');

            // Datos Fiscales Obligatorios
            $table->string('cuit', 13);
            $table->string('numero_ingresos_brutos', 20)->nullable();

            // Domicilio Legal
            $table->string('domicilio_calle');
            $table->string('domicilio_numero', 10);
            $table->string('domicilio_piso', 10)->nullable();
            $table->string('domicilio_depto', 10)->nullable();
            $table->string('localidad', 100);
            $table->string('codigo_postal', 10)->nullable();
            $table->string('provincia', 50);

            // Configuraciones Tributarias
            $table->boolean('responsable_inscripto')->default(true);
            $table->boolean('retiene_ingresos_brutos')->default(false);
            $table->decimal('porcentaje_retencion_iibb', 5, 2)->default(0.00);
            $table->enum('categoria_iva', ['responsable_inscripto', 'excento', 'monotributo'])->default('responsable_inscripto');

            // Datos Adicionales
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('sitio_web', 100)->nullable();

            // Configuraciones del Sistema
            $table->string('logo_path')->nullable();
            $table->text('pie_documentos')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_organizacion');
    }
};
