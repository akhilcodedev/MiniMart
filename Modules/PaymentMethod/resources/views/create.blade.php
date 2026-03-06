@extends('base::layouts.mt-main')
@section('content')
    <div class="card">
        <div class="card-body py-3">
            <form action="{{ route('payment.store') }}" method="POST" enctype="multipart/form-data">
                <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
                    <h1 class="text-center mb-4">{{ __('Create Payment Method') }}</h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('payment.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <button type="submit" id="submit_b" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('Save') }}
                        </button>
                    </div>
                </div>
                @csrf
                <div class="mb-5">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input type="text" name="name" id="name" class="form-control form-control-solid" value="{{ old('name') }}" required>
                </div>
                <div class="mb-5">
                    <label for="code" class="form-label">{{ __('Code') }}</label>
                    <input type="text" name="code" id="code" class="form-control form-control-solid" value="{{ old('code') }}" required>
                </div>
                <div class="mb-5">
                    <label for="sort_order" class="form-label">{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control form-control-solid" value="1" value="{{ old('sort_order') }}">
                </div>
                <div class="mb-5">
                    <label for="test_mode" class="form-label">{{ __('Test Mode') }}</label>
                    <select name="test_mode" id="test_mode" class="form-control form-control-solid">
                        <option value="1" {{ old('test_mode', 1) == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        <option value="0" {{ old('test_mode', 1) == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                    </select>                    
                </div>
                <div class="mb-5">
                    <label for="status" class="form-label">{{ __('Status') }}</label>
                    <select name="status" id="status" class="form-control form-control-solid">
                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }} >{{ __('Active') }}</option>
                        <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }} >{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>{{ __('Attributes') }}</h3>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addAttributeModal">
                            <i class="fas fa-plus"></i> {{ __('Add Attribute') }}
                        </button>
                    </div>
                </div>
                <div id="attributes-container">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Value') }}</th>
                            <th>{{ __('Sort Order') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal fade" id="addAttributeModal" tabindex="-1" aria-labelledby="addAttributeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addAttributeModalLabel">{{ __('Add New Attribute') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 mb-5">
                                        <label for="attributeName" class="form-label">{{ __('Attribute Name') }}</label>
                                        <input type="text" id="attributeName" name="attribute_name" class="form-control form-control-solid" required>
                                    </div>
                                    <div class="col-md-12 mb-5">
                                        <label for="attributeValue" class="form-label">{{ __('Value') }}</label>
                                        <input type="text" id="attributeValue" name="attribute_value" class="form-control form-control-solid">
                                    </div>
                                    <div class="col-md-12 mb-5">
                                        <label for="attributeSortOrder" class="form-label">{{ __('Sort Order') }}</label>
                                        <input type="number" id="attributeSortOrder" name="attribute_sort_order" class="form-control form-control-solid" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="button" class="btn btn-primary" id="saveAttribute">{{ __('Add Attribute') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('saveAttribute').addEventListener('click', function () {
            const name = document.getElementById('attributeName').value;
            const value = document.getElementById('attributeValue').value;
            const sortOrder = document.getElementById('attributeSortOrder').value;

            if (name && value) {
                const tbody = document.querySelector('#attributes-container tbody');
                const row = document.createElement('tr');

                row.innerHTML = `
                    <td><input type="text" name="attributes[name][]" value="${name}" class="form-control" readonly></td>
                    <td><input type="text" name="attributes[value][]" value="${value}" class="form-control"></td>
                    <td><input type="number" name="attributes[sort_order][]" value="${sortOrder}" class="form-control"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-attribute">{{ __('Remove') }}</button></td>
                `;
                tbody.appendChild(row);

                document.querySelectorAll('.remove-attribute').forEach(button => {
                    button.addEventListener('click', function () {
                        this.closest('tr').remove();
                    });
                });

                document.getElementById('addAttributeModal').querySelector('button.btn-close').click();
                document.getElementById('attributeName').value = '';
                document.getElementById('attributeValue').value = '';
                document.getElementById('attributeSortOrder').value = 1;
            }
        });
        document.getElementById('submit_b').addEventListener('click', function () {
            document.querySelectorAll('input[required]').forEach(input => {
                if (input.offsetParent === null) {
                    input.disabled = true;
                }
            });
        });

    </script>
@endsection
