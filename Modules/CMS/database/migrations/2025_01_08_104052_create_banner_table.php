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
        // Schema::create('banners', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('title', 255);
        //     $table->string('subtitle', 255)->nullable();
        //     $table->text('description')->nullable();
        //     $table->json('images')->nullable();
        //     $table->string('alt_tag', 255)->nullable();
        //     $table->timestamps();
        //     $table->softDeletes(); 
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('banners');
    }
};
