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
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); //'simple', 'configurable', 'virtual'
            $table->text('description')->nullable();
            $table->timestamps();
        });


        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('product_type_id')->constrained('product_types')->onDelete('cascade');
            $table->integer('is_in_stock')->nullable();
            $table->text('url_key')->nullable();
            $table->decimal('price', 10, 2);
            $table->float('quantity');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('product_image_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Example: 'primary', 'thumbnail', 'gallery'
            $table->text('description')->nullable(); // Description of the image type
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('image_type_id')->constrained('product_image_types')->onDelete('cascade'); // Links to image types
            $table->string('image_url'); // Image URL or file path
            $table->boolean('is_default')->default(false); // Flag for default image (for main image)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint first
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['image_type_id']);
        });

        // Now drop the tables
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_image_types');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('products');
        Schema::dropIfExists('attribute_set_attributes');
        Schema::dropIfExists('attribute_sets');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('product_types');
        // Schema::dropIfExists('weight_units');
    }
};
