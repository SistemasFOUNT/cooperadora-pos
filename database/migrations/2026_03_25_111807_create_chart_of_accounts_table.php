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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Ej: 1.1.1
            $table->string('parent_code', 20)->nullable(); // Para jerarquía
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('nature', ['debit', 'credit']); // naturaleza de la cuenta
            $table->boolean('is_detailed')->default(true); // si acepta movimientos
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_code']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
