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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/daterangepicker/3.1/daterangepicker.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/daterangepicker/3.1/daterangepicker.min.js"></script>

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">

                    <div class="card-title">
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" data-kt-store-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search Stores" />
                        </div>
                        <!--end::Search-->
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-between align-items-center w-100" data-kt-item-table-toolbar="base">
                            <!-- Input box with calendar icon -->
                            <div class="input-group me-3" style="max-width: 300px; flex-grow: 1;">
                                <input type="text" class="form-control form-control-solid" id="kt_daterangepicker_1" readonly placeholder="Select Date Range" />
                                <span class="input-group-text">
                                    <i class="ki-outline ki-calendar fs-2" id="date-range-picker-icon" style="color: blue;"></i>
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div class="">
                                    <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-order-filter="status">
                                        <option></option>
                                        <option value="all">All</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                {{-- Permission Check for Add Store Button --}}
                                @if(userCan('create_stores'))
                                    <a href="{{ route('stores.create') }}" class="btn btn-primary btn-sm" style="text-wrap:nowrap">Add Store</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Permission Check for Delete Selected Toolbar --}}
                    @if(userCan('delete_stores'))
                        <div class="d-flex justify-content-end align-items-center d-none" data-kt-store-table-toolbar="selected" id="delete-selected-toolbar">
                            <div class="fw-bold me-5">
                                <span id="selected-count">0</span> Selected
                            </div>
                            <button type="button" class="btn btn-danger" id="delete-selected-btn">Delete Selected</button>
                        </div>
                    @endif
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_stores_table">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            {{-- Show checkbox column only if user can delete --}}
                            @if(userCan('delete_stores'))
                                <th class="w-10px pe-2 align-middle">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" id="select-all" />
                                    </div>
                                </th>
                            @else
                                <th class="w-10px pe-2 align-middle"></th> {{-- Placeholder or empty header if delete not allowed --}}
                            @endif
                            <th class="min-w-10px">ID</th>
                            <th class="min-w-125px">Name</th>
                            <th class="min-w-100px">Code</th>
                            <th class="min-w-75px">Status</th>
                            <th class="min-w-150px">URL Key</th>
                            <th class="min-w-150px">Website</th>
                            <th class="min-w-100px">Language</th>
                            <th class="min-w-125px align-middle">Created Date</th>
                            {{-- Show Actions column only if user can edit or delete --}}
                            @if(userCan('edit_stores') || userCan('delete_stores'))
                                <th class="text-end min-w-100px">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @foreach ($stores as $store)
                            <tr data-store-id="{{ $store->id }}">
                                {{-- Show checkbox only if user can delete --}}
                                @if(userCan('delete_stores'))
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $store->id }}" />
                                        </div>
                                    </td>
                                @else
                                    <td></td> {{-- Placeholder or empty cell if delete not allowed --}}
                                @endif
                                <td>{{ $store->id }}</td>
                                <td>{{ $store->name }}</td>
                                <td>{{ $store->code }}</td>
                                <td>
                                        <span class="badge {{ $store->status ? 'badge badge-light-success' : 'badge badge-light-danger' }}">
                                            {{ $store->status ? 'Active' : 'Inactive' }}
                                        </span>
                                </td>
                                <td>{{ $store->url_key }}</td>
                                <td>{{ $store->website }}</td>
                                <td>{{ $store->language ? $store->language->name : '-' }}</td>
                                <td class="align-middle" data-order="{{ $store->created_at->format('Y-m-d') }}">{{ $store->created_at->format('Y-m-d') }}</td>
                                {{-- Show Actions cell content only if user has corresponding permissions --}}
                                @if(userCan('edit_stores') || userCan('delete_stores'))
                                    <td class="text-end">
                                        {{-- Permission Check for Edit Button --}}
                                        @if(userCan('edit_stores'))
                                            <a href="{{ route('stores.edit', $store) }}" class="btn btn-sm btn-light btn-active-light-primary me-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        @endif

                                        {{-- Permission Check for Delete Button --}}
                                        @if(userCan('delete_stores'))
                                            {{-- Note: Your JS targets .delete-button within the form, but the button class is delete-banner. Ensure consistency or update JS selector. --}}
                                            <form action="{{ route('stores.destroy', $store) }}" method="POST" class="delete-form" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light btn-active-light-danger delete-banner"> {{-- Kept original class 'delete-banner' as requested --}}
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
@endsection

@section('custom-js-section')
    <script>
        $(document).ready(function() {
            let table;

            // --- Permission Flags ---
            // Passed from Blade using the helper function
            const canViewCheckbox = {{ userCan('delete_stores') ? 'true' : 'false' }};
            const canEditStores = {{ userCan('edit_stores') ? 'true' : 'false' }};
            const canViewActions = canViewCheckbox || canEditStores; // Actions column is rendered if user can delete OR edit

            // --- Dynamic Column Index Calculation ---
            // These indices represent the column index DataTables will see *after*
            // the visibility rule for the checkbox column (index 0) is applied.
            const checkboxColIdx = 0; // Index of the checkbox column itself (relevant only if canViewCheckbox is true)
            const idColIdx = canViewCheckbox ? 1 : 0;
            const statusColIdx = canViewCheckbox ? 4 : 3;
            const dateColIdx = canViewCheckbox ? 8 : 7;
            // Actions column is the last one, its index depends on whether checkbox column is visible
            const actionsColIdx = canViewActions ? (canViewCheckbox ? 9 : 8) : -1;

            // --- Build columnDefs Array Dynamically ---
            let dtColumnDefs = [];

            // Rule 1: Handle visibility of the checkbox column (always physical column 0 in HTML)
            // DataTables needs to know whether to show the *entire* column (header + cells)
            dtColumnDefs.push({ visible: canViewCheckbox, targets: 0 });

            // Rule 2: Define non-orderable columns targeting the indices *as seen by DataTables*
            let orderableTargets = [];
            if (canViewCheckbox) {
                orderableTargets.push(checkboxColIdx); // Checkbox column (index 0 when visible)
            }
            if (canViewActions) {
                orderableTargets.push(actionsColIdx); // Actions column (dynamic index)
            }
            // Add the orderable rule only if there are targets
            if (orderableTargets.length > 0) {
                dtColumnDefs.push({ orderable: false, targets: orderableTargets });
            }

            // Rule 3: Define the date column type using its dynamic index
            dtColumnDefs.push({ type: 'date', targets: dateColIdx });


            // --- Initialize Date Range Picker ---
            $('#kt_daterangepicker_1').daterangepicker({
                autoApply: true,
                opens: 'left',
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            }, function(start, end) {
                if (start && end) {
                    // console.log("Selected range: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));

                    // Push the date range filter function
                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        // Use the DYNAMIC date column index for filtering
                        let rowDateStr = data[dateColIdx];
                        if (!rowDateStr) return false; // Handle potentially empty date cells
                        let rowDate = moment(rowDateStr, 'YYYY-MM-DD'); // Specify format for reliable parsing
                        // Check if parsing was successful before comparison
                        return rowDate.isValid() && rowDate.isBetween(start, end, 'day', '[]');
                    });
                    table.draw();
                    // Remove the MOST RECENTLY ADDED function from the search array
                    // Important if other filters might exist.
                    $.fn.dataTable.ext.search.pop();
                }
            });

            $('#kt_daterangepicker_1').on('cancel.daterangepicker', function() {
                $(this).val('');
                // Clear *all* custom search functions - adjust if you have other persistent filters
                $.fn.dataTable.ext.search = [];
                table.draw();
            });

            $('#date-range-picker-icon').on('click', function() {
                $('#kt_daterangepicker_1').trigger('click');
            });


            // --- Initialize DataTable ---
            table = $("#kt_stores_table").DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                order: [[idColIdx, 'asc']], // Order by the DYNAMIC ID column index
                columnDefs: dtColumnDefs,    // Use the dynamically built columnDefs
                searching: true,
                paging: true,
                info: true,
                drawCallback: function( settings ) {
                    // Initialize Select2
                    $('.form-select[data-control="select2"]').select2({ // Be more specific with selector
                        minimumResultsForSearch: Infinity,
                        placeholder: "Status" // Add placeholder text back if desired
                    });
                    // Status filter event - Ensure this runs only once if needed
                    $('.form-select[data-kt-ecommerce-order-filter="status"]').off('select2:select select2:unselect').on('select2:select select2:unselect', function() {
                        var status = $(this).val();
                        // Use the DYNAMIC status column index for filtering
                        table.column(statusColIdx).search('').draw(); // Clear previous search

                        switch(status) {
                            case 'active':
                                table.column(statusColIdx).search('^\\s*Active\\s*$', true, false).draw();
                                break;
                            case 'inactive':
                                table.column(statusColIdx).search('^\\s*Inactive\\s*$', true, false).draw();
                                break;
                            case 'all':
                            case null: // Handle clearing the select box
                            default: // Default case handles 'all' and unexpected values
                                table.column(statusColIdx).search('').draw();
                                break;
                        }
                    });
                }
            });

            // Global search input
            $('input[data-kt-store-table-filter="search"]').on('keyup', function() {
                table.search($(this).val()).draw();
            });

            // --- Bulk Delete Logic (Only if checkboxes are visible) ---
            if (canViewCheckbox) { // Check if user can delete, implies checkboxes are active
                const $selectAllCheckbox = $('#select-all');
                // Select row checkboxes more specifically inside the table body
                const $rowCheckboxesSelector = '#kt_stores_table tbody .row-checkbox';
                const $deleteSelectedToolbar = $('#delete-selected-toolbar');
                const $selectedCount = $('#selected-count');
                const $otherToolbarElements = $('[data-kt-item-table-toolbar="base"]');

                function updateToolbarVisibility() {
                    const selectedCheckboxes = $($rowCheckboxesSelector + ':checked');
                    const count = selectedCheckboxes.length;
                    $selectedCount.text(count);

                    if (count > 0) {
                        $deleteSelectedToolbar.removeClass('d-none');
                        hideOtherToolbarElements();
                    } else {
                        $deleteSelectedToolbar.addClass('d-none');
                        showOtherToolbarElements();
                    }
                }

                function hideOtherToolbarElements() {
                    $otherToolbarElements.addClass('d-none');
                }

                function showOtherToolbarElements() {
                    $otherToolbarElements.removeClass('d-none');
                }

                // Select All checkbox handler
                $selectAllCheckbox.on('change', function() {
                    // Target only checkboxes within the current table's body
                    $($rowCheckboxesSelector).prop('checked', $(this).prop('checked'));
                    updateToolbarVisibility();
                });

                // Individual row checkbox handler using event delegation
                $('#kt_stores_table tbody').on('change', '.row-checkbox', function() {
                    // Update "Select All" checkbox state
                    const totalCheckboxes = $($rowCheckboxesSelector).length;
                    const checkedCheckboxes = $($rowCheckboxesSelector + ':checked').length;
                    $selectAllCheckbox.prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
                    updateToolbarVisibility();
                });

                // Delete Selected button handler
                $('#delete-selected-btn').on('click', function() {
                    const selectedIds = $($rowCheckboxesSelector + ':checked')
                        .map(function() {
                            return $(this).val();
                        })
                        .get();

                    if (selectedIds.length > 0) {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete them!' // Updated text
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteSelectedRows(selectedIds);
                            }
                        });
                    } else {
                        Swal.fire('No rows selected', 'Please select rows to delete.', 'info');
                    }
                });

                function deleteSelectedRows(selectedIds) {
                    $.ajax({
                        url: '/stores-delete', // Ensure this route is defined correctly for POST
                        method: 'POST',
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Ensure meta tag exists
                        },
                        data: JSON.stringify({
                            ids: selectedIds
                        }),
                        success: function(data) {
                            if (data.success) {
                                Swal.fire('Deleted!', data.message, 'success').then(() => {
                                    // Iterate and remove rows from DataTable
                                    selectedIds.forEach(function(id) {
                                        // Find the row using the data attribute and remove it
                                        table.row(`tr[data-store-id="${id}"]`).remove();
                                    });
                                    table.draw(false); // Redraw table without resetting page/filters
                                    $selectAllCheckbox.prop('checked', false); // Uncheck select all
                                    updateToolbarVisibility(); // Hide toolbar
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to delete selected stores.', 'error');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.fire('Error', 'Something went wrong. Please check console for details.', 'error');
                            console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                        }
                    });
                }

                // Initial check in case the page loads with some checkboxes checked (e.g., browser back button)
                updateToolbarVisibility();

            } // end if(canViewCheckbox) for bulk delete logic

            // --- Individual Delete Form Handler (using event delegation) ---
            if(canViewActions) { // Only attach if actions column might contain a delete form
                $('#kt_stores_table tbody').on('submit', 'form.delete-form', function(event) {
                    event.preventDefault(); // Prevent default form submission
                    const form = $(this);

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6', // Standard blue
                        cancelButtonColor: '#d33',     // Standard red
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proceed with standard form submission which will trigger backend logic and page reload
                            form.get(0).submit(); // Use native submit to bypass jQuery handler after confirmation
                            // If using AJAX delete instead (optional):
                            // Call an AJAX delete function here similar to deleteSelectedRows but for a single ID from form action
                            // On success: table.row(form.closest('tr')).remove().draw(false); Swal.fire(...);
                        }
                    });
                });
            }

        });
    </script>
@endsection<style>
    .actions-cell {
        display: flex;
        align-items: center;
        gap: 5px;
        justify-content: flex-end;
    }

    .actions-cell form {
        margin-bottom: 0;
        display: inline-block;
    }
</style>
