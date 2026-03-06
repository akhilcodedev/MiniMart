@extends('base::layouts.mt-main')

{{-- Add CSRF Token for AJAX requests --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold">{{ __('Catalog Price Rules') }}</h2>
        {{-- Show Create button only if user has permission or is super admin --}}
        @if(auth()->user()->is_super_admin || auth()->user()->can('create_catalog_rules'))
            <a class="btn btn-primary" href="{{ route('catalog-price-rules.create') }}">
                <i class="fas fa-plus me-1"></i>
                {{ __('Create New Rule') }}
            </a>
        @endif
    </div>
    <div class="card">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">{{ __('Price Rules') }}</span>
                {{-- Optional: Add a count or description --}}
                {{-- <span class="text-muted mt-1 fw-semibold fs-7">{{ $catalogPriceRule->count() }} rules</span> --}}
            </h3>
            {{-- Optional: Add filter/search elements here --}}
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table id="catalog_price_rule_table" class="table table-striped table-row-bordered gy-5 gs-7">
                    <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Rule') }}</th>
                        <th>{{ __('Store') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        {{-- Show Actions column header only if user has edit or delete permission or is super admin --}}
                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_catalog_rules') || auth()->user()->can('delete_catalog_rules'))
                            <th class="text-end min-w-100px">{{ __('Actions') }}</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    @foreach($catalogPriceRule as $rule)
                        <tr>
                            <td>{{ $rule->id }}</td>
                            <td>
                                {{-- Link to edit only if user has edit permission or is super admin --}}
                                @if(auth()->user()->is_super_admin || auth()->user()->can('edit_catalog_rules'))
                                    <a href="{{ route('catalog-price-rules.edit', $rule->id) }}" class="text-gray-800 text-hover-primary">
                                        {{ $rule->name }}
                                    </a>
                                @else
                                    {{ $rule->name }}
                                @endif
                            </td>
                            <td>
                                {{-- Use optional() helper --}}
                                {{ optional(\Modules\StoreManagement\Models\Store::find($rule->store_id))->name ?? 'All Stores' }} {{-- Adjusted default text --}}
                            </td>
                            <td>
                                <span
                                    class="badge {{ $rule->is_active ? 'badge-light-success' : 'badge-light-danger' }} fw-bold fs-7">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $rule->priority ?? 0 }}</td>

                            {{-- Show Actions cell content only if user has edit or delete permission or is super admin --}}
                            @if(auth()->user()->is_super_admin || auth()->user()->can('edit_catalog_rules') || auth()->user()->can('delete_catalog_rules'))
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end">
                                        {{-- Show Edit button only if user has permission or is super admin --}}
                                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_catalog_rules'))
                                            <a class="btn btn-sm btn-light btn-active-light-primary me-2 d-flex align-items-center"
                                               href="{{ route('catalog-price-rules.edit', $rule->id) }}">
                                                <i class="fas fa-edit me-1"></i> {{ __('Edit') }}
                                            </a>
                                        @endif
                                        {{-- Show Delete button only if user has permission or is super admin --}}
                                        @if(auth()->user()->is_super_admin || auth()->user()->can('delete_catalog_rules'))
                                            <button
                                                class="btn btn-sm btn-light btn-active-light-danger deleteCatalogPriceRule d-flex align-items-center" {{-- Removed unnecessary me-2 here if delete is the last button --}}
                                            data-id="{{ $rule->id }}" type="button">
                                                <i class="fas fa-trash me-1"></i> {{ __('Delete') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('custom-js-section')
    <script>
        $(document).ready(function () {
            // Check if user can modify (edit or delete) to determine if Actions column exists
            const canModify = {{ (auth()->user()->is_super_admin || auth()->user()->can('edit_catalog_rules') || auth()->user()->can('delete_catalog_rules')) ? 'true' : 'false' }};

            let dataTableOptions = {
                dom: 'frtip', // Standard DataTables layout: f-filtering input, r-processing display, t-table, i-information summary, p-pagination control
                "order": [], // Disable initial sorting
            };

            // If the actions column exists (index 5), disable ordering for it
            if (canModify) {
                dataTableOptions.columnDefs = [
                    { "orderable": false, "targets": 5 } // Target the 6th column (index 5)
                ];
            }

            $('#catalog_price_rule_table').DataTable(dataTableOptions);


            // Attach delete handler only if delete buttons can potentially exist
            if({{ auth()->user()->is_super_admin || auth()->user()->can('delete_catalog_rules') ? 'true' : 'false' }}) {
                $(document).on('click', '.deleteCatalogPriceRule', function (ev) {
                    let id = $(this).data('id');
                    let deleteUrl = "{{ route('catalog-price-rules.delete') }}"; // Define URL variable

                    Swal.fire({
                        text: "Are you sure you want to delete this catalog price rule?", // Specific confirmation text
                        icon: "warning",
                        buttonsStyling: false,
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "No, cancel",
                        customClass: {
                            confirmButton: "btn btn-primary",
                            cancelButton: "btn btn-danger"
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: deleteUrl, // Use variable
                                type: "POST", // Or "DELETE", adjust according to your route definition
                                data: {
                                    'id': id,
                                    '_token': $('meta[name="csrf-token"]').attr('content') // Get CSRF token from meta tag
                                },
                                dataType: 'json',
                                success: function (res) {
                                    Swal.fire({
                                        text: res.message || "Catalog price rule deleted successfully.", // Default success message
                                        icon: res.status === 'success' ? 'success' : 'info', // Optional: Use status from response if available
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then(() => {
                                        location.reload(); // Reload page to see changes
                                    });
                                },
                                error: function (xhr, status, error) {
                                    // Try to get a specific error message from response
                                    let errorMsg = "Failed to delete the rule. Please try again.";
                                    if(xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    Swal.fire({
                                        text: errorMsg,
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn btn-primary" // Keep styling consistent
                                        }
                                    });
                                }
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) { // More specific cancel check
                            Swal.fire({
                                text: "Deletion was cancelled.",
                                icon: "info",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    });
                });
            }
        });
    </script>
@stop
