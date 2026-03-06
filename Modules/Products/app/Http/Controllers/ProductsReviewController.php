<?php

namespace Modules\Products\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Products\Models\Product;
use Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\Schema;
use Modules\Products\Models\ProductReview;
use Modules\Products\Models\ProductReviewAttribute;

class ProductsReviewController extends Controller
{
    /**
     * Display a listing of product reviews.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $query = ProductReview::with(['customer', 'product']);
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q2) use ($search) {
                    $q2->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('customer', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reviews = $query->latest()->paginate(10);

        return view('products::products_review.index', compact('reviews'));
    }

    /**
     * Show the form for creating a new product review.
     *
     * @return View
     */
    public function create()
    {
        $products = Product::all();
        $customers = Customer::all();
        $attributes = ProductReviewAttribute::all();
        return view('products::products_review.create', compact('products', 'customers', 'attributes'));
    }

    /**
     * Store a newly created product review in the database.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:approved,pending,not_approved',
            'attribute_ratings' => 'array',
            'attribute_ratings.*' => 'required|integer|between:1,5',
        ]);

        $review = ProductReview::create([
            // 'user_id' => auth()->id(),
            'customer_id' => $validated['customer_id'],
            'product_id' => $validated['product_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        if (isset($validated['attribute_ratings'])) {
            foreach ($validated['attribute_ratings'] as $attributeId => $rating) {
                $review->attributeRatings()->create([
                    'product_review_attribute_id' => $attributeId,
                    'rating' => $rating,
                ]);
            }

            if (Schema::hasColumn('product_reviews', 'average_rating')) {
                $averageRating = $review->calculateAverageRating();
                $review->update(['average_rating' => $averageRating]);
            }
        }

        return redirect()->route('products_review.index')->with('success', 'Review created successfully.');
    }

    /**
     * Show the form for editing the specified product review.
     *
     * @param ProductReview $review
     * @return View
     */
    public function edit(ProductReview $review)
    {
        $products = Product::all();
        $customers = Customer::all();
        $attributes = ProductReviewAttribute::all();
        $review->load('attributeRatings');
        $existingRatings = $review->attributeRatings->pluck('rating', 'product_review_attribute_id')->toArray();

        return view('products::products_review.edit', compact('review', 'products', 'customers', 'attributes', 'existingRatings'));
    }

    /**
     * Update the specified product review in the database.
     *
     * @param Request $request
     * @param ProductReview $review
     * @return RedirectResponse
     */
    public function update(Request $request, ProductReview $review)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'title' => 'nullable|string|max:255',
            'star_rating' => 'nullable|integer|between:1,5',
            'description' => 'nullable|string|max:1000',
            'status' => 'in:approved,pending,not_approved',
            'attribute_ratings' => 'array',
            'attribute_ratings.*' => 'integer|between:1,5',
        ]);

        $review->update([
            'product_id' => $validated['product_id'],
            'title' => $validated['title'],
            'star_rating' => $validated['star_rating'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        if (isset($validated['attribute_ratings'])) {
            $review->attributeRatings()->delete();

            foreach ($validated['attribute_ratings'] as $attributeId => $rating) {
                $review->attributeRatings()->create([
                    'product_review_attribute_id' => $attributeId,
                    'rating' => $rating,
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

        return redirect()->route('products_review.index')->with('success', 'Review updated successfully.');
    }

    /**
     * Remove the specified product review from the database.
     *
     * @param ProductReview $review
     * @return RedirectResponse
     */
    public function destroy(ProductReview $review)
    {
        $review->delete();
        return redirect()->route('products_review.index')->with('success', 'Review deleted successfully.');
    }
}
