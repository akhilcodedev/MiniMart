@php
    // Helper function to check permission or super admin status
    function userCan($permission) {
        // Ensure user is authenticated before checking properties/methods
        if (!auth()->check()) {
            return false;
        }
        // Super admin bypasses permission checks
        if (auth()->user()->is_super_admin == 1) {
            return true;
        }
        // Check if the user has the specific permission
        return auth()->user()->can($permission);
    }
@endphp

@extends('base::layouts.mt-main')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" data-kt-item-table-filter="search"
                                   class="form-control form-control-solid w-250px ps-13" placeholder="Search Orders" />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex align-items-center mt-2 me-5">
                            <input type="text" id="date-range-picker" class="form-control form-control-solid w-300px"
                                   placeholder="Select Date Range" readonly />
                        </div>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_items_table">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            {{-- Checkboxes are currently decorative or for potential future bulk actions. No specific permission tied yet. --}}
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                                           data-kt-check-target="#kt_items_table .form-check-input" value="1" />
                                </div>
                            </th>
                            <th class="min-w-125px">Order No</th>
                            <th class="min-w-250px">Customer Name</th>
                            <th class="min-w-250px">Email</th>
                            <th class="min-w-125px">Created Date</th>
                            <th class="min-w-125px">Order Status</th>
                            {{-- Conditionally render Actions column header --}}
                            @if(userCan('view_orders'))
                                <th class="text-end min-w-70px">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        {{-- Note: Removed the $i counter as it's not reliably unique for checkboxes if pagination/sorting is used. Best to use $order->id if needed later. --}}
                        @forelse ($orders as $order)
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        {{-- Using order id might be better if these checkboxes are used for actions --}}
                                        <input class="form-check-input select-checkbox" type="checkbox"
                                               value="{{ $order->id }}" />
                                    </div>
                                </td>
                                <td data-order-id="{{ $order->order_number }}">{{ $order->order_number }}</td>

                                <td data-customer-name="{{ $order->first_name }} - {{ $order->last_name }}">
                                    {{ $order->first_name }} {{ $order->last_name ?? 'Unknown Customer' }}</td> {{-- Combined names directly --}}

                                <td data-email="{{ $order->email }}">
                                    {{ $order->email }}</td>
                                {{-- Added data-order attribute for easier sorting/filtering if needed --}}
                                <td data-created-at="{{ $order->created_at->format('Y-m-d') }}" data-order="{{ $order->created_at->timestamp }}">
                                    {{ $order->created_at->format('Y-m-d') }}</td>
                                <td data-order-status="{{ $order->order_status ?? 'unknown' }}">
                                    @php
                                        $orderStatus = $order->order_status ?? 'new_order'; // Treat null as a specific state if needed
                                        $badgeClass = match ($orderStatus) {
                                            'pending' => 'badge badge-light-info',
                                            'payment_pending' => 'badge badge-light-warning',
                                            'fraud' => 'badge badge-light-danger',
                                            'processing' => 'badge badge-light-primary',
                                            'shipped' => 'badge badge-light-info',
                                            'delivered' => 'badge badge-light-success',
                                            'pickup_started' => 'badge badge-light-info',
                                            'cancelled' => 'badge badge-light-secondary',
                                            'holded' => 'badge badge-light-warning',
                                            'new_order' => 'badge badge-light-success', // Displaying "New Order" explicitly
                                            'unknown' => 'badge badge-light-secondary', // Handling 'unknown' if it occurs
                                            default => 'badge badge-light-secondary', // Fallback
                                        };
                                        // Assuming you have a lang file or a helper for translation
                                        $statusLabel = trans($orderStatus); // Or however you translate status slugs
                                    @endphp
                                    <span class="{{ $badgeClass }}">{{ $statusLabel }}</span>
                                </td>
                                {{-- Conditionally render Actions cell content --}}
                                @if(userCan('view_orders'))
                                    <td class="text-end">
                                        {{-- Permission check specifically for the view action --}}
                                        @if(userCan('view_orders'))
                                            <a href="{{ route('ordermanagement.show', $order->id) }}"
                                               class="btn btn-sm btn-light btn-active-light-primary me-2">
                                                {{-- Changed icon to eye for view for clarity --}}
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        @endif
                                        {{-- Add other action buttons here with their respective permission checks, e.g., cancel_orders --}}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            {{-- Adjust colspan based on whether Actions column is visible --}}
                            <td colspan="{{ userCan('view_orders') ? '7' : '6' }}" class="text-center">No orders found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
@endsection

@section('custom-js-section')
    {{-- Include moment.js if not already loaded globally --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script>
        $(document).ready(function () {

            // --- Permission Flag ---
            const canViewOrders = {{ userCan('view_orders') ? 'true' : 'false' }};

            // --- Dynamic Column Index Calculation ---
            const checkboxColIdx = 0;
            const orderNoColIdx = 1;
            const customerNameColIdx = 2;
            const emailColIdx = 3;
            const dateColIdx = 4; // Created Date column index
            const statusColIdx = 5;
            const actionsColIdx = canViewOrders ? 6 : -1; // Actions column index (if visible)

            // --- Build columnDefs ---
            let dtColumnDefs = [];
            let orderableTargets = [checkboxColIdx]; // Checkbox column non-orderable
            if (actionsColIdx !== -1) {
                orderableTargets.push(actionsColIdx); // Actions column non-orderable if visible
            }
            dtColumnDefs.push({ orderable: false, targets: orderableTargets });

            // Add type definition for the date column for correct sorting/filtering
            dtColumnDefs.push({ type: 'date', targets: dateColIdx });


            const table = $("#kt_items_table").DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                order: [[dateColIdx, 'desc']], // Default sort by Created Date desc
                columnDefs: dtColumnDefs,     // Apply dynamic column definitions
                searching: true,
                paging: true,
                info: true,
                // Ensure select-all checkbox targets only checkboxes within this table's body
                initComplete: function() {
                    KTUtil.onDOMContentLoaded(function() {
                        KTUtil.init(); // Or specific init like KTApp.initCheck(); if needed for checkboxes
                    });

                    const checkAll = this.api().table().header().querySelector('[data-kt-check="true"]');
                    if (checkAll) {
                        checkAll.addEventListener('change', e => {
                            this.api().rows().nodes().forEach(row => {
                                const checkbox = row.querySelector('.form-check-input.select-checkbox');
                                if (checkbox) {
                                    checkbox.checked = e.target.checked;
                                }
                            });
                        });
                    }
                }
            });

            // Global Search Filter
            $('[data-kt-item-table-filter="search"]').on('keyup', function () {
                table.search($(this).val()).draw();
            });

            // Date Range Picker Initialization
            $('#date-range-picker').daterangepicker({
                autoUpdateInput: false, // Input field is updated manually on apply
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD' // Display format
                },
                ranges: { // Optional: Add predefined ranges
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            // Date Range Apply Event
            $('#date-range-picker').on('apply.daterangepicker', function (ev, picker) {
                // Update the input field text
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                // Apply the filter
                filterByDate(picker.startDate, picker.endDate); // Pass moment objects
            });

            // Date Range Cancel/Clear Event
            $('#date-range-picker').on('cancel.daterangepicker', function (ev, picker) {
                // Clear the input field text
                $(this).val('');
                // Clear the filter
                filterByDate(null, null); // Pass null or empty to signify clearing
            });

            // Store the custom date filter function reference
            let dateFilterFunction;

            function filterByDate(startDate, endDate) {
                // Clear the *previous* date filter function if it exists
                const idx = $.fn.dataTable.ext.search.indexOf(dateFilterFunction);
                if (idx > -1) {
                    $.fn.dataTable.ext.search.splice(idx, 1);
                }

                // If clearing the filter, just draw and return
                if (!startDate && !endDate) {
                    table.draw();
                    return;
                }

                // Define the *new* filter function
                dateFilterFunction = function (settings, data, dataIndex) {
                    // Use the DYNAMIC date column index
                    var createdAtStr = data[dateColIdx] || ''; // Get data from the correct column
                    if (!createdAtStr) return false; // If no date, don't include in filtered results

                    // Parse the date string from the table - ensure it matches the displayed format
                    var rowDate = moment(createdAtStr, 'YYYY-MM-DD');

                    // Check if date is valid and within the selected range (inclusive)
                    return rowDate.isValid() && rowDate.isBetween(startDate, endDate, 'day', '[]');
                };

                // Add the new function and draw the table
                $.fn.dataTable.ext.search.push(dateFilterFunction);
                table.draw();
            }
        });
    </script>
@endsection
