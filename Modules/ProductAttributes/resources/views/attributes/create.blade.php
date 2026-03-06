@extends('base::layouts.mt-main')
@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" rel="stylesheet"/>

    <div class="container">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <div class="card">
                <div class="card-body py-3">
                    <form action="{{ route('product.attributes.store') }}" method="POST" enctype="multipart/form-data">
                        <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
                            <h1 class="text-center mb-4">{{ __('Create Attribute') }}</h1>
                            <div class="d-flex gap-2">
                                <a href="{{ route('product.attributes.index') }}" class="btn btn-sm btn-light">
                                    <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Save') }}
                                </button>
                            </div>
                        </div>

                        @csrf

                        <div class="mb-5">
                            <label for="code" class="form-label">{{ __('Code') }}</label>
                            <input type="text" name="code" id="code" class="form-control form-control-solid" required>
                        </div>

                        <div class="mb-5">
                            <label for="label" class="form-label">{{ __('Label') }}</label>
                            <input type="text" name="label" id="label" class="form-control form-control-solid" required>
                        </div>

                        <div class="mb-5">
                            <label for="input_type" class="form-label">{{ __('Input Type') }}</label>
                            <select name="input_type" id="input_type" class="form-control form-control-solid" required>
                                @foreach($attributeTypes as $attributeTypeKey => $attributeTypeEl)
                                    <option value="{{ $attributeTypeKey }}">{{ $attributeTypeEl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea rows="6" cols="30" name="description" id="description"></textarea>
                        </div>

                        <div class="mb-5">
                            <label for="is_active" class="form-label">{{ __('Status') }}</label>
                            <select name="is_active" id="is_active" class="form-control form-control-solid">
                                @foreach($attributeStatuses as $attributeStatusKey => $attributeStatusEl)
                                    <option value="{{ $attributeStatusKey }}">{{ $attributeStatusEl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-5">
                            <label for="is_filterable" class="form-label">{{ __('Is Required') }}</label>
                            <input type="hidden" name="is_required" value="0">
                            <input type="checkbox" name="is_required" id="is_required" value="1">
                        </div>
                        <div class="mb-5">
                            <label for="is_filterable" class="form-label">{{ __('Is used to filterable') }}</label>
                            <input type="hidden" name="is_filterable" value="0">
                            <input type="checkbox" name="is_filterable" id="is_filterable" value="1">
                        </div>

                        <div class="mb-5">
                            <label for="is_configurable" class="form-label">{{ __('Is used to configurable') }}</label>
                            <input type="hidden" name="is_configurable" value="0">
                            <input type="checkbox" name="is_configurable" id="is_configurable" value="1">
                        </div>

                        <!-- Dynamic Section (Hidden by Default) -->
                        <div id="dynamic-section" style="display: none;">
                            <h3 class="mt-4">Additional Fields</h3>

                            <div id="dynamic-fields-container">
                                <div class="field-group mb-3">
                                    <div class="row">
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
                                </div>
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
            const dynamicSection = document.getElementById("dynamic-section");
            const inputTypeSelect = document.getElementById("input_type");

            if (!container || !addButton || !dynamicSection || !inputTypeSelect) {
                console.error("Some elements are missing in the DOM.");
                return;
            }

            // Show/hide dynamic section based on dropdown selection
            function toggleDynamicSection() {
                dynamicSection.style.display = inputTypeSelect.value === "select" ? "block" : "none";
            }
            inputTypeSelect.addEventListener("change", toggleDynamicSection);
            toggleDynamicSection(); // Run on page load

            // Function to attach event listeners to color inputs

            // Add new fields dynamically
            addButton.addEventListener("click", function () {
                const fieldHTML = `
                <div class="field-group mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="english_value[]" class="form-control" placeholder="English">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="arabic_value[]" class="form-control" placeholder="Arabic">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Color Code</label>
                            <input type="color" name="color_code[]" class="form-control color-picker" value="#000000">
                        </div>
                        <div class="col-md-2">
                            <input type="file" name="image_url[]" class="form-control">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-field">X</button>
                        </div>
                    </div>
                </div>
            `;

                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = fieldHTML;
                container.appendChild(tempDiv.firstElementChild);

                attachColorChangeListener(); // Attach event to new fields
            });

            // Remove a field group (event delegation)
            container.addEventListener("click", function (event) {
                if (event.target.classList.contains("remove-field")) {
                    event.target.closest(".field-group").remove();
                }
            });
        });
    </script>

@endsection
