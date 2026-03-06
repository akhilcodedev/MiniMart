@extends('base::layouts.mt-main')
@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" rel="stylesheet"/>

    <div class="container">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">

            <div class="card">

                <div class="card-body py-3">

                    <form action="{{ route('product.attribute.sets.store') }}" method="POST" enctype="multipart/form-data">

                        <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
                            <h1 class="text-center mb-4">{{ __('Create Attribute Set') }}</h1>
                            <div class="d-flex gap-2">
                                <a href="{{ route('product.attribute.sets.index') }}" class="btn btn-sm btn-light">
                                    <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Save') }}
                                </button>
                            </div>
                        </div>

                        @csrf

                        <div class="mb-5">
                            <label for="name" class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" class="form-control form-control-solid" required>
                        </div>

                        <div class="mb-5">
                            <label for="label" class="form-label">{{ __('Label') }}</label>
                            <input type="text" name="label" id="label" class="form-control form-control-solid" required>
                        </div>

                        <div class="mb-5">
                            <label for="set_type" class="form-label">{{ __('Type') }}</label>
                            <select name="set_type" id="set_type" class="form-control form-control-solid" required>
                                @foreach($attributeSetTypes as $attributeTypeKey => $attributeTypeEl)
                                    <option value="{{ $attributeTypeKey }}">{{ $attributeTypeEl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea rows="6" cols="30" name="description" id="description" ></textarea>
                        </div>

                        <div class="mb-5">
                            <label for="is_active" class="form-label">{{ __('Status') }}</label>
                            <select name="is_active" id="is_active" class="form-control form-control-solid">
                                @foreach($attributeSetStatuses as $attributeStatusKey => $attributeStatusEl)
                                    <option value="{{ $attributeStatusKey }}">{{ $attributeStatusEl }}</option>
                                @endforeach
                            </select>
                        </div>

                    </form>

                </div>
            </div>

        </div>
        <!--end::Container-->
    </div>
@endsection

@section('custom-js-section')

    <!--end::Vendors Javascript-->
    <script src="{{ asset('build-base/ktmt/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
    <!--begin::Custom Javascript(used for this page only)-->

    <script src="{{ asset('build-base/ktmt/js/custom/apps/ecommerce/catalog/save-product.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/apps/chat/chat.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/utilities/modals/create-app.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/utilities/modals/users-search.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>

    <script>

    </script>

@endsection
