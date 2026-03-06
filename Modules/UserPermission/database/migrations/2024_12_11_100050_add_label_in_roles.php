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
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('id'); // Add the 'created_by' column
            $table->softDeletes(); // Add soft deletes
            $table->string('label')->nullable(); // Add the 'label' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('created_by'); // Drop the 'created_by' column
            $table->dropSoftDeletes(); // Drop the soft deletes column
            $table->dropColumn('label'); // Drop the 'label' column
        });
    }
};
