<?php

namespace Modules\PaymentManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use Modules\PaymentManagement\Models\PaymentHistory;
use Modules\PaymentManagement\Models\PaymentMethod;
use Modules\PaymentManagement\Services\StripeApiService;
use Modules\SubscriptionManagement\Models\Subscription;
use Stripe\Webhook as StripeWebhook;
use Modules\Customer\Models\Customer;

class StripeGatewayController extends Controller
{
    /**
     * Initiates a Stripe payment session for a subscription via mobile.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mobilePay(Request $request) {

        try {

            $rules = [
                'subscription_id'   => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->errorResponse(__($validator->errors()->first()), 422);
            }

            $user = Auth::user();
            $subscriptionCurrencyCode = 'USD';
            $subscriptionCurrencyDecimal = 2;
            $subscriptionCurrencyDecimalFactor = pow(10, $subscriptionCurrencyDecimal);

            $subscriptionId = (int)$request->subscription_id;
            $subscriptionObj = Subscription::find($subscriptionId);
            if (!$subscriptionObj) {
                return $this->errorResponse("Invalid Subscription data", 400);
            }

            $subscriptionObj->details;
            $subscriptionObj->options;

            $subscriptionName = $subscriptionObj->name;
            $subscriptionAmount = (double)$subscriptionObj->price ?? 0;
            $subscriptionAmountFactored = floor($subscriptionAmount * $subscriptionCurrencyDecimalFactor);
            $subscriptionInterval = (int)$subscriptionObj->duration ?? 0;
            $subscriptionIntervalUnit = 'day';
            $subscriptionCheckFlag = (int)$subscriptionObj->is_subscription_plan ?? 0;

            $stripeService = new StripeApiService();

            $customerName = trim($user->first_name) ?? '';
            $customerName .= ((trim($customerName) != '') ? ' ' : '') . trim($user->last_name);
            $customerEmail = $user->email;
            $customerStripeObj = null;
            if (!is_null($customerEmail) && is_string($customerEmail) && (trim($customerEmail) != '') && (filter_var(trim($customerEmail), FILTER_VALIDATE_EMAIL) !== false)) {
                $customerStripeObj = $stripeService->searchCustomerByMailId($customerEmail);
                if (is_null($customerStripeObj)) {
                    $checkoutSessionCustomerData = [
                        'email' => $customerEmail,
                    ];
                    if (trim($customerName) != '') {
                        $checkoutSessionCustomerData['name'] = $customerName;
                    }
                    $customerStripeObj = $stripeService->createStripeCustomerObject($checkoutSessionCustomerData);
                }
            }

            $subscriptionStripeId = $subscriptionObj->stripe_subscription_id;
            if (is_null($subscriptionStripeId) || ($subscriptionStripeId == '-')) {
                if (($subscriptionAmountFactored > 0) && ($subscriptionInterval > 0)) {
                    $priceObjData = [
                        'currency' => strtolower($subscriptionCurrencyCode),
                        'unit_amount' => $subscriptionAmountFactored,
                        'product_data' => [
                            'name' => $subscriptionName
                        ],
                    ];
                    if ($subscriptionCheckFlag > 0) {
                        $priceObjData['recurring'] = [
                            'interval' => $subscriptionIntervalUnit,
                            'interval_count' => $subscriptionInterval
                        ];
                    }
                    $stripePriceObj = $stripeService->createPriceObject($priceObjData);
                    if (!is_null($stripePriceObj)) {
                        $subscriptionStripeId = $stripePriceObj->id;
                        $subscriptionObj->fill(['stripe_subscription_id' => $subscriptionStripeId])->save();
                        $subscriptionObj->refresh();
                    }
                }
            }

            if (is_null($subscriptionStripeId) || ($subscriptionStripeId == '-')) {
                return $this->errorResponse("Could not process Stripe payment gateway", 400);
            }

            $successCallbackURLString =  "/stripe/frontend-success-callback?session_id={CHECKOUT_SESSION_ID}&amount=" . $subscriptionAmount . "&status=200&gateway=stripe&subscription_id=" . $request->subscription_id;
            $failureCallbackURLString = "/stripe/frontend-failure-callback?session_id={CHECKOUT_SESSION_ID}&amount=" . $subscriptionAmount . "&status=200&gateway=stripe&subscription_id=" . $request->subscription_id;
            $webhookURLString = '/stripe/frontend-webhook';

            $successCallbackURL = url($successCallbackURLString);
            $failureCallbackURL = url($failureCallbackURLString);
            $webhookURL = url($webhookURLString);

            $checkoutSessionData = [
                'success_url' => $successCallbackURL,
                'cancel_url' => $failureCallbackURL,
                'line_items' => [
                    [
                        'price' => $subscriptionStripeId,
                        'quantity' => 1,
                    ],
                ],
                'mode' => ($subscriptionCheckFlag > 0) ? 'subscription' : 'payment',
                'metadata' => [
                    'given_amount' => $subscriptionAmount,
                    'given_subscription_id' => $request->subscription_id,
                    'given_customer_id' => $user->id,
                    'given_history_type' => 'initial',
                ],
            ];
            if (!is_null($customerStripeObj)) {
                $checkoutSessionData['customer'] = $customerStripeObj->id;
            }
            $stripeCheckoutObj = $stripeService->createCheckoutSession($checkoutSessionData);
            if (is_null($stripeCheckoutObj)) {
                return $this->errorResponse("Could not process Stripe payment gateway", 400);
            }

            return $this->successResponse($stripeCheckoutObj->url, 'The Payment URL generated successfully!');

        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
        }

    }

    /**
     * Handles the Stripe payment success callback for mobile subscriptions.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|null
     */
    public function mobileSuccessCallback(Request $request) {

        try {

            $paymentTrackId = $request->session_id;
            $givenAmount = (double)$request->amount;
            $subscriptionId = (int)$request->subscription_id;

            $contentTypes = $request->getAcceptableContentTypes();
            Log::info("Acceptable Content Types : ");
            Log::info($contentTypes);

            Log::info("Success Callback RequestData : ");
            Log::info($request->all());

            $paymentResultReturnUrl = 'payment/gateway/returnResponse?status=0&gateway=stripe&action=subscription';

            $stripeService = new StripeApiService();

            $paymentCheckResult = $stripeService->checkPaymentStatusBySessionId($paymentTrackId);

            if (is_null($paymentCheckResult)) {
                if ($request->accepts(['text/html'])) {
                    return Redirect::to($paymentResultReturnUrl);
                } elseif ($request->accepts(['application/json'])) {
                    /*return response()->json(['IsSuccess' => 'false', 'Message' => 'Could not process the Payment Verification!','errorFrom' => 'mobileWalletFailureCallback']);*/
                    return $this->errorResponse('Could not process the Payment Verification!', 402);
                }
            }

            $verifyRes = $paymentCheckResult['paid'];
            if ($verifyRes === false) {
                if ($request->accepts(['text/html'])) {
                    return Redirect::to($paymentResultReturnUrl);
                } elseif ($request->accepts(['application/json'])) {
                    /*return response()->json(['IsSuccess' => 'false', 'Message' => $verifyRes['message'],'errorFrom' => 'mobileWalletFailureCallback']);*/
                    return $this->errorResponse('Payment is not processed yet.', 402);
                }
            }

            $subscriptionObj = Subscription::find($subscriptionId);

            $sessionData = $paymentCheckResult['sessionData'];
            $sessionDataArray = $sessionData->toArray();
            $sessionJsonData = json_encode($sessionDataArray);

            Log::info("Success Callback Session Data : ");
            Log::info($sessionJsonData);

            $sessionMetaData = $sessionDataArray['metadata'];

            $stripeCustomerObj = $stripeService->fetchCustomerById($sessionData->customer);
            $serverCustomerObj = Customer::firstWhere('email', $stripeCustomerObj->email);

            $stripeMethodObj = PaymentMethod::firstWhere('code', PaymentMethod::PAYMENT_METHOD_CODE_STRIPE);
            $paymentHistoryObjArray = [
                'amount' => $givenAmount,
                'currency_code' => strtoupper($sessionData->currency),
                'transaction_id' => $sessionData->id,
                'payment_detail' => $sessionJsonData,
                'status' => ($verifyRes === false) ? PaymentHistory::PAYMENT_STATUS_FAILURE : PaymentHistory::PAYMENT_STATUS_SUCCESS,
            ];
            $paymentHistoryObj = PaymentHistory::updateOrCreate([
                'payment_ref' => $sessionData->id,
                'customer_id' => $serverCustomerObj ? $serverCustomerObj->id : null,
                'subscription_id' => $subscriptionObj ? $subscriptionObj->id : null,
                'payment_method_id' => $stripeMethodObj ? $stripeMethodObj->id : null,
                'date' => date('Y-m-d'),
                'type' => $sessionMetaData['given_history_type']
            ], $paymentHistoryObjArray);

            $response = ['IsSuccess' => true, 'Message' => 'Plan subscription paid successfully!', 'Data' => $sessionDataArray];

            if ($request->accepts(['text/html'])) {
                Log::info("text/html");
                $paymentResultReturnUrl = 'payment/gateway/returnResponse?status=200&gateway=stripe&action=subscription&transaction_id=' . $sessionData->id .'&result=CAPTURED';
                return Redirect::to($paymentResultReturnUrl);
            } elseif ($request->accepts(['application/json'])) {
                Log::info("application/json");
                return response()->json([
                    'success' => true,
                    'message' => 'Plan subscription paid successfully!',
                    'results' => $response
                ], 201);
            } else {
                return null;
            }

        } catch (Exception $ex) {
            Log::error('An exception is caught on Stripe mobileSuccessCallback :');
            Log::error($ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            Log::error($ex->getTraceAsString());
            $exMessage = __($ex->getMessage());
            if ($request->accepts(['text/html'])) {
                $paymentResultReturnUrl = 'payment/gateway/returnResponse?status=0&gateway=stripe&action=subscription';
                return Redirect::to($paymentResultReturnUrl);
            } elseif ($request->accepts(['application/json'])) {
                /*return response()->json(['IsSuccess' => 'false', 'Message' => $exMessage,'errorFrom' => 'mobileWalletFailureCallback']);*/
                return $this->errorResponse($exMessage, 402, ['Line' => $ex->getLine(),'File' => $ex->getFile()]);
            } else {
                return null;
            }
        }

    }

    /**
     * Handles the Stripe payment failure callback for mobile subscriptions.
     *
     * Logs the request, checks payment status, updates payment history, and responds
     * based on the request's expected content type (HTML or JSON).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|null
     */
    public function mobileFailureCallback(Request $request) {

        try {

            $paymentTrackId = $request->session_id;
            $givenAmount = (double)$request->amount;
            $subscriptionId = (int)$request->subscription_id;

            $contentTypes = $request->getAcceptableContentTypes();
            Log::info("Acceptable Content Types : ");
            Log::info($contentTypes);

            $paymentResultReturnUrl = 'payment/gateway/returnResponse?status=0&gateway=stripe&action=subscription';

            $stripeService = new StripeApiService();

            $paymentCheckResult = $stripeService->checkPaymentStatusBySessionId($paymentTrackId);

            if (is_null($paymentCheckResult)) {
                if ($request->accepts(['text/html'])) {
                    return Redirect::to($paymentResultReturnUrl);
                } elseif ($request->accepts(['application/json'])) {
                    /*return response()->json(['IsSuccess' => 'false', 'Message' => 'Could not process the Payment Verification!','errorFrom' => 'mobileWalletFailureCallback']);*/
                    return $this->errorResponse('Could not process the Payment Verification!', 402);
                }
            }

            $verifyRes = $paymentCheckResult['paid'];
            if ($verifyRes === false) {
                if ($request->accepts(['text/html'])) {
                    return Redirect::to($paymentResultReturnUrl);
                } elseif ($request->accepts(['application/json'])) {
                    /*return response()->json(['IsSuccess' => 'false', 'Message' => $verifyRes['message'],'errorFrom' => 'mobileWalletFailureCallback']);*/
                    return $this->errorResponse('Payment is not processed yet.', 402);
                }
            }

            $subscriptionObj = Subscription::find($subscriptionId);

            $sessionData = $paymentCheckResult['sessionData'];
            $sessionDataArray = $sessionData->toArray();
            $sessionJsonData = json_encode($sessionDataArray);

            $sessionMetaData = $sessionDataArray['metadata'];

            $stripeCustomerObj = $stripeService->fetchCustomerById($sessionData->customer);
            $serverCustomerObj = Customer::firstWhere('email', $stripeCustomerObj->email);

            $stripeMethodObj = PaymentMethod::firstWhere('code', PaymentMethod::PAYMENT_METHOD_CODE_STRIPE);
            $paymentHistoryObjArray = [
                'amount' => $givenAmount,
                'currency_code' => strtoupper($sessionData->currency),
                'transaction_id' => $sessionData->id,
                'payment_detail' => $sessionJsonData,
                'status' => ($verifyRes === false) ? PaymentHistory::PAYMENT_STATUS_FAILURE : PaymentHistory::PAYMENT_STATUS_SUCCESS,
            ];
            $paymentHistoryObj = PaymentHistory::updateOrCreate([
                'payment_ref' => $sessionData->id,
                'customer_id' => $serverCustomerObj ? $serverCustomerObj->id : null,
                'subscription_id' => $subscriptionObj ? $subscriptionObj->id : null,
                'payment_method_id' => $stripeMethodObj ? $stripeMethodObj->id : null,
                'date' => date('Y-m-d'),
                'type' => $sessionMetaData['given_history_type']
            ], $paymentHistoryObjArray);

            $response = ['IsSuccess' => true, 'Message' => 'Plan subscription paid successfully!', 'Data' => $sessionDataArray];

            if ($request->accepts(['text/html'])) {
                Log::info("text/html");
                $paymentResultReturnUrl = 'payment/gateway/returnResponse?status=200&gateway=stripe&action=subscription&transaction_id=' . $sessionData->id .'&result=CAPTURED';
                return Redirect::to($paymentResultReturnUrl);
            } elseif ($request->accepts(['application/json'])) {
                Log::info("application/json");
                return response()->json([
                    'success' => true,
                    'message' => 'Plan subscription paid successfully!',
                    'results' => $response
                ], 201);
            } else {
                return null;
            }

        } catch (Exception $ex) {
            Log::error('An exception is caught on Stripe mobileFailureCallback :');
            Log::error($ex->getMessage() . ' :: File : ' . $ex->getFile() . ' :: Line : ' . $ex->getLine());
            Log::error($ex->getTraceAsString());
            $exMessage = __($ex->getMessage());
            if ($request->accepts(['text/html'])) {
                $paymentResultReturnUrl = 'payment/gateway/returnResponse?status=0&gateway=stripe&action=subscription';
                return Redirect::to($paymentResultReturnUrl);
            } elseif ($request->accepts(['application/json'])) {
                /*return response()->json(['IsSuccess' => 'false', 'Message' => $exMessage,'errorFrom' => 'mobileWalletFailureCallback']);*/
                return $this->errorResponse($exMessage, 402, ['Line' => $ex->getLine(),'File' => $ex->getFile()]);
            } else {
                return null;
            }
        }

    }

    /**
     * Handle Stripe mobile webhook events.
     *
     * Updates payment history based on success or failure events.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mobileWebhook(Request $request) {

        try {

            $stripeService = new StripeApiService();
            $endpointSecret = $stripeService->getWebhookSecretKey();

            $payload = @file_get_contents('php://input');
            $signatureHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            $event = null;

            $event = StripeWebhook::constructEvent(
                $payload, $signatureHeader, $endpointSecret
            );

            $checkoutSuccessEvents = [
                'checkout.session.completed',
                'checkout.session.async_payment_succeeded',
            ];

            $checkoutFailureEvents = [
                'checkout.session.expired',
                'checkout.session.async_payment_failed',
            ];

            $eventDataObject = null;
            if (in_array($event->type, $checkoutSuccessEvents)) {

                $eventDataObject = $event->data->object;

                $paymentCheckResult = $stripeService->checkPaymentStatusBySessionId($eventDataObject->id);

                if (is_null($paymentCheckResult)) {
                    return $this->errorResponse('Could not process the Payment Verification!', 402);
                }

                $verifyRes = $paymentCheckResult['paid'];
                if ($verifyRes === false) {
                    return $this->errorResponse('Payment is not processed yet.', 402);
                }

                $sessionData = $paymentCheckResult['sessionData'];
                $sessionDataArray = $sessionData->toArray();
                $sessionJsonData = json_encode($sessionDataArray);

                $sessionMetaData = $sessionDataArray['metadata'];

                $givenAmount = (double)$sessionMetaData['given_amount'];
                $subscriptionId = (int)$sessionMetaData['given_subscription_id'];
                $givenCustomerId = (int)$sessionMetaData['given_customer_id'];
                $givenHistoryType = $sessionMetaData['given_history_type'];

                $subscriptionObj = Subscription::find($subscriptionId);
                $serverCustomerObj = Customer::find($givenCustomerId);

                $stripeMethodObj = PaymentMethod::firstWhere('code', PaymentMethod::PAYMENT_METHOD_CODE_STRIPE);
                $paymentHistoryObjArray = [
                    'amount' => $givenAmount,
                    'currency_code' => strtoupper($sessionData->currency),
                    'transaction_id' => $sessionData->id,
                    'payment_detail' => $sessionJsonData,
                    'status' => ($verifyRes === false) ? PaymentHistory::PAYMENT_STATUS_FAILURE : PaymentHistory::PAYMENT_STATUS_SUCCESS,
                ];
                $paymentHistoryObj = PaymentHistory::updateOrCreate([
                    'payment_ref' => $sessionData->id,
                    'customer_id' => $serverCustomerObj ? $serverCustomerObj->id : null,
                    'subscription_id' => $subscriptionObj ? $subscriptionObj->id : null,
                    'payment_method_id' => $stripeMethodObj ? $stripeMethodObj->id : null,
                    'date' => date('Y-m-d'),
                    'type' => $givenHistoryType
                ], $paymentHistoryObjArray);

                $response = ['IsSuccess' => true, 'Message' => 'Plan subscription paid successfully!', 'Data' => $sessionDataArray];
                return response()->json([
                    'success' => true,
                    'message' => 'Plan subscription paid successfully!',
                    'results' => $response
                ], 201);

            } elseif (in_array($event->type, $checkoutFailureEvents)) {

                $eventDataObject = $event->data->object;

            }

            return $this->successResponse($eventDataObject);

        } catch (Exception $exception) {
            Log::error('An exception is caught on StripeController mobileWebhook :');
            Log::error($exception->getMessage() . ' :: File : ' . $exception->getFile() . ' :: Line : ' . $exception->getLine());
            Log::error($exception->getTraceAsString());
            return $this->errorResponse($exception->getMessage(), 400);
        }

    }

    /**
     * Return a standard success JSON response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'Success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return a standard error JSON response.
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
