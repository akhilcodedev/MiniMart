<?php

namespace Modules\ShippingMethode\Database\Seeders;

use Illuminate\Database\Seeder;

class ShippingMethodeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(ShippingMethodSeeder::class);
    }
}
