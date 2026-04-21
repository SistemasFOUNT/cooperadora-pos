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
        Schema::table('configuracion_cuotas_carreras', function (Blueprint $table) {
            // Renombrar campos al español
            $table->renameColumn('career_type', 'tipo_carrera');
            $table->renameColumn('career_name', 'nombre_carrera');
            $table->renameColumn('monthly_fee', 'cuota_mensual');
            $table->renameColumn('enrollment_fee', 'cuota_inscripcion');
            $table->renameColumn('certificate_fee', 'cuota_certificado');
            $table->renameColumn('duration_months', 'duracion_meses');
            $table->renameColumn('is_active', 'activo');
            $table->renameColumn('additional_fees', 'cuotas_adicionales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_cuotas_carreras', function (Blueprint $table) {
            // Revertir nombres en inglés
            $table->renameColumn('tipo_carrera', 'career_type');
            $table->renameColumn('nombre_carrera', 'career_name');
            $table->renameColumn('cuota_mensual', 'monthly_fee');
            $table->renameColumn('cuota_inscripcion', 'enrollment_fee');
            $table->renameColumn('cuota_certificado', 'certificate_fee');
            $table->renameColumn('duracion_meses', 'duration_months');
            $table->renameColumn('activo', 'is_active');
            $table->renameColumn('cuotas_adicionales', 'additional_fees');
        });
    }
};
