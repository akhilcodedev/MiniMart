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
        Schema::create('catalog_price_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade')->nullable();
            $table->text('customer_groups')->nullable();
            $table->integer('priority')->default(1)->nullable();;
            $table->json('conditions')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();;
            $table->decimal('discount_value', 10, 2)->nullable();;
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('discard_subsequent')->default(0)->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_price_rules');
    }
};
