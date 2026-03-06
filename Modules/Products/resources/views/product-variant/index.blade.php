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
                            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap">

                                <!-- Search Input -->
                                <div class="d-flex align-items-center position-relative">
                                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                                    <input type="text" id="product_search" name="search" class="form-control form-control-solid w-250px ps-12" placeholder="Search Product">
                                </div>
                            </div>
                        </div>

                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">

                            <!--begin::Add product-->
                            <!-- Export Products Section -->
                            <div class="d-flex align-items-center ms-4">
                                <select class="form-select form-select-solid" id="product_status" name ="product_status" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-product-filter="status">

                                    <option value="all" selected>All</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center ms-4">
                            </div>

                            <div class="d-flex align-items-center ms-4">
                                <button id="delete-selected" class="btn btn-danger btn-sm">Delete Selected</button>
                            </div>
                            <div class="d-flex align-items-center ms-4">
                                <a href="{{ route('create.variant.product', $parent_product_id)  }}" class="btn btn-primary btn-sm">Add Product</a>
                            </div>


                            <!--end::Add product-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">

                        <!--begin::Datatable-->
                        <table id="product_table" class="table align-middle table-row-dashed fs-6 gy-5">
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
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Product Type</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @foreach( $products as $product)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input select-checkbox" value="{{  $product->id  }}">
                                        </td>
                                        <td> {{ $product->id  }}</td>
                                        <td> {{ $product->name  }}</td>
                                        <td> {{ $product->sku  }}</td>
                                        <td> {{ $product->price  }}</td>
                                        <td> {{ $product->quantity  }}</td>
                                        <td> {{ $product->productType ? $product->productType->name : 'N/A'  }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('product.variant.edit', $product->id ) }}" class="btn btn-sm btn-light btn-active-light-primary me-2">
                                                    <i class="fas fa-edit"></i>Edit</a>
                                                <button class="btn btn-sm btn-light btn-active-light-danger deleteVariantProduct" data-id="{{ $product->id }}">
                                                    <i class="fas fa-trash"></i>Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('custom-js-section')
    <script src="{{ asset('build-base/ktmt/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/apps/ecommerce/catalog/products.js') }}"></script>
    <script>

        var KTDatatablesServerSide = function () {
            var dt;

            var initDatatable = function () {
                dt = $("#product_table").DataTable();

                $('#product_search').on('keyup', function () {
                    dt.draw();
                });
                $('#product_status').change(function () {
                    const selectedValue = $(this).val();
                    dt.draw();
                });
                $('#select-all').on('click', function () {
                    $('.select-checkbox').prop('checked', this.checked);
                });

                $('#delete-selected').on('click', function() {
                    let selected = [];
                    $('.select-checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });

                    if (selected.length > 0) {
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
                                    url: "{{ route('product.variable.bulk-delete') }}",
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
                                        dt.ajax.reload();
                                    },
                                    error: function(xhr, status, error) {
                                        console.log('Bulk delete failed:', error);
                                    }
                                });
                            }
                        });

                    } else {
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

                $('body').on('click', '.deleteVariantProduct', function () {
                    var product_id = $(this).data("id");

                    if (!product_id) {
                        Swal.fire({
                            text: "Invalid product ID",
                            icon: "error",
                        });
                        return;
                    }

                    Swal.fire({
                        title: "Are you sure?",
                        text: "This action cannot be undone. Do you want to delete this product?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('product.variable.delete') }}",
                                type: 'POST',
                                data: {
                                    id: product_id,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function (response) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "success",
                                    });
                                    dt.ajax.reload();
                                },
                                error: function (xhr, status, error) {
                                    console.error("AJAX Error:", xhr.responseText);
                                    Swal.fire({
                                        text: "Failed to delete the product. Please try again.",
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
