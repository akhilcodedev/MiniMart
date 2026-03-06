<?php

namespace Modules\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerGroups = [
            [
                'name' => 'General',
                'description' => 'Default group for general customers.',
                'discount_rate' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ];

        DB::table('customer_groups')->insert($customerGroups);
    }
}
