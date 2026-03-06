<?php

namespace Modules\StoreManagement\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Modules\CMS\Models\Language;
use App\Http\Controllers\Controller;
use Modules\StoreManagement\Models\Store;


class StoreManagementController extends Controller
{
    /**
     * Display a listing of stores.
     *
     * @return View
     */
    public function index(): View
    {
        $stores = Store::with('language')->get();
        $languages = Language::pluck('name', 'id');
        return view('storemanagement::stores.index', compact('stores', 'languages'));
    }

    /**
     * Show the form for creating a new store.
     *
     * @return View
     */
    public function create(): View
    {
        $languages = Language::all();
        return view('storemanagement::stores.create', compact('languages'));
    }

    /**
     * Store a newly created store in the database.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
      $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|integer|unique:stores',
            'status' => 'required|boolean',
            'url_key' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'language_id' => 'nullable|exists:languages,id'
        ]);

        Store::create($validated);

        return redirect()->route('stores.index')
            ->with('success', 'Store created successfully');
    }

    /**
     * Show the form for editing the specified store.
     *
     * @param Store $store
     * @return View
     */
    public function edit(Store $store): View
    {
        $languages = Language::all();
        return view('storemanagement::stores.edit', compact('store', 'languages'));
    }

    /**
     * Update the specified store in the database.
     *
     * @param Request $request
     * @param Store $store
     * @return RedirectResponse
     */
   public function update(Request $request, Store $store)
   {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|integer|unique:stores,code,' . $store->id,
            'status' => 'required|boolean',
            'url_key' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
             'language_id' => 'nullable|exists:languages,id'
        ]);

        $store->update($validated);

        return redirect()->route('stores.index')
            ->with('success', 'Store updated successfully');
    }

    /**
     * Remove the specified store from the database.
     *
     * @param Store $store
     * @return RedirectResponse
     */
    public function destroy(Store $store)
    {
        if($store->id == 1){
            return redirect()->back()
                ->with('error', 'Your are trying to delete the default store');
        }
        $store->delete();
        return redirect()->route('stores.index')
            ->with('success', 'Store deleted successfully');
    }

    /**
     * Delete multiple selected stores from the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSelected(Request $request)
       {
        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No records selected for deletion.']);
        }

        if (in_array(1, $ids)) {
            return response()->json(['success' => false, 'message' => 'Your are trying to delete the default store with ID 1.']);
        }

         try {
             Store::whereIn('id', $ids)->delete();
              return response()->json(['success' => true, 'message' => 'Selected Stores deleted successfully.']);
        } catch (\Exception $e) {
              return response()->json(['success' => false, 'message' => 'Error deleting selected stores.']);
        }
}
}
