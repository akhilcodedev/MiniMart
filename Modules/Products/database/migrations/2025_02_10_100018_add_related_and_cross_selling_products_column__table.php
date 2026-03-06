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
        Schema::table('products', function (Blueprint $table) {
            $table->string('related_products')->nullable();
            $table->string('cross_selling_products')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'related_products')) {
                $table->dropColumn('related_products');
            }
            if (Schema::hasColumn('products', 'cross_selling_products')) {
                $table->dropColumn('cross_selling_products');
            }
        });
    }
};
