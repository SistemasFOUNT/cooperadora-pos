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
        if (Schema::hasTable('payment_methods')) {
            return;
        }

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Efectivo, Tarjeta Débito, Tarjeta Crédito, Transferencia
            $table->string('code', 10)->unique(); // EFE, TDB, TDC, TRA
            $table->enum('type', ['cash', 'card', 'transfer', 'check', 'other']);
            $table->boolean('requires_authorization')->default(false);
            $table->decimal('commission_percentage', 5, 2)->default(0);
            $table->integer('settlement_days')->default(0); // días de liquidación
            $table->json('configuration')->nullable(); // config específica del método
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
