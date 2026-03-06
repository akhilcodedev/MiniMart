@extends('base::layouts.mt-main')
@section('content')
    <div class="card">
        <div class="card-body py-3">
            <form action="{{ route('payment.update', $paymentMethod->id) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
                    <h1 class="text-center mb-4">{{ __('Edit Payment Method') }}</h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('payment.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <button type="submit" id="submit_b" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('Update') }}
                        </button>
                    </div>
                </div>
                @csrf
                <div class="mb-5">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input type="text" name="name" id="name" class="form-control form-control-solid" value="{{ $paymentMethod->name }}" required>
                </div>
                <div class="mb-5">
                    <label for="code" class="form-label">{{ __('Code') }}</label>
                    <input type="text" name="code" id="code" class="form-control form-control-solid" value="{{ $paymentMethod->code }}" required>
                </div>
                <div class="mb-5">
                    <label for="sort_order" class="form-label">{{ __('Sort Order') }}</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control form-control-solid" value="{{ $paymentMethod->sort_order }}" required>
                </div>
                <div class="mb-5">
                    <label for="test_mode" class="form-label">{{ __('Test Mode') }}</label>
                    <select name="test_mode" id="test_mode" class="form-control form-control-solid">
                        <option value="1" {{ $paymentMethod->test_mode == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        <option value="0" {{ $paymentMethod->test_mode == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                    </select>
                </div>
                <div class="mb-5">
                    <label for="status" class="form-label">{{ __('Status') }}</label>
                    <select name="status" id="status" class="form-control form-control-solid">
                        <option value="1" {{ $paymentMethod->is_active == 1 ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="0" {{ $paymentMethod->is_active == 0 ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>{{ __('Attributes') }}</h3>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-light" id="addAttributeButton">
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
                        @foreach($paymentMethod->attributes as $attribute)
                            <tr>
                                <td><input type="text" name="attributes[name][]" value="{{ $attribute->name }}" class="form-control" readonly></td>
                                <td><input type="text" name="attributes[value][]" value="{{ $attribute->value }}" class="form-control"></td>
                                <td><input type="number" name="attributes[sort_order][]" value="{{ $attribute->sort_order }}" class="form-control"></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm edit-attribute" data-name="{{ $attribute->name }}" data-value="{{ $attribute->value }}" data-sort_order="{{ $attribute->sort_order }}">{{ __('Edit') }}</button>
                                    <button type="button" class="btn btn-danger btn-sm remove-attribute">{{ __('Remove') }}</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Attribute Modal -->
    <div class="modal fade" id="editAttributeModal" tabindex="-1" aria-labelledby="editAttributeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAttributeModalLabel">{{ __('Edit Attribute') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editRowIndex">
                    <div class="mb-3">
                        <label>{{ __('Name') }}</label>
                        <input type="text" id="editAttributeName" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Value') }}</label>
                        <input type="text" id="editAttributeValue" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Sort Order') }}</label>
                        <input type="number" id="editAttributeSortOrder" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" id="saveEditAttribute" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Handle Edit button click for both existing and newly added rows
        document.querySelector('#attributes-container').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('edit-attribute')) {
                const row = e.target.closest('tr');
                const index = Array.from(row.parentNode.children).indexOf(row);

                document.getElementById('editRowIndex').value = index;
                document.getElementById('editAttributeName').value = row.querySelector('input[name="attributes[name][]"]').value;
                document.getElementById('editAttributeValue').value = row.querySelector('input[name="attributes[value][]"]').value;
                document.getElementById('editAttributeSortOrder').value = row.querySelector('input[name="attributes[sort_order][]"]').value;

                new bootstrap.Modal(document.getElementById('editAttributeModal')).show();
            }
        });

        // Save edited attribute
        document.getElementById('saveEditAttribute').addEventListener('click', function() {
            const index = document.getElementById('editRowIndex').value;
            const rows = document.querySelectorAll('#attributes-container tbody tr');

            rows[index].querySelector('input[name="attributes[name][]"]').value = document.getElementById('editAttributeName').value;
            rows[index].querySelector('input[name="attributes[value][]"]').value = document.getElementById('editAttributeValue').value;
            rows[index].querySelector('input[name="attributes[sort_order][]"]').value = document.getElementById('editAttributeSortOrder').value;

            bootstrap.Modal.getInstance(document.getElementById('editAttributeModal')).hide();
        });

        // Add new attribute row
        document.getElementById('addAttributeButton').addEventListener('click', function () {
            const tableBody = document.querySelector('#attributes-container tbody');
            const row = document.createElement('tr');

            row.innerHTML = `
        <td><input type="text" name="attributes[name][]" class="form-control" required></td>
        <td><input type="text" name="attributes[value][]" class="form-control" required></td>
        <td><input type="number" name="attributes[sort_order][]" class="form-control" required></td>
        <td>
            <button type="button" class="btn btn-warning btn-sm edit-attribute">{{ __('Edit') }}</button>
            <button type="button" class="btn btn-danger btn-sm remove-attribute">{{ __('Remove') }}</button>
        </td>
    `;
            tableBody.appendChild(row);
        });

        // Remove an attribute row
        document.querySelector('#attributes-container').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-attribute')) {
                e.target.closest('tr').remove();
            }
        });




    </script>
@endsection
