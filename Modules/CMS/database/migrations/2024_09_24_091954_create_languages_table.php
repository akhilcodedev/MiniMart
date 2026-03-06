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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 56)->nullable();
            $table->string('code', 5);
            $table->string('nicename', 56)->nullable();
            $table->string('iso3', 5)->nullable();
            $table->integer('numcode')->nullable();
            $table->integer('phonecode')->nullable();
            $table->string('dial_code', 10)->nullable(); // New column for dial code
            $table->timestamps();
        });




        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('sort_code', 4)->unique();
            $table->string('name', 100)->unique();
            $table->string('nativeName')->nullable();
            $table->timestamps();
        });

        Schema::create('currencies', function(Blueprint $table)
        {
            $table->id();
            $table->string('name', 50);
            $table->integer('priority')->default(0);
            $table->string('iso_code', 5);
            $table->string('symbol', 10);
            $table->string('subunit', 20);
            $table->integer('subunit_to_unit');
            $table->tinyInteger('symbol_first');
            $table->string('html_entity', 25);
            $table->string('decimal_mark', 10);
            $table->string('thousands_separator', 10);
            $table->smallInteger('iso_numeric')->default(0);
            $table->timestamps();
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->index('name');
            $table->index('priority');
            $table->index('iso_code');
            $table->index('iso_numeric');
        });



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('currencies');

    }

};
