@extends('base::layouts.mt-main')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card card-flush">
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <div class="card-title">
                        <h1>Product Reviews</h1>
                    </div>
                    <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                        {{-- Check for permission to create (assuming 'edit' covers 'manage' including create, as no specific create perm is listed) --}}
                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_product_reviews'))
                            <a href="{{ route('products_review.create') }}" class="btn btn-primary">Create New Review</a>
                        @endif
                    </div>
                </div>

                <div class="card-body pt-0">
                    <form action="{{ route('products_review.index') }}" method="GET">
                        <div class="row mb-5">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}" placeholder="Search by product or customer name">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status:</label>
                                <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                                    </option>
                                    <option value="not_approved" {{ request('status') == 'not_approved' ? 'selected' : '' }}>
                                        Not Approved</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <table id="kt_ecommerce_products_table" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Title</th>
                            <th>Rating</th>
                            <th>Status</th>
                            {{-- Conditionally show Actions header if user can edit OR delete --}}
                            @if(auth()->user()->is_super_admin || auth()->user()->can('edit_product_reviews') || auth()->user()->can('delete_product_reviews'))
                                <th class="text-end">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($reviews as $review)
                            <tr>
                                <td>{{ $review->product->name ?? null}}</td>
                                <td>{{ $review->customer->first_name ?? 'N/A' }}</td>
                                <td>{{ $review->title }}</td>
                                <td>
                                    {{-- Display Star Rating based on average_rating --}}
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $review->average_rating)
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-muted"></i>
                                        @endif
                                    @endfor
                                    ({{ $review->average_rating }})
                                </td>
                                <td>
                                        <span
                                            class="badge badge-light-{{ $review->status == 'approved' ? 'success' : ($review->status == 'not_approved' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($review->status) }}
                                        </span>
                                </td>
                                {{-- Conditionally show Actions cell if user can edit OR delete --}}
                                @if(auth()->user()->is_super_admin || auth()->user()->can('edit_product_reviews') || auth()->user()->can('delete_product_reviews'))
                                    <td class="text-end">
                                        {{-- Edit Button Permission Check --}}
                                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_product_reviews'))
                                            <a href="{{ route('products_review.edit', $review->id) }}"
                                               class="btn btn-sm btn-light btn-active-light-primary me-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        @endif

                                        {{-- Delete Button Permission Check --}}
                                        @if(auth()->user()->is_super_admin || auth()->user()->can('delete_product_reviews'))
                                            <form id="delete-form-{{ $review->id }}"
                                                  action="{{ route('products_review.destroy', $review->id) }}" method="POST"
                                                  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-light btn-active-light-danger delete-banner"
                                                        style="border:none; background:none;"
                                                        onclick="confirmDelete(event, '{{ $review->id }}')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Keep the SweetAlert script as it is --}}
@push('scripts') {{-- Or use @section('scripts') if your layout uses that --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(event, reviewId) {
        event.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // No need for the second Swal here, submit the form directly
                document.getElementById('delete-form-' + reviewId).submit();
                // You might want to show the success message *after* the form submission is successful,
                // which usually involves handling the response via AJAX or on the next page load
                // For simplicity, keeping it as is, but ideally handled differently.
                // Swal.fire(
                //     'Deleted!',
                //     'Your file has been deleted.',
                //     'success'
                // )
            }
        })
    }
</script>
@endpush
