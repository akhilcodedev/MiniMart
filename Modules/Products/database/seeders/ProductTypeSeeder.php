<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Products\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    public function run()
    {
        // Define product types
        $productTypes = [
            [
                'name' => 'simple',
                'description' => 'A product with no variations, such as size or color.',
            ],
            [
                'name' => 'configurable',
                'description' => 'A product with multiple variations, such as different sizes or colors.',
            ],
            [
                'name' => 'virtual',
                'description' => 'A non-physical product, such as a service or membership.',
            ],
        ];

        // Insert or update product types
        foreach ($productTypes as $type) {
            DB::table('product_types')->updateOrInsert(
                ['name' => $type['name']], // Unique constraint
                ['description' => $type['description']]
            );
        }

    }
}
