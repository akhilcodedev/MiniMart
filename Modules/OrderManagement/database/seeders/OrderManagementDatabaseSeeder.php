<?php

namespace Modules\OrderManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\OrderManagement\Database\Seeders\OrderStatusSeeder;
use Modules\OrderManagement\Database\Seeders\OrderTableSeeder;
use Modules\OrderManagement\Database\Seeders\PaymentStatusOptionSeeder;


class OrderManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(OrderStatusSeeder::class);
        $this->call(OrderTableSeeder::class);
        $this->call(PaymentStatusOptionSeeder::class);
    }
}
