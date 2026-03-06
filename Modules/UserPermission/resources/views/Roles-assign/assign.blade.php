@extends('base::layouts.mt-main')
@section('content')
    @php
        $authUser = auth()->user();
        $isSuperAdmin = $authUser && $authUser->is_super_admin == 1;
    @endphp

    <div class="card mb-6">
        <div class="card-header border-0 pt-5">
            <div class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Assign Roles to Users</span>
            </div>
        </div>
        <div class="card-body pt-3">
            <form action="{{ url('users/assign-roles') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex flex-wrap flex-stack mb-6 border border-solid border-gray-300 rounded px-7 py-3 mb-6 h-100">
                            <h5 class="fw-bold my-2">Select User</h5>
                            <div class="d-flex flex-wrap my-2 w-100 position-relative">
                                <select name="user_id" id="user" data-control="select2" data-hide-search="false"
                                        class="form-select form-select-md bg-light border-body min-w-400px">
                                    <option value="Active" disabled selected="selected">Select a user</option>
                                    @foreach($roleUnassignedUsers as $roleUnassignedUser)
                                        <option value="{{ $roleUnassignedUser->id }}">{{ $roleUnassignedUser->name }} ({{ $roleUnassignedUser->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-wrap flex-stack mb-6 border border-solid border-gray-300 rounded px-7 py-3 mb-6 align-items-start h-100">
                            <h5 class="fw-bold my-2">Select Role</h5>
                            <div class="d-flex flex-wrap my-2 w-100 position-relative">
                                @php
                                    $hiddenRoles = ['Vendor', 'super_admin'];
                                @endphp
                                <select name="role_name" id="role" data-control="select2" data-hide-search="false"
                                        class="form-select form-select-md bg-light border-body w-100 min-w-[100%]">
                                    <option value="Active" disabled selected="selected">Select a role</option>
                                    @foreach($roles as $role)
                                        @if (!in_array($role->name, ['Vendor', 'super_admin']))
                                            <option value="{{ $role->name }}">{{ $role->label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                @if($isSuperAdmin || $authUser->can('assign_users_role'))

                <div class="row">
                    <div class="col mt-9">
                        <button type="submit" class="btn btn-primary">Assign Permissions</button>
                    </div>
                </div>
                @endif

            </form>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-header border-0 pt-5">
            <div class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Users and Assigned Roles</span>
            </div>
        </div>
        <div class="card-body py-3">
            <div class="table-responsive">
                <table id="role_users_table" class="table table-row-dashed table-row-gray-300 align-start gs-0 gy-4 dataTable">
                    <thead>
                    <tr class="fw-bold text-muted bg-light border-bottom-0">
                        <th class="ps-4 min-w-150px rounded-start">User</th>
                        <th class="min-w-350px">Role</th>
                        <th class="min-w-100px text-end rounded-end pe-4">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="ps-4">{{ $user->name }} ({{ $user->email }})</td>
                            <td>
                                @if($user->roles->isEmpty())
                                    <span class="text-muted">No role assigned</span>
                                @else
                                    <ul class="ps-4">
                                        @foreach($user->roles as $role)
                                            <li>{{ $role->label }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($isSuperAdmin || $authUser->can('edit_users_role'))

                                <a href="#" class="btn btn-bg-light btn-color-muted btn-active-color-primary btn-sm px-4 me-2 edit-user-role"
                                   data-bs-toggle="modal"
                                   data-bs-target="#edit_role_for_user_admin"
                                   data-user-id="{{ $user->id }}"
                                   data-user-name="{{ $user->name }}"
                                   data-user-role="{{ $user->roles->isEmpty() ? 'No role assigned' : $user->roles->first()->name }}"
                                   data-user-label="{{ $user->roles->isEmpty() ? 'No role assigned' : $user->roles->first()->label }}">Edit
                                </a>
                                @endif
                                    <form action="{{ route('users.delete',$user->id) }}" method="POST" style="display:inline;" id="delete-user-form">
                                    @csrf
                                    @method('DELETE')
                                        @if($isSuperAdmin || $authUser->can('delete_users_role'))

                                        <button type="submit" class="btn btn-bg-danger btn-color-white btn-sm px-4">Delete</button>
                                            @endif
                                </form>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="edit_role_for_user_admin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Edit Role for User: <span id="modal-user-name"></span></h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="edit-role-form" method="POST" action="{{ route('users.assign-roles.store') }}">
                    @csrf
                    <!-- Remove @method('POST') -->

                        <div class="form-group">
                            <h5 class="fs-6 fw-semibold form-label mb-4">Select Role</h5>
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-md-6 mb-6">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="radio" name="role_name"
                                                   value="{{ $role->name }}" id="role-{{ $role->id }}">
                                            <label class="form-check-label" for="role-{{ $role->id }}">
                                                {{ $role->label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

@stop

@section('custom-js-section')

    <script>
        $(document).ready(function () {
            $('#role_users_table').DataTable();

            $('.edit-user-role').on('click', function () {
                var userId = $(this).data('user-id');
                var userName = $(this).data('user-name');
                var userRole = $(this).data('user-role');
                var userRoleLabel = $(this).data('user-label');

                // Set modal title
                $('#modal-user-name').text(userName);

                // Set form action
                $('#edit-role-form').attr('action', '/users/' + userId + '/edit-roles');

                // Set radio buttons to checked based on user role
                $('input[name="role_name"]').each(function () {
                    if ($(this).val() === userRole || $(this).val() === userRoleLabel) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const selectElement = document.getElementById('role');
            const options = selectElement.options;


            for (let i = 0; i < options.length; i++) {
                if (options[i].text.toLowerCase() === 'super admin') {
                    options[i].style.display = 'none';
                }
            }
        });

        $(document).ready(function () {
            $("#edit-role-form").submit(function (e) {
                e.preventDefault(); // Prevent page reload

                let form = $(this);
                let formData = form.serialize();
                let actionUrl = form.attr("action");

                $.ajax({
                    url: actionUrl,
                    type: "POST", // Ensure it's POST
                    data: formData,
                    success: function (response) {
                        Swal.fire("Success", "Role updated successfully!", "success");
                       location.reload(); // Refresh page
                    },
                    error: function (xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = "";

                        $.each(errors, function (key, value) {
                            errorMessage += value[0] + "\n";
                        });

                        Swal.fire("Error", errorMessage, "error");
                    }

                });
                location.reload(); // Refresh page
            });
        });

    </script>

@stop

