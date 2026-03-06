<?php

namespace Modules\OrderManagement\Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Modules\Customer\Models\Customer;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\OrderItem;
use Modules\Cart\Models\Cart; // Import the Cart model

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Get Existing Customer IDs
        $customerIds = Customer::pluck('id')->toArray();
        // 1. Get Existing Cart IDs
        $cartIds = Cart::pluck('id')->toArray();
   


        for ($i = 0; $i < 50; $i++) {
             // 2. Ensure customerIds are not empty
            if(empty($customerIds)){
                $this->command->warn("No customers found. Aborting Order seeder");
                 return;
            }

            // 3. Ensure Cart IDs are not empty
             if(empty($cartIds)){
                $this->command->warn("No carts found. Aborting Order seeder");
                 return;
            }
            $order = Order::create([
                'order_number' => rand(100000, 999999),
                'store_id' => $faker->numberBetween(1, 10),
                 // 3. Use a valid customer ID
                'customer_id' => $faker->randomElement($customerIds),
                 // 4. Use a valid Cart ID
                'cart_id' => $faker->randomElement($cartIds),
                'status' => $faker->numberBetween(1, 5),
                'total' => $faker->randomFloat(2, 10, 1000),
            ]);

            for ($j = 0; $j < $faker->numberBetween(1, 5); $j++) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $faker->numberBetween(1, 50),
                    'sku' => $faker->bothify('SKU-####'),
                    'product_name' => $faker->words(3, true),
                    'product_attributes' => json_encode([
                        'color' => $faker->safeColorName(),
                        'size' => $faker->randomElement(['S', 'M', 'L', 'XL']),
                    ]),
                    'product_price' => $faker->randomFloat(2, 10, 1000),
                    'quantity' => $faker->numberBetween(1, 10),
                    'total' => $faker->randomFloat(2, 10, 1000),
                ]);
            }
        }
    }
}