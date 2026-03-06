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
        if (Schema::hasTable('attribute_set_maps')) {
            Schema::table('attribute_set_maps', function (Blueprint $table) {
                if(!Schema::hasColumn('attribute_set_maps','is_required')) {
                    $table->unsignedTinyInteger('is_required')->default(0)->nullable()->comment(' 0 for No, 1 for Yes')->after('sort_order');
                }
                if(!Schema::hasColumn('attribute_set_maps','is_filterable')) {
                    $table->unsignedTinyInteger('is_filterable')->default(1)->nullable()->comment(' 0 for No, 1 for Yes')->after('is_required');
                }
                if(!Schema::hasColumn('attribute_set_maps','is_sortable')) {
                    $table->unsignedTinyInteger('is_sortable')->default(1)->nullable()->comment(' 0 for No, 1 for Yes')->after('is_filterable');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attribute_set_maps')) {
            Schema::table('attribute_set_maps', function (Blueprint $table) {
                if(Schema::hasColumn('attribute_set_maps','is_sortable')) {
                    $table->dropColumn('is_sortable');
                }
                if(Schema::hasColumn('attribute_set_maps','is_filterable')) {
                    $table->dropColumn('is_filterable');
                }
                if(Schema::hasColumn('attribute_set_maps','is_required')) {
                    $table->dropColumn('is_required');
                }
            });
        }
    }
};
