<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Category\Models\Category;
use Illuminate\Support\Facades\Log;
use Modules\Products\Models\Product;

class CategoryController extends Controller
{
    /**
     * function to fetch all category with sub-category
     * Method GET
     * @return mixed
     */
    public function getAllCategory()
    {
        try {
            $categories = Category::where('is_enabled', 1)
                ->where('included_in_menu', 1)
                ->with(['childrenRecursive' => function ($query) {
                    $query->where('is_enabled', 1)
                        ->where('included_in_menu', 1)
                        ->with('childrenRecursive');
                }])
                ->with('meta')
                ->whereNull('parent_id')
                ->get();

            $categories = $categories->map(function ($category) {
                return $this->transformCategory($category);
            });

            return response()->json([
                "status" => true,
                "message" => "Categories fetched successfully",
                "data" => $categories
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in getAllCategory: ' . $e->getMessage() . ' at line ' . $e->getLine());

            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform category to include cat_url_key and apply recursively for children.
     */
    private function transformCategory($category)
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'url_key' => $category->url_key,
            'cat_url_key' => $category->url_key,
            'description' => $category->description,
            'parent_id' => $category->parent_id,
            'path' => $category->path,
            'position' => $category->position,
            'level' => $category->level,
            'is_enabled' => $category->is_enabled,
            'included_in_menu' => $category->included_in_menu,
            'banner_image' => $category->banner_image,
            'category_image' => $category->category_image,
            'meta_title' => $category->meta_title,
            'meta_keywords' => $category->meta_keywords,
            'meta_description' => $category->meta_description,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
            'deleted_at' => $category->deleted_at,
            'meta' => $category->meta,
            'children_recursive' => $category->childrenRecursive->map(function ($child) {
                return $this->transformCategory($child);
            }),
        ];
    }

    /**
     * function to get category using id ,
     * if "allow_children" request parameter is true can get category children's
     * @param $id
     * @param Request $request
     * @return json
     */

    public function getCategoryById($id, Request $request)
    {
        try {
            if ($request->allow_children && $request->allow_children == "true") {
                $category = Category::where('is_enabled', 1)
                    ->where('included_in_menu', 1)
                    ->with(['childrenRecursive' => function($query) {
                        $query->where('is_enabled', 1)
                            ->where('included_in_menu', 1)
                            ->with('childrenRecursive');
                    }])
                    ->with('meta')
                    ->where('id', $id)
                    ->first();
            } else {
                $category = Category::where('is_enabled', 1)
                    ->where('included_in_menu', 1)
                    ->with('meta')
                    ->where('id', $id)
                    ->first();
            }

            if (!$category) {
                return response()->json([
                    "status" => false,
                    "message" => "Category not found",
                ], 404);
            }

            return response()->json([
                "status" => true,
                "message" => "Category fetched successfully",
                "data" => $category
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in getCategoryById: ' . $e->getMessage() . ' at line ' . $e->getLine());

            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
            ], 500);
        }
    }

    /**
     * function to get category using name (slug) ,
     * if "allow_children" request parameter is true can get category children's
     * @param $slug
     * @param Request $request
     * @return json
     */

    public function getCategoryBySlug($slug, Request $request)
    {
        try {
            if ($request->allow_children && $request->allow_children == "true") {
                $category = Category::where('is_enabled', 1)
                    ->where('included_in_menu', 1)
                    ->with(['childrenRecursive' => function ($query) {
                        $query->where('is_enabled', 1)
                            ->where('included_in_menu', 1)
                            ->with('childrenRecursive', 'meta');
                    }, 'meta'])
                    ->where('url_key', $slug)
                    ->first();
            } else {
                $category = Category::where('is_enabled', 1)
                    ->where('included_in_menu', 1)
                    ->with('meta')
                    ->with('childrenRecursive')
                    ->where('url_key', $slug)
                    ->first();
            }

            if (!$category) {
                return response()->json([
                    "status" => false,
                    "message" => "Category not found",
                ], 404);
            }

            return response()->json([
                "status" => true,
                "message" => "Category fetched successfully",
                "data" => $category
            ], 200);

        } catch (\Exception $e) {
            Log::error('Bug in getAllCategoryBySlug: ' . $e->getMessage() . ' at line ' . $e->getLine());

            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
            ], 500);
        }
    }

    /**
     * get category image
     * @param $filename
     * @return mixed
     */
    public function getCategoryImage($filename)
    {
        try {
            $imagePath = public_path('storage/categories/banners/' . $filename);

            if (!file_exists($imagePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found.',
                ], 404);
            }

            if (!getimagesize($imagePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image file.',
                ], 400);
            }

            return response()->file($imagePath);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param $urlKey
     * @return mixed
     */
    public function checkUrlKey($urlKey)
    {
        try {
            $category = Category::where('url_key', $urlKey)->first();
            if ($category) {
                return response()->json([
                    "status" => true,
                    "message" => "URL key exists in categories.",
                    "type" => "category",
                    "data" => [
                        "id" => $category->id,
                        "name" => $category->name,
                        "url_key" => $category->url_key,
                        "cat_url_key" => $category->url_key ?? Str::slug($category->name)
                    ]
                ], 200);
            }

            $product = Product::where('url_key', $urlKey)->where('is_variant', '!=', 1)->first();
            if ($product) {
                return response()->json([
                    "status" => true,
                    "message" => "URL key exists in products.",
                    "type" => "product",
                    "data" => [
                        "id" => $product->id,
                        "name" => $product->name,
                        "url_key" => $product->url_key,
                        "product_url_key" => $product->url_key ?? Str::slug($product->name)
                    ]
                ], 200);
            }

            return response()->json([
                "status" => false,
                "message" => "URL key not found in categories or products.",
                "type" => null
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error in checkUrlKey: ' . $e->getMessage() . ' at line ' . $e->getLine());

            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "error" => $e->getMessage()
            ], 500);
        }
    }


}
