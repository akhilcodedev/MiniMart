@extends('base::layouts.mt-main')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header"><h1>Create Tax Class</h1></div>
            <div class="card-body">
                <form action="{{ route('tax.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code"  value="{{ old('code') }}">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary mr-2">Create</button>
                        <a href="{{ route('tax.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
