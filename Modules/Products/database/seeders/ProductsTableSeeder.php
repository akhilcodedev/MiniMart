<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $productTypes = DB::table('product_types')->pluck('id'); // Assuming product_types table exists

        for ($i = 1; $i <= 50   ; $i++) {
            // Insert into products table
            $productId = DB::table('products')->insertGetId([
                'product_type_id' => $productTypes->random(),
                'name' => 'Product ' . $i,
                'price' => rand(100, 1000),
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'quantity' => rand(1, 100),
                'status' => ['active', 'inactive'][rand(0, 1)],
                'is_in_stock' => rand(0, 1),
                'url_key' => 'product-' . strtolower(Str::random(8)),
                'special_price' => rand(80, 900),
                'special_price_from' => now()->subDays(rand(1, 30)),
                'special_price_to' => now()->addDays(rand(1, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert into products_meta table
            DB::table('products_meta')->insert([
                'product_id' => $productId, // Reference the product ID
                'language_id' => rand(1, 5), // Assuming a random language ID
                'description' => 'This is a detailed description for Product ' . $i,
                'short_description' => 'This is a short description for Product ' . $i,
                'meta_title' => 'Meta Title for Product ' . $i,
                'meta_description' => 'Meta Description for Product ' . $i,
                'meta_keyword' => 'meta,product,' . $i,
                'meta_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
