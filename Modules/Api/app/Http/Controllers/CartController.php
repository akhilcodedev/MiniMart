<?php

namespace App\Http\Controllers;

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Models\CartAddress;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerAddress;
use Modules\PriceRuleManagement\Models\Coupon;
use Modules\Products\Models\Product;

class CartController extends Controller
{

    /**
     * @param Request $request
     * @return json
     */
    public function addToCart(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'token' => 'nullable|string',
            ]);

            $productId = $validated['product_id'];
            $quantity = $validated['quantity'];
            $authUser = Auth::user();
            $customerId = $authUser ? $authUser->id : null;

            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either a valid token or fingerprint_id must be provided.',
                ], 400);
            }

            DB::beginTransaction();

            $product = Product::with('productImages')->findOrFail($productId);

            if ($quantity < $product->min_qty_allowed_in_shopping_cart) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum quantity allowed for this product is {$product->min_qty_allowed_in_shopping_cart}.",
                ], 400);
            }

            if ($quantity > $product->max_qty_allowed_in_shopping_cart) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum quantity allowed for this product is {$product->max_qty_allowed_in_shopping_cart}.",
                ], 400);
            }

            if (($product->quantity - $quantity) < $product->out_of_stock_threshold) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is out of stock.',
                ], 400);
            }

            $cart = Cart::where('customer_id', $customerId)->first();
            if (!$cart) {
                $cart = Cart::create([
                    'customer_id' => $customerId,
                    'customer_code' => $authUser->customer_code,
                    'first_name' => $authUser->first_name,
                    'last_name' => $authUser->last_name,
                    'email' => $authUser->email,
                    'phone' => $authUser->phone ?? '',
                    'password' => bcrypt('default_password'),
                    'is_active' => $authUser->is_active ?? 1,
                    'guest_fingerprint_code' => null,
                    'shipping_cost' => 0,
                    'shipping_method_name' => 'Free Shipping',
                    'shipping_method_code' => 'free',
                    'shipping_method_status' => 1,
                    'shipping_attribute_name' => 'free',
                    'shipping_attribute_type' => 'Hours',
                    'shipping_attribute_value' => '0',
                    'shipping_attribute_sort_order' => 2,
                    'is_cart_active' => 1,
                ]);
            }
            $defaultAddress = CustomerAddress::where('customer_id', $customerId)
                ->where('is_default', 1)
                ->first();
            if ($defaultAddress) {
                CartAddress::updateOrCreate(
                    ['cart_id' => $cart->id],
                    [
                        'first_name' => $authUser->first_name,
                        'last_name' => $authUser->last_name,
                        'email' => $authUser->email,
                        'phone' => $authUser->phone ?? '',
                        'customer_id' => $customerId,
                        'address_line1' => $defaultAddress->address_line1,
                        'address_line2' => $defaultAddress->address_line2,
                        'city' => $defaultAddress->city,
                        'state' => $defaultAddress->state,
                        'postal_code' => $defaultAddress->postal_code,
                        'country' => $defaultAddress->country,
                        'type' => $defaultAddress->type,
                        'is_default' => $defaultAddress->is_default,
                    ]
                );
            }

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->first();
            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;

                if ($newQuantity > $product->max_qty_allowed_in_shopping_cart) {
                    return response()->json([
                        'success' => false,
                        'message' => "You can only add up to {$product->max_qty_allowed_in_shopping_cart} of this product.",
                    ], 400);
                }
                $cartItem->quantity = $newQuantity;
                $cartItem->total = $cartItem->price * $newQuantity;
                $cartItem->save();
            } else {
                CartItem::create(
                    [
                        'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $product->price * $quantity,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'product_type_id' => $product->product_type_id,
                    'product_is_in_stock' => $product->is_in_stock,
                    'product_url_key' => $product->url_key,
                    'product_price' => $product->price,
                    'product_special_price' => $product->special_price,
                    'product_special_price_from' => $product->special_price_from,
                    'product_special_price_to' => $product->special_price_to,
                    'product_status' => $product->status,
                    'language_id' => $product->language_id,
                    'product_image_type_id' => $product->productImages->first()->image_type_id ?? null,
                    'product_image_url' => $product->productImages->first()->image_url ?? '',
                    'product_image_is_default' => $product->productImages->first()->is_default ?? 0,
                ]);
            }

            $cart->is_cart_active = 1;
            $cart->save();
            $product->quantity -= $quantity;
            $product->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * update cart item by cartitemid for customers
     * @param Request $request
     * @return mixed
     */
    public function updateCartItem(Request $request)
    {
        try {
            $validated = $request->validate([
                'cart_item_id' => 'required|exists:cart_items,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItemId = $validated['cart_item_id'];
            $quantity = $validated['quantity'];
            $cartItem = CartItem::findOrFail($cartItemId);

            $product = $cartItem->product;

            if ($product->quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock to update quantity.',
                ], 400);
            }

            $cartItem->quantity = $quantity;
            $cartItem->total = $cartItem->price * $quantity;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully.',
                'cart_item' => $cartItem->toArray(),

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * update cart item by cartitemid for customers
     * @param Request $request
     * @return mixed
     */
    public function removeCartItem(Request $request)
    {
        try {
            $validated = $request->validate([
                'cart_item_id' => 'required|exists:cart_items,id',
            ]);

            $cartItemId = $validated['cart_item_id'];
            $cartItem = CartItem::findOrFail($cartItemId);

            // Restock product
            $product = $cartItem->product;
            $product->quantity += $cartItem->quantity;
            $product->save();

            // Delete cart item
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart item removed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param Request $request
     * get cart details
     * @return json
     */

    public function getCart(Request $request)
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
            $cart = Cart::with('items.product.productImages')
                ->where('customer_id', $customerId)
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found.',
                ], 404);
            }

            $subtotal = $cart->items->sum(function ($item) {
                return GetFinalPrice($item->product) * $item->quantity;
            });

            $shippingCost = $cart->shipping_cost ?? 0;
            $couponId = $cart->coupon_id ?? null;
            $couponDiscountAmount = $cart->total_coupon_amount ?? 0;
            $grandTotal = $subtotal + $shippingCost - (float)$couponDiscountAmount;

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

            return response()->json([
                'success' => true,
                'message' => 'Cart fetched successfully.',
                'cart' => [
                    'id' => $cart->id,
                    'guest_fingerprint_id' => $cart->guest_fingerprint_code,
                    'is_active' => $cart->is_active,
                    'items' => $cart->items->map(function ($item) {
                        $finalPrice = GetFinalPrice($item->product);

                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'special_price' => $item->product_special_price,
                            'special_price_from' => $item->product_special_price_from,
                            'special_price_to' => $item->product_special_price_to,
                            'final_price' => $finalPrice,
                            'total' => $finalPrice * $item->quantity,
                            'product' => [
                                'name' => $item->product_name,
                                'sku' => $item->product_sku,
                                'slug' => $item->product_url_key,
                                'short_description' => $item->product_short_description,
                                'is_in_stock' => (bool)$item->product_is_in_stock,
                                'price' => $item->product->price,
                                'status' => $item->product->status,
                                'images' => $item->product->productImages->pluck('image_url'),
                                'out_of_stock_threshold' => $item->product->out_of_stock_threshold,
                                'min_qty_allowed_in_shopping_cart' => $item->product->min_qty_allowed_in_shopping_cart,
                                'max_qty_allowed_in_shopping_cart' => $item->product->max_qty_allowed_in_shopping_cart,
                            ],
                        ];
                    }),
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount_amount' => $couponDiscountAmount,
                    'grand_total' => $grandTotal,
                    'coupon_data' => $couponData,
                ],
            ]);
        } catch (\Illuminate\Database\QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $qe->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Total Amount Cart amount incluing shippin cost
     * fingerprint_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse array
     */

    public function getTotal(Request $request)
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
                    'price' => $item->product->price,
                    'special_price' => $item->product->special_price,
                    'special_price_from' => $item->product->special_price_from,
                    'special_price_to' => $item->product->special_price_to,
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
            return response()->json([
                'success' => true,
                'message' => 'Total calculated successfully.',
                'total' => [
                    'subtotal' => number_format($subtotal, 2),
                    'shipping_cost' => number_format($shippingCost, 2),
                    'discount_amount' => number_format($couponDiscountAmount, 2),
                    'vat_amount' => number_format($vatAmount, 2),
                    'vat_percentage' => number_format($vatPercentage, 2),
                    'grand_total' => number_format($grandTotal, 2),
                    'product_count' => $cartItemsCount,
                    'coupon_data' => $couponData,
                    'type' => $taxType,
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
     * Retrieve cross-selling items for the products in a specified cart.
     *
     * This method validates the `cart_id`, checks if the cart is active, and then finds cross-selling
     * products for each item in the cart. It returns the cart items along with the related cross-selling
     * products, including their details like price, images, and stock status.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object containing `cart_id`.
     * @return \Illuminate\Http\JsonResponse The response containing cart items and related products.
     */
    public function getCrossSellingItems(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer|exists:carts,id'
        ]);
        $cartId = $request->query('cart_id');
        $cart = Cart::where('id', $cartId)->first();
        if (!$cart || $cart->is_cart_active != 1) {
            return response()->json(['error' => 'Cart is not active or does not exist'], 400);
        }
        $cartItems = CartItem::where('cart_id', $cartId)->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'No items found in this cart'], 404);
        }
        $relatedProducts = [];
        foreach ($cartItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $crossSellingProducts = array_filter(explode(',', $product->cross_selling_products));
                if (!empty($crossSellingProducts)) {
                    $relatedProducts[] = [
                        'product_id' => $product->id,
                        'cross_selling_products' => Product::with('images')
                            ->whereIn('id', $crossSellingProducts)
                            ->get()
                            ->map(function ($relatedProduct) {
                                return [
                                    'id' => $relatedProduct->id,
                                    'sku' => $relatedProduct->sku,
                                    'name' => $relatedProduct->name,
                                    'product_type_id' => $relatedProduct->product_type_id,
                                    'is_in_stock' => $relatedProduct->is_in_stock,
                                    'url_key' => $relatedProduct->url_key,
                                    'price' => $relatedProduct->price,
                                    'special_price' => $relatedProduct->special_price,
                                    'final_price' => GetFinalPrice($relatedProduct),
                                    'images' => $relatedProduct->images->pluck('image_url')
                                ];
                            })
                    ];
                }
            }
        }

        return response()->json([
            'cart_items' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product->name
                ];
            }),
            'cross_selling_products' => $relatedProducts
        ]);
    }

}
