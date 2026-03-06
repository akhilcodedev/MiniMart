<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Products\Models\ProductVariantOption;

class ProductVariantOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productOptions = ProductVariantOption::orderBy('id', 'desc')->get();
        return view('products::product_variant_options.index', compact('productOptions'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products::product_variant_options.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:product_variant_options,code',
            'name' => 'required|string',
            'active' => 'required|boolean'
        ], [
            'code.required' => 'The option code is required.',
            'code.min' => 'The option code must be at least 8 characters.',
            'code.unique' => 'The option code has already been taken.',
            'name.required' => 'The option name is required.',
            'active.required' => 'The active field is required.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ProductVariantOption::create([
            'code' => $request->code,
            'name' => $request->name,
            'is_active' => $request->active,
            'created_by' => auth()->user()->id ?? 1,
        ]);

        return redirect()->route('product.variant.options.index')->with('success', 'Product option created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     * @param $id
     * @return \Illuminate\Container\Container|mixed
     */
    public function edit($id)
    {
        $option = ProductVariantOption::findOrFail($id);
        return view('products::product_variant_options.edit' , compact('option'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return
     */
    public function update(Request $request, $id)
    {
        $option = ProductVariantOption::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code'   => [
                'required',
                'string',
                Rule::unique('product_variant_options', 'code')->ignore($option->id)
            ],
            'name'   => 'required|string',
            'active' => 'required|boolean'
        ], [
            'code.required'   => 'The option code is required.',
            'code.min'        => 'The option code must be at least 8 characters.',
            'code.unique'     => 'The option code has already been taken.',
            'name.required'   => 'The option name is required.',
            'active.required' => 'The active field is required.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $option->code   = $request->code;
        $option->name   = $request->name;
        $option->is_active = $request->active;
        $option->save();

        return redirect()->route('product.variant.options.index')
            ->with('success', 'Product option updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return
     */
    public function destroy($id)
    {
        $option = ProductVariantOption::findOrFail($id);
        $option->delete();
        return redirect()->route('product.variant.options.index')->with('success', 'Product option deleted successfully.');

    }
}
