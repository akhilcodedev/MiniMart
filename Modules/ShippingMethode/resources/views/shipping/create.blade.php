@extends('base::layouts.mt-main')

@section('content')
    <div class="card">
        <div class="card-body py-3">
            <form action="{{ route('shipping.store') }}" method="POST" enctype="multipart/form-data" id="shippingForm">
                <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
                    <h1 class="text-center mb-4">{{ __('Create Shipping Method') }}</h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('shipping.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <i class="fas fa-save"></i> {{ __('Save') }}
                        </button>
                    </div>
                </div>
                @csrf
                <div class="mb-5">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input type="text" name="name" id="name" class="form-control form-control-solid" required value="{{ old('name') }}">
                </div>
                <div class="mb-5">
                    <label for="code" class="form-label">{{ __('Code') }}</label>
                    <input type="text" name="code" id="code" class="form-control form-control-solid" required value="{{ old('code') }}">
                </div>
                <div class="mb-5">
                    <label for="status" class="form-label">{{ __('Status') }}</label>
                    <select name="status" id="status" class="form-control form-control-solid">
                        <option value="1" {{old('status',1)==1 ? 'selected' : ''}}>{{ __('Active') }}</option>
                        <option value="0" {{old('status',1)==0 ? 'selected' : ''}}>{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>{{ __('Attributes') }}</h3>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-toggle="modal"
                                data-bs-target="#addAttributeModal">
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


                <div class="modal fade" id="addAttributeModal" tabindex="-1" aria-labelledby="addAttributeModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addAttributeModalLabel">{{ __('Add New Attribute') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 mb-5">
                                        <label for="attributeName" class="form-label">{{ __('Attribute Name') }}</label>
                                        <input type="text" id="attributeName" class="form-control form-control-solid"
                                               required value="{{old('attributeName')}}">
                                    </div>
                                   
                                    <div class="col-md-12 mb-5">
                                        <label for="attributeValue" class="form-label">{{ __('Value') }}</label>
                                        <input type="text" id="attributeValue" class="form-control form-control-solid" value="{{old('attributeValue')}}">
                                    </div>
                                    <div class="col-md-12 mb-5">
                                        <label for="attributeSortOrder"
                                               class="form-label">{{ __('Sort Order') }}</label>
                                        <input type="number" id="attributeSortOrder" class="form-control form-control-solid"
                                               value="1" value="{{old('attributeSortOrder')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="button" class="btn btn-primary"
                                        id="saveAttribute">{{ __('Add Attribute') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let attributeIndex = 0;
        const attributesContainer = document.getElementById('attributes-container');
        const attributesTableBody = attributesContainer.querySelector('tbody');
        const shippingForm = document.getElementById('shippingForm');
        const submitButton = document.getElementById('submitButton');

        function createAttributeElement(attribute, index) {
            const row = document.createElement('tr');
            row.innerHTML = `
                     <td>
                         <input type="text" name="attributes[${index}][name]" value="${attribute.name}" class="form-control form-control-solid" required>
                      </td>
                       <td>
                            <input type="text" name="attributes[${index}][value]" value="${attribute.value || ''}" class="form-control form-control-solid">
                        </td>
                        <td>
                            <input type="number" name="attributes[${index}][sort_order]" value="${attribute.sort_order || 1}" class="form-control form-control-solid">
                        </td>
                         <td>
                                <button type="button" class="btn btn-danger btn-sm remove-attribute">
                                    <i class="fas fa-trash"></i> {{ __('Remove') }}
                                </button>
                            </td>

                    `;

            return row;
        }

        function addAttribute(attribute) {
            const attributeElement = createAttributeElement(attribute, attributeIndex);
            attributesTableBody.appendChild(attributeElement);
            attributeIndex++;
            attachRemoveHandlers();
        }

        document.getElementById('saveAttribute').addEventListener('click', function () {
            const attributeName = document.getElementById('attributeName').value;
            const attributeValue = document.getElementById('attributeValue').value;
            const attributeSortOrder = document.getElementById('attributeSortOrder').value;

             const newAttribute = {
                name: attributeName,
                 value: attributeValue,
                sort_order: attributeSortOrder
            };


            addAttribute(newAttribute);


            document.getElementById('attributeName').value = '';
            document.getElementById('attributeValue').value = '';
            document.getElementById('attributeSortOrder').value = '1';

            $('#addAttributeModal').modal('hide');

        });
        
       submitButton.addEventListener('click', function(event) {
          event.preventDefault();
          shippingForm.submit();
        });

        function attachRemoveHandlers() {
    const removeButtons = attributesTableBody.querySelectorAll('.remove-attribute');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); 
            const row = this.closest('tr'); 
           Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                     row.remove(); 
                }
            });
         });
    });
}

        attachRemoveHandlers();
    </script>
@endsection