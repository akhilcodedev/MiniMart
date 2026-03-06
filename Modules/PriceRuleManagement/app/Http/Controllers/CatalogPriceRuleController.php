<?php

namespace Modules\PriceRuleManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Category\Models\Category;
use Modules\Customer\Models\CustomerGroups;
use Modules\PriceRuleManagement\Jobs\ApplyCatalogPriceRuleJob;
use Modules\PriceRuleManagement\Models\CatalogPriceRule;

class CatalogPriceRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $catalogPriceRule = CatalogPriceRule::all();
        return view('pricerulemanagement::Catalog.index', compact('catalogPriceRule'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customerGroups = CustomerGroups::all();
        return view('pricerulemanagement::Catalog.create', compact('customerGroups'));
    }

    /**
     * function to store catalog price rule
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer',
            'customer_groups' => 'required|array',
            'discount_type' => 'required|string',
            'discount_amount' => 'required|numeric',
            'rule_conditions' => 'nullable|array'
        ]);

        try {
            $rule = new CatalogPriceRule();
            $rule->name = $request->name;
            $rule->store_id = session('store_id') ?? 1;
            $rule->description = $request->description;
            $rule->is_active = $request->active;
            $rule->customer_groups = json_encode($request->customer_groups);
            $rule->priority = $request->priority;
            $rule->discount_type = $request->discount_type;
            $rule->discount_value = $request->discount_amount;
            $rule->discard_subsequent = $request->discard_subsequent;

            $conditions = [];
            if ($request->rule_conditions) {
                foreach ($request->rule_conditions as $condition) {
                    if (isset($condition['rule_type']) && isset($condition['rule_values'])) {
                        if ($condition['rule_type'] == 'sku') {
                            $skuValues = explode(',', $condition['rule_values'][0]);
                            $skuValues = array_map('trim', $skuValues);
                            $conditions[] = [
                                'rule_type' => $condition['rule_type'],
                                'rule_values' => $skuValues,
                            ];
                        } else {
                            $conditions[] = [
                                'rule_type' => $condition['rule_type'],
                                'rule_values' => $condition['rule_values'],
                            ];
                        }
                    }
                }
            }

            $rule->conditions = json_encode($conditions);
            $rule->save();

            return response()->json([
                'success' => true,
                'message' => 'Catalog price rule saved successfully!',
                'redirect' => route('catalog-price-rules.index')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $priceRule = CatalogPriceRule::findOrFail($id);
        $customerGroups = CustomerGroups::all();
        $categories = Category::select('id', 'name')->get();
        return view('pricerulemanagement::Catalog.edit', compact('priceRule', 'customerGroups', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request)
    {
        $rule = CatalogPriceRule::findOrFail($request->id);
        $rule->name = $request->name;
        $rule->store_id = session('store_id') ?? 1;
        $rule->description = $request->description;
        $rule->is_active = $request->active;
        $rule->customer_groups = json_encode($request->customer_groups);
        $rule->priority = $request->priority;
        $rule->discount_type = $request->discount_type;
        $rule->discount_value = $request->discount_amount;
        $rule->discard_subsequent = $request->discard_subsequent;

        $conditions = [];
        if ($request->rule_conditions) {
            foreach ($request->rule_conditions as $condition) {
                if (isset($condition['rule_type']) && isset($condition['rule_values'])) {

                    if ($condition['rule_type'] == 'sku') {
                        if (isset($condition['rule_values'][0]) && $condition['rule_values'][0]) {
                            $skuValues = explode(',', $condition['rule_values'][0]);
                            $skuValues = array_map('trim', $skuValues);
                            $conditions[] = [
                                'rule_type' => $condition['rule_type'],
                                'rule_values' => $skuValues,
                            ];
                        }
                    } else {
                        $conditions[] = [
                            'rule_type' => $condition['rule_type'],
                            'rule_values' => $condition['rule_values'],
                        ];
                    }
                }
            }
        }
        $rule->conditions = json_encode($conditions);
        $rule->save();
        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return mixed
     *
     */
    public function destroy(Request $request)
    {
        $catalogPriceRule = CatalogPriceRule::find($request->id);
        try {
            $catalogPriceRule->delete();
            return response()->json([
                'status' => true,
                'message' => 'Block deleted successfully'
            ], 200);
        } catch (\Exception $exception) {
            Log::error('Something went wrong when delete cms block , Error ::' . $exception->getMessage() . " on Line :: " . $exception->getLine());
            return response()->json([
                'status' => false,
                'message' => 'something went wrong',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * return values for catalog price , return response as json
     * @param Request $request
     * @return mixed
     */
    public function getRuleValues(Request $request)
    {
        $type = $request->input('type');
        if ($type === 'category') {
            return response()->json(Category::select('id', 'name')->get());
        }

        return response()->json([]);
    }

    /**
     * run apply price rule job background
     * @return mixed
     */
    public function runIndexer()
    {
        ApplyCatalogPriceRuleJob::dispatch();
        return response()->json([
            'status' => true,
            'message' => 'Job running successfully'
        ], 200);
    }
}
