<?php

namespace Modules\PaymentMethod\Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentMethodDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PaymentmethodseederSeeder::class);
    }
}
