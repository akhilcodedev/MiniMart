@extends('base::layouts.mt-main')
@section('content')
    <div class="container">
        <h1 class="mb-4">Edit Role for User: {{ $user->name }}</h1>


        <form action="{{ url('users/' . $user->id . '/edit-roles') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="roles">Select Role</label>
                <div class="row">
                    @foreach($roles as $role)
                        <div class="col-md-4 mb-6">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" name="role_name"
                                       value="{{ $role->name }}" id="role-{{ $role->id }}"
                                        {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Role</button>
        </form>
    </div>
@stop
