@extends('base::layouts.mt-main')
@section('content')

    <div class="card mb-6">
        <div class="card-header border-0 pt-5">
            <div class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Edit Permissions for Role: {{ $role->label }}</span>
            </div>
        </div>
        <div class="card-body py-3">
            <form action="{{ url('/roles/' . $role->id . '/edit-permissions-user') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div
                            class="d-flex flex-wrap flex-stack mb-6 border border-solid border-gray-300 rounded px-7 py-3 mb-6 align-items-start h-100">
                            <!--begin::Heading-->
                            <h5 class="fw-bold my-2">Select Permissions</h5>
                            <!--end::Heading-->
                            <div class="form-check form-check-sm form-check-custom form-check-solid w-100">
                                <input class="form-check-input" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all-permissions">
                                    Select All
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-9">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="col-md-12">
                            <div
                                class="d-flex flex-wrap flex-stack mb-6 border border-dashed border-gray-300 rounded p-5 mb-4">
                                <div class="col-md-12 mb-5">
                                    @php
                                        $module_name = ucfirst($module);
                                        $convert_module_name = strtolower(str_replace(' ', '-', $module_name));
                                    @endphp
                                    <div
                                        class="form-check form-check-sm form-check-custom form-check-solid w-100 align-items-center">
                                        <input class="form-check-input permission-module-checkbox me-2" type="checkbox"
                                               id="{{$convert_module_name}}-module">
                                        <h5 class="m-0">{{ $module }}</h5>
                                    </div>
                                </div>
                                @foreach($modulePermissions as $permission)
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid w-100">
                                            <input type="checkbox"
                                                   class="form-check-input permission-checkbox {{$convert_module_name}}-module"
                                                   name="permissions[]" value="{{ $permission->id }}"
                                                   {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                                   module="{{$convert_module_name}}">
                                            <label class="form-check-label">{{ $permission->label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary">Update Permissions</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>

@stop
@section('custom-js-section')
    <script>

        document.getElementById('select-all').addEventListener('change', function () {
            var isChecked = this.checked;
            var checkboxes = document.querySelectorAll('input[name="permissions[]"]');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
            document.querySelectorAll('.permission-module-checkbox').forEach(function (moduleCheckbox) {
                moduleCheckbox.checked = isChecked;
            });
        });


        document.addEventListener('DOMContentLoaded', function () {
            const isAdminCheckbox = document.getElementById('is-administrator-role');
            const isVendorCheckbox = document.getElementById('is-vendor-role');
            const errorMessage = document.getElementById('error-message');

            function togglePermissions() {
                const isAdminChecked = isAdminCheckbox.checked;
                const isVendorChecked = isVendorCheckbox.checked;
                const modulePermissionRows = document.querySelectorAll('.module-permission-row');

                if (isAdminChecked && isVendorChecked) {
                    errorMessage.style.display = 'block';
                } else {
                    errorMessage.style.display = 'none';
                    modulePermissionRows.forEach(row => {
                        const module = row.getAttribute('data-module');
                        if (isAdminChecked) {
                            if (['Admin User Management', 'Promo Code', 'Price Rule', 'B2C Configuration', 'Wallet Management', 'Web Configuration Management', 'Report Management', 'Page Management', 'Subscription Management'].includes(module)) {
                                row.style.display = 'block';
                            } else {
                                row.style.display = 'none';
                            }
                        } else if (isVendorChecked) {
                            if (['Vendor User Management', 'User Permission', 'Wallet Management', 'Order Management'].includes(module)) {
                                row.style.display = 'block';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                }
            }

            isAdminCheckbox.addEventListener('change', togglePermissions);
            isVendorCheckbox.addEventListener('change', togglePermissions);

            togglePermissions();

            document.getElementById('select-all-permissions').addEventListener('change', function () {
                const isChecked = this.checked;

                if (isAdminCheckbox.checked) {
                    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                        const module = checkbox.getAttribute('module');
                        if (['admin-user-management', 'promo-code', 'price-rule', 'b2c-configuration', 'wallet-management', 'web-configuration-management', 'report-management', 'page-management', 'subscription-management'].includes(module)) {
                            checkbox.checked = isChecked;
                        }
                    });
                } else if (isVendorCheckbox.checked) {
                    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                        const module = checkbox.getAttribute('module');
                        if (['vendor-user-management', 'user-permission', 'wallet-management', 'order-management'].includes(module)) {
                            checkbox.checked = isChecked;
                        }
                    });
                }
            });
        });

        document.querySelectorAll('.permission-module-checkbox').forEach(moduleCheckbox => {
            moduleCheckbox.addEventListener('change', function () {
                const module = this.id.replace('-module', '');
                const isChecked = this.checked;

                document.querySelectorAll(`input.permission-checkbox[module="${module}"]`).forEach(permissionCheckbox => {
                    permissionCheckbox.checked = isChecked;
                });
            });
        });

        jQuery(document).on('change', '.permission-checkbox', function() {
            let module = jQuery(this).attr('module');
            selectModule(module);
        });
        function selectModule(module){
            if ($('.permission-checkbox.'+module+'-module:checked').length > 0) {
                jQuery('#'+module+'-module').prop('checked', true);
            } else {
                jQuery('#'+module+'-module').prop('checked', false);
            }
        }

        $(document).ready(function() {
            $('input[name="role-type"]').change(function() {
                if ($('#is-administrator-role').is(':checked')) {
                    $('#is-vendor-role').prop('disabled', true);
                } else if ($('#is-vendor-role').is(':checked')) {
                    $('#is-administrator-role').prop('disabled', true);
                } else {
                    $('#is-administrator-role, #is-vendor-role').prop('disabled', false);
                }
            });

            $('input[name="role-type"]:checked').trigger('change');
        });
    </script>
    @include('userpermission::Role.permission-js')
@stop
