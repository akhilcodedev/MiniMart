@extends('base::layouts.mt-main')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card card-flush">
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <div class="card-title">
                        <h1>Edit Review</h1>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('products_review.update', $review) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-10">
                            <label for="product_id" class="required form-label">Product</label>
                            <select name="product_id" id="product_id" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Select an option" data-allow-clear="true" required>
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ (old('product_id') ?? $review->product_id) == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-10">
                            <label for="customer_id" class="required form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Select a Customer" data-allow-clear="true"
                                required>
                                <option></option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ (old('customer_id') ?? $review->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->first_name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                       
                        <div class="mb-10">
                            <label for="title" class="required form-label">Title</label>
                            <input name="title" id="title" class="form-control form-control-solid"
                                value="{{ old('title') ?? $review->title }}" required />
                            @error('title')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card card-flush mb-5">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Ratings</h2>
                                </div>
                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <!--begin::Table wrapper-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                                        <!--begin::Table head-->
                                        <thead>
                                            {{-- You can uncomment the header row if needed --}}
                                        </thead>
                                        <!--end::Table head-->

                                        <!--begin::Table body-->
                                        <tbody class="text-gray-600 fw-semibold">
                                            @foreach($attributes as $attribute)
                                                <tr class="rating-container" data-attribute-id="{{ $attribute->id }}">
                                                    <td>{{ $attribute->label }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="rating-stars">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <span class="rating-star cursor-pointer" data-rating="{{ $i }}">
                                                                        <i class="ki-duotone ki-star fs-2x"
                                                                            id="star_{{ $attribute->id }}_{{ $i }}"></i>
                                                                    </span>
                                                                @endfor
                                                            </div>
                                                            <input type="hidden" name="attribute_ratings[{{ $attribute->id }}]"
                                                                id="attribute_{{ $attribute->id }}"
                                                                value="{{ old('attribute_ratings.' . $attribute->id, $existingRatings[$attribute->id] ?? 0) }}">
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <!--end::Table body-->
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table wrapper-->
                            </div>
                            <!--end::Card body-->
                        </div>

                        <div class="mb-10">
                            <label for="description" class="form-label">Review</label>
                            <textarea name="description" id="description" class="form-control form-control-solid"
                                rows="4">{{ old('description') ?? $review->description }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-10">
                            <label for="star_rating" class="form-label">Star Rating</label>
                            <select name="star_rating" id="star_rating" class="form-select form-select-solid">
                                <option value="">Select Rating</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ (old('star_rating') ?? $review->star_rating) == $i ? 'selected' : '' }}>
                                        {{ $i }} Stars
                                    </option>
                                @endfor
                            </select>
                            @error('star_rating')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-10">
                            <label for="status" class="required form-label">Status</label>
                            <select name="status" id="status" class="form-select form-select-solid" required>
                                <option value="pending" {{ (old('status') ?? $review->status) == 'pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="approved" {{ (old('status') ?? $review->status) == 'approved' ? 'selected' : '' }}>
                                    Approved</option>
                                <option value="not_approved" {{ (old('status') ?? $review->status) == 'not_approved' ? 'selected' : '' }}>
                                    Not Approved</option>
                            </select>
                            @error('status')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-left-end">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary me-3">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('[data-control="select2"]').select2();

        function updateStars(container, rating) {
            container.find('.rating-star').each(function (index) {
                const starIndex = index + 1;
                const starIcon = $(this).find('i');
                if (starIndex <= rating) {
                    starIcon.removeClass('text-gray-300').addClass('text-warning');
                } else {
                    starIcon.removeClass('text-warning').addClass('text-gray-300');
                }
            });
        }

        $('.rating-star').on('click', function () {
            const container = $(this).closest('.rating-container');
            const rating = parseInt($(this).data('rating'));
            const attributeId = container.data('attribute-id');

            updateStars(container, rating);
            container.find('input[type="hidden"]').val(rating);
        });

        @foreach($attributes as $attribute)
            @if(old('attribute_ratings.' . $attribute->id, isset($existingRatings[$attribute->id]) ? $existingRatings[$attribute->id] : null))
                const initialRating{{ $attribute->id }} = {{ old('attribute_ratings.' . $attribute->id, isset($existingRatings[$attribute->id]) ? $existingRatings[$attribute->id] : 0) }};
                const container{{ $attribute->id }} = $('[data-attribute-id="{{ $attribute->id }}"]');
                updateStars(container{{ $attribute->id }}, initialRating{{ $attribute->id }});
            @endif
        @endforeach
    });
</script>

{{-- Styles --}}
<style>
    /* Existing rating styles */
    .rating-stars {
        display: flex;
        gap: 0.5rem;
    }
    .rating-star {
        transition: color 0.2s ease-in-out;
    }
    .rating-star:hover {
        transform: scale(1.1);
    }
    .text-warning {
        color: #ffc700 !important;
    }
    .text-gray-300 {
        color: #E4E6EF !important;
    }

    /* Additional styles for a polished look */
    .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        background-color: #ffffff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    .card-title h1, .card-title h2 {
        font-family: 'Roboto', sans-serif;
        color: #333;
    }
    .form-label {
        font-weight: 600;
        color: #5a5c69;
    }
    .form-control, .form-select {
        border: 1px solid #d1d3e2;
        border-radius: 0.375rem;
        box-shadow: none;
    }
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
    }
    .btn-secondary {
        background-color: #858796;
        border-color: #858796;
    }
    .btn-secondary:hover {
        background-color: #6e707e;
        border-color: #5a5c69;
    }
</style>
