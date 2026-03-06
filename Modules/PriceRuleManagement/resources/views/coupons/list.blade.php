@extends('base::layouts.mt-main')

@section('content')
    <div class="container">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <!-- Coupon Filter Card -->
                <div class="card card-flush mb-7">
                    <div class="card-header flex-wrap border-0 pt-6 pb-0">
                        <div class="card-title">
                            <h3 class="card-label">Coupon Filter</h3>
                        </div>
                        <div class="card-toolbar">
                            <form class="row" method="POST" action="{{ route('priceRule.cart.coupons.import') }}" enctype="multipart/form-data" name="coupon_import_form" id="coupon_import_form">
                                @csrf
                                {{--
                                <div class="col-md-7 mt-3 mt-md-0">
                                    <div class="custom-file">
                                        <input type="file" id="coupon_import_file" name="coupon_import_file" accept="text/csv" class="form-control custom-file-input" placeholder="Select CSV file" required>
                                        <label class="custom-file-label" for="coupon_import_file">Choose CSV file</label>
                                    </div>
                                </div>
                                <div class="col-auto mt-3 mt-md-0">
                                    <button type="submit" name="importSubmit" value="1" class="btn btn-warning">
                                        <i class="fas fa-file-import font-size-16 align-middle me-2"></i> CSV Import
                                    </button>
                                </div>
                                --}}
                            </form>
                            {{--
                            <a href="{{ route('priceRule.cart.downloadSampleCsv') }}" class="btn btn-info mx-2">
                                <i class="fas fa-file-csv"></i> Sample CSV
                            </a>
                            <a href="{{ route('priceRule.cart.coupons.export') }}" class="btn btn-info mx-2">
                                <i class="fas fa-file-export"></i> CSV Export
                            </a>
                            --}}
                            @if(auth()->user()->is_super_admin || auth()->user()->can('create_coupons'))

                            <a href="{{ route('priceRule.cart.coupons.new') }}" class="btn btn-primary font-weight-bolder">
                                <i class="la la-plus"></i> New Coupon
                            </a>
                            @endif

                        </div>
                    </div>
                    <div class="card-body">
                        <form name="filter_coupons_list_form" action="{{ route('priceRule.cart.coupons.searchByFilters') }}" method="POST" id="filter_coupons_list_form">
                            @csrf
                            <div class="row align-items-center mb-8">
                                <div class="col-lg-5">
                                    <input type="text" class="form-control form-control-solid datatable-input" id="search_term_filter" name="search_term_filter" placeholder="Search..." />
                                </div>
                                <div class="col-lg-3">
                                    {{-- Additional filters can be placed here --}}
                                </div>
                                <div class="col-lg-4 text-lg-end">
                                    <input type="hidden" name="filter_action" id="filter_action" value="datatable" />
                                    <button type="button" id="filter_coupons_list_filter_btn" class="btn btn-primary me-2">
                                        <i class="la la-search"></i> Search
                                    </button>
                                    <button type="button" id="filter_coupons_list_reset_btn" class="btn btn-light-primary">
                                        <i class="la la-close"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Coupon List Card -->
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">Coupon List</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-center" id="coupon_list_filter_table_area">
                            <table class="table table-row-bordered table-row-dashed gy-5" id="coupon_list_filter_table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Type</th>
                                        <th>Mode</th>
                                        <th>Discount Value</th>
                                        <th>Max Discount Value</th>
                                        <th>Minimum Cart Value</th>
                                        <th>Entity</th>
                                        <th>Customer Eligibility</th>
                                        <th>Order Eligibility</th>
                                        <th>Order Eligible Value</th>
                                        <th>Total Available</th>
                                        <th>Count Per User</th>
                                        <th>Used Count</th>
                                        <th>Updated At</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Coupon Details Modal -->
                <div class="modal fade" id="couponDetailsModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="couponDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="couponDetailsModalLabel"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <div id="couponDetailsModalBody"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-primary font-weight-bold" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Modal -->

            </div>
        </div>
    </div>
@endsection

@section('custom-js-section')
    <script src="{{ asset('build-base/ktmt/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/widgets.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let CouponCustomJsBlocks = function () {

            let initCouponListTable = function (hostUrl, token) {

                let table = $('table#coupon_list_filter_table');
                let targetForm = $('form#filter_coupons_list_form');
                let dataTable = table.DataTable({
                    responsive: true,
                    dom: `<'row'<'col-sm-12'tr>>
                          <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 dataTables_pager'lp>>`,
                    lengthMenu: [25, 50, 100, 200, 500],
                    pageLength: 25,
                    order: [[0, 'asc']],
                    searchDelay: 500,
                    processing: true,
                    language: {
                        processing: '<div class="btn btn-secondary spinner spinner-dark spinner-right">Please Wait</div>',
                    },
                    serverSide: true,
                    ajax: {
                        url: targetForm.attr('action'),
                        type: targetForm.attr('method'),
                        data: function (d) {
                            $.each(targetForm.serializeArray(), function (key, val) {
                                d[val.name] = (val.name === 'filter_action') ? 'datatable' : val.value;
                            });
                            d['columnsDef'] = [
                                'couponId', 'couponCode', 'couponName', 'startDate', 'endDate', 'couponType',
                                'couponMode', 'couponDiscount', 'couponMaxDiscount', 'minCartValue', 'couponEntity',
                                'couponCustomer', 'couponOrder', 'couponOrderValue', 'totalAvailable',
                                'countPerUser', 'usedCount', 'updatedAt', 'isActive', 'actions'
                            ];
                        },
                    },
                    columns: [
                        {data: 'couponId', className: 'text-wrap'},
                        {data: 'couponCode', className: 'text-wrap'},
                        {data: 'couponName', className: 'text-wrap'},
                        {data: 'startDate', className: 'text-wrap'},
                        {data: 'endDate', className: 'text-wrap'},
                        {data: 'couponType', className: 'text-wrap'},
                        {data: 'couponMode', className: 'text-wrap'},
                        {data: 'couponDiscount', className: 'text-wrap'},
                        {data: 'couponMaxDiscount', className: 'text-wrap'},
                        {data: 'minCartValue', className: 'text-wrap'},
                        {data: 'couponEntity', className: 'text-wrap'},
                        {data: 'couponCustomer', className: 'text-wrap'},
                        {data: 'couponOrder', className: 'text-wrap'},
                        {data: 'couponOrderValue', className: 'text-wrap'},
                        {data: 'totalAvailable', className: 'text-wrap'},
                        {data: 'countPerUser', className: 'text-wrap'},
                        {data: 'usedCount', className: 'text-wrap'},
                        {data: 'updatedAt', className: 'text-wrap'},
                        {data: 'isActive', className: 'text-wrap'},
                        {data: 'actions', className: 'text-wrap', responsivePriority: -1},
                    ],
                });

                targetForm.on('submit', function (e) {
                    e.preventDefault();
                    dataTable.draw();
                });

                $('button#filter_coupons_list_filter_btn').on('click', function (e) {
                    e.preventDefault();
                    dataTable.draw();
                });

                $('button#filter_coupons_list_reset_btn').on('click', function (e) {
                    e.preventDefault();
                    $('.datatable-input').val('');
                    $('.datatable-input-multiselect').val('').trigger('change');
                    $('.datatable-input-multiselect-values').val('');
                    dataTable.draw();
                });

                $('#couponDetailsModal').on('shown.bs.modal', function (event) {
                    $('#couponDetailsModalBody').html('<h4 class="text-center mt-5 mb-5">Please wait...</h4>');
                    let button = $(event.relatedTarget);
                    let id = button.data('id');
                    let couponName = button.data('name');
                    let couponField = button.data('field');
                    let couponFieldLabel = button.data('field-label');
                    $('#couponDetailsModalLabel').html('FAQ ' + couponFieldLabel + ' - ' + couponName);
                    $.ajax({
                        url: "{{ route('priceRule.cart.coupons.getDetail') }}",
                        data : {'id': id, 'field': couponField},
                        type: "GET",
                        dataType: 'json'
                    }).done(function(data) {
                        let detailHtml = data.res !== undefined ? data.res : '';
                        $('#couponDetailsModalBody').html(detailHtml);
                    });
                });
            };

            let setCommonDatePicker = function(targetSelector, valueSelector) {
                let leftArrow = '<i class="la la-angle-' + (KTUtil.isRTL() ? 'right' : 'left') + '"></i>';
                let rightArrow = '<i class="la la-angle-' + (KTUtil.isRTL() ? 'left' : 'right') + '"></i>';
                $(targetSelector).datepicker({
                    rtl: KTUtil.isRTL(),
                    todayHighlight: true,
                    todayBtn: "linked",
                    clearBtn: true,
                    autoclose: true,
                    orientation: "bottom left",
                    templates: {
                        leftArrow: leftArrow,
                        rightArrow: rightArrow
                    },
                    format: 'dd-mm-yyyy',
                }).on('changeDate', function(e) {
                    let dObj = new Date(e.date);
                    if (!isNaN(dObj)) {
                        let dayStr = (dObj.getDate() <= 9 ? '0' : '') + dObj.getDate();
                        let monthStr = (dObj.getMonth() + 1 <= 9 ? '0' : '') + (dObj.getMonth() + 1);
                        let year = dObj.getFullYear();
                        $(valueSelector).val(`${year}-${monthStr}-${dayStr}`);
                    }
                });
            };

            let showAlertMessage = function(message, type = 'info', duration = 3000) {
                if (message.trim() !== '') {
                    let divClass = 'alert-dark alert-light-dark';
                    let iconClass = 'flaticon-information';
                    if (type === 'success') {
                        divClass = 'alert-success alert-light-success';
                        iconClass = 'flaticon2-check-mark';
                    } else if (type === 'error') {
                        divClass = 'alert-danger alert-light-danger';
                        iconClass = 'flaticon2-warning';
                    }
                    $("div.custom_alert_trigger_messages_area").html(
                        '<div class="alert alert-custom ' + divClass + ' fade show" role="alert">' +
                        '<div class="alert-icon"><i class="' + iconClass + '"></i></div>' +
                        '<div class="alert-text">' + message + '</div>' +
                        '<div class="alert-close">' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>' +
                        '</div>'
                    );

                    setTimeout(function () {
                        $("div.custom_alert_trigger_messages_area").empty();
                    }, duration);
                }
            };

            return {
                listPage: function (hostUrl, token) {
                    initCouponListTable(hostUrl, token);
                }
            };
        }();

        jQuery(document).ready(function() {
            CouponCustomJsBlocks.listPage('{{ url('/') }}', '{{ csrf_token() }}');
        });

        $(document).on('click', '.delete-coupon', function () {
            let couponId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This coupon will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post({
                        url: "{{ route('priceRule.cart.coupons.destroy') }}",
                        data: {
                            couponId: couponId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            $('#coupon_list_filter_table').DataTable().ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Coupon deleted successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: 'Delete failed. Please try again.'
                            });
                        }
                    });
                }
            });
        });


    </script>
@endsection
