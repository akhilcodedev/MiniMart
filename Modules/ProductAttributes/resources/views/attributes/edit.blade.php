@extends('base::layouts.mt-main')
@section('content')

    <div class="container">
        <div id="kt_content_container" class="container-xxl">
            <div class="card">
                <div class="card-body py-3">
                    <form action="{{ route('product.attributes.update', $attribute->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
                            <h1 class="text-center mb-4">{{ __('Edit Attribute') }}</h1>
                            <div class="d-flex gap-2">
                                <a href="{{ route('product.attributes.index') }}" class="btn btn-sm btn-light">
                                    <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Update') }}
                                </button>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="code" class="form-label">{{ __('Code') }}</label>
                            <input type="text" name="code" id="code" value="{{ $attribute->code }}" class="form-control form-control-solid" required>
                        </div>

                        <div class="mb-5">
                            <label for="label" class="form-label">{{ __('Label') }}</label>
                            <input type="text" name="label" id="label" value="{{ $attribute->label }}" class="form-control form-control-solid" required>
                        </div>

                        <div class="mb-5">
                            <label for="input_type" class="form-label">{{ __('Input Type') }}</label>
                            <select name="input_type" id="input_type" class="form-control form-control-solid" required>
                                @foreach($attributeTypes as $key => $type)
                                    <option value="{{ $key }}" {{ $attribute->input_type == $key ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea rows="6" cols="30" name="description" id="description" class="form-control form-control-solid">{{ $attribute->description }}</textarea>
                        </div>

                        <div class="mb-5">
                            <label for="is_active" class="form-label">{{ __('Status') }}</label>
                            <select name="is_active" id="is_active" class="form-control form-control-solid">
                                @foreach($attributeStatuses as $key => $status)
                                    <option value="{{ $key }}" {{ $attribute->is_active == $key ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-5">
                            <label for="is_required" class="form-label">{{ __('Is Required') }}</label>
                            <input type="hidden" name="is_required" value="0">
                            <input type="checkbox" name="is_required" id="is_required" value="1"
                                {{ old('is_required', $attribute->is_required) == 1 ? 'checked' : '' }}>
                        </div>

                        <div class="mb-5">
                            <label for="is_filterable" class="form-label">{{ __('Is used to filterable') }}</label>
                            <input type="hidden" name="is_filterable" value="0">
                            <input type="checkbox" name="is_filterable" id="is_filterable" value="1"
                                {{ old('is_filterable', $attribute->is_filterable) == 1 ? 'checked' : '' }}>
                        </div>

                        <div class="mb-5">
                            <label for="is_configurable" class="form-label">{{ __('Is used to configurable') }}</label>
                            <input type="hidden" name="is_configurable" value="0">
                            <input type="checkbox" name="is_configurable" id="is_configurable" value="1"
                                {{ old('is_configurable', $attribute->is_configurable) == 1 ? 'checked' : '' }}>
                        </div>

                        <!-- Attribute Options Section -->
                        <div id="dynamic-section">
                            <h3 class="mt-4">{{ __('Attribute Options') }}</h3>

                            <div id="dynamic-fields-container">
                                @foreach($options as $option)
                                    <div class="field-group mb-3">
                                        <div class="row">
                                            <!-- Hidden input for option_id -->
                                            <input type="hidden" name="option_id[]" value="{{ $option['id'] }}">

                                            <div class="col-md-3">
                                                <input type="text" name="english_value[]" value="{{ $option['english_value'] }}" class="form-control" placeholder="English">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="arabic_value[]" value="{{ $option['arabic_value'] }}" class="form-control" placeholder="Arabic">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Color Code</label>

                                                <input type="color" name="color_code[]" class="form-control color-picker" value="{{ $option['color_code'] }}">
                                            </div>




                                            <div class="col-md-2">
                                                <input type="file" name="image_url[]" class="form-control">
                                                @if($option['image_url'])
                                                    <img src="{{ asset('storage/' . $option['image_url']) }}" alt="Option Image" width="50">
                                                    <!-- Hidden input to preserve old image -->
                                                    <input type="hidden" name="old_image_url[]" value="{{ $option['image_url'] }}">
                                                @endif
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger remove-field">X</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" id="add-field" class="btn btn-success mt-3">Add More</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('custom-js-section')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("dynamic-fields-container");
            const addButton = document.getElementById("add-field");

            if (!container || !addButton) {
                console.error("Missing required elements.");
                return;
            }

            addButton.addEventListener("click", function () {
                const fieldHTML = `
                <div class="field-group mb-3">
                    <div class="row">
                        <input type="hidden" name="option_id[]" value="">

                        <div class="col-md-3">
                            <input type="text" name="english_value[]" class="form-control" placeholder="English">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="arabic_value[]" class="form-control" placeholder="Arabic">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Color Code</label>
                            <input type="color" name="color_code[]" class="form-control color-picker" value="">
                        </div>
                        <div class="col-md-2">
                            <input type="file" name="image_url[]" class="form-control">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-field">X</button>
                        </div>
                    </div>
                </div>`;

                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = fieldHTML;
                container.appendChild(tempDiv.firstElementChild);
            });

            container.addEventListener("click", function (event) {
                if (event.target.classList.contains("remove-field")) {
                    event.target.closest(".field-group").remove();
                }
            });
        });
    </script>

@endsection
