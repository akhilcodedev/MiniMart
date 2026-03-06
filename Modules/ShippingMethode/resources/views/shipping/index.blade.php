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
    $canViewActions = userCan('edit_shipping_methods') || userCan('delete_shipping_methods');

@endphp

@extends('base::layouts.mt-main')

@section('title', 'Shipping Methods')

@section('content')
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                    <span class="svg-icon svg-icon-1 position-absolute ms-6">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor" />
                            <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor" />
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                    {{-- Search input without form - handled by JS --}}
                    <input type="text" id="shipping_method_search" value="{{ $search ?? '' }}" class="form-control form-control-solid w-250px ps-14" placeholder="Search Shipping Methods" />

                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-shipping-method-table-toolbar="base">
                    <!--begin::Add shipping method (Permission Checked) -->
                    @if(userCan('create_shipping_methods'))
                        <a href="{{ route('shipping.create') }}" class="btn btn-primary">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                            <span class="svg-icon svg-icon-2">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                      transform="rotate(-90 11.364 20.364)" fill="currentColor"/>
                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="currentColor"/>
                            </svg>
                        </span>
                            <!--end::Svg Icon-->
                            Add Shipping Method
                        </a>
                @endif
                <!--end::Add shipping method-->
                </div>
                <!--end::Toolbar-->
                {{-- Note: Group actions (bulk delete) functionality is not implemented here based on the original code.
                     If added later, apply delete_shipping_methods permission check. --}}
                <div class="d-flex justify-content-end align-items-center d-none" data-kt-shipping-method-table-toolbar="selected">
                    <div class="fw-bolder me-5">
                        <span class="me-2" data-kt-shipping-method-table-select="selected_count"></span>Selected
                    </div>
                    @if(userCan('delete_shipping_methods'))
                        <button type="button" class="btn btn-danger" data-kt-shipping-method-table-select="delete_selected">Delete Selected</button>
                    @endif
                </div>
                <!--end::Group actions-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-6">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
        @endif

        <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_shipping_method_table">
                <thead>
                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Name</th>
                    <th class="min-w-125px">Code</th>
                    <th class="min-w-100px">Status</th>
                    {{-- Conditionally render Actions column header --}}
                    @if($canViewActions)
                        <th class="text-end min-w-100px">Actions</th>
                    @endif
                </tr>
                </thead>
                <tbody class="text-gray-600 fw-bold">
                @forelse($shippingMethods as $shippingMethod)
                    <tr>
                        <td>{{ $shippingMethod->name }}</td>
                        <td>{{ $shippingMethod->code }}</td>
                        <td>
                                    <span class="badge {{ $shippingMethod->status ? 'badge-light-success' : 'badge-light-danger' }}">
                                        {{ $shippingMethod->status ? 'Active' : 'Inactive' }}
                                    </span>
                        </td>
                        {{-- Conditionally render Actions cell content --}}
                        @if($canViewActions)
                            <td class="text-end">
                                {{-- Edit Button (Permission Checked) --}}
                                @if(userCan('edit_shipping_methods'))
                                    <a href="{{ route('shipping.edit', $shippingMethod->id) }}"
                                       class="btn btn-sm btn-light btn-active-light-primary me-2">
                                        <i class="fas fa-edit"></i> {{ __('Edit') }}
                                    </a>
                                @endif

                                {{-- Delete Button Form (Permission Checked) --}}
                                @if(userCan('delete_shipping_methods'))
                                    <form action="{{ route('shipping.destroy', $shippingMethod->id) }}" method="POST"
                                          style="display:inline;" class="delete-form d-inline"> {{-- Added d-inline --}}
                                        @csrf
                                        @method('DELETE')
                                        {{-- Changed type to submit, logic handled by attached JS listener --}}
                                        <button type="submit" class="btn btn-sm btn-light btn-active-light-danger delete-button">
                                            <i class="fas fa-trash"></i> {{ __('Delete') }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        @endif {{-- End if($canViewActions) for cell --}}
                    </tr>
                @empty
                    {{-- Adjust colspan based on whether Actions column is visible --}}
                    <tr>
                        <td colspan="{{ $canViewActions ? 4 : 3 }}" class="text-center">No Shipping Methods Found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <!--end::Table-->

        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection

@section('scripts')
    {{-- Ensure jQuery and DataTables JS/CSS are loaded, preferably via your asset pipeline or layout --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> {{-- Or your bundled version --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    {{-- SweetAlert for delete confirmation --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    {{-- Remove reference to unused list.js if it conflicts --}}
    {{-- <script src="{{ asset('assets/js/custom/apps/shipping/list.js') }}"></script> --}}

    <script>
        $(document).ready(function() {

            const canViewActions = {{ $canViewActions ? 'true' : 'false' }};
            const actionsColIdx = 3; // The physical index of the actions column if present

            // Build columnDefs dynamically
            let dtColumnDefs = [];
            if (canViewActions) {
                // Target actions column index if visible
                dtColumnDefs.push({ orderable: false, targets: actionsColIdx });
            }

            const table = $('#kt_shipping_method_table').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                // Order by name (index 0) initially or Code (index 1) if preferred
                order: [[0, 'asc']],
                columnDefs: dtColumnDefs, // Use dynamic defs
                searching: true, // Use DataTable's native search configured below
                paging: true,
                info: true,
                // Disable ordering and searching for columns if needed via columnDefs
            });

            // Search input handler - target the specific input field
            $('#shipping_method_search').on('keyup', function () {
                table.search($(this).val()).draw();
            });

            // Delete confirmation using event delegation
            $('#kt_shipping_method_table').on('submit', 'form.delete-form', function(event) {
                event.preventDefault(); // Stop direct form submission
                const form = this; // The form that triggered the submit event

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
                        // Optional: AJAX delete instead
                        // $.ajax({ url: form.action, type: 'POST', data: $(form).serialize(), success: ... remove row from table.draw() ... });
                    }
                });
            });

        });
    </script>
@endsection
