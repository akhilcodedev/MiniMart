@extends('base::layouts.mt-main')

@section('content')

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">Edit Customer Group</h3>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <form action="{{ route('customer.groups.update', $customerGroup->id) }}" method="POST" class="form">
                    @csrf
                    @method('PUT')

                    <!-- Name Field -->
                        <div class="mb-10">
                            <label class="form-label required">Name</label>
                            <input type="text" name="name" class="form-control form-control-solid" value="{{ old('name', $customerGroup->name) }}" placeholder="Enter Customer Group Name" required />
                            @error('name')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description Field -->
                        <div class="mb-10">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control form-control-solid" placeholder="Enter Description">{{ old('description', $customerGroup->description) }}</textarea>
                            @error('description')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Discount Rate Field -->
                    {{--                        <div class="mb-10">--}}
                    {{--                            <label class="form-label">Discount Rate</label>--}}
                    {{--                            <input type="number" name="discount_rate" class="form-control form-control-solid" value="{{ old('discount_rate', $customerGroup->discount_rate) }}" placeholder="Enter Discount Rate" step="0.01" />--}}
                    {{--                            @error('discount_rate')--}}
                    {{--                            <div class="text-danger mt-2">{{ $message }}</div>--}}
                    {{--                            @enderror--}}
                    {{--                        </div>--}}

                    <!-- Buttons -->
                        <div class="text-end">
                            <!-- Back Button -->
                            <a href="{{ route('customer.groups.index') }}" class="btn btn-light me-3">Back</a>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>

@endsection
