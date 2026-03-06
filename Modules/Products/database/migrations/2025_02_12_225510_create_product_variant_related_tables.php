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

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if(!Schema::hasColumn('products','is_variant')) {
                    $table->boolean('is_variant')->nullable()->default(0);
                }
                if(!Schema::hasColumn('products','parent_id')) {
                    $table->unsignedBigInteger('parent_id')->nullable()->default(null);
                }
                if(!Schema::hasColumn('products','variant_products')) {
                    $table->text('variant_products')->nullable();
                }
            });
        }

        if (!Schema::hasTable('product_variant_options')) {
            Schema::create('product_variant_options', function (Blueprint $table) {
                $table->id();
                $table->string('code', 512)->nullable();
                $table->text('name')->nullable();
                $table->boolean('is_active')->nullable()->default(1);
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_variant_option_maps')) {
            Schema::create('product_variant_option_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->nullable()->default(null)->constrained('products')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('option_id')->nullable()->default(null)->constrained('product_variant_options')->cascadeOnUpdate()->nullOnDelete();
                $table->text('value')->nullable();
                $table->integer('sort_order')->nullable()->default(0);
                $table->boolean('is_active')->nullable()->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_tags')) {
            Schema::create('product_tags', function (Blueprint $table) {
                $table->id();
                $table->string('name', 512)->nullable();
                $table->boolean('is_active')->nullable()->default(1);
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_tag_maps')) {
            Schema::create('product_tag_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->nullable()->default(null)->constrained('products')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('tag_id')->nullable()->default(null)->constrained('product_tags')->cascadeOnUpdate()->nullOnDelete();
                $table->boolean('is_active')->nullable()->default(1);
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('product_tag_maps');
        Schema::dropIfExists('product_tags');
        Schema::dropIfExists('product_variant_option_maps');
        Schema::dropIfExists('product_variant_options');
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if(Schema::hasColumn('products','variant_products')) {
                    $table->dropColumn('variant_products');
                }
                if(Schema::hasColumn('products','parent_id')) {
                    $table->dropColumn('parent_id');
                }
                if(Schema::hasColumn('products','is_variant')) {
                    $table->dropColumn('is_variant');
                }
            });
        }

    }
};
