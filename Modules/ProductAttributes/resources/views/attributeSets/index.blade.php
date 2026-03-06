@extends('base::layouts.mt-main')
@section('content')
    @php
        $authUser = auth()->user();
        $isSuperAdmin = $authUser && $authUser->is_super_admin == 1;
    @endphp
    <style>
        .image-input-placeholder {
            background-image: url("https://preview.keenthemes.com/html/metronic/docs/assets/media/svg/avatars/blank.svg");
        }

        [data-bs-theme="dark"] .image-input-placeholder {
            background-image: url("https://preview.keenthemes.com/html/metronic/docs/assets/media/svg/avatars/blank.svg");
        }
    </style>

    <div class="container">
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                <!--begin::Products-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <!--begin::Search-->
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                                <input type="text" data-kt-ecommerce-product-filter="search" id="attribute_set_search" name = "search" class="form-control form-control-solid w-250px ps-12" placeholder="Search Attribute Set" />
                            </div>
                            <!--end::Search-->
                        </div>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                            <div class="w-100 mw-150px">
                                <!--begin::Select2-->
                                <select class="form-select form-select-solid" id="attribute_set_status" name ="attribute_set_status" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-product-filter="status">
                                    <option value="all" selected>All</option>
                                    @foreach($attributeSetStatuses as $attributeStatusKey => $attributeStatusEl)
                                        <option value="{{ $attributeStatusKey }}">{{ $attributeStatusEl }}</option>
                                    @endforeach
                                </select>
                                <!--end::Select2-->
                            </div>
                            <!--begin::Add product-->
                            @if($isSuperAdmin || $authUser->can('delete_attribute_set'))
                            <button id="delete-selected" class="btn btn-danger">Delete Selected</button>
                            @endif
                                @if($isSuperAdmin || $authUser->can('create_attribute_set'))
                            <a href="{{ $createUrl  }}" class="btn btn-primary">Add Attribute Set</a>
                        @endif
                            <!--end::Add product-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">

                        <!--begin::Datatable-->
                        <table id="attribute_set_table" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox"
                                                   data-kt-check="true" id="select-all" data-kt-check-target="" value="1"/>
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Label</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Linked Attributes Count</th>
                                    <th>Is Active</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                            </tbody>
                        </table>
                        <!--end::Datatable-->
                    </div>

                    <!--end::Card body-->
                </div>
                <!--end::Products-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
@endsection
@section('custom-js-section')
    <script src="{{ asset('build-base/ktmt/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/apps/ecommerce/catalog/products.js') }}"></script>
    <script>

        var KTDatatablesServerSide = function () {
            var dt;

            // Initialize DataTable
            var initDatatable = function () {
                dt = $("#attribute_set_table").DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    ajax: {
                        url: "{{ route('product.attribute.sets.all') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-Token': '{{ csrf_token() }}',
                        },
                        data: function (d) {
                            d.search.value =  $('#attribute_set_search').val();
                            d.attribute_set_status =  $('#attribute_set_status').val();
                        },
                        error: function (xhr, error, thrown) {
                            console.error('Ajax error:', error);
                            console.error('Thrown error:', thrown);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function (data) {
                                return `<input type="checkbox" class="form-check-input select-checkbox" value="${data}">`;
                            }
                        },
                        {data: 'id', name: 'id'},
                        {data: 'name', name: 'name', searchable: true, sortable: true},
                        {data: 'label', name: 'label', searchable: true, sortable: true},
                        {data: 'set_type', name: 'set_type', searchable: true},
                        {data: 'description', name: 'description'},
                        {data: 'linked_attributes', name: 'linked_attributes'},
                        {data: 'is_active', name: 'is_active'},
                        {data: 'created_by', name: 'created_by'},
                        {data: 'created_at', name: 'created_at'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ],
                });

                $('#attribute_set_search').on('keyup', function () {
                    dt.draw();
                });
                $('#attribute_set_status').change(function () {
                    const selectedValue = $(this).val(); // Get the selected value
                    dt.draw();
                });

                // Select/Deselect All Checkboxes
                $('#select-all').on('click', function () {
                    $('.select-checkbox').prop('checked', this.checked);
                });

                // Bulk Delete Selected Rows
                $('#delete-selected').on('click', function() {
                    let selected = [];
                    $('.select-checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });

                    if (selected.length > 0) {

                        // Display confirmation dialog
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This will delete the selected attribute sets and cannot be undone!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete them!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: "{{ route('product.attribute.sets.bulk-delete') }}",
                                    type: 'POST',
                                    data: {
                                        ids: selected,
                                        _token: "{{ csrf_token() }}"
                                    },
                                    success: function (response) {
                                        Swal.fire({
                                            text: response.message,
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-primary"
                                            }
                                        });
                                        dt.ajax.reload(); // Reload the DataTable
                                    },
                                    error: function(xhr, status, error) {
                                        console.log('Bulk delete failed:', error);
                                    }
                                });
                            }
                        });

                    } else {
                        Swal.fire({
                            text: "Please select at least one attribute set.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                });

                // Delete Individual Product
                $('body').on('click', '.deleteAttributeSet', function () {
                    var attribute_id = $(this).data("id"); // Fetch the product ID

                    if (!attribute_id) {
                        Swal.fire({
                            text: "Invalid attribute ID",
                            icon: "error",
                        });
                        return;
                    }

                    // Show confirmation dialog
                    Swal.fire({
                        title: "Are you sure?",
                        text: "This action cannot be undone. Do you want to delete this attribute set?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proceed with deletion
                            $.ajax({
                                url: "{{ route('product.attribute.sets.delete') }}",
                                type: 'POST',
                                data: {
                                    id: attribute_id,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function (response) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "success",
                                    });
                                    // Reload the DataTable or update the UI
                                    dt.ajax.reload();
                                },
                                error: function (xhr, status, error) {
                                    console.error("AJAX Error:", xhr.responseText);
                                    Swal.fire({
                                        text: "Failed to delete the attribute. Please try again.",
                                        icon: "error",
                                    });
                                }
                            });
                        }
                    });
                });

            };

            return {
                init: function () {
                    initDatatable();
                }
            };
        }();
        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });

    </script>
@endsection
