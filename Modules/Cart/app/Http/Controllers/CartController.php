<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Yajra\DataTables\DataTables;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $data['create_url'] = route('cart.create');
        return view('cart::index',$data);
    }

    /**
     * Fetch and return all cart products with filtering, pagination, and search.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCartProducts(Request $request){

        try {
            $start = $request->start;
            $limit = $request->length;
            $draw = $request->draw;
            $cartItemsQuery = CartItem::with(['product.productType', 'cart','cart.customer']);
            $totalData = $cartItemsQuery->count();
            if ($request->has('search') && !empty($request['search']['value'])) {
                $searchValue = $request['search']['value'];
                $cartItemsQuery->whereHas('product', function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('sku', 'like', '%' . $searchValue . '%');
                });
            }

            if ($request->has('product_status') && !empty($request['product_status'])) {
                $statusValue = $request['product_status'];
                $cartItemsQuery->whereHas('product', function ($query) use ($statusValue) {
                    $query->where('status', $statusValue);
                });
            }

            $totalFiltered = $cartItemsQuery->count();
            $cartItems = $cartItemsQuery
                ->skip($start)
                ->take($limit)
                ->get();

            $data = [];
            foreach ($cartItems as $cartItem) {
                $customerName = $cartItem->cart->customer->first_name . ' ' . $cartItem->cart->customer->last_name ?? 'NA';

                $btn = '<div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="actionMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="actionMenu">
                            <li>
                                <a href="' . route('products.edit', $cartItem->product->id) . '">
                                    <button class="dropdown-item">Edit</button>
                                </a>
                            </li>
                            <li>
                                <button class="dropdown-item deleteProduct" data-id="' . $cartItem->product->id . '">Delete</button>
                            </li>
                        </ul>
                    </div>';

                $data[] = [
                    'id' => $cartItem->id,
                    'customer_name' => $customerName,
                    'product_name' => $cartItem->product->name,
                    'sku' => $cartItem->product->sku,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'product_type' => $cartItem->product->productType->name ?? 'N/A',
                    'action' => $btn,
                ];
            }

            $json_data = [
                "draw" => intval($draw),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            ];

            return response()->json($json_data);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json([
                'error' => 'An error occurred while fetching the data',
            ]);
        }

    }

    public function create()
    {
        return view('cart::create');
    }


    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return view('cart::show');
    }

    public function edit($id)
    {
        return view('cart::edit');
    }

    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}
