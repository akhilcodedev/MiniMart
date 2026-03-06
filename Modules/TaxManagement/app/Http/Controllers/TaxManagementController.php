<?php

namespace Modules\TaxManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\TaxManagement\Models\TaxClass;

class TaxManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = TaxClass::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $taxClasses = $query->get();

        return view('taxmanagement::index', compact('taxClasses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('taxmanagement::create');
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tax_classes',
        ]);

        TaxClass::create($request->all());

        return redirect()->route('tax.index')->with('success', 'Tax Class Created Successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TaxClass $tax
     * @return \Illuminate\View\View
     */
    public function edit(TaxClass $tax)
    {
        return view('taxmanagement::edit', compact('tax'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param TaxClass $tax
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TaxClass $tax)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tax_classes,code,' . $tax->id,
        ]);

        $tax->update($request->all());

        return redirect()->route('tax.index')->with('success', 'Tax Class Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TaxClass $tax
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TaxClass $tax)
    {
        $tax->delete();
        return redirect()->route('tax.index')->with('success', 'Tax Class Deleted Successfully');
    }
}
