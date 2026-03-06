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
        Schema::create('url_rewrites', function (Blueprint $table) {
            $table->id('id'); 
            $table->string('entity_type'); 
            $table->unsignedBigInteger('entity_id'); 
            $table->string('request_path')->unique();
            $table->string('target_path'); 
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_rewrites');
    }
};
