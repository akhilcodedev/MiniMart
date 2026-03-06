@extends('base::layouts.mt-main')
@section('content')
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
                                <input type="text" data-kt-ecommerce-product-filter="search" id="product_search" name = "search" class="form-control form-control-solid w-250px ps-12" placeholder="Search Product" />
                            </div>
                            <!--end::Search-->
                        </div>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                            <div class="w-100 mw-150px">
                                <!--begin::Select2-->
                                <select class="form-select form-select-solid" id="product_status" name ="product_status" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-product-filter="status">

                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <!--end::Select2-->
                            </div>
                            <!--begin::Add product-->
                            <button id="delete-selected" class="btn btn-danger">Delete Selected</button>
                            <a href="{{ $create_url  }}" class="btn btn-primary">Add Product</a>
                            <!--end::Add product-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">

                        <!--begin::Datatable-->
                        <table id="cart_table" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox"
                                               data-kt-check="true" id="select-all" data-kt-check-target="" value="1"/>
                                    </div>
                                </th>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Product Type</th>
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
                dt = $("#cart_table").DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    ajax: {
                        url: "{{ route('cart.all') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-Token': '{{ csrf_token() }}',
                        },
                        data: function (d) {
                            d.search.value =  $('#product_search').val();
                            d.product_status =  $('#product_status').val();
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

                        {data: 'customer_name', name: 'customer_name'},
                        {data: 'product_name', name: 'product_name'},

                        {
                            data: 'sku',
                            name: 'sku',
                            searchable: true,
                            sortable: true
                        },
                        {data: 'price', name: 'price', searchable: true},
                        {data: 'quantity', name: 'quantity', searchable: true},
                        {data: 'product_type', name: 'product_type'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ],

                });

                $('#product_search').on('keyup', function () {
                    dt.draw();
                });
                $('#product_status').change(function () {
                    const selectedValue = $(this).val(); // Get the selected value
                    dt.draw();
                });

                // Select/Deselect All Checkboxes
                $('#select-all').on('click', function () {
                    $('.select-checkbox').prop('checked', this.checked);
                });
                // Delete Selected Rows
                $('#delete-selected').on('click', function() {
                    let selected = [];
                    $('.select-checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });

                    if (selected.length > 0) {

                        // Display confirmation dialog
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This will delete the selected products and cannot be undone!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete them!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: "{{ route('products.bulk-delete') }}",
                                    type: 'POST',
                                    data: {
                                        ids: selected,
                                        _token: "{{ csrf_token() }}"
                                    },
                                    success: function (response) {
                                        //alert(response.message);
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
                                    }
                                });
                            }
                        });

                    } else {
                        //alert('Please select at least one product.');
                        Swal.fire({
                            text: "Please select at least one product.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                });
                $('body').on('click', '.deleteProduct', function () {

                    var product_id = $(this).data("id");
                    if (product_id != 0 || product_id != '') {

                        // Display confirmation dialog
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This will delete the selected products and cannot be undone!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete them!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: "{{ route('products.delete') }}",
                                    type: 'POST',
                                    data: {
                                        ids: selected,
                                        _token: "{{ csrf_token() }}"
                                    },
                                    success: function (response) {
                                        //alert(response.message);
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
                                    }
                                });
                            }
                        });

                    }

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
