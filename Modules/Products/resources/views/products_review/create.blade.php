@extends('base::layouts.mt-main')

@section('content')

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card card-flush">
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <div class="card-title">
                        <h1>Create Review</h1>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('products_review.store') }}" method="POST">
                        @csrf

                        <div class="mb-10">
                            <label for="product_id" class="required form-label">Product</label>
                            <select name="product_id" id="product_id" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Select an option" data-allow-clear="true" required>
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
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
                            <select name="customer_id" id="customer_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Select a Customer" data-allow-clear="true" required>
                                <option></option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->first_name }},{{ $customer->email }}) </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-10">
                            <label for="title" class="required form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control form-control-solid"
                                value="{{ old('title') }}" required>
                            @error('title')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card card-flush mb-5">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Ratings</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                                        <tbody class="text-gray-600 fw-semibold">
                                            @foreach($attributes as $attribute)
                                                <tr class="rating-container" data-attribute-id="{{ $attribute->id }}">
                                                    <td>{{ $attribute->label }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="rating-stars">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <span class="rating-star cursor-pointer" data-rating="{{ $i }}">
                                                                        <i class="ki-duotone ki-star fs-2x text-gray-300"
                                                                            id="star_{{ $attribute->id }}_{{ $i }}"></i>
                                                                    </span>
                                                                @endfor
                                                            </div>
                                                            <input type="hidden" name="attribute_ratings[{{ $attribute->id }}]"
                                                                id="attribute_{{ $attribute->id }}" value="0">
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <label for="description" class="form-label">Review</label>
                            <textarea name="description" id="description" class="form-control form-control-solid"
                                rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-10">
                            <label for="star_rating" class="form-label">Star Rating</label>
                            <select name="star_rating" id="star_rating" class="form-select form-select-solid">
                                <option value="">Select Rating</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('star_rating') == $i ? 'selected' : '' }}>
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
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="not_approved" {{ old('status') == 'not_approved' ? 'selected' : '' }}>Not
                                    Approved</option>
                            </select>
                            @error('status')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-left-end">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary me-3">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                Create Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {

        $('[data-control="select2"]').select2();

        $('.rating-star').on('click', function () {
            const container = $(this).closest('.rating-container');
            const rating = parseInt($(this).data('rating'));
            const attributeId = container.data('attribute-id');

            container.find('.rating-star').each(function (index) {
                const starIndex = index + 1;
                const starIcon = $(this).find('i');

                if (starIndex <= rating) {
                    starIcon.removeClass('text-gray-300').addClass('text-warning');
                } else {
                    starIcon.removeClass('text-warning').addClass('text-gray-300');
                }
            });


            container.find('input[type="hidden"]').val(rating);
        });

    });
</script>
<style>
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
</style>