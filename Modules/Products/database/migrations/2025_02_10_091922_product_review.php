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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_review_attribute_id')->nullable();
            $table->string('title')->nullable();
            $table->integer('star_rating')->nullable();
            $table->text('description')->nullable();
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->enum('status', ['approved', 'pending', 'not_approved'])->default('pending');
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_review_attribute_id')->references('id')->on('product_review_attributes')->onDelete('set null');

            $table->index(['product_id', 'customer_id']);
        });
    }


    public function down(): void
    {
        // Simply drop the table. Foreign keys and indexes are removed automatically when the table is dropped.
        Schema::dropIfExists('product_reviews');
    }
};
