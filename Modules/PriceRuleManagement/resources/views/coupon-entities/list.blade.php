@extends('base::layouts.mt-main')

@section('content')

    <div class="post d-flex flex-column-fluid" id="kt_post">

        <div id="kt_content_container" class="container-xxl">

            <div class="card card-flush">

                <div class="row border-bottom mb-7">
                    <div class="col-md-12">

                        <div class="card card-custom">

                            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                                <div class="card-title">
                                    <h3 class="card-label">Coupon Entity Filter</h3>
                                </div>
                                <div class="card-toolbar">

                                    <form class="row" method="POST" action="{{ route('priceRule.cart.couponEntities.import') }}" enctype="multipart/form-data" name="coupon_entity_import_form" id="coupon_entity_import_form">
                                        @csrf

                                        {{-- <div class="col-md-7 mg-t-10 mg-md-t-0">
                                            <div class="custom-file">
                                                <input type="file" id="coupon_entity_import_file" name="coupon_entity_import_file" accept="text/csv" class="form-control custom-file-input" placeholder="Select CSV file" required>
                                                <label class="custom-file-label" style="padding: 0;" for="coupon_entity_import_file">Choose CSV file</label>
                                            </div>
                                        </div>

                                        <div class="col-auto mg-t-10 mg-md-t-0">
                                            <button type="submit" name="importSubmit" value="1" class="btn btn-warning"><i class="fas fa-file-import font-size-16 align-middle me-2"></i> CSV Import</button>
                                        </div> --}}

                                    </form>

                                    {{-- <a href="{{ $serviceHelper->getFileUrl('uploads/csv/samples/coupon-entities-sample-data.csv') }}" download class="btn btn-info" style="margin-right: 5px;margin-left: 5px;">
                                        <i class="fas fa-file-csv"></i> Sample CSV
                                    </a>

                                    <a href="{{ route('priceRule.cart.couponEntities.export') }}" class="btn btn-info" style="margin-right: 5px;margin-left: 5px;">
                                        <i class="fas fa-file-export"></i> CSV Export
                                    </a> --}}

                                </div>
                            </div>

                            <div class="card-body">

                                <div class="row">
                                    <div class="col col-12">
                                        <form name="filter_coupon_entities_list_form" action="{{ route('priceRule.cart.couponEntities.searchByFilters') }}" method="POST" id="filter_coupon_entities_list_form">
                                            @csrf
                                            <div class="form-group mb-8">
                                                <div class="form-group row">
                                                    <div class="col-lg-5">
                                                        <input type="text" class="form-control datatable-input" id="search_term_filter" name="search_term_filter" placeholder="Search..." />
                                                    </div>
                                                    <div class="col-lg-3">

                                                    </div>
                                                    <div class="col-4 text-right">
                                                        <input type="hidden" name="filter_action" id="filter_action" value="datatable" />
                                                        <button type="button" id="filter_coupon_entities_list_filter_btn" class="btn btn-primary btn-lg mr-2">
                                                            <span><i class="la la-search"></i>Search</span>
                                                        </button>
                                                        <button type="button" id="filter_coupon_entities_list_reset_btn" class="btn btn-primary btn-lg mr-2">
                                                            <span><i class="la la-close"></i>Reset</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="card card-custom">

                            <div class="row border-bottom mb-7">
                                <div class="col-md-12">

                                    <div class="card card-custom gutter-b">

                                        <div class="card-header">
                                            <div class="card-title">
                                                <h3 class="card-label">Coupon Entity List</h3>
                                            </div>
                                        </div>

                                        <div class="card-body">

                                            <div class="row">
                                                <div class="col col-12">

                                                    <div class="table-responsive text-center" id="coupon_entity_list_filter_table_area">
                                                        <table class="table table-bordered" id="coupon_entity_list_filter_table">

                                                            <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Code</th>
                                                                <th>Name</th>
                                                                <th>Sort Order</th>
                                                                <th>Updated By</th>
                                                                <th>Updated At</th>
                                                                <th>Active</th>
                                                            </tr>
                                                            </thead>

                                                        </table>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

@endsection

@section('custom-js-section')

    <script src="{{ asset('build-base/ktmt/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/widgets.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>

    <script>

        let CouponEntityCustomJsBlocks = function () {

            let initCouponEntityListTable = function (hostUrl) {

                let table = $('table#coupon_entity_list_filter_table');
                let targetForm = $('form#filter_coupon_entities_list_form');
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
                                if (val.name === 'filter_action') {
                                    d[val.name] = 'datatable';
                                } else {
                                    d[val.name] = val.value;
                                }
                            });
                            d['columnsDef'] = [
                                'appId', 'appCode', 'appName', 'appSortOrder', 'updatedBy', 'updatedAt', 'isActive'
                            ];
                        },
                    },
                    columns: [
                        {data: 'appId', className: 'text-wrap'},
                        {data: 'appCode', className: 'text-wrap'},
                        {data: 'appName', className: 'text-wrap'},
                        {data: 'appSortOrder', className: 'text-wrap'},
                        {data: 'updatedBy', className: 'text-wrap'},
                        {data: 'updatedAt', className: 'text-wrap'},
                        {data: 'isActive', className: 'text-wrap', responsivePriority: -1},
                    ],
                    columnDefs: [],
                });

                targetForm.on('submit', function (e) {
                    e.preventDefault();
                    dataTable.table().draw();
                });

                $('button#filter_coupon_entities_list_filter_btn').on('click', function (e) {
                    e.preventDefault();
                    dataTable.table().draw();
                });

                $('button#filter_coupon_entities_list_reset_btn').on('click', function (e) {
                    e.preventDefault();
                    $('.datatable-input').each(function () {
                        $(this).val('');
                    });
                    $('.datatable-input-multiselect').each(function() {
                        $(this).val('').trigger('change');
                    });
                    $('.datatable-input-multiselect-values').each(function() {
                        $(this).val('');
                    });
                    dataTable.table().draw();
                });

            };

            let showAlertMessage = function(message, type = 'info') {
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
                    $("div.custom_alert_trigger_messages_area")
                        .html('<div class="alert alert-custom ' + divClass + ' fade show" role="alert">' +
                            '<div class="alert-icon"><i class="' + iconClass + '"></i></div>' +
                            '<div class="alert-text">' + message + '</div>' +
                            '<div class="alert-close">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true"><i class="ki ki-close"></i></span>' +
                            '</button>' +
                            '</div>' +
                            '</div>');
                }
            };

            return {
                listPage: function (hostUrl) {
                    initCouponEntityListTable(hostUrl);
                }
            };

        } ();

        jQuery(document).ready(function() {
            CouponEntityCustomJsBlocks.listPage('{{ url('/') }}');
        });

    </script>

@endsection
