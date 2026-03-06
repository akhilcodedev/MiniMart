@extends('base::layouts.mt-main')

@section('content')
    <div class="card col-md-6">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Edit Role</span>
            </h3>
        </div>
        <div class="card-body py-3">
            <form action="{{ route('roles.update', $role->id) }}" method="POST" class="form row">
                @csrf
                @method('PUT')

                <div class="col-12 mb-7">
                    <label class="form-label fw-semibold required mb-2" for="name">Role Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-solid"
                           value="{{ $role->label }}" required>


                </div>



                <div class="col-12 mb-7 d-flex justify-content-start">
                    <!-- Update Role Button -->
                    <button type="submit" class="btn btn-success me-3">Update Role</button>
                    <!-- Back Button -->
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateAdminToggleText(checkbox) {
            const text = checkbox.checked ? 'Yes' : 'No';
            document.getElementById('admin-toggle-text').innerText = text;
        }
    </script>
@stop
