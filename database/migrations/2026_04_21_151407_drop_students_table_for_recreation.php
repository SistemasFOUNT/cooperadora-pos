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
        // Usar SQL directo para DROP CASCADE en PostgreSQL
        DB::statement('DROP TABLE IF EXISTS students CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se puede revertir fácilmente, esta migración destruye datos
    }
};
