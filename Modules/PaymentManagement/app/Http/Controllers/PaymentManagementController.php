<?php

namespace Modules\PaymentManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\PaymentManagement\Helpers\PaymentManagementServiceHelper;
use Modules\PaymentManagement\Models\PaymentMethod;
use Modules\SubscriptionManagement\Models\Subscription;

class PaymentManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = 'Payment Methods';
        $pageSubTitle = 'Payment Methods';

        $serviceHelper = new PaymentManagementServiceHelper();

        $todayDate = date('Y-m-d');

        return view('paymentmanagement::payment-methods.list', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
            'serviceHelper'
        ));
    }

    /**
     * Filter and return payment methods based on search criteria for DataTables.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPaymentMethodByFilters(Request $request)
    {

        set_time_limit(600);

        $serviceHelper = new PaymentManagementServiceHelper();

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

            $filteredCouponModesRaw = $serviceHelper->getPaymentMethodsFilteredData($searchTerm, $dtStart, $dtPageLength, $dtSortColumn, $dtSortDir);
            if (!is_null($filteredCouponModesRaw)) {

                $totalCount = $filteredCouponModesRaw['totalCount'];
                $filteredCouponModes = $filteredCouponModesRaw['filteredData'];

                if ($filteredCouponModes->count() > 0) {

                    $filteredCouponModeData = [];
                    $filteredCouponModeArrayData = json_decode($filteredCouponModes->toJson(), true);
                    $totalRec = 0;

                    foreach ($filteredCouponModeArrayData as $modeArrayEl) {

                        $modeUpdatedAt = $serviceHelper->getFormattedTime($modeArrayEl['updatedAt'], 'F d, Y, h:i:s A');

                        $testMode = "";
                        if ($modeArrayEl['methodTestMode'] == PaymentMethod::TEST_MODE_YES){
                            $testMode = '<span class="label label-lg font-weight-bold label-light-success label-inline">' . PaymentMethod::TEST_MODE_STATUS_LIST[$modeArrayEl['methodTestMode']] . '</span>';
                        } else {
                            $testMode = '<span class="label label-lg font-weight-bold label-light-danger label-inline">' . PaymentMethod::TEST_MODE_STATUS_LIST[$modeArrayEl['methodTestMode']] . '</span>';
                        }

                        $onlineStatus = "";
                        if ($modeArrayEl['methodOnlineStatus'] == PaymentMethod::ONLINE_YES){
                            $onlineStatus = '<span class="label label-lg font-weight-bold label-light-success label-inline">' . PaymentMethod::ONLINE_STATUS_LIST[$modeArrayEl['methodOnlineStatus']] . '</span>';
                        } else {
                            $onlineStatus = '<span class="label label-lg font-weight-bold label-light-danger label-inline">' . PaymentMethod::ONLINE_STATUS_LIST[$modeArrayEl['methodOnlineStatus']] . '</span>';
                        }

                        $isActive = "";
                        if ($modeArrayEl['methodActiveStatus'] == PaymentMethod::ACTIVE_YES){
                            $isActive = '<span class="label label-lg font-weight-bold label-light-success label-inline">' . PaymentMethod::ACTIVE_STATUS_LIST[$modeArrayEl['methodActiveStatus']] . '</span>';
                        } else {
                            $isActive = '<span class="label label-lg font-weight-bold label-light-danger label-inline">' . PaymentMethod::ACTIVE_STATUS_LIST[$modeArrayEl['methodActiveStatus']] . '</span>';
                        }

                        $actionsBlock = '<a href="' . route('admin.paymentMethods.edit', ['methodId' => $modeArrayEl['methodId']]) . '" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit" target="_blank"><i class="fa-solid fa-pen text-warning"></i></a>';

                        $tempRecord = [
                            'methodId' => $modeArrayEl['methodId'],
                            'methodCode' => $modeArrayEl['methodCode'],
                            'methodName' => $modeArrayEl['methodName'],
                            'methodSortOrder' => $modeArrayEl['methodSortOrder'],
                            'methodTestMode' => $testMode,
                            'methodOnlineStatus' => $onlineStatus,
                            'methodActiveStatus' => $isActive,
                            'updatedAt' => $modeUpdatedAt,
                            'actions' => $actionsBlock,
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
     * Show the form for editing the specified resource.
     */
    public function edit($methodId)
    {

        if (is_null($methodId) || !is_numeric($methodId) || ((int)$methodId <= 0)) {
            return back()
                ->with('error', 'The Payment Method Id input is invalid!');
        }

        $givenPaymentMethodData = PaymentMethod::find($methodId);
        if(!$givenPaymentMethodData) {
            return back()
                ->with('error', 'The Payment Method does not exist!');
        }

        $pageTitle = 'Payment Methods';
        $pageSubTitle = 'Edit Payment Method : ' . $givenPaymentMethodData->code;

        $serviceHelper = new PaymentManagementServiceHelper();

        $testModeList = PaymentMethod::TEST_MODE_STATUS_LIST;
        $onlineStatusList = PaymentMethod::ONLINE_STATUS_LIST;
        $activeStatusList = PaymentMethod::ACTIVE_STATUS_LIST;

        $todayDate = date('Y-m-d');

        $credentialsList = [
            'stripe' => [
                'publishable_key' => 'Publishable Key',
                'secret_key' => 'Secret Key',
                'webhook_secret_key' => 'Webhook Secret Key',
            ],
        ];

        $credentialValueList = [];
        if (array_key_exists($givenPaymentMethodData->code, $credentialsList)) {
            $targetCredentialList = $credentialsList[$givenPaymentMethodData->code];
            foreach ($targetCredentialList as $mainKey => $mainLabel) {
                $credentialValueList[$mainKey] = [
                    'name' => $mainKey,
                    'label' => $mainLabel,
                    'value' => ''
                ];
            }
            $credentialValues = (isset($givenPaymentMethodData->credentials) && $serviceHelper->checkIsValidJSONString($givenPaymentMethodData->credentials)) ? json_decode($givenPaymentMethodData->credentials, true) : [];
            if (is_array($credentialValues) && (count($credentialValues) > 0)) {
                foreach ($credentialValues as $mainKey => $mainValue) {
                    if (array_key_exists($mainKey, $credentialValueList)) {
                        $credentialValueList[$mainKey]['value'] = $mainValue;
                    }
                }
            }
        }

        return view('paymentmanagement::payment-methods.edit', compact(
            'pageTitle',
            'pageSubTitle',
            'todayDate',
            'givenPaymentMethodData',
            'testModeList',
            'onlineStatusList',
            'activeStatusList',
            'credentialValueList',
            'serviceHelper'
        ));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $methodId)
    {

        if (is_null($methodId) || !is_numeric($methodId) || ((int)$methodId <= 0)) {
            return back()
                ->with('error', 'The Payment Method Id input is invalid!');
        }

        $givenPaymentMethodData = PaymentMethod::find($methodId);
        if(!$givenPaymentMethodData) {
            return back()
                ->with('error', 'The Payment Method does not exist!');
        }

        $loggedUser = Auth::user();
        $loggerUserId = $loggedUser->id;

        $validator = Validator::make($request->all() , [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'sort_order' => ['required', 'numeric', 'integer'],
            'description' => ['nullable', 'string'],
            'test_mode' => ['required', 'numeric', 'integer', Rule::in(array_keys(PaymentMethod::TEST_MODE_STATUS_LIST))],
            'is_online' => ['required', 'numeric', 'integer', Rule::in(array_keys(PaymentMethod::ONLINE_STATUS_LIST))],
            'is_active' => ['required', 'numeric', 'integer', Rule::in(array_keys(PaymentMethod::ACTIVE_STATUS_LIST))],
            'credentials' => ['nullable', 'array'],
        ], [
            'name.required' => 'The Coupon Name should be provided.',
            'name.string' => 'The Coupon Name should be a string value.',
            'name.min' => 'The Coupon Name should be minimum :min characters.',
            'name.max' => 'The Coupon Name should not exceed :max characters.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator);
        }

        $postData = $validator->validated();

        $serviceHelper = new PaymentManagementServiceHelper();

        $tempRecord = [
            'name' => trim($postData['name']),
            'sort_order' => trim($postData['sort_order']),
            'test_mode' => trim($postData['test_mode']),
            'is_online' => trim($postData['is_online']),
            'description' => trim($postData['description']),
            'is_active' => trim($postData['is_active']),
        ];

        if (isset($postData['credentials']) && is_array($postData['credentials']) && (count($postData['credentials']) > 0)) {
            $tempRecord['credentials'] = json_encode($postData['credentials']);
        }

        $givenPaymentMethodData->fill($tempRecord)->save();

        return redirect()->route('admin.paymentMethods.list')->with('success', 'The Payment Method is updated successfully!');

    }

    /**
     * Get all active payment methods for API response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethodsApi(Request $request)
    {

        $paymentOptions = PaymentMethod::where('is_active', PaymentMethod::ACTIVE_YES)->get(['id', 'code', 'name', 'sort_order', 'is_online', 'is_active', 'description']);

        return response()->json([
            'data' => $paymentOptions
        ], 201);

    }

    /**
     * Process payment request via selected gateway.
     *
     * Dynamically calls the gateway-specific method like `postPaymentVia_gatewayName`.
     * Validates presence of gateway name and subscription ID before proceeding.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $gateway
     * @return \Illuminate\Http\JsonResponse
     */
    public function postPaymentProcessingApi(Request $request, $gateway = '')
    {

        if (empty($gateway)) {
            return $this->errorResponse("Invalid Gateway Request", 400);
        }

        $function = 'postPaymentVia_' . $gateway;
        if (!method_exists($this, $function)) {
            return $this->errorResponse("Invalid Gateway Request", 400);
        }

        if (empty($request->subscription_id)) {
            return $this->errorResponse("Invalid Gateway Request", 400);
        }

        $subscriptionId = (int)$request->subscription_id;
        $subscriptionObj = Subscription::find($subscriptionId);
        if (!$subscriptionObj) {
            return $this->errorResponse("Invalid Gateway Request", 400);
        }

        $response = $this->$function($request);
        return $response;

    }

    /**
     * Handle payment processing using Stripe.
     *
     * Instantiates the Stripe gateway controller and delegates the mobile payment handling.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postPaymentVia_stripe(Request $request)
    {
        $gateway = new StripeGatewayController();
        return $gateway->mobilePay($request);
    }

    /**
     * Returns the payment gateway response based on request type.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|null
     */
    public function getGatewayReturnResponse(Request $request)
    {
        $contentTypes = $request->getAcceptableContentTypes();
        Log::info("Acceptable Content Types - View : ");
        Log::info($contentTypes);

        if ($request->accepts(['text/html'])) {
            Log::info("text/html - View");
            return view('paymentmanagement::gatewayReturnResponse');
        } elseif ($request->accepts(['application/json'])) {

            Log::info("application/json - View");

            $requestData = $request->all();
            $returnStatus = null;
            $returnMessage = '';
            $response = [];
            if (((int)$requestData['status'] >= 200) && ((int)$requestData['status'] < 300)) {
                $returnStatus = true;
                $returnMessage = 'The order payment is processed successfully';
                $response = ['IsSuccess' => $returnStatus, 'Message' => $returnMessage, 'Data' => ['transaction_id' => $requestData['transaction_id']],'errorFrom4' => 'mobileSuccessCallback'];
            } else {
                $returnStatus = false;
                $returnMessage = 'Could not process the Payment!';
                $response = ['IsSuccess' => $returnStatus, 'Message' => $returnMessage, 'Data' => [],'errorFrom4' => 'mobileFailureCallback'];
            }

            return response()->json([
                'success' => $returnStatus,
                'message' => $returnMessage,
                'results' => $response
            ], 201);

        }

        return null;

    }

    /**
     * Returns a standardized JSON error response.
     *
     * @param string|null $message
     * @param int $code
     * @param mixed|null $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message = null, $code, $data = null)
    {
        $validCodes = range(100, 599);

        if (!is_int($code) || !in_array($code, $validCodes)) {
            $code = 500;
        }
        return response()->json([
            'status' => 'Error',
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }

}
