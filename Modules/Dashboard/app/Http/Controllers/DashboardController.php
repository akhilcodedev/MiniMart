<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Products\Models\Product;
use Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\Redirect;
use Modules\OrderManagement\Models\Order;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalUsers   = Customer::count();
        $totalProducts = Product::count();
        $totalOrders   = Order::count();

        $newCustomers = Customer::orderBy('created_at', 'desc')->limit(5)->get();

        $ordersByMonth = Order::select(
            DB::raw('DATE_FORMAT(created_at, "%b") as month'),
            DB::raw('count(*) as count'))
            ->groupBy('month')->orderBy(DB::raw('MIN(created_at)'))->get();

        $ordersByDate = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count') )
            ->groupBy('date')->orderBy('date')->get();

        $productsCategory = DB::table('category_products')
            ->join('categories', 'categories.id', '=', 'category_products.category_id')->whereNull('categories.parent_id')
            ->select('categories.name as category', DB::raw('count(*) as count'))->groupBy('categories.name')->get();

        $recentOrders = Order::with('customerData')->orderBy('created_at', 'desc')->limit(5)->get();


        return view('dashboard::dashboard', compact(
            'totalUsers',
            'totalProducts',
            'totalOrders',
            'ordersByMonth',
            'ordersByDate',
            'productsCategory',
            'recentOrders',
            'newCustomers',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('dashboard::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Log out the authenticated user and redirect to the homepage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
