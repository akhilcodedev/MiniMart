<?php

namespace Modules\PriceRuleManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Modules\Category\Models\Category;
use Modules\Customer\Models\Customer;
use Modules\PriceRuleManagement\Helpers\CartRuleServiceHelper;
use Modules\PriceRuleManagement\Models\Coupon;
use Modules\PriceRuleManagement\Models\CouponMode;
use Modules\PriceRuleManagement\Models\CouponType;
use Modules\PriceRuleManagement\Models\CouponEntity;
use Modules\PriceRuleManagement\Models\CouponEntityMap;
use Modules\PriceRuleManagement\Models\CouponEligibilityMap;
use Modules\Products\Models\Product;

class CartPriceRuleManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $pageTitle = 'Coupons';
        $pageSubTitle = 'Coupons';

        $serviceHelper = new CartRuleServiceHelper();

        $todayDate = date('Y-m-d');

        return view('pricerulemanagement::coupons.list', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
            'serviceHelper'
        ));

    }

    /**
     * Display a listing of the resource.
     */
    public function searchCouponByFilters(Request $request)
    {

        set_time_limit(600);

        $serviceHelper = new CartRuleServiceHelper();

        $availableActions = ['datatable'];
        $methodAction = (
            $request->has('filter_action')
            && (trim($request->input('filter_action')) != '')
            && in_array(trim($request->input('filter_action')), $availableActions)
        ) ? trim($request->input('filter_action')) : 'datatable';

        $dtDraw = (
            $request->has('draw')
            && (trim($request->input('draw')) != '')
        ) ? (int)trim($request->input('draw')) : 1;

        $dtStart = (
            $request->has('start')
            && (trim($request->input('start')) != '')
        ) ? (int)trim($request->input('start')) : 0;

        $dtPageLength = (
            $request->has('length')
            && (trim($request->input('length')) != '')
        ) ? (int)trim($request->input('length')) : 10;

        $sortOrder = (
            $request->has('order')
            && is_array($request->input('order'))
            && (count($request->input('order')) > 0)
        ) ? $request->input('order') : [];

        $columnDefs = (
            $request->has('columnsDef')
            && is_array($request->input('columnsDef'))
            && (count($request->input('columnsDef')) > 0)
        ) ? $request->input('columnsDef') : [];

        $searchTerm = (
            $request->has('search_term_filter')
            && (trim($request->input('search_term_filter')) != '')
        ) ? trim($request->input('search_term_filter')) : '';

        $dtSortColumn = '';
        $dtSortDir = '';
        if (count($sortOrder) > 0) {
            $dtSortOrder = $sortOrder[0];
            if (array_key_exists('column', $dtSortOrder)) {
                $dtSortColumn = ((count($columnDefs) > 0) && array_key_exists($dtSortOrder['column'], $columnDefs)) ? $columnDefs[$dtSortOrder['column']] : '';
            }
            if (array_key_exists('dir', $dtSortOrder) && ($dtSortColumn != '')) {
                $allowedDirs = ['asc', 'desc'];
                $dtSortDir = in_array(strtolower($dtSortOrder['dir']), $allowedDirs) ? strtolower($dtSortOrder['dir']) : '';
            }
        }

        $returnData = [];
        if ($methodAction == 'datatable') {

            $filteredCouponsRaw = $serviceHelper->getCouponsFilteredData($searchTerm, $dtStart, $dtPageLength, $dtSortColumn, $dtSortDir);
            if (!is_null($filteredCouponsRaw)) {

                $totalCount = $filteredCouponsRaw['totalCount'];
                $filteredCoupons = $filteredCouponsRaw['filteredData'];

                if ($filteredCoupons->count() > 0) {

                    $filteredCouponData = [];
                    $filteredCouponArrayData = json_decode($filteredCoupons->toJson(), true);
                    $totalRec = 0;

                    foreach ($filteredCouponArrayData as $couponArrayEl) {

                        $couponDesc = '<button class="btn btn-sm btn-primary waves-effect waves-light" data-toggle="modal" data-id='. $couponArrayEl['couponId'] . ' data-name="'. $couponArrayEl['couponName'] . '" data-field="description" data-field-label="Description" data-target="#couponDetailsModal">Description</button>';

                        $couponUpdatedAt = $serviceHelper->getFormattedTime($couponArrayEl['updatedAt'], 'F d, Y, h:i:s A');

                        $isActive = "";
                        if ($couponArrayEl['isActive'] == Coupon::ACTIVE_YES){
                            $isActive = '<span class="label label-lg font-weight-bold label-light-success label-inline">Yes</span>';
                        } else {
                            $isActive = '<span class="label label-lg font-weight-bold label-light-danger label-inline">No</span>';
                        }

                        if (auth()->user()->is_super_admin || auth()->user()->can('edit_coupons')) {
                            $actionsBlock = '<a href="' . route('priceRule.cart.coupons.edit', ['couponId' => $couponArrayEl['couponId']]) . '" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit" target="_blank"><i class="fa-solid fa-pen text-warning"></i></a>';
                        }
                        if (auth()->user()->is_super_admin || auth()->user()->can('delete_coupons')) {
                            $actionsBlock .= '<a href="javascript:void(0);" data-id="' . $couponArrayEl['couponId'] . '" class="btn btn-sm btn-clean btn-icon delete-coupon" title="Delete"><i class="fa-solid fa-trash text-danger"></i></a>';
                        }

                        $tempRecord = [
                            'couponId' => $couponArrayEl['couponId'],
                            'couponCode' => $couponArrayEl['couponCode'],
                            'couponName' => $couponArrayEl['couponName'],
                            'couponDesc' => $couponDesc,
                            'startDate' => $couponArrayEl['startDate'],
                            'endDate' => $couponArrayEl['endDate'],
                            'couponType' => $couponArrayEl['couponType'],
                            'couponMode' => $couponArrayEl['couponMode'],
                            'couponDiscount' => $couponArrayEl['couponDiscount'],
                            'couponMaxDiscount' => ($couponArrayEl['couponHasLimit'] == Coupon::MAX_LIMIT_YES) ? $couponArrayEl['couponMaxDiscount'] : 'No',
                            'minCartValue' => $couponArrayEl['minCartValue'],
                            'couponEntity' => $couponArrayEl['CouponEntity'],
                            /*'couponRegion' =>  Coupon::REGION_ELIGIBILITY_LIST[$couponArrayEl['couponRegion']],*/
                            'couponCustomer' => Coupon::CUSTOMER_ELIGIBILITY_LIST[$couponArrayEl['couponCustomer']],
                            'couponOrder' => Coupon::ORDER_ELIGIBILITY_LIST[$couponArrayEl['couponOrder']],
                            'couponOrderValue' => $couponArrayEl['couponOrderValue'],
                            'totalAvailable' => $couponArrayEl['totalAvailable'],
                            'countPerUser' => $couponArrayEl['countPerUser'],
                            'usedCount' => $couponArrayEl['usedCount'],
                            'updatedAt' => $couponUpdatedAt,
                            'isActive' => $isActive,
                            'actions' => $actionsBlock,
                        ];

                        $filteredCouponData[] = $tempRecord;
                        $totalRec++;

                    }

                    $returnData = [
                        'draw' => $dtDraw,
                        'recordsTotal' => $totalCount,
                        'recordsFiltered' => $totalCount,
                        'data' => $filteredCouponData
                    ];

                } else {
                    $returnData = [
                        'draw' => $dtDraw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => []
                    ];
                }

            } else {
                $returnData = [
                    'draw' => $dtDraw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ];
            }

        }

        return response()->json($returnData, 200);

    }

    /**
     * Display a listing of the resource.
     */
    public function getCouponDetail(Request $request)
    {

        $couponId = (
            $request->has('id')
            && (trim($request->input('id')) != '')
            && is_numeric($request->input('id'))
            && ((int)trim($request->input('id')) > 0)
        ) ? (int)trim($request->input('id')) : '';

        $couponField = (
            $request->has('field')
            && (trim($request->input('field')) != '')
        ) ? trim($request->input('field')) : '';

        $returnData = [
            'res' => ''
        ];

        if (($couponId != '') && ($couponField != '')) {
            $couponObj = Coupon::find($couponId);
            if ($couponObj) {
                if (($couponField == 'description') && (trim($couponObj->description) != '')) {
                    $returnData['res'] = html_entity_decode($couponObj->description);
                }
            }
        }

        return response()->json($returnData, 200);

    }

    /**
     * Display a listing of the resource.
     */
    public function importCoupon(Request $request)
    {

        try {
            $file = $request->file('coupon_import_file');
            $reader = Reader::createFromPath($file->getPathname(), 'r');
            $reader->setHeaderOffset(0);
            $records = $reader->getRecords();

            foreach ($records as $record) {

                // Get IDs for related tables
                $application = CouponEntity::firstOrCreate(['name' => $record['couponApplication.name']]);
                $mode = CouponMode::firstOrCreate(['name' => $record['couponMode.name']]);
                $type = CouponType::firstOrCreate(['name' => $record['couponType.name']]);

                if (!$application || !$mode || !$type) {
                    throw new Exception('Related record not found for coupon');
                }

                $couponData = [
                    'code' => $record['code'] ?? null,
                    'name' => $record['name'] ?? null,
                    'start_date' => $record['start_date'] ?? null,
                    'end_date' => $record['end_date'] ?? null,
                    'is_active' => $record['is_active'] ?? null,
                    'description' => $record['description'] ?? null,
                    'discount_value' => $record['discount_value'] ?? null,
                    'has_max_limit' => $record['has_max_limit'] ?? 0,
                    'max_discount_value' => $record['max_discount_value'] ?? null,
                    'min_cart_value' => $record['min_cart_value'] ?? null,
                    'max_usage_count' => $record['max_usage_count'] ?? null,
                    'max_count_per_user' => $record['max_count_per_user'] ?? null,
                    'order_eligibility' => $record['order_eligibility'] ?? null,
                    'order_eligibility_value' => $record['order_eligibility_value'] ?? null,
                    'customer_eligibility' => $record['customer_eligibility'] ?? null,
                    'region_eligibility' => $record['region_eligibility'] ?? null,
                    'used_count' => $record['used_count'] ?? null,
                ];

                Coupon::updateOrCreate($couponData);
            }

            return redirect()->route('priceRule.cart.coupons.index')->with('success', 'The Coupons are imported successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'The Coupons could not import! ' . $e->getMessage());
        }

    }

    /**
     * Display a listing of the resource.
     */
    public function exportCoupon(Request $request)
    {

        try {
            $coupons = Coupon::with('couponEntity', 'couponMode', 'couponType')->get();
            $fileName = 'coupons-master-' . date('Y-m-d h-i-sa') . '.csv';

            $columns = [
                'code',
                'name',
                'couponEntity.name',
                'couponType.name',
                'couponMode.name',
                'description',
                'start_date',
                'end_date',
                'discount_value',
                'has_max_limit',
                'max_discount_value',
                'min_cart_value',
                'customer_eligibility',
                'order_eligibility',
                'order_eligibility_value',
                'used_count',
                'max_usage_count',
                'max_count_per_user',
                'is_active',
            ];

            $callback = function () use ($coupons, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($coupons as $coupon) {
                    $data = [];
                    foreach ($columns as $column) {
                        if (strpos($column, '.') !== false) {
                            $relation = explode('.', $column);
                            $relatedModel = $coupon->{$relation[0]};
                            $value = ($relatedModel && $relatedModel->name) ? $relatedModel->name : '';
                        } else {
                            $value = $coupon->{$column};
                        }
                        $data[] = $value;
                    }
                    fputcsv($file, $data);
                }

                fclose($file);
            };
            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'The Coupons could not export! ' . $e->getMessage());
        }

    }

    /**
     * Display a listing of the resource.
     */
    public function searchItemsToCoupon(Request $request)
    {

        $searchTerm = (
            $request->has('term')
            && (trim($request->input('term')) != '')
        ) ? trim($request->input('term')) : '';

        $applyTo = (
            $request->has('applyId')
            && (trim($request->input('applyId')) != '')
        ) ? trim($request->input('applyId')) : '';

        $page = (
            $request->has('page')
            && (trim($request->input('page')) != '')
        ) ? (int)trim($request->input('page')) : 0;

        $serviceHelper = new CartRuleServiceHelper();
        $itemListData = $serviceHelper->getCouponItemsFilteredData($applyTo, $searchTerm, $page, 20);

        $itemList = [];
        if ($itemListData && (count($itemListData) > 0)) {
            $itemList = json_decode(json_encode($itemListData), true);
        }

        $returnData = [
            'status' => (count($itemList) > 0) ? true : false,
            'items' => $itemList
        ];

        return response()->json($returnData, 200);

    }

    /**
     * Display a listing of the resource.
     */
    public function searchCustomersToCoupon(Request $request)
    {

        $searchTerm = (
            $request->has('term')
            && (trim($request->input('term')) != '')
        ) ? trim($request->input('term')) : '';

        $page = (
            $request->has('page')
            && (trim($request->input('page')) != '')
        ) ? (int)trim($request->input('page')) : 0;

        $serviceHelper = new CartRuleServiceHelper();
        $customerListData = $serviceHelper->getCustomersSearchData($searchTerm, $page, 20);

        $customerList = [];
        if ($customerListData && (count($customerListData) > 0)) {
            $targetCustomersArray = $customerListData->toArray();
            $customerList = json_decode(json_encode($targetCustomersArray), true);
        }

        $returnData = [
            'status' => (count($customerList) > 0) ? true : false,
            'items' => $customerList
        ];

        return response()->json($returnData, 200);

    }

    /**
     * Display a listing of the resource.
     */
    public function newCoupon(Request $request)
    {

        $pageTitle = 'Coupons';
        $pageSubTitle = 'Add Coupon';

        $serviceHelper = new CartRuleServiceHelper();

        $availableCouponTypes = $serviceHelper->getCouponTypeList()->toArray();
        $availableCouponModes = $serviceHelper->getCouponModeList()->toArray();
        $availableCouponEntities = $serviceHelper->getCouponEntityList()->toArray();

        $regionEligibilityList = Coupon::REGION_ELIGIBILITY_LIST;
        $customerEligibilityList = Coupon::CUSTOMER_ELIGIBILITY_LIST;
        $orderEligibilityList = Coupon::ORDER_ELIGIBILITY_LIST;

        $todayDate = date('Y-m-d');

        return view('pricerulemanagement::coupons.add', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
            'availableCouponTypes',
            'availableCouponModes',
            'availableCouponEntities',
            'regionEligibilityList',
            'customerEligibilityList',
            'orderEligibilityList',
            'serviceHelper'
        ));

    }

    /**
     * Display a listing of the resource.
     */
    public function saveCoupon(Request $request)
    {

        $loggedUser = Auth::user();
        $loggerUserId = $loggedUser->id;

        $validator = Validator::make($request->all() , [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'code' => ['required', 'string', 'min:3', 'max:255'],
            'coupon_type' => ['required', 'numeric', 'integer'],
            'start_date_value' => ['nullable', 'string', 'date'],
            'end_date_value' => ['nullable', 'string', 'date'],
            'status' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'coupon_mode' => ['required', 'numeric', 'integer'],
            'discount_value' => ['required', 'numeric'],
            'has_max_limit' => ['nullable', 'numeric'],
            'max_discount_value' => ['nullable', 'numeric'],
            'min_cart_value' => ['nullable', 'numeric'],
            'max_usage_count' => ['nullable', 'numeric'],
            'max_count_per_user' => ['nullable', 'numeric'],
            'order_eligibility' => ['nullable', 'numeric'],
            'order_eligibility_value' => ['nullable', 'numeric'],
            'entity_id' => ['required', 'numeric', 'integer'],
            'coupon_items' => ['nullable', 'array'],
            'customer_eligibility' => ['nullable', 'numeric'],
            'coupon_customers' => ['nullable', 'array'],
        ], [
            'name.required' => 'The Coupon Name should be provided.',
            'name.string' => 'The Coupon Name should be a string value.',
            'name.min' => 'The Coupon Name should be minimum :min characters.',
            'name.max' => 'The Coupon Name should not exceed :max characters.',
            'code.required' => 'The Coupon Code should be provided.',
            'code.string' => 'The Coupon Code should be a string value.',
            'code.min' => 'The Coupon Code should be minimum :min characters.',
            'code.max' => 'The Coupon Code should not exceed :max characters.',
            'coupon_type.required' => 'The Coupon Type should be provided.',
            'coupon_mode.required' => 'The Coupon Mode should be provided.',
            'discount_value.required' => 'The Coupon Discount Value should be provided.',
            'entity_id.required' => 'The Coupon Entity should be provided.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)->withInput();
        }

        $postData = $validator->validated();

        $serviceHelper = new CartRuleServiceHelper();

        $couponCode = trim($postData['code']);
        $targetCouponData = Coupon::select('id', 'name', 'code')
            ->whereRaw("code like '$couponCode'")
            ->get();
        if ($targetCouponData && (count($targetCouponData) > 0)) {
            return back()
                ->with('error', "The Coupon Code '$couponCode' already exists!")->withInput();
        }

        $tempRecord = [
            'code' => $couponCode,
            'name' => trim($postData['name']),
            'type_id' => trim($postData['coupon_type']),
            'mode_id' => trim($postData['coupon_mode']),
            'entity_id' => trim($postData['entity_id']),
            'description' => trim($postData['description']),
            'start_date' => (!is_null($postData['start_date_value']) && (trim($postData['start_date_value']) != '')) ? date('Y-m-d', strtotime(trim($postData['start_date_value']))) : null,
            'end_date' => (!is_null($postData['end_date_value']) && (trim($postData['end_date_value']) != '')) ? date('Y-m-d', strtotime(trim($postData['end_date_value']))) : null,
            'discount_value' => (float)trim($postData['discount_value']),
            'has_max_limit' => ($postData['has_max_limit'] == '1') ? Coupon::MAX_LIMIT_YES : Coupon::MAX_LIMIT_NO,
            'max_discount_value' => (($postData['has_max_limit'] == '1') && (trim($postData['max_discount_value']) != '')) ? (float)trim($postData['max_discount_value']) : null,
            'min_cart_value' => (trim($postData['min_cart_value']) != '') ? (float)trim($postData['min_cart_value']) : null,
            'customer_eligibility' => ($postData['customer_eligibility'] == '1') ? Coupon::CUSTOMER_ELIGIBILITY_SPECIFIC : Coupon::CUSTOMER_ELIGIBILITY_ALL,
            'region_eligibility' => Coupon::REGION_ELIGIBILITY_ALL,
            'order_eligibility' => (array_key_exists($postData['order_eligibility'], Coupon::ORDER_ELIGIBILITY_LIST)) ? $postData['order_eligibility'] : Coupon::ORDER_ELIGIBILITY_ALL,
            'max_usage_count' => ((int)trim($postData['max_usage_count']) > 0) ? (int)trim($postData['max_usage_count']) : null,
            'order_eligibility_value' => null,
            'max_count_per_user' => ((int)trim($postData['max_count_per_user']) > 0) ? (int)trim($postData['max_count_per_user']) : null,
            'is_active' => ($postData['status'] == '1') ? Coupon::ACTIVE_YES : Coupon::ACTIVE_NO,
            'created_by' => $loggerUserId,
            'updated_by' => $loggerUserId,
        ];

        if (($tempRecord['order_eligibility'] != Coupon::ORDER_ELIGIBILITY_ALL) && (trim($postData['order_eligibility_value']) != '')) {
            $tempRecord['order_eligibility_value'] = (float)trim($postData['order_eligibility_value']);
        }

        $couponObj = Coupon::create($tempRecord);

        if (array_key_exists('coupon_customers', $postData) && is_array($postData['coupon_customers'])) {
            $deletedItems = CouponEligibilityMap::whereIn('coupon_id', [$couponObj->id])
                ->where('eligible_code',  CouponEligibilityMap::ELIGIBILITY_CODE_CUSTOMER)->delete();
            if (($tempRecord['customer_eligibility'] == Coupon::CUSTOMER_ELIGIBILITY_SPECIFIC) && (count($postData['coupon_customers']) > 0)) {
                foreach ($postData['coupon_customers'] as $itemId) {
                    $itemCreatedObj = CouponEligibilityMap::create([
                        'coupon_id' => $couponObj->id,
                        'eligible_code' => CouponEligibilityMap::ELIGIBILITY_CODE_CUSTOMER,
                        'target_id' => (int)$itemId,
                        'is_active' => CouponEligibilityMap::ACTIVE_YES,
                        'created_by' => $loggerUserId,
                        'updated_by' => $loggerUserId,
                    ]);
                }
            }
        }

        if (array_key_exists('coupon_items', $postData) && is_array($postData['coupon_items'])) {
            $deletedItems = CouponEntityMap::whereIn('coupon_id', [$couponObj->id])->delete();
            $entityObj = CouponEntity::find((int)$tempRecord['entity_id']);
            if ($entityObj && ($entityObj->code != CouponEntity::COUPON_ENTITY_ALL) && (count($postData['coupon_items']) > 0)) {
                foreach ($postData['coupon_items'] as $itemId) {
                    $itemCreatedObj = CouponEntityMap::create([
                        'coupon_id' => $couponObj->id,
                        'entity_id' => (int)$tempRecord['entity_id'],
                        'target_id' => (int)$itemId,
                        'is_active' => CouponEntityMap::ACTIVE_YES,
                        'created_by' => $loggerUserId,
                        'updated_by' => $loggerUserId,
                    ]);
                }
            }
        }

        return redirect()->route('priceRule.cart.coupons.index')->with('success', 'The Coupon is added successfully!');

    }

    /**
     * Display a listing of the resource.
     */
    public function editCoupon($couponId)
    {

        if (is_null($couponId) || !is_numeric($couponId) || ((int)$couponId <= 0)) {
            return back()
                ->with('error', 'The Coupon Id input is invalid!');
        }

        $givenCouponData = Coupon::find($couponId);
        if(!$givenCouponData) {
            return back()
                ->with('error', 'The Coupon does not exist!');
        }

        $pageTitle = 'Coupons';
        $pageSubTitle = 'Edit Coupon : ' . $givenCouponData->code;

        $serviceHelper = new CartRuleServiceHelper();

        $availableCouponTypes = $serviceHelper->getCouponTypeList()->toArray();
        $availableCouponModes = $serviceHelper->getCouponModeList()->toArray();
        $availableCouponEntities = $serviceHelper->getCouponEntityList()->toArray();

        $regionEligibilityList = Coupon::REGION_ELIGIBILITY_LIST;
        $customerEligibilityList = Coupon::CUSTOMER_ELIGIBILITY_LIST;
        $orderEligibilityList = Coupon::ORDER_ELIGIBILITY_LIST;

        $givenCouponData->entityMap;
        $givenCouponData->eligibleCustomers;

        $givenCouponDataArray = $givenCouponData->toArray();

        $appliedCustomers = [];
        $appliedCustomersRaw = $givenCouponDataArray['eligible_customers'];
        if (count($appliedCustomersRaw) > 0) {
            $targetItems = Customer::whereIn('id', array_unique(array_column($appliedCustomersRaw, 'target_id')))->get();
            if ($targetItems && (count($targetItems) > 0)) {
                foreach ($targetItems as $itemEl) {
                    $appliedCustomers[$itemEl->id] = [
                        'id' => $itemEl->id,
                        'label' => $itemEl->first_name . ' ' . $itemEl->last_name,
                    ];
                }
            }
        }

        $appliedItems = [];
        $appliedItemsCheck = [];
        $appliedItemsRaw = $givenCouponDataArray['entity_map'];
        if (count($appliedItemsRaw) > 0) {

            foreach ($appliedItemsRaw as $item) {
                $appliedItemsCheck[$item['entity_id']][] = $item['target_id'];
            }

            $targetEntityArray = (array_key_exists($givenCouponData->entity_id, $appliedItemsCheck))
                ? $appliedItemsCheck[$givenCouponData->entity_id] : [];
            if (count($targetEntityArray) > 0) {
                $entityObj = CouponEntity::find($givenCouponData->entity_id);
                if ($entityObj) {
                    switch($entityObj->code) {
                        case CouponEntity::COUPON_ENTITY_CATEGORY:
                            $targetItems = Category::whereIn('id', array_unique($targetEntityArray))->get();
                            if ($targetItems && (count($targetItems) > 0)) {
                                foreach ($targetItems as $itemEl) {
                                    $appliedItems[$itemEl->id] = [
                                        'id' => $itemEl->id,
                                        'label' => $itemEl->name,
                                    ];
                                }
                            }
                            break;
                        case CouponEntity::COUPON_ENTITY_PRODUCT:
                            $targetItems = Product::whereIn('id', array_unique($targetEntityArray))->get();
                            if ($targetItems && (count($targetItems) > 0)) {
                                foreach ($targetItems as $itemEl) {
                                    $appliedItems[$itemEl->id] = [
                                        'id' => $itemEl->id,
                                        'label' => $itemEl->name . ' (' . $itemEl->sku . ')',
                                    ];
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
            }

        }

        $todayDate = date('Y-m-d');

        return view('pricerulemanagement::coupons.edit', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
            'givenCouponData',
            'givenCouponDataArray',
            'appliedItems',
            'appliedCustomers',
            'availableCouponTypes',
            'availableCouponModes',
            'availableCouponEntities',
            'regionEligibilityList',
            'customerEligibilityList',
            'orderEligibilityList',
            'serviceHelper'
        ));

    }

    /**
     * Display a listing of the resource.
     */
    public function updateCoupon(Request $request, $couponId)
    {

        if (is_null($couponId) || !is_numeric($couponId) || ((int)$couponId <= 0)) {
            return back()
                ->with('error', 'The Coupon Id input is invalid!');
        }

        $givenCouponData = Coupon::find($couponId);
        if(!$givenCouponData) {
            return back()
                ->with('error', 'The Coupon does not exist!');
        }

        $loggedUser = Auth::user();
        $loggerUserId = $loggedUser->id;

        $validator = Validator::make($request->all() , [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'code' => ['required', 'string', 'min:3', 'max:255'],
            'coupon_type' => ['required', 'numeric', 'integer'],
            'start_date_value' => ['nullable', 'string', 'date'],
            'end_date_value' => ['nullable', 'string', 'date'],
            'status' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'coupon_mode' => ['required', 'numeric', 'integer'],
            'discount_value' => ['required', 'numeric'],
            'has_max_limit' => ['nullable', 'numeric'],
            'max_discount_value' => ['nullable', 'numeric'],
            'min_cart_value' => ['nullable', 'numeric'],
            'max_usage_count' => ['nullable', 'numeric'],
            'max_count_per_user' => ['nullable', 'numeric'],
            'order_eligibility' => ['nullable', 'numeric'],
            'order_eligibility_value' => ['nullable', 'numeric'],
            'entity_id' => ['required', 'numeric', 'integer'],
            'coupon_items' => ['nullable', 'array'],
            'customer_eligibility' => ['nullable', 'numeric'],
            'coupon_customers' => ['nullable', 'array'],
        ], [
            'name.required' => 'The Coupon Name should be provided.',
            'name.string' => 'The Coupon Name should be a string value.',
            'name.min' => 'The Coupon Name should be minimum :min characters.',
            'name.max' => 'The Coupon Name should not exceed :max characters.',
            'code.required' => 'The Coupon Code should be provided.',
            'code.string' => 'The Coupon Code should be a string value.',
            'code.min' => 'The Coupon Code should be minimum :min characters.',
            'code.max' => 'The Coupon Code should not exceed :max characters.',
            'coupon_type.required' => 'The Coupon Type should be provided.',
            'coupon_mode.required' => 'The Coupon Mode should be provided.',
            'discount_value.required' => 'The Coupon Discount Value should be provided.',
            'entity_id.required' => 'The Coupon Entity should be provided.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator);
        }

        $postData = $validator->validated();

        $serviceHelper = new CartRuleServiceHelper();

        $couponCode = trim($postData['code']);
        $targetCouponData = Coupon::select('id', 'name', 'code')
            ->whereRaw("code like '$couponCode'")
            ->whereNotIn('id', [$couponId])
            ->get();
        if ($targetCouponData && (count($targetCouponData) > 0)) {
            return back()
                ->with('error', "The Coupon Code '$couponCode' already exists!");
        }

        $tempRecord = [
            'code' => $couponCode,
            'name' => trim($postData['name']),
            'type_id' => trim($postData['coupon_type']),
            'mode_id' => trim($postData['coupon_mode']),
            'entity_id' => trim($postData['entity_id']),
            'description' => trim($postData['description']),
            'start_date' => (!is_null($postData['start_date_value']) && (trim($postData['start_date_value']) != '')) ? date('Y-m-d', strtotime(trim($postData['start_date_value']))) : null,
            'end_date' => (!is_null($postData['end_date_value']) && (trim($postData['end_date_value']) != '')) ? date('Y-m-d', strtotime(trim($postData['end_date_value']))) : null,
            'discount_value' => (float)trim($postData['discount_value']),
            'has_max_limit' => ($postData['has_max_limit'] == '1') ? Coupon::MAX_LIMIT_YES : Coupon::MAX_LIMIT_NO,
            'max_discount_value' => (($postData['has_max_limit'] == '1') && (trim($postData['max_discount_value']) != '')) ? (float)trim($postData['max_discount_value']) : null,
            'min_cart_value' => (trim($postData['min_cart_value']) != '') ? (float)trim($postData['min_cart_value']) : null,
            'customer_eligibility' => ($postData['customer_eligibility'] == '1') ? Coupon::CUSTOMER_ELIGIBILITY_SPECIFIC : Coupon::CUSTOMER_ELIGIBILITY_ALL,
            'region_eligibility' => Coupon::REGION_ELIGIBILITY_ALL,
            'order_eligibility' => (array_key_exists($postData['order_eligibility'], Coupon::ORDER_ELIGIBILITY_LIST)) ? $postData['order_eligibility'] : Coupon::ORDER_ELIGIBILITY_ALL,
            'max_usage_count' => ((int)trim($postData['max_usage_count']) > 0) ? (int)trim($postData['max_usage_count']) : null,
            'order_eligibility_value' => null,
            'max_count_per_user' => ((int)trim($postData['max_count_per_user']) > 0) ? (int)trim($postData['max_count_per_user']) : null,
            'is_active' => ($postData['status'] == '1') ? Coupon::ACTIVE_YES : Coupon::ACTIVE_NO,
            'updated_by' => $loggerUserId,
        ];

        if (($tempRecord['order_eligibility'] != Coupon::ORDER_ELIGIBILITY_ALL) && (trim($postData['order_eligibility_value']) != '')) {
            $tempRecord['order_eligibility_value'] = (float)trim($postData['order_eligibility_value']);
        }

        $couponObj = Coupon::find($couponId);
        $couponObj->fill($tempRecord)->save();

        if (array_key_exists('coupon_regions', $postData) && is_array($postData['coupon_regions'])) {
            $deletedItems = CouponEligibilityMap::whereIn('coupon_id', [$couponObj->id])
                ->where('eligible_code',  CouponEligibilityMap::ELIGIBILITY_CODE_REGION)->delete();
            if (($tempRecord['region_eligibility'] == Coupon::REGION_ELIGIBILITY_SPECIFIC) && (count($postData['coupon_regions']) > 0)) {
                foreach ($postData['coupon_regions'] as $itemId) {
                    $itemCreatedObj = CouponEligibilityMap::create([
                        'coupon_id' => $couponObj->id,
                        'eligible_code' => CouponEligibilityMap::ELIGIBILITY_CODE_REGION,
                        'target_id' => (int)$itemId,
                        'is_active' => CouponEligibilityMap::ACTIVE_YES,
                        'created_by' => $loggerUserId,
                        'updated_by' => $loggerUserId,
                    ]);
                }
            }
        }

        if (array_key_exists('coupon_customers', $postData) && is_array($postData['coupon_customers'])) {
            $deletedItems = CouponEligibilityMap::whereIn('coupon_id', [$couponObj->id])
                ->where('eligible_code',  CouponEligibilityMap::ELIGIBILITY_CODE_CUSTOMER)->delete();
            if (($tempRecord['customer_eligibility'] == Coupon::CUSTOMER_ELIGIBILITY_SPECIFIC) && (count($postData['coupon_customers']) > 0)) {
                foreach ($postData['coupon_customers'] as $itemId) {
                    $itemCreatedObj = CouponEligibilityMap::create([
                        'coupon_id' => $couponObj->id,
                        'eligible_code' => CouponEligibilityMap::ELIGIBILITY_CODE_CUSTOMER,
                        'target_id' => (int)$itemId,
                        'is_active' => CouponEligibilityMap::ACTIVE_YES,
                        'created_by' => $loggerUserId,
                        'updated_by' => $loggerUserId,
                    ]);
                }
            }
        }

        if (array_key_exists('coupon_items', $postData) && is_array($postData['coupon_items'])) {
            $deletedItems = CouponEntityMap::whereIn('coupon_id', [$couponObj->id])->delete();
            $entityObj = CouponEntity::find((int)$tempRecord['entity_id']);
            if ($entityObj && ($entityObj->code != CouponEntity::COUPON_ENTITY_ALL) && (count($postData['coupon_items']) > 0)) {
                foreach ($postData['coupon_items'] as $itemId) {
                    $itemCreatedObj = CouponEntityMap::create([
                        'coupon_id' => $couponObj->id,
                        'entity_id' => (int)$tempRecord['entity_id'],
                        'target_id' => (int)$itemId,
                        'is_active' => CouponEntityMap::ACTIVE_YES,
                        'created_by' => $loggerUserId,
                        'updated_by' => $loggerUserId,
                    ]);
                }
            }
        }

        return redirect()->route('priceRule.cart.coupons.index')->with('success', 'The Coupon is updated successfully!');

    }

    /**
     * Display a listing of the resource.
     */
    public function destroyCoupon(Request $request)
    {

        $couponId = $request->input('couponId');

        if (!isset($couponId) || is_null($couponId) || !is_numeric($couponId) || ((int)$couponId <= 0)) {
            return response()->json(['message' => 'Invalid Coupon Id'], 40);
        }

        $couponObj = Coupon::find($couponId);
        if (!$couponObj) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $couponObj->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);

    }

    /**
     * Display a listing of the resource.
     */
    public function bulkDeleteCoupon(Request $request)
    {

        try {

            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:product_attributes,id',
            ]);

            Coupon::whereIn('id', $request->ids)->each(function ($couponObj) {
                $couponObj->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Selected coupons have been deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting coupons.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Download the sample CSV file for coupons.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadSampleCsv()
    {
        $path = public_path('uploads/coupons-sample-data.csv');
        return response()->download($path, 'coupons-sample-data.csv');
    }

    /**
     * Display a listing of the resource.
     */
    public function couponModeList(Request $request)
    {

        $pageTitle = 'Coupon Modes';
        $pageSubTitle = 'Coupon Modes';

        $serviceHelper = new CartRuleServiceHelper();

        $todayDate = date('Y-m-d');

        return view('pricerulemanagement::coupon-modes.list', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
            'serviceHelper'
        ));

    }

    /**
     * Display a listing of the resource.
     */
    public function searchCouponModeByFilters(Request $request)
    {

        set_time_limit(600);

        $serviceHelper = new CartRuleServiceHelper();

        $availableActions = ['datatable'];
        $methodAction = (
            $request->has('filter_action')
            && (trim($request->input('filter_action')) != '')
            && in_array(trim($request->input('filter_action')), $availableActions)
        ) ? trim($request->input('filter_action')) : 'datatable';

        $dtDraw = (
            $request->has('draw')
            && (trim($request->input('draw')) != '')
        ) ? (int)trim($request->input('draw')) : 1;

        $dtStart = (
            $request->has('start')
            && (trim($request->input('start')) != '')
        ) ? (int)trim($request->input('start')) : 0;

        $dtPageLength = (
            $request->has('length')
            && (trim($request->input('length')) != '')
        ) ? (int)trim($request->input('length')) : 10;

        $sortOrder = (
            $request->has('order')
            && is_array($request->input('order'))
            && (count($request->input('order')) > 0)
        ) ? $request->input('order') : [];

        $columnDefs = (
            $request->has('columnsDef')
            && is_array($request->input('columnsDef'))
            && (count($request->input('columnsDef')) > 0)
        ) ? $request->input('columnsDef') : [];

        $searchTerm = (
            $request->has('search_term_filter')
            && (trim($request->input('search_term_filter')) != '')
        ) ? trim($request->input('search_term_filter')) : '';

        $dtSortColumn = '';
        $dtSortDir = '';
        if (count($sortOrder) > 0) {
            $dtSortOrder = $sortOrder[0];
            if (array_key_exists('column', $dtSortOrder)) {
                $dtSortColumn = ((count($columnDefs) > 0) && array_key_exists($dtSortOrder['column'], $columnDefs)) ? $columnDefs[$dtSortOrder['column']] : '';
            }
            if (array_key_exists('dir', $dtSortOrder) && ($dtSortColumn != '')) {
                $allowedDirs = ['asc', 'desc'];
                $dtSortDir = in_array(strtolower($dtSortOrder['dir']), $allowedDirs) ? strtolower($dtSortOrder['dir']) : '';
            }
        }

        $returnData = [];
        if ($methodAction == 'datatable') {

            $filteredCouponModesRaw = $serviceHelper->getCouponModesFilteredData($searchTerm, $dtStart, $dtPageLength, $dtSortColumn, $dtSortDir);
            if (!is_null($filteredCouponModesRaw)) {

                $totalCount = $filteredCouponModesRaw['totalCount'];
                $filteredCouponModes = $filteredCouponModesRaw['filteredData'];

                if ($filteredCouponModes->count() > 0) {

                    $filteredCouponModeData = [];
                    $filteredCouponModeArrayData = json_decode($filteredCouponModes->toJson(), true);
                    $totalRec = 0;

                    foreach ($filteredCouponModeArrayData as $modeArrayEl) {

                        $modeUpdatedAt = $serviceHelper->getFormattedTime($modeArrayEl['updatedAt'], 'F d, Y, h:i:s A');

                        $isActive = "";
                        if ($modeArrayEl['isActive'] == CouponMode::ACTIVE_YES){
                            $isActive = '<span class="label label-lg font-weight-bold label-light-success label-inline">Yes</span>';
                        } else {
                            $isActive = '<span class="label label-lg font-weight-bold label-light-danger label-inline">No</span>';
                        }

                        $tempRecord = [
                            'modeId' => $modeArrayEl['modeId'],
                            'modeCode' => $modeArrayEl['modeCode'],
                            'modeName' => $modeArrayEl['modeName'],
                            'modeSortOrder' => $modeArrayEl['modeSortOrder'],
                            'updatedBy' => $modeArrayEl['updatedBy'],
                            'updatedAt' => $modeUpdatedAt,
                            'isActive' => $isActive,
                        ];

                        $filteredCouponModeData[] = $tempRecord;
                        $totalRec++;

                    }

                    $returnData = [
                        'draw' => $dtDraw,
                        'recordsTotal' => $totalCount,
                        'recordsFiltered' => $totalCount,
                        'data' => $filteredCouponModeData
                    ];

                } else {
                    $returnData = [
                        'draw' => $dtDraw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => []
                    ];
                }

            } else {
                $returnData = [
                    'draw' => $dtDraw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ];
            }

        }

        return response()->json($returnData, 200);

    }

    /**
     * Display a listing of the resource.
     */
    public function importCouponMode(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                "coupon_mode_import_file" => ['required', 'file', 'mimes:csv,txt'],
            ], [
                "coupon_mode_import_file.required" => "The Import CSV file is required",
                "coupon_mode_import_file.file" => "The Import field expects a file.",
                "coupon_mode_import_file.mimes" => "The Import file should be a CSV file.",
            ]);
        if ($validator->fails()) {
            $validatorErrArr = $validator->errors()->all();
            return back()
                ->withErrors($validator);
        }

        try {

            $loggedUser = Auth::user();
            $processUserId = $loggedUser->id;

            $importFile = $request->file('coupon_mode_import_file');
            $reader = Reader::createFromFileObject($importFile->openFile());
            $reader->setHeaderOffset(0);
            $headers = $reader->getHeader();
            $records = $reader->getRecords();

            $requiredHeaders = [
                'CouponModeCode', 'CouponMode', 'SortOrder', 'Status'
            ];

            $leftOutFields = array_diff($requiredHeaders, $headers);
            if (count($leftOutFields) > 0) {
                return back()
                    ->with('error', "Following Headers are not found: '" . implode("', '", $leftOutFields) . "'");
            }

            if (count($reader) == 0) {
                return back()
                    ->with('error', 'No data found in the CSV file!');
            }

            $serviceHelper = new CartRuleServiceHelper();

            $csvData = [];
            foreach ($records as $record) {
                $csvData[] = $record;
            }

            $importResult = $serviceHelper->processCouponModeImport($csvData, $processUserId);
            if ($importResult['success'] === false) {
                return back()
                    ->with('error', 'The Coupon Modes could not import! ' . $importResult['message']);
            }

            return redirect()->route('priceRule.cart.couponModes.index')->with('success', 'The Coupon Modes are imported successfully! ' . $importResult['message']);

        } catch (Exception $e) {
            return back()
                ->with('error', 'The Coupon Modes could not import! ' . $e->getMessage());
        }

    }

    /**
     * Display a listing of the resource.
     */
    public function exportCouponMode(Request $request)
    {

        $serviceHelper = new CartRuleServiceHelper();

        $exportResult = $serviceHelper->processCouponModeExport();

        $modeArrayData = [];
        if ($exportResult->count() > 0) {
            $modeArrayData = json_decode($exportResult->toJson(), true);
        }

        if (count($modeArrayData) == 0) {
            return back()
                ->with('error', 'The Coupon Modes could not export! No data to export!');
        }

        $fileName = 'coupon-modes-' . date('Y-m-d h-i-sa') . '.csv';
        $headers = [
            "Content-Encoding"    => "none",
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Content-Description" => "File Transfer",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $headingColumns = array_keys($modeArrayData[0]);

        $contentCallback = function() use($modeArrayData, $headingColumns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headingColumns);
            if(count($modeArrayData) > 0) {
                foreach($modeArrayData as $row) {
                    $tempRow = $row;
                    $tempRow['Status'] = ($row['Status'] == CouponMode::ACTIVE_YES) ? 'TRUE' : 'FALSE';
                    fputcsv($file, $tempRow);
                }
            }
            fclose($file);
        };

        return response()->stream($contentCallback, 200, $headers);

    }

    /**
     * Display a listing of the resource.
     */
    public function couponTypeList(Request $request)
    {

        $pageTitle = 'Coupon Types';
        $pageSubTitle = 'Coupon Types';

        $serviceHelper = new CartRuleServiceHelper();

        $todayDate = date('Y-m-d');

        return view('pricerulemanagement::coupon-types.list', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
            'serviceHelper'
        ));

    }

    /**
     * Display a listing of the resource.
     */
    public function searchCouponTypeByFilters(Request $request)
    {

        set_time_limit(600);

        $serviceHelper = new CartRuleServiceHelper();

        $availableActions = ['datatable'];
        $methodAction = (
            $request->has('filter_action')
            && (trim($request->input('filter_action')) != '')
            && in_array(trim($request->input('filter_action')), $availableActions)
        ) ? trim($request->input('filter_action')) : 'datatable';

        $dtDraw = (
            $request->has('draw')
            && (trim($request->input('draw')) != '')
        ) ? (int)trim($request->input('draw')) : 1;

        $dtStart = (
            $request->has('start')
            && (trim($request->input('start')) != '')
        ) ? (int)trim($request->input('start')) : 0;

        $dtPageLength = (
            $request->has('length')
            && (trim($request->input('length')) != '')
        ) ? (int)trim($request->input('length')) : 10;

        $sortOrder = (
            $request->has('order')
            && is_array($request->input('order'))
            && (count($request->input('order')) > 0)
        ) ? $request->input('order') : [];

        $columnDefs = (
            $request->has('columnsDef')
            && is_array($request->input('columnsDef'))
            && (count($request->input('columnsDef')) > 0)
        ) ? $request->input('columnsDef') : [];

        $searchTerm = (
            $request->has('search_term_filter')
            && (trim($request->input('search_term_filter')) != '')
        ) ? trim($request->input('search_term_filter')) : '';

        $dtSortColumn = '';
        $dtSortDir = '';
        if (count($sortOrder) > 0) {
            $dtSortOrder = $sortOrder[0];
            if (array_key_exists('column', $dtSortOrder)) {
                $dtSortColumn = ((count($columnDefs) > 0) && array_key_exists($dtSortOrder['column'], $columnDefs)) ? $columnDefs[$dtSortOrder['column']] : '';
            }
            if (array_key_exists('dir', $dtSortOrder) && ($dtSortColumn != '')) {
                $allowedDirs = ['asc', 'desc'];
                $dtSortDir = in_array(strtolower($dtSortOrder['dir']), $allowedDirs) ? strtolower($dtSortOrder['dir']) : '';
            }
        }

        $returnData = [];
        if ($methodAction == 'datatable') {

            $filteredCouponTypesRaw = $serviceHelper->getCouponTypesFilteredData($searchTerm, $dtStart, $dtPageLength, $dtSortColumn, $dtSortDir);
            if (!is_null($filteredCouponTypesRaw)) {

                $totalCount = $filteredCouponTypesRaw['totalCount'];
                $filteredCouponTypes = $filteredCouponTypesRaw['filteredData'];

                if ($filteredCouponTypes->count() > 0) {

                    $filteredCouponTypeData = [];
                    $filteredCouponTypeArrayData = json_decode($filteredCouponTypes->toJson(), true);
                    $totalRec = 0;

                    foreach ($filteredCouponTypeArrayData as $typeArrayEl) {

                        $typeUpdatedAt = $serviceHelper->getFormattedTime($typeArrayEl['updatedAt'], 'F d, Y, h:i:s A');

                        $isActive = "";
                        if ($typeArrayEl['isActive'] == CouponType::ACTIVE_YES){
                            $isActive = '<span class="label label-lg font-weight-bold label-light-success label-inline">Yes</span>';
                        } else {
                            $isActive = '<span class="label label-lg font-weight-bold label-light-danger label-inline">No</span>';
                        }

                        $tempRecord = [
                            'typeId' => $typeArrayEl['typeId'],
                            'typeCode' => $typeArrayEl['typeCode'],
                            'typeName' => $typeArrayEl['typeName'],
                            'typeSortOrder' => $typeArrayEl['typeSortOrder'],
                            'updatedBy' => $typeArrayEl['updatedBy'],
                            'updatedAt' => $typeUpdatedAt,
                            'isActive' => $isActive,
                        ];

                        $filteredCouponTypeData[] = $tempRecord;
                        $totalRec++;

                    }

                    $returnData = [
                        'draw' => $dtDraw,
                        'recordsTotal' => $totalCount,
                        'recordsFiltered' => $totalCount,
                        'data' => $filteredCouponTypeData
                    ];

                } else {
                    $returnData = [
                        'draw' => $dtDraw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => []
                    ];
                }

            } else {
                $returnData = [
                    'draw' => $dtDraw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ];
            }

        }

        return response()->json($returnData, 200);

    }

    /**
     * Display a listing of the resource.
     */
    public function importCouponType(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                "coupon_type_import_file" => ['required', 'file', 'mimes:csv,txt'],
            ], [
                "coupon_type_import_file.required" => "The Import CSV file is required",
                "coupon_type_import_file.file" => "The Import field expects a file.",
                "coupon_type_import_file.mimes" => "The Import file should be a CSV file.",
            ]);
        if ($validator->fails()) {
            $validatorErrArr = $validator->errors()->all();
            return back()
                ->withErrors($validator);
        }

        try {

            $loggedUser = Auth::user();
            $processUserId = $loggedUser->id;

            $importFile = $request->file('coupon_type_import_file');
            $reader = Reader::createFromFileObject($importFile->openFile());
            $reader->setHeaderOffset(0);
            $headers = $reader->getHeader();
            $records = $reader->getRecords();

            $requiredHeaders = [
                'CouponTypeCode', 'CouponType', 'SortOrder', 'Status'
            ];

            $leftOutFields = array_diff($requiredHeaders, $headers);
            if (count($leftOutFields) > 0) {
                return back()
                    ->with('error', "Following Headers are not found: '" . implode("', '", $leftOutFields) . "'");
            }

            if (count($reader) == 0) {
                return back()
                    ->with('error', 'No data found in the CSV file!');
            }

            $serviceHelper = new CartRuleServiceHelper();

            $csvData = [];
            foreach ($records as $record) {
                $csvData[] = $record;
            }

            $importResult = $serviceHelper->processCouponTypeImport($csvData, $processUserId);
            if ($importResult['success'] === false) {
                return back()
                    ->with('error', 'The Coupon Types could not import! ' . $importResult['message']);
            }

            return redirect()->route('priceRule.cart.couponTypes.index')->with('success', 'The Coupon Types are imported successfully! ' . $importResult['message']);

        } catch (Exception $e) {
            return back()
                ->with('error', 'The Coupon Types could not import! ' . $e->getMessage());
        }

    }

    /**
     * Display a listing of the resource.
     */
    public function exportCouponType(Request $request)
    {

        $serviceHelper = new CartRuleServiceHelper();

        $exportResult = $serviceHelper->processCouponTypeExport();

        $typeArrayData = [];
        if ($exportResult->count() > 0) {
            $typeArrayData = json_decode($exportResult->toJson(), true);
        }

        if (count($typeArrayData) == 0) {
            return back()
                ->with('error', 'The Coupon Types could not export! No data to export!');
        }

        $fileName = 'coupon-types-' . date('Y-m-d h-i-sa') . '.csv';
        $headers = [
            "Content-Encoding"    => "none",
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Content-Description" => "File Transfer",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $headingColumns = array_keys($typeArrayData[0]);

        $contentCallback = function() use($typeArrayData, $headingColumns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headingColumns);
            if(count($typeArrayData) > 0) {
                foreach($typeArrayData as $row) {
                    $tempRow = $row;
                    $tempRow['Status'] = ($row['Status'] == CouponType::ACTIVE_YES) ? 'TRUE' : 'FALSE';
                    fputcsv($file, $tempRow);
                }
            }
            fclose($file);
        };

        return response()->stream($contentCallback, 200, $headers);

    }

    /**
     * Display a listing of the resource.
     */
    public function couponEntityList(Request $request)
    {

        $pageTitle = 'Coupon Entities';
        $pageSubTitle = 'Coupon Entities';

        $serviceHelper = new CartRuleServiceHelper();

        $todayDate = date('Y-m-d');

        return view('pricerulemanagement::coupon-entities.list', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
            'serviceHelper'
        ));

    }

    /**
     * Display a listing of the resource.
     */
    public function searchCouponEntityByFilters(Request $request)
    {

        set_time_limit(600);

        $serviceHelper = new CartRuleServiceHelper();

        $availableActions = ['datatable'];
        $methodAction = (
            $request->has('filter_action')
            && (trim($request->input('filter_action')) != '')
            && in_array(trim($request->input('filter_action')), $availableActions)
        ) ? trim($request->input('filter_action')) : 'datatable';

        $dtDraw = (
            $request->has('draw')
            && (trim($request->input('draw')) != '')
        ) ? (int)trim($request->input('draw')) : 1;

        $dtStart = (
            $request->has('start')
            && (trim($request->input('start')) != '')
        ) ? (int)trim($request->input('start')) : 0;

        $dtPageLength = (
            $request->has('length')
            && (trim($request->input('length')) != '')
        ) ? (int)trim($request->input('length')) : 10;

        $sortOrder = (
            $request->has('order')
            && is_array($request->input('order'))
            && (count($request->input('order')) > 0)
        ) ? $request->input('order') : [];

        $columnDefs = (
            $request->has('columnsDef')
            && is_array($request->input('columnsDef'))
            && (count($request->input('columnsDef')) > 0)
        ) ? $request->input('columnsDef') : [];

        $searchTerm = (
            $request->has('search_term_filter')
            && (trim($request->input('search_term_filter')) != '')
        ) ? trim($request->input('search_term_filter')) : '';

        $dtSortColumn = '';
        $dtSortDir = '';
        if (count($sortOrder) > 0) {
            $dtSortOrder = $sortOrder[0];
            if (array_key_exists('column', $dtSortOrder)) {
                $dtSortColumn = ((count($columnDefs) > 0) && array_key_exists($dtSortOrder['column'], $columnDefs)) ? $columnDefs[$dtSortOrder['column']] : '';
            }
            if (array_key_exists('dir', $dtSortOrder) && ($dtSortColumn != '')) {
                $allowedDirs = ['asc', 'desc'];
                $dtSortDir = in_array(strtolower($dtSortOrder['dir']), $allowedDirs) ? strtolower($dtSortOrder['dir']) : '';
            }
        }

        $returnData = [];
        if ($methodAction == 'datatable') {

            $filteredCouponApplicationsRaw = $serviceHelper->getCouponEntitiesFilteredData($searchTerm, $dtStart, $dtPageLength, $dtSortColumn, $dtSortDir);
            if (!is_null($filteredCouponApplicationsRaw)) {

                $totalCount = $filteredCouponApplicationsRaw['totalCount'];
                $filteredCouponApplications = $filteredCouponApplicationsRaw['filteredData'];

                if ($filteredCouponApplications->count() > 0) {

                    $filteredCouponApplicationData = [];
                    $filteredCouponApplicationArrayData = json_decode($filteredCouponApplications->toJson(), true);
                    $totalRec = 0;

                    foreach ($filteredCouponApplicationArrayData as $appArrayEl) {

                        $appUpdatedAt = $serviceHelper->getFormattedTime($appArrayEl['updatedAt'], 'F d, Y, h:i:s A');

                        $isActive = "";
                        if ($appArrayEl['isActive'] == CouponEntity::ACTIVE_YES){
                            $isActive = '<span class="label label-lg font-weight-bold label-light-success label-inline">Yes</span>';
                        } else {
                            $isActive = '<span class="label label-lg font-weight-bold label-light-danger label-inline">No</span>';
                        }

                        $tempRecord = [
                            'appId' => $appArrayEl['appId'],
                            'appCode' => $appArrayEl['appCode'],
                            'appName' => $appArrayEl['appName'],
                            'appSortOrder' => $appArrayEl['appSortOrder'],
                            'updatedBy' => $appArrayEl['updatedBy'],
                            'updatedAt' => $appUpdatedAt,
                            'isActive' => $isActive,
                        ];

                        $filteredCouponApplicationData[] = $tempRecord;
                        $totalRec++;

                    }

                    $returnData = [
                        'draw' => $dtDraw,
                        'recordsTotal' => $totalCount,
                        'recordsFiltered' => $totalCount,
                        'data' => $filteredCouponApplicationData
                    ];

                } else {
                    $returnData = [
                        'draw' => $dtDraw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => []
                    ];
                }

            } else {
                $returnData = [
                    'draw' => $dtDraw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ];
            }

        }

        return response()->json($returnData, 200);

    }

    /**
     * Display a listing of the resource.
     */
    public function importCouponEntity(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                "coupon_entity_import_file" => ['required', 'file', 'mimes:csv,txt'],
            ], [
                "coupon_entity_import_file.required" => "The Import CSV file is required",
                "coupon_entity_import_file.file" => "The Import field expects a file.",
                "coupon_entity_import_file.mimes" => "The Import file should be a CSV file.",
            ]);
        if ($validator->fails()) {
            $validatorErrArr = $validator->errors()->all();
            return back()
                ->withErrors($validator);
        }

        try {

            $loggedUser = Auth::user();
            $processUserId = $loggedUser->id;

            $importFile = $request->file('coupon_entity_import_file');
            $reader = Reader::createFromFileObject($importFile->openFile());
            $reader->setHeaderOffset(0);
            $headers = $reader->getHeader();
            $records = $reader->getRecords();

            $requiredHeaders = [
                'CouponEntityCode', 'CouponEntity', 'SortOrder', 'Status'
            ];

            $leftOutFields = array_diff($requiredHeaders, $headers);
            if (count($leftOutFields) > 0) {
                return back()
                    ->with('error', "Following Headers are not found: '" . implode("', '", $leftOutFields) . "'");
            }

            if (count($reader) == 0) {
                return back()
                    ->with('error', 'No data found in the CSV file!');
            }

            $serviceHelper = new CartRuleServiceHelper();

            $csvData = [];
            foreach ($records as $record) {
                $csvData[] = $record;
            }

            $importResult = $serviceHelper->processCouponEntityImport($csvData, $processUserId);
            if ($importResult['success'] === false) {
                return back()
                    ->with('error', 'The Coupon Entities could not import! ' . $importResult['message']);
            }

            return redirect()->route('priceRule.cart.couponEntities.index')->with('success', 'The Coupon Entities are imported successfully! ' . $importResult['message']);

        } catch (Exception $e) {
            return back()
                ->with('error', 'The Coupon Entities could not import! ' . $e->getMessage());
        }

    }

    /**
     * Display a listing of the resource.
     */
    public function exportCouponEntity(Request $request)
    {

        $serviceHelper = new CartRuleServiceHelper();

        $exportResult = $serviceHelper->processCouponEntityExport();

        $appArrayData = [];
        if ($exportResult->count() > 0) {
            $appArrayData = json_decode($exportResult->toJson(), true);
        }

        if (count($appArrayData) == 0) {
            return back()
                ->with('error', 'The Coupon Entities could not export! No data to export!');
        }

        $fileName = 'coupon-entities-' . date('Y-m-d h-i-sa') . '.csv';
        $headers = [
            "Content-Encoding"    => "none",
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Content-Description" => "File Transfer",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $headingColumns = array_keys($appArrayData[0]);

        $contentCallback = function() use($appArrayData, $headingColumns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headingColumns);
            if(count($appArrayData) > 0) {
                foreach($appArrayData as $row) {
                    $tempRow = $row;
                    $tempRow['Status'] = ($row['Status'] == CouponEntity::ACTIVE_YES) ? 'TRUE' : 'FALSE';
                    fputcsv($file, $tempRow);
                }
            }
            fclose($file);
        };

        return response()->stream($contentCallback, 200, $headers);

    }

    /**
     * Store a newly created coupon mode in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponStore(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupon_modes,code',
            'name' => 'required|string',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean', // Add validation for is_active
        ]);

        CouponMode::create([
            'code' => $request->code,
            'name' => $request->name,
            'sort_order' => $request->sort_order,
            'is_active' => $request->is_active,  // Use the value from the request
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => 'Coupon Mode created successfully!']);
    }

    /**
     * Show the form for editing the specified coupon mode.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function couponEdit($id)
    {
        $couponMode = CouponMode::findOrFail($id);
        return view('pricerulemanagement::coupon-modes.edit', compact('couponMode'));
    }

    /**
     * Update the specified coupon mode in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponUpdate(Request $request, $id)
    {
        $request->validate([
            'code' => "required|string|unique:coupon_modes,code,{$id}",
            'name' => 'required|string',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        $couponMode = CouponMode::findOrFail($id);
        $couponMode->update([
            'code' => $request->code,
            'name' => $request->name,
            'sort_order' => $request->sort_order,
            'is_active' => $request->is_active,
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => 'Coupon Mode updated successfully!']);
    }

    /**
     * Remove the specified coupon mode from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponDestroy($id)
    {
        CouponMode::findOrFail($id)->delete();
        return response()->json(['success' => 'Coupon Mode deleted successfully!']);
    }

    /**
     * Show the form for creating a new coupon mode.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function couponCreate(Request $request)
    {

        $pageTitle = 'Coupons';
        $pageSubTitle = 'Coupons';


        $todayDate = date('Y-m-d');


        return view('pricerulemanagement::coupon-modes.create', [
            'canEditCouponMode' => auth()->user()->can('edit_coupon_modes'),
            'canDeleteCouponMode' => auth()->user()->can('delete_coupon_modes'),
            'pageTitle',
            'pageSubTitle',
            'todayDate',
        ]);
    }

    /**
     * Store a newly created coupon type in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponTypeStore(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupon_modes,code',
            'name' => 'required|string',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean', // Add validation for is_active
        ]);

        CouponType::create([
            'code' => $request->code,
            'name' => $request->name,
            'sort_order' => $request->sort_order,
            'is_active' => $request->is_active,  // Use the value from the request
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => 'Coupon Mode created successfully!']);

    }

    /**
     * Show the form for editing the specified coupon type.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function couponTypeEdit($id)
    {
        $couponMode = CouponType::findOrFail($id);
        return view('pricerulemanagement::coupon-types.edit', compact('couponMode'));
    }

    /**
     * Update the specified coupon type in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponTypeUpdate(Request $request, $id)
    {
        $request->validate([
            'code' => "required|string|unique:coupon_modes,code,{$id}",
            'name' => 'required|string',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        $couponMode = CouponType::findOrFail($id);
        $couponMode->update([
            'code' => $request->code,
            'name' => $request->name,
            'sort_order' => $request->sort_order,
            'is_active' => $request->is_active,
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => 'Coupon Mode updated successfully!']);
    }

    /**
     * Remove the specified coupon type from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponTypeDestroy($id)
    {
        CouponType::findOrFail($id)->delete();
        return response()->json(['success' => 'Coupon Mode deleted successfully!']);
    }

    /**
     * Show the form for creating a new coupon type.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function couponTypeCreate(Request $request)
    {

        $pageTitle = 'Coupons';
        $pageSubTitle = 'Coupons';


        $todayDate = date('Y-m-d');

        return view('pricerulemanagement::coupon-types.create', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
        ));

    }

}
