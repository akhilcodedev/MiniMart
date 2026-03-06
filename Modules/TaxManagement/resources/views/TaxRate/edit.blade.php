@extends('base::layouts.mt-main')

@section('content')
    <div class="container-xxl">
        <div class="card">
            <div class="card-header">
                <h3>Edit Tax Rate</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('tax-rates.update', $taxRate) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label>Tax Class</label>
                        <select name="tax_class_id" class="form-control" required>
                            <option value="">Select Tax Class</option>
                            @foreach($taxClasses as $taxClass)
                                <option value="{{ $taxClass->id }}" {{ $taxRate->tax_class_id == $taxClass->id ? 'selected' : '' }}>
                                    {{ $taxClass->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Country</label>
                        <input type="text" name="country" value="{{ $taxRate->country }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>State</label>
                        <input type="text" name="state" value="{{ $taxRate->state }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Rate (%)</label>
                        <input type="number" step="0.01" name="rate" value="{{ $taxRate->rate }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="exclusive" {{ $taxRate->type == 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                            <option value="inclusive" {{ $taxRate->type == 'inclusive' ? 'selected' : '' }}>Inclusive</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary mr-2">Save Changes</button>
                        <a href="{{ route('tax-rates.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
