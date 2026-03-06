<?php

namespace Modules\PriceRuleManagement\Traits;

use Illuminate\Support\Facades\Log;
use Modules\PriceRuleManagement\Models\CatalogPriceRule;

trait DiscountApply
{
    protected function applyPriceRuleToProduct($product)
    {
        $rules = CatalogPriceRule::where('is_active', 1)->orderBy('priority', 'asc')->get();
        $updatedPrice = $product->price;
        foreach ($rules as $rule) {
            $conditions = json_decode($rule->conditions, true);
            if ($this->checkIfProductMeetsAllConditions($product, $conditions)) {
                if ($rule->discount_type == 'percentage') {
                    $updatedPrice -= ($product->price * $rule->discount_value / 100);
                } elseif ($rule->discount_type == 'fixed') {
                    $updatedPrice -= $rule->discount_value;
                }
            }

            if($rule->discard_subsequent == 1){
                break;
            }
        }

        return max($updatedPrice, 0);
    }

    protected function checkIfProductMeetsAllConditions($product, $conditions)
    {
        foreach ($conditions as $condition) {
            if (!$this->doesProductMeetCondition($product, $condition)) {
                return false;
            }
        }
        return true;
    }

    protected function doesProductMeetCondition($product, $condition)
    {
        if ($condition['rule_type'] == 'sku') {
            return in_array($product->sku, $condition['rule_values']);
        }

        if ($condition['rule_type'] == 'category') {
            foreach ($product->category as $categoryProduct) {
                if (in_array($categoryProduct->category_id, $condition['rule_values'])) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
}
