@extends('base::layouts.mt-main')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">Edit Tax Class</div>
            <div class="card-body">
                <form action="{{ route('tax.update', $tax) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $tax->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code" value="{{ $tax->code }}" required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary mr-2">Update</button>
                        <a href="{{ route('tax.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
