<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\PriceRuleManagement\Traits\DiscountApply;
use Modules\Products\Models\Product;
use Modules\Category\Models\Category;
use Modules\Products\Models\ProductVariantAttribute;

class ProductController extends Controller
{
    use DiscountApply;

    /**
     * Get all products with metadata and optional sorting by price.
     * /api/products?sort=asc (low to high)
     * /api/products?sort=desc (high to low)
     */
    public function getAllProducts(Request $request)
    {
        try {
            $sortOrder = $request->query('sort', 'asc');

            if (!in_array($sortOrder, ['asc', 'desc'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid sort order. Use "asc" or "desc".',
                ], 400);
            }
            $products = Product::with(['metaDetails', 'productImages'])
                ->selectRaw("
                products.*,
                CASE 
                    WHEN products.special_price IS NOT NULL 
                        AND products.special_price_from <= NOW() 
                        AND products.special_price_to >= NOW() 
                    THEN products.special_price
                    ELSE products.price 
                END AS final_price
            ")
                ->when($request->query('sort_by') === 'price', function ($query) use ($sortOrder) {
                    $query->orderBy('final_price', $sortOrder);
                }, function ($query) {
                    $query->orderBy('id', 'desc');
                })->where('is_variant', '!=', 1)
                ->paginate(12);

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products available.',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Products fetched successfully.',
                'products' => $this->transformProducts($products),
                'paging' => [
                    'first_page_url' => $products->url(1),
                    'last_page' => $products->lastPage(),
                    'last_page_url' => $products->url($products->lastPage()),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get product by ID.
     */
    public function getProductById($id)
    {
        try {
            $product = Product::with(['metaDetails', 'productImages'])->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product fetched successfully.',
                'product' => $this->transformProduct($product),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get product by slug.
     */

    public function getProductBySlug($slug)
    {
        try {
            $product = Product::with([
                'metaDetails',
                'productImages',
                'category',
                'productAttributes.attributeData',
                'productAttributes.attributeOption',
                'variantProducts.productImages',
                'variantProducts.productAttributes.attributeData',
                'variantProducts.productAttributes.attributeOption'
            ])
                ->where('url_key', $slug)
                ->where('is_variant', '!=', 1)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            $variants = [];

            if ($product->is_variant == 0) {
                $variantIds = json_decode($product->variant_products, true) ?? [];

                $variants = Product::whereIn('id', $variantIds)
                    ->with(['productAttributes.attributeData', 'productAttributes.attributeOption'])
                    ->get()
                    ->map(function ($variant) {
                        $attributes = ProductVariantAttribute::where('variant_id', $variant->id)->first();

                        return [
                            'id' => $variant->id,
                            'sku' => $variant->sku,
                            'url_key' => $variant->url_key,
                            'attributes' => $attributes ? json_decode($attributes->variants, true) : [],
                        ];
                    })->values();

            } else {
                $parentProduct = Product::whereRaw("FIND_IN_SET(?, variant_products)", [$product->id])
                    ->with('productImages')
                    ->first();

                if ($parentProduct) {
                    $product->parent_product = $this->transformProduct($parentProduct);

                    $variantIds = json_decode($parentProduct->variant_products, true) ?? [];
                    $variants = Product::whereIn('id', $variantIds)
                        ->where('id', '!=', $product->id)
                        ->with(['productAttributes.attributeData', 'productAttributes.attributeOption'])
                        ->get()
                        ->map(function ($variant) {
                            $attributes = ProductVariantAttribute::where('variant_id', $variant->id)->first();
                            return [
                                'id' => $variant->id,
                                'sku' => $variant->sku,
                                'url_key' => $variant->url_key,
                                'attributes' => $attributes ? json_decode($attributes->variants, true) : [],
                            ];
                        })->values();
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Product fetched successfully.',
                'product' => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'is_in_stock' => $product->is_in_stock,
                    'url_key' => $product->url_key,
                    'original_price' => $product->original_price,
                    'price' => $product->price,
                    'special_price' => $product->special_price,
                    'special_price_from' => $product->special_price_from,
                    'special_price_to' => $product->special_price_to,
                    'out_of_stock_threshold' => $product->out_of_stock_threshold,
                    'min_qty_allowed_in_shopping_cart' => $product->min_qty_allowed_in_shopping_cart,
                    'max_qty_allowed_in_shopping_cart' => $product->max_qty_allowed_in_shopping_cart,
                    'quantity' => $product->quantity,
                    'status' => $product->status,
                    'final_price' => getFinalPrice($product),
                    'meta_data' => $product->metaDetails ?? [],
                    'images' => $product->productImages->map(fn($img) => ['image_url' => $img->image_url]),
                    'attributes' => $product->productAttributes
                        ->filter(fn($attr) => $attr->attributeData)
                        ->map(fn($attribute) => [
                            'code' => optional($attribute->attributeData)->code,
                            'label' => optional($attribute->attributeData)->label,
                            'value' => optional($attribute->attributeOption)->english_value ?? null,
                        ])->values(),
                ],
                'variants' => $variants,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    /**
     * Format the main product response.
     */
    private function formatProduct($product)
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'is_in_stock' => $product->is_in_stock,
            'url_key' => $product->url_key,
            'original_price' => $product->original_price,
            'price' => $product->price,
            'special_price' => $product->special_price,
            'special_price_from' => $product->special_price_from,
            'special_price_to' => $product->special_price_to,
            'out_of_stock_threshold' => $product->out_of_stock_threshold,
            'min_qty_allowed_in_shopping_cart' => $product->min_qty_allowed_in_shopping_cart,
            'max_qty_allowed_in_shopping_cart' => $product->max_qty_allowed_in_shopping_cart,
            'quantity' => $product->quantity,
            'status' => $product->status,
            'final_price' => $product->special_price ?? $product->price,
            'meta_data' => $product->metaDetails ?? [],
            'images' => $product->productImages->map(fn($img) => ['image_url' => $img->image_url]),
            'category' => $product->category->map(fn($cat) => [
                'name' => $cat->name,
                'description' => $cat->description,
                'category_image' => $cat->category_image,
                'banner' => $cat->banner,
            ]),
            'attributes' => $product->productAttributes->map(function ($attribute) {
                return [
                    'code' => $attribute->attributeData->code,
                    'label' => $attribute->attributeData->label,
                    'value' => optional($attribute->options)->english_value,
                ];
            })->filter()->values()
        ];
    }

    /**
     * get product variant by $parentSlug, $variantSlug
     * @param $parentSlug
     * @param $variantSlug
     * @return mixed
     */
    public function getProductBySlugWithVariantSlug($parentSlug, $variantSlug)
    {
        try {
            $parentProduct = Product::with(['productImages', 'metaDetails'])
                ->where('url_key', $parentSlug)
                ->first();

            if (!$parentProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent product not found.',
                ], 404);
            }

            $allVariants = Product::where('parent_id', $parentProduct->id)
                ->with(['productVariantAttributes'])
                ->get();

            $variants = $allVariants->map(function ($variant) {
                $variantAttributes = [];

                foreach ($variant->productVariantAttributes as $attribute) {
                    $attributes = json_decode($attribute->variants, true);
                    if (!empty($attributes)) {
                        foreach ($attributes as $attributeCode => $attributeValue) {
                            $variantAttributes[$attributeCode] = $attributeValue;
                        }
                    }
                }

                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'url_key' => $variant->url_key,
                    'attributes' => $variantAttributes,
                ];
            });

            $selectedVariant = $allVariants->where('url_key', $variantSlug)->first();

            if (!$selectedVariant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variant not found.',
                ], 404);
            }

            $response = [
                'success' => true,
                'message' => 'Product fetched successfully.',
                'product' => [
                    'id' => $selectedVariant->id,
                    'sku' => $selectedVariant->sku,
                    'name' => $selectedVariant->name,
                    'is_in_stock' => $selectedVariant->is_in_stock,
                    'url_key' => $selectedVariant->url_key,
                    'original_price' => $selectedVariant->original_price,
                    'price' => $selectedVariant->price,
                    'special_price' => $selectedVariant->special_price,
                    'special_price_from' => $selectedVariant->special_price_from,
                    'special_price_to' => $selectedVariant->special_price_to,
                    'out_of_stock_threshold' => $selectedVariant->out_of_stock_threshold,
                    'min_qty_allowed_in_shopping_cart' => $selectedVariant->min_qty_allowed_in_shopping_cart,
                    'max_qty_allowed_in_shopping_cart' => $selectedVariant->max_qty_allowed_in_shopping_cart,
                    'quantity' => $selectedVariant->quantity,
                    'status' => $selectedVariant->status,
                    'final_price' => getFinalPrice($selectedVariant),
                    'meta_data' => $selectedVariant->metaDetails ?? [],
                    'images' => $selectedVariant->productImages->map(fn($img) => ['image_url' => $img->image_url]),
                    'attributes' => $selectedVariant->productAttributes
                        ->filter(fn($attr) => $attr->attributeData)
                        ->map(fn($attribute) => [
                            'code' => optional($attribute->attributeData)->code,
                            'label' => optional($attribute->attributeData)->label,
                            'value' => optional($attribute->attributeOption)->english_value ?? null,
                        ])->values(),
                ],
                'variants' => $variants->values(),
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error fetching product variant:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the product variant.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * transform product variant
     * @param $variant
     * @return array
     */
    private function transformProductVariant($variant)
    {

        $updatedPrice = $this->applyPriceRuleToProduct($variant);
        $currentDate = now();
        return [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'url_key' => $variant->url_key,
            'original_price' => $variant->price,
            'price' => (string)$updatedPrice ?? (string)$variant->price,
            'special_price' => $variant->special_price,
            'special_price_from' => $variant->special_price_from,
            'special_price_to' => $variant->special_price_to,
            'out_of_stock_threshold' => $variant->out_of_stock_threshold,
            'min_qty_allowed_in_shopping_cart' => $variant->min_qty_allowed_in_shopping_cart,
            'max_qty_allowed_in_shopping_cart' => $variant->out_of_stock_threshold,
            'quantity' => $variant->quantity,
            'status' => $variant->status,
            'final_price' => getFinalPrice($variant),
            'images' => $variant->productImages->map(function ($image) {
                return [
                    'image_url' => $image->image_url,

                ];
            }),
            'meta_data' => $variant->metaDetails->map(function ($meta) {
                return [
                    'meta_image' => $meta->meta_image,
                    'short_description' => $meta->short_description,
                    'description' => $meta->description,
                    'meta_title' => $meta->meta_title,
                    'meta_keyword' => $meta->meta_keyword,
                    'meta_description' => $meta->meta_description,
                ];
            }),
            'variant_options' => $variant->optionMap->map(function ($map) {
                return $map->option ? [
                    'code' => $map->option->code,
                    'name' => $map->value,
                ] : null;
            })->filter(),

        ];
    }



    /**
     * search product
     * @param Request $request
     * @return mixed
     */
    public function searchProduct(Request $request)
    {
        try {
            $query = Product::select(['id', 'sku', 'name', 'url_key'])->where('is_variant', '!=', 1);

            if ($request->has('sku')) {
                $query->where('sku', 'LIKE', $request->sku . '%');
            }

            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            $products = $query->paginate(12);

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products found.',
                ], 404);
            }

            $products->load([
                'productImages:id,image_url,product_id',
                'productAttributes.attributeData:id,code'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Products fetched successfully.',
                'products' => $products->map(fn($product) => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'url_key' => $product->url_key,
                    'product_images' => $product->productImages->map(fn($image) => [
                        'id' => $image->id,
                        'image_url' => $image->image_url,
                    ]),
                    'product_attributes' => $product->productAttributes->map(fn($attr) => [
                        'id' => $attr->id,
                        'attribute_name' => $attr->attributeData->code ?? null,
                        'attribute_value' => $attr->value,
                    ]),
                ]),
                'pagination' => [
                    'first_page_url' => $products->url(1),
                    'last_page' => $products->lastPage(),
                    'last_page_url' => $products->url($products->lastPage()),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    /**
     * Get products by category IDs.
     * ex: { "ids": [1, 2, 3] }
     */
    public function getProductsByCategoieIds(Request $request)
    {
        try {
            $request->validate([
                'ids' => ['required', 'array'],
            ]);

            $ids = $request->ids;
            $products = Product::with(['metaDetails', 'productImages', 'productAttributes.attributeData'])
                ->whereHas('category', function ($q) use ($ids) {
                    $q->whereIn('category_id', $ids);
                })
                ->paginate(10);

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Products fetched successfully.',
                'products' => $products->transform(fn ($product) => $this->transformProduct($product)),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get products by category Slug.
     * ex: woman,childrens,
     */


    public function getProductsByCategorySlug(Request $request, $slug)
    {
        try {
            $validated = $request->validate([
                'sort_by_price' => 'nullable|in:asc,desc',
            ]);

            $sortByPrice = $validated['sort_by_price'] ?? null;
            $category = Category::where('url_key', $slug)->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.',
                ], 404);
            }

            $products = Product::with(['metaDetails', 'productImages', 'categories'])
                ->whereHas('categories', function ($query) use ($category) {
                    $query->where('categories.id', $category->id);
                })
                ->selectRaw("
                products.*,
                CASE 
                    WHEN products.special_price IS NOT NULL 
                        AND products.special_price_from <= NOW() 
                        AND products.special_price_to >= NOW() 
                    THEN products.special_price
                    ELSE products.price 
                END AS final_price
            ")
                ->when($sortByPrice, function ($query) use ($sortByPrice) {
                    $query->orderBy('final_price', $sortByPrice);
                }, function ($query) {
                    $query->orderBy('id', 'desc');
                })
                ->where('is_variant', '!=', 1)
                ->paginate(12);

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products found for this category.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Products fetched successfully.',
                'products' => $products->map(fn ($product) => $this->transformProduct($product)),
                'paging' => [
                    'first_page_url' => $products->url(1),
                    'last_page' => $products->lastPage(),
                    'last_page_url' => $products->url($products->lastPage()),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transform a single product for the response.
     */
    private function transformProduct($product)
    {
        $updatedPrice = $this->applyPriceRuleToProduct($product);
        $currentDate = now(); // Get current date
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'is_in_stock' => (bool)$product->is_in_stock,
            'url_key' => $product->url_key,
            'original_price' => $product->price,
            'price' => (string)$updatedPrice ?? (string)$product->price,
            'special_price' => $product->special_price,
            'special_price_from' => $product->special_price_from,
            'special_price_to' => $product->special_price_to,
            'out_of_stock_threshold' => $product->out_of_stock_threshold,
            'min_qty_allowed_in_shopping_cart' => $product->min_qty_allowed_in_shopping_cart,
            'max_qty_allowed_in_shopping_cart' => $product->out_of_stock_threshold,
            'quantity' => $product->quantity,
            'status' => $product->status,
            'final_price' => getFinalPrice($product),
            'meta_data' => $product->metaDetails->map(function ($meta) {
                return [
                    'meta_image' => $meta->meta_image,
                    'short_description' => $meta->short_description,
                    'description' => $meta->description,
                    'meta_title' => $meta->meta_title,
                    'meta_keyword' => $meta->meta_keyword,
                    'meta_description' => $meta->meta_description,
                ];
            }),
            'images' => $product->productImages->map(function ($image) {
                return [
                    'image_url' => $image->image_url,

                ];
            }),
            'category' => $product->categories->map(function ($category) {
                return [
                    'name' => $category->name,
                    'description' => $category->description,
                    'category_image' => $category->category_image,
                    'banner' => $category->banner_image,

                ];
            }),
            'attributes' => $product->productAttributes->map(function ($attributeMap) {
                return [
                    'code' => $attributeMap->attributeData->code ?? null,
                    'label' => $attributeMap->attributeData->label ?? null,
                    'value' => $attributeMap->attributeOption->english_value ?? $attributeMap->value, // Fetch label if exists
                ];
            }),

        ];
    }

    /**
     * Transform a collection of products for the response.
     */
    private function transformProducts($products)
    {
        return $products->map(fn ($product) => $this->transformProduct($product));
    }

    /**
     * Handle exceptions and return a standardized response.
     */
    private function handleException(\Exception $e)
    {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage(),
        ], 500);
    }


    /**
     * Get related products for a given product ID.
     *
     * This method retrieves related products based on the `related_products` field,
     * which contains a comma-separated list of related product IDs.
     *
     * @param Request $request The HTTP request instance containing the product_id.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response with the product details and its related products.
     */

    public function getRelatedProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'error' => 'Product not found'
            ], 404);
        }

        $relatedProductIds = explode(',', $product->related_products);
        $relatedProducts = Product::whereIn('id', $relatedProductIds)->get();

        return response()->json([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'related_products' => $relatedProducts->map(function ($relatedProduct) {
                return [
                    'id' => $relatedProduct->id,
                    'name' => $relatedProduct->name,
                    'sku' => $relatedProduct->sku,
                    'url_key'=>$relatedProduct->url_key,
                    'original_price' => $relatedProduct->price,
                    'final_price' => getFinalPrice($relatedProduct), // Assuming getFinalPrice function exists
                    'images' => $relatedProduct->productImages->map(function ($image) {
                        return [
                            'image_url' => $image->image_url,
                        ];
                    }),
                ];
            })
        ]);
    }

}
