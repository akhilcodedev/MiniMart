<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cart_id')->nullable()->index('order_items_cart_id_foreign');
            $table->unsignedBigInteger('cart_item_id')->nullable()->index('order_items_cart_item_id_foreign');
            $table->unsignedBigInteger('order_id')->nullable()->index('order_items_order_id_foreign');
            $table->unsignedBigInteger('product_id')->nullable()->index('order_items_product_id_foreign');
            $table->string('product_sku')->nullable();
            $table->string('product_name')->nullable();
            $table->unsignedBigInteger('product_type_id')->nullable();
            $table->boolean('product_is_in_stock')->nullable();
            $table->text('product_url_key')->nullable();
            $table->decimal('product_special_price', 10)->nullable();
            $table->date('product_special_price_from')->nullable();
            $table->date('product_special_price_to')->nullable();
            $table->enum('product_status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->string('product_image_url')->nullable();
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
        Schema::dropIfExists('order_items');
    }
};
