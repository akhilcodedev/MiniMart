<?php

namespace Modules\OrderManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\OrderManagement\Models\PaymentStatusOption;

class PaymentStatusOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'pending' => "Pending",
            'canceled' => "Canceled",
            'payment_review' => "Payment Review",
            'paid' => "Paid",
            'hold' => "Hold",
            'refunded' => "Refunded"
        ];

        foreach ($statuses as $key => $label) {
            PaymentStatusOption::updateOrCreate(
                ['status' => $key],
                [
                    'label' => $label
                ]
            );
        }
    }
}
