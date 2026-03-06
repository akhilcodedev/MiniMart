<?php

namespace Modules\ProductAttributes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ProductAttributes\Models\Attribute;
use Modules\ProductAttributes\Models\AttributeSet;
use Modules\ProductAttributes\Models\ProductAttributeOptions;
use Modules\Products\Models\Product;
use Modules\ShippingMethode\Models\ShippingMethod;
use Modules\ShippingMethode\Models\ShippingMethodAttribute;

class ProductAttributesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'createUrl' => route('product.attributes.create'),
            'attributeStatuses' => Attribute::ACTIVE_STATUS_LIST,
            'attributeTypes' => Attribute::INPUT_TYPE_LIST,
        ];
        return view('productattributes::attributes.index', $data);
    }

    /**
     * Fetch all attributes with pagination, search, and filters for DataTables.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAttributes(Request $request){

        try {
            $authUser = auth()->user();
            $isSuperAdmin = $authUser && $authUser->is_super_admin == 1;
            $start = $request->start;
            $limit = $request->length;
            $draw = $request->draw;

            $attributesQuery = Attribute::with('createdUser', 'updatedUser')->latest('id');

            $totalData = $attributesQuery->count();

            if (isset($request['search']['value'])) {
                $searchValue = $request['search']['value'];
                $attributesQuery = $attributesQuery->where(function ($query) use ($searchValue) {
                    $query->where('code', 'like', '%' . $searchValue . '%')
                        ->orWhere('label', 'like', '%' . $searchValue . '%')
                        ->orWhere('input_type', 'like', '%' . $searchValue . '%');
                });
            }

            if (isset($request['attribute_status']) && ($request['attribute_status'] !== 'all') && is_numeric($request['attribute_status']) && in_array((int)$request['attribute_status'], array_keys(Attribute::ACTIVE_STATUS_LIST))) {
                $statusValue = (int)$request['attribute_status'];
                $attributesQuery = $attributesQuery->where('is_active', $statusValue);
            }
            $totalFiltered = $attributesQuery->count();
            $attributes = $attributesQuery
                ->skip($start)
                ->take($limit)
                ->get();
            $data = [];

            foreach ($attributes as $attribute) {
                $btn = '<div class="d-flex align-items-center gap-2 justify-content-center">';
                if ($isSuperAdmin || $authUser->can('edit_attribute')) {
                    $btn .= '<a href="' . route('product.attributes.edit', $attribute->id) . '" class="btn btn-sm btn-light btn-active-light-primary me-2 d-flex align-items-center"><i class="fas fa-edit me-1"></i>Edit</a>';
                }
                if ($isSuperAdmin || $authUser->can('delete_attribute')) {
                    $btn .= '<button class="btn btn-sm btn-light btn-active-light-danger d-flex align-items-center deleteProductAttribute" data-id="' . $attribute->id . '"><i class="fas fa-trash me-1"></i>Delete</button>';
                }
                $btn .= '</div>';

                $attributeSets = [];
                if ($attribute->mappedAttributeSets) {
                    foreach ($attribute->mappedAttributeSets as $attributeSet) {
                        $attributeSets[] = $attributeSet->label . ' (Id: ' . $attributeSet->id . ')';
                    }
                }

                $data[] = [
                    'id' => $attribute->id,
                    'code' => $attribute->code,
                    'label' => $attribute->label,
                    'input_type' => Attribute::INPUT_TYPE_LIST[$attribute->input_type],
                    'description' => $attribute->description,
                    'linked_attribute_sets' => implode(', ', $attributeSets),
                    'is_active' => Attribute::ACTIVE_STATUS_LIST[(int)$attribute->is_active],
                    'created_by' => $attribute->createdUser ? $attribute->createdUser->name : '-',
                    'created_at' => date('Y-m-d H:i:s', strtotime($attribute->created_at)),
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'attributeStatuses' => Attribute::ACTIVE_STATUS_LIST,
            'attributeTypes' => Attribute::INPUT_TYPE_LIST,
        ];
        return view('productattributes::attributes.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $attributeStatuses = Attribute::ACTIVE_STATUS_LIST;
        $attributeTypes = Attribute::INPUT_TYPE_LIST;
        $loggedUser = Auth::user();

        $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:product_attributes,code'],
            'label' => ['required', 'string', 'max:255'],
            'input_type' => ['required', 'string', Rule::in(array_keys($attributeTypes))],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'numeric', Rule::in(array_keys($attributeStatuses))],
            'english_value.*' => ['nullable', 'string', 'max:255'],
            'arabic_value.*' => ['nullable', 'string', 'max:255'],
            'color_code.*' => ['nullable', 'string', 'max:10'], // Hex color format
            'image_url.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            $attributeCode = Str::snake(strtolower(trim($request->code)));
            if (Attribute::where('code', $attributeCode)->exists()) {
                return redirect()->back()->withErrors('Attribute Code already exists. Please try again.');
            }

            $attribute = Attribute::create([
                'code' => $attributeCode,
                'label' => $request->label,
                'input_type' => $request->input_type,
                'description' => $request->description ?? null,
                'is_active' => $request->is_active,
                'is_required' => $request->is_required,
                'is_filterable' => $request->is_filterable,
                'is_configurable' => $request->is_configurable,
                'created_by' => $loggedUser->id,
                'updated_by' => $loggedUser->id,
            ]);

            if ($request->input_type === 'select') {
                $englishValues = $request->english_value ?? [];
                $arabicValues = $request->arabic_value ?? [];
                $colorCodes = $request->color_code ?? [];
                $imageFiles = $request->file('image_url', []);

                foreach ($englishValues as $index => $englishValue) {
                    $arabicValue = $arabicValues[$index] ?? null;
                    $colorCode = $colorCodes[$index] ?? null;
                    $imageUrl = null;

                    if (isset($imageFiles[$index])) {
                        $imageUrl = $imageFiles[$index]->store('attribute_images', 'public');
                    }

                    ProductAttributeOptions::create([
                        'attribute_id' => $attribute->id,
                        'english_value' => $englishValue,
                        'arabic_value' => $arabicValue,
                        'color_code' => $colorCode,
                        'image_url' => $imageUrl,
                        'created_by' => $loggedUser->id,
                        'updated_by' => $loggedUser->id,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('product.attributes.index')->with('success', 'Attribute Created Successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating attribute: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while creating the attribute. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!isset($id) || is_null($id) || !is_numeric($id) || ((int)$id <= 0)) {
            return redirect()->route('product.attributes.index')->withErrors('Invalid Attribute Id.');
        }

        $attributeObj = Attribute::find($id);
        if (!$attributeObj) {
            return redirect()->route('product.attributes.index')->withErrors('Attribute Not found.');
        }
        $attribute = Attribute::with('options')->findOrFail($id);
        $attributeTypes =  Attribute::INPUT_TYPE_LIST;

        $attributeStatuses = [
            1 => 'Active',
            0 => 'Inactive',
        ];

        // Transform attribute options for better readability in the view
        $options = $attribute->options->map(function ($option) {
            return [
                'id' => $option->id,
                'english_value' => $option->english_value,
                'arabic_value' => $option->arabic_value,
                'color_code' => $option->color_code ?? '#000000',
                'image_url' => $option->image_url,
            ];
        });

        return view('productattributes::attributes.edit', compact('attribute', 'attributeTypes', 'attributeStatuses', 'options'));
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);
        $loggedUser = auth()->user();

        $attribute->update([
            'code' => $request->input('code'),
            'label' => $request->input('label'),
            'input_type' => $request->input('input_type'),
            'description' => $request->input('description'),
            'is_required' => $request->input('is_required', 0),
            'is_filterable' => $request->input('is_filterable', 0),
            'is_configurable' => $request->input('is_configurable', 0),
            'is_active' => $request->input('is_active'),
            'updated_by' => $loggedUser->id,
        ]);

        $optionIds = $request->input('option_id', []); // Get existing option IDs
        $englishValues = $request->input('english_value', []);
        $arabicValues = $request->input('arabic_value', []);
        $colorCodes = $request->input('color_code', []);
        $imageFiles = $request->file('image_url', []);

        $existingOptions = ProductAttributeOptions::where('attribute_id', $attribute->id)->get()->keyBy('id');

        $updatedOptionIds = [];
        foreach ($englishValues as $index => $englishValue) {
            $optionId = $optionIds[$index] ?? null;
            $arabicValue = $arabicValues[$index] ?? null;
            $newColorCode = $colorCodes[$index] ?? null;
            $imageUrl = null;

            $existingAttributeValue = $optionId ? ($existingOptions[$optionId] ?? null) : null;

            if ($existingAttributeValue) {
                $colorCode = ($newColorCode === "#000000" || $newColorCode === null)
                    ? $existingAttributeValue->color_code
                    : $newColorCode;

                if (isset($imageFiles[$index])) {
                    $imageUrl = $imageFiles[$index]->store('attribute_images', 'public');
                } else {
                    $imageUrl = $existingAttributeValue->image_url;
                }

                $existingAttributeValue->update([
                    'english_value' => $englishValue,
                    'arabic_value' => $arabicValue,
                    'color_code' => $colorCode,
                    'image_url' => $imageUrl,
                    'updated_by' => $loggedUser->id,
                ]);

                $updatedOptionIds[] = $optionId;
            } else {
                $newOption = ProductAttributeOptions::create([
                    'attribute_id' => $attribute->id,
                    'english_value' => $englishValue,
                    'arabic_value' => $arabicValue,
                    'color_code' => $newColorCode ?? null,
                    'image_url' => $imageUrl,
                    'created_by' => $loggedUser->id,
                    'updated_by' => $loggedUser->id,
                ]);

                $updatedOptionIds[] = $newOption->id; // Store new option ID
            }
        }

        ProductAttributeOptions::where('attribute_id', $attribute->id)
            ->whereNotIn('id', $updatedOptionIds)
            ->delete();

        return redirect()->route('product.attributes.index')->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $productAttributeId = $request->input('id');

        if (!isset($productAttributeId) || is_null($productAttributeId) || !is_numeric($productAttributeId) || ((int)$productAttributeId <= 0)) {
            return response()->json(['message' => 'Invalid Attribute ID'], 400);
        }

        $productAttribute = Attribute::find($productAttributeId);
        if (!$productAttribute) {
            return response()->json(['message' => 'Attribute not found'], 404);
        }

        $productAttribute->delete();

        return response()->json(['message' => 'Attribute deleted successfully']);

    }

    /**
     * Bulk delete selected product attributes.
     *
     * Validates the incoming request to ensure `ids` is an array of existing attribute IDs,
     * then deletes them one by one.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {

        try {
            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:product_attributes,id',
            ]);

            Attribute::whereIn('id', $request->ids)->each(function ($attribute) {
                $attribute->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Selected attributes have been deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting attributes.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

}
