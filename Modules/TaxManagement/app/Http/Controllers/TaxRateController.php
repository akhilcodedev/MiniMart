<?php

namespace Modules\TaxManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\TaxManagement\Models\TaxClass;
use Modules\TaxManagement\Models\TaxRate;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $taxRates = TaxRate::all();
        return view('taxmanagement::TaxRate.index', compact('taxRates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $taxClasses = TaxClass::all();
        return view('taxmanagement::TaxRate.create', compact('taxClasses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'tax_class_id' => 'required|exists:tax_classes,id',
            'country' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0',
            'type' => 'required|string|max:255', // Type of tax (e.g., fixed, percentage)
        ]);

        TaxRate::create($request->all());

        return redirect()->route('tax-rates.index')->with('success', 'Tax Rate created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TaxRate $taxRate
     * @return \Illuminate\View\View
     */
    public function edit(TaxRate $taxRate)
    {
        $taxClasses = TaxClass::all();
        return view('taxmanagement::TaxRate.edit', compact('taxRate', 'taxClasses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param TaxRate $taxRate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TaxRate $taxRate)
    {
        $request->validate([
            'tax_class_id' => 'required|exists:tax_classes,id',
            'country' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0',
            'type' => 'required|string|max:255',
        ]);

        $taxRate->update($request->all());

        return redirect()->route('tax-rates.index')->with('success', 'Tax Rate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TaxRate $taxRate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return redirect()->route('tax-rates.index')->with('success', 'Tax Rate deleted successfully.');
    }
}
