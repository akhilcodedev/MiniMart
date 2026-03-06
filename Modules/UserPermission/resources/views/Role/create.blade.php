@extends('base::layouts.mt-main')
@section('content')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card col-md-6">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Create Role</span>
            </h3>
        </div>
        <div class="card-body py-3">
            <form action="{{ route('roles.store') }}" method="POST" class="form row">
                @csrf
                <div class="col-12 mb-7">
                    <label class="form-label fw-semibold required mb-2" for="name">Role Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-solid" required>
                    @if($errors->has('name'))
                        <div class="text-danger">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                </div>



                <div class="col-12 mb-7 d-flex justify-content-end">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary min-w-150px">Back</a>
                    <button type="submit" class="btn btn-primary ms-3 min-w-150px">Create Role</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateAdminToggleText(checkbox) {
            const toggleText = document.getElementById('admin-toggle-text');
            toggleText.textContent = checkbox.checked ? 'Yes' : 'No';
        }
    </script>
@stop
