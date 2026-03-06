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
                            <h2 class="fw-bolder fs-3">Invoice Details</h2>
                            <p><strong>Invoice Number:</strong> {{ $invoiceData['invoice_number'] }}</p>

                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Billing Address</h5>
                                    @if($billingAddress)
                                        <p>{{ $billingAddress->address_line1 }}, {{ $billingAddress->city }},
                                            {{ $billingAddress->state }} - {{ $billingAddress->postal_code }}</p>
                                    @else
                                        <p>No Billing Address Found</p>
                                    @endif
                                </div>
                                <div>
                                    <h5>Customer</h5>
                                    <p>{{ strtoupper($invoiceData['first_name']) }}
                                        {{ strtoupper($invoiceData['last_name']) }}</p>
                                    <a href="{{ route('ordermanagement.download', $invoiceData['order_id']) }}"
                                        class="btn btn-sm btn-success">Download</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex-1">
                        <div class="bg-light rounded p-6 mb-4">
                            <h4 class="fw-bold">Coupon Information</h4>
                            @if($invoiceData['coupon'])
                                <p><strong>Coupon Code:</strong> {{ $invoiceData['coupon']['code'] }}</p>
                                <p><strong>Discount:</strong> ₹{{ number_format($invoiceData['discount_amount'], 2) }}</p>
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
                                    @foreach($invoiceData['items'] as $item)
                                        <tr>
                                            <td>{{ $item['product_name'] }}</td>
                                            <td class="text-end">{{ $item['product_sku'] }}</td>
                                            <td class="text-end">{{ $item['quantity'] }}</td>
                                            <td class="text-end">₹{{ number_format($item['price'], 2) }}</td>
                                            <td class="text-end">₹{{ number_format((float) $item['total'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end">₹{{ $invoiceData['subtotal'] }}</td>
                                    </tr>
                                    @if($invoiceData['tax_type'] === 'exclusive')
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>VAT (Exclusive):</strong></td>
                                            <td class="text-end">{{ $invoiceData['vat_percentage'] }}%</td>
                                        </tr>
                                    @elseif($invoiceData['tax_type'] === 'inclusive')
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>VAT (Inclusive):</strong></td>
                                            <td class="text-end">₹{{ $invoiceData['vat_amount'] }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Shipping Cost:</strong></td>
                                        <td class="text-end">₹{{ $invoiceData['shipping_cost'] }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                                        <td class="text-end">₹{{ $invoiceData['discount_amount'] }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                        <td class="text-end fw-bold">₹{{ $invoiceData['grand_total'] }}</td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end bg-light">
                    <a href="{{ route('ordermanagement.show', $invoiceData['order_id']) }}" class="btn btn-secondary">Back
                        to Order Details</a>
                </div>
            </div>
        </div>
    </div>
@endsection