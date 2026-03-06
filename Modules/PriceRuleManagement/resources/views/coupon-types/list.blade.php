@extends('base::layouts.mt-main')

{{-- Add CSRF Token for AJAX requests --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')

    <div class="container">

        {{-- Container for displaying dynamic alert messages --}}
        <div class="custom_alert_trigger_messages_area mb-5"></div>

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">

                {{-- Check if user can view list OR is super admin --}}
                @if(auth()->user()->is_super_admin || auth()->user()->can('show_coupon_types'))

                    {{-- Main Card Container --}}
                    <div class="card card-flush">

                        {{-- Filter Card (Removed unnecessary nesting) --}}
                        <div class="card card-custom mb-7">
                            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                                <div class="card-title">
                                    <h3 class="card-label">Coupon Type Filter</h3>
                                </div>
                                <div class="card-toolbar gap-2"> {{-- Added gap for consistency --}}
                                    {{-- Import Form (Conditional - check super admin OR edit) --}}
                                    {{--
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('edit_coupon_types'))
                                    <form class="row g-1 align-items-center" method="POST" action="{{ route('priceRule.cart.couponTypes.import') }}" enctype="multipart/form-data" name="coupon_type_import_form" id="coupon_type_import_form">
                                        @csrf
                                        <div class="col flex-grow-1">
                                            <input type="file" id="coupon_type_import_file" name="coupon_type_import_file" accept="text/csv" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" name="importSubmit" value="1" class="btn btn-sm btn-warning d-flex align-items-center"><i class="fas fa-file-import fs-6 me-1"></i> CSV Import</button>
                                        </div>
                                    </form>
                                    @endif
                                    --}}
                                    {{-- Sample CSV (Conditional - check super admin OR edit) --}}
                                    {{--
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('edit_coupon_types'))
                                    <a href="{{ $serviceHelper->getFileUrl('uploads/csv/samples/coupon-types-sample-data.csv') }}" download class="btn btn-sm btn-info d-flex align-items-center">
                                        <i class="fas fa-file-csv fs-6 me-1"></i> Sample CSV
                                    </a>
                                    @endif
                                    --}}
                                    {{-- Export CSV (Conditional - check super admin OR show list) --}}
                                    {{--
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('show_coupon_types'))
                                    <a href="{{ route('priceRule.cart.couponTypes.export') }}" class="btn btn-sm btn-info d-flex align-items-center">
                                        <i class="fas fa-file-export fs-6 me-1"></i> CSV Export
                                    </a>
                                    @endif
                                    --}}
                                </div>
                            </div>

                            <div class="card-body">
                                {{-- Filter Form --}}
                                <form name="filter_coupon_types_list_form" action="{{ route('priceRule.cart.couponTypes.searchByFilters') }}" method="POST" id="filter_coupon_types_list_form">
                                    @csrf
                                    {{-- Removed redundant form-group wrappers --}}
                                    <div class="row align-items-center"> {{-- Using row for layout --}}
                                        <div class="col-lg-8 mb-4 mb-lg-0"> {{-- Adjust column size --}}
                                            <label for="search_term_filter" class="form-label visually-hidden">Search Term</label>
                                            <input type="text" class="form-control datatable-input" id="search_term_filter" name="search_term_filter" placeholder="Search by Code or Name..." />
                                        </div>
                                        {{-- Placeholder: Add more filter inputs here if needed --}}
                                        {{-- <div class="col-lg-3 mb-4 mb-lg-0">...</div> --}}
                                        <div class="col-lg-4 text-lg-end"> {{-- Adjust column size & alignment --}}
                                            <input type="hidden" name="filter_action" id="filter_action" value="datatable" />
                                            <button type="button" id="filter_coupon_types_list_filter_btn" class="btn btn-primary me-2"> {{-- Default size buttons --}}
                                                <i class="la la-search"></i> Search
                                            </button>
                                            <button type="button" id="filter_coupon_types_list_reset_btn" class="btn btn-secondary"> {{-- Secondary style for reset --}}
                                                <i class="la la-close"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        {{-- End Filter Card --}}

                        {{-- List Card (Removed redundant nesting) --}}
                        <div class="card card-custom">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-label">Coupon Type List</h3>
                                </div>
                                <div class="card-toolbar">
                                    {{-- Add Button: Check super admin OR create permission --}}
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('create_coupon_types')) {{-- Permission check added --}}
                                    <a href="{{ route('priceRule.cart.couponTypes.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Add Coupon Type
                                    </a>
                                    @endif
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-row-bordered gy-5 gs-7" id="coupon_type_list_filter_table" style="width:100%;">
                                        <thead>
                                        <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200"> {{-- Consistent header style --}}
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Sort Order</th>
                                            <th>Updated By</th>
                                            <th>Updated At</th>
                                            <th>Active</th>
                                            {{-- Actions Header: Show if super admin OR can edit OR can delete --}}
                                            @if(auth()->user()->is_super_admin || auth()->user()->can('edit_coupon_types') || auth()->user()->can('delete_coupon_types')) {{-- Permission check added --}}
                                            <th class="text-center min-w-100px">Actions</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {{-- DataTables will populate this --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        {{-- End List Card --}}

                    </div>
                    {{-- End Main Card Container --}}

                @else {{-- If user cannot view the list AND is not super admin --}}
                <div class="alert alert-danger">You do not have permission to view Coupon Types.</div>
                @endif

            </div> {{-- End kt_content_container --}}
        </div> {{-- End kt_post --}}
    </div> {{-- End container --}}

@endsection

@section('custom-js-section')

    {{-- Include necessary JS assets --}}
    <script src="{{ asset('build-base/ktmt/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    {{-- Widgets/CKEditor removed unless specifically needed --}}
    {{-- <script src="{{ asset('build-base/ktmt/js/widgets.bundle.js') }}"></script> --}}
    {{-- <script src="{{ asset('build-base/ktmt/js/custom/widgets.js') }}"></script> --}}
    {{-- <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> {{-- Using SweetAlert2 --}}

    <script>
        // Encapsulate JavaScript logic
        var CouponTypeCustomJsBlocks = function () {

            // Private variables
            let dataTable;
            let filterForm;
            let dataTableInitialized = false;

            // Initialize DataTable function
            let initCouponTypeListTable = function (hostUrl, csrfToken) {

                // 1. Permission Check (JS side) - Includes Super Admin
                if (!({{ auth()->user()->is_super_admin ? 'true' : 'false' }} || {{ auth()->user()->can('show_coupon_types') ? 'true' : 'false' }})) {
                    console.warn("User lacks permission or super admin status. Init skipped.");
                    $('#coupon_type_list_filter_table_area').html('<div class="alert alert-danger text-center p-5">Access Denied: Cannot view this data.</div>');
                    return;
                }

                if (dataTableInitialized) return; // Prevent re-initialization


                const tableElement = $('#coupon_type_list_filter_table');
                filterForm = $('#filter_coupon_types_list_form');

                // Check for essential elements
                if (!tableElement.length) {
                    console.error("Table element '#coupon_type_list_filter_table' not found!");
                    return;
                }
                if (!filterForm.length) {
                    console.warn("Filter form '#filter_coupon_types_list_form' not found! Table may load without filtering.");
                }

                // 2. Get JS Permission Flags (Includes Super Admin Check)
                const isSuperAdmin = {{ auth()->user()->is_super_admin ? 'true' : 'false' }};
                const canEditCouponType = {{ auth()->user()->can('edit_coupon_types') ? 'true' : 'false' }} || isSuperAdmin;
                const canDeleteCouponType = {{ auth()->user()->can('delete_coupon_types') ? 'true' : 'false' }} || isSuperAdmin;


                // 3. Define Base DataTable Columns
                let dtColumns = [
                    { data: 'typeId', name: 'coupon_types.id', className: 'text-center' }, // Center ID
                    { data: 'typeCode', name: 'coupon_types.type_code' },
                    { data: 'typeName', name: 'coupon_types.type_name' },
                    { data: 'typeSortOrder', name: 'coupon_types.sort_order' },
                    { data: 'updatedBy', name: 'users.name', orderable: false, searchable: false },
                    { data: 'updatedAt', name: 'coupon_types.updated_at' },
                    { data: 'isActive', name: 'coupon_types.is_active', className: 'text-center' },
                ];

                // 4. Conditionally Add Actions Column Definition (Check Super Admin or Specific Perms)
                const actionsColumnExists = isSuperAdmin || canEditCouponType || canDeleteCouponType;
                if (actionsColumnExists) {
                    dtColumns.push({
                        data: null, // Rendered client-side based on permissions below
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center text-nowrap'
                    });
                }

                // 5. Initialize DataTable
                dataTable = tableElement.DataTable({
                    responsive: true,
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    order: [[0, 'desc']], // Default order by ID descending
                    stateSave: false,
                    ajax: {
                        url: filterForm.length ? filterForm.attr('action') : '{{ route("priceRule.cart.couponTypes.searchByFilters") }}', // Fallback URL
                        type: filterForm.length ? filterForm.attr('method') : 'POST', // Fallback Method
                        headers: { 'X-CSRF-TOKEN': csrfToken }, // CSRF Token
                        data: function (d) {
                            // Append filter form data if form exists
                            if (filterForm.length) {
                                $.each(filterForm.serializeArray(), function (key, val) {
                                    d[val.name] = val.value;
                                });
                            } else {
                                d.filter_action = 'datatable'; // Default if no form
                            }
                        },
                        error: function (xhr, error, thrown) { // Enhanced Error Handling
                            console.error("DataTables AJAX Error:", error, thrown, xhr.responseText);
                            let errorMsg = 'Error loading data.';
                            if (xhr.status === 403) { // Specific permission error
                                errorMsg = 'Access Denied: You do not have permission to access this data.';
                            } else if (xhr.responseJSON && xhr.responseJSON.message) { // Server message
                                errorMsg = xhr.responseJSON.message;
                            }
                            showAlertMessage(errorMsg, 'error');
                            let colspan = dtColumns.length;
                            tableElement.find('tbody').html(`<tr><td colspan="${colspan}" class="text-center text-danger fw-bold p-5">${errorMsg}</td></tr>`);
                            $('.dataTables_processing').hide(); // Hide loading indicator on error
                        }
                    },
                    columns: dtColumns, // Use dynamically built columns array
                    columnDefs: [
                        { // Definition for 'isActive' column
                            targets: 6, // Adjust index based on final dtColumns array
                            orderable: true,
                            render: function (data, type, row) {
                                const status = data ? { 'title': 'Active', 'class': 'badge-light-success' } : { 'title': 'Inactive', 'class': 'badge-light-danger' };
                                return `<span class="badge ${status.class} fs-7 fw-bold">${status.title}</span>`;
                            }
                        },
                        // Conditionally add column definition for 'Actions'
                        ...(actionsColumnExists ? [{
                            targets: -1, // Last column
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row, meta) {
                                let actionHtml = '';

                                // Use JS flags which include the super admin check
                                if (canEditCouponType) {
                                    let editUrl = '{{ route("priceRule.cart.couponTypes.edit", ["id" => ":id"]) }}';
                                    editUrl = editUrl.replace(':id', row.typeId);
                                    actionHtml += `
                                         <a href="${editUrl}" class="btn btn-icon btn-active-light-primary w-30px h-30px me-2" title="Edit Coupon Type (ID: ${row.typeId})">
                                             <span class="svg-icon svg-icon-3"><i class="fas fa-edit fs-4"></i></span>
                                         </a>
                                     `;
                                }

                                if (canDeleteCouponType) {
                                    actionHtml += `
                                         <a href="#" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-coupon-type" data-id="${row.typeId}" data-name="${row.typeName || 'this type'}" title="Delete Coupon Type (ID: ${row.typeId})">
                                             <span class="svg-icon svg-icon-3"><i class="fas fa-trash fs-4"></i></span>
                                         </a>
                                     `;
                                }

                                return actionHtml || '<span class="text-muted fs-7">No Actions</span>'; // Fallback text
                            } // end render
                        }] : []) // End conditional action column definition
                    ],
                    // Language settings
                    language: {
                        processing: '<div class="d-flex justify-content-center align-items-center p-3"><span class="spinner-border spinner-border-sm text-primary me-2"></span> Processing...</div>',
                        emptyTable: "No coupon types found.",
                        zeroRecords: "No matching coupon types found."
                    }
                });

                dataTableInitialized = true;

                // --- Event Listeners ---

                // Filter button click
                $('#filter_coupon_types_list_filter_btn').on('click', function (e) {
                    e.preventDefault();
                    if (dataTable) dataTable.draw(); // Ensure DataTable is initialized
                });

                // Reset button click
                $('#filter_coupon_types_list_reset_btn').on('click', function (e) {
                    e.preventDefault();
                    if (filterForm.length) filterForm[0].reset(); // Reset if form exists
                    if (dataTable) {
                        dataTable.search('');
                        dataTable.columns().search('');
                        dataTable.draw();
                    }
                });


                // Delete button click (Conditional attachment)
                if (canDeleteCouponType) { // This flag includes super admin check
                    tableElement.on('click', '.delete-coupon-type', function(e) {
                        e.preventDefault();
                        const button = $(this);
                        const id = button.data('id');
                        const name = button.data('name') || `ID ${id}`; // Fallback name
                        // Correct route param name should be 'id' based on the original render func
                        const deleteUrl = '{{ route("priceRule.cart.couponTypes.destroy", ["id" => ":id"]) }}'.replace(':id', id);


                        // SweetAlert2 confirmation
                        Swal.fire({
                            text: `Are you sure you want to delete the coupon type "${name}"?`,
                            icon: "warning",
                            showCancelButton: true,
                            buttonsStyling: false,
                            confirmButtonText: "Yes, delete!",
                            cancelButtonText: "No, cancel",
                            customClass: {
                                confirmButton: "btn fw-bold btn-danger",
                                cancelButton: "btn fw-bold btn-active-light-primary"
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>'); // Show loading state

                                $.ajax({
                                    url: deleteUrl,
                                    type: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': csrfToken },
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success || response.status === 'success') {
                                            showAlertMessage(response.message || `Coupon type "${name}" deleted successfully!`, 'success');
                                            if (dataTable) dataTable.ajax.reload(null, false); // Refresh table
                                        } else {
                                            button.prop('disabled', false).html('<span class="svg-icon svg-icon-3"><i class="fas fa-trash fs-4"></i></span>'); // Restore button
                                            showAlertMessage(response.message || `Error deleting "${name}".`, 'error');
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(`Delete Error (${id}):`, xhr.responseText);
                                        button.prop('disabled', false).html('<span class="svg-icon svg-icon-3"><i class="fas fa-trash fs-4"></i></span>'); // Restore button
                                        let errorMessage = `Error deleting "${name}". Please try again.`;
                                        if (xhr.responseJSON && xhr.responseJSON.message) {
                                            errorMessage = xhr.responseJSON.message;
                                        }
                                        showAlertMessage(errorMessage, 'error');
                                    }
                                });
                            }
                        });
                    });
                } // End if(canDeleteCouponType)

            }; // End initCouponTypeListTable


            // Use SweetAlert2 for messages
            let showAlertMessage = function(message, type = 'info') {
                if (!message || message.trim() === '') return;

                let iconType = type.toLowerCase();
                if (!['success', 'error', 'warning', 'info', 'question'].includes(iconType)) {
                    iconType = 'info';
                }

                Swal.fire({
                    text: message,
                    icon: iconType,
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary" // Use theme's button style
                    },
                    timer: iconType === 'success' ? 3500 : null, // Auto close success messages
                    timerProgressBar: iconType === 'success'
                });
            };


            // Public interface
            return {
                listPage: function (hostUrl, csrfToken) {
                    initCouponTypeListTable(hostUrl, csrfToken);

                    // Display session/validation messages on load
                    @if(session('success'))
                    showAlertMessage("{{ session('success') }}", 'success');
                    @endif
                    @if(session('error'))
                    showAlertMessage("{{ session('error') }}", 'error');
                        @endif
                        @if($errors->any())
                    let errorList = '<ul class="mb-0 text-start">';
                    @foreach ($errors->all() as $error)
                        errorList += `<li>{{ $error }}</li>`;
                    @endforeach
                        errorList += '</ul>';
                    showAlertMessage(`<strong>Please fix the following errors:</strong>${errorList}`, 'error');
                    @endif
                }
            };

        } (); // IIFE execution

        // Initialize the script when the DOM is ready
        jQuery(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            if (!csrfToken) {
                console.error('CRITICAL ERROR: CSRF token meta tag not found!');
                // Provide visual feedback if token is missing
                $('body').prepend('<div class="alert alert-danger m-5">Error: Security token missing. Page functionality may be limited. Please refresh or contact support.</div>');
                return; // Halt execution
            }
            // Initialize the page logic
            CouponTypeCustomJsBlocks.listPage('{{ url('/') }}', csrfToken);
        });

    </script>

@endsection
