<?php

namespace Modules\Cart\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CartDataSeeder extends Seeder
{

    public function run(): void
    {
        $customer = DB::table('customers')->pluck('id'); // Assuming users table exists
        for ($i = 1; $i <= 50; $i++) {
            DB::table('carts')->insert([
                'customer_id' => $customer->random(),
                'guest_fingerprint_id' => $customer->random(),
                'is_active' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
