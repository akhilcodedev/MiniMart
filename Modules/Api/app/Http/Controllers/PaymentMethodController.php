<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\PaymentMethod\Models\PaymentMethod;

class PaymentMethodController extends Controller
{ 
    /**
     * Fetch all payment methods
     *
     * @return void
     */
    public function index()
    {
        try {
            $paymentMethods = PaymentMethod::with('attributes')->get();

            return response()->json([
                'success' => true,
                'data' => $paymentMethods
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    /**
     * Fetch a specific payment method by ID
     *
     * @return void
     */
    public function show($id)
    {
        try {
            $paymentMethod = PaymentMethod::with('attributes')->find($id);

            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $paymentMethod
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment method details.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
