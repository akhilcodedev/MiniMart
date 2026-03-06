<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Cart\Models\Cart;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CartCheckOutController extends Controller
{
    public function getCart(Request $request)
    {
        return $this->fetchCartData($request, 'guest');
    }

    public function getShipping(Request $request)
    {
        return $this->fetchShippingDetails($request, 'guest');
    }

    public function getTotal(Request $request)
    {
        return $this->fetchTotal($request, 'guest');
    }

    public function getCartCustomer(Request $request)
    {
        return $this->fetchCartData($request, 'customer');
    }

    public function getShippingCustomer(Request $request)
    {
        return $this->fetchShippingDetails($request, 'customer');
    }

    public function getTotalCustomer(Request $request)
    {
        return $this->fetchTotal($request, 'customer');
    }

    /**
     * Fetch cart data for guest or authenticated user.
     */
    private function fetchCartData(Request $request, $type)
    {
        try {
            $cart = $this->getCartByType($request, $type);

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart fetched successfully.',
                'cart' => [
                    'id' => $cart->id,
                    'guest_fingerprint_id' => $cart->guest_fingerprint_id,
                    'is_active' => $cart->is_active,
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->total,
                            'product' => [
                                'name' => $item->product->name,
                                'sku' => $item->product->sku,
                                'slug' => $item->product->url_key,
                                'short_description' => $item->product->short_description,
                                'is_in_stock' => (bool) $item->product->is_in_stock,
                                'price' => $item->product->price,
                                'special_price' => $item->product->special_price,
                                'status' => $item->product->status,
                                'images' => $item->product->productImages->pluck('image_url'),
                            ],
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Fetch shipping details for guest or authenticated user.
     */
    private function fetchShippingDetails(Request $request, $type)
    {
        try {
            $cart = $this->getCartByType($request, $type);

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Shipping details fetched successfully.',
                'shipping' => [
                    'shipment_method_id' => $cart->shipment_method_id,
                    'name' => 'Standard Shipping',
                    'shipping_cost' => $cart->shipping_cost,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Fetch total details for guest or authenticated user.
     */
    private function fetchTotal(Request $request, $type)
    {
        try {
            $cart = $this->getCartByType($request, $type);

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found.',
                ], 404);
            }

            $subtotal = $cart->items->sum('total');
            $grandTotal = $subtotal + ($cart->shipping_cost ?? 0);

            return response()->json([
                'success' => true,
                'message' => 'Cart totals fetched successfully.',
                'totals' => [
                    'subtotal' => $subtotal,
                    'shipping_cost' => $cart->shipping_cost,
                    'grand_total' => $grandTotal,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get cart by type (guest or authenticated user).
     */
    private function getCartByType(Request $request, $type)
    {
        if ($type === 'guest') {
            $validated = $request->validate([
                'fingerprint_id' => 'nullable|string',
            ]);

            $fingerprintId = $validated['fingerprint_id'] ?? null;

            return Cart::with(['items.product.productImages'])
                ->where('guest_fingerprint_id', $fingerprintId)
                ->first();
        } elseif ($type === 'customer') {
            $validated = $request->validate([
                'token' => 'nullable|string',
            ]);

            $authUser = Auth::user();

            if (!$authUser) {
                throw new \Exception('User not authenticated.', 401);
            }

            return Cart::with(['items.product.productImages'])
                ->where('customer_id', $authUser->id)
                ->first();
        }

        return null;
    }

    /**
     * Handle exceptions and return a standardized response.
     */
    private function handleException(\Exception $e)
    {
        $status = $e->getCode() === 401 ? 401 : 500;

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $status);
    }
}
