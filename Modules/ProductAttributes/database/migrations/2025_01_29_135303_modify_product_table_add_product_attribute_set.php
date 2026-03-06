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
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if(!Schema::hasColumn('products','attribute_set_id')) {
                    $table->unsignedBigInteger('attribute_set_id')->nullable();
                    $table->foreign('attribute_set_id')->references('id')->on('product_attribute_sets')->cascadeOnUpdate()->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if(Schema::hasColumn('products','attribute_set_id')) {
                    $table->dropForeign(['attribute_set_id']);
                }
            });
        }
    }
};
