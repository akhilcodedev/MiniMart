@extends('base::layouts.mt-main')

@section('content')
    <div class="container-xxl">
        <div class="card">
            <div class="card-header">
                <h3>Add Tax Rate</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('tax-rates.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Tax Class</label>
                        <select name="tax_class_id" class="form-control" required>
                            <option value="">Select Tax Class</option>
                            @foreach($taxClasses as $taxClass)
                                <option value="{{ $taxClass->id }}" {{ old('tax_class_id') == $taxClass->id ? 'selected' : '' }}>
                                    {{ $taxClass->name }}
                                </option>
                            @endforeach
                        </select>                        
                    </div>
                    <div class="mb-3">
                        <label>Country</label>
                        <input type="text" name="country" class="form-control" required value="{{ old('country') }}">
                    </div>
                    <div class="mb-3">
                        <label>State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                    </div>
                    <div class="mb-3">
                        <label>Rate (%)</label>
                        <input type="number" step="0.01" name="rate" class="form-control" required value="{{ old('rate') }}">
                    </div>
                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="exclusive">Exclusive</option>
                            <option value="inclusive">Inclusive</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary mr-2">Save</button>
                        <a href="{{ route('tax-rates.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
