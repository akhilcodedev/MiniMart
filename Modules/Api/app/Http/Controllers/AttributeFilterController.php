<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Category\Models\Category;
use Modules\PriceRuleManagement\Models\CatalogPriceRule;
use Modules\ProductAttributes\Models\Attribute;
use Modules\ProductAttributes\Models\ProductAttributeMap;
use Modules\ProductAttributes\Models\ProductAttributeOptions;
use Modules\Products\Models\CategoryProduct;
use Modules\Products\Models\Product;
use Modules\PriceRuleManagement\Traits\DiscountApply;


class AttributeFilterController extends Controller
{
    use DiscountApply;
    /**
     * get all attributes , category url_key is optional params
     * @param Request $request
     * @return json data (price range,all attributes)
     */
    public function getAllFilterAttributes(Request $request)
    {
        $catUrlKey = $request->url_key;
        $productTableName = (new Product())->getTable();
        $productAttributeMapTableName = (new ProductAttributeMap())->getTable();
        $categoryTableName = (new Category())->getTable();
        $categoryProductsTableName = (new CategoryProduct())->getTable();
        $attributeTableName = (new Attribute())->getTable();
        $attributeOptionTableName = (new ProductAttributeOptions())->getTable();

        $query = DB::table("$productAttributeMapTableName as pam")
            ->leftJoin("$productTableName as p", 'p.id', '=', 'pam.product_id')
            ->leftJoin("$categoryProductsTableName as catP", 'catP.product_id', '=', 'p.id')
            ->leftJoin("$categoryTableName as cat", 'cat.id', '=', 'catP.category_id')
            ->leftJoin("$attributeTableName as at", 'at.id', '=', 'pam.attribute_id')
            ->where('p.status', Product::ACTIVE_YES)
            ->where('at.is_active', Attribute::ACTIVE_YES);

        if (!empty($catUrlKey)) {
            $query->where('cat.url_key', $catUrlKey);
        }

        $attributeRequestData = $query
            ->select(
                'p.id as productId',
                'at.id as attributeId',
                'at.code as attributeCode',
                'at.label as attributeLabel',
                'pam.value as mappedValue'
            )
            ->groupBy('p.id', 'at.id', 'at.code', 'at.label', 'pam.value')
            ->orderBy('at.id', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->get();

        if ($attributeRequestData->isEmpty()) {
            return response()->json([
                'attributes' => [],
                'price_range' => [
                    'min' => "0.00",
                    'max' => "0.00",
                ],
                'message' => 'No attributes found for the selected category.'
            ]);
        }

        $attributeOptions = DB::table("$attributeOptionTableName as aot")
            ->whereIn('attribute_id', $attributeRequestData->pluck('attributeId')->unique()->toArray())
            ->select('attribute_id', 'id as option_id', 'english_value', 'color_code')
            ->get();

        $attributeOptionsMap = [];
        foreach ($attributeOptions as $option) {
            $attributeOptionsMap[$option->attribute_id][$option->option_id] = [
                'color' => $option->color_code,
                'value' => $option->english_value
            ];
        }

        $attributeSplitArray = [];
        $prices = [];

        foreach ($attributeRequestData as $attribute) {
            $mappedValue = trim($attribute->mappedValue ?? '');

            if (empty($mappedValue)) {
                continue;
            }

            if (isset($attributeOptionsMap[$attribute->attributeId][$mappedValue])) {
                $option = $attributeOptionsMap[$attribute->attributeId][$mappedValue];
                $displayValue = $option['value'];

                if (strtolower($attribute->attributeCode) === 'color') {
                    $colorKey = !empty($option['color']) ? $option['color'] : 'unknown-color';
                    $attributeSplitArray['color'][$colorKey] = ($attributeSplitArray['color'][$colorKey] ?? 0) + 1;
                } else {
                    $attributeSplitArray[$attribute->attributeCode][$displayValue] =
                        ($attributeSplitArray[$attribute->attributeCode][$displayValue] ?? 0) + 1;
                }
            } else {
                $attributeSplitArray[$attribute->attributeCode][$mappedValue] =
                    ($attributeSplitArray[$attribute->attributeCode][$mappedValue] ?? 0) + 1;
            }

            $product = Product::find($attribute->productId);
            if ($product) {
                $prices[] = getFinalPrice($product);
            }
        }

        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 0;

        return response()->json([
            'attributes' => $attributeSplitArray,
            'price_range' => [
                'min' => number_format($minPrice, 2, '.', ''),
                'max' => number_format($maxPrice, 2, '.', ''),
            ],
        ]);
    }

    /**
     * get products by filtered attributes
     * @param Request $request
     * @return mixed
     */
    public function getProductsByAttributes(Request $request)
    {
        $filters = $request->filters;
        Log::info('Received Filters:', ['filters' => $filters]);
        $query = Product::select([
            'products.id', 'products.sku', 'products.name', 'products.url_key',
            'products.price', 'products.special_price', 'products.special_price_from',
            'products.special_price_to', 'products.is_in_stock', 'products.quantity',
            'products.status'
        ])->where('products.status', Product::ACTIVE_YES)
            ->where('is_variant', '!=', 1);
        if (!empty($filters)) {
            foreach ($filters as $attributeCode => $values) {
                if ($attributeCode === 'price_range') {
                    $minPrice = isset($values['min']) ? (float)$values['min'] : 0;
                    $maxPrice = isset($values['max']) ? (float)$values['max'] : PHP_INT_MAX;
                    Log::info("Applying price range filter: $minPrice - $maxPrice");
                    $query->where(function ($q) use ($minPrice, $maxPrice) {
                        $q->where(function ($subQuery) use ($minPrice, $maxPrice) {
                            $subQuery->whereNotNull('special_price')
                                ->where('special_price_from', '<=', now())
                                ->where('special_price_to', '>=', now())
                                ->whereBetween('special_price', [$minPrice, $maxPrice]);
                        })
                            ->orWhere(function ($subQuery) use ($minPrice, $maxPrice) {
                                $subQuery->where(function ($q1) {
                                    $q1->whereNull('special_price')
                                        ->orWhere('special_price_from', '>', now())
                                        ->orWhere('special_price_to', '<', now());
                                })
                                    ->whereBetween('price', [$minPrice, $maxPrice]);
                            });
                    });
                }
                else {
                    $attributeOptionIds = [];
                    if ($attributeCode === 'color') {
                        $attributeOptionIds = DB::table('product_attribute_options')
                            ->whereIn('color_code', $values)
                            ->pluck('id')
                            ->toArray();
                    } else {
                        $attributeOptionIds = DB::table('product_attribute_options')
                            ->whereIn('english_value', $values)
                            ->pluck('id')
                            ->toArray();
                    }
                    Log::info("Mapped Attribute: $attributeCode => IDs: " . json_encode($attributeOptionIds));
                    if (!empty($attributeOptionIds)) {
                        $query->whereExists(function ($subquery) use ($attributeCode, $attributeOptionIds) {
                            $subquery->select(DB::raw(1))
                                ->from('product_attribute_maps as pam_filter')
                                ->join('product_attributes as at_filter', 'at_filter.id', '=', 'pam_filter.attribute_id')
                                ->whereColumn('pam_filter.product_id', 'products.id')
                                ->where('at_filter.code', $attributeCode)
                                ->whereIn('pam_filter.value', $attributeOptionIds);
                        });
                    }
                }
            }
        }
        $perPage = 12;
        $page = $request->query('page', 1);
        $products = $query->paginate($perPage, ['*'], 'page', $page);
        Log::info("SQL Query: " . $query->toSql(), $query->getBindings());
        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No products found for the given criteria.',
            ], 404);
        }
        $products->load([
            'productImages:id,image_url,product_id',
            'metaDetails',
            'categories',
            'productAttributes.attributeData'
        ]);
        $transformedProducts = $products->map(fn($product) => $this->transformProduct($product));
        return response()->json([
            'success' => true,
            'message' => 'Products fetched successfully.',
            'products' => $transformedProducts,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'first_page_url' => $products->url(1),
                'last_page_url' => $products->url($products->lastPage()),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
        ], 200);
    }

    /**
     * Transform a single product for the response.
     */
    private function transformProduct($product)
    {
        $updatedPrice = $this->applyPriceRuleToProduct($product) ?? $product->price;

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'is_in_stock' => (bool)$product->is_in_stock,
            'url_key' => $product->url_key,
            'original_price' => $product->price,
            'price' => (string)$updatedPrice, // Ensure price is correctly assigned
            'special_price' => $product->special_price,
            'special_price_from' => $product->special_price_from,
            'special_price_to' => $product->special_price_to,
            'out_of_stock_threshold' => $product->out_of_stock_threshold,
            'min_qty_allowed_in_shopping_cart' => $product->min_qty_allowed_in_shopping_cart,
            'max_qty_allowed_in_shopping_cart' => $product->max_qty_allowed_in_shopping_cart,
            'quantity' => $product->quantity,
            'status' => $product->status,
            'final_price' => getFinalPrice($product),
            'images' => $product->productImages->map(fn($image) => [
                'image_url' => $image->image_url,
            ]),

        ];
    }

    /**
     * Apply price rule to product (Assuming function exists)
     */
    private function applyPriceRuleToProduct($product)
    {
        if (!$product) {
            return null;
        }
        return getFinalPrice($product);
    }
}
