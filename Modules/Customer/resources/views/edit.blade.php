@extends('base::layouts.mt-main')

@section('content')
    <div class="d-flex flex-column flex-root">

        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <div class="container-xxl" id="kt_content_container">
                <h1 class="text-center mb-5">Edit Customer</h1>

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="form">
                            @csrf
                            @method('PUT')

                            <div class="row g-5 mb-4">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control form-control-solid @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name"
                                           value="{{ old('first_name', $customer->first_name) }}" required>
                                    @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control form-control-solid @error('last_name') is-invalid @enderror"
                                           id="last_name" name="last_name"
                                           value="{{ old('last_name', $customer->last_name) }}">
                                    @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control form-control-solid @error('email') is-invalid @enderror"
                                       id="email" name="email"
                                       value="{{ old('email', $customer->email) }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-7">
                                <label for="phone" class="form-label fs-6 fw-semibold mb-3 required">Phone </label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="w-150px">
                                        <select 
                                            id="dial_code" 
                                            name="dial_code" 
                                            class="form-select form-select-solid" 
                                            data-control="select2" 
                                            data-placeholder="Select code" 
                                            data-kt-initialized="1"
                                            required
                                        >
                                            <option value=""></option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->dial_code }}" 
                                                    {{ old('dial_code', $customer->dial_code) == $country->dial_code ? 'selected' : '' }}>
                                                    {{ $country->name }} ({{ $country->dial_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input 
                                        type="tel" 
                                        class="form-control form-control-solid flex-grow-1" 
                                        id="phone" 
                                        name="phone" 
                                        placeholder="Enter phone number" 
                                        value="{{ old('phone', $customer->phone) }}"
                                        required
                                    >
                                </div>
                            </div>
                            

                            <div class="mb-4">
                                <label for="group" class="form-label">Group</label>
                                <select class="form-select form-select-solid @error('group') is-invalid @enderror" id="group" name="group" required>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id ?? null}}"
                                            {{ $group->id == old('group', $currentGroup->id) ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('group')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select form-select-solid @error('is_active') is-invalid @enderror" id="status"
                                        name="is_active" required>
                                    <option value="1" {{ $customer->is_active == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $customer->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control form-control-solid @error('password') is-invalid @enderror"
                                       id="password" name="password">
                                <div class="form-text">Leave blank if you do not want to change the password.</div>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <h5 class="mb-4">
                                  Addresses
                                    <button type="button" class="btn btn-sm btn-light add-address" data-bs-toggle="modal" data-bs-target="#addressModal">Add Address</button>
                            </h5>
                            <div class="card mt-5">
                               <div class="card-body">
                                  <h4>Saved Addresses</h4>
                                    <table class="table table-bordered" id="address-grid">
                                        <thead>
                                        <tr>
                                            <th>Address Line 1</th>
                                            <th>City</th>
                                            <th>State</th>
                                            <th>Postal Code</th>
                                            <th>Country</th>
                                            <th>Type</th>
                                            <th>Default</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                         @foreach ($customer->addresses as $index => $address)
                                            <tr class="address-item">
                                                <td>{{ $address->address_line1 }}</td>
                                                <td>{{ $address->city }}</td>
                                                <td>{{ $address->state }}</td>
                                                <td>{{ $address->postal_code }}</td>
                                                <td>{{ $address->country }}</td>
                                                <td>{{ $address->type }}</td>
                                                 <td>{{ $address->is_default ? 'Yes' : 'No' }}</td>
                                                <td>
                                                     <button type="button" class="btn btn-sm btn-primary edit-address"
                                                        data-bs-toggle="modal" data-bs-target="#addressModal"
                                                        data-address-id="{{ $address->id }}"
                                                        data-address-line1="{{ $address->address_line1 }}"
                                                        data-city="{{ $address->city }}"
                                                        data-state="{{ $address->state }}"
                                                        data-postal-code="{{ $address->postal_code }}"
                                                        data-country="{{ $address->country }}"
                                                        data-type="{{ $address->type }}"
                                                        data-is-default="{{ $address->is_default }}"

                                                    >Edit</button>
                                                    <button type="button" class="btn btn-sm btn-danger remove-address" data-address-id="{{ $address->id }}">Remove</button>
                                                     <input type="hidden" name="address[{{ $index }}][id]" value="{{ $address->id }}">
                                                      <input type="hidden" name="address[{{ $index }}][address_line1]" value="{{ $address->address_line1 }}">
                                                      <input type="hidden" name="address[{{ $index }}][city]" value="{{ $address->city }}">
                                                      <input type="hidden" name="address[{{ $index }}][state]" value="{{ $address->state }}">
                                                      <input type="hidden" name="address[{{ $index }}][postal_code]" value="{{ $address->postal_code }}">
                                                      <input type="hidden" name="address[{{ $index }}][country]" value="{{ $address->country }}">
                                                      <input type="hidden" name="address[{{ $index }}][type]" value="{{ $address->type }}">
                                                        <input type="hidden" name="address[{{ $index }}][is_default]" value="{{ $address->is_default ? 1 : 0 }}">

                                                </td>
                                            </tr>
                                         @endforeach
                                        </tbody>
                                    </table>
                                </div>
                             </div>

                            <div id="address-hidden-inputs">

                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('customers.index') }}" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

      <!-- Address Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-7">
                            <label for="modal_address_line1" class="form-label fw-semibold mb-2 required">Address Line
                                1</label>
                            <input type="text" class="form-control form-control-solid" id="modal_address_line1"
                                   required>
                            <div class="invalid-feedback">Please provide an address line 1.</div>
                        </div>
                        <div class="col-md-12 mb-7">
                            <label for="modal_city" class="form-label fw-semibold mb-2 required">City</label>
                            <input type="text" class="form-control form-control-solid" id="modal_city" required>
                            <div class="invalid-feedback">Please provide a city.</div>
                        </div>
                        <div class="col-md-12 mb-7">
                            <label for="modal_state" class="form-label fw-semibold mb-2 required">State</label>
                            <input type="text" class="form-control form-control-solid" id="modal_state" required>
                            <div class="invalid-feedback">Please provide a state.</div>
                        </div>
                        <div class="col-md-12 mb-7">
                            <label for="modal_postal_code" class="form-label fw-semibold mb-2 required">Postal
                                Code</label>
                            <input type="text" class="form-control form-control-solid" id="modal_postal_code"
                                   required>
                            <div class="invalid-feedback">Please provide a postal code.</div>
                        </div>
                        <div class="col-md-12 mb-7">
                            <label for="modal_country" class="form-label fw-semibold mb-2 required">Country</label>
                            <input type="text" class="form-control form-control-solid" id="modal_country" required>
                            <div class="invalid-feedback">Please provide a country.</div>
                        </div>
                        <div class="col-md-12 mb-7">
                            <label for="modal_type" class="form-label fw-semibold mb-2 required">Type</label>
                            <select class="form-select form-select-solid" id="modal_type" required>
                                <option value="billing">Billing</option>
                                <option value="shipping">Shipping</option>
                            </select>
                            <div class="invalid-feedback">Please select the address type.</div>
                        </div>
                        <div class="col-md-12 mb-7">
                            <label for="modal_is_default" class="form-label fw-semibold mb-2 required">Default Address</label>
                            <input type="checkbox"  id="modal_is_default" value="1">
                          </div>
                          <input type="hidden" id="modal_address_id" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAddressModal">Save Address</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-js-section')
    <script>
         $(document).ready(function() {
    let savedAddresses = [];
    let addressCount = $('.address-item').length;
    let editingAddressId = null;
    
    // Initialize savedAddresses with existing addresses
    $('#address-grid .address-item').each(function() {
        savedAddresses.push({
            id: $(this).find('input[name^="address["][name$="][id]"]').val(),
            address_line1: $(this).find('input[name^="address["][name$="][address_line1]"]').val(),
            city: $(this).find('input[name^="address["][name$="][city]"]').val(),
            state: $(this).find('input[name^="address["][name$="][state]"]').val(),
            postal_code: $(this).find('input[name^="address["][name$="][postal_code]"]').val(),
            country: $(this).find('input[name^="address["][name$="][country]"]').val(),
            type: $(this).find('input[name^="address["][name$="][type]"]').val(),
            is_default: $(this).find('input[name^="address["][name$="][is_default]"]').val() == 1
        });
    });

    function updateAddressGrid() {
        let tableBody = $('#address-grid tbody');
        let hiddenInputsContainer = $('#address-hidden-inputs');

        tableBody.empty();
        hiddenInputsContainer.empty();

        savedAddresses.forEach((address, index) => {
            tableBody.append(`
                <tr class="address-item">
                    <td>${address.address_line1}</td>
                    <td>${address.city}</td>
                    <td>${address.state}</td>
                    <td>${address.postal_code}</td>
                    <td>${address.country}</td>
                    <td>${address.type}</td>
                    <td>${address.is_default ? 'Yes' : 'No'}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary edit-address"
                                data-bs-toggle="modal" 
                                data-bs-target="#addressModal"
                                data-address-id="${address.id}"
                                data-address-line1="${address.address_line1}"
                                data-city="${address.city}"
                                data-state="${address.state}"
                                data-postal-code="${address.postal_code}"
                                data-country="${address.country}"
                                data-type="${address.type}"
                                data-is-default="${address.is_default}">
                            Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-danger remove-address" 
                                data-address-id="${address.id}">
                            Remove
                        </button>
                    </td>
                </tr>
            `);

            // Add hidden inputs for form submission
            hiddenInputsContainer.append(`
                ${address.id ? `<input type="hidden" name="address[${index}][id]" value="${address.id}">` : ''}
                <input type="hidden" name="address[${index}][address_line1]" value="${address.address_line1}">
                <input type="hidden" name="address[${index}][city]" value="${address.city}">
                <input type="hidden" name="address[${index}][state]" value="${address.state}">
                <input type="hidden" name="address[${index}][postal_code]" value="${address.postal_code}">
                <input type="hidden" name="address[${index}][country]" value="${address.country}">
                <input type="hidden" name="address[${index}][type]" value="${address.type}">
                <input type="hidden" name="address[${index}][is_default]" value="${address.is_default ? 1 : 0}">
            `);
        });
        
        addressCount = savedAddresses.length;
    }

    $('#saveAddressModal').on('click', function() {
        const addressData = {
            address_line1: $('#modal_address_line1').val(),
            city: $('#modal_city').val(),
            state: $('#modal_state').val(),
            postal_code: $('#modal_postal_code').val(),
            country: $('#modal_country').val(),
            type: $('#modal_type').val(),
            is_default: $('#modal_is_default').is(':checked')
        };

        if (editingAddressId) {
            // For existing addresses, include the ID
            addressData.id = editingAddressId;
            savedAddresses = savedAddresses.map(address => 
                address.id == editingAddressId ? {...address, ...addressData} : address
            );
            editingAddressId = null;
        } else {
            // For new addresses, don't set an ID - let the database handle it
            savedAddresses.push(addressData);
        }

        updateAddressGrid();
        $('#addressModal').modal('hide');
        resetModalForm();
    });

    function resetModalForm() {
        $('#modal_address_line1').val('');
        $('#modal_city').val('');
        $('#modal_state').val('');
        $('#modal_postal_code').val('');
        $('#modal_country').val('');
        $('#modal_type').val('billing');
        $('#modal_is_default').prop('checked', false);
        $('#modal_address_id').val('');
    }

    $(document).on('click', '.remove-address', function() {
        const addressId = $(this).data('address-id');
        savedAddresses = savedAddresses.filter(address => address.id != addressId);
        updateAddressGrid();
    });

    $(document).on('click', '.edit-address', function() {
        editingAddressId = $(this).data('address-id');
        $('#modal_address_id').val(editingAddressId);
        $('#modal_address_line1').val($(this).data('address-line1'));
        $('#modal_city').val($(this).data('city'));
        $('#modal_state').val($(this).data('state'));
        $('#modal_postal_code').val($(this).data('postal-code'));
        $('#modal_country').val($(this).data('country'));
        $('#modal_type').val($(this).data('type'));
        $('#modal_is_default').prop('checked', $(this).data('is-default'));
    });

    updateAddressGrid();
});
    </script>
@endsection