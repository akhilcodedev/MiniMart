<?php

namespace Modules\Products\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\CMS\Models\Language;
use Modules\Products\Models\Attribute;
use Modules\Products\Models\ProductVariantOption;
use Modules\Products\Models\ProductVariantOptionMap;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Modules\Products\Models\Product;
use Illuminate\Support\Facades\Log;
use Modules\Category\Models\Category;

use Modules\Products\Models\ProductType;
use Modules\Products\Models\ProductImage;
use Modules\Products\Models\ProductsMeta;
use Modules\URLRewrite\Models\UrlRewrite;
use Modules\Products\Models\CategoryProduct;
use Modules\Products\Models\ProductImageType;
use Modules\StoreManagement\Models\StoreProduct;
use Modules\ProductAttributes\Models\AttributeSet;
use Modules\PriceRuleManagement\Traits\DiscountApply;
use Modules\ProductAttributes\Models\AttributeSetMap;
use Modules\ProductAttributes\Models\ProductAttributeMap;
use Modules\PriceRuleManagement\Models\ProductCatalogPrice;
use Modules\ProductAttributes\Models\Attribute as ProductAttribute;


class ProductsController extends Controller
{
    use DiscountApply;

    public function __construct()
    {
        //     $this->middleware('auth'); // Apply authentication if needed


    }

    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data['create_url'] = route('products.create');
        return view('products::products.index', $data);
    }

    /**
     * Retrieve all products with optional search, status filter, and pagination.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing parameters for pagination, filtering, and search.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing product data, total records, and filtered records.
     */
    public function getAllProducts(Request $request)
    {
        try {
            $start = $request->start;
            $limit = $request->length;
            $draw = $request->draw;

            $productsQuery = Product::with('productType')->where('is_variant', 0)->latest('id');

            $totalData = $productsQuery->count();

            if (isset($request['search']['value'])) {
                $searchValue = $request['search']['value'];
                $productsQuery = $productsQuery->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('sku', 'like', '%' . $searchValue . '%')
                        ->orWhere('price', 'like', '%' . $searchValue . '%')
                        ->orWhere('quantity', 'like', '%' . $searchValue . '%');
                });
            }

            if (isset($request['product_status']) && $request['product_status'] !== 'all') {
                $statusValue = $request['product_status'];
                $productsQuery = $productsQuery->where('status', $statusValue);
            }

            $totalFiltered = $productsQuery->count();

            $products = $productsQuery
                ->skip($start)
                ->take($limit)
                ->get();

            $data = [];

            foreach ($products as $product) {
                $btn = '<div class="d-flex gap-2">';
                $btn .= '<a href="' . route('products.edit', $product->id) . '" class="btn btn-sm btn-light btn-active-light-primary me-2"><i class="fas fa-edit"></i>Edit</a>';
                $btn .= '<button class="btn btn-sm btn-light btn-active-light-danger deleteProduct" data-id="' . $product->id . '"><i class="fas fa-trash"></i>Delete</button>';
                $btn .= '</div>';

                $data[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'product_type' => $product->productType ? $product->productType->name : 'N/A',
                    'action' => $btn
                ];
            }

            $json_data = [
                "draw" => intval($draw),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            ];

            return response()->json($json_data);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json([
                'error' => 'An error occurred while fetching the data',
            ]);
        }
    }

    /**
     * Show the form for creating a new product.
     *
     * This method retrieves necessary data to display the product creation form,
     * including product types, attributes, languages, and categories.
     *
     * @return \Illuminate\View\View The view displaying the product creation form with the necessary data.
     */
    public function create()
    {
        $languages = Language::all();

        $data['productTypes'] = ProductType::all();
        $data['cancel_url'] = route('products.index');
        $data['store_url'] = route('products.store');
        $data['attributes'] = Attribute::all();

        $data['relatedProducts'] = Product::where('status', 'active')->paginate(10);


        $data['languages'] = $languages;
        $data['categories'] = Category::getCategoryTree();
        return view('products::products.create', $data);
    }

    /**
     * Store a newly created product in the database.
     *
     * This method validates the input data, processes the product and its details,
     * handles image uploads, and associates the product with categories and stores.
     * It also manages the special price date range and saves the product's metadata.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object containing the product data.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the product index page with a success message if the product is created successfully,
     *                                             or redirects back with an error message in case of failure.
     */

     public function store(Request $request)
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
                     return back()->with('error', 'Invalid date format for special price.  Please use mm/dd/yyyy - mm/dd/yyyy.')->withInput();
                 }
             }
 
             $request->validate([
                 'product_type_id' => 'required|exists:product_types,id',
                 'product_name' => 'required|string|max:255',
                 'url_key' => 'nullable|string|max:255',
                 'price' => 'required|numeric|min:0',
                 'sku' => 'required|string|max:255|unique:products,sku',
                 'quantity' => 'required|integer|min:0',
                 'meta_title' => 'nullable|string|max:255',
                 'meta_description' => 'nullable|string',
                 'meta_keywords' => 'nullable|string',
                 'product_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                 'meta_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                 'language_id' => 'required|array', 
                 'language_id.*' => 'exists:languages,id', 
                 'selected_category' => 'nullable|string', 
             ]);
 
             $urlKey = $request->url_key ? Str::slug($request->url_key, '-') : Str::slug($request->product_name, '-');
 
 
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
                 ]);
 
                 $meta_image = '';
 
                 foreach ($request->language_id as $languageId) {
                     $productDetails = new ProductsMeta();
                     $productDetails->product_id = $product->id;
                     $productDetails->language_id = $languageId;
                     $productDetails->short_description = $request->short_description;
                     $productDetails->description = $request->description;
                     $productDetails->meta_title = $request->meta_title;
                     $productDetails->meta_keyword = $request->meta_keywords;
                     $productDetails->meta_description = $request->meta_description;
                     $productDetails->save();
                 }
 
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
                 return redirect()->route('products.index')->with('success', 'Product created successfully.');
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
     * Show the form for editing the specified product.
     *
     * This method fetches the product details along with its associated product type, images, meta details,
     * and store-specific product details. It also retrieves the categories associated with the product
     * and prepares the necessary data for the edit form view.
     *
     * @param int $id The ID of the product to be edited.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *                               product types, image types, languages, and categories.
     */
    public function edit($id)
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

        return view('products::products.edit', $data);
    }

    public function listAllAttributes(Request $request, $id)
    {

        $data['parent_id'] = $id;
        $data['parentProductSku'] = Product::where('id', $id)->value('sku');

        if ($request->ajax()) {
            $query = ProductAttribute::select('id', 'code', 'label', 'is_active', 'created_by', 'updated_at')
                ->where('is_active', 1)
                ->where('input_type', 'select')
                ->where('is_configurable', 1);

            // Search filter
            if (!empty($request->search)) {
                $query->where(function ($q) use ($request) {
                    $q->where('code', 'like', '%' . $request->search . '%')
                        ->orWhere('label', 'like', '%' . $request->search . '%');
                });
            }

            return DataTables::of($query)
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="attribute-checkbox" value="' . $row->id . '">';
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-success">Yes</span>'
                        : '<span class="badge badge-danger">No</span>';
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('F d, Y, h:i:s A') : 'N/A';
                })
                ->rawColumns(['checkbox', 'is_active'])
                ->make(true);
        }

        return view('products::product-variant.allAttributes', $data);
    }

    public function getAttributeOptions(Request $request)
    {
        $attributeCodes = $request->codes;

        if (!is_array($attributeCodes) || empty($attributeCodes)) {
            return response()->json(['message' => 'Invalid attribute codes'], 400);
        }

        $attributes = ProductAttribute::whereIn('code', $attributeCodes)->with('options')->get();
        if ($attributes->isEmpty()) {
            return response()->json(['message' => 'No attributes found'], 404);
        }

        return response()->json([
            'attributes' => $attributes->map(function ($attribute) {
                return [
                    'code' => $attribute->code,
                    'options' => $attribute->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'english_value' => $option->english_value,
                            'color_code' => $option->color_code ?? null
                        ];
                    })
                ];
            })
        ]);
    }


    /**
     * Get related product
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getRelatedProducts(Request $request)
    {
        $relatedProductIds = [];
        if ($request->has('product_id')) {
            $product = Product::find($request->product_id);
            if ($product && $product->related_products) {
                $relatedProductIds = explode(',', $product->related_products);
            }
        }
        $query = Product::select('id', 'sku', 'name')
            ->where('status', 'active');
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('select', function ($product) use ($relatedProductIds) {
                $checked = in_array($product->id, $relatedProductIds) ? 'checked' : ''; // Auto-check
                return '<input type="checkbox" class="related_checkbox" name="related_products[]" value="' . htmlspecialchars($product->id, ENT_QUOTES, 'UTF-8') . '" ' . $checked . '>';
            })
            ->rawColumns(['select'])
            ->make(true);
    }

    /**
     * save related products separated by coma
     * @param Request $request
     * @return mixed
     */
    public function saveRelatedProducts(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'related_product_ids' => 'nullable|string', // Comma-separated IDs
        ]);
        $product = Product::findOrFail($request->product_id);
        $product->related_products = $request->related_product_ids;
        $product->save();
        return response()->json(['message' => 'Related products updated successfully.']);
    }

    /**
     * get cross selling products
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getCrossSellingProducts(Request $request)
    {

        $crossSellingProductIds = [];
        if ($request->has('product_id')) {
            $product = Product::find($request->product_id);
            if ($product && $product->cross_selling_products) {
                $crossSellingProductIds = explode(',', $product->cross_selling_products);
            }
        }
        $query = Product::select('id', 'sku', 'name')
            ->where('status', 'active');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }
        return DataTables::of($query)
            ->addColumn('select', function ($product) use ($crossSellingProductIds) {
                $checked = in_array($product->id, $crossSellingProductIds) ? 'checked' : ''; // Auto-check
                return '<input type="checkbox" class="cross_selling_checkbox" name="cross_selling_products[]" value="' . htmlspecialchars($product->id, ENT_QUOTES, 'UTF-8') . '" ' . $checked . '>';
            })
            ->rawColumns(['select'])
            ->make(true);
    }

    /**
     * save croos selling products separated by coma
     * @param Request $request
     * @return mixed
     */
    public function saveCrossSellingProducts(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'cross_selling_product_ids' => 'nullable|string', // Comma-separated IDs
        ]);
        $product = Product::findOrFail($request->product_id);
        $product->cross_selling_products = $request->cross_selling_product_ids;
        $product->save();
        return response()->json(['message' => 'Cross Selling  products updated successfully.']);
    }


    public function getProductAttributeContent(Request $request)
    {
        $attributeSetId = $request->has('attribute_set_id') && is_numeric($request->input('attribute_set_id')) && (int)$request->input('attribute_set_id') > 0
            ? (int)$request->input('attribute_set_id')
            : '';

        $productId = $request->has('product_id') && is_numeric($request->input('product_id')) && (int)$request->input('product_id') > 0
            ? (int)$request->input('product_id')
            : '';

        $returnData = ['res' => ''];

        if ($attributeSetId && $productId) {
            $targetProductObj = Product::with([
                'attributeSet.attributeMappings.attributeData.options'
            ])->find($productId);

            $targetAttributeSetObj = AttributeSet::where([
                ['id', '=', $attributeSetId],
                ['type', '=', AttributeSet::ATTRIBUTE_SET_TYPE_PRODUCT],
                ['is_active', '=', AttributeSet::ACTIVE_YES]
            ])->first();

            if ($targetProductObj && $targetAttributeSetObj) {
                $attributesList = $targetAttributeSetObj->mappedAttributes()
                    ->where('product_attributes.is_active', ProductAttribute::ACTIVE_YES)
                    ->orderByPivot('sort_order', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $sortedAttributeList = [];

                foreach ($attributesList as $idEl) {
                    $sortedAttributeList[$idEl->id] = [
                        'attributeId' => $idEl->id,
                        'attributeCode' => $idEl->code,
                        'attributeLabel' => $idEl->label,
                        'attributeDesc' => $idEl->description,
                        'attributeInputType' => $idEl->input_type,
                        'attributeInputTypeLabel' => ProductAttribute::INPUT_TYPE_LIST[$idEl->input_type],
                        'attributeMapDesc' => $idEl->pivot->description,
                        'attributeValue' => $idEl->pivot->value,
                        'attributeIsRequired' => $idEl->pivot->is_required,
                        'attributeSortOrder' => $idEl->pivot->sort_order,
                        'attributeIsActive' => $idEl->pivot->is_active,
                    ];
                }

                // Fetch mapped attributes and options
                $attributeIds = array_column($sortedAttributeList, 'attributeId');

                $linkedProductAttributeListObjs = DB::table('attribute_set_maps as asm')
                    ->join('product_attributes as pa', 'asm.attribute_id', '=', 'pa.id')
                    ->leftJoin('product_attribute_maps as pam', function ($join) use ($productId) {
                        $join->on('pa.id', '=', 'pam.attribute_id')
                            ->where('pam.product_id', '=', $productId); // ✅ Ensures correct product selection
                    })
                    ->leftJoin('product_attribute_options as pao', 'pa.id', '=', 'pao.attribute_id') // ✅ Correct FK reference
                    ->where('asm.attribute_set_id', $attributeSetId) // ✅ Use correct alias for attribute_set_id
                    ->whereIn('asm.attribute_id', $attributeIds) // ✅ Use correct alias for attribute_id
                    ->select(
                        'pa.id as attribute_id',
                        'pa.code',
                        'pa.input_type',
                        'pa.label',
                        'pao.id as option_id',
                        'pao.english_value as option_value',
                        'pam.value as selected_value' // ✅ Fetch selected value from `product_attribute_maps`
                    )
                    ->get();


                $linkedProductAttributeList = [];

                foreach ($linkedProductAttributeListObjs as $idEl) {
                    if (!isset($idEl->attribute_id)) {
                        continue;
                    }

                    // Ensure select-type attributes get their options
                    if ($idEl->input_type === 'select') {
                        if (!isset($linkedProductAttributeList[$idEl->attribute_id])) {
                            $linkedProductAttributeList[$idEl->attribute_id] = [
                                'attributeId' => $idEl->attribute_id,
                                'attributeCode' => $idEl->code,
                                'attributeLabel' => $idEl->label,
                                'attributeInputType' => $idEl->input_type,
                                'attributeInputTypeLabel' => ProductAttribute::INPUT_TYPE_LIST[$idEl->input_type],
                                'attributeValue' => [],
                                'attributeSelectedValue' => $idEl->selected_value, // ✅ Correctly set selected value
                            ];
                        }

                        // Append available options
                        $linkedProductAttributeList[$idEl->attribute_id]['attributeValue'][] = [
                            'id' => $idEl->option_id,
                            'value' => $idEl->option_value
                        ];
                    } else {
                        // For other types (text, date, etc.), directly assign the value
                        $linkedProductAttributeList[$idEl->attribute_id] = [
                            'attributeId' => $idEl->attribute_id,
                            'attributeCode' => $idEl->code,
                            'attributeLabel' => $idEl->label,
                            'attributeInputType' => $idEl->input_type,
                            'attributeInputTypeLabel' => ProductAttribute::INPUT_TYPE_LIST[$idEl->input_type],
                            'attributeValue' => $idEl->option_value ?? null,
                            'attributeSelectedValue' => $idEl->selected_value ?? '', // ✅ Ensure the value is set
                        ];
                    }
                }


                $returnData['res'] = view('products::products.link-attribute', [
                    'attributeSetMapStatuses' => AttributeSetMap::ACTIVE_STATUS_LIST,
                    'attributeSetMapRequires' => AttributeSetMap::REQUIRED_STATUS_LIST,
                    'attributeSetStatuses' => AttributeSet::ACTIVE_STATUS_LIST,
                    'attributeSetTypes' => AttributeSet::ATTRIBUTE_SET_TYPE_LIST,
                    'attributeStatuses' => ProductAttribute::ACTIVE_STATUS_LIST,
                    'attributeTypes' => ProductAttribute::INPUT_TYPE_LIST,
                    'attributeValueTypes' => ProductAttribute::VALUE_NEEDED_INPUT_TYPES,
                    'productData' => $targetProductObj,
                    'attributeSetData' => $targetAttributeSetObj,
                    'activeAttributes' => $sortedAttributeList,
                    'mappedAttributes' => $linkedProductAttributeList,
                ])->render();
            }
        }

        return response()->json($returnData, 200);
    }

    /**
     * Update the specified product in the database.
     *
     * This method updates the product's details, including its price, SKU, quantity, status, and other metadata.
     * It also handles updating associated product images, categories, and product meta details.
     * If files are uploaded, it deletes old images and uploads new ones, while ensuring proper validation of input data.
     *
     * @param \Illuminate\Http\Request $request The incoming request containing the updated product data.
     * @param int $id The ID of the product to be updated.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the product index page with a success message.
     */


    public function update(Request $request, $id)
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

                $parentProductObj = Product::find($product->parent_id);
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

            return redirect()->route('products.index')->with('success', 'Product updated successfully!');
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());

        }


    }


    /**
     * Delete a product and its associated images.
     *
     * This method handles the deletion of a product, including its associated images from both the database
     * and the file system.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the product ID.
     *
     * @return \Illuminate\Http\JsonResponse The response indicating the result of the deletion.
     */
    public function delete(Request $request)
    {
        $productId = $request->input('id');

        if (!$productId || !is_numeric($productId)) {
            return response()->json(['message' => 'Invalid product ID'], 400);
        }

        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        foreach ($product->productImages as $image) {
            if (file_exists(public_path($image->image_url))) {
                unlink(public_path($image->image_url));
            }
        }

        $product->productImages()->delete();

        $product->delete();

        return response()->json(['message' => 'Product and associated images deleted successfully']);
    }


    /**
     * Uploads the product meta image and stores it in the specified directory.
     *
     * This method handles uploading a product meta image, moving it to the correct directory,
     * and returning the image path for storage in the database.
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded image file to be stored.
     *
     * @return string The relative path to the uploaded image.
     */
    private function UploadProductMetaImage($file)
    {
        $folderPath = public_path('images/product_meta_images');

        // If the directory doesn't exist, create it (commented out to avoid redundancy)
        // if (!File::exists($folderPath)) {
        //     File::makeDirectory($folderPath, 0755, true);
        // }

        $image = $file;

        $destinationPath = 'images/product_meta_images';

        $imageName = time() . '_' . $image->getClientOriginalName();

        $image->move(public_path($destinationPath), $imageName);

        $meta_image = $destinationPath . '/' . $imageName;

        return $meta_image;
    }

    /**
     * Uploads the product image and stores it in the specified directory.
     *
     * This method handles uploading a product image, moving it to the correct directory,
     * and returning the image path for storage in the database.
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded image file to be stored.
     *
     * @return string The relative path to the uploaded image.
     */
    private function UploadProductImages($file)
    {
        $folderPath = public_path('images/product_images');

        // If the directory doesn't exist, create it (commented out to avoid redundancy)
        // if (!File::exists($folderPath)) {
        //     File::makeDirectory($folderPath, 0755, true);
        // }

        $image = $file;

        $destinationPath = 'images/product_images';

        $imageName = time() . '_' . $image->getClientOriginalName();

        $image->move(public_path($destinationPath), $imageName);

        $product_image = $destinationPath . '/' . $imageName;

        return $product_image;
    }

    /**
     * Handles the bulk deletion of selected products.
     *
     * This method validates the provided product IDs, performs a bulk delete operation,
     * and updates the associated product images by soft deleting them. If the operation
     * is successful, a success response is returned. In case of failure, an error message
     * is returned with the exception details.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the IDs of the products to be deleted.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:products,id',
            ]);

            Product::whereIn('id', $request->ids)->each(function ($product) {
                $product->productImages()->update(['deleted_at' => now()]);
                $product->delete();
            });

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
}
