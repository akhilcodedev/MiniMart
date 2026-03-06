<?php

namespace Modules\Base\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BaseDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Model::unguard();

        $seedersArray = [];

        $otherSeederList = $this->getOtherSeederClasses();
        if (!is_null($otherSeederList) && is_array($otherSeederList) && (count($otherSeederList) > 0)) {
            foreach ($otherSeederList as $seederClass) {
                if (
                    !is_null($seederClass)
                    && is_string($seederClass)
                    && (trim($seederClass) != '')
                    && class_exists(trim($seederClass))
                    && is_subclass_of(trim($seederClass), Seeder::class)
                ) {
                    $seedersArray[] = $seederClass;
                }
            }
        }

        if (count($seedersArray) > 0) {
            $this->call($seedersArray);
        }

    }

    private function getOtherSeederClasses() {
        return config('customConfigs.seeders');
    }

}
