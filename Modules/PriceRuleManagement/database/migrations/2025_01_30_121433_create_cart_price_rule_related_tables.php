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

        if (!Schema::hasTable('coupon_modes')) {
            Schema::create('coupon_modes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 512)->nullable();
                $table->text('name')->nullable();
                $table->unsignedInteger('sort_order')->nullable()->default(0);
                $table->boolean('is_active')->default(1);
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupon_types')) {
            Schema::create('coupon_types', function (Blueprint $table) {
                $table->id();
                $table->string('code', 512)->nullable();
                $table->text('name')->nullable();
                $table->unsignedInteger('sort_order')->nullable()->default(0);
                $table->boolean('is_active')->default(1);
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupon_entities')) {
            Schema::create('coupon_entities', function (Blueprint $table) {
                $table->id();
                $table->string('code', 512)->nullable();
                $table->text('name')->nullable();
                $table->unsignedInteger('sort_order')->nullable()->default(0);
                $table->boolean('is_active')->default(1);
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 512)->nullable();
                $table->string('name', 512)->nullable();
                $table->foreignId('type_id')->nullable()->default(null)->constrained('coupon_types')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('mode_id')->nullable()->default(null)->constrained('coupon_modes')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('entity_id')->nullable()->default(null)->constrained('coupon_entities')->cascadeOnUpdate()->nullOnDelete();
                $table->text('description')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_automatic')->default(0);
                $table->decimal('discount_value', 10, 2)->nullable();
                $table->unsignedInteger('buy_count')->nullable();
                $table->unsignedInteger('get_count')->nullable();
                $table->boolean('has_max_limit')->default(0);
                $table->decimal('max_discount_value', 15, 3)->nullable();
                $table->decimal('min_cart_value', 15, 3)->nullable();
                $table->decimal('max_cart_value', 15, 3)->nullable();
                $table->boolean('customer_eligibility')->nullable()->default(0);
                $table->boolean('region_eligibility')->nullable()->default(0);
                $table->unsignedInteger('order_eligibility')->nullable();
                $table->decimal('order_eligibility_value', 15, 3)->nullable();
                $table->unsignedBigInteger('used_count')->nullable();
                $table->unsignedBigInteger('max_usage_count')->nullable();
                $table->unsignedBigInteger('max_count_per_user')->nullable();
                $table->boolean('is_active')->default(1);
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupon_entity_maps')) {
            Schema::create('coupon_entity_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->nullable()->default(null)->constrained('coupons')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('entity_id')->nullable()->default(null)->constrained('coupon_entities')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->boolean('is_active')->default(1);
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupon_eligibility_maps')) {
            Schema::create('coupon_eligibility_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->nullable()->default(null)->constrained('coupons')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('eligible_code', 512)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->boolean('is_active')->default(1);
                $table->foreignId('created_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('new_carts')) {
            Schema::table('new_carts', function (Blueprint $table) {
                if(!Schema::hasColumn('new_carts','coupon_id')) {
                    $table->text('coupon_id')->nullable();
                }
                if(!Schema::hasColumn('new_carts','total_coupon_amount')) {
                    $table->decimal('total_coupon_amount', 15, 3)->nullable();
                }
            });
        }

        if (Schema::hasTable('new_cart_items')) {
            Schema::table('new_cart_items', function (Blueprint $table) {
                if(!Schema::hasColumn('new_cart_items','coupon_id')) {
                    $table->text('coupon_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('new_orders')) {
            Schema::table('new_orders', function (Blueprint $table) {
                if(!Schema::hasColumn('new_orders','coupon_id')) {
                    $table->text('coupon_id')->nullable();
                }
                if(!Schema::hasColumn('new_orders','total_coupon_amount')) {
                    $table->decimal('total_coupon_amount', 15, 3)->nullable();
                }
            });
        }

        if (Schema::hasTable('new_order_items')) {
            Schema::table('new_order_items', function (Blueprint $table) {
                if(!Schema::hasColumn('new_order_items','coupon_id')) {
                    $table->text('coupon_id')->nullable();
                }
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('new_order_items')) {
            Schema::table('new_order_items', function (Blueprint $table) {
                if(Schema::hasColumn('new_order_items','coupon_id')) {
                    $table->dropColumn('coupon_id');
                }
            });
        }
        if (Schema::hasTable('new_orders')) {
            Schema::table('new_orders', function (Blueprint $table) {
                if(Schema::hasColumn('new_orders','total_coupon_amount')) {
                    $table->dropColumn('total_coupon_amount');
                }
                if(Schema::hasColumn('new_orders','coupon_id')) {
                    $table->dropColumn('coupon_id');
                }
            });
        }
        Schema::dropIfExists('coupon_eligibility_maps');
        Schema::dropIfExists('coupon_entity_maps');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('coupon_entities');
        Schema::dropIfExists('coupon_types');
        Schema::dropIfExists('coupon_modes');
    }
};
