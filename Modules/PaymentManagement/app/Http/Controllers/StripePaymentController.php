<?php

namespace Modules\PaymentManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OrderManagement\Models\Order;
use Stripe\Stripe;
use Stripe\PaymentIntent;

use Illuminate\Support\Facades\Log;

class StripePaymentController extends Controller
{
    /**
     * Handle payment processing via Stripe and create an order if successful.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|string',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100,
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $order = Order::create([
                    'user_id' => auth()->id() ?? null,
                    'amount' => $request->amount,
                    'status' => 'paid',
                    'transaction_id' => $paymentIntent->id,
                ]);
                return response()->json(['message' => 'Payment successful', 'order' => $order]);
            } else {
                return response()->json(['error' => 'Payment failed'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Stripe Error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Stripe webhook for successful payment intents.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        $event = $request->all();

        if ($event['type'] === 'payment_intent.succeeded') {
            $paymentIntent = $event['data']['object'];
            $order = Order::where('transaction_id', $paymentIntent['id'])->first();
            if ($order) {
                $order->update(['status' => 'completed']);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
