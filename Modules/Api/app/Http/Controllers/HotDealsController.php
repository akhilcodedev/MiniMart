<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Products\Models\HotDeal;

class HotDealsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function HotDealsIndex()
    {
        $hotDeals = HotDeal::with(['products.images'])->get();
        return response()->json($hotDeals);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function HotDealsStore()
    {
        $request->validate([
            'discount' => 'required|numeric|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'products' => 'required|array',
        ]);

        $existing = HotDeal::where(function ($query) use ($request) {
            $query->where('end_date', '>=', $request->start_date)
                ->where('start_date', '<=', $request->end_date);
        })->first();

        if ($existing) {
            return response()->json(['error' => 'An active hot deal already exists for this time period!'], 409);
        }

        $deal = HotDeal::create($request->only(['discount', 'start_date', 'end_date']));
        $deal->products()->attach($request->products);

        return response()->json(['message' => 'Hot Deal created successfully!', 'data' => $deal], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function HotDealsShow($id)
    {
        $deal = HotDeal::with('products.images')->find($id);

        if (!$deal) {
            return response()->json(['error' => 'Hot Deal not found.'], 404);
        }

        return response()->json($deal);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('api::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function HotDealsUpdate(Request $request, $id)
    {
        $request->validate([
            'discount' => 'required|numeric|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'products' => 'required|array',
        ]);

        $deal = HotDeal::find($id);
        if (!$deal) {
            return response()->json(['error' => 'Hot Deal not found.'], 404);
        }

        $overlapping = HotDeal::where('id', '!=', $id)
            ->where(function ($query) use ($request) {
                $query->where('end_date', '>=', $request->start_date)
                    ->where('start_date', '<=', $request->end_date);
            })->first();

        if ($overlapping) {
            return response()->json(['error' => 'Another active hot deal exists for this time period!'], 409);
        }

        $deal->update($request->only(['discount', 'start_date', 'end_date']));
        $deal->products()->sync($request->products);

        return response()->json(['message' => 'Hot Deal updated successfully!', 'data' => $deal]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function HotDealsDestroy($id)
    {
        $deal = HotDeal::find($id);
        if (!$deal) {
            return response()->json(['error' => 'Hot Deal not found.'], 404);
        }

        $deal->products()->detach();
        $deal->delete();

        return response()->json(['message' => 'Hot Deal deleted successfully.']);
    }
}
