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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->nullable()->change();
            $table->string('customer_code')->nullable()->change();
            $table->boolean('is_active')->default(0)->nullable()->change();
            $table->string('profile_path')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->string('customer_code')->nullable(false)->change();
            $table->boolean('is_active')->default(null)->nullable(false)->change();
            $table->dropColumn('profile_path');
        });
    }
};
