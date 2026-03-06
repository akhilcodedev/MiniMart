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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_number')->nullable();
            $table->unsignedBigInteger('cart_id')->nullable()->index('carts_id_foreign');
            $table->unsignedBigInteger('customer_id')->nullable()->index('orders_customer_id_foreign');
            $table->string('customer_code')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->boolean('is_active')->default(true);
            $table->boolean('guest_is_active')->default(true);
            $table->string('shipping_method_name')->nullable();
            $table->string('shipping_method_code')->nullable();
            $table->string('shipping_method_status')->nullable();
            $table->decimal('shipping_cost', 10)->default(0);
            $table->timestamps();
            $table->string('order_status')->nullable();
            $table->string('payment_status_label')->nullable();
            $table->string('order_status_label')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('coupon_id')->nullable();
            $table->decimal('total_coupon_amount', 15, 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
