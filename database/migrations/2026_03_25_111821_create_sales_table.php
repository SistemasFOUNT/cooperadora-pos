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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique(); // número de venta único
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->constrained('users'); // cajero que realizó la venta
            $table->foreignId('student_id')->nullable()->constrained('students'); // si es pago de cuota
            $table->foreignId('payment_method_id')->constrained('payment_methods');

            $table->datetime('sale_datetime');
            $table->enum('type', ['product_sale', 'service_sale', 'student_fee', 'treatment', 'other']);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            // Datos fiscales
            $table->string('fiscal_document_type', 10)->nullable(); // A, B, C, X
            $table->string('fiscal_document_number')->nullable();
            $table->string('cae')->nullable(); // Código de Autorización Electrónica
            $table->datetime('cae_expiry')->nullable();

            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'sale_datetime']);
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['fiscal_document_type', 'fiscal_document_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
