@extends('base::layouts.mt-main')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column xl:flex-row">
                    <div class="flex-1 mr-xl-8 mb-6 mb-xl-0">
                        <div class="d-flex align-items-center pb-4 border-bottom border-light">
                            <img src="{{ asset('logo.svg') }}" width="200" class="h-20 w-40 object-contain mr-4"
                                onerror="this.onerror=null; this.src='{{ asset('path/to/your/fallback/logo.png') }}'">
                        </div>

                        <div class="mt-6 mb-8">
                            <h2 class="fw-bolder fs-3">Shipping Details</h2>
                            <p><strong>Invoice:</strong> #INV-{{ str_pad($orderData['order_id'], 7, '0', STR_PAD_LEFT) }}</p>

                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Billing Address</h5>
                                    @if($billingAddress)
                                        <p>
                                            {{ $billingAddress->address_line1 }},
                                            {{ $billingAddress->city }},
                                            {{ $billingAddress->state }} -
                                            {{ $billingAddress->postal_code }}
                                        </p>
                                    @else
                                        <p>No Billing Address Found</p>
                                    @endif
                                </div>
                                <div>
                                    <h5>Issued By</h5>
                                    <p>Commerce9 FZCO, Dubai Digital Park, UAE</p>
                                </div>
                            </div>
                        </div>

                        @if($shipping)
                            <div class="bg-light rounded p-6 mb-4">
                                <h4 class="fw-bold">Shipping Information</h4>
                                <p><strong>Shipping ID:</strong> #SHP-{{ str_pad($shipping->id, 7, '0', STR_PAD_LEFT) }}</p>
                                <p><strong>Shipping Status:</strong> {{ ucfirst($shipping->status ?? 'Not Available') }}</p>
                            </div>
                        @else
                            <p>No Shipping Details Available</p>
                        @endif
                    </div>

                    <div class="mt-6 flex-1">
                        <div class="bg-light rounded p-6 mb-4">
                            <h4 class="fw-bold">Coupon Information</h4>
                            @if($orderData['coupon'])
                                <p><strong>Coupon Code:</strong> {{ $orderData['coupon']['code'] }}</p>
                                <p><strong>Discount:</strong> ₹{{ number_format($orderData['discount_amount'], 2) }}</p>
                            @else
                                <p>No Coupon Applied</p>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">SKU</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderData['items'] as $item)
                                        <tr>
                                            <td>{{ $item['product_name'] }}</td>
                                            <td class="text-end">{{ $item['product_sku'] }}</td>
                                            <td class="text-end">{{ $item['quantity'] }}</td>
                                            <td class="text-end">₹{{ number_format($item['price'], 2) }}</td>
                                            <td class="text-end">₹{{ number_format($item['total'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end">₹{{ number_format($orderData['subtotal'], 2) }}</td>
                                    </tr>
                                    @if(isset($invoiceData['taxType']) && $invoiceData['taxType'] === 'exclusive')
                                        <tr id="vat_exclusive_row">
                                            <td colspan="4" class="text-end"><strong>VAT (Exclusive):</strong></td>
                                            <td class="text-end">{{ $invoiceData['vat_percentage'] }}%</td>
                                        </tr>
                                    @endif
                                    @if(isset($invoiceData['taxType']) && $invoiceData['taxType'] === 'inclusive')
                                        <tr id="vat_inclusive_row">
                                            <td colspan="4" class="text-end"><strong>VAT (Inclusive):</strong></td>
                                            <td class="text-end">&#8377;{{ number_format($invoiceData['vat_amount_inclusive'], 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($orderData['discount_amount'] > 0)
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Coupon Discount:</strong></td>
                                            <td class="text-end text-danger">-₹{{ number_format($orderData['discount_amount'], 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                        <td class="text-end">₹{{ number_format($orderData['grand_total'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
