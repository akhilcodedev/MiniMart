<?php

namespace Modules\ShippingMethode\Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Modules\ShippingMethode\Models\ShippingMethod;
use Modules\ShippingMethode\Models\ShippingMethodAttribute;

class ShippingMethodSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // Define actual shipping methods
            $shippingMethods = [
                [
                    'name' => 'Standard Shipping',
                    'code' => 'standard',
                    'status' => 1,
                    'attributes' => [
                        [
                            'name' => 'Delivery Time',
                            'type' => 'text',
                            'value' => '3-5 Business Days',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Cost',
                            'type' => 'text',
                            'value' => 'Free',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'name' => 'Express Shipping',
                    'code' => 'express',
                    'status' => 1,
                    'attributes' => [
                        [
                            'name' => 'Delivery Time',
                            'type' => 'text',
                            'value' => '1-2 Business Days',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Cost',
                            'type' => 'text',
                            'value' => '$10.00',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'name' => 'Overnight Shipping',
                    'code' => 'overnight',
                    'status' => 1,
                    'attributes' => [
                        [
                            'name' => 'Delivery Time',
                            'type' => 'text',
                            'value' => 'Next Day Delivery',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Cost',
                            'type' => 'text',
                            'value' => '$20.00',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                // Add more shipping methods as needed
            ];

            // Seed shipping methods with attributes
            foreach ($shippingMethods as $method) {
                $shippingMethod = ShippingMethod::create([
                    'name' => $method['name'],
                    'code' => $method['code'],
                    'status' => $method['status'],
                ]);

                foreach ($method['attributes'] as $attribute) {
                    $attribute['shipping_method_id'] = $shippingMethod->id;
                    ShippingMethodAttribute::create($attribute);
                }
            }
        });

        echo "Seeded actual shipping methods with attributes.\n";
    }
}
