<?php

namespace Modules\StoreManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\StoreManagement\Models\Store;

class StoreController extends Controller
{

    /**
     * Switch the current store by updating the session.
     *
     * @param Request $request The incoming HTTP request.
     * @param int $store_id The ID of the store to switch to.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchStore(Request $request, $store_id)
    {
        $store = Store::find($store_id);
        if (!$store) {
            return redirect()->back()->with('error', 'Store not found');
        }
        session(['store_id' => $store->id]);
        return redirect()->back()->with('success', 'Store switched successfully');
    }
}
