<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ShippingMethode\Models\ShippingMethod;

class ShippingMethodController extends Controller
{

     /**
      * Fetch all active shipping methods
      *
      * @return json
      */
     public function index()
     {
        try {
            $shippingMethods = ShippingMethod::with('attributes')
                ->where('status', 1)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $shippingMethods->map(function ($method) {
                    $costAttribute = $method->attributes->firstWhere('name', 'Cost');
                    $cost = $costAttribute ? $costAttribute->value : null;

                    return [
                        'id' => $method->id,
                        'name' => $method->name,
                        'code' => $method->code,
                        'cost' => $cost,
                    ];
                }),
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch shipping methods.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
     }

     /**
      * Fetch a single shipping method by ID
      *
      * @return json
      */
     public function show($id)
     {
        try {
            $shippingMethod = ShippingMethod::with('attributes')->find($id);

            if (!$shippingMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipping Method not found',
                ], 404);
            }

            $costAttribute = $shippingMethod->attributes->firstWhere('name', 'Cost');
            $cost = $costAttribute ? $costAttribute->value : null;

            $responseData = [
                'id' => $shippingMethod->id,
                'name' => $shippingMethod->name,
                'code' => $shippingMethod->code,
                'cost' => $cost,
            ];
            return response()->json([
                'success' => true,
                'data' => $responseData,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch the shipping method.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
     }
     /**
      * custom logic
      *
      * @param Request $request
      * @return json
      */
     public function calculateShipping(Request $request)
     {
         try {
             $request->validate([
                 'city' => 'required|string',
                 'product_count' => 'required|integer|min:1',
             ]);

             $city = $request->city;
             $productCount = $request->product_count;
             if ($productCount < 5) {
                 $baseShippingFee = 10;
             } elseif ($productCount <= 10) {
                 $baseShippingFee = 20;
             } else {
                 $baseShippingFee = 30;
             }
             $additionalFee = 0;
             $additionalCities = ['CityA', 'CityB'];
             if (in_array($city, $additionalCities)) {
                 $additionalFee = 5;
             }
             $totalShippingCost = $baseShippingFee + $additionalFee;

             return response()->json([
                 'success' => true,
                 'data' => [
                     'base_shipping_fee' => $baseShippingFee,
                     'additional_fee' => $additionalFee,
                     'total_shipping_cost' => $totalShippingCost,
                 ],
                 'message' => 'Shipping cost calculated successfully.',
             ], 200);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to calculate shipping cost.',
                 'error' => $e->getMessage(),
             ], 500);
         }
     }
}
