<?php

namespace Modules\PriceRuleManagement\Database\Seeders;

use Illuminate\Database\Seeder;

class PriceRuleManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CouponRelatedTableSeeder::class,
        ]);
    }
}
