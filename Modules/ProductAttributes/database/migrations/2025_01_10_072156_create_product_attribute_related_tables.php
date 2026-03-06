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

        if (!Schema::hasTable('product_attributes')) {
            Schema::create('product_attributes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->nullable();
                $table->string('label')->nullable();
                $table->enum('input_type', ['text', 'textarea', 'select', 'multiselect', 'boolean', 'date', 'price'])->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_required')->default(false)->nullable();
                $table->boolean('is_filterable')->default(false)->nullable();
                $table->boolean('is_configurable')->default(false)->nullable();
                $table->boolean('is_sortable')->default(false)->nullable();
                $table->boolean('is_active')->default(true)->nullable();
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('product_attribute_options')) {
            Schema::create('product_attribute_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attribute_id')->nullable()->default(null)->constrained('product_attributes')->nullOnDelete();

                // Storing English and Arabic values in separate columns
                $table->string('english_value')->nullable();
                $table->string('arabic_value')->nullable();

                // Color code storage
                $table->string('color_code', 10)->nullable();

                // Image URL
                $table->text('image_url')->nullable();

                // User tracking
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->nullOnDelete();

                $table->timestamps();
            });
        }


        if (!Schema::hasTable('product_attribute_sets')) {
            Schema::create('product_attribute_sets', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique()->nullable();
                $table->string('label')->nullable();
                $table->string('type')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->nullable();
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attribute_set_maps')) {
            Schema::create('attribute_set_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attribute_set_id')->nullable()->default(null)->constrained('product_attribute_sets')->nullOnDelete();
                $table->foreignId('attribute_id')->nullable()->default(null)->constrained('product_attributes')->nullOnDelete();
                $table->text('value')->nullable();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->nullable();
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_attribute_maps')) {
            Schema::create('product_attribute_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->nullable()->default(null)->constrained('products')->onDelete('cascade');
                $table->foreignId('attribute_set_id')->nullable()->default(null)->constrained('product_attribute_sets')->nullOnDelete();
                $table->foreignId('attribute_id')->nullable()->default(null)->constrained('product_attributes')->nullOnDelete();
                $table->text('description')->nullable();
                $table->text('value')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->nullable();
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_maps');
        Schema::dropIfExists('attribute_set_maps');
        Schema::dropIfExists('product_attribute_options');
        Schema::dropIfExists('product_attribute_sets');
        Schema::dropIfExists('product_attributes');

    }
};
