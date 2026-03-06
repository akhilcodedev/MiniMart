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
        Schema::table('cart_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('cart_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_addresses', function (Blueprint $table) {
            $table->dropColumn('customer_id');

        });
    }
};
