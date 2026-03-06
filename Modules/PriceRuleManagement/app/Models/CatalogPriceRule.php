<?php

namespace Modules\PriceRuleManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CatalogPriceRule extends Model
{
    use HasFactory;

    const RULE_TYPE_BRAND = 'brand';
    const RULE_TYPE_CATEGORY = 'category';
    const RULE_TYPE_SKU = 'sku';

    const RULE_TYPE_LIST = [
        self::RULE_TYPE_BRAND => 'Brand',
        self::RULE_TYPE_CATEGORY => 'Category',
        self::RULE_TYPE_SKU => 'SKU',
    ];

    const RULE_DISCOUNT_TYPE_FIXED = 'fixed';
    const RULE_DISCOUNT_TYPE_PERCENTAGE = 'percentage';

    const RULE_DISCOUNT_TYPE_LIST = [
        self::RULE_DISCOUNT_TYPE_FIXED => 'Fixed',
        self::RULE_DISCOUNT_TYPE_PERCENTAGE => 'Percentage',
    ];


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'description', 'store_id', 'customer_groups', 'priority', 'discount_type', 'discount_value', 'start_date', 'end_date', 'discard_subsequent', 'conditions', 'is_active'
    ];

    protected $casts = [
        'conditions' => 'array',
    ];
}
