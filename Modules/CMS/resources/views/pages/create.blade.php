@extends('base::layouts.mt-main')

@section('content')
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <div class="card">
        <div class="card-body">
            <h1 class="text-center mb-4">{{ __('Add New CMS Page') }}</h1>
            <form class="row needs-validation" method="POST" action="{{ route('cms.pages.store') }}" novalidate>
                @csrf
                <div class="col-md-12 mb-7 d-flex justify-content-end gap-3">
                    <button class="btn btn-primary" type="submit">
                        {{ __('Submit') }}
                    </button>
                    <a href="{{ route('cms.pages') }}" class="btn btn-outline btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                </div>
                <div class="col-md-6 mb-7">
                    <label for="slug" class="form-label fw-semibold mb-2 required">{{ __('Slug') }}</label>
                    <input type="text" class="form-control form-control-solid" id="slug" name="slug" required value="{{ old('slug') }}">
                    <div class="invalid-feedback">{{ __('Please provide a valid slug.') }}</div>
                </div>

                <div class="col-md-6 mb-7">
                    <label for="title" class="form-label fw-semibold mb-2 required">{{ __('Title') }}</label>
                    <input type="text" class="form-control form-control-solid" id="title" name="title" required value="{{ old('title') }}">
                    <div class="invalid-feedback">{{ __('Please provide a title.') }}</div>
                </div>

                <div class="col-md-6 mb-7">
                    <label for="language" class="form-label fw-semibold mb-2 required">{{ __('Language') }}</label>
                    <select class="form-select form-select-solid" data-control="select2" id="language" name="language" required>
                        <option value="" disabled {{ old('language') ? '' : 'selected' }}>{{ __('Select Language..') }}</option>
                        @foreach($languages as $id => $name)
                            <option value="{{ $id }}" {{ old('language') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">{{ __('Please select a language.') }}</div>
                </div>
                

                <div class="col-md-6 mb-7">
                    <label for="meta-title" class="form-label fw-semibold mb-2">{{ __('Meta Title') }}</label>
                    <input type="text" class="form-control form-control-solid" id="meta-title" name="meta-title" value="{{ old('meta-title') }}">
                </div>
                <div class="col-md-6 mb-7">
                    <label for="meta-key" class="form-label fw-semibold mb-2">{{ __('Meta Key') }}</label>
                    <input type="text" class="form-control form-control-solid" id="meta-key" name="meta-key" value="{{ old('meta-key') }}">
                </div>
                <div class="col-md-6 mb-7">
                    <label for="meta-description" class="form-label fw-semibold mb-2">{{ __('Meta Description') }}</label>
                    <input type="text" class="form-control form-control-solid" id="meta-description"
                           name="meta-description" value="{{ old('meta-description') }}">
                </div>

                <div class="col-md-12 mb-7">
                    <label for="content" class="form-label fw-semibold mb-2">{{ __('Content') }}</label>
                    <textarea class="form-control" name="content" id="kt-ckeditor-1" rows="6" required>{{old('content')}}</textarea>
                    <div class="invalid-feedback">{{ __('Please provide content.') }}</div>
                </div>

                <div class="col-md-12 mb-7">
                    <div class="form-check">
                        <input type="hidden" name="publish" value="0">
                        <input class="form-check-input" type="checkbox" id="publish" name="publish" value="1" {{ old('publish') ? 'checked' : '' }}>
                        <label class="form-check-label" for="publish">{{ __('Publish Now') }}</label>
                    </div>
                </div>

            </form>
        </div>
    </div>
    <script>
        ClassicEditor
            .create(document.querySelector('#kt-ckeditor-1'))
            .then(editor => {
                console.log('CKEditor initialized:', editor);
            })
            .catch(error => {
                console.error('Error initializing CKEditor:', error);
            });
    </script>
@endsection