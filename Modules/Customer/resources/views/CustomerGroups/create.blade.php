@extends('base::layouts.mt-main')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6 d-flex justify-content-center">
                    <div class="card-title">
                        <h1 class="fw-bold mb-0">Add Customer Group</h1>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body">
                    <form action="{{ route('customer.groups.store') }}" method="POST" class="form">
                        @csrf
                        <div class="row">
                            <div class="text-end">
                                <a href="{{ route('customer.groups.index') }}" class="btn btn-light me-3">Back</a>
                                <button type="submit" class="btn btn-primary">
                                    <span class="indicator-label">Save</span>
                                </button>
                            </div>
                            <!-- Name Input -->
                            <div class="col-md-6 mb-5">
                                <label for="name" class="form-label fw-bold fs-6">Name</label>
                                <input
                                    type="text"
                                    class="form-control form-control-solid"
                                    id="name"
                                    name="name"
                                    placeholder="Enter group name"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Description Input -->
                        <div class="mb-5">
                            <label for="description" class="form-label fw-bold fs-6">Description</label>
                            <textarea
                                class="form-control form-control-solid"
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Enter group description"
                            ></textarea>
                        </div>

                        <!-- Buttons: Save and Back -->
                        
                    </form>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
@endsection
