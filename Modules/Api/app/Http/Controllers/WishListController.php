<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Models\WishListItem;

class WishListController extends Controller
{
    /**
     * Add a product to the customer's wish list.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToWishList(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
            ]);

            $authUser = Auth::user();
            $productId = $request->product_id;

            WishListItem::updateOrCreate([
                'product_id' => $productId,
                'customer_id' => $authUser->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Item added to wish list',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all wish list items for the authenticated customer.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function getWishListItems(Request $request)
    {
        try {
            $authUser = Auth::user();

            $wishListItems = WishListItem::with('product.productImages')
                ->where('customer_id', $authUser->id)
                ->paginate(12);

            $pagination = [
                'current_page' => $wishListItems->currentPage(),
                'per_page' => $wishListItems->perPage(),
                'total' => $wishListItems->total(),
                'last_page' => $wishListItems->lastPage(),
                'first_page_url' => $wishListItems->url(1),
                'last_page_url' => $wishListItems->url($wishListItems->lastPage()),
                'next_page_url' => $wishListItems->nextPageUrl(),
                'prev_page_url' => $wishListItems->previousPageUrl(),
            ];

            $currentDate = now();
            $responseData = $wishListItems->map(function ($item) use ($currentDate) {
                if (!$item->product) {
                    return null;
                }

                $product = $item->product;
                $specialPrice = $product->special_price;
                $productPrice = $product->price;

                $finalPrice = GetFinalPrice($product);

                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'is_in_stock' => (bool) $product->is_in_stock,
                        'url_key' => $product->url_key,
                        'price' => $productPrice,
                        'special_price' => $specialPrice,
                        'final_price' => $finalPrice,
                        'images' => $product->productImages->map(function ($image) {
                            return ['url' => $image->image_url];
                        }),
                    ],
                ];
            })->filter();
            $subtotal = $wishListItems->sum(function ($item) {
                return GetFinalPrice($item->product) * $item->quantity;
            });

            return response()->json([
                'status' => true,
                'message' => 'Wishlist items fetched successfully.',
                'data' => $responseData->values(),
                'pagination' => $pagination,
                'subtotal' => $subtotal,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a wish list item (e.g., add notes).
     *
     * @param Request $request
     * @param int $id WishListItem ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateWishListItem(Request $request, int $id)
    {
        try {
            $request->validate([
                'notes' => 'nullable|string|max:255',
            ]);

            $authUser = Auth::user();
            $wishListItem = WishListItem::where('id', $id)
                ->where('customer_id', $authUser->id)
                ->first();

            if (!$wishListItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Wish list item not found or does not belong to the user',
                ], 404);
            }

            if ($request->has('notes')) {
                $wishListItem->notes = $request->notes;
                $wishListItem->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Wish list item updated successfully',
                'data' => $wishListItem, // Send the updated item in response
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a wish list item.
     *
     * @param int $id WishListItem ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteWishListItem(int $id)
    {
        try {
            $authUser = Auth::user();
            $wishListItem = WishListItem::where('id', $id)
                ->where('customer_id', $authUser->id)
                ->first();

            if (!$wishListItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Wish list item not found or does not belong to the user',
                ], 404);
            }

            $wishListItem->delete();

            return response()->json([
                'status' => true,
                'message' => 'Wish list item deleted successfully',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}
