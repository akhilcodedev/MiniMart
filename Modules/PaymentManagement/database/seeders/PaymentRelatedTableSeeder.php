<?php

namespace Modules\PaymentManagement\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\PaymentManagement\Models\PaymentMethod;

class PaymentRelatedTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();

        $availablePaymentMethods = $this->getDefaultPaymentMethods();
        if (count($availablePaymentMethods) > 0) {
            /*Schema::disableForeignKeyConstraints();
            PaymentMethod::query()->truncate();*/
            foreach ($availablePaymentMethods as $paymentMethodEl) {
                $insertedDisplayPosition = PaymentMethod::updateOrCreate([
                    'code' => $paymentMethodEl['code']
                ], [
                    'name' => $paymentMethodEl['name'],
                    'description' => $paymentMethodEl['description'],
                    'sort_order' => $paymentMethodEl['sort_order'],
                    'test_mode' => $paymentMethodEl['test_mode'],
                    'is_online' => $paymentMethodEl['is_online'],
                    'is_active' => $paymentMethodEl['is_active'],
                ]);
            }
            /*Schema::enableForeignKeyConstraints();*/
            $this->command->info('Seeded the Default Payment Methods!');
        }

    }

    /**
     * Returns default fixed Payment Methods which must be present for accessing the system.
     *
     * @return array
     */
    private function getDefaultPaymentMethods() {
        return [
            'stripe' => [
                'code' => 'stripe',
                'name' => 'Stripe',
                'description' => 'Stripe Payment Gateway',
                'sort_order' => 1,
                'test_mode' => 1,
                'is_online' => 1,
                'is_active' => 1
            ],
            'telr' => [
                'code' => 'telr',
                'name' => 'Telr',
                'description' => 'telr Payment Gateway',
                'sort_order' => 2,
                'test_mode' => 1,
                'is_online' => 1,
                'is_active' => 1
            ],

        ];
    }

}
