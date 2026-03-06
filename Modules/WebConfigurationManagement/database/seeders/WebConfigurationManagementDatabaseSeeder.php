<?php

namespace Modules\WebConfigurationManagement\Database\Seeders;

use Illuminate\Database\Seeder;

class WebConfigurationManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(CountrySeeder::class);
    }
}
