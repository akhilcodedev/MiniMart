<?php

namespace Modules\OrderManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\OrderManagement\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'pending' => "Pending",
            'processing' => "Processing",
            'pending_payment' => "Pending Payment",
            'payment_review' => "Payment Review",
            'hold' => "Hold",
            'complete' => "Complete",
            'canceled' => "Canceled",
            'return' => "Returned",
            'fraud' => "Fraud",
            'closed' => "Closed",
        ];

        foreach ($statuses as $key => $label) {
            OrderStatus::updateOrCreate(
                ['status' => $key],
                [
                    'label' => $label
                ]
            );
        }
    }
}
