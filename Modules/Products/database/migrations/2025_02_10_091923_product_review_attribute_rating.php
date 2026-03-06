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
        if (!Schema::hasTable('product_review_attribute_ratings')) {
            Schema::create('product_review_attribute_ratings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_review_id');
                $table->unsignedBigInteger('product_review_attribute_id')->nullable();
                $table->integer('rating');
                $table->timestamps();

                $table->foreign('product_review_id', 'pra_review_fk')
                    ->references('id')->on('product_reviews')
                    ->onDelete('cascade');

                $table->foreign('product_review_attribute_id', 'pra_attribute_fk')
                    ->references('id')->on('product_review_attributes')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_review_attribute_ratings', function (Blueprint $table) {
            $table->dropForeign('pra_review_fk');
            $table->dropForeign('pra_attribute_fk');
        });

        Schema::dropIfExists('product_review_attribute_ratings');
    }
};
