<?php
namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use Modules\Products\Models\Product;
use Modules\Products\Models\ProductImage;
use Illuminate\Support\Facades\File;

class ProductTenThousandSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable foreign key checks
        Product::truncate();
        ProductImage::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Enable foreign key checks

        $faker = Faker::create();
        $productTypes = DB::table('product_types')->pluck('id'); // Assuming product_types table exists
        $productImageTypes = DB::table('product_image_types')->pluck('id'); // Assuming product_image_types table exists

        // Ensure the directory exists
        $imagePath = public_path('images/product_images/');
        if (!File::exists($imagePath)) {
            File::makeDirectory($imagePath, 0777, true, true);
        }

        for ($i = 0; $i < 300; $i++) {

            $productName = 'Product ' . $i;
            $urlKey = Str::slug($productName);

            $product = Product::create([
                'sku' => strtoupper(Str::random(10)),
                'name' => $productName,
                'product_type_id' => $productTypes->random(),
                'is_in_stock' => rand(0, 1),
                'url_key' => $urlKey,
                'price' => $faker->randomFloat(2, 10, 500),
                'special_price' => rand(0, 1) ? $faker->randomFloat(2, 5, 450) : 0.00,
                'special_price_from' => now(),
                'special_price_to' => now()->addDays(rand(10, 30)),
                'quantity' => rand(1, 100),
                'status' => 'active',
                'out_of_stock_threshold' => rand(1, 10),
                'min_qty_allowed_in_shopping_cart' => rand(0,1),
                'max_qty_allowed_in_shopping_cart' => rand(10, 100),
                'qty_uses_decimals' => 0,
                'backorders' => 0,
                'attribute_set_id' => null,
                'related_products' => implode(',', array_rand(range(1, 1000), 5)), // Random related products
                'cross_selling_products' => implode(',', array_rand(range(1, 1000), 3)), // Random cross-sell products
            ]);

            for ($j = 0; $j < 5; $j++) { // Ensuring each product has 5 images
                $filePath = $this->generateAndStoreFakeImage(); // Generate and store image

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_type_id' => $productImageTypes->random(),
                    'image_url' => $filePath, // Store the image path
                    'is_default' => $j == 0 ? 1 : 0, // First image is default
                ]);
            }
        }
    }

    /**
     * Generate and store a fake image in public/images/product_images/.
     * Returns the file path that can be saved in the database.
     */
    private function generateAndStoreFakeImage()
    {
        $imagePath = public_path('images/product_images/');

        // Ensure the directory exists
        if (!File::exists($imagePath)) {
            File::makeDirectory($imagePath, 0777, true, true);
        }

        $fileName = 'product_' . Str::random(10) . '.jpg'; // Generate a unique file name
        $fullPath = $imagePath . $fileName; // Full path in public/images/product_images/

        // Create ImageManager instance with the correct driver
        $manager = new ImageManager(new Driver());

        // Create a blank image with a random background color
        $image = $manager->create(400, 400)->fill('#' . substr(md5(rand()), 0, 6));

        // Save the image to the public directory
        $image->save($fullPath, 80);

        return 'images/product_images/' . $fileName; // Return relative path for database
    }
}
