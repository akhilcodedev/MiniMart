<?php

namespace Modules\StoreManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CMS\Models\Language;
use Modules\StoreManagement\Models\Store;

class StoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languageId = Language::where('sort_code', 'en')->value('id');
        if($languageId){
            Store::updateOrCreate(
                [
                    'id' => 1,
                ],
                [
                    'name'        => 'English',
                    'code'        => 776567,
                    'status'      => true,
                    'url_key'     => 'en',
                    'website'     => '',
                    'language_id' => $languageId,
                    'is_default' => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }else{
            $language = Language::updateOrCreate([
                'sort_code' => 'en'
            ],[
                'name' => "English",
                'nativeName' => "English"
            ]);

            Store::updateOrCreate(
                [
                    'id' => 1,
                ],
                [
                    'name'        => 'English',
                    'code'        => 776567,
                    'status'      => true,
                    'url_key'     => 'en',
                    'website'     => '',
                    'is_default' => 1,
                    'language_id' => $language->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

    }
}
