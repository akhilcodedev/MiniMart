<?php

namespace Modules\Customer\Http\Controllers\CustomerGroups;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Customer\Models\CustomerGroups;

class CustomerGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $itemsPerPage = request('page_size', 10);
        $customerGroups = CustomerGroups::paginate($itemsPerPage);

        return view('customer::CustomerGroups.index', compact('customerGroups'));
    }

    /**
     * Filter and paginate customer groups based on search keyword.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
   public function filter(Request $request)
    {
       $query = CustomerGroups::query();

        if ($request->has('search') && $request->search != '') {
             $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $pageSize = $request->has('page_size') ? $request->page_size : 10;
        $customerGroups = $query->paginate($pageSize);

        return view('customer::CustomerGroups.index', compact('customerGroups'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer::CustomerGroups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        CustomerGroups::create($request->only(['name', 'description']));

        return redirect()->route('customer.groups.index')->with('success', 'Customer Group added successfully.');
    }

    /**
     * Show the form for editing the specified customer group.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $customerGroup = CustomerGroups::findOrFail($id);

        return view('customer::CustomerGroups.edit', compact('customerGroup'));
    }

    /**
     * Update the specified customer group in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $customerGroup = CustomerGroups::findOrFail($id);

        $customerGroup->update($request->only(['name', 'description']));

        return redirect()->route('customer.groups.index')->with('success', 'Customer Group updated successfully.');
    }

    /**
     * Remove the specified customer group from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $customerGroup = CustomerGroups::findOrFail($id);

        $customerGroup->delete();

        return redirect()->route('customer.groups.index')->with('success', 'Customer Group deleted successfully.');
    }
}
