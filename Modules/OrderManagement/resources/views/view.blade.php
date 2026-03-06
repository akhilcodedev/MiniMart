@extends('base::layouts.mt-main')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Order details page-->
            <div class="d-flex flex-column gap-7 gap-lg-10">
               
                <div class="d-flex flex-wrap justify-content-between gap-5 gap-lg-10">
                    <!--begin::: Tabs-->
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-lg-n2 me-auto"
                        role="tablist">
                        <a href="{{ route('ordermanagement.index') }}" class="btn btn-secondary btn-sm">Back</a>

                        <!--begin::: Tab item-->
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab"
                                href="#kt_ecommerce_sales_order_summary" aria-selected="true" role="tab">Order Summary</a>
                        </li>

                    </ul>
                    <!--end::: Tabs-->

                    <!--begin::Buttons-->
                    <div class="d-flex align-items-center gap-2 flex-wrap">

                        

                        <!-- Shipping Button (make sure it’s clickable) -->
                        <a href="{{ route('order.ship', ['order' => $order->id]) }}"
                            class="btn btn-info btn-sm d-inline-flex align-items-center rounded" id="shippingButton">
                            <i class="fas fa-truck me-1"></i>
                            <span class="d-none d-sm-inline">Shipping</span>
                        </a>
                        @if ($orderState !== 'cancelled' && $orderState !== 'complete')
                            <!-- Cancel Button -->
                            <a href="#" class="btn btn-danger btn-sm d-inline-flex align-items-center rounded"
                                onclick="confirmCancel('{{ route('order.cancel', ['order' => $order->id]) }}')">
                                <i class="fas fa-times-circle me-1"></i>
                                <span class="d-none d-sm-inline">Cancel</span>
                            </a>
                        @endif

                        @if ($orderState !== 'holded')
                            <!-- Hold Button -->
                            <a href="#" class="btn btn-warning btn-sm d-inline-flex align-items-center rounded"
                                onclick="confirmHold('{{ route('order.hold', ['order' => $order->id]) }}')">
                                <i class="fas fa-pause me-1"></i>
                                <span class="d-none d-sm-inline">Hold</span>
                            </a>
                        @endif

                        @if ($orderState == 'holded')
                            <!-- Unhold Button -->
                            <a href="#" class="btn btn-success btn-sm d-inline-flex align-items-center rounded"
                                onclick="confirmUnhold('{{ route('order.unhold', ['order' => $order->id]) }}')">
                                <i class="fas fa-play me-1"></i>
                                <span class="d-none d-sm-inline">Unhold</span>
                            </a>
                        @endif

                    </div>
                    <!--end::Buttons-->

                </div>
                <!--begin::Order summary-->
                <div class="d-flex flex-column flex-xl-row gap-7 gap-lg-10">
                    <!--begin::Order details-->
                    <div class="card card-flush py-4 flex-row-fluid">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Order Details (#{{ $order->order_number }})</h2>
                            </div>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                    <tbody class="fw-semibold text-gray-600">
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-calendar fs-2 me-2"></i> Order Date
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">{{ $order->created_at}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-wallet fs-2 me-2"></i>Payment Method
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">
                                                {{ trim($orderData['payment_method']) }}
                                            </td>
                                        </tr>                                        

                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-truck fs-2 me-2"></i>Shipping Method
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">
                                                {{ ucfirst(trans($order->shipping_method_name ?? 'free')) }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-truck fs-2 me-2"></i>Order Status
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">
                                                {{ ucfirst(trans($orderState ?? 'new')) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!--end::Table-->
                            </div>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Order details-->

                    <!--begin::Customer details-->
                    <div class="card card-flush py-4  flex-row-fluid">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Customer Details</h2>
                            </div>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                    <tbody class="fw-semibold text-gray-600">
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-profile-circle fs-2 me-2"></i>Customer
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <!--begin:: Avatar -->
                                                    <a href="{{ route('ordermanagement.customer.details', $order->customer_id) }}"
                                                        class="view-customer-details">
                                                        <div class="symbol-label">
                                                            <i class="fas fa-eye fa-2x"></i>
                                                        </div>
                                                    </a>

                                                    {{ $order->first_name . "-" . $order->last_name}}</a>
                                                    <!--end::Name-->
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-sms fs-2 me-2"></i>Email
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">
                                                <a href="" class="text-gray-600 text-hover-primary">
                                                    {{ $order->email}}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-phone fs-2 me-2"></i>Phone
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">{{ $order->phone}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!--end::Table-->
                            </div>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Customer details-->
                    <!--begin::Documents-->
                    <div class="card card-flush py-4 flex-row-fluid">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Documents</h2>
                            </div>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                    <tbody class="fw-semibold text-gray-600">
                                        @if($orderState === 'complete')
                                            <tr>
                                                <td class="text-muted">
                                                    <a href="{{ route('order.invoice', ['order' => $order->id]) }}"
                                                        class="d-flex align-items-center text-gray-600 text-hover-primary">
                                                        <i class="ki-outline ki-devices fs-2 me-2"></i> Invoice
                                                        <span class="ms-1" data-bs-toggle="tooltip"
                                                            aria-label="View the invoice generated by this order.">
                                                            <i class="ki-outline ki-information-5 text-gray-500 fs-6"></i>
                                                        </span>
                                                    </a>
                                                </td>
                                                <td class="fw-bold text-end">
                                                    @if($invoice)
                                                        <a href="{{ route('order.invoice', ['order' => $order->id]) }}"
                                                            class="text-gray-600 text-hover-primary">
                                                            #{{ $invoice->invoice_number }}
                                                        </a>
                                                    @else
                                                        No Invoice Yet
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                {{-- <td colspan="2" class="text-end">
                                                    <a href="{{ route('order.invoice', ['order' => $order->id]) }}"
                                                        class="btn btn-primary">
                                                        Generate Invoice
                                                    </a>
                                                </td> --}}
                                            </tr>
                                        @endif
                                        @if($orderState === 'complete')
                                            <tr>
                                                <td class="text-muted">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-outline ki-truck fs-2 me-2"></i> Shipping
                                                        <span class="ms-1" data-bs-toggle="tooltip"
                                                            aria-label="View the shipping manifest generated by this order."
                                                            data-bs-original-title="View the shipping manifest generated by this order."
                                                            data-kt-initialized="1">
                                                            <i class="ki-outline ki-information-5 text-gray-500 fs-6"></i>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-end">
                                                    @if($order->shipping)
                                                        <a href="{{ route('ordermanagement.shipping_details', $order->id) }}"
                                                            class="btn btn-icon btn-color-muted btn-active-color-primary btn-sm btn-circle"
                                                            data-bs-toggle="tooltip" title="View Shipping Details">
                                                            <i class="ki-outline ki-eye fs-2"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-gray-600">No Shipping Details</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                        </tr>
                                    </tbody>
                                </table>
                                <!--end::Table-->
                            </div>
                        </div>
                        
                        <!--end::Card body-->
                    </div>
                    <!--end::Documents-->
                </div>
                <!--end::Order summary-->

                <!--begin::Tab content-->
                <div class="tab-content">
                    <!--begin::Tab pane-->
                    <div class="tab-pane fade show active" id="kt_ecommerce_sales_order_summary" role="tab-panel">
                        <!--begin::Orders-->
                        <div class="d-flex flex-column gap-7 gap-lg-10">
                            <div class="d-flex flex-column flex-xl-row gap-7 gap-lg-10">

                                <div class="card card-flush py-4 flex-row-fluid position-relative">
                                    <!--begin::Background-->
                                    <div
                                        class="position-absolute top-0 end-0 bottom-0 opacity-10 d-flex align-items-center me-5">
                                        <i class="ki-solid ki-delivery" style="font-size: 13em">
                                        </i>
                                    </div>
                                    <!--end::Background-->

                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Shipping Address</h2>
                                        </div>
                                    </div>
                                    <!--end::Card header-->
                                    <div class="card-body pt-0">
                                        @if ($billingAddress)
                                            <p>{{ $billingAddress->first_name }} {{ $billingAddress->last_name }}</p>
                                            <p>{{ $billingAddress->address_line1 }}</p>
                                            @if ($billingAddress->address_line2)
                                                <p>{{ $billingAddress->address_line2 }}</p>
                                            @endif
                                            <p>{{ $billingAddress->city }}, {{ $billingAddress->state }},
                                                {{ $billingAddress->postal_code }}</p>
                                            <p>{{ $billingAddress->country }}</p>
                                        @endif

                                        @if ($shippingAddress)
                                            <p>{{ $shippingAddress->first_name }} {{ $shippingAddress->last_name }}</p>
                                            <p>{{ $shippingAddress->address_line1 }}</p>
                                            @if ($shippingAddress->address_line2)
                                                <p>{{ $shippingAddress->address_line2 }}</p>
                                            @endif
                                            <p>{{ $shippingAddress->city }}, {{ $shippingAddress->state }},
                                                {{ $shippingAddress->postal_code }}</p>
                                            <p>{{ $shippingAddress->country }}</p>
                                        @endif

                                    </div>
                                </div>
                                <!--end::Shipping address-->
                            </div>

                            <div class="card card-flush py-4 flex-row-fluid position-relative">
                                <!--begin::Background-->
                                <div
                                    class="position-absolute top-0 end-0 bottom-0 opacity-10 d-flex align-items-center me-5">

                                </div>
                                <!--end::Background-->

                                <!--begin::Card header-->
                                <div class="card-header">
                                    <div class="card-title">
                                        <h2>Coupon Information</h2>
                                    </div>
                                </div>
                                <!--end::Card header-->
                                <div class="card-body pt-0">
                                    <div class="p-4 border rounded mb-5" style="background-color: #f9f9f9;">
                                        <h5 class="text-dark fw-bold mb-3"></h5>

                                        @if($order->coupon)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-gray-600">Coupon Applied:</div>
                                                <div class="text-success fw-semibold">Yes</div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-gray-600">Coupon Code:</div>
                                                <div class="text-dark fw-semibold">{{ $order->coupon->code }}</div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-gray-600">Coupon Discount Amount:</div>
                                                <div class="text-dark fw-semibold">
                                                    {{ number_format($order->total_coupon_amount, 2) }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-gray-600">Coupon Applied:</div>
                                                <div class="text-danger fw-semibold">No</div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-gray-600">Coupon Discount Amount:</div>
                                                <div class="text-dark fw-semibold">{{ number_format(0, 2) }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!--end::Shipping address-->
                            </div>
                            <!--begin::Product List-->
                            <div class="card card-flush py-4 flex-row-fluid overflow-hidden">
                                <!--begin::Card header-->
                                <div class="card-header">
                                    <div class="card-title">
                                        <h2>Order #{{ $order->order_number}}</h2>
                                    </div>
                                </div>
                                <!--end::Card header-->

                                <!--begin::Card body-->
                                <div class="card card-flush py-4 flex-row-fluid overflow-hidden">
                                    <div class="card-body pt-0">

                                        <!-- Order Table -->
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle fs-6 gy-5 mb-0">
                                                <thead>
                                                    <tr class="text-start bg-light text-dark fw-bold fs-7 text-uppercase">
                                                        <th class="min-w-200px">Product</th>
                                                        <th class="min-w-150px text-end">SKU</th>
                                                        <th class="min-w-100px text-end">Quantity</th>
                                                        <th class="min-w-150px text-end">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $subTotal = 0;
                                                    @endphp

                                                    @foreach($orderData['items'] as $item)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <a href="#" class="symbol symbol-50px me-5">
                                                                        <span class="symbol-label"
                                                                            style="background-image:url('{{ asset('public/' . $item['product_image']) }}'); background-size: cover; background-position: center;"></span>
                                                                    </a>
                                                                    <div class="fw-bold text-gray-600">
                                                                        {{ $item['product_name'] ?? 'Unknown Product' }}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                {{ $item['product_id'] ?? 'N/A' }}
                                                            </td>
                                                            <td class="text-end">
                                                                {{ $item['quantity'] }}
                                                            </td>
                                                            <td class="text-end">
                                                                ₹{{ number_format($item['price'], 2) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach


                                                    <tr class="bg-light">
                                                        <td colspan="3" class="fs-5 text-dark text-end fw-semibold">Subtotal
                                                        </td>
                                                        <td class="text-dark fs-5 text-end fw-bold">
                                                            ₹{{ number_format($orderData['subtotal'], 2) }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" class="fs-5 text-dark text-end fw-semibold">Shipping
                                                            Cost</td>
                                                        <td class="text-dark fs-5 text-end fw-bold">
                                                            ₹{{ number_format($orderData['shipping_cost'], 2) }}</td>
                                                    </tr>
                                                    <tr class="bg-light">
                                                        <td colspan="3" class="fs-5 text-dark text-end fw-semibold">Coupon
                                                            Discount Amount</td>
                                                        <td class="text-dark fs-5 text-end fw-bold">
                                                            ₹{{ number_format($orderData['discount_amount'], 2) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" class="fs-5 text-dark text-end fw-semibold">Tax</td>
                                                        <td class="text-dark fs-5 text-end fw-bold">
                                                            @if($orderData['tax_type'] === 'exclusive')
                                                                {{ number_format($orderData['vat_amount'], 2) }}%
                                                            @else
                                                                ₹{{ number_format($orderData['vat_amount'], 2) }}
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan="3" class="fs-4 text-dark text-end fw-bolder">Grand
                                                            Total</td>
                                                        <td class="text-dark fs-4 text-end fw-bolder">
                                                            ₹{{ number_format(max(0, (float) $orderData['grand_total']), 2) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>

                                <!--end::Card body-->
                            </div>
                            <!--end::Product List-->
                        </div>
                        <!--end::Orders-->
                    </div>
                    <!--end::Tab pane-->

                    <!--begin::Tab pane-->

                    <!--end::Tab pane-->
                </div>
                <!--end::Tab content-->
            </div>
            <!--end::Order details page-->
        </div>
        <!--end::Container-->
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const invoiceButton = document.getElementById('invoiceButton');
        const shippingButton = document.getElementById('shippingButton');

        // Listen for the shipping button click
        shippingButton.addEventListener('click', function () {
            // Show the invoice button after the shipping button is clicked
            invoiceButton.style.display = 'inline-flex';
        });
    });

    function confirmCancel(url) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to cancel this order?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    function confirmHold(url) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to put this order on hold?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, put it on hold!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    function confirmUnhold(url) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to release this order from hold?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, release it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>