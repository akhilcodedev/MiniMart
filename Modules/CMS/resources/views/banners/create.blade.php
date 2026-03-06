@extends('base::layouts.mt-main')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
            <h1 class="text-center mb-4">{{ __('Add Hero Banner') }}</h1>
        </div>

        <div class="card-body py-3">
            <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data">
                <div class="d-flex gap-2">
                    <a href="{{ route('banners.index') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('Save') }}
                    </button>
                </div>
                @csrf
                <div class="mb-5">
                    <label for="title" class="form-label">{{ __('Title') }}</label>
                    <input type="text" name="title" id="title" class="form-control form-control-solid" required value="{{ old('title') }}" >
                </div>
                <div class="mb-5">
                    <label for="subtitle" class="form-label">{{ __('Subtitle') }}</label>
                    <input type="text" name="subtitle" id="subtitle" class="form-control form-control-solid" value="{{ old('subtitle') }}">
                </div>
                <div class="mb-5">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" id="description" class="form-control form-control-solid" rows="7">{{ old('description') }}</textarea>
                </div>
                <div class="mb-5">
                    <label for="banner_images" class="form-label">{{ __('Banner Images') }}</label>
                    <input type="file" name="banner_images" id="banner_images" class="form-control form-control-solid">
                
                    @if(old('banner_images_path'))
                        <div class="mt-2">
                            <p>Previously selected image:</p>
                            <img src="{{ asset(old('banner_images_path')) }}" alt="Banner Image" style="max-height: 150px;">
                        </div>
                    @endif
                </div>
                
                <div class="mb-5">
                    <label for="alt_tag" class="form-label">{{ __('Alt Tag') }}</label>
                    <input type="text" name="alt_tag" id="alt_tag" class="form-control form-control-solid" value="{{ old('alt_tag') }}">
                </div>
                <div class="mb-5">
                    <label for="position" class="form-label">{{ __('Position') }}</label>
                    <input type="number" name="position" id="position" class="form-control form-control-solid" value="{{old ('position', $hero_banner->position ?? 0) }}">
                </div>
                <div class="mb-5">
                    <label for="active" class="form-label">{{ __('Status') }}</label>
                    <div class="form-check form-switch d-flex align-items-center">
                        <input 
                            class="form-check-input me-3" type="checkbox" name="status" id="active" value="1" 
                            {{ old('status', $hero_banner->status ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">
                        </label>
                    </div>
                </div>
                
            </form>
        </div>
    </div>
@endsection

@section('custom-js-section')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const activeCheckbox = document.getElementById('active');
            const activeLabel = document.getElementById('active-label');
            activeLabel.textContent = '{{ __('Inactive') }}';

            activeCheckbox.addEventListener('change', function () {
                if (activeCheckbox.checked) {
                    activeCheckbox.nextElementSibling.value = 1;
                    activeLabel.textContent = '{{ __('Active') }}';
                } else {
                   activeCheckbox.nextElementSibling.value = 0;
                    activeLabel.textContent = '{{ __('Inactive') }}';
                }
            });
        });
    </script>
@endsection