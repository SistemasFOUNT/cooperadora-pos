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
        Schema::table('students', function (Blueprint $table) {
            // Agregar campo is_active después de status
            $table->boolean('is_active')->default(true)->after('status');
        });

        // Migrar datos: status 'active' = is_active true, resto = false
        DB::statement("UPDATE students SET is_active = CASE WHEN status = 'active' THEN true ELSE false END");

        // Agregar índice para optimizar consultas
        Schema::table('students', function (Blueprint $table) {
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');
        });
    }
};
