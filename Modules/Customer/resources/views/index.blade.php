@extends('base::layouts.mt-main')

{{-- Include these in your main layout's <head> or here if not already included --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/daterangepicker/3.1/daterangepicker.css" />
{{-- Make sure jQuery is loaded before these scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/daterangepicker/3.1/daterangepicker.min.js"></script>
{{-- Ensure CSRF token meta tag is in your layout's <head> --}}
{{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}

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
                            <input type="text" data-kt-customer-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search Customers" />
                        </div>
                    </div>

                    <div class="card-toolbar">
                        {{-- Base toolbar --}}
                        <div class="d-flex justify-content-between align-items-center w-100" data-kt-item-table-toolbar="base">
                            <!-- Input box with calendar icon -->
                            <div class="input-group me-3" style="max-width: 300px; flex-grow: 1;">
                                <input type="text" class="form-control form-control-solid" id="kt_daterangepicker_1" readonly placeholder="Select Date Range" />
                                <span class="input-group-text">
                                    <i class="ki-outline ki-calendar fs-2" id="date-range-picker-icon" style="color: blue;"></i>
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div class="w-150px">
                                    <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-order-filter="status">
                                        <option></option>
                                        <option value="all">All</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                {{-- Add Customer Button - Permission Check --}}
                                @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('create_customer'))
                                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">Add Customer</a>
                                @endif
                            </div>
                        </div>
                        {{-- End Base toolbar --}}

                        {{-- Selected toolbar --}}
                        <div class="d-flex justify-content-end align-items-center d-none" data-kt-customer-table-toolbar="selected" id="delete-selected-toolbar">
                            <div class="fw-bold me-5">
                                <span id="selected-count">0</span> Selected
                            </div>
                            {{-- Delete Selected Button - Permission Check --}}
                            @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('delete_customer'))
                                <button type="button" class="btn btn-danger" id="delete-selected-btn">Delete Selected</button>
                            @endif
                        </div>
                        {{-- End Selected toolbar --}}
                    </div>


                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customers_table">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                {{-- Select All Checkbox - Permission Check (Only show if user can delete) --}}
                                @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('delete_customer'))
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" id="select-all" />
                                    </div>
                                @endif
                            </th>
                            <th class="min-w-125px">Customer Name</th>
                            <th class="min-w-125px">Email</th>
                            <th class="min-w-125px">Phone Number</th>
                            <th class="min-w-125px">Status</th>
                            <th class="min-w-125px">Created Date</th>
                            <th class="text-end min-w-70px">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @foreach($customers as $index => $customer)
                            <tr>
                                <td>
                                    {{-- Row Checkbox - Permission Check (Only show if user can delete) --}}
                                    @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('delete_customer'))
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $customer->id }}" />
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>
                                    @if($customer->is_active)
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td data-order="{{ $customer->created_at->format('Y-m-d') }}">{{ $customer->created_at->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    {{-- View Button - Permission Check --}}
                                    @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('view_customer'))
                                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-light btn-active-light-info me-2">
                                            <i class="fas fa-eye"></i> View</a>
                                    @endif

                                    {{-- Edit Button - Permission Check --}}
                                    @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('edit_customer'))
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-light btn-active-light-primary me-2">
                                            <i class="fas fa-edit"></i> Edit</a>
                                    @endif

                                    {{-- Delete Button/Form - Permission Check --}}
                                    @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('delete_customer'))
                                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light btn-active-light-danger delete-banner">
                                                <i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('custom-js-section')
    {{-- Include SweetAlert CSS in your layout's <head> or here --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Make sure these assets are correctly linked --}}
    {{-- <script src="{{ asset('build-base/ktmt/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script> --}}
    {{-- <script src="{{ asset('build-base/ktmt/js/custom/apps/ecommerce/catalog/products.js') }}"></script> --}}
    <script>
        $(document).ready(function() {
            let table;

            // Date Range Picker Initialization
            $('#kt_daterangepicker_1').daterangepicker({
                autoUpdateInput: false, // Don't automatically update input value
                autoApply: true,
                opens: 'left',
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });

            // Apply event for Date Range Picker
            $('#kt_daterangepicker_1').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                table.column(5).search(picker.startDate.format('YYYY-MM-DD') + '|' + picker.endDate.format('YYYY-MM-DD'), true, false).draw();

            });

            // Cancel event for Date Range Picker
            $('#kt_daterangepicker_1').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val(''); // Clear the input
                table.column(5).search('').draw(); // Clear the search filter
            });


            $('#date-range-picker-icon').on('click', function() {
                $('#kt_daterangepicker_1').trigger('click');
            });

            // DataTable Initialization
            table = $("#kt_customers_table").DataTable({
                pageLength: 10,
                lengthMenu: [5,10, 25, 50],
                order: [[1, 'asc']], // Default sort by Customer Name
                columnDefs: [
                    { orderable: false, targets: [0, 6] }, // Disable sorting for checkbox and actions
                    { type: 'date', targets: 5 },         // Treat column 5 as date type for sorting/filtering
                ],
                searching: true,
                paging: true,
                info: true,
                // Add responsive features if needed
                // responsive: true,
            });

            // Search Input Filter
            $('input[data-kt-customer-table-filter="search"]').on('keyup', function() {
                table.search($(this).val()).draw();
            });

            // Status Filter Select2 Initialization
            $('.form-select[data-control="select2"]').select2({
                minimumResultsForSearch: Infinity // Hide search box in select2
            });

            // Status Filter Logic
            $('.form-select[data-kt-ecommerce-order-filter="status"]').on('change', function() {
                var status = $(this).val();
                var statusSearchTerm = ''; // Default to searching for nothing (effectively 'all')

                if (status === 'active') {
                    statusSearchTerm = '^Active$'; // Regex for exact match "Active"
                } else if (status === 'inactive') {
                    statusSearchTerm = '^Inactive$'; // Regex for exact match "Inactive"
                }
                // For 'all' or empty value, statusSearchTerm remains '', clearing the filter

                // Apply the search to column 4 (Status) with regex enabled, case-insensitive disabled
                table.column(4).search(statusSearchTerm, true, false).draw();
            });

            // Select All Checkbox Logic (Now handled in bulk delete section below)
            // $('[data-kt-check="true"]').on('change', function() { ... }); // Redundant with #select-all logic below
        });


        // Bulk Delete Logic
        $(document).ready(function () {
            const $selectAllCheckbox = $('#select-all');
            const $rowCheckboxes = $('.row-checkbox'); // Select only row checkboxes
            const $deleteSelectedToolbar = $('#delete-selected-toolbar');
            const $selectedCount = $('#selected-count');
            const $baseToolbar = $('[data-kt-item-table-toolbar="base"]'); // More specific selector for base toolbar

            // Function to update toolbar visibility based on checkbox selection
            function updateToolbarVisibility() {
                const selectedCheckboxes = $('.row-checkbox:checked');
                const numSelected = selectedCheckboxes.length;

                $selectedCount.text(numSelected); // Update selected count display

                if (numSelected > 0) {
                    // Show selected toolbar, hide base toolbar
                    $deleteSelectedToolbar.removeClass('d-none');
                    $baseToolbar.addClass('d-none');

                    // Update select-all checkbox state
                    if ($selectAllCheckbox.length) { // Check if select-all exists (permission check)
                        $selectAllCheckbox.prop('checked', numSelected === $rowCheckboxes.length);
                    }

                } else {
                    // Hide selected toolbar, show base toolbar
                    $deleteSelectedToolbar.addClass('d-none');
                    $baseToolbar.removeClass('d-none');

                    // Uncheck select-all checkbox
                    if ($selectAllCheckbox.length) {
                        $selectAllCheckbox.prop('checked', false);
                    }
                }
            }

            // Event listener for the select-all checkbox
            // Only attach if the checkbox exists (due to permission check)
            if ($selectAllCheckbox.length) {
                $selectAllCheckbox.on('change', function () {
                    const isChecked = $(this).prop('checked');
                    $rowCheckboxes.prop('checked', isChecked);
                    updateToolbarVisibility();
                });
            }

            // Event listener for individual row checkboxes
            // Use event delegation on the table body for potentially dynamic rows
            $('#kt_customers_table tbody').on('change', '.row-checkbox', function() {
                updateToolbarVisibility();
            });

            // Initial toolbar state check on page load
            updateToolbarVisibility();


            // Delete Selected Button Click Handler
            $('#delete-selected-btn').on('click', function () {
                const selectedIds = $rowCheckboxes
                    .filter(':checked')
                    .map(function () {
                        return $(this).val(); // Get the customer ID from the value attribute
                    })
                    .get(); // Get as a plain array

                if (selectedIds.length > 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert deleting these " + selectedIds.length + " customers!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33', // Red for delete
                        cancelButtonColor: '#3085d6', // Blue for cancel
                        confirmButtonText: 'Yes, delete them!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proceed with AJAX deletion
                            deleteSelectedRows(selectedIds);
                        }
                    });
                } else {
                    // Should not happen if button is only visible when items are selected, but good practice
                    Swal.fire('No rows selected', 'Please select at least one customer to delete.', 'info');
                }
            });

            // Function to handle AJAX deletion of selected rows
            function deleteSelectedRows(selectedIds) {
                // Make sure CSRF token is available
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                if (!csrfToken) {
                    Swal.fire('Error', 'CSRF token missing. Please refresh the page.', 'error');
                    return;
                }

                $.ajax({
                    url: '/customers-delete', // Make sure this route is defined in web.php/api.php for POST method
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: JSON.stringify({ ids: selectedIds }), // Send IDs as JSON payload
                    success: function (data) {
                        if (data.success) {
                            Swal.fire('Deleted!', data.message || 'Selected customers have been deleted.', 'success').then(() => {
                                // Option 1: Reload the whole page
                                location.reload();
                                // Option 2: Remove rows from DataTable (more complex, needs DataTable API)
                                // table.rows($rowCheckboxes.filter(':checked').closest('tr')).remove().draw();
                                // updateToolbarVisibility(); // Reset toolbar after deletion
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Failed to delete customers.', 'error');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        Swal.fire('Error', 'An error occurred while deleting customers. Please try again.', 'error');
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            }
        });

        // Single Row Delete Confirmation
        // Use event delegation for potentially dynamic rows
        $('#kt_customers_table tbody').on('click', '.delete-banner', function (e) {
            e.preventDefault(); // Prevent default form submission

            const form = $(this).closest('form'); // Find the parent form

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form if confirmed
                }
            });
        });
    </script>
@endsection
