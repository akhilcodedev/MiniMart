@extends('base::layouts.mt-main')

@section('content')
    <div class="card">
        <div class="card-body py-3">
            <form action="{{ route('shipping.update', $shippingMethod->id) }}" method="POST"
                  enctype="multipart/form-data" id="shippingForm">
                <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
                    <h1 class="text-center mb-4">{{ __('Edit Shipping Method') }}</h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('shipping.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <i class="fas fa-save"></i> {{ __('Update') }}
                        </button>
                    </div>
                </div>
                @csrf
                @method('PUT') 

                <div class="mb-5">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input type="text" name="name" id="name" class="form-control form-control-solid"
                           value="{{ $shippingMethod->name }}" required>
                </div>
                <div class="mb-5">
                    <label for="code" class="form-label">{{ __('Code') }}</label>
                    <input type="text" name="code" id="code" class="form-control form-control-solid"
                           value="{{ $shippingMethod->code }}" required>
                </div>
                <div class="mb-5">
                    <label for="status" class="form-label">{{ __('Status') }}</label>
                    <select name="status" id="status" class="form-control form-control-solid">
                        <option value="1" {{ $shippingMethod->status == 1 ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="0" {{ $shippingMethod->status == 0 ? 'selected' : '' }}>{{ __('Inactive') }}</option>
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
                        @if($shippingMethod->attributes)
                            @foreach($shippingMethod->attributes as $attribute)
                                <tr data-attribute-id="{{ $attribute->id }}">
                                    <td>
                                        <input type="text" name="attributes[{{ $loop->index }}][name]"
                                               value="{{ $attribute->name }}"
                                               class="form-control form-control-solid" required>
                                    </td>

                                    <td>
                                        <input type="text" name="attributes[{{ $loop->index }}][value]"
                                               value="{{ $attribute->value }}"
                                               class="form-control form-control-solid">
                                    </td>
                                    <td>
                                        <input type="number" name="attributes[{{ $loop->index }}][sort_order]"
                                               value="{{ $attribute->sort_order }}"
                                               class="form-control form-control-solid">
                                    </td>
                                    <td>
                                        <input type="hidden" name="attributes[{{ $loop->index }}][id]"
                                               value="{{ $attribute->id }}">
                                        <button type="button" class="btn btn-danger btn-sm remove-attribute">
                                            <i class="fas fa-trash"></i> {{ __('Remove') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>


                <!-- Add Attribute Modal -->
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
                                               required>
                                    </div>
                                     <div class="col-md-12 mb-5">
                                         <label for="attributeType" class="form-label">{{ __('Type') }}</label>
                                        <select id="attributeType" class="form-control form-control-solid">
                                            <option value="text">{{ __('Text') }}</option>
                                            <option value="dropdown">{{ __('Dropdown') }}</option>
                                            <option value="textarea">{{ __('Textarea') }}</option>
                                            <option value="checkbox">{{ __('Checkbox') }}</option>
                                            <option value="radio">{{ __('Radio') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-5">
                                        <label for="attributeValue" class="form-label">{{ __('Value') }}</label>
                                        <input type="text" id="attributeValue" class="form-control form-control-solid">
                                    </div>
                                    <div class="col-md-12 mb-5">
                                        <label for="attributeSortOrder"
                                               class="form-label">{{ __('Sort Order') }}</label>
                                        <input type="number" id="attributeSortOrder" class="form-control form-control-solid"
                                               value="1">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let attributeIndex =  {{ $shippingMethod->attributes->count() }} ;
        const attributesContainer = document.getElementById('attributes-container');
        const attributesTableBody = attributesContainer.querySelector('tbody');
        const shippingForm = document.getElementById('shippingForm');
        const submitButton = document.getElementById('submitButton');



        function reindexAttributes() {
            const rows = attributesTableBody.querySelectorAll('tr');
             attributeIndex = rows.length;
            rows.forEach((row, index) => {
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.name.startsWith('attributes[')) {
                        const originalName = input.name;
                        const newName = originalName.replace(/attributes\[\d+\]/, `attributes[${index}]`);
                        input.name = newName;
                    }
                });
            });
              
        }

        document.getElementById('saveAttribute').addEventListener('click', function () {
            const attributeName = document.getElementById('attributeName').value;
             const attributeType = document.getElementById('attributeType').value;
            const attributeValue = document.getElementById('attributeValue').value;
            const attributeSortOrder = document.getElementById('attributeSortOrder').value;

              const newRow = document.createElement('tr');
            newRow.innerHTML = `
                   <td>
                             <input type="text" name="attributes[${attributeIndex}][name]" value="${attributeName}" class="form-control form-control-solid" required>
                          </td>
                         
                           <td>
                                <input type="text" name="attributes[${attributeIndex}][value]" value="${attributeValue}" class="form-control form-control-solid">
                            </td>
                            <td>
                                <input type="number" name="attributes[${attributeIndex}][sort_order]" value="${attributeSortOrder}" class="form-control form-control-solid">
                            </td>
                             <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-attribute">
                                        <i class="fas fa-trash"></i> {{ __('Remove') }}
                                    </button>
                                </td>

                        `;
                attributesTableBody.appendChild(newRow)
            attributeIndex++;
                reindexAttributes(); 
                attachRemoveHandlers();
            document.getElementById('attributeName').value = '';
             document.getElementById('attributeType').value = 'text';
            document.getElementById('attributeValue').value = '';
            document.getElementById('attributeSortOrder').value = '1';

            $('#addAttributeModal').modal('hide');

        });

        submitButton.addEventListener('click', function (event) {
            event.preventDefault();
            shippingForm.submit();
        });


         function attachRemoveHandlers() {
            const removeButtons = attributesTableBody.querySelectorAll('.remove-attribute');
            removeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const row = this.closest('tr');
                    const attributeId = row.dataset.attributeId;

                    Swal.fire({
                        title: '{{ __('Are you sure?') }}',
                        text: "{{ __('You wont be able to revert this!') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: '{{ __('Yes, delete it!') }}',
                        cancelButtonText: '{{ __('Cancel') }}'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if(attributeId){
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'delete_attributes[]';
                                hiddenInput.value = attributeId;
                                shippingForm.appendChild(hiddenInput);
                            }
                             row.remove();
                            reindexAttributes(); 
                            Swal.fire(
                                '{{ __('Removed!') }}',
                                '{{ __('Attribute has been removed.') }}',
                                'success'
                            )
                        }
                    });
                });
            });
        }


        attachRemoveHandlers();
        reindexAttributes();

    </script>
@endsection