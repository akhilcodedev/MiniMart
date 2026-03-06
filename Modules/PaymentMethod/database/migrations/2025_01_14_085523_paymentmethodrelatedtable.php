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

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 512)->nullable();
            $table->text('name')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->nullable()->default(0);
            $table->text('description')->nullable();
            $table->json('credentials')->nullable()->comment('credentials in json format');
            $table->unsignedTinyInteger('test_mode')->nullable()->default(0)->comment('0 = false, 1 = true');
            $table->boolean('is_online')->nullable()->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // Create the `shipping_methods_attributes` table
        Schema::create('payment_method_attributes', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('payment_method_id') // Foreign key referencing `shipping_methods`
            ->constrained('payment_methods')
                ->onDelete('cascade');
            $table->string('name'); // Attribute name
            $table->string('type'); // Input type (e.g., textbox, dropdown, etc.)
            $table->string('value')->nullable(); // Attribute value
            $table->integer('sort_order')->default(1); // Sort order
            $table->timestamps(); // Created at and Updated at timestamps
        });
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 4)->default(0.00)->nullable();
            $table->string('currency_code')->nullable();
            $table->string('payment_ref')->nullable();
            $table->string('transaction_id')->nullable();
            $table->foreignId('customer_id')->nullable()->default(null)->constrained('customers')->cascadeOnUpdate()->nullOnDelete();
            //$table->foreignId('subscription_id')->nullable()->default(null)->constrained('subscription')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->default(null)->constrained('payment_methods')->cascadeOnUpdate()->nullOnDelete();
            $table->string('type')->nullable();
            $table->date('date')->nullable();
            $table->text('payment_detail')->nullable();
            $table->text('comments')->nullable();
            $table->unsignedTinyInteger('status')->nullable()->default(0)->comment('0 = failed, 1 = success');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the tables in reverse order of creation
        Schema::dropIfExists('payment_histories');
        Schema::dropIfExists('payment_method_attributes');
        Schema::dropIfExists('payment_methods');
    }
};
