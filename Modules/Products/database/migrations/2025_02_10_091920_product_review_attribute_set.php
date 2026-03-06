<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_review_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->timestamps();
        });

        DB::table('product_review_attributes')->insert([
            ['name' => 'price', 'label' => 'Price', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'value', 'label' => 'Value', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'quality', 'label' => 'Quality', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review_attributes');
    }
};