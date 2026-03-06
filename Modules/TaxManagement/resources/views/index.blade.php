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

    // Determine if the Actions column should be visible
    // For Tax Classes, assuming CRUD uses "tax_methods" permission names as per the JSON
    $canViewActions = userCan('edit_tax_methods') || userCan('delete_tax_methods');

@endphp

@extends('base::layouts.mt-main')

@section('title', 'Tax Classes') {{-- Added title --}}

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        {{-- Search Input (handled by DataTables JS) --}}
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" id="tax_class_search" class="form-control form-control-solid w-250px ps-13" placeholder="Search Tax Classes" />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        {{-- Add Button (Permission Checked) --}}
                        @if(userCan('create_tax_methods'))
                            <a href="{{ route('tax.create') }}" class="btn btn-primary btn-sm">Add Tax Class</a>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_tax_classes_table"> {{-- Added ID --}}
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0"> {{-- Added gs-0 class --}}
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            {{-- Conditionally render Actions column header --}}
                            @if($canViewActions)
                                <th class="text-end">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600"> {{-- Added classes --}}
                        @forelse ($taxClasses as $tax) {{-- Use forelse --}}
                        <tr>
                            <td>{{ $tax->id }}</td>
                            <td>{{ $tax->name }}</td>
                            <td>{{ $tax->code }}</td>
                            {{-- Conditionally render Actions cell content --}}
                            @if($canViewActions)
                                <td class="text-end">
                                    {{-- Edit Button (Permission Checked) --}}
                                    @if(userCan('edit_tax_methods'))
                                        <a href="{{ route('tax.edit', $tax) }}" class="btn btn-sm btn-light btn-active-light-primary me-2"> <i class="fas fa-edit"></i> Edit</a>
                                    @endif

                                    {{-- Delete Button Form (Permission Checked) --}}
                                    @if(userCan('delete_tax_methods'))
                                        {{-- Using event delegation pattern for JS --}}
                                        <form action="{{ route('tax.destroy', $tax) }}" method="POST" class="delete-form d-inline" >
                                            @csrf
                                            @method('DELETE')
                                            {{-- Button type=submit triggers form submission handled by JS --}}
                                            <button type="submit" class="btn btn-sm btn-light btn-active-light-danger delete-button">
                                                <i class="fas fa-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            @endif {{-- End $canViewActions for cell --}}
                        </tr>
                        @empty {{-- Handle empty results --}}
                        {{-- Adjust colspan based on whether Actions column is visible --}}
                        <tr>
                            <td colspan="{{ $canViewActions ? 4 : 3 }}" class="text-center">No Tax Classes Found</td>
                        </tr>
                        @endforelse {{-- End forelse --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts') {{-- Use @section('scripts') for consistency --}}
{{-- Ensure jQuery, DataTables JS/CSS, and SweetAlert JS/CSS are loaded --}}
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function() {
        const canViewActions = {{ $canViewActions ? 'true' : 'false' }};
        const actionsColIdx = 3; // Actions column index if visible (0-based)

        // Build columnDefs dynamically
        let dtColumnDefs = [];
        if (canViewActions) {
            // Target actions column index if visible
            dtColumnDefs.push({ orderable: false, targets: actionsColIdx });
        }

        // Initialize DataTables
        const table = $('#kt_tax_classes_table').DataTable({
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[0, 'asc']], // Default order by ID
            columnDefs: dtColumnDefs,
            searching: true, // Enable native search
            paging: true,
            info: true,
        });

        // Search input handler
        $('#tax_class_search').on('keyup', function () {
            table.search($(this).val()).draw();
        });

        // Delete confirmation using event delegation on the table
        $('#kt_tax_classes_table').on('submit', 'form.delete-form', function(event) {
            event.preventDefault(); // Prevent default form submission
            const form = this;    // The form element

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form if confirmed
                    // Optional AJAX Delete:
                    /*
                    $.ajax({
                        url: form.action, type: 'POST', data: $(form).serialize(),
                        success: function(response) {
                           Swal.fire('Deleted!','Tax class has been deleted.','success');
                           table.row($(form).closest('tr')).remove().draw(false);
                        },
                        error: function(xhr) { Swal.fire('Error!', 'Could not delete tax class.', 'error'); }
                    });
                    */
                }
            });
        });

    }); // End document ready

    // Note: Removed the previous inline <script> block containing confirmDelete and the search input listener,
    // as these are now handled cleanly within the $(document).ready block using DataTables and event delegation.

</script>
@endsection
