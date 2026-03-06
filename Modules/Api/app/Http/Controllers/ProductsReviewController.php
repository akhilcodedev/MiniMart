<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Modules\Products\Models\ProductReview;
use Modules\Products\Models\ProductReviewAttributeRating;
use Illuminate\Support\Arr;

class ProductsReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $reviews = ProductReview::with(['customer', 'product', 'attributeRatings.attribute'])
            ->latest()
            ->paginate(10);

        $data = [
            'data' => $reviews->items(),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Reviews fetched successfully.',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:approved,pending,not_approved',
            'attribute_ratings' => 'required|array',
            'attribute_ratings.*.attribute_id' => 'required|exists:product_review_attributes,id',
            'attribute_ratings.*.rating' => 'required|integer|between:1,5',
        ]);

        $user = auth()->user();

        $customerId = $user->id;

        $status = $validated['status'] ?? 'pending';

        $review = ProductReview::create([
            'customer_id' => $customerId,
            'product_id' => $validated['product_id'],
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $status,
        ]);

        foreach ($validated['attribute_ratings'] as $ratingData) {
            $review->attributeRatings()->create([
                'product_review_attribute_id' => $ratingData['attribute_id'],
                'rating' => $ratingData['rating'],
            ]);
        }

        if (Schema::hasColumn('product_reviews', 'average_rating')) {
            $averageRating = $review->calculateAverageRating();
            $review->update(['average_rating' => $averageRating]);
        }

        return response()->json([
            'message' => 'Review created successfully.',
            'data' => $review,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(ProductReview $review ,$productId): JsonResponse
    {

        $validator = Validator::make(['product_id' => $productId], [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID.',
                'errors' => $validator->errors(),
            ], 400);
        }


        $reviews = ProductReview::with(['customer', 'product', 'attributeRatings.attribute'])
            ->where('product_id', $productId)
            ->latest()
            ->paginate(10);

        if ($reviews->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews found for this product.',
            ], 404);
        }

        $user = Auth::user();

        $data = [
            'current_user' => $user,
            'data' => $reviews->items(),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Product reviews fetched successfully.',
        ]);
    }


    /**
     * Display product reviews for a specific product.
     */
    public function showByProduct(Request $request, $productId): JsonResponse
    {


        $validator = Validator::make(['product_id' => $productId], [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID.',
                'errors' => $validator->errors(),
            ], 400);
        }


        $reviews = ProductReview::with(['customer', 'product', 'attributeRatings.attribute'])
            ->where('product_id', $productId)
            ->latest()
            ->paginate(10);

        if ($reviews->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews found for this product.',
            ], 404);
        }

        $user = Auth::user();

        $data = [
            'current_user' => $user,
            'data' => $reviews->items(),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Product reviews fetched successfully.',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductReview $review): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_review_attribute_id' => 'required|exists:product_review_attributes,id',
            'title' => 'nullable|string|max:255',
            'star_rating' => 'nullable|integer|between:1,5',
            'description' => 'nullable|string|max:1000',
            'status' => 'in:approved,pending,not_approved',
            'attribute_ratings' => 'required|array',
            'attribute_ratings.*.attribute_id' => 'required|exists:product_review_attributes,id',
            'attribute_ratings.*.rating' => 'required|integer|between:1,5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $review->update([
                'product_id' => $request->product_id,
                'product_review_attribute_id' => $request->product_review_attribute_id,
                'title' => $request->title,
                'star_rating' => $request->star_rating,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            $review->attributeRatings()->delete();

            if ($request->has('attribute_ratings')) {
                foreach ($request->input('attribute_ratings', []) as $attributeRating) {
                    $review->attributeRatings()->create([
                        'product_review_attribute_id' => $attributeRating['attribute_id'],
                        'rating' => (int)$attributeRating['rating'],
                    ]);
                }

                if (Schema::hasColumn('product_reviews', 'average_rating')) {
                    $averageRating = $review->calculateAverageRating();
                    $review->update(['average_rating' => $averageRating]);
                }
            } else {
                if (Schema::hasColumn('product_reviews', 'average_rating')) {
                    $review->update(['average_rating' => null]);
                }
            }

            $review->load(['customer', 'product', 'attributeRatings.attribute']);

            return response()->json([
                'success' => true,
                'data' => $review,
                'message' => 'Review updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductReview $review): JsonResponse
    {
        try {
            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
