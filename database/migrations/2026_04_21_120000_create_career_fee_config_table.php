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
        Schema::create('career_fee_config', function (Blueprint $table) {
            $table->id();
            $table->string('career_type'); // tecnicatura_protesis, tecnicatura_asistencia, etc.
            $table->string('career_name'); // Nombre completo de la carrera
            $table->decimal('monthly_fee', 12, 2); // Cuota mensual
            $table->decimal('enrollment_fee', 12, 2)->default(0); // Matrícula inicial
            $table->decimal('certificate_fee', 12, 2)->default(0); // Certificado
            $table->integer('duration_months')->default(24); // Duración en meses
            $table->boolean('is_active')->default(true);
            $table->json('additional_fees')->nullable(); // Otros aranceles (laboratorio, etc.)
            $table->timestamps();

            $table->unique('career_type');
            $table->index(['career_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_fee_config');
    }
};
