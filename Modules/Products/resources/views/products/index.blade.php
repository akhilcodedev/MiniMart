@php
    // Define convenience variables for cleaner Blade checks (optional but recommended)
    $isSuperAdmin = auth()->user()->is_super_admin == 1;
@endphp

@extends('base::layouts.mt-main')

@section('content')
    <style>
        .image-input-placeholder {
            background-image: url("https://preview.keenthemes.com/html/metronic/docs/assets/media/svg/avatars/blank.svg");
        }

        [data-bs-theme="dark"] .image-input-placeholder {
            background-image: url("https://preview.keenthemes.com/html/metronic/docs/assets/media/svg/avatars/blank.svg");
        }
    </style>

    <div class="container">
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                <!--begin::Products-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap">
                                <!-- Search Input -->
                                <div class="d-flex align-items-center position-relative">
                                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                                    <input type="text" id="product_search" name="search" class="form-control form-control-solid w-250px ps-12" placeholder="Search Product">
                                </div>
                            </div>
                        </div>
                        <!--end::Card title-->

                        <!--begin::Card toolbar-->
                        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">

                            <!-- Status Filter (Generally available) -->
                            <div class="d-flex align-items-center ms-4">
                                <select class="form-select form-select-solid" id="product_status" name ="product_status" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-product-filter="status">
                                    <option value="all" selected>All</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            {{-- Import Section --}}
                            @if($isSuperAdmin || auth()->user()->can('product-import'))
                                <div class="d-flex align-items-center ms-4">
                                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="file" required>
                                        <br><br>
                                        <label for="limit">Max Rows:</label>
                                        <input type="number" class="form-control form-control-sm me-2" name="limit" value="100" min="1" max="1000" required>
                                        <br><br>
                                        <button type="submit" class="btn btn-primary btn-sm">Import</button>
                                    </form>
                                </div>
                            @endif

                            {{-- Export Section --}}
                            @if($isSuperAdmin || auth()->user()->can('product-export'))
                                <div class="d-flex align-items-center ms-4">
                                    <form action="{{ route('products.export') }}" method="GET">
                                        <label for="limit">Max Rows:</label>
                                        <input type="number" class="form-control form-control-sm me-2" name="limit" value="100" min="1" max="1000" required>
                                        <label for="start_date">Start Date:</label>
                                        <input type="date" class="form-control form-control-sm me-2" name="start_date">
                                        <label for="end_date">End Date:</label>
                                        <input type="date" class="form-control form-control-sm me-2" name="end_date">
                                        <br><br>
                                        <button type="submit" class="btn btn-primary btn-sm">Export Products</button>
                                    </form>
                                </div>
                            @endif

                            {{-- Bulk Delete Button --}}
                            @if($isSuperAdmin || auth()->user()->can('product-delete'))
                                <div class="d-flex align-items-center ms-4">
                                    <button id="delete-selected" class="btn btn-danger btn-sm">Delete Selected</button>
                                </div>
                            @endif

                            {{-- Add Product Button --}}
                            @if($isSuperAdmin || auth()->user()->can('product-create'))
                                <div class="d-flex align-items-center ms-4">
                                    @isset($create_url) {{-- Make sure $create_url is passed from the controller --}}
                                    <a href="{{ $create_url }}" class="btn btn-primary btn-sm">Add Product</a>
                                    @endisset
                                </div>
                            @endif

                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Datatable-->
                        <table id="product_table" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                {{-- Conditionally show checkbox column header --}}
                                @if($isSuperAdmin || auth()->user()->can('product-delete'))
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox"
                                                   data-kt-check="true" id="select-all" data-kt-check-target="" value="1"/>
                                        </div>
                                    </th>
                                @endif
                                <th>ID</th>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Product Type</th>
                                {{-- Conditionally show Actions column header --}}
                                @if($isSuperAdmin || auth()->user()->can('product-edit') || auth()->user()->can('product-delete'))
                                    <th class="min-w-100px">Actions</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                            {{-- Data will be populated by DataTables --}}
                            </tbody>
                        </table>
                        <!--end::Datatable-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Products-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
@endsection

@section('custom-js-section')
    {{-- Keep original JS includes --}}
    <script src="{{ asset('build-base/ktmt/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>

    <script>
        // Pass PHP permission checks to JavaScript
        const IS_SUPER_ADMIN = {{ auth()->user()->is_super_admin == 1 ? 'true' : 'false' }};
        // CAN_LIST_PRODUCT check is primarily for UI elements now, backend enforces the actual data access.
        const CAN_EDIT_PRODUCT = {{ (auth()->user()->is_super_admin == 1 || auth()->user()->can('product-edit')) ? 'true' : 'false' }};
        const CAN_DELETE_PRODUCT = {{ (auth()->user()->is_super_admin == 1 || auth()->user()->can('product-delete')) ? 'true' : 'false' }};

        var KTDatatablesServerSide = function () {
            var dt;

            var initDatatable = function () {
                // --- Removed the frontend !CAN_LIST_PRODUCT check here ---
                // Backend route protection for 'products.all' handles access denial.

                // Define columns dynamically based on permissions
                let columns = [
                    // Checkbox column (only if user can delete)
                    ...(CAN_DELETE_PRODUCT ? [{
                        data: 'id',
                        name: null, // Checkbox doesn't directly map usually
                        orderable: false,
                        searchable: false,
                        className: 'w-10px pe-2 dt-checkbox', // Added dt-checkbox class for potential styling/selection
                        render: function (data) {
                            return `<div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input select-checkbox" type="checkbox" value="${data}" />
                                    </div>`;
                        }
                    }] : []),
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'sku', name: 'sku', searchable: true, sortable: true},
                    {data: 'price', name: 'price', searchable: true},
                    {data: 'quantity', name: 'quantity', searchable: true},
                    {data: 'product_type', name: 'product_type'},
                    // Action column (only if user can edit or delete)
                    ...(CAN_EDIT_PRODUCT || CAN_DELETE_PRODUCT ? [{
                        data: 'action', // Generated server-side based on permissions
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end min-w-100px'
                    }] : [])
                ];

                dt = $("#product_table").DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    stateSave: false,
                    ajax: {
                        url: "{{ route('products.all') }}", // THIS ROUTE MUST BE PROTECTED BY 'product-list' PERMISSION
                        type: 'POST',
                        headers: { 'X-CSRF-Token': '{{ csrf_token() }}' },
                        data: function (d) {
                            d.search.value = $('#product_search').val();
                            d.product_status = $('#product_status').val();
                        },
                        error: function (xhr, error, thrown) {
                            console.error('DataTables Ajax error:', error, thrown, xhr.responseText);
                            // DataTable will often show a generic error message itself on failure
                            // You could show a custom Swal error here if the default isn't sufficient
                            if (xhr.status === 403) {
                                Swal.fire({ title: "Access Denied", text: "You do not have permission to view these products.", icon: "error"});
                            } else {
                                Swal.fire({ title: "Error", text: "Could not load product data. Please try again.", icon: "error"});
                            }
                        }
                    },
                    columns: columns,
                    order: [[ CAN_DELETE_PRODUCT ? 1 : 0, 'desc' ]], // Order by ID (adjust index)
                    columnDefs: [
                        { targets: 0, orderable: false, searchable: false }, // Make first column (checkbox or ID) non-orderable/searchable
                        // Add other columnDefs if needed
                    ],
                    drawCallback: function( settings ) {
                        KTMenu?.createInstances(); // Use createInstances for potentially multiple menus
                        if (CAN_DELETE_PRODUCT) {
                            $('#select-all').prop('checked', false);
                        }
                    },
                    // Consider adding language options for internationalization
                    // language: { processing: "Loading products..." }
                });

                // --- Event Handlers ---

                $('#product_search').on('keyup', function () {
                    dt.search($(this).val()).draw();
                });

                $('#product_status').change(function () {
                    dt.draw();
                });

                // --- Conditional Event Listeners for Deletion ---
                if (CAN_DELETE_PRODUCT) {
                    // Select/Deselect All Checkboxes
                    $('#select-all').on('click', function () {
                        const isChecked = this.checked;
                        dt.$('.select-checkbox').prop('checked', isChecked);
                    });

                    // Handle individual checkbox clicks
                    $('#product_table tbody').on('change', '.select-checkbox', function(){
                        if (!this.checked) {
                            $('#select-all').prop('checked', false);
                        }
                        // Optional check if all are now checked (might be removed for performance)
                    });

                    // Bulk Delete Selected Rows
                    $('#delete-selected').on('click', function() {
                        let selected = [];
                        dt.$('.select-checkbox:checked').each(function() {
                            selected.push($(this).val());
                        });

                        if (selected.length > 0) {
                            Swal.fire({ /* ... bulk delete confirmation ... */ }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({ /* ... bulk delete ajax call ... */
                                        success: function (response) {
                                            Swal.fire({ text: response.message || "Products deleted.", icon: "success" });
                                            dt.ajax.reload(null, false);
                                            $('#select-all').prop('checked', false);
                                        },
                                        error: function(xhr) { /* ... bulk delete error handling ... */ }
                                    });
                                }
                            });
                        } else { /* ... handle no selection ... */ }
                    });
                } // end if(CAN_DELETE_PRODUCT)

                // --- Listener for Individual Delete Action Button ---
                $('#product_table tbody').on('click', '.deleteProduct', function (e) {
                    e.preventDefault();
                    if (!CAN_DELETE_PRODUCT) { /* ... permission check ... */ return; }
                    var product_id = $(this).data("id");
                    if (!product_id) { /* ... ID check ... */ return; }

                    Swal.fire({ /* ... single delete confirmation ... */ }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({ /* ... single delete ajax call ... */
                                url: "{{ route('products.delete') }}",
                                type: 'POST',
                                data: { id: product_id, _token: "{{ csrf_token() }}" },
                                success: function (response) {
                                    Swal.fire({ text: response.message || "Product deleted.", icon: "success" });
                                    dt.ajax.reload(null, false);
                                },
                                error: function (xhr) { /* ... single delete error handling ... */ }
                            });
                        }
                    });
                }); // End individual delete listener

            }; // end initDatatable

            return { init: function () { initDatatable(); } };
        }(); // end KTDatatablesServerSide

        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });
    </script>
@endsection
