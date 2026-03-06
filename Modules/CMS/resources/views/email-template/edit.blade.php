@extends('base::layouts.mt-main')

@section('content')
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <div class="card mb-7">
        <div class="card-body">
            <form class="row needs-validation" method="POST" action="{{ route('email.templates.update', $emailTemplate->id) }}" novalidate>
                @csrf
                @method('PUT')
                <div class="col-md-4 mb-7">
                    <label for="slug" class="form-label fw-semibold mb-2 required">{{ __('Slug') }}</label>
                    <input type="text" class="form-control form-control-solid" id="slug" name="slug"
                           value="{{ $emailTemplate->slug }}" required>
                    <div class="invalid-feedback">{{ __('Please provide a valid slug.') }}</div>
                </div>

                <div class="col-md-4 mb-7">
                    <label for="tags" class="form-label fw-semibold mb-2 required">{{ __('Tags') }}</label>
                    <input type="text" class="form-control form-control-solid" id="tags" name="tags"
                           value="{{ $emailTemplate->tags }}" required>
                    <div class="invalid-feedback">{{ __('Please provide a Tag.') }}</div>
                </div>

                <div class="col-md-4 mb-7">
                    <label for="label" class="form-label fw-semibold mb-2">{{ __('Label') }}</label>
                    <input type="text" class="form-control form-control-solid" id="label" name="label"
                           value="{{ $emailTemplate->label }}">
                </div>

                <div class="col-md-4 mb-7">
                    <label for="subject" class="form-label fw-semibold mb-2">{{ __('Subject') }}</label>
                    <input type="text" class="form-control form-control-solid" id="subject" name="subject"
                           value="{{ $emailTemplate->subject }}">
                </div>

                <div class="col-md-8 mb-7">
                    <label for="content" class="form-label fw-semibold mb-2">{{ __('Content') }}</label>
                    <textarea class="form-control" name="content" id="content" rows="6" required>{{ $emailTemplate->content }}</textarea>
                    <div class="invalid-feedback">{{ __('Please provide content.') }}</div>
                </div>

                <div class="col-md-12 mb-7 d-flex justify-content-between">
                    <a href="{{ route('email.templates.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                    <button class="btn btn-primary" type="submit">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom-js-section')
    <script>
        ClassicEditor.create(document.querySelector('#content'));
    </script>
@stop
