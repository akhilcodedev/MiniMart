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
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('controller')->nullable();
            $table->string('module')->nullable();
            $table->string('label')->nullable(); // Adding the label column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('controller');
            $table->dropColumn('module');
            $table->dropColumn('label'); // Dropping the label column
        });
    }
};

