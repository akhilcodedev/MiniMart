<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Products\Models\ProductImageType;

class ImageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define image types
        $imageTypes = [
            [
                'name' => 'primary',
                'description' => 'Main image used for the product display.',
            ],
            [
                'name' => 'thumbnail',
                'description' => 'Small-sized image used in listings or previews.',
            ],
            [
                'name' => 'gallery',
                'description' => 'Additional images for the product gallery.',
            ],
        ];

        // Insert or update the image types
        foreach ($imageTypes as $type) {
            DB::table('product_image_types')->updateOrInsert(
                ['name' => $type['name']], // Unique constraint
                ['description' => $type['description'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
