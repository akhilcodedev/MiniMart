<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartAddress;
use Modules\Cart\Models\CartItem;
use Modules\Customer\Models\CustomerAddress;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\OrderAddress;
use Modules\OrderManagement\Models\OrderItem;
use Modules\OrderManagement\Models\PaymentStatusOption;
use Modules\OrderManagement\Models\OrderStatus;
use Modules\PaymentMethod\Models\PaymentHistory;
use Modules\PaymentMethod\Models\PaymentMethod;
use Modules\PaymentMethod\Services\StripeApiService;
use Modules\PaymentMethod\Services\TelrActions;
use Modules\PriceRuleManagement\Models\CatalogPriceRule;
use Modules\PriceRuleManagement\Models\Coupon;
use Modules\Products\Models\Product;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use Modules\WebConfigurationManagement\Models\EmailTemplate;
use PDF;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class OrderController extends Controller
{
    /**
     * function to create customer order
     * order and order items details are required amount calculation should done before the function call (API already available to calculate)
     * Email will be added in email queue
     * @param Request $request
     * @return  JsonResponse
     */
    public function createOrder(Request $request)
    {
        try {
            $authUser = Auth::user();
            $request->validate([
                'cart_id' => 'required|exists:carts,id',
                'address_id' => 'required|exists:cart_addresses,id',
                'payment_id' => 'required|exists:payment_methods,id',
            ]);

            $paymentMethod = PaymentMethod::select('id', 'name', 'code')
                ->where('id', $request->payment_id)
                ->first();

            if (!$paymentMethod) {
                return response()->json(['status' => false, 'message' => "Invalid payment method"], 400);
            }

            $userCart = Cart::with(['items', 'addresses'])
                ->where('id', $request->cart_id)
                ->where('customer_id', $authUser->id)
                ->first();

            if (!$userCart) {
                return response()->json(['status' => false, 'message' => "Cart not found for the user"], 404);
            }

            $selectedAddress = $userCart->addresses->where('id', $request->address_id)->first();
            if (!$selectedAddress) {
                return response()->json(['status' => false, 'message' => "Address not found for the cart"], 404);
            }

            $orderStatus = OrderStatus::where('status', 'Pending')->first();
            $paymentStatus = PaymentStatusOption::where('status', 'pending')->first();

            if (!$orderStatus || !$paymentStatus) {
                return response()->json(['status' => false, 'message' => "Order status or payment status not found"], 400);
            }

            $order = Order::create([
                'order_number' => rand(100000, 999999),
                'cart_id' => $userCart->id,
                'customer_id' => $userCart->customer_id,
                'customer_code' => $userCart->customer_code ?? null,
                'first_name' => $selectedAddress->first_name,
                'last_name' => $selectedAddress->last_name,
                'email' => $selectedAddress->email,
                'phone' => $selectedAddress->phone ?? 0000000000,
                'is_active' => $userCart->is_active ?? 1,
                'shipping_method_name' => $userCart->shipping_method_name ?? null,
                'shipping_method_code' => $userCart->shipping_method_code ?? null,
                'shipping_method_status' => $userCart->shipping_method_status ?? 1,
                'shipping_cost' => $userCart->shipping_cost,
                'payment_status' => $paymentStatus->status,
                'order_status' => $orderStatus->status,
                'total_coupon_amount' => $userCart->total_coupon_amount ?? 0,
                'coupon_id' => $userCart->coupon_id ?? null,
                'payment_method_id' => $paymentMethod->id,
            ]);

            OrderAddress::create([
                'order_id' => $order->id,
                'customer_id' => $authUser->id,
                'first_name' => $selectedAddress->first_name,
                'last_name' => $selectedAddress->last_name,
                'email' => $selectedAddress->email,
                'phone' => $selectedAddress->phone ?? 0000000000,
                'address_line1' => $selectedAddress->address_line1,
                'address_line2' => $selectedAddress->address_line2,
                'locality' => $selectedAddress->locality,
                'city' => $selectedAddress->city,
                'state' => $selectedAddress->state,
                'postal_code' => $selectedAddress->postal_code,
                'country' => $selectedAddress->country,
                'type' => $selectedAddress->type,
            ]);

            foreach ($userCart->items as $item) {

                OrderItem::create([

                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_sku' => $item->product_sku,
                    'product_name' => $item->product_name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'product_special_price' => $item->product_special_price ?? null,
                    'product_special_price_from' => $item->product_special_price_from ?? null,
                    'product_special_price_to' => $item->product_special_price_to ?? null,
                    'total' => $item->total,
                    'coupon_id' => $item->coupon_id ?? null,
                ]);

                $item->delete();
            }

            $userCart->update([
                'shipping_cost' => 0,
                'coupon_id' => null,
                'total_coupon_amount' => 0,
                'is_active' => 0,
            ]);

            if ($paymentMethod->code == PaymentMethod::PAYMENT_METHOD_CODE_STRIPE) {
                return $this->proceedStripeCheckout($order->id);
            }

            if ($paymentMethod->code == PaymentMethod::PAYMENT_METHOD_CODE_TELR) {
                return $this->proceedTelrCheckout($order->id);
            }

            $template = EmailTemplate::where('slug', 'order_place')->first();
            if ($template) {
                EmailQueue::create([
                    'type' => 'order_place',
                    'template_id' => $template->id,
                    'email' => $authUser->email,
                    'content' => json_encode([
                        "name" => $authUser->name,
                        "order_number" => $order->order_number,
                        "support_url" => "https://store9.com/support",
                        "company_name" => "Store9",
                        "order_date" => $order->created_at->toDateString(),
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proceed Payment if payment method is Stripe Payment Gateway
     * @param $orderId
     * @return mixed
     */
    public function proceedStripeCheckout($orderId)
    {
        try {

            $authUser = Auth::user();
            $lineItems = [];

            $orderCurrencyCode = 'INR';
            $orderCurrencyDecimal = 2;
            $orderCurrencyDecimalFactor = pow(10, $orderCurrencyDecimal);

            $order = Order::where('id', $orderId)->firstOrFail();
            $orderItems = OrderItem::where('order_id', $orderId)->get();
            $shippingCost = $order->shipping_cost ?? 0;
            $couponDiscountAmount = $order->total_coupon_amount ?? 0;
            $totalItemsPrice = 0;

            foreach ($orderItems as $item) {
                if (!$item->product) {
                    return response()->json([
                        'status' => false,
                        'error' => "Product not found for item ID: {$item->id}",
                    ], 400);
                }

                $finalPrice = GetFinalPrice($item->product);
                $totalItemsPrice += ($finalPrice * $item->quantity);
            }

            $taxConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($taxConfig['value'] ?? 0);
            $taxType = $taxConfig['tax_type'] ?? 'exclusive';
            $vatAmount = 0;

            if ($taxType === 'exclusive') {
                $vatAmount = ($totalItemsPrice * $vatPercentage) / 100;
                $grandTotal = $totalItemsPrice + $vatAmount + $shippingCost - $couponDiscountAmount;
            } else {
                $vatAmount = calculateInclusiveTax($totalItemsPrice, $vatPercentage);
                $grandTotal = $totalItemsPrice + $shippingCost - $couponDiscountAmount;
            }

            $grandTotal = max($grandTotal, 0);
            $discountFactor = ($totalItemsPrice > 0) ? ($couponDiscountAmount / $totalItemsPrice) : 0;

            foreach ($orderItems as $item) {
                $finalPrice = GetFinalPrice($item->product);

                $discountedPrice = $finalPrice - ($finalPrice * $discountFactor);
                $discountedPrice = max($discountedPrice, 0);
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $orderCurrencyCode,
                        'product_data' => ['name' => $item->product_name],
                        'unit_amount' => floor($discountedPrice * $orderCurrencyDecimalFactor),
                    ],
                    'quantity' => $item->quantity,
                ];
            }

            if ($taxType === 'exclusive' && $vatAmount > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $orderCurrencyCode,
                        'product_data' => ['name' => 'VAT (' . $vatPercentage . '%)'],
                        'unit_amount' => floor($vatAmount * $orderCurrencyDecimalFactor),
                    ],
                    'quantity' => 1,
                ];
            }

            if ($shippingCost > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $orderCurrencyCode,
                        'product_data' => ['name' => 'Shipping Cost'],
                        'unit_amount' => floor($shippingCost * $orderCurrencyDecimalFactor),
                    ],
                    'quantity' => 1,
                ];
            }

            $checkoutSessionData = [
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => env('FRONTEND_URL') . "/payment-success?session_id={CHECKOUT_SESSION_ID}&order_id={$orderId}",
                'cancel_url' => url("/payment/cancel?order_id={$orderId}"),
                'customer_email' => $authUser->email,
            ];
            $stripeService = new StripeApiService();
            $checkoutSession = $stripeService->createCheckoutSession($checkoutSessionData);

            $paymentObj = PaymentMethod::firstWhere('code', PaymentMethod::PAYMENT_METHOD_CODE_STRIPE);
            $order->fill([
                'payment_method_id' => $paymentObj->id,
                'payment_ref' => $checkoutSession->id,
            ])->save();

            return response()->json([
                'status' => true,
                'message' => 'Redirect to Stripe for payment.',
                'checkout_url' => $checkoutSession->url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proceed Payment if payment method is Telr Payment Gateway
     * @param $orderId
     * @return mixed
     */
    public function proceedTelrCheckout($orderId)
    {
        try {
            $authUser = Auth::user();
            $lineItems = [];
            $orderCurrencyCode = 'AED';
            $orderCurrencyDecimal = 2;
            $orderCurrencyDecimalFactor = pow(10, $orderCurrencyDecimal);

            $order = Order::where('id', $orderId)->firstOrFail();
            $orderCustomer = $order->customerData;
            $customerDefaultAddress = CustomerAddress::select('*')->where('customer_id', $orderCustomer->id)->where('is_default', 1)->first();

            $orderItems = OrderItem::where('order_id', $orderId)->get();
            $shippingCost = $order->shipping_cost ?? 0;
            $couponDiscountAmount = $order->total_coupon_amount ?? 0;
            $totalItemsPrice = 0;
            foreach ($orderItems as $item) {
                if (!$item->product) {
                    return response()->json([
                        'status' => false,
                        'error' => "Product not found for item ID: {$item->id}",
                    ], 400);
                }

                $finalPrice = GetFinalPrice($item->product);
                $totalItemsPrice += ($finalPrice * $item->quantity);
            }

            $taxConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($taxConfig['value'] ?? 0);
            $taxType = $taxConfig['tax_type'] ?? 'exclusive';
            $vatAmount = 0;

            if ($taxType === 'exclusive') {
                $vatAmount = ($totalItemsPrice * $vatPercentage) / 100;
                $grandTotal = $totalItemsPrice + $vatAmount + $shippingCost - $couponDiscountAmount;
            } else {
                $vatAmount = calculateInclusiveTax($totalItemsPrice, $vatPercentage);
                $grandTotal = $totalItemsPrice + $shippingCost - $couponDiscountAmount;
            }

            $grandTotal = max($grandTotal, 0);
            $params = [];
            $orderDetails = [
                "cartid"        => $order->id,
                "amount"        => $grandTotal,
                "currency"      => $orderCurrencyCode,
                "description"   => 'Store9 Sale Order #' . $order,
            ];
            $params["order"] = $orderDetails;
            $customerDetails = [
                "ref" =>  "store9-customer-" . $orderCustomer->id,
                "email" => $orderCustomer->email,
                "name" => [
                    "forenames" => $orderCustomer->first_name,
                    "surname" => $orderCustomer->last_name,
                ],
                "address" => [
                    "line1" => $customerDefaultAddress ? $customerDefaultAddress->address_line1 : '',
                    "city" => $customerDefaultAddress ? $customerDefaultAddress->city : '',
                    "country" => $customerDefaultAddress ? $customerDefaultAddress->country : '',
                ],
                "phone" => $orderCustomer->phone,
            ];
            $params["customer"] = $customerDetails;
            $returnDetails = [
                "authorised"    => env('FRONTEND_URL') . "/payment-success?order_id={$orderId}",
                "cancelled"     => url("/payment/cancel?order_id={$orderId}"),
                "declined"      => url("/payment/cancel?order_id={$orderId}")
            ];
            $params["return"] = $returnDetails;
            $telrActions = new TelrActions();
            $paymentResult = $telrActions->createTelrOrder($params);
            if (is_null($paymentResult) || !array_key_exists('order', $paymentResult)) {
                return response()->json([
                    'status' => false,
                    'error' => 'No data found!',
                ], 404);
            }

            $orderResult = $paymentResult['order'];
            $paymentRef = trim($orderResult['ref']);
            $paymentUrl = trim($orderResult['url']);
            $paymentObj = PaymentMethod::firstWhere('code', PaymentMethod::PAYMENT_METHOD_CODE_TELR);
            $order->fill([
                'payment_method_id' => $paymentObj->id,
                'payment_ref' => $paymentRef,
            ])->save();

            return response()->json([
                'status' => true,
                'message' => 'Redirect to Telr for payment.',
                'checkout_url' => $paymentUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Payment Success
     */
    public function paymentSuccess(Request $request)
    {

        try {

            $order = Order::findOrFail($request->order_id);
            if (!$order) {
                return response()->json([
                    'status' => false,
                    'error' => 'Order Not found!',
                ], 404);
            }
            $paymentObj = PaymentMethod::find($order->payment_method_id);
            if (!$paymentObj) {
                return response()->json([
                    'status' => false,
                    'error' => 'Order Payment Method Not found!',
                ], 404);
            }
            $orderRef = $order->payment_ref;
            switch ($paymentObj->code) {
                case PaymentMethod::PAYMENT_METHOD_CODE_STRIPE:
                    $stripeService = new StripeApiService();
                    $session = $stripeService->fetchCheckoutSessionById($orderRef);
                    $paymentIntent = $stripeService->fetchPaymentIntentById($session->payment_intent);
                    $order->fill([
                        'payment_status' => 'Paid',
                        'transaction_id' => $paymentIntent->id,
                        'order_status' => 'Processing',
                    ])->save();

                    $orderCurrencyCode = $session->currency;
                    $orderCurrencyDecimal = 2;
                    $orderCurrencyDecimalFactor = pow(10, $orderCurrencyDecimal);

                    $sessionDataArray = $session->toArray();
                    $sessionJsonData = json_encode($sessionDataArray);

                    $paymentHistoryObj = PaymentHistory::create([
                        'payment_ref' => $session->id,
                        'customer_id' => $order->customer_id,
                        'order_id' => $order->id,
                        'payment_method_id' => $order->payment_method_id,
                        'date' => date('Y-m-d'),
                        'amount' => ($session->amount_total / $orderCurrencyDecimalFactor),
                        'currency_code' => strtoupper($orderCurrencyCode),
                        'transaction_id' => $session->id,
                        'payment_detail' => $sessionJsonData,
                        'status' => PaymentHistory::PAYMENT_STATUS_SUCCESS,
                    ]);
                    break;
                case PaymentMethod::PAYMENT_METHOD_CODE_TELR:

                    $telrActions = new TelrActions();
                    $paymentResult = $telrActions->checkTelrOrder($orderRef);
                    if (is_null($paymentResult)) {
                        return response()->json([
                            'status' => false,
                            'error' => 'Payment data  Not found!',
                        ], 404);
                    }

                    if (isset($paymentResult['error'])) {
                        return response()->json([
                            'status' => false,
                            'error' => trim($paymentResult['error']['message']),
                        ], 500);
                    }
                    $orderResult = $paymentResult['order'];
                    $ref = trim($orderResult['ref']);
                    $txnRef = trim($orderResult['ref']);
                    $order->fill([
                        'payment_status' => 'Paid',
                        'order_status' => 'Processing',
                        'transaction_id' => $txnRef
                    ])->save();
                    $orderTotalAmount = (float)$orderResult['amount'];
                    $orderCurrencyCode = $orderResult['currency'];
                    $paymentHistoryObj = PaymentHistory::create([
                        'payment_ref' => $ref,
                        'customer_id' => $order->customer_id,
                        'order_id' => $order->id,
                        'payment_method_id' => $order->payment_method_id,
                        'date' => date('Y-m-d'),
                        'amount' => $orderTotalAmount,
                        'currency_code' => strtoupper($orderCurrencyCode),
                        'transaction_id' => $txnRef,
                        'payment_detail' => json_encode($orderResult),
                        'status' => PaymentHistory::PAYMENT_STATUS_SUCCESS,
                    ]);
                    break;
                default:
                    break;
            }
            $order->refresh();

            return response()->json([
                'status' => true,
                'message' => 'Payment successful.',
                'order' => $order,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Payment Cancel
     */
    public function paymentCancel(Request $request)
    {
        try {
            $order = Order::findOrFail($request->order_id);
            $order->update([
                'payment_status' => 'Failed'
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Payment cancelled.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve Payment History from Stripe
     */
    public function getPaymentHistory()
    {
        try {

            $stripeService = new StripeApiService();
            $payments = $stripeService->fetchAllPaymentIntents(10);

            return response()->json([
                'status' => true,
                'payments' => $payments,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * function to update the order status like cancel and return etc.
     * can't update the previous status again and some addition validations are added
     * @param Request $request
     * @return  JsonResponse
     */
    public function updateOrderStatus(Request $request)
    {
        try {
            $authUser = Auth::user();

            $orderNumber = $request->order_number;
            $statusInput = $request->status;

            $status = OrderStatus::find($statusInput);
            if (!$status) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid status',
                ], 500);
            }

            if ($orderNumber && $status) {
                $order = Order::where('order_number', $orderNumber)->first();

                if ($order->customer_id != $authUser->id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized to update this order.',
                    ], 403);
                }

                if (!$order) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Order not found',
                    ], 500);
                }

                if ($order->status == $statusInput) {
                    return response()->json([
                        'status' => false,
                        'message' => 'trying to update same status',
                    ], 500);
                }

                if ($status->id == OrderStatus::ORDER_STATUS_CANCELED) {
                    if ($order->payment_status == PaymentStatusOption::PAYMENT_STATUS_PAID) {
                        // TODO
                        throw new Exception("Error Processing Request");
                    } else {
                        $order->status = $statusInput;
                        $order->payment_status = PaymentStatusOption::PAYMENT_STATUS_CANCELED;
                        $order->save();

                        $updatedOrder = Order::where('id', $order->id)->with(['OrderStatus'])->first();
                        return response()->json([
                            'status' => true,
                            'message' => 'Order status updated successfully',
                            'data' => $updatedOrder,
                        ], 200);
                    }
                } else {
                    $order->status = $status->id;
                    $order->save();
                    $updatedOrder = Order::where('id', $order->id)->with(['OrderStatus'])->first();

                    return response()->json([
                        'status' => true,
                        'message' => 'Order status updated successfully',
                        'data' => $updatedOrder,
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Order number and status required',
                ], 500);
            }
        } catch (Exception $e) {
            Log::error("Error in order status update: " . $e->getMessage() . " at line " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    /**
     * function to update the order
     * Need order_id as parameter to update the order and order items are required, validations are added
     * @param Request $request
     * @param $orderId
     * @return JsonResponse
     */
    public function updateOrder(Request $request, $orderId)
    {
        try {
            $authUser = Auth::user();
            // Fetch the order
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            if ($order->customer_id != $authUser->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to update this order.',
                ], 403);
            }

            $order->order_status = 'Canceled';
            $order->save();
            return response()->json([
                'status' => true,
                'message' => 'Order status updated successfully.',
                'order' => [
                    'order_id' => $order->id,
                    'status' => $order->order_status,
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error("Error updating order status: " . $e->getMessage() . " at line " . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch paginated orders for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function fetchUserOrders()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }
            $countryConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($countryConfig['value'] ?? 0);
            $taxType = $countryConfig['tax_type'] ?? null;
            $orders = Order::where('customer_id', $user->id)
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $pagination = [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
                'first_page_url' => $orders->url(1),
                'last_page_url' => $orders->url($orders->lastPage()),
                'next_page_url' => $orders->nextPageUrl(),
                'prev_page_url' => $orders->previousPageUrl(),
            ];

            $ordersData = $orders->map(function ($order) use ($vatPercentage, $taxType) {
                $subtotal = calculateSubtotal($order);
                $shippingCost = $order->shipping_cost ?? 0;
                $couponDiscountAmount = $order->total_coupon_amount ?? 0;
                $taxCalculation = calculateTaxAmount($subtotal, $vatPercentage, $taxType, $order->items->count(), 1, $subtotal);
                $totalVat = $taxCalculation['vat_amount'];
                $grandTotal = $taxCalculation['grand_total'] + $shippingCost - (float)$couponDiscountAmount;

                $couponData = null;
                if (!empty($order->coupon_id)) {
                    $coupon = Coupon::find($order->coupon_id);
                    if ($coupon) {
                        $couponData = [
                            'id' => $coupon->id,
                            'code' => $coupon->code,
                            'name' => $coupon->name,
                            'description' => $coupon->description,
                        ];
                    }
                }

                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_code' => $order->customer_code,
                    'first_name' => $order->first_name,
                    'last_name' => $order->last_name,
                    'created_at' => $order->created_at,
                    'subtotal' => round($subtotal, 2),
                    'shipping_cost' => round($shippingCost, 2),
                    'shipping_method' => $order->shipping_method_name,
                    'coupon_discount' => round($couponDiscountAmount, 2),
                    'total_vat' => round($vatPercentage, 2),
                    'grand_total' => max(0, round($grandTotal, 2)),
                    'product_count' => $order->items->count(),
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'coupon' => $couponData,
                    'items' => $order->items->map(function ($item) use ($vatPercentage, $taxType) {
                        $price = GetFinalPrice($item->product);
                        $taxCalculation = calculateTaxAmount($price, $vatPercentage, $taxType, $item->quantity, $price);
                        $taxAmount = $vatPercentage;
                        $totalPrice = $taxCalculation['total_amount'];

                        return [
                            'product_name' => $item->product_name,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'price' => round($price, 2),
                            'tax' => round($vatPercentage, 2),
                            'total' => round($totalPrice, 2),
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Orders fetched successfully.',
                'data' => $ordersData,
                'pagination' => $pagination,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get lastest Order
     *
     */

    public function getLatestOrder()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $lastOrder = Order::where('customer_id', $user->id)
                ->with(['items'])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$lastOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'No orders found for the user.',
                ], 404);
            }

            // Fetch tax configuration
            $countryConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($countryConfig['value'] ?? 0);
            $taxType = $countryConfig['tax_type'] ?? null;

            $subtotal = 0;
            $totalVat = 0;
            $total = 0;
            $grandTotal = 0;
            $shippingCost = $lastOrder->shipping_cost ?? 0;
            $couponDiscountAmount = $lastOrder->total_coupon_amount ?? 0;

            $cartItemsResponse = $lastOrder->items->map(function ($item) use (&$subtotal) {
                $finalPrice = GetFinalPrice($item->product); // Directly using item price
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;

                return [
                    'product_name' => $item->product_name,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'special_price' => $item->product_special_price,
                    'special_price_from' => $item->product_special_price_from,
                    'special_price_to' => $item->product_special_price_to,
                    'final_price' => $finalPrice,
                ];
            });

            $vatAmount = 0;
            $grandTotal = $subtotal;

            if ($taxType === 'exclusive') {
                $grandTotal = calculateExclusiveTax($subtotal, $vatPercentage);
                $vatAmount = $grandTotal - $subtotal;
                $vatAmount = 0;
            } else {
                $vatAmount = calculateInclusiveTax($subtotal, $vatPercentage);
            }

            $grandTotal += $shippingCost;
            $grandTotal -= $couponDiscountAmount;

            $couponData = null;
            if (!empty($lastOrder->coupon_id)) {
                $coupon = Coupon::find($lastOrder->coupon_id);
                if ($coupon) {
                    $couponData = [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'name' => $coupon->name,
                        'description' => $coupon->description,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Latest order fetched successfully.',
                'latest_order' => [
                    'order_id' => $lastOrder->id,
                    'order_number' => $lastOrder->order_number,
                    'customer_code' => $lastOrder->customer_code,
                    'first_name' => strtoupper($lastOrder->first_name),
                    'last_name' => strtoupper($lastOrder->last_name),
                    'created_at' => $lastOrder->created_at->format('Y-m-d H:i:s'),
                    'subtotal' => number_format($subtotal, 2),
                    'shipping_cost' => number_format($shippingCost, 2),
                    'discount_amount' => number_format($couponDiscountAmount, 2),
                    'vat_percentage' => number_format($vatPercentage, 2),
                    'vat_amount' => number_format($vatAmount, 2),
                    'grand_total' => number_format($grandTotal, 2),
                    'item_count' => $lastOrder->items->sum('quantity'),
                    'order_status' => $lastOrder->order_status,
                    'type' => $taxType,
                    'payment_status' => $lastOrder->payment_status,
                    'coupon' => $couponData,
                    'items' => $cartItemsResponse,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * get order by order id
     * @param $order_id
     * @return json
     */

    public function getOrderDetails($order_id)
    {
        try {
            $order = Order::with([
                'items',
                'comments',
                'paymentStatusOption',
                'invoice',
                'shipping',
                'addresses'
            ])->findOrFail($order_id);

            $countryConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($countryConfig['value'] ?? 0);
            $taxType = $countryConfig['tax_type'] ?? null;
            $subtotal = 0;
            $shippingCost = $order->shipping_cost ?? 0;
            $couponDiscountAmount = $order->total_coupon_amount ?? 0;
            $items = $order->items;
            $cartItemsCount = $items->count();

            $itemsResponse = $items->map(function ($item) use (&$subtotal) {
                $finalPrice = GetFinalPrice($item->product);
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;
                return [
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $finalPrice,
                    'total' => number_format($itemTotal, 2),
                ];
            });

            $vatAmount = 0;
            $grandTotal = $subtotal;

            if ($taxType === 'exclusive') {
                $grandTotal = calculateExclusiveTax($subtotal, $vatPercentage);
                $vatAmount = $grandTotal - $subtotal;
                $vatAmount = 0;
            } else {
                $vatAmount = calculateInclusiveTax($subtotal, $vatPercentage);
            }

            $grandTotal += $shippingCost;
            $grandTotal -= $couponDiscountAmount;

            $couponData = null;
            if (!empty($order->coupon_id)) {
                $coupon = Coupon::find($order->coupon_id);
                if ($coupon) {
                    $couponData = [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'name' => $coupon->name,
                        'description' => $coupon->description,
                    ];
                }
            }

            $customerId = $order->customer_id;
            $shippingAddress = $order->addresses->where('customer_id', $customerId)->where('type', 'shipping')->first();
            $billingAddress = $order->addresses->where('customer_id', $customerId)->where('type', 'billing')->first();

            return response()->json([
                'success' => true,
                'order' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_code' => $order->customer_code,
                    'first_name' => strtoupper($order->first_name),
                    'last_name' => strtoupper($order->last_name),
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'subtotal' => number_format($subtotal, 2),
                    'shipping_cost' => number_format($shippingCost, 2),
                    'discount_amount' => number_format($couponDiscountAmount, 2),
                    'type' => $taxType,
                    'vat_percentage' => number_format($vatPercentage, 2),
                    'vat_amount' => number_format($vatAmount, 2),
                    'grand_total' => number_format($grandTotal, 2),
                    'item_count' => $cartItemsCount,
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'coupon' => $couponData,
                    'items' => $itemsResponse,
                    'invoice' => optional($order->invoice),
                    'shipping' => optional($order->shipping),
                    'shipping_address' => $shippingAddress ? $shippingAddress->toArray() : null,
                    'billing_address' => $billingAddress ? $billingAddress->toArray() : null,
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Error fetching order details.', [
                'error' => $e->getMessage(),
                'order_id' => $order_id
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * downloadInvoiceApi by orderId
     * @param $orderId
     * @return invoice
     */
    public function downloadInvoiceApi($orderId)
    {
        try {
            $order = Order::with([
                'items.product',
                'addresses',
                'comments',
                'paymentStatusOption',
                'invoice'
            ])->findOrFail($orderId);

            $subtotal = 0;
            $order->items->each(function ($item) use (&$subtotal) {
                $finalPrice = $item->product ? GetFinalPrice($item->product) : 0;
                $lineTotal = $finalPrice * $item->quantity;
                $subtotal += $lineTotal;
            });

            $shippingCost = $order->shipping_cost ?? 0;
            $couponDiscountAmount = $order->total_coupon_amount ?? 0;

            $taxConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($taxConfig['value'] ?? 0);
            $taxType = $taxConfig['tax_type'] ?? null;

            if ($taxType === 'exclusive') {
                $vatAmount = ($subtotal * $vatPercentage) / 100;
                $grandTotal = $subtotal + $vatAmount + $shippingCost - $couponDiscountAmount;
            } else {
                $vatAmount = calculateInclusiveTax($subtotal, $vatPercentage);
                $grandTotal = $subtotal + $shippingCost - $couponDiscountAmount;
            }

            $shippingAddress = optional($order->addresses->where('type', 'shipping')->first());
            $billingAddress  = optional($order->addresses->where('type', 'billing')->first());

            $couponData = null;
            if (!empty($order->coupon_id)) {
                $coupon = Coupon::find($order->coupon_id);
                if ($coupon) {
                    $couponData = [
                        'id'          => $coupon->id,
                        'code'        => $coupon->code,
                        'name'        => $coupon->name,
                        'description' => $coupon->description,
                    ];
                }
            }

            $items = $order->items->map(function ($item) {
                $finalPrice = $item->product ? GetFinalPrice($item->product) : 0;
                $lineTotal = $finalPrice * $item->quantity;
                return [
                    'product_name' => optional($item->product)->name ?? 'Unknown Product',
                    'product_sku'  => optional($item->product)->sku ?? 'N/A',
                    'quantity'     => $item->quantity,
                    'price'        => $finalPrice,
                    'total'        => $lineTotal,
                ];
            });

            $invoiceData = [
                'invoice_id'      => optional($order->invoice)->id ?? null,
                'invoice_number'  => optional($order->invoice)->invoice_number ?? 'N/A',
                'order_id'        => $order->id,
                'order_number'    => $order->order_number,
                'customer_code'   => $order->customer_code,
                'first_name'      => strtoupper($order->first_name),
                'last_name'       => strtoupper($order->last_name),
                'created_at'      => $order->created_at->format('Y-m-d H:i:s'),
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shippingCost,
                'discount_amount' => $couponDiscountAmount,
                'vat_percentage'  => $vatPercentage,
                'vat_amount'      => $vatAmount,
                'grand_total'     => $grandTotal,
                'item_count'      => $order->items->count(),
                'payment_status'  => $order->payment_status,
                'coupon'          => $couponData,
                'tax_type'        => $taxType,
                'items'           => $items,
            ];

            $pdf = PDF::loadView('ordermanagement::pdf.invoice_pdf', [
                'order'           => $order,
                'invoiceData'     => $invoiceData,
                'shippingAddress' => $shippingAddress,
                'billingAddress'  => $billingAddress,
            ]);

            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                'invoice_' . $orderId . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate invoice: ' . $e->getMessage()
            ], 500);
        }
    }
}
