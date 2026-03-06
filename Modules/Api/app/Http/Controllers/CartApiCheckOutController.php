<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Api\Helpers\ApiServiceHelper;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartAddress;
use Modules\Cart\Models\CartItem;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerAddress;
use Modules\PriceRuleManagement\Helpers\CartRuleServiceHelper;
use Modules\PriceRuleManagement\Models\Coupon;
use Modules\ShippingMethode\Models\ShippingMethod;
use function Illuminate\Validation\Rules\decimal;

class CartApiCheckOutController extends Controller
{


    /**
     * Get the address details of the authenticated user.*
     * @param Request $request
     * @return JsonResponse
     */
    public function getAddressByUser(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        try {
            $cart = Cart::where('customer_id', $user->id)->first();

            if ($cart) {
                if (is_null($cart->first_name) || is_null($cart->is_active) || is_null($cart->customer_id) || is_null($cart->customer_code)) {
                    $customer = Customer::where('id', $user->id)->first();

                    if ($customer) {
                        $cart->update([
                            'first_name'    => $cart->first_name ?? $customer->first_name,
                            'last_name'     => $cart->last_name ?? $customer->last_name,
                            'email'         => $cart->email ?? $customer->email,
                            'phone'         => $cart->phone ?? $customer->phone,
                            'is_active'     => $cart->is_active ?? $customer->is_active,
                            'customer_id'   => $cart->customer_id ?? $customer->id,
                            'customer_code' => $cart->customer_code ?? $customer->customer_code
                        ]);
                    }
                }
            } else {
                $customer = Customer::where('id', $user->id)->first();

                if ($customer) {
                    $cart = Cart::create([
                        'customer_id'   => $customer->id,
                        'customer_code' => $customer->customer_code,
                        'first_name'    => $customer->first_name,
                        'last_name'     => $customer->last_name,
                        'email'         => $customer->email,
                        'phone'         => $customer->phone,
                        'is_active'     => $customer->is_active ?? 1
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No customer data found'
                    ], 404);
                }
            }
            $addresses = $cart->addresses()
                ->where('customer_id', $user->id)->latest()
                ->paginate(3);
            return response()->json([
                'success'   => true,
                'message'   => 'User details and addresses retrieved successfully',
                'user_data' => [
                    'customer_id'   => $cart->customer_id,
                    'customer_code' => $cart->customer_code,
                    'first_name'    => $cart->first_name,
                    'last_name'     => $cart->last_name,
                    'email'         => $cart->email,
                    'phone'         => $cart->phone,
                    'is_active'     => $cart->is_active
                ],
                'addresses' => $addresses->items(),
                'pagination' => [
                    'current_page' => $addresses->currentPage(),
                    'per_page' => $addresses->perPage(),
                    'total' => $addresses->total(),
                    'last_page' => $addresses->lastPage(),
                    'next_page_url' => $addresses->nextPageUrl(),
                    'prev_page_url' => $addresses->previousPageUrl(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve addresses',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a cart and customer address by their ID.
     *
     * @param Request $request The HTTP request object.
     * @param int $addressId The ID of the address to be deleted.
     *
     * @return JsonResponse The JSON response indicating the success or failure of the delete operation.
     */
    public function deleteAddress(Request $request, $addressId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        DB::beginTransaction();

        try {
            $cartAddress = CartAddress::whereHas('cart', function ($query) use ($user) {
                $query->where('customer_id', $user->id);
            })->where('id', $addressId)->first();

            if (!$cartAddress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart address not found'
                ], 404);
            }

            $cartAddress->delete();

            $cart = $cartAddress->cart;
            if ($cart->addresses()->count() == 0) {
                $cart->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart and customer address deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cart and customer address',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve the cart and customer address by the given address ID.
     *
     * @param Request $request The HTTP request object.
     * @param int $addressId The ID of the address to be retrieved.
     *
     * @return JsonResponse The JSON response containing the cart and customer address data.
     */
    public function getCartCustomerAddress(Request $request, $addressId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        try {
            $cartAddress = CartAddress::whereHas('cart', function ($query) use ($user) {
                $query->where('customer_id', $user->id);
            })->where('id', $addressId)->first();

            if (!$cartAddress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart address not found'
                ], 404);
            }

            $customerAddress = CustomerAddress::where('customer_id', $user->id)
                ->where('address_line1', $cartAddress->address_line1)
                ->where('postal_code', $cartAddress->postal_code)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Cart and customer address retrieved successfully',
                'data'    => [
                    'cart_address'     => $cartAddress,
                    'customer_address' => $customerAddress ?? null, // Return null if no customer address found
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart and customer address',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Create a new cart address and link it to the customer's address.*
     * @param Request $request The HTTP request object.*
     * @return JsonResponse The JSON response containing the created cart address, customer address, and cart data.
     */


    public function CreateCartAddress(Request $request)
    {
        $user = Auth::user();
        $customerId = $user->id;
        $validator = Validator::make($request->all(), [
            'first_name'    => 'nullable|string|max:100',
            'last_name'     => 'nullable|string|max:100',
            'email'         => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:100',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'locality'      => 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'state'         => 'nullable|string|max:100',
            'postal_code'   => 'required|string|max:10',
            'country'       => 'required|string|max:100',
            'type'          => 'required|string|max:100',
            'is_default'    => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $newCart = Cart::where('customer_id', $user->id)->first();

            if (!$newCart) {
                throw new \Exception('No cart found for the user');
            }

            $firstName = $request->first_name ?? $user->first_name;
            $lastName = $request->last_name ?? $user->last_name;
            $email = $request->email ?? $user->email;

            $newCartAddress = CartAddress::create([
                'cart_id'       => $newCart->id,
                'customer_id'   => $customerId,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'email'         => $email,
                'phone'         => $request->phone,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'locality'      => $request->locality,
                'city'          => $request->city,
                'state'         => $request->state,
                'postal_code'   => $request->postal_code,
                'country'       => $request->country,
                'type'          => $request->type,
                'is_default'    => $request->is_default ?? false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Address created and linked to cart successfully',
                'data'    => [
                    'cart_address'     => $newCartAddress,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create cart address',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Update the cart address for the authenticated user.*
     * @param Request $request
     * @param  int  $cart_id
     * @return JsonResponse
     */

    public function UpdateCartAddress(Request $request, $address_id)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'state'         => 'nullable|string|max:100',
            'postal_code'   => 'required|string|max:10',
            'country'       => 'required|string|max:100',
            'type'          => 'required|string|max:255',
            'is_default'    => 'nullable|boolean',
            'first_name'    => 'nullable|string|max:100',
            'last_name'     => 'nullable|string|max:100',
            'locality'      => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $cartAddress = CartAddress::where('id', $address_id)
                ->whereHas('cart', function ($query) use ($user) {
                    $query->where('customer_id', $user->id);
                })
                ->first();

            if (!$cartAddress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found'
                ], 404);
            }

            $type = $request->type;

            $lastName = $request->last_name ?? $request->last_name_;

            $customerAddress = CustomerAddress::updateOrCreate(
                [
                    'customer_id' => $user->id,
                ],
                [
                    'first_name'    => $request->first_name,
                    'last_name'     => $lastName,
                    'address_line1' => $request->address_line1,
                    'address_line2' => $request->address_line2 ?? null,
                    'city'          => $request->city,
                    'state'         => $request->state ?? null,
                    'postal_code'   => $request->postal_code,
                    'country'       => $request->country,
                    'is_default'    => $request->is_default ?? false,
                    'locality'      => $request->locality ?? null,
                    'type'        => $type

                ]
            );

            $cartAddress->update([
                'first_name'    => $request->first_name,
                'last_name'     => $lastName,
                'customer_id' => $user->id,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2 ?? null,
                'city'          => $request->city,
                'state'         => $request->state ?? null,
                'postal_code'   => $request->postal_code,
                'country'       => $request->country,
                'is_default'    => $request->is_default ?? false,
                'locality'      => $request->locality ?? null,
                'type'          => $type
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart address and customer address updated successfully',
                'data'    => [
                    'customer_address' => $customerAddress,
                    'cart_address'     => $cartAddress
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart and customer address',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetching the Cart Price Rule Coupons List
     * @param Request $request
     * @return JsonResponse
     */
    public function couponList(Request $request) {

        try {

            $serviceHelper = new CartRuleServiceHelper();

            $user = Auth::user();
            if (!$user) {
                $errMessage = 'User not authenticated.';
                $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_UNAUTHORIZED;
                return $this->sendError($errMessage, ['error' => $errMessage], $httpCode);
            }

            $loggedCustomerId = $user->id;

            $returnResult = $serviceHelper->fetchCouponList($loggedCustomerId);

            $resultStatus = $returnResult['success'];
            $result = $returnResult['result'];
            $message = $returnResult['message'];
            $extraData = [];
            if (array_key_exists('extras', $returnResult) && is_array($returnResult['extras'])) {
                $extraData = $returnResult['extras'];
            }
            if ($resultStatus === false) {
                $errorCode = 0;
                $errMessage = $returnResult['message'];
                $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_OK;
                return $this->sendError($errMessage, ['error' => $errMessage], $httpCode, $errorCode, $extraData);
            }
            return $this->sendResponse($result, $message, $extraData);

        } catch (\Exception $e){
            Log::error("couponList ended up with error : " . $e);
            $errMessage = 'something went wrong at server';
            $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR;
            return $this->sendError($errMessage, ['error' => $errMessage], $httpCode);
        }

    }

    /**
     * Apply the given Coupon Code to the Cart.
     * @param Request $request
     * @return JsonResponse
     */
    public function applyCoupon(Request $request) {

        try {

            $serviceHelper = new CartRuleServiceHelper();

            $user = Auth::user();
            if (!$user) {
                $errMessage = 'User not authenticated.';
                $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_UNAUTHORIZED;
                return $this->sendError($errMessage, ['error' => $errMessage], $httpCode);
            }

            $loggedCustomerId = $user->id;

            $couponCode = ($request->has('coupon_code') && (trim($request->input('coupon_code')) != '')) ? trim($request->input('coupon_code')) : '';

            $returnResult = $serviceHelper->applyCouponToCart($loggedCustomerId, $couponCode);

            $resultStatus = $returnResult['success'];
            $result = $returnResult['result'];
            $message = $returnResult['message'];
            $extraData = [];
            if (array_key_exists('extras', $returnResult) && is_array($returnResult['extras'])) {
                $extraData = $returnResult['extras'];
            }
            if ($resultStatus === false) {
                $errorCode = 0;
                $errMessage = $returnResult['message'];
                $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_OK;
                return $this->sendError($errMessage, ['error' => $errMessage], $httpCode, $errorCode, $extraData);
            }
            return $this->sendResponse($result, $message, $extraData);

        } catch (\Exception $e){
            Log::error("applyCoupon ended up with error : " . $e);
            $errMessage = 'something went wrong at server';
            $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR;
            return $this->sendError($errMessage, ['error' => $errMessage], $httpCode);
        }

    }

    /**
     * Remove the applied Coupon from the Cart.
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCoupon(Request $request) {

        try {

            $serviceHelper = new CartRuleServiceHelper();

            $user = Auth::user();
            if (!$user) {
                $errMessage = 'User not authenticated.';
                $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_UNAUTHORIZED;
                return $this->sendError($errMessage, ['error' => $errMessage], $httpCode);
            }

            $loggedCustomerId = $user->id;

            $returnResult = $serviceHelper->removeCouponFromCart($loggedCustomerId);

            $resultStatus = $returnResult['success'];
            $result = $returnResult['result'];
            $message = $returnResult['message'];
            $extraData = [];
            if (array_key_exists('extras', $returnResult) && is_array($returnResult['extras'])) {
                $extraData = $returnResult['extras'];
            }
            if ($resultStatus === false) {
                $errorCode = 0;
                $errMessage = $returnResult['message'];
                $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_OK;
                return $this->sendError($errMessage, ['error' => $errMessage], $httpCode, $errorCode, $extraData);
            }
            return $this->sendResponse($result, $message, $extraData);

        } catch (\Exception $e){
            Log::error("removeCoupon ended up with error : " . $e);
            $errMessage = 'something went wrong at server';
            $httpCode = ApiServiceHelper::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR;
            return $this->sendError($errMessage, ['error' => $errMessage], $httpCode);
        }

    }

    /**
     * get grnad total
     * @param Request $request
     * @return JsonResponse
     */
    public function grandTotal(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'nullable|string',
            ]);

            $authUser = Auth::user();
            if (!$authUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $customerId = $authUser->id;
            $cart = Cart::with('items.product')->where('customer_id', $customerId)->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found.',
                ], 404);
            }

            $countryConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($countryConfig['value'] ?? 0);
            $taxType = $countryConfig['tax_type'] ?? null;

            $cartItems = $cart->items;
            $cartItemsCount = $cartItems->count();
            $shippingCost = $cart->shipping_cost ?? 0;
            $couponId = $cart->coupon_id ?? null;
            $couponDiscountAmount = $cart->total_coupon_amount ?? 0;

            $couponData = null;
            if ($couponId) {
                $couponDataObj = Coupon::find((int)$couponId);
                if ($couponDataObj) {
                    $couponData = [
                        'id' => $couponDataObj->id,
                        'code' => $couponDataObj->code,
                        'name' => $couponDataObj->name,
                        'description' => $couponDataObj->description,
                    ];
                }
            }

            $subtotal = 0;
            $cartItemsResponse = $cartItems->map(function ($item) use (&$subtotal) {
                $finalPrice = GetFinalPrice($item->product);
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;

                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'special_price' => $item->product_special_price,
                    'special_price_from' => $item->product_special_price_from,
                    'special_price_to' => $item->product_special_price_to,
                    'final_price' => $finalPrice,
                    'total'=>$itemTotal,
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

            return response()->json([
                'success' => true,
                'message' => 'Total calculated successfully.',
                'total' => [
                    'subtotal' => number_format($subtotal, 2),
                    'shipping_cost' => number_format($shippingCost, 2),
                    'discount_amount' => number_format($couponDiscountAmount, 2),
                    'vat_amount' => number_format($vatAmount, 2),
                    'vat_percentage'=>number_format($vatPercentage, 2),
                    'grand_total' => number_format($grandTotal, 2),
                    'product_count' => $cartItemsCount,
                    'coupon_data' => $couponData,
                    'type' => $taxType,
                    'cartItems' => $cartItemsResponse,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param Request $request
     * shipping methode id
     * @return json
     */
    public function updateShippingMethod(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $authUser = Auth::user();

            if (!$authUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $cart = Cart::where('customer_id', $authUser->id)->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found for this user.',
                ], 404);
            }

            $shippingMethod = ShippingMethod::find($request->shipping_method_id);

            if (!$shippingMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipping method not found.',
                ], 404);
            }
            $cart->shipping_method_id = $shippingMethod->id;
            $cart->shipping_method_name = $shippingMethod->name;
            $cart->shipping_method_code = $shippingMethod->code;
            if($shippingMethod->id == 1){
                $cart->shipping_cost = 0;

            }elseif($shippingMethod->id == 2){
                $cart->shipping_cost = 10;

            }elseif($shippingMethod->id == 3){
                $cart->shipping_cost = 20;

            }

            $cart->save();

            return response()->json([
                'success' => true,
                'message' => 'Shipping method updated successfully.',
                'data' => $cart
            ], 200); // success status ok

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * success response method.
     *
     * @param $result
     * @param string $message
     * @param array $extraData
     *
     * @return JsonResponse
     */
    public function sendResponse($result, string $message = '', array $extraData = [])
    {
        $response = [
            'code' => 1,
            'result' => $result,
            'msg' => $message,
        ];
        $finalResponse = $response;
        if(!is_null($extraData) && is_array($extraData) && (count($extraData) > 0)){
            $finalResponse = array_merge($response, $extraData);
        }
        return response()->json($finalResponse, 200);
    }


    /**
     * return error response.
     *
     * @param $error
     * @param array $errorMessages
     * @param int $code
     * @param int $errorCode
     * @param array $extraData
     *
     * @return JsonResponse
     */
    public function sendError($error, array $errorMessages = [], int $code = 404, int $errorCode = 0, array $extraData = [])
    {
        $response = [
            'code' => $errorCode,
            'msg' => $error,
        ];
        if(!empty($errorMessages)){
            $response['result'] = $errorMessages;
        }
        $finalResponse = $response;
        if(!is_null($extraData) && is_array($extraData) && (count($extraData) > 0)){
            $finalResponse = array_merge($response, $extraData);
        }
        return response()->json($finalResponse, $code);
    }

}
