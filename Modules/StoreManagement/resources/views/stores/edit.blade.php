@extends('base::layouts.mt-main')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fw-bold">Edit Store</h2>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('stores.update', $store) }}" method="POST" class="form">
                        @csrf
                        @method('GET')

                        <div class="row mb-5">
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Name</label>
                                <input type="text" name="name" class="form-control form-control-solid" value="{{ $store->name }}" placeholder="Store Name" required />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Code</label>
                                <input type="number" name="code" class="form-control form-control-solid" value="{{ $store->code }}" placeholder="Store Code" required />
                            </div>

                        </div>
                        <div class="row mb-5">

                            <div class="col-md-6 fv-row">
                                <label class="form-label">URL Key</label>
                                <input type="text" name="url_key" class="form-control form-control-solid" value="{{ $store->url_key }}" placeholder="Store URL Key" />
                            </div>

                            <div class="col-md-6 fv-row">
                                <label class="form-label">Website</label>
                                <input type="text" name="website" class="form-control form-control-solid" value="{{ $store->website }}" placeholder="Store Website" />
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6 fv-row">
                                <label class="form-label">Language</label>
                                <select name="language_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Language">
                                    <option></option>
                                    @foreach ($languages as $language)
                                        <option value="{{ $language->id }}" {{ $store->language_id == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Status</label>
                                <select name="status" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Select Status">
                                    <option value="1" {{ $store->status ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$store->status ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6 fv-row">
                                <label class="form-label">Is Default</label>
                                <select name="is_default" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Default">
                                    <option value="1" {{ old('is_default', $store->is_default) == 1 ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('is_default', $store->is_default) == 0 ? 'selected' : '' }}>No</option>
                                </select>

                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">
                                    <i class="fas fa-save"></i> Update Store
                                </span>
                            </button>
                            <a href="{{ route('stores.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
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
