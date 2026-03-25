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
            $table->string('student_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('document_type', 10);
            $table->string('document_number')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->enum('career_type', ['tecnicatura_protesis', 'tecnicatura_asistencia', 'grado_odontologia', 'postgrado']);
            $table->string('career_name')->nullable();
            $table->integer('academic_year')->nullable();
            $table->enum('fee_frequency', ['monthly', 'annual', 'biannual']); // frecuencia de pago
            $table->decimal('fee_amount', 12, 2); // monto de cuota
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'inactive', 'graduated', 'dropout'])->default('active');
            $table->json('additional_data')->nullable();
            $table->timestamps();

            $table->index(['career_type', 'status']);
            $table->index(['student_number']);
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
