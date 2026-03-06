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
                @if(auth()->user()->is_super_admin || auth()->user()->can('show_coupon_modes'))

                    {{-- Main Card Container --}}
                    <div class="card card-flush">

                        {{-- Filter Card --}}
                        <div class="card card-custom mb-5">
                            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                                <div class="card-title">
                                    <h3 class="card-label">Coupon Mode Filter</h3>
                                </div>
                                <div class="card-toolbar gap-2">
                                    {{-- Import/Export/Sample (Conditional - check super admin OR permission) --}}
                                    {{--
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('edit_coupon_modes'))
                                    <form class="row g-1 align-items-center" method="POST" action="{{ route('priceRule.cart.couponModes.import') }}" enctype="multipart/form-data" name="coupon_mode_import_form" id="coupon_mode_import_form">
                                        @csrf
                                        <div class="col flex-grow-1">
                                            <input type="file" id="coupon_mode_import_file" name="coupon_mode_import_file" accept="text/csv" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" name="importSubmit" value="1" class="btn btn-sm btn-warning d-flex align-items-center"><i class="fas fa-file-import fs-6 me-1"></i> CSV Import</button>
                                        </div>
                                    </form>
                                    @endif
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('edit_coupon_modes'))
                                    <a href="{{ $serviceHelper->getFileUrl('uploads/csv/samples/coupon-modes-sample-data.csv') }}" download class="btn btn-sm btn-info d-flex align-items-center">
                                        <i class="fas fa-file-csv fs-6 me-1"></i> Sample CSV
                                    </a>
                                    @endif
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('show_coupon_modes'))
                                    <a href="{{ route('priceRule.cart.couponModes.export') }}" class="btn btn-sm btn-info d-flex align-items-center">
                                        <i class="fas fa-file-export fs-6 me-1"></i> CSV Export
                                    </a>
                                    @endif
                                    --}}
                                </div>
                            </div>

                            <div class="card-body">
                                {{-- Filter Form --}}
                                <form name="filter_coupon_modes_list_form" action="{{ route('priceRule.cart.couponModes.searchByFilters') }}" method="POST" id="filter_coupon_modes_list_form">
                                    @csrf
                                    <div class="row align-items-center">
                                        <div class="col-lg-8 mb-4 mb-lg-0">
                                            <label for="search_term_filter" class="form-label visually-hidden">Search Term</label>
                                            <input type="text" class="form-control datatable-input" id="search_term_filter" name="search_term_filter" placeholder="Search by Code or Name..." />
                                        </div>
                                        <div class="col-lg-4 text-lg-end">
                                            <input type="hidden" name="filter_action" id="filter_action" value="datatable" />
                                            <button type="button" id="filter_coupon_modes_list_filter_btn" class="btn btn-primary me-2">
                                                <i class="la la-search"></i>Search
                                            </button>
                                            <button type="button" id="filter_coupon_modes_list_reset_btn" class="btn btn-secondary">
                                                <i class="la la-close"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        {{-- End Filter Card --}}

                        {{-- List Card --}}
                        <div class="card card-custom">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="card-label">Coupon Mode List</h3>
                                </div>
                                <div class="card-toolbar">
                                    {{-- Add Button: Check super admin OR create permission --}}
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('create_coupon_modes'))
                                        <a href="{{ route('priceRule.cart.couponModes.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Add Coupon Mode
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-row-bordered gy-5 gs-7" id="coupon_mode_list_filter_table" style="width:100%;">
                                        <thead>
                                        <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Sort Order</th>
                                            <th>Updated By</th>
                                            <th>Updated At</th>
                                            <th>Active</th>
                                            {{-- Actions Header: Show if super admin OR can edit OR can delete --}}
                                            @if(auth()->user()->is_super_admin || auth()->user()->can('edit_coupon_modes') || auth()->user()->can('delete_coupon_modes'))
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

                @else {{-- If user cannot view list AND is not super admin --}}
                <div class="alert alert-danger">You do not have permission to view Coupon Modes.</div>
                @endif

            </div> {{-- End kt_content_container --}}
        </div> {{-- End kt_post --}}
    </div> {{-- End container --}}

@endsection

@section('custom-js-section')

    {{-- Include necessary JS assets --}}
    <script src="{{ asset('build-base/ktmt/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    {{-- Widgets bundle included but ensure it's necessary for this page --}}
    {{-- <script src="{{ asset('build-base/ktmt/js/widgets.bundle.js') }}"></script> --}}
    {{-- <script src="{{ asset('build-base/ktmt/js/custom/widgets.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Encapsulate JavaScript logic
        var CouponModeCustomJsBlocks = function () {

            // Private variables
            let dataTable;
            let filterForm;
            let dataTableInitialized = false;

            // Initialize DataTable function
            let initCouponModeListTable = function (hostUrl, csrfToken) {

                // 1. Permission Check (JS side)
                if (!({{ auth()->user()->is_super_admin ? 'true' : 'false' }} || {{ auth()->user()->can('show_coupon_modes') ? 'true' : 'false' }})) {
                    console.warn("User lacks 'show_coupon_modes' permission and is not super admin. Init skipped.");
                    $('#coupon_mode_list_filter_table_area').html('<div class="alert alert-danger text-center p-5">Access Denied: You do not have permission to view this list.</div>');
                    return;
                }

                if (dataTableInitialized) return;

                const tableElement = $('#coupon_mode_list_filter_table');
                filterForm = $('#filter_coupon_modes_list_form');

                if (!tableElement.length) {
                    console.error("Table element #coupon_mode_list_filter_table not found!");
                    return;
                }
                if (!filterForm.length) {
                    console.warn("Filter form #filter_coupon_modes_list_form not found! Table will load without filter controls.");
                }

                // 2. Get Permission Flags (JS side, includes Super Admin)
                const isSuperAdmin = {{ auth()->user()->is_super_admin ? 'true' : 'false' }};
                const canEditCouponMode = {{ auth()->user()->can('edit_coupon_modes') ? 'true' : 'false' }} || isSuperAdmin;
                const canDeleteCouponMode = {{ auth()->user()->can('delete_coupon_modes') ? 'true' : 'false' }} || isSuperAdmin;

                // 3. Define DataTable Columns base structure
                let dtColumns = [
                    { data: 'modeId', name: 'coupon_modes.id', className: 'text-center' },
                    { data: 'modeCode', name: 'coupon_modes.mode_code' },
                    { data: 'modeName', name: 'coupon_modes.mode_name' },
                    { data: 'modeSortOrder', name: 'coupon_modes.sort_order' },
                    { data: 'updatedBy', name: 'users.name', orderable: false, searchable: false },
                    { data: 'updatedAt', name: 'coupon_modes.updated_at' },
                    { data: 'isActive', name: 'coupon_modes.is_active', className: 'text-center' },
                ];

                // 4. Conditionally add Actions column definition based on permissions
                const actionsColumnExists = isSuperAdmin || canEditCouponMode || canDeleteCouponMode;
                if (actionsColumnExists) {
                    dtColumns.push({
                        data: null, // Data is null because we render client-side
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
                    order: [[0, 'desc']], // Default sort
                    stateSave: false, // Optional state saving
                    ajax: {
                        url: filterForm.length ? filterForm.attr('action') : '{{ route("priceRule.cart.couponModes.searchByFilters") }}',
                        type: filterForm.length ? filterForm.attr('method') : 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        data: function (d) {
                            // Append filter form data if form exists
                            if (filterForm.length) {
                                $.each(filterForm.serializeArray(), function (key, val) {
                                    d[val.name] = val.value;
                                });
                            } else {
                                d.filter_action = 'datatable'; // Ensure default action if no form
                            }
                        },
                        error: function (xhr, error, thrown) {
                            console.error("DataTables AJAX Error:", error, thrown, xhr.responseText);
                            let errorMsg = 'Error loading data. Please check console or try again later.';
                            if (xhr.status === 403) {
                                errorMsg = 'Access Denied: You might not have permission to view this data.';
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showAlertMessage(errorMsg, 'error');
                            let colspan = dtColumns.length; // Use dynamic colspan
                            tableElement.find('tbody').html(`<tr><td colspan="${colspan}" class="text-center text-danger fw-bold p-5">${errorMsg}</td></tr>`);
                            $('.dataTables_processing').hide();
                        }
                    },
                    columns: dtColumns, // Use dynamic columns array
                    columnDefs: [
                        {
                            targets: 6, // Index for 'isActive' (adjust if columns change)
                            orderable: true,
                            // Use render instead of createdCell for better DT API consistency
                            render: function (data, type, row) {
                                const status = data ? { 'title': 'Active', 'class': 'badge-light-success' } : { 'title': 'Inactive', 'class': 'badge-light-danger' };
                                return `<span class="badge ${status.class} fs-7 fw-bold">${status.title}</span>`;
                            }
                        },
                        // Conditionally add the render function for the 'Actions' column
                        ...(actionsColumnExists ? [{ // Spread operator conditionally adds this object
                            targets: -1, // Target the last column (which is actions if it exists)
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row, meta) {
                                let actionHtml = ''; // Initialize action buttons HTML

                                // Check permissions (JS flags already include super admin check)
                                if (canEditCouponMode) {
                                    let editUrl = '{{ route("priceRule.cart.couponModes.edit", ["id" => ":id"]) }}';
                                    editUrl = editUrl.replace(':id', row.modeId); // Use unique row data (modeId)

                                    actionHtml += `
                                        <a href="${editUrl}" class="btn btn-icon btn-active-light-primary w-30px h-30px me-2" title="Edit Coupon Mode (ID: ${row.modeId})">
                                            <span class="svg-icon svg-icon-3">
                                                <i class="fas fa-edit fs-4"></i>
                                            </span>
                                        </a>
                                    `;
                                }

                                if (canDeleteCouponMode) {
                                    // Use backticks for template literal to easily include data attributes
                                    actionHtml += `
                                        <a href="#" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-coupon-mode" data-id="${row.modeId}" data-name="${row.modeName || 'this mode'}" title="Delete Coupon Mode (ID: ${row.modeId})">
                                            <span class="svg-icon svg-icon-3">
                                                <i class="fas fa-trash fs-4"></i>
                                            </span>
                                        </a>
                                    `;
                                }

                                return actionHtml || '<span class="text-muted fs-7">No Actions</span>'; // Show fallback text if no actions are allowed
                            } // End render function
                        }] : []) // End conditional object addition
                    ],
                    // Language settings
                    language: {
                        processing: '<div class="d-flex justify-content-center align-items-center p-3"><span class="spinner-border spinner-border-sm text-primary me-2"></span> Processing...</div>',
                        emptyTable: "No coupon modes found.",
                        zeroRecords: "No matching coupon modes found."
                    }
                });

                dataTableInitialized = true;

                // --- Event Listeners ---

                // Filter button click
                $('#filter_coupon_modes_list_filter_btn').on('click', function (e) {
                    e.preventDefault();
                    if (dataTable) dataTable.draw(); // Check if initialized
                });

                // Reset button click
                $('#filter_coupon_modes_list_reset_btn').on('click', function (e) {
                    e.preventDefault();
                    if (filterForm.length) filterForm[0].reset();
                    if (dataTable) {
                        dataTable.search('');
                        dataTable.columns().search('');
                        dataTable.draw();
                    }
                });

                // Delete button click (delegate from table - attach only if needed)
                if (canDeleteCouponMode) { // Use flag which includes super admin
                    tableElement.on('click', '.delete-coupon-mode', function (e) {
                        e.preventDefault();
                        const button = $(this);
                        const id = button.data('id');
                        const name = button.data('name') || `ID ${id}`;
                        const deleteUrl = '{{ route("priceRule.cart.couponModes.destroy", ["id" => ":id"]) }}'.replace(':id', id); // Corrected route param name

                        // SweetAlert2 confirmation
                        Swal.fire({
                            text: `Are you sure you want to delete the coupon mode "${name}"?`,
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
                                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>'); // Show loading

                                $.ajax({
                                    url: deleteUrl,
                                    type: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': csrfToken },
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success || response.status === 'success') {
                                            showAlertMessage(response.message || `Coupon mode "${name}" deleted successfully!`, 'success');
                                            if (dataTable) dataTable.ajax.reload(null, false); // Refresh table data
                                        } else {
                                            button.prop('disabled', false).html('<span class="svg-icon svg-icon-3"><i class="fas fa-trash fs-4"></i></span>'); // Restore icon
                                            showAlertMessage(response.message || `Error deleting "${name}".`, 'error');
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(`Delete Error (${id}):`, xhr.responseText);
                                        button.prop('disabled', false).html('<span class="svg-icon svg-icon-3"><i class="fas fa-trash fs-4"></i></span>'); // Restore icon
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
                } // End delete listener attachment check

            };

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
                        confirmButton: "btn btn-primary"
                    },
                    timer: iconType === 'success' ? 3500 : null,
                    timerProgressBar: iconType === 'success'
                });
            };


            // Public interface
            return {
                listPage: function (hostUrl, csrfToken) {
                    initCouponModeListTable(hostUrl, csrfToken);

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

        }(); // IIFE execution

        // Initialize the script when the DOM is ready
        jQuery(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            if (!csrfToken) {
                console.error('CRITICAL ERROR: CSRF token meta tag not found!');
                $('body').prepend('<div class="alert alert-danger m-5">Error: Security token missing. Page may not function correctly.</div>');
                return;
            }
            CouponModeCustomJsBlocks.listPage('{{ url('/') }}', csrfToken);
        });

    </script>

@endsection
