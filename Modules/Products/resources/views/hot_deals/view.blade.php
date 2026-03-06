@extends('base::layouts.mt-main')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-8">
        <h1 class="fs-2 fw-bolder">Hot Deal Details</h1>
        <a href="{{ route('hot_deals.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
    </div>

    <div class="card card-flush mb-6">
        <div class="card-body">
            <p><strong>Discount:</strong> {{ $deal->discount }}%</p>
            <p><strong>Start Date:</strong> {{ $deal->start_date }}</p>
            <p><strong>End Date:</strong> {{ $deal->end_date }}</p>
        </div>
    </div>

    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">Associated Products</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($deal->products as $product)
                    <div class="col-md-4 mb-5">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                @if($product->images->isNotEmpty())
                                    <div class="product-images mb-3">
                                        @foreach($product->images as $image)
                                         <img src="{{ asset($image->image_url) }}" alt="{{ $product->name }}" class="rounded me-2 mb-2" style="max-width: 80px; object-fit: cover;">
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mb-3 text-muted">No image available</div>
                                @endif
                                <h5 class="mb-1">{{ $product->name }}</h5>
                                <p class="text-muted mb-0">Price: ₹{{ number_format($product->price, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
