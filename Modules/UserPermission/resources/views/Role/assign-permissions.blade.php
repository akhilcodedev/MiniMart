@extends('base::layouts.mt-main')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

@section('content')


    <div class="card">
        <div class="card-header border-0 pt-5">
            <div class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Assign Permissions to Role</span>
            </div>
        </div>
        <div class="card-body py-3">
            {{-- Ensure the user has permission to assign roles/permissions before showing the form --}}
            @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('assign_permissions_role')))
                <form action="{{ url('/roles/assign-permissions-user') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div
                                class="d-flex flex-wrap flex-stack mb-6 border border-solid border-gray-300 rounded px-7 py-3 mb-6 h-100">
                                <h5 class="fw-bold my-2">Select Role</h5>
                                <div class="d-flex flex-wrap my-2 w-100">
                                    <div class="me-4">
                                        <select name="role_id" id="role" data-control="select2" data-hide-search="false"
                                                class="form-select form-select-md bg-light border-body min-w-400px" required> {{-- Added required --}}
                                            <option value="" disabled selected="selected">Select a role</option> {{-- Changed value to empty for better validation --}}
                                            @foreach($unassignedRoles as $unassignedRole)
                                                @if($unassignedRole->name !== 'super_admin') {{-- Check by name for super admin --}}
                                                <option value="{{ $unassignedRole->id }}">{{ $unassignedRole->label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div
                                class="d-flex flex-wrap flex-stack mb-6 border border-solid border-gray-300 rounded px-7 py-3 mb-6 align-items-start h-100">
                                <h5 class="fw-bold my-2">Select Permissions</h5>
                                <div class="form-check form-check-sm form-check-custom form-check-solid w-100">
                                    <input class="form-check-input" type="checkbox" id="select-all-permissions">
                                    <label class="form-check-label" for="select-all-permissions">
                                        Select All
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-9">
                        @foreach($permissions as $module => $modulePermissions)
                            <div class="col-md-12">
                                <div
                                    class="d-flex flex-wrap flex-stack mb-6 border border-dashed border-gray-300 rounded p-5 mb-4">
                                    <div class="col-md-12 mb-5">
                                        @php
                                            // Use the original module name directly for display, slugify for IDs
                                            $module_display_name = $module; // Assuming $module key is human-readable like "User Management"
                                            $module_slug = \Illuminate\Support\Str::slug($module_display_name); // Generate a slug for IDs/classes
                                        @endphp
                                        <div
                                            class="form-check form-check-sm form-check-custom form-check-solid w-100 align-items-center">
                                            <input class="form-check-input permission-module-checkbox me-2" type="checkbox"
                                                   id="{{$module_slug}}-module">
                                            <h5 class="m-0">{{ $module_display_name }}</h5> {{-- Use the display name --}}
                                        </div>
                                    </div>
                                    @foreach($modulePermissions as $permission)
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid w-100">
                                                <input
                                                    class="form-check-input permission-checkbox {{$module_slug}}-module" {{-- Use slug --}}
                                                type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    id="perm-{{ $permission->id }}" data-module-slug="{{$module_slug}}"> {{-- Changed module attr to data-module-slug and used slug --}}
                                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                    {{ $permission->label }} {{-- Assuming permission object has a 'label' property --}}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary">Assign Permissions</button>
                </form>
            @else
                <div class="alert alert-warning">You do not have permission to assign permissions to roles.</div>
            @endif
        </div>
    </div>

    <div class="card mt-9">
        <div class="card-header border-0 pt-5">
            <div class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Roles and Assigned Permissions</span>
            </div>
        </div>
        <div class="card-body py-3">
            {{-- Ensure user can list roles before showing table --}}
            @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('list_role') || auth()->user()->can('list_permissions_role')))
                <div class="table-responsive">
                    <table id="role_permissions"
                           class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3 dataTable">
                        <thead>
                        <tr class="fw-bold text-muted bg-light border-bottom-0">
                            <th class="ps-4 min-w-150px rounded-start">Role</th>
                            <th class="min-w-100px text-start">Created By</th>
                            <th class="min-w-350px">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- No need for this separate query if $roles passed from controller already contains roles --}}
                        {{--
                        @php
                            // It's generally better to filter this in the controller
                            $activeRoles = $roles->whereNull('deleted_at')->unique('name');
                        @endphp
                        --}}

                        @foreach($roles->where('name', '!=', 'super_admin') as $role) {{-- Filter super_admin here, assumes $roles is passed from controller --}}
                        <tr>
                            <td class="ps-4">{{ $role->label ?? $role->name }}</td> {{-- Prefer label, fallback to name --}}
                            <td class="text-start">
                                @if (is_numeric($role->created_by))
                                    @php
                                        // Eager load creator if possible in the controller for performance
                                        $creator = \App\Models\User::find($role->created_by);
                                    @endphp
                                    {{-- Display creator name, handle null creator --}}
                                    {{ $creator->name ?? 'User Not Found' }}
                                @else {{-- Assuming system roles might not have a numeric created_by --}}
                                System/Admin
                                @endif

                            </td>
                            <td>
                                {{-- Show Permissions Button - Check if user is super admin OR has 'show_permission_history' or 'list_permissions_role' permission --}}
                                @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('show_permission_history') || auth()->user()->can('show_permission_history')))
                                    <button type="button" class="btn btn-light-primary show-permissions-btn btn-sm me-4"
                                            {{-- data-role="{{ $role->name }}" --}} {{-- Using label might be clearer for the user --}}
                                            {{-- Eager load permissions with module in controller for efficiency --}}
                                            data-permissions="{{ $role->permissions->groupBy('module')->map(function ($permissions, $module) {
                                                        return $permissions->map(function ($permission) {
                                                            return ['label' => $permission->label]; // Ensure 'label' exists on your Permission model
                                                        });
                                                    })->toJson() }}"
                                            data-label="{{ $role->label ?? $role->name }}"> {{-- Use label or name for the button's context --}}
                                        Show Permissions
                                    </button>
                                @endif


                                {{-- Edit Permissions Link - Check if user is super admin OR has 'edit_permissions_role' permission --}}
                                @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_permissions_role')))
                                    <a href="{{ url('roles/' . $role->id . '/edit-permissions') }}"
                                       class="btn btn-bg-light btn-color-muted btn-active-color-primary btn-sm px-4 me-2">
                                        Edit Permissions {{-- More descriptive text --}}
                                    </a>
                                @endif

                                {{-- Potential Delete Role Button (Add if needed) - Check if user is super admin OR has 'delete_role' permission --}}
                                {{-- @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_role')))
                                    <form action="{{ url('roles/' . $role->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light-danger btn-sm px-4">Delete Role</button>
                                    </form>
                                @endif --}}
                            </td>
                        </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>
            @else
                <div class="alert alert-warning">You do not have permission to view roles and their permissions.</div>
            @endif
        </div>
    </div>

@stop

{{-- Modal remains unchanged as it's for displaying data --}}
<div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog" aria-labelledby="permissionsModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                {{-- Title will be set dynamically by JS --}}
                <h2 class="fw-bold mb-0" id="permissionsModalLabel">Permissions</h2>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                     aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body" id="permissionsModalBody">
                {{-- Content will be populated by JS --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btn-close" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Scripts remain mostly unchanged, adjusted module checkbox logic slightly --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
{{-- JQuery is required for Select2 and the included script, ensure it's loaded before this script if not already loaded by the layout --}}
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
{{-- Select2 JS (needs CSS too, add link tag if not in layout) --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet">


@section('custom-js-section')
    {{-- Include the original permission JS file if it contains necessary logic --}}
    {{-- @include('userpermission::Role.permission-js') --}}

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#role').select2({
                placeholder: "Select a role",
                allowClear: true // Optional: Add a clear button
            });

            // --- Show Permissions Modal Logic ---
            $('.show-permissions-btn').on('click', function () {
                var roleLabel = $(this).data('label');
                var permissionsData = $(this).data('permissions'); // Expecting object directly if parsed correctly
                // var permissions = permissionsData ? JSON.parse(permissionsData) : {}; // Use this line if data-permissions is still a string
                var permissions = typeof permissionsData === 'string' ? JSON.parse(permissionsData) : permissionsData; // More robust check

                var modalBody = $('#permissionsModalBody');
                var modalTitle = $('#permissionsModalLabel');
                modalBody.empty(); // Clear previous content
                modalTitle.text(`Permissions for ${roleLabel}`);

                var permissionsHtml = '<div class="row g-5 g-xl-8">';
                var permissionsExist = false;

                // Get unique module names from the permissions data
                var modules = Object.keys(permissions);

                if (modules.length > 0) {
                    permissionsExist = true;
                    // Create columns - Aim for roughly 3 columns (col-md-4)
                    let colCount = 0;
                    modules.forEach(function (module) {
                        if (permissions[module] && permissions[module].length > 0) {
                            permissionsHtml += '<div class="col-md-4">'; // Start new column
                            permissionsHtml += '<div class="card mb-6 h-100">';
                            permissionsHtml += '<div class="card-header">';
                            permissionsHtml += `<h3 class="card-title fw-bold">${module}</h3>`; // Display Module Name
                            permissionsHtml += '</div>';
                            permissionsHtml += '<div class="card-body pt-2">'; // Reduced top padding
                            permissionsHtml += '<ul class="list-unstyled">'; // Use a list for permissions
                            permissions[module].forEach(function (permission) {
                                permissionsHtml += `<li class="mb-1"><i class="ki-duotone ki-check-square fs-5 text-success me-2"><span class="path1"></span><span class="path2"></span></i>${permission.label}</li>`;
                            });
                            permissionsHtml += '</ul></div></div></div>'; // Close card-body, card, col-md-4
                            colCount++;
                        }
                    });


                }

                permissionsHtml += '</div>'; // Close row


                if (permissionsExist) {
                    modalBody.html(permissionsHtml);
                } else {
                    modalBody.html('<div class="alert alert-info">No specific permissions assigned to this role.</div>');
                }

                // Use Bootstrap's JS to get the modal instance and show it
                var permissionsModal = new bootstrap.Modal(document.getElementById('permissionsModal'));
                permissionsModal.show();
            });

            // --- Close Modal Logic (redundant if using data-bs-dismiss) ---
            // $('#btn-close').on('click', function () {
            //     var modal = bootstrap.Modal.getInstance(document.getElementById('permissionsModal'));
            //     modal.hide();
            // });

            // --- Select/Deselect All Permissions ---
            $('#select-all-permissions').on('click', function () {
                var isChecked = this.checked;
                $('.permission-checkbox').prop('checked', isChecked);
                $('.permission-module-checkbox').prop('checked', isChecked); // Sync module checkboxes too
            });

            // --- Select/Deselect All Permissions within a Module ---
            $('.permission-module-checkbox').on('click', function () {
                var isChecked = this.checked;
                var moduleSlug = this.id.replace('-module', ''); // Get slug from ID
                // Find checkboxes associated with this module using the data attribute or class
                // Option 1: Using data attribute (better if class name has issues)
                // $(`.permission-checkbox[data-module-slug="${moduleSlug}"]`).prop('checked', isChecked);
                // Option 2: Using class name (make sure slug generation is consistent)
                $(`.permission-checkbox.${moduleSlug}-module`).prop('checked', isChecked);

                // Update the 'Select All' checkbox state
                updateSelectAllState();
            });

            // --- Update Module Checkbox and Select All based on individual permission changes ---
            $('.permission-checkbox').on('click', function() {
                var moduleSlug = $(this).data('module-slug'); // Get slug from data attribute
                var moduleCheckboxes = $(`.permission-checkbox[data-module-slug="${moduleSlug}"]`);
                var moduleCheckbox = $(`#${moduleSlug}-module`);

                // Check if all permissions in this module are checked
                var allCheckedInModule = moduleCheckboxes.length === moduleCheckboxes.filter(':checked').length;
                moduleCheckbox.prop('checked', allCheckedInModule);

                // Update the main 'Select All' checkbox state
                updateSelectAllState();
            });

            // Helper function to update the main 'Select All' checkbox
            function updateSelectAllState() {
                var allPermissionCheckboxes = $('.permission-checkbox');
                var allAreChecked = allPermissionCheckboxes.length === allPermissionCheckboxes.filter(':checked').length;
                $('#select-all-permissions').prop('checked', allAreChecked);
            }

            // Initial check in case the form is repopulated (e.g., validation error)
            $('.permission-module-checkbox').each(function() {
                var moduleSlug = this.id.replace('-module', '');
                var moduleCheckboxes = $(`.permission-checkbox[data-module-slug="${moduleSlug}"]`);
                var allCheckedInModule = moduleCheckboxes.length > 0 && moduleCheckboxes.length === moduleCheckboxes.filter(':checked').length;
                $(this).prop('checked', allCheckedInModule);
            });
            updateSelectAllState(); // Set initial state of 'Select All'

        }); // End document ready
    </script>

@endsection
