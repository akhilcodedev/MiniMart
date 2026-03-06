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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_sku')->nullable();
            $table->string('product_name')->nullable();
            $table->unsignedBigInteger('product_type_id')->nullable()->index('new_cart_items_product_type_id_foreign');
            $table->integer('product_is_in_stock')->nullable();
            $table->text('product_url_key')->nullable();
            $table->decimal('product_price', 10)->nullable();
            $table->decimal('product_special_price', 10)->nullable();
            $table->date('product_special_price_from')->nullable();
            $table->date('product_special_price_to')->nullable();
            $table->enum('product_status', ['active', 'inactive'])->nullable()->default('active');
            $table->unsignedBigInteger('language_id')->nullable()->index('new_cart_items_language_id_foreign');
            $table->unsignedBigInteger('attribute_id')->nullable()->index('new_cart_items_attribute_id_foreign');
            $table->text('product_attribute_value_text')->nullable();
            $table->integer('product_attribute_value_int')->nullable();
            $table->decimal('product_attribute_value_decimal', 10)->nullable();
            $table->date('product_attribute_value_date')->nullable();
            $table->string('product_image_type_name')->nullable();
            $table->text('product_image_type_description')->nullable();
            $table->unsignedBigInteger('product_image_type_id')->nullable()->index('new_cart_items_product_image_type_id_foreign');
            $table->string('product_image_url')->nullable();
            $table->boolean('product_image_is_default')->nullable()->default(false);
            $table->integer('quantity')->nullable();
            $table->decimal('price', 10)->nullable();
            $table->decimal('total', 10)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->text('coupon_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
