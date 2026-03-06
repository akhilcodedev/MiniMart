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
  
        // Create the `shipping_methods` table
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Shipping method name
            $table->string('code'); // Unique code for the method
            $table->boolean('status')->default(1); // Status (1 for active, 0 for inactive)
            $table->timestamps(); // Created at and Updated at timestamps
        });

        // Create the `shipping_methods_attributes` table
        Schema::create('shipping_method_attributes', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('shipping_method_id') // Foreign key referencing `shipping_methods`
                ->constrained('shipping_methods')
                ->onDelete('cascade');
            $table->string('name'); // Attribute name
            $table->string('type'); // Input type (e.g., textbox, dropdown, etc.)
            $table->string('value')->nullable(); // Attribute value
            $table->integer('sort_order')->default(1); // Sort order
            $table->timestamps(); // Created at and Updated at timestamps
        });
    

    
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the tables in reverse order of creation
        Schema::dropIfExists('shipping_method_attributes');
        Schema::dropIfExists('shipping_methods');
    }
};
