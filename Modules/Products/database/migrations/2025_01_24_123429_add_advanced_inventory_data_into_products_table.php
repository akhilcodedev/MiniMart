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
            $table->unsignedBigInteger('out_of_stock_threshold')->nullable()->after('status')->comment('When a product is out of stock');
            $table->unsignedBigInteger('min_qty_allowed_in_shopping_cart')->nullable()->after('out_of_stock_threshold')->comment('Minimum quantity allowed in shopping cart');
            $table->unsignedBigInteger('max_qty_allowed_in_shopping_cart')->nullable()->after('min_qty_allowed_in_shopping_cart')->comment('Maximum quantity allowed in shopping cart');
            $table->tinyInteger('qty_uses_decimals')->nullable()->default(0)->after('max_qty_allowed_in_shopping_cart')->comment('0 => No , 1 => Yes');
            $table->tinyInteger('backorders')->nullable()->default(0)->after('qty_uses_decimals')
                ->comment('
                0 => No Backorders, 
                1 => Allow Quantity Below 0,
                2 => Allow Quantity Below 0 and Notify Customer,
                3 => Allow Pre-Order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('out_of_stock_threshold');
            $table->dropColumn('min_qty_allowed_in_shopping_cart');
            $table->dropColumn('max_qty_allowed_in_shopping_cart');
            $table->dropColumn('qty_uses_decimals');
            $table->dropColumn('backorders');
        });
    }
};
