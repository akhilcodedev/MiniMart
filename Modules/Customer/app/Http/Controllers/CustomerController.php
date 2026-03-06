<?php

namespace Modules\Customer\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerGroups;
use Modules\Customer\Models\CustomerGroupMap;
use Modules\WebConfigurationManagement\Models\Country;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::with('addresses')->get();
        $countries = Country::all();
        return view('customer::index', compact('customers', 'countries'));
    }


    /**
     * Filter and return customers with optional search, status, and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('is_active', $request->status == 'active' ? 1 : 0);
        }

        $pageSize = $request->has('page_size') ? $request->page_size : 10;
        $customers = $query->paginate($pageSize);

        if ($request->ajax()) {
            $customersHtml = view('customers._customer_list', compact('customers'))->render();
            $paginationHtml = view('pagination::bootstrap-4', ['paginator' => $customers])->render();

            return response()->json([
                'customersHtml' => $customersHtml,
                'paginationHtml' => $paginationHtml,
            ]);
        }

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
        public function create()
    {
        $groups = CustomerGroups::all();
        $countries = Country::all();
        return view('customer::create', compact('groups', 'countries'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string',
            'last_name'       => 'nullable|string',
            'email'           => 'required|email|unique:customers,email',
            'phone'           => 'nullable|string|min:10|max:15',
            'dial_code'       => 'nullable|string',
            'customer_code'   => 'required|string|unique:customers,customer_code',
            'password'        => 'required|string',
            'is_active'       => 'required|boolean',
            'address'         => 'required|array|min:1',
            'address.*.address_line1' => 'required|string',
            'address.*.city'          => 'required|string',
            'address.*.state'         => 'required|string',
            'address.*.postal_code'   => 'required|string',
            'address.*.country'       => 'required|string',
            'address.*.type'          => 'required|in:billing,shipping',
            'group_id'        => 'required|exists:customer_groups,id',
        ]);

        $customer = Customer::create([
            'first_name'    => $validated['first_name'],
            'last_name'     => $validated['last_name'],
            'email'         => $validated['email'],
            'dial_code'     => $validated['dial_code'],
            'phone'         => $validated['phone'],
            'customer_code' => $validated['customer_code'],
            'password'      => Hash::make($validated['password']),
            'is_active'     => $validated['is_active'],
        ]);

        foreach ($validated['address'] as $address) {
            $customer->addresses()->create($address);
        }

        CustomerGroupMap::create([
            'customer_id' => $customer->id,
            'group_id'    => $validated['group_id'],
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $customer = Customer::with('addresses')->findOrFail($id);
        $currentGroup = DB::table('customer_groups_maps')
            ->where('customer_id', $customer->id)
            ->join('customer_groups', 'customer_groups.id', '=', 'customer_groups_maps.group_id')
            ->select('customer_groups.*')
            ->first();
        $groups = CustomerGroups::all();
        return view('customer::show', compact('customer', 'currentGroup', 'groups'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $customer = Customer::with('addresses')->findOrFail($id);
        $groups = CustomerGroups::all();
        $countries = Country::all();

        $currentGroup = DB::table('customer_groups_maps')
            ->where('customer_id', $customer->id)
            ->join('customer_groups', 'customer_groups.id', '=', 'customer_groups_maps.group_id')
            ->select('customer_groups.*')
            ->first();

        if (!$currentGroup) {
            $generalGroup = CustomerGroups::firstOrCreate(['name' => 'General']);

            DB::table('customer_groups_maps')->insertOrIgnore([
                'customer_id' => $customer->id,
                'group_id' => $generalGroup->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $currentGroup = $generalGroup;
        }

        return view('customer::edit', compact('customer', 'currentGroup', 'groups', 'countries'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'required|email|unique:customers,email,' . $id,
            'dial_code'  => 'required|string',
            'phone'      => 'nullable|string|min:10|max:15',
            'is_active'  => 'required|boolean',
            'group'      => 'required|exists:customer_groups,id',
            'address'    => 'required|array|min:1',
            'address.*.address_line1' => 'required|string',
            'address.*.city'          => 'required|string',
            'address.*.state'         => 'required|string',
            'address.*.postal_code'   => 'required|string',
            'address.*.country'       => 'required|string',
            'address.*.type'          => 'required|in:billing,shipping',
        ]);

        $customer = Customer::findOrFail($id);

        $customer->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'dial_code'  => $request->dial_code,
            'phone'      => $request->phone,
            'is_active'  => $request->is_active,
        ]);

        $groupId = $request->group;

        DB::table('customer_groups_maps')->updateOrInsert(
            ['customer_id' => $customer->id],
            ['group_id' => $groupId]
        );

        if ($request->has('address') && is_array($request->address)) {
            foreach ($request->address as $address) {
                if (isset($address['id']) && !empty($address['id'])) {
                    $customer->addresses()->where('id', $address['id'])->update($address);
                } else {
                    $customer->addresses()->create($address);
                }
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->json('ids');

            if (empty($ids) || !is_array($ids)) {
                return response()->json(['success' => false, 'message' => 'Invalid customer IDs provided'], 400);
            }
            Customer::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Customers deleted successfully']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Error deleting customers'], 500);
        }
    }
}
