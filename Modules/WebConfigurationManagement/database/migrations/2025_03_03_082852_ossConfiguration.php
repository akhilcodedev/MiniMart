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
        Schema::create('oss_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();  
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_email')->nullable();  
            $table->string('guest_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
