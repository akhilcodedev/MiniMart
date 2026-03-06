@extends('base::layouts.mt-main')

@section('content')
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <div class="card">
        <div class="card-body">
            <h1 class="text-center mb-4">Add New Customer</h1>

            <form class="row needs-validation" method="POST" action="{{ route('customers.store') }}" novalidate>
                @csrf
                <!-- Customer Details -->
                <div class="col-md-12 mb-7 d-flex justify-content-end gap-3">
                    <button class="btn btn-primary" type="submit">Save Customer</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>                
                <div class="col-md-12">
                    <h4 class="mb-4">Customer Details</h4>
                </div>
                <div class="col-md-6 mb-7">
                    <label for="first_name" class="form-label fw-semibold mb-2 required">First Name</label>
                    <input type="text" class="form-control form-control-solid" id="first_name" name="first_name"
                           required>
                    <div class="invalid-feedback">Please provide a first name.</div>
                </div>

                <div class="col-md-6 mb-7">
                    <label for="last_name" class="form-label fw-semibold mb-2">Last Name</label>
                    <input type="text" class="form-control form-control-solid" id="last_name" name="last_name">
                </div>

                <div class="col-md-6 mb-7">
                    <label for="email" class="form-label fw-semibold mb-2 required">Email</label>
                    <input type="email" class="form-control form-control-solid" id="email" name="email" required>
                    <div class="invalid-feedback">Please provide a valid email.</div>
                </div>
                
                <div class="col-md-6 mb-7">
                    <label for="phone" class="form-label fs-6 fw-semibold mb-3 required">Phone Number</label>
                    
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
                                    <option value="{{ $country->dial_code }}">{{ $country->name }} ({{ $country->dial_code }})</option>
                                @endforeach
                            </select>
                        </div>                        
                        <input 
                            type="tel" 
                            class="form-control form-control-solid flex-grow-1" id="phone" name="phone" placeholder="Enter phone number" 
                        required>
                    </div>
               
                </div>
     
                    {{-- <div class="fv-plugins-message-container invalid-feedback">
                        <div data-field="phone" data-validator="notEmpty">Please provide a valid phone number.</div>
                    </div> --}}
                <div class="col-md-6 mb-7">
                    <label for="customer_code" class="form-label fw-semibold mb-2 required">Customer Code</label>
                    <input type="text" class="form-control form-control-solid" id="customer_code" name="customer_code"
                           required>
                    <div class="invalid-feedback">Please provide a customer code.</div>
                </div>

                <div class="col-md-6 mb-7">
                    <label for="password" class="form-label fw-semibold mb-2 required">Password</label>
                    <input type="password" class="form-control form-control-solid" id="password" name="password"
                           required>
                    <div class="invalid-feedback">Please provide a password.</div>
                </div>

                <div class="col-md-6 mb-7">
                    <label for="is_active" class="form-label fw-semibold mb-2 required">Status</label>
                    <select class="form-select form-select-solid" id="is_active" name="is_active" required>
                        <option value="" disabled selected>Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="invalid-feedback">Please select the status.</div>
                </div>

                <div class="col-md-6 mb-7">
                    <label for="group" class="form-label fw-semibold mb-2 required">Group</label>
                    <select class="form-select form-select-solid" id="group" name="group_id" required>
                        <option value="" disabled>Select Group</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}"
                            @if((old('group_id') == $group->id) || ($group->name == 'General' && !old('group_id')))
                                selected
                            @endif>
                        {{ $group->name }}
                    </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select a group.</div>
                </div>


                <!-- Address Details -->
                <div class="col-md-12 d-flex justify-content-between align-items-center mt-5 mb-4">
                  <h4 class="">Address Details</h4>
                  <button type="button" class="btn btn-sm btn-light add-address" data-bs-toggle="modal" data-bs-target="#addressModal">Add Address</button>
                </div>
                <div class="card mt-5">
                   <div class="card-body">
                      <h4>Saved Addresses</h4>
                      <table class="table table-bordered" id="address-grid">
                          <thead>
                            <tr>
                              <th>Address Line </th>
                              <th>City</th>
                              <th>State</th>
                              <th>Postal Code</th>
                              <th>Country</th>
                              <th>Type</th>
                               <th>Default</th>
                            </tr>
                          </thead>
                        <tbody></tbody>
                    </table>
                  </div>
                </div>
                <div id="address-hidden-inputs">

                </div>
            </form>
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
    $('#dial_code').select2({
        placeholder: "Select code",
        minimumResultsForSearch: Infinity
    });
});
        $(document).ready(function() {
            let savedAddresses = [];
             let addressCount = 0;
            function updateAddressGrid() {
                let tableBody = $('#address-grid tbody');
                let hiddenInputsContainer = $('#address-hidden-inputs');

                tableBody.empty();
                hiddenInputsContainer.empty(); // Clear existing hidden fields

                savedAddresses.forEach((address, index) => {
                    tableBody.append(`
                         <tr>
                            <td>${address.address_line1}</td>
                             <td>${address.city}</td>
                            <td>${address.state}</td>
                            <td>${address.postal_code}</td>
                            <td>${address.country}</td>
                            <td>${address.type}</td>
                            <td>${address.is_default ? 'Yes' : 'No'}</td>
                        </tr>
                    `);

                    hiddenInputsContainer.append(`
                        <input type="hidden" name="address[${index}][address_line1]" value="${address.address_line1}">
                        <input type="hidden" name="address[${index}][city]" value="${address.city}">
                        <input type="hidden" name="address[${index}][state]" value="${address.state}">
                        <input type="hidden" name="address[${index}][postal_code]" value="${address.postal_code}">
                        <input type="hidden" name="address[${index}][country]" value="${address.country}">
                        <input type="hidden" name="address[${index}][type]" value="${address.type}">
                        <input type="hidden" name="address[${index}][is_default]" value="${address.is_default ? 1 : 0}">
                    `);
                });
            }
               $('#saveAddressModal').on('click', function() {
                     const addressData = {
                         address_line1: $('#modal_address_line1').val(),
                         city: $('#modal_city').val(),
                         state: $('#modal_state').val(),
                         postal_code: $('#modal_postal_code').val(),
                         country: $('#modal_country').val(),
                         type: $('#modal_type').val(),
                        is_default: $('#modal_is_default').is(':checked'),
                     };
                    savedAddresses.push(addressData);
                    updateAddressGrid();
                     $('#addressModal').modal('hide');
                       $('#modal_address_line1').val('');
                       $('#modal_city').val('');
                       $('#modal_state').val('');
                       $('#modal_postal_code').val('');
                       $('#modal_country').val('');
                       $('#modal_type').val('billing');
                       $('#modal_is_default').prop('checked', false);


             });
        });
    </script>
@endsection