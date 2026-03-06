<?php

namespace Modules\Products\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Products\Models\ProductReviewAttribute;

class ProductReviewAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attributes = ProductReviewAttribute::all();
        return view('products::product_review_attributes.index', compact('attributes'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products::product_review_attributes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:product_review_attributes',
            'label' => 'required',
        ]);

        ProductReviewAttribute::create($request->all());

        return redirect()->route('product_review_attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(ProductReviewAttribute $productReviewAttribute)
    {
        return view('product_review_attributes.show', compact('productReviewAttribute'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductReviewAttribute $productReviewAttribute)
    {
        return view('products::product_review_attributes.edit', compact('productReviewAttribute'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductReviewAttribute $productReviewAttribute)
    {
        $request->validate([
            'name' => 'required|unique:product_review_attributes,name,' . $productReviewAttribute->id,
            'label' => 'required',
        ]);

        $productReviewAttribute->update($request->all());

        return redirect()->route('product_review_attributes.index')
            ->with('success', 'Attribute updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductReviewAttribute $productReviewAttribute)
    {
        $productReviewAttribute->delete();

        return redirect()->route('product_review_attributes.index')
            ->with('success', 'Attribute deleted successfully');
    }
}
