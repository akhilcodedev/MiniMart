<?php

namespace Modules\ProductAttributes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Modules\ProductAttributes\Models\Attribute;
use Modules\ProductAttributes\Models\AttributeSet;
use Modules\ProductAttributes\Models\AttributeSetMap;

class ProductAttributeSetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'createUrl' => route('product.attribute.sets.create'),
            'attributeSetStatuses' => AttributeSet::ACTIVE_STATUS_LIST,
            'attributeSetTypes' => AttributeSet::ATTRIBUTE_SET_TYPE_LIST,
        ];
        return view('productattributes::attributeSets.index', $data);
    }

    /**
     * Fetch all attribute sets with filtering, pagination, and search functionality.
     *
     * @param Request $request The HTTP request containing pagination and filtering parameters.
     * @return \Illuminate\Http\JsonResponse JSON response containing attribute set data.
     */
    public function getAllAttributeSets(Request $request){

        try {
            $authUser = auth()->user();
            $isSuperAdmin = $authUser && $authUser->is_super_admin == 1;
            $start = $request->start;
            $limit = $request->length;
            $draw = $request->draw;

            // Start the query
            $attributeSetsQuery = AttributeSet::with('createdUser', 'updatedUser')->latest('id');

            // Total records count
            $totalData = $attributeSetsQuery->count();

            // Apply search filter
            if (isset($request['search']['value'])) {
                $searchValue = $request['search']['value'];
                $attributeSetsQuery = $attributeSetsQuery->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('label', 'like', '%' . $searchValue . '%')
                        ->orWhere('type', 'like', '%' . $searchValue . '%');
                });
            }
            // Apply status filter
            // Apply status filter
            if (isset($request['attribute_set_status']) && ($request['attribute_set_status'] !== 'all') && is_numeric($request['attribute_set_status']) && in_array((int)$request['attribute_set_status'], array_keys(AttributeSet::ACTIVE_STATUS_LIST))) {
                $statusValue = (int)$request['attribute_set_status'];
                $attributeSetsQuery = $attributeSetsQuery->where('is_active', $statusValue);
            }

            // Filtered records count
            $totalFiltered = $attributeSetsQuery->count();

            // Paginate results
            $attributeSets = $attributeSetsQuery
                ->skip($start)
                ->take($limit)
                ->get();

            // Prepare data for response
            $data = [];


            foreach ($attributeSets as $attributeSet) {
                $btn = '<div class="d-flex gap-2 align-items-center">';
                if ($isSuperAdmin || $authUser->can('edit_attribute_set')) {
                    $btn .= '<a href="' . route('product.attribute.sets.edit', $attributeSet->id) . '" class="btn btn-sm btn-light btn-active-light-primary me-2" style="white-space: nowrap;"><i class="fas fa-edit"></i> Edit</a>';
                }
                if ($isSuperAdmin || $authUser->can('delete_attribute_set')) {
                    $btn .= '<button class="btn btn-sm btn-light btn-active-light-danger deleteAttributeSet" data-id="' . $attributeSet->id . '" style="white-space: nowrap;"><i class="fas fa-trash"></i> Delete</button>';
                }
                $btn .= '</div>';

                $attributeCount = 0;
                if ($attributeSet->mappedAttributes) {
                    foreach ($attributeSet->mappedAttributes as $attribute) {
                        $attributeCount++;
                    }
                }

                $data[] = [
                    'id' => $attributeSet->id,
                    'name' => $attributeSet->name,
                    'label' => $attributeSet->label,
                    'set_type' => AttributeSet::ATTRIBUTE_SET_TYPE_LIST[$attributeSet->type],
                    'description' => $attributeSet->description,
                    'linked_attributes' => $attributeCount,
                    'is_active' => AttributeSet::ACTIVE_STATUS_LIST[(int)$attributeSet->is_active],
                    'created_by' => $attributeSet->createdUser ? $attributeSet->createdUser->name : '-',
                    'created_at' => date('Y-m-d H:i:s', strtotime($attributeSet->created_at)),
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
            'attributeSetStatuses' => AttributeSet::ACTIVE_STATUS_LIST,
            'attributeSetTypes' => AttributeSet::ATTRIBUTE_SET_TYPE_LIST,
        ];
        return view('productattributes::attributeSets.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $attributeStatuses = AttributeSet::ACTIVE_STATUS_LIST;
        $attributeTypes = AttributeSet::ATTRIBUTE_SET_TYPE_LIST;

        $loggedUser = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_attribute_sets,name'],
            'label' => ['required', 'string', 'max:255'],
            'set_type' => ['required', 'string', Rule::in(array_keys($attributeTypes))],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'numeric', Rule::in(array_keys($attributeStatuses))],
        ]);

        DB::beginTransaction();

        try {

            $attributeSetName = Str::snake(strtolower(trim($request->name)));
            $attributeSetSearchObj = AttributeSet::firstWhere('name', $attributeSetName);
            if ($attributeSetSearchObj) {
                return redirect()->back()->withErrors('Attribute Set Name already exists. Please try again.');
            }

            $createData = [
                'name' => $attributeSetName,
                'label' => $request->label,
                'type' => $request->set_type,
                'description' => $request->description ?? null,
                'is_active' => $request->is_active,
                'created_by' => $loggedUser->id,
                'updated_by' => $loggedUser->id,
            ];
            $attributeObj = AttributeSet::create($createData);

            DB::commit();

            return redirect()->route('product.attribute.sets.index')->with('success', 'Attribute Set Created Successfully!');

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error creating attribute : ' . $e->getMessage());

            return redirect()->back()->withErrors('An error occurred while creating the attribute set. Please try again.');

        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        if (!isset($id) || is_null($id) || !is_numeric($id) || ((int)$id <= 0)) {
            return redirect()->route('product.attribute.sets.index')->withErrors('Invalid Attribute Set Id.');
        }

        $attributeSetObj = AttributeSet::find($id);
        if (!$attributeSetObj) {
            return redirect()->route('product.attribute.sets.index')->withErrors('Attribute Set Not found.');
        }

        $mappedAttributes = $attributeSetObj->mappedAttributes;

        $usedActiveAttributeIds = [];
        foreach ($mappedAttributes as $attribute) {
            $usedActiveAttributeIds[] = $attribute->id;
        }

        $allUnusedActiveAttributesQ = Attribute::select('id', 'code', 'label', 'input_type')->where('is_active', Attribute::ACTIVE_YES);
        if (count($usedActiveAttributeIds) > 0) {
            $allUnusedActiveAttributesQ->whereNotIn('id', $usedActiveAttributeIds);
        }
        $allUnusedActiveAttributes = $allUnusedActiveAttributesQ->get();

        $data = [
            'attributeSetMapStatuses' => AttributeSetMap::ACTIVE_STATUS_LIST,
            'attributeSetMapRequires' => AttributeSetMap::REQUIRED_STATUS_LIST,
            'attributeSetStatuses' => AttributeSet::ACTIVE_STATUS_LIST,
            'attributeSetTypes' => AttributeSet::ATTRIBUTE_SET_TYPE_LIST,
            'attributeStatuses' => Attribute::ACTIVE_STATUS_LIST,
            'attributeTypes' => Attribute::INPUT_TYPE_LIST,
            'attributeValueTypes' => Attribute::VALUE_NEEDED_INPUT_TYPES,
            'attributeSetData' => $attributeSetObj,
            'activeAttributes' => $allUnusedActiveAttributes,
        ];

        return view('productattributes::attributeSets.edit', $data);

    }

    /**
     * Fetches and returns the linked attribute details for an attribute set.
     *
     * @param Request $request The HTTP request containing attribute set and attribute IDs.
     * @return \Illuminate\Http\JsonResponse JSON response with attribute set mapping data.
     */
    public function fetchLinkAttributeToSet(Request $request) {

        $attributeSetId = (
            $request->has('attribute_set_id')
            && (trim($request->input('attribute_set_id')) != '')
            && is_numeric($request->input('attribute_set_id'))
            && ((int)trim($request->input('attribute_set_id')) > 0)
        ) ? (int)trim($request->input('attribute_set_id')) : '';

        $attributeId = (
            $request->has('attribute_id')
            && (trim($request->input('attribute_id')) != '')
            && is_numeric($request->input('attribute_id'))
            && ((int)trim($request->input('attribute_id')) > 0)
        ) ? (int)trim($request->input('attribute_id')) : '';

        $attributeSetAction = (
            $request->has('action')
            && (trim($request->input('action')) != '')
        ) ? trim($request->input('action')) : '';

        $returnData = [
            'res' => ''
        ];

        if (($attributeSetId != '') && ($attributeId != '') && ($attributeSetAction != '')) {
            $attributeSetMapObj = AttributeSetMap::select('*')->where('attribute_set_id', $attributeSetId)->where('attribute_id', $attributeId)->first();
            if ($attributeSetMapObj) {

                $attributeSetObj = $attributeSetMapObj->attributeSet;
                $attributeObj = $attributeSetMapObj->attributeData;

                $attributeSetMapStatuses = AttributeSetMap::ACTIVE_STATUS_LIST;
                $attributeSetMapRequires = AttributeSetMap::REQUIRED_STATUS_LIST;
                $attributeSetStatuses = AttributeSet::ACTIVE_STATUS_LIST;
                $attributeSetTypes = AttributeSet::ATTRIBUTE_SET_TYPE_LIST;
                $attributeStatuses = Attribute::ACTIVE_STATUS_LIST;
                $attributeTypes = Attribute::INPUT_TYPE_LIST;
                $attributeValueTypes = Attribute::VALUE_NEEDED_INPUT_TYPES;
                $attributeSetData = $attributeSetObj;
                $activeAttribute = $attributeObj;
                $mappedSetObj = $attributeSetMapObj;

                $returnData['res'] = view('productattributes::attributeSets.link-attribute', compact(
                    'attributeSetMapStatuses',
                    'attributeSetMapRequires',
                    'attributeSetStatuses',
                    'attributeSetTypes',
                    'attributeStatuses',
                    'attributeTypes',
                    'attributeValueTypes',
                    'attributeSetData',
                    'activeAttribute',
                    'mappedSetObj',
                ))->render();

            }
        }

        return response()->json($returnData, 200);

    }

    /**
     * Link an attribute to an attribute set.
     *
     * @param Request $request The HTTP request containing attribute linking details.
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function linkAttributeToSet(Request $request) {

        $validator = Validator::make($request->all() , [
            'attribute_id' => ['required', 'numeric', 'integer', Rule::exists(Attribute::class, 'id')],
            'attribute_set_id' => ['required', 'numeric', 'integer', Rule::exists(AttributeSet::class, 'id')],
            'attribute_value' => ['nullable', 'string'],
            'attribute_desc' => ['nullable', 'string'],
            'attribute_sort_order' => ['required', 'numeric', 'integer'],
            'attribute_is_required' => ['required', 'numeric', Rule::in(array_keys(AttributeSetMap::REQUIRED_STATUS_LIST))],
            'attribute_is_active' => ['required', 'numeric', Rule::in(array_keys(AttributeSetMap::ACTIVE_STATUS_LIST))],
        ]);

        if ($validator->fails()) {
            $errMessage = implode(" | ", $validator->errors()->all());
            return response()->json(['message' => $errMessage], 400);
        }

        $postData = $validator->validated();

        $attributeId = (array_key_exists('attribute_id', $postData)) ? (int)trim($postData['attribute_id']) : '';
        $attributeSetId = (array_key_exists('attribute_set_id', $postData)) ? (int)trim($postData['attribute_set_id']) : '';
        $attributeValue = (array_key_exists('attribute_value', $postData)) ? trim($postData['attribute_value']) : null;
        $attributeDesc = (array_key_exists('attribute_desc', $postData)) ? trim($postData['attribute_desc']) : null;
        $attributeSortOrder = (array_key_exists('attribute_sort_order', $postData)) ? (int)trim($postData['attribute_sort_order']) : 0;
        $attributeIsRequired = (array_key_exists('attribute_is_required', $postData)) ? (int)trim($postData['attribute_is_required']) : AttributeSetMap::REQUIRED_NO;
        $attributeIsActive = (array_key_exists('attribute_is_active', $postData)) ? (int)trim($postData['attribute_is_active']) : AttributeSetMap::ACTIVE_YES;
        $values = explode(',', $attributeValue);
        $formattedValues = collect($values)->map(fn($val) => [
            'label' => ucfirst(trim($val)),
            'value' => trim($val),
        ])->toJson();

        $attributeValue = $formattedValues;
        $attributeObj = Attribute::find($attributeId);
        if (in_array($attributeObj->input_type, array_keys(Attribute::VALUE_NEEDED_INPUT_TYPES)) && is_null($attributeValue)) {
            return response()->json(['message' => 'The given Attribute need a value.'], 400);
        }

        $loggedUser = Auth::user();

        $attributeSetMap = AttributeSetMap::updateOrCreate([
            'attribute_set_id' => $attributeSetId,
            'attribute_id' => $attributeObj->id,
        ], [
            'value' => $attributeValue,
            'description' => $attributeDesc,
            'sort_order' => $attributeSortOrder,
            'is_required' => $attributeIsRequired,
            'is_active' => $attributeIsActive,
            'updated_by' => $loggedUser->id,
        ]);

        if (is_null($attributeSetMap->created_by)) {
            $attributeSetMap->fill(['created_by' => $loggedUser->id])->save();
        }

        return response()->json(['message' => 'Attribute is linked to the Attribute Set successfully!'], 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        if (!isset($id) || is_null($id) || !is_numeric($id) || ((int)$id <= 0)) {
            return redirect()->route('product.attribute.sets.index')->withErrors('Invalid Attribute Set Id.');
        }

        $attributeSetObj = AttributeSet::find($id);
        if (!$attributeSetObj) {
            return redirect()->route('product.attribute.sets.index')->withErrors('Attribute Set Not found.');
        }

        $attributeStatuses = AttributeSet::ACTIVE_STATUS_LIST;
        $attributeTypes = AttributeSet::ATTRIBUTE_SET_TYPE_LIST;

        $loggedUser = Auth::user();

        $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'set_type' => ['required', 'string', Rule::in(array_keys($attributeTypes))],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'numeric', Rule::in(array_keys($attributeStatuses))],
        ]);

        DB::beginTransaction();

        try {

            $updateData = [
                'label' => $request->label,
                'type' => $request->set_type,
                'description' => $request->description ?? null,
                'is_active' => $request->is_active,
                'updated_by' => $loggedUser->id,
            ];
            $attributeSetObj->fill($updateData)->save();

            DB::commit();

            return redirect()->route('product.attribute.sets.index')->with('success', 'Attribute Set updated Successfully!');

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error creating attribute : ' . $e->getMessage());

            return redirect()->back()->withErrors('An error occurred while updating the attribute set. Please try again.');

        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $attributeSetId = $request->input('id');

        if (!isset($attributeSetId) || is_null($attributeSetId) || !is_numeric($attributeSetId) || ((int)$attributeSetId <= 0)) {
            return response()->json(['message' => 'Invalid Attribute Set Id'], 40);
        }

        $attributeSetObj = AttributeSet::find($attributeSetId);
        if (!$attributeSetObj) {
            return response()->json(['message' => 'Attribute Set not found'], 404);
        }

        $attributeSetObj->delete();

        return response()->json(['message' => 'Attribute Set deleted successfully']);

    }

    /**
     * Bulk delete attribute sets.
     *
     * @param Request $request The HTTP request containing attribute set IDs to be deleted.
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function bulkDelete(Request $request)
    {

        try {
            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:product_attributes,id',
            ]);

            AttributeSet::whereIn('id', $request->ids)->each(function ($attributeSet) {
                $attributeSet->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Selected attribute sets have been deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting attribute sets.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

}
