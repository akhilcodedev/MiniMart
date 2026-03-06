@extends('base::layouts.mt-main')
@section('content')
    <div class="container">
        <div class="card-title py-5">
            <h2>Add Product Option</h2>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('product.variant.options.update', $option->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="code">Code</label>
                                <input type="text" name="code" class="form-control" value="{{ old('code', $option->code) }}"  required>
                                @error('code')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $option->code) }}"  required>
                                @error('name')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="active">Active</label>
                                <select name="active" class="form-control" required>
                                    <option value="1" {{ old('active', $option->active) === '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{  old('active', $option->active) === '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('active')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mt-5 gap-5">
                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="{{ route('product.variant.options.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('custom-js-section')
@endsection
