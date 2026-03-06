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
        // Creating the 'role_resources' table
        Schema::create('role_resources', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_id')->unsigned()->nullable();
            $table->bigInteger('parent_role_id')->unsigned()->nullable();
            $table->string('controllers');
            $table->foreign('parent_role_id')->references('id')->on('roles');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->timestamps();
        });

        // Creating the 'user_role_permission' table
        Schema::create('user_role_permission', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('user_name');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        // Altering the 'user_role_permission' table to update foreign keys
        Schema::table('user_role_permission', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping the 'role_resources' and 'user_role_permission' tables
        Schema::dropIfExists('role_resources');
        Schema::dropIfExists('user_role_permission');
    }
};
