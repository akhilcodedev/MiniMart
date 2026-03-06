<?php

namespace Modules\PaymentMethod\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PaymentMethod\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::with('attributes')->get();
        return view('paymentmethod::index',compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $testModeList = PaymentMethod::TEST_MODE_STATUS_LIST;
        $onlineStatusList = PaymentMethod::ONLINE_STATUS_LIST;
        $activeStatusList = PaymentMethod::ACTIVE_STATUS_LIST;

        return view('paymentmethod::create', compact(
            'testModeList',
            'onlineStatusList',
            'activeStatusList'
        ));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods,code',
            'sort_order' => 'required|integer|min:1',
            'test_mode' => 'required|boolean',
            'status' => 'required|boolean',
            'attributes.name' => 'nullable|array',
            'attributes.name.*' => 'nullable|string|max:255',
            'attributes.value' => 'nullable|array',
            'attributes.value.*' => 'nullable|string',
            'attributes.sort_order' => 'nullable|array',
            'attributes.sort_order.*' => 'nullable|integer|min:1'
        ]);

        $paymentMethod = PaymentMethod::create([
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'sort_order' => $validatedData['sort_order'],
            'test_mode' => $validatedData['test_mode'],
            'is_active' => $validatedData['status'],
        ]);


        if (!empty($validatedData['attributes']['name'])) {
            foreach ($validatedData['attributes']['name'] as $index => $attrName) {
                $paymentMethod->attributes()->create([
                    'payment_method_id' => $paymentMethod->id,
                    'name' => $attrName,
                    'value' => $validatedData['attributes']['value'][$index] ?? null,
                    'sort_order' => $validatedData['attributes']['sort_order'][$index] ?? null,
                ]);
            }
        }

        return redirect()->route('payment.index')->with('success', 'Payment method created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('paymentmethod::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        $paymentMethod = PaymentMethod::with('attributes')->findOrFail($id);

        $testModeList = PaymentMethod::TEST_MODE_STATUS_LIST;
        $onlineStatusList = PaymentMethod::ONLINE_STATUS_LIST;
        $activeStatusList = PaymentMethod::ACTIVE_STATUS_LIST;

        return view('paymentmethod::edit', compact(
            'paymentMethod',
            'testModeList',
            'onlineStatusList',
            'activeStatusList'
        ));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods,code,' . $id,
            'sort_order' => 'required|integer|min:1',
            'test_mode' => 'required|boolean',
            'status' => 'required|boolean',
            'attributes.name' => 'nullable|array',
            'attributes.name.*' => 'nullable|string|max:255',
            'attributes.value' => 'nullable|array',
            'attributes.value.*' => 'nullable|string',
            'attributes.sort_order' => 'nullable|array',
            'attributes.sort_order.*' => 'nullable|integer|min:1'
        ]);

        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->update([
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'sort_order' => $validatedData['sort_order'],
            'test_mode' => $validatedData['test_mode'],
            'is_active' => $validatedData['status'],
        ]);

        $paymentMethod->attributes()->delete();

        if (!empty($validatedData['attributes']['name'])) {
            foreach ($validatedData['attributes']['name'] as $index => $attrName) {
                $paymentMethod->attributes()->create([
                    'payment_method_id' => $paymentMethod->id,
                    'name' => $attrName,
                    'value' => $validatedData['attributes']['value'][$index] ?? null,
                    'sort_order' => $validatedData['attributes']['sort_order'][$index] ?? null,
                ]);
            }
        }

        return redirect()->route('payment.index')->with('success', 'Payment method updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);

            if ($paymentMethod->attributes) {
                $paymentMethod->attributes()->delete();
            }

            $paymentMethod->delete();

            return redirect()->route('payment.index')->with('success', 'Payment method and its related attributes deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('payment.index')->with('error', 'An error occurred while deleting the payment method. Please try again later.');
        }
    }

}
