<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\Category\Models\Category;
use Illuminate\Support\Facades\Storage;
use Modules\URLRewrite\Models\UrlRewrite;
use Modules\Category\Models\CategoriesMeta;

class CategoryController extends Controller
{


    /**
     * redirect to category page with all categories
     * @return view
     */
    public function index()
    {
        $categories = Category::getCategoryTree();
        return view('category::categories.index', compact('categories'));
    }

    /**
     * function to store single category
     * created category will be returned as json format
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'meta_keywords' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_enabled' => 'required|boolean',
            'included_in_menu' => 'required|boolean',
            'url_key' => 'nullable|string|unique:categories,url_key',
        ]);

        $url_key = $request->url_key ?: Str::slug($request->name, '-');
        if ($request->parent_id) {
            $parentCategory = Category::find($request->parent_id);
            if ($parentCategory) {
                $url_key = Str::slug($parentCategory->name . '-' . $request->name, '-');
            }
        }

        if (Category::where('url_key', $url_key)->exists()) {
            return response()->json([
                'message' => 'The URL key is already taken, please choose a different one.',
            ], 400);
        }

        try {
            DB::transaction(function () use ($request, &$category, $url_key) {
                $data = $request->only([
                    'name',
                    'parent_id',
                    'is_enabled',
                    'included_in_menu',
                ]);

                $data['url_key'] = $url_key;
                if ($request->hasFile('banner_image')) {
                    $data['banner_image'] = $request->file('banner_image')->store('categories/banners', 'public');
                }

                if ($request->hasFile('category_image')) {
                    $data['category_image'] = $request->file('category_image')->store('categories/images', 'public');
                }

                $parentCategory = $request->parent_id ? Category::find($request->parent_id) : null;
                $data['position'] = 0;
                $data['level'] = $parentCategory ? $parentCategory->level + 1 : 0;
                $data['description'] = $request->description ? $request->description : null;

                $category = Category::create($data);
                $categoryMetaData = $request->only([
                    'title',
                    'short_description',
                    'description',
                    'meta_keywords',
                    'meta_title',
                    'meta_description',
                ]);

                $categoryMetaData['category_id'] = $category->id;
                CategoriesMeta::create($categoryMetaData);

                try {
                    UrlRewrite::create([
                        'entity_type' => 'category',
                        'entity_id' => $category->id,
                        'request_path' => $url_key,
                        'target_path' => 'category/' . $url_key,
                    ]);
                } catch (\Exception $e) {
                    Log::error('URL Rewrite creation failed: ' . $e->getMessage());
                    throw $e;
                }
            });

            return response()->json([
                'message' => 'Category added successfully',
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add category: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'meta_keywords' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_enabled' => 'required|boolean',
            'included_in_menu' => 'required|boolean',
        ]);

        try {
            DB::transaction(function () use ($request, $id, &$category) {

                $data = $request->only([
                    'name',
                    'parent_id',
                    'is_enabled',
                    'included_in_menu'
                ]);

                $data['url_key'] = $request->url_key ?: $category->url_key;
                $data['description'] = $request->description ? $request->description : null;

                if ($request->hasFile('banner_image')) {
                    if ($category->banner_image) {
                        Storage::disk('public')->delete($category->banner_image);
                    }
                    $data['banner_image'] = $request->file('banner_image')->store('categories/banners', 'public');
                }

                if ($request->hasFile('category_image')) {
                    if ($category->category_image) {
                        Storage::disk('public')->delete($category->category_image);
                    }
                    $data['category_image'] = $request->file('category_image')->store('categories/images', 'public');
                }

                $category->update($data);
                $categoryMetaData = $request->only([
                    'description',
                    'meta_keywords',
                    'meta_title',
                    'meta_description'
                ]);

                $categoryMetaData['category_id'] = $category->id;
                CategoriesMeta::updateOrCreate(
                    ['category_id' => $category->id],
                    $categoryMetaData
                );

            });

            return response()->json([
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update category: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * function to delete category using category id , id should be passed as parameter ,
     * soft-delete implemented for avoid foreign key error, and delete the image permanently
     * @param $id
     * @return json
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->url_key) {
            Category::where('url_key', $category->url_key)
                ->update(['url_key' => null]);
        }

        if ($category->banner_image) {
            Storage::disk('public')->delete($category->banner_image);
        }

        if ($category->category_image) {
            Storage::disk('public')->delete($category->category_image);
        }

        if ($category->url_key) {
            UrlRewrite::where('request_path', $category->url_key)->delete();
        }

        CategoriesMeta::where('category_id', $category->id)->delete();

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    /**
     * Update the parent and level of a category.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLevel(Request $request)
    {
        $category = Category::findOrFail($request->category_id);
        $category->parent_id = $request->parent_id;
        $category->level = $request->level;

        $category->save();

        return response()->json([
            'message' => 'Category updated successfully'
        ]);
    }


    /**
     * function to get single category , child  categories  will not available for this function
     * pass parameter as category id to fetch the category
     * @param $id
     * @return json
     */
    public function getCategoryById($id)
    {
        try {
            $category = Category::with('meta')->findOrFail($id);

            return response()->json([
                "status" => true,
                "message" => "Category fetched successfully",
                "data" => $category
            ], 200);
        } catch (\Exception $e) {
            Log::error('Bug in getCategoryById: ' . $e->getMessage() . ' at line ' . $e->getLine());

            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
