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
        Schema::table('students', function (Blueprint $table) {
            // Los campos redundantes ya no existen en la nueva estructura
            // Solo agregar clave foránea hacia career_fee_config
            $table->foreign('carrera')->references('career_type')->on('career_fee_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Eliminar la clave foránea
            $table->dropForeign(['carrera']);
        });
    }
};
