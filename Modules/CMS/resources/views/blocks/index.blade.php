@extends('base::layouts.mt-main')

{{-- Add CSRF Token for AJAX requests --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold">CMS Static Blocks</h2>
        {{-- Show Create button only if user has permission or is super admin --}}
        @if(auth()->user()->is_super_admin || auth()->user()->can('create_cms_blocks'))
            <a class="btn btn-primary" href="{{ route('cms-blocks.create') }}">
                <i class="fas fa-plus me-1"></i>
                {{ __('Create New Block') }}
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-header border-0 pt-5">
            {{-- Optional: Add filtering/search controls here if needed --}}
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table id="cms_blocks_listing" class="table table-striped table-row-bordered gy-5 gs-7">
                    <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Identifier') }}</th>
                        <th>{{ __('Store') }}</th>
                        <th>{{ __('Status') }}</th>
                        {{-- Show Actions column header only if user has edit or delete permission or is super admin --}}
                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_cms_blocks') || auth()->user()->can('delete_cms_blocks'))
                            <th class="min-w-100px text-end">{{ __('Actions') }}</th> {{-- Added text-end for consistency --}}
                        @endif
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    @foreach($blocks as $block)
                        <tr>
                            <td>
                                {{-- Link to edit only if user has edit permission or is super admin --}}
                                @if(auth()->user()->is_super_admin || auth()->user()->can('edit_cms_blocks'))
                                    <a href="{{ route('cms-blocks.edit', $block->id) }}" class="text-gray-800 text-hover-primary">
                                        {{ $block->title }}
                                    </a>
                                @else
                                    {{ $block->title }}
                                @endif
                            </td>
                            <td>{{ $block->identifier }}</td>
                            <td>
                                {{-- Using optional() is good practice here --}}
                                {{ optional(\Modules\StoreManagement\Models\Store::find($block->store_id))->name ?? 'All Stores' }} {{-- Adjusted default text --}}
                            </td>
                            <td>
                            <span class="badge {{ $block->is_active ? 'badge-light-success' : 'badge-light-danger' }} fw-bold fs-7">
                                 {{ $block->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            </td>
                            {{-- Show Actions cell content only if user has edit or delete permission or is super admin --}}
                            @if(auth()->user()->is_super_admin || auth()->user()->can('edit_cms_blocks') || auth()->user()->can('delete_cms_blocks'))
                                <td class="text-end"> {{-- Added text-end for consistency --}}
                                    <div class="d-flex align-items-center justify-content-end"> {{-- Removed unnecessary justify-content-end as text-end is on td --}}
                                        {{-- Show Edit button only if user has permission or is super admin --}}
                                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_cms_blocks'))
                                            <a class="btn btn-sm btn-light btn-active-light-primary me-2 d-flex align-items-center" href="{{ route('cms-blocks.edit', $block->id) }}">
                                                <i class="fas fa-edit me-1"></i> {{ __('Edit') }}
                                            </a>
                                        @endif
                                        {{-- Show Delete button only if user has permission or is super admin --}}
                                        @if(auth()->user()->is_super_admin || auth()->user()->can('delete_cms_blocks'))
                                            <button class="btn btn-sm btn-light btn-active-light-danger deleteCmsBlock d-flex align-items-center" data-id="{{ $block->id }}" type="button">
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
            // Check if user can edit or delete to determine if Actions column exists
            const canModify = {{ (auth()->user()->is_super_admin || auth()->user()->can('edit_cms_blocks') || auth()->user()->can('delete_cms_blocks')) ? 'true' : 'false' }};

            let dataTableOptions = {
                dom: 'frtip', // Basic datatables layout (search, processing, table, info, pagination)
                "order": [], // Disable initial ordering
            };

            // If the actions column exists (index 4), disable ordering for it
            if (canModify) {
                dataTableOptions.columnDefs = [
                    { "orderable": false, "targets": 4 } // Target the 5th column (index 4)
                ];
            }

            $('#cms_blocks_listing').DataTable(dataTableOptions);


            // Attach delete handler only if delete buttons can potentially exist
            if ({{ auth()->user()->is_super_admin || auth()->user()->can('delete_cms_blocks') ? 'true' : 'false' }}) {
                $(document).on('click', '.deleteCmsBlock', function (ev) {
                    let id = $(this).data('id');
                    let deleteUrl = "{{ route('cms-blocks.delete') }}"; // Ensure this route handles POST/DELETE appropriately

                    Swal.fire({
                        text: "Are you sure you want to delete this static block?", // More specific text
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
                                url: deleteUrl, // Use the defined variable
                                type: "POST", // Or "DELETE", depends on your route definition
                                data: {
                                    'id': id,
                                    '_token': $('meta[name="csrf-token"]').attr('content') // Get CSRF token from meta tag
                                },
                                dataType: 'json',
                                success: function (res) {
                                    Swal.fire({
                                        text: res.message || "Static block deleted successfully.", // Provide default success message
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then(() => {
                                        location.reload(); // Reload page to reflect changes
                                    });
                                },
                                error: function (xhr, status, error) {
                                    // Try to get a specific error message from response
                                    let errorMsg = "Failed to delete the static block. Please try again.";
                                    if(xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    Swal.fire({
                                        text: errorMsg,
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn btn-primary" // Maintain consistent styling
                                        }
                                    });
                                }
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) { // More specific check for cancel
                            Swal.fire({
                                text: "Deletion was cancelled.", // Clearer message
                                icon: "info",
                                buttonsStyling: false, // Keep styling consistent
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
