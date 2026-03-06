<?php

namespace Modules\ShippingMethode\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\ShippingMethode\Models\ShippingMethod;
use Modules\ShippingMethode\Models\ShippingMethodAttribute;

class ShippingMethodController extends Controller
{
    /**
     * Display a listing of shipping methods.
     * @param Request $request The incoming HTTP request.
     * @return \Illuminate\View\View The rendered view.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = ShippingMethod::query();

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $shippingMethods = $query->get();
        return view('shippingmethode::shipping.index', compact('shippingMethods', 'search'));
    }

    /**
     * Show the form for creating a new shipping method.
     * @return \Illuminate\View\View The rendered view.
     */
    public function create()
    {
        return view('shippingmethode::shipping.create');
    }

     /**
     * Store a newly created shipping method in storage.
     * @param  Request $request The incoming HTTP request containing shipping method data.
     * @return \Illuminate\Http\RedirectResponse Redirects to the index page with success message on success or back to form page with error message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:shipping_methods,code',
            'status' => 'required|boolean',
            'attributes' => 'array',
            'attributes.*.name' => 'required|string',
            'attributes.*.value' => 'nullable|string',
            'attributes.*.sort_order' => 'nullable|integer',
        ]);


        DB::beginTransaction(); 

        try {
            $shippingMethod = ShippingMethod::create($request->only('name', 'code', 'status'));

            if ($request->has('attributes')) {
                foreach ($request->input('attributes', []) as $attributeData) {
                    $attributeData['type'] = 'text'; 
                    $shippingMethod->attributes()->create($attributeData);
                }
            }
            DB::commit();
           return redirect()->route('shipping.index')->with('success' , 'Shipping Method Created Successfully!'); // Redirect back to index page with success message.
        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Error creating shipping method: ' . $e->getMessage());

            return redirect()->back()->withErrors('error', 'An error occurred while creating the shipping method. Please try again.'); // Redirect to the back page with error message.
        }
    }

    /**
     * Show the form for editing the specified shipping method.  
     * @param ShippingMethod $shippingMethod The shipping method model instance to be edited (route model binding).
     * @return \Illuminate\View\View The rendered view.
     */
    public function edit(ShippingMethod $shippingMethod)
    {
        return view('shippingmethode::shipping.edit', compact('shippingMethod'));
    }

    /**
     * Update the specified shipping method in storage.
     * @param  Request $request The incoming HTTP request containing updated shipping method data.
     * @param ShippingMethod $shippingMethod The shipping method model instance to be updated (route model binding).
     * @return \Illuminate\Http\RedirectResponse  Redirects to the index page with success message on success or back to form page with error message.
     */
    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:shipping_methods,code,' . $shippingMethod->id,
            'status' => 'required|boolean',
            'attributes' => 'array',
            'attributes.*.id' => 'nullable|integer',
            'attributes.*.name' => 'required|string',
            'attributes.*.value' => 'nullable|string',
            'attributes.*.sort_order' => 'nullable|integer',
            'delete_attributes' => 'nullable|array', 
        ]);
        DB::beginTransaction(); 

        try {
            $shippingMethod->update($request->only('name', 'code', 'status'));

            if ($request->has('delete_attributes')) {
                 ShippingMethodAttribute::whereIn('id', $request->input('delete_attributes'))->delete();
            }

            if ($request->has('attributes')) {
                foreach ($request->input('attributes', []) as $attributeData) {
                    if (isset($attributeData['id']) && $attributeData['id']) {
                       $existingAttribute = ShippingMethodAttribute::find($attributeData['id']);
                        if ($existingAttribute) {
                           $existingAttribute->update(
                             collect($attributeData)->except('type')->toArray()
                           );
                        } else {
                           Log::error('Error updating shipping method attribute, attribute does not exist: ' . json_encode($attributeData));
                           continue;
                        }
                    } else {
                       $attributeData['type'] = 'text';
                        $shippingMethod->attributes()->create($attributeData);
                    }
                }
            }
           DB::commit();  

           return redirect()->route('shipping.index')->with('success', 'Shipping Method Updated Successfully!'); // Redirect to index page with success message.

        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Error updating shipping method: ' . $e->getMessage());  
           return redirect()->back()->withErrors('error'  ,'An error occurred while updating the shipping method. Please try again.'); //Redirect back to edit page with error message
        }
    }

    /**
     * Remove the specified shipping method from storage.
     * @param ShippingMethod $shippingMethod The shipping method model instance to be deleted (route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirects to the index page with success message on success or back to form page with error message.
     */
    public function destroy(ShippingMethod $shippingMethod)
    {
        try {
            $shippingMethod->delete();  

            return redirect()->route('shipping.index')->with('success' , 'Shipping Method deleted successfully!'); // Redirect to index page with success message.
        } catch (\Exception $e) {
            Log::error('Error deleting shipping method: ' . $e->getMessage()); 
            return redirect()->back()->withErrors('error' , 'An error occurred while deleting the shipping method. Please try again.'); //Redirect back to the index page with error message.
        }
    }
}