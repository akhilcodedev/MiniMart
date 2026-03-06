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
        Schema::create('carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('guest_fingerprint_code')->nullable();
            $table->string('guest_first_name')->nullable();
            $table->string('guest_last_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_password')->nullable();
            $table->boolean('guest_is_active')->default(true);
            $table->unsignedBigInteger('shipping_method_id')->nullable()->index('new_carts_shipping_method_id_foreign');
            $table->string('shipping_method_name')->nullable();
            $table->string('shipping_method_code')->nullable();
            $table->boolean('shipping_method_status')->default(true);
            $table->unsignedBigInteger('shipping_method_attribute_id')->nullable()->index('new_carts_shipping_method_attribute_id_foreign');
            $table->string('shipping_attribute_name')->nullable();
            $table->string('shipping_attribute_type')->nullable();
            $table->string('shipping_attribute_value')->nullable();
            $table->integer('shipping_attribute_sort_order')->default(1);
            $table->decimal('shipping_cost', 10)->nullable();
            $table->timestamps();
            $table->boolean('is_cart_active')->default(false)->comment('Indicates if the cart is active');
            $table->text('coupon_id')->nullable();
            $table->decimal('total_coupon_amount', 15, 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
