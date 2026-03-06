@extends('base::layouts.mt-main')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card">
                <div class="card-header border-0 pt-6 d-flex align-items-center pe-10">
                    <div class="card-title">
                        <h1 class="fw-bold mb-0">Add New Store</h1>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('stores.store') }}" method="POST" class="form">
                        @csrf
                        <div class="d-flex gap-2 mb-10">
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">
                                    <i class="fas fa-save"></i> Save Store
                                </span>
                            </button>
                            <a href="{{ route('stores.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Name</label>
                                <input type="text" name="name" class="form-control form-control-solid" placeholder="Store Name" required />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Code</label>
                                <input type="number" name="code" class="form-control form-control-solid" placeholder="Store Code" required />
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6 fv-row">
                                <label class="form-label">URL Key</label>
                                <input type="text" name="url_key" class="form-control form-control-solid" placeholder="Store URL Key" />
                            </div>

                            <div class="col-md-6 fv-row">
                                <label class="form-label">Website</label>
                                <input type="text" name="website" class="form-control form-control-solid" placeholder="Store Website" />
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6 fv-row">
                                <label class="form-label">Language</label>
                                <select name="language_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Language">
                                    <option></option>
                                    @foreach ($languages as $language)
                                        <option value="{{ $language->id }}">{{ $language->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Status</label>
                                <select name="status" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Select Status">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>

                        <!-- Add the is_default select dropdown -->
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row">
                                <label class="form-label">Is Default</label>
                                <select name="is_default" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Default Status">
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-js-section')
    <script>
        $(document).ready(function() {
            $('.form-select').select2({
                minimumResultsForSearch: Infinity
            });
        });
    </script>
@endsection