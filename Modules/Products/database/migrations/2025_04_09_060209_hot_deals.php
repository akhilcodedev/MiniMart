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
        Schema::create('hot_deals', function (Blueprint $table) {
            $table->id();
            $table->decimal('discount', 5, 2); // e.g., 25.00%
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('hot_deal_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hot_deal_id')->constrained('hot_deals')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hot_deal_product');
        Schema::dropIfExists('hot_deals');
    }
};
