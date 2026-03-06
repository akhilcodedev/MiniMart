<?php

namespace Modules\PaymentMethod\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\PaymentMethod\Models\PaymentMethod;
use Modules\PaymentMethod\Models\PaymentMethodAttribute;

class PaymentmethodseederSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // Define actual payment methods
            $paymentMethods = [
                'credit_card' => [
                    'name' => 'Credit Card',
                    'code' => 'credit_card',
                    'description' => 'Cash or Credit Card',
                    'sort_order' => 1,
                    'test_mode' => 1,
                    'is_online' => 0,
                    'is_active' => 1,
                    'attributes' => [
                        [
                            'name' => 'Merchant ID',
                            //'type' => 'text',
                            'value' => 'merchant_123',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'API Key',
                            //'type' => 'text',
                            'value' => 'api_key_456',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                'paypal' => [
                    'name' => 'PayPal',
                    'code' => 'paypal',
                    'description' => 'Paypal Payment Gateway',
                    'sort_order' => 2,
                    'test_mode' => 1,
                    'is_online' => 1,
                    'is_active' => 1,
                    'attributes' => [
                        [
                            'name' => 'Client ID',
                           // 'type' => 'text',
                            'value' => 'client_789',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Secret',
                            //'type' => 'text',
                            'value' => 'secret_abc',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                'bank_transfer' => [
                    'name' => 'Bank Transfer',
                    'code' => 'bank_transfer',
                    'description' => 'Bank Transfer',
                    'sort_order' => 3,
                    'test_mode' => 1,
                    'is_online' => 0,
                    'is_active' => 1,
                    'attributes' => [
                        [
                            'name' => 'Bank Name',
                           // 'type' => 'text',
                            'value' => 'ABC Bank',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Account Number',
                            //'type' => 'text',
                            'value' => '1234567890',
                            'sort_order' => 2,
                        ],
                    ],
                ],
                'stripe' => [
                    'name' => 'Stripe',
                    'code' => 'stripe',
                    'description' => 'Stripe Payment Gateway',
                    'sort_order' => 4,
                    'test_mode' => 1,
                    'is_online' => 1,
                    'is_active' => 1,
                    'attributes' => [
                        [
                            'name' => 'Publishable Key',
                           // 'type' => 'text',
                            'value' => '',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Secret Key',
                            //'type' => 'text',
                            'value' => '',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Webhook Secret Key',
                            //'type' => 'text',
                            'value' => '',
                            'sort_order' => 3,
                        ],
                    ],
                ],
                'telr' => [
                    'name' => 'Telr',
                    'code' => 'telr',
                    'description' => 'Telr Payment Gateway',
                    'sort_order' => 5,
                    'test_mode' => 1,
                    'is_online' => 1,
                    'is_active' => 1,
                    'attributes' => [
                        [
                            'name' => 'Transaction Type',
                           // 'type' => 'text',
                            'value' => '',
                            'sort_order' => 1,
                        ],
                        [
                            'name' => 'Store Id',
                            //'type' => 'text',
                            'value' => '',
                            'sort_order' => 2,
                        ],
                        [
                            'name' => 'Auth Key',
                            //'type' => 'text',
                            'value' => '',
                            'sort_order' => 3,
                        ],
                    ],
                ],
                // Add more payment methods as needed
            ];

            if (count($paymentMethods) > 0) {
                /*Schema::disableForeignKeyConstraints();
                PaymentMethod::query()->truncate();*/
                foreach ($paymentMethods as $paymentMethodEl) {
                    $insertedPaymentMethod = PaymentMethod::updateOrCreate([
                        'code' => $paymentMethodEl['code'],
                    ], [
                        'name' => $paymentMethodEl['name'],
                        'description' => $paymentMethodEl['description'],
                        'sort_order' => $paymentMethodEl['sort_order'],
                        'test_mode' => $paymentMethodEl['test_mode'],
                        'is_online' => $paymentMethodEl['is_online'],
                        'is_active' => $paymentMethodEl['is_active'],
                    ]);
                    if (isset($paymentMethodEl['attributes']) && is_array($paymentMethodEl['attributes']) && (count($paymentMethodEl['attributes']) > 0)) {
                        foreach ($paymentMethodEl['attributes'] as $attribute) {
                            PaymentMethodAttribute::updateOrCreate([
                                'payment_method_id' => $insertedPaymentMethod->id,
                                'name' => $attribute['name'],
                            ], [
                                'value' => $attribute['value'],
                                'sort_order' => $attribute['sort_order'],
                            ]);
                        }
                    }
                }
                /*Schema::enableForeignKeyConstraints();*/
                $this->command->info('Seeded the Default Payment Methods with attributes!');
            }

        });

    }
}
