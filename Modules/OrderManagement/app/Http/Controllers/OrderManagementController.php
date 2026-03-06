<?php

namespace Modules\OrderManagement\Http\Controllers;

use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Models\Customer;
use Modules\OrderManagement\Models\Order;
use Modules\Customer\Models\CustomerGroups;
use Modules\OrderManagement\Models\Invoice;
use Modules\OrderManagement\Models\Shipping;
use Modules\PriceRuleManagement\Models\Coupon;
use Modules\PaymentMethod\Models\PaymentMethod;
use Modules\OrderManagement\Models\OrderAddress;
use Modules\PriceRuleManagement\Models\CatalogPriceRule;


class OrderManagementController extends Controller
{
    /**
     * Define order states and their corresponding statuses.
     * This array maps a state (e.g., 'new', 'processing') to its possible database statuses and a user-friendly label.
     *
     * @var array
     */
    private static $orderStates = [
        'new' => [
            'status' => ['pending', 'payment_pending'],
            'label' => 'New',
        ],
        'processing' => [
            'status' => ['fraud', 'payment_pending', 'processing'],
            'label' => 'Processing',
        ],
        'complete' => [
            'status' => ['shipped', 'delivered', 'pickup_started'],
            'label' => 'Complete',
        ],
        'cancelled' => [
            'status' => ['cancelled'],
            'label' => 'Cancelled',
        ],
        'holded' => [
            'status' => ['holded'],
            'label' => 'Holded',
        ],
    ];

    /**
     * List all Order
     * @return void
     */
    public function index()
    {
        $orders = Order::all();
        return view('ordermanagement::index', compact('orders'));
    }

    /**
     * View order detail page
     * @param [type] $order_id
     * @return void
     */
    public function show($order_id)
    {
        try {
            $order = Order::where('id', $order_id)
                ->with([
                    'items.product',
                    'comments',
                    'paymentStatusOption',
                    'invoice',
                    'shipping',
                    'paymentMethod',
                    'addresses'
                ])
                ->first();

            if (!$order) {
                return redirect()->route('orders.index')->with('error', 'Order not found.');
            }

            $taxConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($taxConfig['value'] ?? 0);
            $taxType = $taxConfig['tax_type'] ?? null;
            $subtotal = 0;
            $itemsResponse = $order->items->map(function ($item) use (&$subtotal) {
                $finalPrice = GetFinalPrice($item->product);
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;

                return [
                    'product_image' => optional($item->product)->product_image_url,
                    'product_sku'   => $item->product_sku,
                    'product_id'    => $item->product_id,
                    'quantity'      => $item->quantity,
                    'price'         => $finalPrice,
                    'total'         => $itemTotal,
                ];
            });

            $vatAmount = 0;
            $grandTotal = $subtotal;
            if ($taxType === 'exclusive') {
                $grandTotal = calculateExclusiveTax($subtotal, $vatPercentage);
                $vatAmount = $grandTotal - $subtotal;
                $vatAmount = $vatPercentage;
            } else {
                $vatAmount = calculateInclusiveTax($subtotal, $vatPercentage);
            }
            $shippingCost = $order->shipping_cost ?? 0;
            $couponDiscountAmount = $order->total_coupon_amount ?? 0;
            $grandTotal += $shippingCost;
            $grandTotal -= $couponDiscountAmount;
            $itemCount = $order->items->count();
            $shippingAddress = $order->addresses->where('type', 'shipping')->first();
            $billingAddress = $order->addresses->where('type', 'billing')->first();
            $orderState = $this->determineOrderState($order->order_status);
            $couponData = null;
            if (!empty($order->coupon_id)) {
                $coupon = Coupon::find($order->coupon_id);
                if ($coupon) {
                    $couponData = [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'name' => $coupon->name,
                        'description' => $coupon->description,
                    ];
                }
            }

            $paymentMethod = $order->paymentMethod ? $order->paymentMethod->name : 'Unknown Payment Method';

            if ($order->paymentMethod && $order->paymentMethod->code === PaymentMethod::PAYMENT_METHOD_CODE_STRIPE) {
                $paymentMethod .= ' (Transaction ID: ' . $order->transaction_id . ')';
            }

            $paymentStatusName = $order->newPaymentStatusOption->label ?? 'No Payment Status';

            $orderData = [
                'order_id'            => $order->id,
                'order_number'        => $order->order_number,
                'customer_code'       => $order->customer_code,
                'first_name'          => strtoupper($order->first_name),
                'last_name'           => strtoupper($order->last_name),
                'created_at'          => $order->created_at->format('Y-m-d H:i:s'),
                'subtotal'            => $subtotal,
                'shipping_cost'       => $shippingCost,
                'discount_amount'     => $couponDiscountAmount,
                'vat_percentage'      => number_format($vatPercentage, 2),
                'vat_amount'          => number_format($vatAmount, 2),
                'tax_type'            => $taxType,
                'grand_total'         => $grandTotal,
                'item_count'          => $itemCount,
                'order_status'        => $order->order_status,
                'payment_status'      => $order->payment_status,
                'payment_method'      => $paymentMethod,
                'payment_status_name' => $paymentStatusName,
                'coupon'              => $couponData,
                'items'               => $itemsResponse,
            ];
            $orderData['subtotal'] = (float) $orderData['subtotal'];
            $orderData['shipping_cost'] = (float) $orderData['shipping_cost'];

            $invoice = $order->invoice;
            $shipping = $order->shipping;
            return view('ordermanagement::view', compact(
                'order',
                'orderData',
                'orderState',
                'invoice',
                'shipping',
                'shippingAddress',
                'billingAddress'
            ));
        } catch (\Exception $e) {
            return redirect()->route('ordermanagement.show', ['id' => $order_id])
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Display the shipping details for a specific order.
     *
     * @param int $order_id The ID of the order.
     * @return \Illuminate\Contracts\View\View
     */
    public function showShipping($order_id)
    {
        try {
            $order = Order::where('id', $order_id)
                ->with([
                    'items.product',
                    'comments',
                    'paymentStatusOption',
                    'invoice',
                    'shipping',
                    'addresses'
                ])
                ->first();

            if (!$order) {
                return redirect()->route('orders.index')->with('error', 'Order not found.');
            }

            $taxConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($taxConfig['value'] ?? 0);
            $taxType = $taxConfig['tax_type'] ?? null;

            $subtotal = 0;
            $itemsResponse = $order->items->map(function ($item) use (&$subtotal) {
                $finalPrice = GetFinalPrice($item->product);
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;

                return [
                    'product_name'  => optional($item->product)->name ?? 'Unknown Product',
                    'product_sku'   => $item->product_sku,
                    'product_id'    => $item->product_id,
                    'quantity'      => $item->quantity,
                    'price'         => $finalPrice,
                    'total'         => $itemTotal,
                ];
            });

            $shippingCost = $order->shipping_cost ?? 0;
            $couponDiscountAmount = $order->total_coupon_amount ?? 0;
            $grandTotal = $subtotal + $shippingCost - $couponDiscountAmount;
            $itemCount = $order->items->count();
            $shippingAddress = $order->addresses->where('type', 'shipping')->first();
            $billingAddress = $order->addresses->where('type', 'billing')->first();
            $orderState = $this->determineOrderState($order->order_status);
            $couponData = null;
            if (!empty($order->coupon_id)) {
                $coupon = Coupon::find($order->coupon_id);
                if ($coupon) {
                    $couponData = [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'name' => $coupon->name,
                        'description' => $coupon->description,
                    ];
                }
            }

            $orderData = [
                'order_id'        => $order->id,
                'order_number'    => $order->order_number,
                'customer_code'   => $order->customer_code,
                'first_name'      => strtoupper($order->first_name),
                'last_name'       => strtoupper($order->last_name),
                'created_at'      => $order->created_at->format('Y-m-d H:i:s'),
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shippingCost,
                'discount_amount' => $couponDiscountAmount,
                'grand_total'     => $grandTotal,
                'item_count'      => $itemCount,
                'order_status'    => $order->order_status,
                'payment_status'  => $order->payment_status,
                'coupon'          => $couponData,
                'items'           => $itemsResponse,
            ];

            $invoice = $order->invoice;
            $shipping = $order->shipping;

            return view('ordermanagement::shipping_details', compact(
                'order',
                'orderData',
                'orderState',
                'invoice',
                'shipping',
                'shippingAddress',
                'billingAddress'
            ));
        } catch (\Exception $e) {
            return redirect()->route('ordermanagement.shipping_details', ['id' => $order_id])
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }



    /**
     * Determine the order state based on its status.
     *
     * @param string $orderStatus
     * @return string
     */
    private function determineOrderState($orderStatus)
    {
        foreach (self::$orderStates as $state => $data) {
            if (in_array($orderStatus, $data['status'])) {
                return $state;
            }
        }
        return 'new';
    }


    /**
     * Cancel order
     * @param [type] $order_id
     * @return void
     */
    public function cancel($order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->order_status = 'cancelled';
        $order->save();
        return redirect()->route('ordermanagement.show', ['id' => $order_id])->with('success', 'Order cancelled successfully');
    }

    /**
     * Hold order
     * @param [type] $order_id
     * @return void
     */
    public function hold($order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->order_status = 'holded';
        $order->save();
        return redirect()->route('ordermanagement.show', ['id' => $order_id])->with('success', 'Order holded successfully');
    }

    /**
     * Unhold order
     * @param [type] $order_id
     * @return void
     */
    public function unhold($order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->order_status = 'processing';
        $order->save();

        return redirect()->route('ordermanagement.show', ['id' => $order_id])->with('success', 'Order unheld successfully');
    }
    /**
     * Ship order
     * @param [type] $order_id
     * @return void
     */
    public function ship($order_id)
    {
        $order = Order::with(['items'])->findOrFail($order_id);

        $order->order_status = 'shipped';
        $order->save();

        $totalAmount = $order->items->sum('total');

        $invoiceNumber = 'INV-' . str_pad($order->id, 8, '0', STR_PAD_LEFT);
        $invoice = Invoice::updateOrCreate(
            ['order_id' => $order->id],
            [
                'invoice_number' => $invoiceNumber,
                'status' => '',
                'order_created_at' => $order->created_at,
            ]
        );

        $shipping = Shipping::updateOrCreate(
            ['order_id' => $order->id],
            [
                'customer_id' => $order->customer_id,
                'total_amount' => $totalAmount,
                'status' => 'shipped',
            ]
        );

        return redirect()->route('ordermanagement.show', ['id' => $order_id])->with('success', 'Order shipped successfully. Invoice and shipping details generated.');
    }

    /**
     * Generate and load invoice blade
     * @param [type] $order_id
     * @return void
     */

    public function invoice($order_id)
    {
        try {
            $order = Order::with([
                'items.product',
                'addresses',
                'comments',
                'paymentStatusOption',
                'invoice'
            ])->findOrFail($order_id);

            $subtotal = 0;
            $itemsResponse = $order->items->map(function ($item) use (&$subtotal) {
                $finalPrice = GetFinalPrice($item->product);
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;
                return [
                    'product_name' => optional($item->product)->name ?? 'Unknown Product',
                    'product_sku'  => $item->product_sku,
                    'product_id'   => $item->product_id,
                    'quantity'     => $item->quantity,
                    'price'        => $finalPrice,
                    'total'        => number_format($itemTotal, 2),
                ];
            });

            $shippingCost = $order->shipping_cost ?? 0;
            $couponDiscountAmount = $order->total_coupon_amount ?? 0;

            $taxConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($taxConfig['value'] ?? 0);
            $taxType = $taxConfig['tax_type'] ?? null;

            if ($taxType === 'exclusive') {
                $vatAmount = ($subtotal * $vatPercentage) / 100;
                $grandTotal = $subtotal + $vatAmount + $shippingCost - $couponDiscountAmount;
            } else {
                $vatAmount = calculateInclusiveTax($subtotal, $vatPercentage);
                $grandTotal = $subtotal + $shippingCost - $couponDiscountAmount;
            }

            $shippingAddress = optional($order->addresses->where('type', 'shipping')->first());
            $billingAddress = optional($order->addresses->where('type', 'billing')->first());
            $couponData = null;
            if (!empty($order->coupon_id)) {
                $coupon = Coupon::find($order->coupon_id);
                if ($coupon) {
                    $couponData = [
                        'id'          => $coupon->id,
                        'code'        => $coupon->code,
                        'name'        => $coupon->name,
                        'description' => $coupon->description,
                    ];
                }
            }

            $invoiceData = [
                'invoice_id'      => optional($order->invoice)->id ?? null,
                'invoice_number'  => optional($order->invoice)->invoice_number ?? 'N/A',
                'order_id'        => $order->id,
                'order_number'    => $order->order_number,
                'customer_code'   => $order->customer_code,
                'first_name'      => strtoupper($order->first_name),
                'last_name'       => strtoupper($order->last_name),
                'created_at'      => $order->created_at->format('Y-m-d H:i:s'),
                'subtotal'        => number_format($subtotal, 2),
                'shipping_cost'   => number_format($shippingCost, 2),
                'discount_amount' => number_format($couponDiscountAmount, 2),
                'vat_percentage'  => number_format($vatPercentage, 2),
                'vat_amount'      => number_format($vatAmount, 2),
                'grand_total'     => number_format($grandTotal, 2),
                'item_count'      => $order->items->count(),
                'payment_status'  => $order->payment_status,
                'coupon'          => $couponData,
                'items'           => $itemsResponse,
                'tax_type'        => $taxType,
            ];

            return view('ordermanagement::invoice', compact(
                'order',
                'invoiceData',
                'shippingAddress',
                'billingAddress'
            ));
        } catch (\Exception $e) {
            return redirect()->route('ordermanagement.invoice', ['id' => $order_id])
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Display the details of a specific customer.
     *
     * @param int $id The ID of the customer to display.
     * @return \Illuminate\Contracts\View\View
     */
    public function showDetails($id)
    {
        $customer = Customer::with('addresses')->findOrFail($id);
        $currentGroup = DB::table('customer_groups_maps')
            ->where('customer_id', $customer->id)
            ->join('customer_groups', 'customer_groups.id', '=', 'customer_groups_maps.group_id')
            ->select('customer_groups.*')
            ->first();
        $groups = CustomerGroups::all();

        return view('ordermanagement::customer_details', compact('customer', 'currentGroup', 'groups'));
    }

    /**
     * Generalte and load invoce blade
     * @param [type] $order_id
     * @return void
     */
    public function generateInvoice($order_id)
    {
        try {
            $order = Order::with(['addresses', 'items', 'comments', 'paymentStatusOption'])->find($order_id);

            if (!$order) {
                abort(404, 'Order not found');
            }

            $taxConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($taxConfig['value'] ?? 0);
            $taxType = $taxConfig['tax_type'] ?? null;

            $invoice = Invoice::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'invoice_number' => 'INV-' . time(),
                    'status'         => 'pending',
                    'order_created_at' => $order->created_at,
                ]
            );
            $subtotal = 0;
            $itemsResponse = $order->items->map(function ($item) use (&$subtotal) {
                $finalPrice = GetFinalPrice($item->product);
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;
            });

            $vatAmount = 0;
            if ($taxType === 'exclusive') {
                $vatAmount = ($subtotal * $vatPercentage) / 100;
                $grandTotal = $subtotal + $vatAmount;
            } else {
                $vatAmount = ($subtotal * $vatPercentage) / (100 + $vatPercentage);
                $grandTotal = $subtotal;
                dd($grandTotal);
            }


            $invoiceData = [
                'invoice_id'      => $invoice->id,
                'invoice_number'  => $invoice->invoice_number,
                'order_id'        => $order->id,
                'order_number'    => $order->order_number,
                'customer_code'   => $order->customer_code,
                'first_name'      => strtoupper($order->first_name),
                'last_name'       => strtoupper($order->last_name),
                'created_at'      => $order->created_at->format('Y-m-d H:i:s'),
                'subtotal'        => number_format($subtotal, 2),
                'vat_percentage'  => isset($vatPercentage) ? number_format($vatPercentage, 2) : '0.00',
                'vat_amount'      => number_format($vatAmount, 2),
                'shipping_cost'   => number_format($shippingCost, 2),
                'discount_amount' => number_format($couponDiscountAmount, 2),
                'grand_total'     => number_format($grandTotal, 2),
                'item_count'      => $order->items->count(),
                'payment_status'  => $order->payment_status,
                'coupon'          => $couponData,
                'items'           => $itemsResponse,
            ];

            return view('ordermanagement::invoice', compact(
                'order',
                'invoice',
                'invoiceData',
                'shippingAddress',
                'billingAddress'
            ));
        } catch (\Exception $e) {
            return redirect()->route('ordermanagement.invoice', ['id' => $order_id])
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Download invoice
     * @param [type] $orderId
     * @return void
     */
    public function downloadInvoice($orderId)
    {
        try {
            $order = Order::with([
                'items.product',
                'addresses',
                'comments',
                'paymentStatusOption',
                'invoice'
            ])->findOrFail($orderId);

            $subtotal = $order->items->sum(fn($item) => $item->product ? $item->product->price * $item->quantity : 0);
            $shippingCost = $order->shipping_cost ?? 0;
            $couponDiscountAmount = $order->total_coupon_amount ?? 0;

            $taxConfig = getCountryConfigData('web_configuration_tax_value');
            $vatPercentage = floatval($taxConfig['value'] ?? 0);
            $taxType = $taxConfig['tax_type'] ?? null;

            $subtotal = 0;
            $itemsResponse = $order->items->map(function ($item) use (&$subtotal) {
                $finalPrice = GetFinalPrice($item->product);
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;
            });
            if ($taxType === 'exclusive') {
                $vatAmount = ($subtotal * $vatPercentage) / 100;
                $grandTotal = $subtotal + $vatAmount + $shippingCost - $couponDiscountAmount;
            } else {
                $vatAmount = calculateInclusiveTax($subtotal, $vatPercentage);
                $grandTotal = $subtotal + $shippingCost - $couponDiscountAmount;
            }

            $shippingAddress = optional($order->addresses->where('type', 'shipping')->first());
            $billingAddress = optional($order->addresses->where('type', 'billing')->first());

            $couponData = optional(Coupon::find($order->coupon_id), function ($coupon) {
                return [
                    'id'          => $coupon->id,
                    'code'        => $coupon->code,
                    'name'        => $coupon->name,
                    'description' => $coupon->description,
                ];
            });

            $invoiceData = [
                'invoice_id'      => optional($order->invoice)->id ?? null,
                'invoice_number'  => optional($order->invoice)->invoice_number ?? 'N/A',
                'order_id'        => $order->id,
                'order_number'    => $order->order_number,
                'customer_code'   => $order->customer_code,
                'first_name'      => strtoupper($order->first_name),
                'last_name'       => strtoupper($order->last_name),
                'created_at'      => $order->created_at->format('Y-m-d H:i:s'),
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shippingCost,
                'discount_amount' => $couponDiscountAmount,
                'vat_percentage'  => $vatPercentage,
                'vat_amount'      => $vatAmount,
                'grand_total'     => $grandTotal,
                'item_count'      => $order->items->count(),
                'payment_status'  => $order->payment_status,
                'coupon'          => $couponData,
                'tax_type'        => $taxType,
                'items'           => $order->items->map(fn($item) => [
                    'product_name' => optional($item->product)->name ?? 'Unknown Product',
                    'product_sku'  => optional($item->product)->sku ?? 'N/A',
                    'quantity'     => $item->quantity,
                    'price'        => $item->product->price ?? 0,
                    'total'        => $item->product->price * $item->quantity,
                ]),
            ];

            $pdf = PDF::loadView('ordermanagement::pdf.invoice_pdf', compact(
                'order',
                'invoiceData',
                'shippingAddress',
                'billingAddress'
            ));

            return $pdf->download('invoice_' . $orderId . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate invoice. Please try again.');
        }
    }
}
