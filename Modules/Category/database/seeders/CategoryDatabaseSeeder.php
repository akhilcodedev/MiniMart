<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Category\Models\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryDatabaseSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic gadgets and devices',
                'subcategories' => [
                    [
                        'name' => 'Mobile Phones',
                        'description' => 'Smartphones from various brands',
                        'subcategories' => [
                            ['name' => 'Android Phones', 'description' => 'Phones running Android OS'],
                            ['name' => 'iPhones', 'description' => 'Apple iPhones'],
                        ],
                    ],
                    [
                        'name' => 'Laptops',
                        'description' => 'Latest laptops and ultrabooks',
                        'subcategories' => [
                            ['name' => 'Gaming Laptops', 'description' => 'High-performance laptops for gaming'],
                            ['name' => 'Business Laptops', 'description' => 'Laptops for professional use'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Fashion',
                'description' => 'Clothing and accessories',
                'subcategories' => [
                    [
                        'name' => 'Men',
                        'description' => 'Men’s fashion and accessories',
                        'subcategories' => [
                            ['name' => 'Shirts', 'description' => 'Casual and formal shirts'],
                            ['name' => 'Footwear', 'description' => 'Shoes, sneakers, and sandals'],
                        ],
                    ],
                    [
                        'name' => 'Women',
                        'description' => 'Women’s fashion and accessories',
                        'subcategories' => [
                            ['name' => 'Dresses', 'description' => 'Casual and formal dresses'],
                            ['name' => 'Handbags', 'description' => 'Bags and purses'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Home & Furniture',
                'description' => 'Furniture and home decor',
                'subcategories' => [
                    [
                        'name' => 'Living Room',
                        'description' => 'Furniture for the living room',
                        'subcategories' => [
                            ['name' => 'Sofas', 'description' => 'Comfortable and stylish sofas'],
                            ['name' => 'Coffee Tables', 'description' => 'Modern and classic coffee tables'],
                        ],
                    ],
                    [
                        'name' => 'Bedroom',
                        'description' => 'Bedroom furniture and decor',
                        'subcategories' => [
                            ['name' => 'Beds', 'description' => 'King, Queen, and Single Beds'],
                            ['name' => 'Wardrobes', 'description' => 'Storage for clothes and essentials'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'subcategories' => [
                    [
                        'name' => 'Fitness Equipment',
                        'description' => 'Gym and workout essentials',
                        'subcategories' => [
                            ['name' => 'Treadmills', 'description' => 'Running and walking machines'],
                            ['name' => 'Dumbbells', 'description' => 'Weights for strength training'],
                        ],
                    ],
                    [
                        'name' => 'Outdoor Gear',
                        'description' => 'Equipment for outdoor adventures',
                        'subcategories' => [
                            ['name' => 'Camping Tents', 'description' => 'Tents for outdoor camping'],
                            ['name' => 'Backpacks', 'description' => 'Travel and hiking backpacks'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Automotive',
                'description' => 'Vehicles, accessories, and parts',
                'subcategories' => [
                    [
                        'name' => 'Car Accessories',
                        'description' => 'Accessories for cars',
                        'subcategories' => [
                            ['name' => 'Seat Covers', 'description' => 'Comfortable seat covers'],
                            ['name' => 'GPS Systems', 'description' => 'Navigation and tracking systems'],
                        ],
                    ],
                    [
                        'name' => 'Bike Accessories',
                        'description' => 'Accessories for motorcycles and bikes',
                        'subcategories' => [
                            ['name' => 'Helmets', 'description' => 'Protective headgear'],
                            ['name' => 'Riding Gloves', 'description' => 'Gloves for better grip and protection'],
                        ],
                    ],
                ],
            ],
        ];

        $imagePath = storage_path('app/public/categories/banners/');
        $existingImages = File::exists($imagePath) ? File::files($imagePath) : [];

        if (empty($existingImages)) {
            Log::warning('No images found in storage/app/public/categories/banners/. Seeder will run without images.');
        }

        foreach ($categories as $mainCategory) {
            $categoryName = $mainCategory['name'];
            $description = $mainCategory['description'];
            $subcategories = $mainCategory['subcategories'];

            try {
                if (Category::where('name', $categoryName)->exists()) {
                    Log::info("Category already exists: " . $categoryName);
                    continue;
                }

                $bannerImage = !empty($existingImages) ? basename($existingImages[array_rand($existingImages)]) : null;
                $categoryImage = !empty($existingImages) ? basename($existingImages[array_rand($existingImages)]) : null;

                $urlKey = Str::slug($categoryName);
                if (Category::where('url_key', $urlKey)->exists()) {
                    $urlKey = Str::slug($categoryName) . '-' . uniqid();
                }

                $category = Category::create([
                    'name' => $categoryName,
                    'url_key' => $urlKey,
                    'description' => $description,
                    'parent_id' => null,
                    'path' => Str::slug($categoryName),
                    'position' => 1,
                    'level' => 0,
                    'is_enabled' => 1,
                    'included_in_menu' => 1,
                    'banner_image' => $bannerImage ? 'categories/banners/' . $bannerImage : null,
                    'category_image' => $categoryImage ? 'categories/banners/' . $categoryImage : null,
                    'meta_title' => $categoryName . ' - Best Deals',
                    'meta_keywords' => strtolower(str_replace(' ', ',', $categoryName)),
                    'meta_description' => 'Explore the best ' . $categoryName . ' lighting options',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Category created: " . $category->name);

                foreach ($subcategories as $subcat) {
                    $subcatName = $subcat['name'];
                    $subcatDesc = $subcat['description'];

                    if (Category::where('name', $subcatName)->where('parent_id', $category->id)->exists()) {
                        Log::info("Subcategory already exists: " . $subcatName);
                        continue;
                    }

                    $subcatBannerImage = !empty($existingImages) ? basename($existingImages[array_rand($existingImages)]) : null;
                    $subcatCategoryImage = !empty($existingImages) ? basename($existingImages[array_rand($existingImages)]) : null;

                    $subcatUrlKey = Str::slug($subcatName);
                    if (Category::where('url_key', $subcatUrlKey)->exists()) {
                        $subcatUrlKey = Str::slug($categoryName) . '-' . Str::slug($subcatName) . '-' . uniqid();
                    }

                    Category::create([
                        'name' => $subcatName,
                        'url_key' => $subcatUrlKey,
                        'description' => $subcatDesc,
                        'parent_id' => $category->id,
                        'path' => $category->path . '/' . Str::slug($subcatName),
                        'position' => 1,
                        'level' => 1,
                        'is_enabled' => 1,
                        'included_in_menu' => 1,
                        'banner_image' => $subcatBannerImage ? 'categories/banners/' . $subcatBannerImage : null,
                        'category_image' => $subcatCategoryImage ? 'categories/banners/' . $subcatCategoryImage : null,
                        'meta_title' => $subcatName . ' - Best Deals',
                        'meta_keywords' => strtolower(str_replace(' ', ',', $subcatName)),
                        'meta_description' => 'Find the best ' . $subcatName . ' lighting options',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Error inserting category: " . $categoryName . " - " . $e->getMessage());
            }

            try {
                DB::statement("INSERT INTO category_products (category_id, product_id, created_at, updated_at)
                SELECT c.id, p.id, NOW(), NOW()
                FROM 
                    (SELECT id FROM categories WHERE level = 1) AS c,
                    (SELECT id FROM products ORDER BY RAND() LIMIT 1000) AS p");
            } catch (\Exception $e) {
                Log::error("Error inserting category-product relations: " . $e->getMessage());
            }
        }
    }
}
