<?php

namespace Modules\Products\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Products\Models\HotDeal;
use Modules\Products\Models\Product;

class HotDealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotDeals = HotDeal::with(['products' => function ($q) {
            $q->with(['images' => function ($q) {
                $q->where('is_default', true);
            }]);
        }])->get();
        return view('products::hot_deals.index', compact('hotDeals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::with(['images' => function ($q) {
            $q->where('is_default', true);
        }])->where('status', 'active')->get();

        return view('products::hot_deals.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'discount' => 'required|numeric|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'products' => 'required|array',
        ]);

        // Check if there's an existing active hot deal
        $existingDeal = HotDeal::where(function ($query) use ($request) {
            $query->where('end_date', '>=', $request->start_date)
                ->where('start_date', '<=', $request->end_date);
        })->first();

        if ($existingDeal) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cannot create new hot deal. An active hot deal already exists for this time period!');
        }

        $hotDeal = HotDeal::create($request->only(['discount', 'start_date', 'end_date']));
        $hotDeal->products()->attach($request->products);

        return redirect()->route('hot_deals.index')->with('success', 'Hot Deal created successfully!');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $deal = HotDeal::with('products.images')->findOrFail($id);
        return view('products::hot_deals.view', compact('deal'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $deal = HotDeal::with('products')->findOrFail($id);

        $products = Product::with(['images' => function ($q) {
            $q->where('is_default', true);
        }])->where('status', 'active')->get();

        return view('products::hot_deals.edit', compact('deal', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'discount' => 'required|numeric|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'products' => 'required|array',
        ]);

        $hotDeal = HotDeal::findOrFail($id);

        // Check for overlapping deals excluding the current deal
        $overlappingDeal = HotDeal::where('id', '!=', $id)
            ->where(function ($query) use ($request) {
                $query->where('end_date', '>=', $request->start_date)
                    ->where('start_date', '<=', $request->end_date);
            })->first();

        if ($overlappingDeal) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cannot update hot deal. Another active deal exists for this time period!');
        }

        $hotDeal->update($request->only(['discount', 'start_date', 'end_date']));
        $hotDeal->products()->sync($request->products);

        return redirect()->route('hot_deals.index')->with('success', 'Hot Deal updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $hotDeal = HotDeal::findOrFail($id);

        $hotDeal->products()->detach();

        $hotDeal->delete();

        return redirect()->route('hot_deals.index')->with('success', 'Hot Deal deleted successfully!');
    }
}
