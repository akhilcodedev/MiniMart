<?php

use Carbon\Carbon;
use Modules\OrderManagement\Models\Order;
use Modules\PriceRuleManagement\Models\CatalogPriceRule;
use Modules\WebConfigurationManagement\Models\CoreConfigData;

if (!function_exists('getConfigData')) {
    /**
     * Get the configuration value by key and prefix.
     *
     * @param string $key
     * @param string $prefix
     * @return string|null
     */
    function getConfigData(string $key, string $prefix): ?string
    {
        try {
            $configPath = $prefix . $key;
            return CoreConfigData::where('config_path', $configPath)->value('value');
        } catch (\Exception $e) {
            return null;
        }
    }
}


if (!function_exists('getCountryConfigData')) {
    function getCountryConfigData(string $key): array
    {
        try {
            $latestCountryId = CoreConfigData::orderByDesc('id')->value('country_id');

            if (!$latestCountryId) {
                return [
                    'value' => 0,
                    'tax_type' => null,
                    'error' => 'No country configuration found'
                ];
            }

            $configData = CoreConfigData::where('country_id', $latestCountryId)
                ->where('config_path', $key)
                ->value('value');

            $taxType = CoreConfigData::where('country_id', $latestCountryId)
                ->where('config_path', 'web_configuration_tax_type')
                ->value('value');

            return [
                'value' => floatval($configData ?? 0), 
                'tax_type' => $taxType ?? null
            ];
        } catch (\Exception $e) {
            return [
                'value' => 0,
                'tax_type' => null,
                'error' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}



//if (!function_exists('getCountryConfigData')) {
//    function getCountryConfigData(string $key): array
//    {
//        try {
//            // Fetch the tax value and tax type configuration
//            $configData = CoreConfigData::where('country_id', 1)
//                ->where('config_path', $key)
//                ->value('value');
//
//            $taxType = CoreConfigData::where('country_id', 1)
//                ->where('config_path', 'web_configuration_tax_type')
//                ->value('value');
//
//            return [
//                'value' => floatval($configData ?? 0), // Ensure numeric value
//                'tax_type' => $taxType ?? null  // Default to 'exclusive'
//            ];
//        } catch (\Exception $e) {
//            return [
//                'value' => 0,
//                'tax_type' => null,
//                'error' => 'Error: ' . $e->getMessage()
//            ];
//        }
//    }
//}

if (!function_exists('calculateInclusiveTax')) {
    function calculateInclusiveTax(float $subtotal, float $vatPercentage): float
    {
        return ($subtotal * $vatPercentage) / (100);
    }
}

if (!function_exists('calculateExclusiveTax')) {
    function calculateExclusiveTax(float $subtotal, float $vatPercentage): float
    {
        $vatAmount = ($subtotal * $vatPercentage) / 100;
        return $subtotal + $vatAmount; // Adds VAT to the subtotal
    }
}

//if (!function_exists('calculateTaxAmount')) {
//    function calculateTaxAmount(float $amount, float $vatPercentage, string $taxType): array
//    {
//        $vatAmount = 0;
//        $totalAmount = $amount;
//
//        if ($taxType === 'inclusive') {
//            // Extract VAT from the given amount
//            $vatAmount = ($amount * $vatPercentage) / 100 ;
//            $totalAmount += $vatAmount; // Since VAT is already included
//
//        } else {
//            // Add VAT separately
//            $vatAmount = ($amount * $vatPercentage) / 100;
//            $totalAmount = $amount + $vatAmount;
//            dd($amount);
//        }
//
//        return [
//            'vat_amount' => $vatAmount,
//            'total_amount' => $totalAmount
//        ];
//    }
//}

//if (!function_exists('calculateInclusiveTaxAmount')) {
//    function calculateTaxAmount(float $amount, float $vatPercentage, string $taxType, int $quantity,int $cartItemsCount): array
//    {
//         $vatAmount = 0;
//         $totalAmount = $amount * $quantity;
//         $vatAmount = ($totalAmount * $vatPercentage) / 100 ;
//         $totalAmount += $vatAmount;
//
//
//        return [
//            'vat_amount' => $vatPercentage, // Ensure VAT is also multiplied
//            'total_amount' => $totalAmount
//        ];
//    }
//}
//
//if (!function_exists('calculateExclusiveTaxAmount')) {
//    function calculateExclusiveTaxAmount(float $grandTotal,float $vatPercentage): array
//    {
//        $grandTotal += ($grandTotal * $vatPercentage /100 );
//        return $grandTotal;
//    }
//}

if (!function_exists('calculateTaxAmount')) {
    function calculateTaxAmount(float $amount, float $vatPercentage, string $taxType, int $quantity, int $cartItemsCount, float $grandTotal = 0): array
    {
        $vatAmount = 0;
        $totalAmount = $amount * $quantity;

        if ($taxType === 'inclusive') {
            $vatAmount = ($totalAmount * $vatPercentage) / 100;
            $totalAmount += $vatAmount;

        } elseif ($taxType === 'exclusive') {
            $grandTotal += ($totalAmount * $vatPercentage / 100);
//            dd($grandTotal);
        }

        return [
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'grand_total' => $grandTotal
        ];
    }
}

if (!function_exists('calculateSubtotal')) {
    function calculateSubtotal($order)
    {
        return $order->items->sum(function ($item) {
            return GetFinalPrice($item->product) * $item->quantity;
        });
    }
}

if (!function_exists('GetFinalPrice')) {
    /**
     * @param $product
     * @return finalprice
     */
    function GetFinalPrice($product)
    {
        $currentDate = Carbon::now();

        // Determine if special price is valid
        $finalPrice = ($product->special_price &&
            $product->special_price_from &&
            $product->special_price_to &&
            $currentDate->between($product->special_price_from, $product->special_price_to))
            ? $product->special_price
            : $product->price;

        // Fetch active catalog price rules
        $rules = CatalogPriceRule::where('is_active', 1)
            ->orderBy('priority', 'desc')
            ->get();

        $catalogRule = $rules->first(function ($rule) use ($product) {
            $conditions = json_decode($rule->conditions, true);

            foreach ($conditions as $condition) {
                if (in_array($product->sku, $condition['rule_values']) ||
                    in_array($product->brand_id, $condition['rule_values']) ||
                    in_array($product->category_id, $condition['rule_values'])) {
                    return true;
                }
            }
            return false;
        });

        // If no catalog price rule applies, return the calculated price
        if (!$catalogRule) {
            return $finalPrice;
        }
        // Apply catalog price rule discount
        if ($catalogRule->discount_type === CatalogPriceRule::RULE_DISCOUNT_TYPE_FIXED) {
            $finalPrice = max(0, $finalPrice - $catalogRule->discount_value);
        } elseif ($catalogRule->discount_type === CatalogPriceRule::RULE_DISCOUNT_TYPE_PERCENTAGE) {
            $finalPrice = max(0, $finalPrice - ($finalPrice * ($catalogRule->discount_value / 100)));
        }

        return $finalPrice;
    }
}


if (!function_exists('getTotalCatalogDiscount')) {

    /**
     * Calculate catalog discount, subtotal, shipping cost, coupon discount, other discounts, and grand total.
     *
     * @param int $order_id
     * @return array
     */
    if (!function_exists('getTotalCatalogDiscount')) {

        /**
         * Calculate catalog discount, subtotal, shipping cost, coupon discount, other discounts, and grand total.
         *
         * @param int $order_id
         * @return array
         */
        if (!function_exists('getTotalCatalogDiscount')) {

            /**
             * Calculate catalog discount, subtotal, shipping cost, coupon discount, other discounts, and grand total.
             *
             * @param int $order_id
             * @return array
             */
            function getTotalCatalogDiscount($order_id)
            {
                $order = Order::with(['items.product'])->where('id', $order_id)->first();

                if (!$order) {
                    abort(404, 'Order not found');
                }

                $totalCatalogDiscount = 0;
                $totalItemCount = 0;
                $subtotal = 0;
                $couponDiscount = 0; // Set dynamically if coupons are applied
                $otherDiscount = 0;  // Placeholder for other discounts
                $shippingCost = $order->shipping_cost;  // Set dynamically based on shipping method
                $itemsWithFinalPrice = []; // Store item details

                foreach ($order->items as $item) {
                    $product = $item->product;
                    $originalPrice = $product->price;
                    $specialPrice = GetFinalPrice($product); // Get special/catalog price if applicable

                    // Ensure special price is applied before any discount
                    $finalPrice = ($specialPrice < $originalPrice) ? $specialPrice : $originalPrice;
                    $catalogDiscount = 0;

                    // Fetch active catalog rules
                    $catalogRules = CatalogPriceRule::where('is_active', 1)
                        ->orderBy('priority', 'desc')
                        ->get();

                    foreach ($catalogRules as $rule) {
                        $conditions = json_decode($rule->conditions, true);

                        if (!is_array($conditions)) {
                            continue;
                        }

                        foreach ($conditions as $condition) {
                            if (!isset($condition['rule_type']) || !isset($condition['rule_values'])) {
                                continue;
                            }

                            $ruleType = $condition['rule_type'];
                            $ruleValues = $condition['rule_values'];

                            if (($ruleType === 'sku' && in_array($product->sku, $ruleValues)) ||
                                ($ruleType === 'brand' && in_array($product->brand_id, $ruleValues)) ||
                                ($ruleType === 'category' && in_array($product->category_id, $ruleValues))) {

                                $discountValue = $rule->discount_value;

                                // Deduct catalog discount from final price
                                if ($rule->discount_type === CatalogPriceRule::RULE_DISCOUNT_TYPE_FIXED) {
                                    $catalogDiscount = min($finalPrice, $discountValue);
                                } elseif ($rule->discount_type === CatalogPriceRule::RULE_DISCOUNT_TYPE_PERCENTAGE) {
                                    $catalogDiscount = $finalPrice * ($discountValue / 100);
                                }

                                $totalCatalogDiscount += $catalogDiscount * $item->quantity;
                                $totalItemCount += $item->quantity;
                                break;
                            }
                        }
                    }

                    // Final price per item after special price and catalog discount
                    $finalItemPrice = max(0, $finalPrice - $catalogDiscount);

                    // Store item price after deduction
                    $itemsWithFinalPrice[] = [
                        'item_id' => $item->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'original_price' => $originalPrice,
                        'special_price' => $specialPrice, // Added special price field
                        'final_price_after_discount' => $finalItemPrice,
                        'quantity' => $item->quantity,
                        'total_price' => $finalItemPrice * $item->quantity,
                    ];

                    // Add to subtotal
                    $subtotal += $finalItemPrice * $item->quantity;
                }

                // Calculate grand total
                $grandTotal = max(0, $subtotal + $shippingCost - $couponDiscount - $totalCatalogDiscount - $otherDiscount);

                return [
                    'success' => true,
                    'message' => 'Catalog discount calculated successfully.',
                    'total_catalog_discount' => $totalCatalogDiscount,
                    'total_items_count' => $totalItemCount,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'coupon_discount' => $couponDiscount,
                    'other_discount' => $otherDiscount,
                    'grand_total' => $grandTotal,
                    'items' => $itemsWithFinalPrice, // Include per-item price details
                ];
            }
        }
    }
}
