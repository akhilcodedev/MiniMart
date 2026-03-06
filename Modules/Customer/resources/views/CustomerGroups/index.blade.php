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
                        {{-- Search bar - Usually doesn't need specific permission unless the entire list is restricted --}}
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" data-kt-item-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search Groups" />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end" data-kt-item-table-toolbar="base">
                            {{-- Add Group Button - Permission Check --}}
                            @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('customer_group_create'))
                                <a href="{{ route('customer.groups.create') }}" class="btn btn-primary">Add Group</a>
                            @endif
                        </div>
                        {{-- NOTE: Export button was in the previous example but removed here. If needed, add it back with appropriate permission checks --}}
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    {{-- Table - Assume access controlled by route middleware (e.g., 'customer_group_list' or general view) --}}
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_items_table">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            {{-- Table Headers --}}
                            <th class="w-10px pe-2">ID</th>
                            <th class="min-w-125px">Name</th>
                            <th class="min-w-250px">Description</th>
                            <th class="min-w-125px">Discount Rate</th>
                            <th class="text-end min-w-70px">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @foreach($customerGroups as $item)
                            <tr>
                                {{-- Table Data Cells --}}
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->discount_rate ? $item->discount_rate . '%' : 'N/A' }}</td> {{-- Added check for null discount_rate --}}
                                <td class="text-end">
                                    {{-- Edit Button - Permission Check --}}
                                    @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('customer_group_edit'))
                                        <a href="{{ route('customer.groups.edit', $item->id) }}" class="btn btn-sm btn-light btn-active-light-primary me-2">
                                            <i class="fas fa-edit"></i> Edit</a>
                                    @endif

                                    {{-- Delete Button/Form - Permission Check --}}
                                    @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('customer_group_delete'))
                                        <form action="{{ route('customer.groups.destroy', $item->id) }}" method="POST" id="delete-form-{{ $item->id }}" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            {{-- The button triggers JS confirmation, which then submits the form --}}
                                            <button type="button" class="btn btn-sm btn-light btn-active-light-danger delete-banner" onclick="confirmDelete({{ $item->id }})">
                                                <i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    @endif

                                    {{-- Show View Button only if user CANNOT edit (optional)
                                        Alternatively, add a specific 'customer_group_view' permission check
                                        Here, we assume Edit/Delete imply sufficient view rights for a list.
                                        If a dedicated view page existed, you'd add a view button here.
                                   @if(Auth::user()->is_super_admin == 1 || Auth::user()->can('customer_group_view'))
                                       <a href="{{ route('customer.groups.show', $item->id) }}" class="btn btn-sm btn-light btn-active-light-info me-2">
                                           <i class="fas fa-eye"></i> View</a>
                                   @endif
                                   --}}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>

    {{-- Export Modal (If needed) - Consider adding permission check around the trigger button and potentially the modal itself --}}
    {{--
    <div class="modal fade" id="kt_items_export_modal" tabindex="-1" aria-hidden="true">
        ... (Modal content as before) ...
    </div>
    --}}
@endsection

@section('custom-js-section')
    {{-- Include SweetAlert CSS/JS if not already in the main layout --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Ensure jQuery and DataTables JS are loaded (usually in the main layout) --}}

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $("#kt_items_table").DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50], // Options for number of rows per page
                order: [[1, 'asc']],       // Default sort by 'Name' column ascending
                columnDefs: [
                    { orderable: false, targets: [0, 4] } // Disable sorting for ID and Actions columns
                ],
                searching: true,           // Enable searching/filtering
                paging: true,              // Enable pagination
                info: true,                // Show table information (e.g., "Showing 1 to 10 of 57 entries")
                // Add responsive features if needed:
                // responsive: true,
                // Add language customization if needed:
                // language: { search: "_INPUT_", searchPlaceholder: "Search..." }
            });

            // Live search functionality
            $('[data-kt-item-table-filter="search"]').on('keyup', function() {
                table.search($(this).val()).draw(); // Apply search filter on keyup and redraw table
            });

            // Export Modal related JS (if modal is used)
            // $('#kt_items_export_close, #kt_items_export_cancel').on('click', function() { ... });
            // $('#kt_items_export_form').on('submit', function(e) { ... });
        });

        // Function to show SweetAlert confirmation for delete action
        function confirmDelete(itemId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!", // Updated text slightly
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel', // Changed cancel button text
                confirmButtonColor: '#d33', // Red for delete button
                cancelButtonColor: '#3085d6', // Blue for cancel button
                reverseButtons: true // Put confirm button on the right
            }).then((result) => {
                // If the user confirmed the action
                if (result.isConfirmed) {
                    // Find the form with the specific ID and submit it
                    document.getElementById('delete-form-' + itemId).submit();
                }
            });
        }
    </script>
@endsection
