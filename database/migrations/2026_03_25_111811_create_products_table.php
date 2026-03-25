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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['product', 'service', 'fee', 'treatment']); // producto, servicio, cuota, tratamiento
            $table->enum('category', ['laboratory', 'dental_treatment', 'student_fee', 'postgraduate_fee', 'other']);
            $table->decimal('price', 12, 2);
            $table->decimal('cost', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->string('barcode')->nullable();
            $table->json('additional_data')->nullable(); // datos específicos según tipo
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'category']);
            $table->index(['is_active']);
            $table->index(['barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
