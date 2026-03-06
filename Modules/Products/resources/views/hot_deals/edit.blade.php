@extends('base::layouts.mt-main')

@section('content')
<div class="container-xxl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Hot Deal</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('update', $deal->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-6">
                    <label class="col-lg-2 col-form-label required fw-bold fs-6" for="discount">Discount (%)</label>
                    <div class="col-lg-10">
                        <input type="number" name="discount" id="discount" min="1" max="100"
                            class="form-control form-control-lg form-control-solid"
                            value="{{ old('discount', $deal->discount) }}" required />
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-2 col-form-label required fw-bold fs-6" for="start_date">Start Date</label>
                    <div class="col-lg-4">
                        <input type="date" name="start_date" id="start_date"
                            class="form-control form-control-lg form-control-solid"
                            value="{{ old('start_date', $deal->start_date) }}" required />
                    </div>

                    <label class="col-lg-2 col-form-label required fw-bold fs-6" for="end_date">End Date</label>
                    <div class="col-lg-4">
                        <input type="date" name="end_date" id="end_date"
                            class="form-control form-control-lg form-control-solid"
                            value="{{ old('end_date', $deal->end_date) }}" required />
                    </div>
                </div>

                <div class="mb-10">
                    <label class="form-label fw-bold fs-6">Select Products</label>
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select products"
                            name="products[]" multiple>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                {{ in_array($product->id, old('products', $deal->products->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline fs-2"></i> Update Hot Deal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
