<?php

namespace App\Http\Controllers;

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Products\Models\Product;
use Modules\ShippingMethode\Models\ShippingMethod;

class GuestCartController extends Controller
{
    /**
     * @param Request $request
     * @return json
     */
    public function addToCart(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'fingerprint_id' => 'nullable|string',
            ]);

            $productId = $validated['product_id'];
            $quantity = $validated['quantity'];
            $fingerprintId = $validated['fingerprint_id'] ?? null;

            if (!$fingerprintId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint ID must be provided for guest users.',
                ], 400);
            }

            DB::beginTransaction();

            $cart = Cart::firstOrCreate(
                ['guest_fingerprint_code' => $fingerprintId],
                [
                    'customer_code' => 'null',
                    'first_name'    => 'null',
                    'last_name'     => 'null',
                    'email'         => 'null',
                    'phone'         => 0,
                    'password'      => '',
                    'is_active'     => 1,
                    'profile_path'  => null,
                    'customer_id' => null,
                    'guest_is_active' => 1,
                    'shipping_cost' => 0,
                    'shipping_method_name' => 'Free Shipping',
                    'shipping_method_code' => 'free',
                    'shipping_method_status' => 1,
                    'shipping_attribute_name' => 'free',
                    'shipping_attribute_type' => 'Hours',
                    'shipping_attribute_value' => '0',
                    'shipping_attribute_sort_order' => 2,
                ]
            );

            $product = Product::findOrFail($productId);

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
                CartItem::create([
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

            $product->quantity -= $quantity;
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully.',
                'cart' => $cart->load('items.product.productImages')->toArray(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * @param Request $request(cart item id,qty)
     * update cart items
     * @return json
     */
    public function updateCartItem(Request $request)
    {
        try {
            $validated = $request->validate([
                'cart_item_id' => 'required|exists:cart_items,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItemId = $validated['cart_item_id'];
            $newQuantity = $validated['quantity'];

            $cartItem = CartItem::findOrFail($cartItemId);
            $product = $cartItem->product;

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            if ($newQuantity < $product->min_qty_allowed_in_shopping_cart) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum quantity allowed for this product is {$product->min_qty_allowed_in_shopping_cart}.",
                ], 400);
            }

            if ($newQuantity > $product->max_qty_allowed_in_shopping_cart) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum quantity allowed for this product is {$product->max_qty_allowed_in_shopping_cart}.",
                ], 400);
            }
            $availableStock = $product->quantity + $cartItem->quantity;
            if (($availableStock - $newQuantity) < $product->out_of_stock_threshold) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock to update quantity.',
                ], 400);
            }

            $product->quantity = $availableStock - $newQuantity;
            $product->save();

            $cartItem->quantity = $newQuantity;
            $cartItem->total = $cartItem->price * $newQuantity;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully.',
                'cart_item' => $cartItem->toArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * @param Request $request(cart item id)
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

            $product = $cartItem->product;
            $product->quantity += $cartItem->quantity;
            $product->save();

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
     * @param Request $request(finger print id )
     * @return mixed
     */
    public function getCart(Request $request)
    {
        try {
            $validated = $request->validate([
                'fingerprint_id' => 'nullable|string',
            ]);

            $fingerprintId = $validated['fingerprint_id'] ?? null;

            $cart = Cart::with('items.product.productImages')
                ->where(function ($query) use ($fingerprintId) {
                    $query->where('guest_fingerprint_code', $fingerprintId);
                })->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found.',
                ], 404);
            }

            $subtotal = $cart->items->sum(function ($item) {
                $finalPrice = GetFinalPrice($item->product);
                return $finalPrice * $item->quantity;
            });

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
                            'shipping_method' => $item->shipping_method,
                            'cost' => $item->shipping_cost,
                            'product' => [
                                'name' => $item->product_name,
                                'sku' => $item->product_sku,
                                'slug' => $item->product_url_key,
                                'short_description' => $item->product_short_description,
                                'is_in_stock' => (bool) $item->product_is_in_stock,
                                'price' => $item->price,
                                'special_price' => $item->product_special_price,
                                'status' => $item->product_status,
                                'images' => $item->product->productImages->pluck('image_url'),
                                'out_of_stock_threshold' => $item->product->out_of_stock_threshold,
                                'min_qty_allowed_in_shopping_cart' => $item->product->min_qty_allowed_in_shopping_cart,
                                'max_qty_allowed_in_shopping_cart' => $item->product->out_of_stock_threshold,
                            ],
                        ];
                    }),
                    'subtotal' => $subtotal,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Total Amount Cart amount incluing shipping cost
     * fingerprint_id
     * @param Request $request
     * @return json array
     */
    public function getTotal(Request $request)
    {
        try {
            $validated = $request->validate([
                'fingerprint_id' => 'nullable|string',
            ]);

            $fingerprintId = $validated['fingerprint_id'] ?? null;
            $cart = Cart::with('items')->where('guest_fingerprint_code', $fingerprintId)->first();

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
                $finalPrice = GetFinalPrice($item->product); // Assuming price is stored directly in the item
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;

                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
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
                'totals' => [
                    'subtotal' => number_format($subtotal, 2),
                    'shipping_cost' => number_format($shippingCost, 2),
                    'discount_amount' => number_format($couponDiscountAmount, 2),
                    'type' => $taxType,
                    'vat_amount' => number_format($vatAmount, 2),
                    'vat_percentage' => number_format($vatPercentage, 2),
                    'grand_total' => number_format($grandTotal, 2),
                    'product_count' => $cartItemsCount,
                    'coupon_data' => $couponData,
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
     * Retrieve cross-selling items based on the cart's products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Validates the cart ID and optional fingerprint ID for guest users.
     * Fetches cart items and retrieves related cross-selling products.
     */
    public function getCrossSellingItems(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer|exists:carts,id',
            'fingerprint_id' => 'nullable|string'
        ]);

        $cartId = $request->query('cart_id');
        $fingerprintId = $request->query('fingerprint_id');

        $cart = Cart::where('id', $cartId)->first();

        if (!auth()->check()) {
            if (!$fingerprintId) {
                return response()->json(['error' => 'Guest users must provide a Fingerprint ID'], 401);
            }

            if (!$cart->guest_fingerprint_code) {
                $cart->guest_fingerprint_code = $fingerprintId;
                $cart->save();
            }

            if ($cart->guest_fingerprint_code !== $fingerprintId) {
                return response()->json(['error' => 'This cart does not belong to the provided Fingerprint ID'], 403);
            }
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
