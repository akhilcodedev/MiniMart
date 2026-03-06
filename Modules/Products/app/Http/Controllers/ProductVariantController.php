<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use Modules\CMS\Models\Language;
use Modules\PriceRuleManagement\Models\ProductCatalogPrice;
use Modules\PriceRuleManagement\Traits\DiscountApply;
use Modules\ProductAttributes\Models\Attribute as ProductAttribute;
use Modules\ProductAttributes\Models\AttributeSet;
use Modules\ProductAttributes\Models\AttributeSetMap;
use Modules\ProductAttributes\Models\ProductAttributeMap;
use Modules\Products\Models\Attribute;
use Modules\Products\Models\CategoryProduct;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductImage;
use Modules\Products\Models\ProductImageType;
use Modules\Products\Models\ProductsMeta;
use Modules\Products\Models\ProductType;
use Modules\Products\Models\ProductVariantAttribute;
use Modules\Products\Models\ProductVariantOption;
use Modules\Products\Models\ProductVariantOptionMap;
use Modules\StoreManagement\Models\StoreProduct;
use Modules\URLRewrite\Models\UrlRewrite;

class ProductVariantController extends Controller
{

    use DiscountApply;

    /**
     * @param $id
     * @return mixed
     *
     */
    public function viewAllVariants($id)
    {
        try {
            $products = Product::with('productType')->where('parent_id', $id)->get();
            return view('products::product-variant.index')->with([
                'products' => $products,
                'parent_product_id' => $id
            ]);
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function createVariantProduct($id)
    {
        try {
            if ($id) {
                $languages = Language::all();
                $data['productTypes'] = ProductType::all();
                $data['cancel_url'] = route('product.variants.all', $id);
                $data['store_url'] = route('store.variant.product');
                $data['attributes'] = Attribute::all();
                $availableOptions = ProductVariantOption::where('is_active', 1)->get();

                $data['relatedProducts'] = Product::where('status', 'active')->paginate(10);
                $data['languages'] = $languages;
                $data['categories'] = Category::getCategoryTree();
                $data['parent_product_id'] = $id;
                $data['parent_variant_options'] = ProductVariantOption::where('is_active', 1)->get();
                $data['availableOptions'] = $availableOptions;
                return view('products::product-variant.create', $data);
            } else {
                throw new \Exception('Parent product id required');
            }
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * sore dynamic variant
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeVariants(Request $request)
    {
        try {
            // ✅ Validate request
            $validatedData = $request->validate([
                'variant_name.*' => 'required|string',
                'sku.*' => 'required|string|distinct|unique:products,sku',
                'price.*' => 'required|numeric|min:0',
                'qty.*' => 'required|integer|min:0',
                'parentId' => 'required|integer|exists:products,id',
                'attributes' => 'required|array',
            ]);

            DB::beginTransaction();

            $parentProduct = Product::findOrFail($request->parentId);
            $existingVariantIds = !empty($parentProduct->variant_products)
                ? json_decode($parentProduct->variant_products, true) ?? []
                : [];

            $newVariantIds = [];

            $attributes = $request->input('attributes', []);
            $attributeKeys = array_keys($attributes);
            $attributeCombinations = $this->generateCombinations($attributes);

            foreach ($attributeCombinations as $index => $combination) {
                $variantName = $request->variant_name[$index] ?? null;
                $sku = $request->sku[$index] ?? null;
                $price = $request->price[$index] ?? null;
                $qty = $request->qty[$index] ?? null;

                if (!$variantName || !$sku || !$price || !$qty) {
                    continue;
                }

                $variant = Product::create([
                    'name' => $variantName,
                    'product_type_id' => 1,
                    'sku' => $sku,
                    'url_key' => str_replace(' ', '-', strtolower($variantName)),
                    'price' => $price,
                    'special_price' => 0.00,
                    'quantity' => $qty,
                    'parent_id' => $request->parentId,
                    'is_variant' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                ProductVariantAttribute::create([
                    'parent_id' => $request->parentId,
                    'variant_id' => $variant->id,
                    'variants' => json_encode($combination, JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $newVariantIds[] = $variant->id;
            }

            $updatedVariantIds = array_merge($existingVariantIds, $newVariantIds);
            $parentProduct->update([
                'variant_products' => json_encode($updatedVariantIds),
                'product_type_id' => 2,
            ]);
            DB::commit();
            return back()->with('success', 'Product Variants created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save variants:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return back()->with('error', 'Failed to save variants: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Generate all possible attribute combinations.
     */
    private function generateCombinations(array $attributes)
    {
        $keys = array_keys($attributes);
        $values = array_map(function ($key) use ($attributes) {
            return $attributes[$key];
        }, $keys);

        $combinations = [[]];

        foreach ($values as $index => $attributeValues) {
            $newCombinations = [];

            foreach ($combinations as $combination) {
                foreach ($attributeValues as $value) {
                    $newCombination = $combination;
                    $newCombination[$keys[$index]] = $value;
                    $newCombinations[] = $newCombination;
                }
            }

            $combinations = $newCombinations;
        }

        return $combinations;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeVariantProduct(Request $request)
    {
        try {
            $input = $request->special_price_date;
            $formattedFromDate = null;
            $formattedToDate = null;

            if (strpos($input, ' - ') !== false) {
                [$from, $to] = explode(' - ', $input);
                $format = 'm/d/Y';
                try {
                    $fromDate = Carbon::createFromFormat($format, trim($from));
                    $toDate = Carbon::createFromFormat($format, trim($to));

                    if ($fromDate && $toDate) {
                        $formattedFromDate = $fromDate->format('Y-m-d');
                        $formattedToDate = $toDate->format('Y-m-d');
                    } else {
                        throw new \Exception('Invalid date format.');
                    }
                } catch (\Exception $e) {
                    return back()->with('error', 'Invalid date format for special price.  Please use mm/dd/yyyy - mm/dd/yyyy.');
                }
            }

            $request->validate([
                'product_type_id' => 'required|exists:product_types,id',
                'product_name' => 'required|string|max:255',
                'url_key' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'sku' => 'required|string|max:255|unique:products,sku',
                'quantity' => 'required|integer|min:0',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'meta_keywords' => 'nullable|string',
                'product_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'meta_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'language_id' => 'required|exists:languages,id',
                'parent_id' => 'required|integer|exists:products,id'
            ]);

            $urlKey = Str::slug($request->url_key, '-');


            DB::beginTransaction();

            try {
                $product = Product::create([
                    'product_type_id' => $request->product_type_id,
                    'name' => $request->product_name,
                    'url_key' => $urlKey,
                    'sku' => $request->sku,
                    'price' => $request->price,
                    'special_price' => $request->special_price ?? null,
                    'special_price_from' => $formattedFromDate,
                    'special_price_to' => $formattedToDate,
                    'quantity' => $request->quantity,
                    'status' => $request->product_status,
                    'is_in_stock' => $request->stock_status,
                    'out_of_stock_threshold' => $request->out_of_stock_threshold,
                    'min_qty_allowed_in_shopping_cart' => $request->min_qty_allowed_in_shopping_cart,
                    'max_qty_allowed_in_shopping_cart' => $request->max_qty_allowed_in_shopping_cart,
                    'qty_uses_decimals' => $request->qty_uses_decimals,
                    'backorders' => $request->backorders,
                    'parent_id' => $request->parent_id,
                    'is_variant' => 1,
                ]);
                $parentProductObj = Product::find($request->parent_id);
                if ($parentProductObj) {
                    $parentVariantList = isset($parentProductObj->variant_products) ?
                        explode(',', $parentProductObj->variant_products) : [];
                    if (!in_array($product->id, $parentVariantList)) {
                        $parentVariantList[] = $product->id;

                    }
                    if (count($parentVariantList) > 0) {
                        $parentProductObj->fill(['variant_products' => implode(',', $parentVariantList)])->save();
                    }

                }
                $meta_image = '';

                $productDetails = new ProductsMeta();
                $productDetails->product_id = $product->id;
                $productDetails->language_id = $request->language_id;
                $productDetails->short_description = $request->short_description;
                $productDetails->description = $request->description;
                $productDetails->meta_title = $request->meta_title;
                $productDetails->meta_keyword = $request->meta_keywords;
                $productDetails->meta_description = $request->meta_description;
                $productDetails->save();

                if ($request->hasFile('product_image')) {
                    foreach ($request->file('product_image') as $image) {
                        $product_image = $this->UploadProductImages($image);
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_type_id' => 1,
                            'image_url' => $product_image,
                            'is_default' => false,
                        ]);
                    }
                }

                if ($request->filled('delete_product_image_ids')) {
                    foreach ($request->delete_product_image_ids as $imageId) {
                        $image = ProductImage::find($imageId);
                        if ($image) {
                            $imagePath = public_path($image->image_url);
                            if (file_exists($imagePath)) {
                                unlink($imagePath);
                            }
                            $image->forceDelete();
                        }
                    }
                }

                if ($product->is_variant) {
                    if ($request->has('product_variant_options')) {
                        foreach ($request->input('product_variant_options') as $optionData) {
                            ProductVariantOptionMap::create([
                                'product_id' => $product->id,
                                'option_id' => $optionData['option_id'],
                                'value' => $optionData['value'],
                                'is_active' => 1,
                            ]);
                        }
                    }
                }

                if ($request->delete_product_meta_image) {
                    $metaImagePath = public_path($request->delete_product_meta_image);
                    if (file_exists($metaImagePath)) {
                        unlink($metaImagePath);
                    }
                }

                $categoryIds = explode(',', $request->selected_category);

                if ($request->hasFile('meta_image')) {
                    $meta_image = $this->UploadProductMetaImage($request->file('meta_image'));
                    $productDetails->meta_image = $meta_image;
                    $productDetails->save();
                }

                if ($categoryIds) {
                    foreach ($categoryIds as $categoryId) {
                        CategoryProduct::create([
                            'product_id' => $product->id,
                            'category_id' => $categoryId,
                        ]);
                    }
                }

                $storeProduct = StoreProduct::create([
                    'product_id' => $product->id,
                    'store_id' => session('store_id') ?? 1,
                    'short_description' => $request->short_description ?? null,
                    'description' => $request->description ?? null,
                    'meta_title' => $request->meta_title,
                    'meta_image' => $meta_image ?? null,
                    'meta_keyword' => $request->meta_keywords,
                    'meta_description' => $request->meta_description
                ]);

                try {
                    UrlRewrite::create([
                        'entity_type' => 'product',
                        'entity_id' => $product->id,
                        'request_path' => $urlKey,
                        'target_path' => 'product/' . $urlKey,
                    ]);
                } catch (\Exception $exception) {
                    Log::error('Product URL Rewrite creation failed: ' . $exception->getMessage());
                    throw $exception;
                }

                if ($product) {
                    $updatedPrice = $this->applyPriceRuleToProduct($product);
                    ProductCatalogPrice::updateOrCreate(
                        ['product_id' => $product->id],
                        [
                            'original_price' => $product->price,
                            'updated_price' => $updatedPrice
                        ]
                    );
                }

                DB::commit();
                return redirect()->route('product.variants.all', $request->parent_id)->with('success', 'Product created successfully.');
            } catch (\Exception $exception) {
                DB::rollback();
                Log::error('Product creation failed: ' . $exception->getMessage());
                return back()->with('error', 'An error occurred while creating the product. Please try again. ' . $exception->getMessage())->withInput();
            }
        } catch (\Exception $exception) {
            Log::error('Product creation initial exception: ' . $exception->getMessage());
            return back()->with('error', 'An error occurred while creating the product. Please try again. ' . $exception->getMessage())->withInput();
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Container\Container|mixed
     */
    public function editVariantProduct($id)
    {
        $storeId = session('store_id') ?? 1;

        $productObj = Product::with([
            'productType',
            'productImages',
            'metaDetails',
            'productAttributes',
            'attributeSetData',
            'optionMap.option',
            'storeProduct' => function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            }
        ])->find($id);

        $availableOptions = ProductVariantOption::where('is_active', 1)->get();
        $selectedOptions = [];

        if ($productObj->optionMap) {
            foreach ($productObj->optionMap as $map) {
                $selectedOptions[] = [
                    'option_id' => $map->option_id,
                    'value' => $map->value,
                ];
            }
        }

        if (!$productObj) {
            return redirect()->route('products.index')->with('error', 'Product data not found.');
        }
        $attributeSets = AttributeSet::select('*')->where('type', AttributeSet::ATTRIBUTE_SET_TYPE_PRODUCT)->where('is_active', AttributeSet::ACTIVE_YES)->get();
        $data['product'] = $productObj;
        $data['productCategories'] = CategoryProduct::where('product_id', $id)->pluck('category_id');
        $data['productTypes'] = ProductType::all();
        $data['productImageType'] = ProductImageType::all();
        $data['languages'] = Language::all();
        $data['cancel_url'] = route('products.index');
        $data['update_url'] = route('products.update', $id);
        $data['categories'] = Category::getCategoryTree();
        $data['attributeSets'] = $attributeSets;
        $data['availableOptions'] = $availableOptions;
        $data['selectedOptions'] = $selectedOptions;
        return view('products::product-variant.edit', $data);
    }

    /**
     * Update the specified variant product in storage.
     *
     * @param \Illuminate\Http\Request $request The incoming request instance containing product data.
     * @param int $id The ID of the variant product to update.
     * @return \Illuminate\Http\RedirectResponse Redirects back or to the variants list route with a status message.
     */
    public function updateVariantProduct(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $input = $request->special_price_date;
            $formattedFromDate = null;
            $formattedToDate = null;
            if (strpos($input, ' - ') !== false) {
                [$from, $to] = explode(' - ', $input);
                $format = 'm/d/Y';
                try {
                    $fromDate = Carbon::createFromFormat($format, trim($from));
                    $toDate = Carbon::createFromFormat($format, trim($to));
                    $formattedFromDate = $fromDate->format('Y-m-d');
                    $formattedToDate = $toDate->format('Y-m-d');
                } catch (\Exception $e) {
                    return back()->with('error', 'Invalid date format. Please use mm/dd/yyyy.');
                }
            }

            $validatedData = $request->validate([
                'product_type_id' => 'required|exists:product_types,id',
                'product_name' => 'required|string|max:255',
                'url_key' => 'required|string|max:255',
                'product_status' => 'required',
                'stock_status' => 'required',
                'short_description' => 'nullable|string',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'sku' => 'required|string|max:255|unique:products,sku,' . $id,
                'quantity' => 'required|integer|min:0',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'meta_keywords' => 'nullable|string',
                'product_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'product_image_type' => 'nullable|array',
                'product_image_type.*' => 'exists:product_image_types,id',
                //'meta_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'delete_product_image_ids' => 'nullable|array',
                'delete_product_image_ids.*' => 'exists:product_images,id',
                'delete_product_meta_image' => 'nullable|string',
                'language_id' => 'required|exists:languages,id',
                'attribute_set_id' => 'nullable|numeric|integer|exists:product_attribute_sets,id',
                'attribute_link' => 'nullable|array',
                'selected_category' => 'required|exists:categories,id',
            ]);

            $urlKey = Str::slug($validatedData['url_key'], '-');

            $loggedUser = Auth::user();

            $attributeSetObj = null;
            $attributeValueArray = [];
            $attributeValueRequiredArray = [];
            $attributeSetIdRaw = (isset($validatedData['attribute_set_id']) && isset($validatedData['attribute_link']) && (count($validatedData['attribute_link']) > 0)) ? $validatedData['attribute_set_id'] : null;

            if (!is_null($attributeSetIdRaw)) {
                $attributeSetObj = AttributeSet::select('*')
                    ->where('id', $attributeSetIdRaw)
                    ->where('type', AttributeSet::ATTRIBUTE_SET_TYPE_PRODUCT)
                    ->where('is_active', AttributeSet::ACTIVE_YES)
                    ->first();

                if ($attributeSetObj) {
                    $attributeLinkArrayRaw = $validatedData['attribute_link'];
                    $givenAttributeIds = array_keys($attributeLinkArrayRaw);

                    $productAttributeTableName = (new ProductAttribute())->getTable();
                    $attributesAvailableList = $attributeSetObj->mappedAttributes()->whereIn($productAttributeTableName . '.id', $givenAttributeIds)->get();

                    if ($attributesAvailableList) {
                        foreach ($attributesAvailableList as $item) {
                            $currentSelectedAttributeValue = null;
                            $currentSelectedAttributeValueTemp = $attributeLinkArrayRaw[$item->id] ?? null;

                            if (!is_null($currentSelectedAttributeValueTemp)) {
                                if ($item->input_type == ProductAttribute::INPUT_TYPE_DATE) {
                                    $currentSelectedAttributeValue = date('Y-m-d', strtotime($currentSelectedAttributeValueTemp));
                                } elseif ($item->input_type == ProductAttribute::INPUT_TYPE_SELECT_MULTI) {
                                    $currentSelectedAttributeValue = (is_array($currentSelectedAttributeValueTemp) && (count($currentSelectedAttributeValueTemp) > 0)) ? implode(',', $currentSelectedAttributeValueTemp) : null;
                                } else {
                                    $currentSelectedAttributeValue = $currentSelectedAttributeValueTemp;
                                }
                            }

                            if (($item->pivot->is_required == AttributeSetMap::REQUIRED_YES) && is_null($currentSelectedAttributeValue)) {
                                $attributeValueRequiredArray[] = $item->id;
                            }

                            $attributeValueArray[] = [
                                'product_id' => $product->id,
                                'attribute_set_id' => $attributeSetObj->id,
                                'attribute_id' => $item->id,
                                'value' => $currentSelectedAttributeValue,
                                'is_active' => ProductAttributeMap::ACTIVE_YES,
                                'created_by' => $loggedUser->id,
                                'updated_by' => $loggedUser->id,
                            ];
                        }
                    }
                }
            }

            if ((count($attributeValueArray) > 0) && (count($attributeValueRequiredArray) > 0)) {
                return back()->with('error', 'Please fill out all the required Attributes.');
            }

            DB::transaction(function () use ($request, $product, $validatedData, $loggedUser, $attributeSetObj, $attributeValueArray, $formattedFromDate, $formattedToDate, $urlKey) {
                $product->update([
                    'product_type_id' => $validatedData['product_type_id'],
                    'name' => $validatedData['product_name'],
                    'url_key' => $urlKey,
                    'price' => $validatedData['price'],
                    'special_price' => $request->special_price ?? null,
                    'special_price_from' => $formattedFromDate ?? null,
                    'special_price_to' => $formattedToDate ?? null,
                    'sku' => $validatedData['sku'],
                    'quantity' => $validatedData['quantity'],
                    'status' => $validatedData['product_status'],
                    'is_in_stock' => $validatedData['stock_status'],
                    'out_of_stock_threshold' => $request->out_of_stock_threshold,
                    'min_qty_allowed_in_shopping_cart' => $request->min_qty_allowed_in_shopping_cart,
                    'max_qty_allowed_in_shopping_cart' => $request->max_qty_allowed_in_shopping_cart,
                    'qty_uses_decimals' => $request->qty_uses_decimals,
                    'backorders' => $request->backorders,
                    'attribute_set_id' => ($attributeSetObj && (count($attributeValueArray) > 0)) ? $attributeSetObj->id : null,
                ]);
                $product->refresh();

//                $parentProductObj = Product::find($product->parent_id);
//                if ($parentProductObj) {
//                    $parentVariantList = isset($parentProductObj->variant_products) ?
//                        explode(',', $parentProductObj->variant_products) : [];
//                    if (!in_array($product->id, $parentVariantList)) {
//                        $parentVariantList[] = $product->id;
//
//                    }
//                    if (count($parentVariantList) > 0) {
//                        $parentProductObj->fill(['variant_products' => implode(',', $parentVariantList)])->save();
//                    }
//
//                }

                $productDetails = $product->metaDetails()->where('product_id', $product->id)->first();

                if ($productDetails) {

                    $productDetails->update([
                        'language_id' => $validatedData['language_id'] ?? null,
                        'short_description' => $validatedData['short_description'],
                        'description' => $validatedData['description'],
                        'meta_title' => $validatedData['meta_title'],
                        'meta_keyword' => $validatedData['meta_keywords'],
                        'meta_description' => $validatedData['meta_description'],
                    ]);
                } else {

                    $product->metaDetails()->create([
                        'language_id' => $validatedData['language_id'],
                        'short_description' => $validatedData['short_description'],
                        'description' => $validatedData['description'],
                        'meta_title' => $validatedData['meta_title'],
                        'meta_keyword' => $validatedData['meta_keywords'],
                        'meta_description' => $validatedData['meta_description'],
                    ]);
                }

                if ($request->hasFile('meta_image')) {

                    $meta_image = $this->UploadProductMetaImage($request->file('meta_image'));
                    $product->metaDetails()->update([
                        'meta_image' => $meta_image
                    ]);

                    $product->save();

                }

                if ($request->hasFile('product_image')) {
                    $oldImages = $product->productImages;
                    $product_image_type = ProductImageType::findOrFail(1);
                    foreach ($request->file('product_image') as $image) {
                        $product_image = $this->UploadProductImages($image);
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_type_id' => $product_image_type->id,
                            'image_url' => $product_image,
                            'is_default' => false,
                        ]);
                    }
                }

                if ($request->has('product_image_type')) {
                    foreach ($request->product_image_type as $imageId => $imageTypeId) {
                        $productImage = ProductImage::find($imageId);
                        if ($productImage) {
                            $productImage->update([
                                'image_type_id' => $imageTypeId,
                            ]);
                        }
                    }
                }

                if ($product->is_variant) {
                    $product->optionMap()->delete();
                    if ($request->has('product_variant_options')) {
                        foreach ($request->input('product_variant_options') as $optionData) {
                            ProductVariantOptionMap::create([
                                'product_id' => $product->id,
                                'option_id' => $optionData['option_id'],
                                'value' => $optionData['value'],
                                'is_active' => 1,
                            ]);
                        }
                    }
                }

                if ($request->filled('delete_product_image_ids')) {
                    foreach ($request->delete_product_image_ids as $imageId) {
                        $image = ProductImage::find($imageId);
                        if ($image) {
                            if (file_exists(public_path($image->image_url))) {
                                unlink(public_path($image->image_url));
                            }
                            $image->forceDelete();
                        }
                    }
                }

                if ($request->delete_product_meta_image) {
                    if (file_exists(public_path($request->delete_product_meta_image))) {
                        unlink(public_path($request->delete_product_meta_image));
                    }
                }

                $categoryIds = explode(',', $request->selected_category);
                if ($categoryIds) {
                    $oldCategories = CategoryProduct::where('product_id', $product->id)->delete();
                    foreach ($categoryIds as $categoryId) {
                        CategoryProduct::create([
                            'product_id' => $product->id,
                            'category_id' => $categoryId,
                        ]);
                    }
                }

                $storeProduct = StoreProduct::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'store_id' => session('store_id') ?? 1
                    ],
                    [
                        'short_description' => $request->short_description ?? null,
                        'description' => $request->description ?? null,
                        'meta_title' => $request->meta_title,
                        'meta_image' => $meta_image ?? null,
                        'meta_keyword' => $request->meta_keywords,
                        'meta_description' => $request->meta_description
                    ]
                );

                if (count($attributeValueArray) > 0) {
                    $productAttributeDeletes = ProductAttributeMap::where('product_id', $product->id)->delete();
                    foreach ($attributeValueArray as $item) {
                        $currentAttributeMapObj = ProductAttributeMap::create($item);
                    }
                }

                try {
                    $urlRewrite = UrlRewrite::where('entity_type', 'product')
                        ->where('entity_id', $product->id)
                        ->first();

                    if ($urlRewrite) {
                        $urlRewrite->update([
                            'request_path' => $urlKey,
                            'target_path' => 'product/' . $urlKey,
                        ]);
                    } else {
                        UrlRewrite::create([
                            'entity_type' => 'product',
                            'entity_id' => $product->id,
                            'request_path' => $urlKey,
                            'target_path' => 'product/' . $urlKey,
                        ]);
                    }
                } catch (\Exception $exception) {
                    Log::error('Product URL Rewrite update failed: ' . $exception->getMessage());
                    throw $exception;
                }

                $updatedPrice = $this->applyPriceRuleToProduct($product);
                ProductCatalogPrice::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'original_price' => $product->price,
                        'updated_price' => $updatedPrice
                    ]
                );
            });

            return redirect()->route('product.variants.all', $product->parent_id)->with('success', 'Variant Product updated successfully!');

        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());

        }
    }

    /**
     * Delete a specific variant product.
     *
     * @param \Illuminate\Http\Request $request Request containing the product ID.
     * @return \Illuminate\Http\JsonResponse Status message.
     */
    public function deleteVariantProduct(Request $request)
    {
        try {
            $productId = $request->input('id');

            if (!$productId || !is_numeric($productId)) {
                return response()->json(['message' => 'Invalid product ID'], 400);
            }

            $product = Product::find($productId);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Delete associated images
            foreach ($product->productImages as $image) {
                if (file_exists(public_path($image->image_url))) {
                    unlink(public_path($image->image_url));
                }
            }
            $product->productImages()->delete();

            // Get the parent product and update variant_products
            if ($product->parent_id) {
                $parentProduct = Product::find($product->parent_id);
                if ($parentProduct) {
                    $updatedVariantIds = collect(explode(',', $parentProduct->variant_products))
                        ->reject(fn ($id) => $id == $productId)
                        ->implode(',');

                    $parentProduct->update(['variant_products' => $updatedVariantIds]);
                }
            }

            // Delete the product
            $product->delete();

            return response()->json(['message' => 'Product and associated images deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete multiple variant products in bulk.
     *
     * @param \Illuminate\Http\Request $request Request containing an array of product IDs ('ids').
     * @return \Illuminate\Http\JsonResponse Status message indicating success or failure.
     */
    public function deleteBulkVariantProducts(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:products,id',
            ]);

            $productIds = $request->ids;
            $products = Product::whereIn('id', $productIds)->get();

            foreach ($products as $product) {
                // Delete associated images
                foreach ($product->productImages as $image) {
                    if (file_exists(public_path($image->image_url))) {
                        unlink(public_path($image->image_url));
                    }
                }
                $product->productImages()->delete();

                // Get the parent product and update variant_products
                if ($product->parent_id) {
                    $parentProduct = Product::find($product->parent_id);
                    if ($parentProduct) {
                        $updatedVariantIds = collect(explode(',', $parentProduct->variant_products))
                            ->reject(fn ($id) => in_array($id, $productIds))
                            ->implode(',');

                        $parentProduct->update(['variant_products' => $updatedVariantIds]);
                    }
                }
                $product->delete();

            }

            return response()->json([
                'success' => true,
                'message' => 'Selected products have been deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Uploads the product image and stores it in the specified directory.
     * This method handles uploading a product image, moving it to the correct directory,
     * and returning the image path for storage in the database.
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded image file to be stored
     * @return string The relative path to the uploaded image.
     */
    private function UploadProductImages($file)
    {
        $image = $file;
        $destinationPath = 'images/product_images';
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path($destinationPath), $imageName);
        $product_image = $destinationPath . '/' . $imageName;
        return $product_image;
    }

    /**
     * Uploads the product meta image and stores it in the specified directory.
     * This method handles uploading a product meta image, moving it to the correct directory,
     * and returning the image path for storage in the database.
     * @param \Illuminate\Http\UploadedFile $file The uploaded image file to be stored.
     * @return string The relative path to the uploaded image.
     */
    private function UploadProductMetaImage($file)
    {
        //dd($file);
        $image = $file;
        $destinationPath = 'images/product_meta_images';
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path($destinationPath), $imageName);
        $meta_image = $destinationPath . '/' . $imageName;
        return $meta_image;
    }
}
