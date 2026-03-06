<?php

namespace Modules\Customer\Database\Seeders;


use Modules\Customer\Models\CustomerAddress;
use Modules\Customer\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        // Create 50 customers
        for ($i = 0; $i < 50; $i++) {
            // Create a customer
            $customer = Customer::create([
                'customer_code' => Str::upper(Str::random(8)),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'password' => Hash::make('password'), // Default password
                'is_active' => $faker->boolean(90), // 90% chance to be active
            ]);

            // Create addresses for the customer
            $numAddresses = $faker->numberBetween(1, 3); // Each customer can have 1-3 addresses
            for ($j = 0; $j < $numAddresses; $j++) {
                CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'address_line1' => $faker->streetAddress,
                    'address_line2' => $faker->optional()->secondaryAddress,
                    'city' => $faker->city,
                    'state' => $faker->state,
                    'postal_code' => $faker->postcode,
                    'country' => $faker->country,
                    'type' => $faker->randomElement(['billing', 'shipping']),
                    'is_default' => $j === 0, // Make the first address default
                ]);
            }
        }
    }
}
