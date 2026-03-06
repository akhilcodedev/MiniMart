<?php

namespace Modules\OrderManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\OrderManagement\Models\PaymentStatusOption;

class PaymentStatusOptionNewSeeder extends Seeder
{
   
    public function run()
    {
        PaymentStatusOption::insert([
            ['status' => 'pending', 'label' => 'Pending'],
            ['status' => 'completed', 'label' => 'Completed'],
            ['status' => 'failed', 'label' => 'Failed'],
            ['status' => 'canceled', 'label' => 'Canceled'],
            ['status' => 'refunded', 'label' => 'Refunded']
        ]);
    }
}
