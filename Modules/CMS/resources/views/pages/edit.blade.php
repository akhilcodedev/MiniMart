@extends('base::layouts.mt-main')

@section('content')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <div class="card">
        <div class="card-body">
            <form class="row needs-validation" method="POST" action="{{ route('cms.pages.update', $page->id) }}" novalidate>
                @csrf
                @method('PUT')

                <div class="col-md-6 mb-7">
                    <label for="slug" class="form-label fw-semibold mb-2 required">{{ __('Slug') }}</label>
                    <input type="text" class="form-control form-control-solid" id="slug" name="slug" value="{{ old('slug', $page->meta->slug ?? '') }}" required>
                                 <div class="invalid-feedback">{{ __('Please provide a valid slug.') }}</div>
                </div>

                <div class="col-md-6 mb-7">
                    <label for="title" class="form-label fw-semibold mb-2 required">{{ __('Title') }}</label>
                    <input type="text" class="form-control form-control-solid" id="title" name="title" value="{{ old('title', $page->title) }}" required>
                    <div class="invalid-feedback">{{ __('Please provide a title.') }}</div>
                </div>

                    <div class="col-md-6 mb-7">
                        <label for="language" class="form-label fw-semibold mb-2 required">{{ __('Language') }}</label>
                        <select class="form-select form-select-solid" data-control="select2" id="language" name="language" required>
                            <option value="" disabled>{{ __('Select Language..') }}</option>
                            @foreach($languages as $key => $value)
                                <option value="{{ $key }}" {{ old('language', $page->language) == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">{{ __('Please select a language.') }}</div>
                    </div>


                    <div class="col-md-6 mb-7">
                        <label for="meta-title" class="form-label fw-semibold mb-2">{{ __('Meta Title') }}</label>
                        <input type="text" class="form-control form-control-solid" id="meta-title" name="meta-title" 
                               value="{{ old('meta-title', $page->meta->meta_title ?? '') }}">
                    </div>
                    
                    <div class="col-md-6 mb-7">
                        <label for="meta-key" class="form-label fw-semibold mb-2">{{ __('Meta Key') }}</label>
                        <input type="text" class="form-control form-control-solid" id="meta-key" name="meta-key" 
                               value="{{ old('meta-key', $page->meta->meta_key ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-7">
                        <label for="meta-description" class="form-label fw-semibold mb-2">{{ __('Meta Description') }}</label>
                        <input type="text" class="form-control form-control-solid" id="meta-description" name="meta-description" 
                               value="{{ old('meta-description', $page->meta->meta_description ?? '') }}">
                    </div>

                <div class="col-md-12 mb-7">
                    <label for="content" class="form-label fw-semibold mb-2">{{ __('Content') }}</label>
                    <textarea class="form-control" id="kt-ckeditor-1" name="content" rows="6" required>{{ old('content', $page->content) }}</textarea>
                    <div class="invalid-feedback">{{ __('Please provide content.') }}</div>
                </div>

                <div class="col-md-12 mb-7">
                    <div class="form-check">
                        <input type="hidden" name="publish" value="0">
                        <input class="form-check-input" type="checkbox" id="publish" name="publish" value="1" {{ old('publish', $page->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label" for="publish">{{ __('Publish Now') }}</label>
                    </div>
                </div>

                <div class="col-md-12 mb-7">
                    <button class="btn btn-outline btn-primary min-w-300px" type="submit">{{ __('Update') }}</button>
                    <a href="{{ route('cms.pages') }}" class="btn btn-outline btn-secondary min-w-300px">{{ __('Cancel') }}</a>
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
