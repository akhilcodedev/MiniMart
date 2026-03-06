@extends('base::layouts.mt-main')
@section('content')
<!-- <div class="container">
    <h1>Edit User</h1>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <form action="{{ route('user.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password (Leave blank to keep current)</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('user.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div> -->
<div class="modal fade" id="edit_user" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Edit </h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="edit_user_form" action="#" method="POST" class="form">
                    @csrf
                    @method('PUT')
                    <div class="fv-row mb-7">
                        <label for="edit_name" class="fs-6 fw-semibold form-label mb-2">
                            <span class="required">Name</span>
                        </label>
                        <input class="form-control form-control-solid" type="text"
                            placeholder="Enter your username" name="name" id="edit_name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="fv-row mb-7">
                        <label for="edit_email" class="fs-6 fw-semibold form-label mb-2">
                            <span class="required">Email</span>
                        </label>
                        <input class="form-control form-control-solid" type="email"
                            placeholder="Enter your email address" name="email" id="edit_email" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="fv-row mb-7">
                        <label for="edit_password" class="fs-6 fw-semibold form-label mb-2">
                            Password (Leave blank to keep current)
                        </label>
                        <input class="form-control form-control-solid" type="password"
                            placeholder="Enter new password" name="password" id="edit_password">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@stop
