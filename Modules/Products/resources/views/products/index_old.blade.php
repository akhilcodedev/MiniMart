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
                        </div>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                        {{--                            <div class="w-100 mw-150px">--}}
                        {{--                                <!--begin::Select2-->--}}
                        {{--                                <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-product-filter="status">--}}
                        {{--                                    <option></option>--}}
                        {{--                                    <option value="all">All</option>--}}
                        {{--                                    <option value="published">Published</option>--}}
                        {{--                                    <option value="scheduled">Scheduled</option>--}}
                        {{--                                    <option value="inactive">Inactive</option>--}}
                        {{--                                </select>--}}
                        {{--                                <!--end::Select2-->--}}
                        {{--                            </div>--}}
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
                        <!--begin::Table-->
                        <table id="products-table" class="table table-bordered">
                            <thead>

                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Product Type</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                        </table>
                        <!--end::Table-->
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

        $(document).ready(function () {
            let table =   $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"custom-toolbar">frtip', // Custom toolbar + search box
                ajax: "{{ route('products.index') }}",
                //type: 'GET', // HTTP method
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return `<input type="checkbox" class="select-checkbox" value="${data}">`;
                        }
                    },
                    {data: 'id', name: 'id'},
                    {
                        data: 'name',
                        name: 'name',
                        searchable: true,
                        sortable: true
                    },

                    {
                        data: 'sku',
                        name: 'sku',
                        searchable: true,
                        sortable: true
                    },
                    {
                        data: 'price',
                        name: 'price',
                        sortable: true,
                        searchable: true
                    },
                    {
                        data: 'quantity',
                        name: 'quantity',
                        searchable: true,
                        sortable: true
                    },
                    {
                        data: 'product_type',
                        name: 'product_type',
                        searchable: true,
                        sortable: true
                    },
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                pageLength: 10, // Pagination: 10 rows per page
                order: [[1, 'asc']]
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
                                    table.ajax.reload(); // Reload the DataTable
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
                                    table.ajax.reload(); // Reload the DataTable
                                }
                            });
                        }
                    });

                }

            });

        });


    </script>
@endsection
