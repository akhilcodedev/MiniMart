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

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if(!Schema::hasColumn('orders','payment_method_id')) {
                    $table->unsignedBigInteger('payment_method_id')->nullable()->default(null)->after('payment_status');
                }
                if(!Schema::hasColumn('orders','payment_ref')) {
                    $table->string('payment_ref')->nullable()->after('payment_method_id');
                }
            });
        }

        if (Schema::hasTable('payment_histories')) {
            Schema::table('payment_histories', function (Blueprint $table) {
                if(!Schema::hasColumn('payment_histories','order_id')) {
                    $table->unsignedBigInteger('order_id')->nullable()->default(null)->after('customer_id');
                }
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        if (Schema::hasTable('payment_histories')) {
            Schema::table('payment_histories', function (Blueprint $table) {
                if(Schema::hasColumn('payment_histories','order_id')) {
                    $table->dropColumn('order_id');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if(Schema::hasColumn('orders','payment_ref')) {
                    $table->dropColumn('payment_ref');
                }
                if(Schema::hasColumn('orders','payment_method_id')) {
                    $table->dropColumn('payment_method_id');
                }
            });
        }

    }
};
