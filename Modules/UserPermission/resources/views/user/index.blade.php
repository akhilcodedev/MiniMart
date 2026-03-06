@extends('base::layouts.mt-main')

@section('content')

    @php
        $authUser = auth()->user();
        $isSuperAdmin = $authUser && $authUser->is_super_admin == 1;
    @endphp


    <div id="message-container" style="display: none;" class="alert alert-success">
        <span id="message"></span>
    </div>
    <div class="card">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">{{ __(' Manage Users') }}</span>
            </h3>

            @if($isSuperAdmin || $authUser->can('create_user'))
                <div class="card-toolbar">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#add_user"
                       class="btn btn-sm btn-primary"> {{-- More prominent Add button style --}}
                        <i class="ki-outline ki-plus fs-2"></i>{{ __(' Add User') }}
                    </a>
                </div>
            @endif

        </div>
        <div class="card-body py-3">
            <div class="table-responsive">
                <table id="userTable" class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3">
                    <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-250px">{{ __(' Name') }}</th>
                        <th class="min-w-150px">{{ __(' Email') }}</th>
                        <th class="min-w-150px">{{ __(' User Type') }}</th>
                        <th class="min-w-100px text-end">{{ __(' Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->is_vendor_user ? 'Vendor' : 'Normal User' }}</td>
                            <td class="text-end">

                                @if($isSuperAdmin || $authUser->can('update_user'))
                                    <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 editUserBtn"
                                            title="Edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#edit_user"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-user-email="{{ $user->email }}">
                                        <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </button>
                                @endif

                                @if( ($isSuperAdmin || $authUser->can('delete_user')) && $authUser->id != $user->id )
                                    <form action="{{ route('user.destroy', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm deleteUserBtn"
                                                title="Delete"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}">
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

    <div class="modal fade" id="add_user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">{{ __(' Add User') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                         aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="addUserForm" action="{{ route('user.store') }}" method="POST"
                          class="form fv-plugins-bootstrap5 fv-plugins-framework">
                        @csrf
                        <div class="fv-row mb-7 fv-plugins-icon-container">
                            <label for="name" class="fs-6 fw-semibold form-label mb-3">
                                <span class="required">{{ __(' Name') }}</span>
                            </label>
                            <input class="form-control form-control-solid" type="text" placeholder="Enter user name"
                                   name="name" id="name" required>
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                            </div>
                        </div>

                        <div class="fv-row mb-7 fv-plugins-icon-container">
                            <label for="email" class="fs-6 fw-semibold form-label mb-3">
                                <span class="required">{{ __(' Email') }}</span>
                            </label>
                            <input class="form-control form-control-solid" type="email"
                                   placeholder="Enter email address" name="email" id="email" required>
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                            </div>
                        </div>

                        <div class="fv-row mb-7 fv-plugins-icon-container">
                            <label for="password" class="fs-6 fw-semibold form-label mb-3">
                                <span class="required">{{ __(' Password') }}</span>
                            </label>
                            <input class="form-control form-control-solid" type="password" placeholder="Enter password"
                                   name="password" id="password" required>
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">{{ __(' Save') }}</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="edit_user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">{{ __('Edit User') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="editUserForm" method="POST">
                        @csrf
                        <input type="hidden" id="editUserId" name="user_id">

                        <div class="fv-row mb-7">
                            <label for="editName" class="fs-6 fw-semibold form-label mb-3">
                                <span class="required">{{ __('Name') }}</span>
                            </label>
                            <input class="form-control form-control-solid"
                                   type="text"
                                   name="name"
                                   id="editName"
                                   required>
                        </div>

                        <div class="fv-row mb-7">
                            <label for="editEmail" class="fs-6 fw-semibold form-label mb-3">
                                <span class="required">{{ __('Email') }}</span>
                            </label>
                            <input class="form-control form-control-solid"
                                   type="email"
                                   name="email"
                                   id="editEmail"
                                   required>
                        </div>

                        <div class="fv-row mb-7">
                            <label for="editPassword" class="fs-6 fw-semibold form-label mb-3">
                                <span>{{ __('Password') }}</span> <span class="text-muted">({{ __('Leave blank to keep current') }})</span>
                            </label>
                            <input class="form-control form-control-solid"
                                   type="password"
                                   placeholder="New password (optional)"
                                   name="password"
                                   id="editPassword">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">{{ __('Update') }}</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop

@section('custom-js-section')
    <script src="{{ asset('assets/js/cdn/sweetalert2@11.js') }}"></script>
    {{-- jQuery should be loaded globally by the layout --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            $(document).ready(function () {

                $("#userTable").on("click", ".editUserBtn", function () {
                    let userId = $(this).data("user-id");
                    let userName = $(this).data("user-name");
                    let userEmail = $(this).data("user-email");

                    $("#editName").val(userName);
                    $("#editEmail").val(userEmail);
                    $("#editUserId").val(userId);
                    $("#editPassword").val('');

                    $("#editUserForm").attr("action", `{{ url('users') }}/${userId}/update`);
                });


                $("#editUserForm, #addUserForm").submit(function (e) {
                    e.preventDefault();

                    let form = $(this);
                    let formData = form.serialize();
                    let actionUrl = form.attr("action");
                    let isEdit = form.attr('id') === 'editUserForm';
                    let httpMethod = isEdit ? 'POST' : 'POST'; // Both POST in HTML, handle PUT via data
                    let methodSpoof = isEdit ? '&_method=PUT' : ''; // Spoof PUT for edit

                    let submitButton = form.find('button[type="submit"]');
                    let buttonText = submitButton.find('.indicator-label').text(); // Original button text

                    // Disable button and show loading
                    submitButton.prop('disabled', true).attr('data-kt-indicator', 'on');


                    $.ajax({
                        url: actionUrl,
                        type: httpMethod,
                        data: formData + methodSpoof,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            let modalId = isEdit ? '#edit_user' : '#add_user';
                            $(modalId).modal("hide");

                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });

                        },
                        error: function (xhr) {
                            let errorMessage = "An error occurred. Please check the form and try again.";
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({ title: "Error", html: errorMessage, icon: "error"}); // Use html property for <br>
                        },
                        complete: function() {
                            // Re-enable button and hide loading
                            submitButton.prop('disabled', false).removeAttr('data-kt-indicator');
                            form.trigger('reset'); // Reset form fields
                        }
                    });
                }); // End Add/Edit User Submit

                $("#userTable").on("click", ".deleteUserBtn", function (e) {
                    e.preventDefault();

                    let userId = $(this).data("user-id");
                    let userName = $(this).data("user-name");
                    let form = $(this).closest("form");

                    Swal.fire({
                        title: "Are you sure?",
                        text: `Delete user "${userName}"? This cannot be undone.`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel",
                        customClass: { // Match theme styling if possible
                            confirmButton: "btn btn-danger",
                            cancelButton: "btn btn-light"
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }); // End Delete User Click

            }); // End jQuery document ready
        }); // End DOMContentLoaded
    </script>
@stop
