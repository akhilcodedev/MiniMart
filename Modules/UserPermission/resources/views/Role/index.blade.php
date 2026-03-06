@extends('base::layouts.mt-main')
@section('content')

    @php
        // Get authenticated user and super admin status once
        $authUser = auth()->user();
        $isSuperAdmin = $authUser && $authUser->is_super_admin == 1; // Or check for specific role
    @endphp

    <div class="card">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Roles</span>
            </h3>
            {{-- Show Create Role button if user has permission or is super admin --}}
            @if($isSuperAdmin || $authUser->can('create_role'))
                <div>
                    <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary"> {{-- More prominent Add button style --}}
                        <i class="ki-outline ki-plus fs-2"></i> Create Role
                    </a>
                </div>
            @endif
        </div>
        <div class="card-body py-3">
            <div class="table-responsive">
                <table id="rolesTable" class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3 dataTable">
                    <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-40px text-start">#</th>
                        <th class="min-w-100px text-start">Name</th>
                        <th class="min-w-100px text-start">Created By</th>
                        <th class="min-w-150px text-end">Actions</th> {{-- Adjusted min-width for buttons --}}
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td class="text-start">{{ $loop->iteration }}</td>
                            <td class="text-start">
                                {{ $role->label }} {{-- Displaying the label as 'Name' --}}
                            </td>
                            <td class="text-start">
                                @if (is_numeric($role->created_by))
                                    @php
                                        // Eager loading creator relationship in Controller is more efficient
                                        $creator = $role->creator ?? \App\Models\User::find($role->created_by);
                                    @endphp
                                    {{-- Check if creator exists and show name --}}
                                    {{ $creator->name ?? 'User Not Found' }}
                                @elseif($role->created_by === null)
                                    System Default {{-- Clarified for system roles --}}
                                @else
                                    {{ $role->created_by }} {{-- Handle other cases if needed --}}
                                @endif
                            </td>
                            <td class="text-end">
                                {{-- Show Edit Button if user has permission or is super admin --}}
                                @if($isSuperAdmin || $authUser->can('update_role'))
                                    <a href="{{ route('roles.edit', $role->id) }}"
                                       title="Edit"
                                       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                        <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </a>
                                @endif

                                {{-- Show Delete Button if user has permission or is super admin --}}
                                {{-- Add protection against deleting critical roles if necessary --}}
                                {{-- Example: && !in_array($role->name, ['super-admin', 'admin']) --}}
                                @if($isSuperAdmin || $authUser->can('delete_role'))
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display: inline-block;" class="delete-role-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" {{-- Change type to button to prevent immediate submit --}}
                                        title="Delete"
                                                class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-role-btn"
                                                data-role-name="{{ $role->label }}"> {{-- Add role name for confirm message --}}
                                            <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
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
@stop

@section('custom-js-section')
    <script src="{{ asset('assets/js/cdn/sweetalert2@11.js') }}"></script>
    {{-- jQuery and DataTables should be loaded globally or ensure they are loaded before this script --}}
    <script>
        $(document).ready(function () {
            $('#rolesTable').DataTable({
                "paging": true,      // Enable pagination
                "lengthChange": true, // Show entries per page dropdown
                "searching": true,   // Enable search box
                "ordering": true,    // Enable column sorting
                "info": true,        // Show 'Showing x to y of z entries'
                "autoWidth": false,  // Disable auto width calculation
                "responsive": true   // Enable responsiveness
            });

            // Use event delegation for delete buttons clicked within the table
            $('#rolesTable tbody').on('click', '.delete-role-btn', function (event) {
                event.preventDefault(); // Prevent default button action

                const button = $(this);
                const roleName = button.data('role-name'); // Get role name from data attribute
                const form = button.closest('form'); // Find the parent form

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete the role "${roleName}"? This may affect users assigned to this role.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: "btn btn-danger",
                        cancelButton: "btn btn-light"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Optional: Show a loading state
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit(); // Submit the form if confirmed
                    }
                });
            });
        });
    </script>
@stop
