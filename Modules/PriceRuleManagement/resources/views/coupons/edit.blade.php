@extends('base::layouts.mt-main')

@section('content')

    <div class="container">

        <div class="post d-flex flex-column-fluid" id="kt_post">

            <div id="kt_content_container" class="container-xxl">

                <div class="card card-flush">

                    <div class="row border-bottom mb-7">
                        <div class="col-md-12">

                            <div class="card card-custom gutter-b">

                                <div class="card-header flex-wrap py-3">

                                    <div class="card-title">
                                        <h3 class="card-label">
                                            <?= $pageSubTitle; ?>
                                        </h3>
                                    </div>

                                    <div class="card-toolbar">


                                    </div>

                                </div>

                                <form class="form" id="coupon_edit_form" action="{{ route('priceRule.cart.coupons.update', ['couponId' => $givenCouponData->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="card-body">

                                        <div class="row border-bottom mb-7 mt-7 justify-content-center">
                                            <div class="col col-12 col-md-12">

                                                <div class="accordion accordion-solid accordion-toggle-arrow" id="coupon-edit-accordion-main-section">

                                                    <div class="accordion-item" id="coupon-edit-accordion-details-sub-section">
                                                        <h2 class="accordion-header" id="coupon-edit-accordion-details-sub-section-heading">
                                                            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#coupon-edit-accordion-details-sub-section-body" aria-expanded="true" aria-controls="coupon-edit-accordion-details-sub-section-body">
                                                                Coupon Details
                                                            </button>
                                                        </h2>
                                                        <div id="coupon-edit-accordion-details-sub-section-body" class="accordion-collapse collapse show" aria-labelledby="coupon-edit-accordion-details-sub-section-heading" data-bs-parent="#coupon-edit-accordion-main-section">
                                                            <div class="accordion-body"  id="coupon-details-main-area">

                                                                <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
                                                                    <div class="col col-11 col-md-11">

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="form-label" for="name">Name*</label>
                                                                                    <input type="text" name="name" id="name" value="{{ $givenCouponData->name }}" class="form-control" required placeholder="Type Name"/>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="form-label" for="code">Code*</label>
                                                                                    <input type="text" name="code" id="code" value="{{ $givenCouponData->code }}" class="form-control" required placeholder="Type Code"/>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="coupon_type">Coupon Type*</label>
                                                                                    <select class="form-control" id="coupon_type" name="coupon_type" required>
                                                                                        @foreach($availableCouponTypes as $typeKey => $typeEl)
                                                                                            <option value="{{ $typeEl['id'] }}" {{ ($givenCouponData->type_id == $typeEl['id']) ? "selected" : "" }}>{{ $typeEl['name'] }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-3 mb-4">
                                                                                    <label class="form-label" for="start_date">Start Date</label>
                                                                                    <input type="text" class="form-control" id="start_date" name="start_date" value="{{ $givenCouponData->start_date }}"/>
                                                                                    <input type="hidden" name="start_date_value" id="start_date_value" value="{{ $givenCouponData->start_date }}" />
                                                                                </div>

                                                                                <div class="col-md-3 mb-4">
                                                                                    <label class="form-label" for="end_date">End Date</label>
                                                                                    <input type="text" class="form-control" id="end_date" name="end_date" value="{{ $givenCouponData->end_date }}"/>
                                                                                    <input type="hidden" name="end_date_value" id="end_date_value" value="{{ $givenCouponData->end_date }}" />
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="status">Status Active/Inactive</label>
                                                                                    <select class="form-control" id="status" name="status" >
                                                                                        <option value="1" {{ ($givenCouponData->is_active == 1) ? "selected" : "" }}>Active</option>
                                                                                        <option value="0" {{ ($givenCouponData->is_active == 0) ? "selected" : "" }}>Inactive</option>
                                                                                    </select>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-12 mb-4">
                                                                                    <label for="description">Description</label>
                                                                                    <textarea class="form-control" name="description" id="description" rows="3" ><?= $givenCouponData->description ?></textarea>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item" id="coupon-edit-accordion-settings-sub-section">
                                                        <h2 class="accordion-header" id="coupon-edit-accordion-settings-sub-section-heading">
                                                            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#coupon-edit-accordion-settings-sub-section-body" aria-expanded="true" aria-controls="coupon-edit-accordion-settings-sub-section-body">
                                                                Coupon Settings
                                                            </button>
                                                        </h2>
                                                        <div id="coupon-edit-accordion-settings-sub-section-body" class="accordion-collapse collapse" aria-labelledby="coupon-edit-accordion-settings-sub-section-heading" data-bs-parent="#coupon-edit-accordion-main-section">
                                                            <div class="accordion-body" id="coupon-settings-main-area">

                                                                <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
                                                                    <div class="col col-11 col-md-11">

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-3 mb-4">
                                                                                    <label class="" for="coupon_mode">Coupon Mode*</label>
                                                                                    <select class="form-control" id="coupon_mode" name="coupon_mode" required>
                                                                                        @foreach($availableCouponModes as $modeKey => $modeEl)
                                                                                            <option value="{{ $modeEl['id'] }}" {{ ($givenCouponData->mode_id == $modeEl['id']) ? "selected" : "" }}>{{ $modeEl['name'] }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                                <div class="col-md-3 mb-4">
                                                                                    <label class="" for="discount_value">Discount Value*</label>
                                                                                    <input type="text" id="discount_value" name="discount_value" value="{{ $givenCouponData->discount_value }}" class="form-control" required placeholder="Type Discount Value"/>
                                                                                </div>

                                                                                <div class="col-md-3 mb-4">
                                                                                    <label class="" for="has_max_limit">Max Limit</label>
                                                                                    <select class="form-control" id="has_max_limit" name="has_max_limit" >
                                                                                        <option value="0" {{ ($givenCouponData->has_max_limit == 0) ? "selected" : "" }}>No</option>
                                                                                        <option value="1" {{ ($givenCouponData->has_max_limit == 1) ? "selected" : "" }}>Yes</option>
                                                                                    </select>
                                                                                </div>

                                                                                <div class="col-md-3 mb-4" id="max_discount_value_block" style="{{ ($givenCouponData->has_max_limit == 1) ? "" : "display: none;" }}">
                                                                                    <label class="form-label" for="max_discount_value">Max Discount Value</label>
                                                                                    <input type="text" id="max_discount_value" name="max_discount_value" value="{{ $givenCouponData->max_discount_value }}" class="form-control" placeholder="Type Maximum Discount Value"/>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="min_cart_value">Minimum Cart Value</label>
                                                                                    <input type="text" id="min_cart_value" name="min_cart_value" value="{{ $givenCouponData->min_cart_value }}" class="form-control" placeholder="Type Minimum Cart Value"/>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="max_usage_count">Usage Limits</label>
                                                                                    <input type="text" id="max_usage_count" name="max_usage_count" value="{{ $givenCouponData->max_usage_count }}" class="form-control" placeholder="Type Usage Limit Count"/>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="form-label" for="max_count_per_user">Per User Limit</label>
                                                                                    <input type="text" id="max_count_per_user" name="max_count_per_user" value="{{ $givenCouponData->max_count_per_user }}" class="form-control" placeholder="Type Per User Limit count"/>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="order_eligibility">Order Condition</label>
                                                                                    <select class="form-control" id="order_eligibility" name="order_eligibility">
                                                                                        @foreach($orderEligibilityList as $eligibleKey => $eligibleEl)
                                                                                            <option value="{{ $eligibleKey }}" {{ ($givenCouponData->order_eligibility == $eligibleKey) ? "selected" : "" }}>{{ $eligibleEl }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4" id="order_condition_value_block" style="{{ ($givenCouponData->order_eligibility == 1) ? "display: none;" : "" }}">
                                                                                    <label class="" for="order_eligibility_value">Order Condition Value</label>
                                                                                    <input type="text" id="order_eligibility_value" name="order_eligibility_value" value="{{ $givenCouponData->order_eligibility_value }}" class="form-control" placeholder="Type Order Condition Value"/>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item" id="coupon-edit-accordion-applications-sub-section">
                                                        <h2 class="accordion-header" id="coupon-edit-accordion-applications-sub-section-heading">
                                                            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#coupon-edit-accordion-applications-sub-section-body" aria-expanded="true" aria-controls="coupon-edit-accordion-applications-sub-section-body">
                                                                Coupon Entities
                                                            </button>
                                                        </h2>
                                                        <div id="coupon-edit-accordion-applications-sub-section-body" class="accordion-collapse collapse" aria-labelledby="coupon-edit-accordion-applications-sub-section-heading" data-bs-parent="#coupon-edit-accordion-main-section">
                                                            <div class="accordion-body" id="coupon-applications-main-area">

                                                                <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
                                                                    <div class="col col-11 col-md-11">

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <?php $selectedApplicationCode = ''; ?>
                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="entity_id">Apply To*</label>
                                                                                    <select class="form-control" id="entity_id" name="entity_id" required>
                                                                                        @foreach($availableCouponEntities as $applyKey => $applyEl)
                                                                                                <?php
                                                                                                if ($givenCouponData->application_id == $applyEl['id']) {
                                                                                                    $selectedApplicationCode = $applyEl['code'];
                                                                                                }
                                                                                                ?>
                                                                                            <option value="{{ $applyEl['id'] }}" data-code="{{ $applyEl['code'] }}" data-name="{{ $applyEl['name'] }}" {{ ($givenCouponData->application_id == $applyEl['id']) ? "selected" : "" }}>{{ $applyEl['name'] }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group mb-8" id="coupon_items_block" style="{{ ($selectedApplicationCode == 'all') ? "display: none;" : "" }}">
                                                                            <div class="form-group row">

                                                                                <label class="col-form-label col-lg-3 col-sm-12 text-lg-right" for="coupon_items">Items</label>
                                                                                <div class="col-lg-9 col-xl-6">
                                                                                    <select class="form-control couponItemSearch" name="coupon_items[]" id="coupon_items" multiple style="width: 100%">
                                                                                        @if(!is_null($appliedItems) && is_array($appliedItems) && (count($appliedItems) > 0))
                                                                                            @foreach($appliedItems as $itemEl)
                                                                                                <option value="{{ $itemEl['id'] }}" selected>
                                                                                                    {{ $itemEl['label'] }}
                                                                                                </option>
                                                                                            @endforeach
                                                                                        @endif
                                                                                    </select>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item" id="coupon-edit-accordion-eligibility-sub-section">
                                                        <h2 class="accordion-header" id="coupon-edit-accordion-eligibility-sub-section-heading">
                                                            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#coupon-edit-accordion-eligibility-sub-section-body" aria-expanded="true" aria-controls="coupon-edit-accordion-eligibility-sub-section-body">
                                                                Coupon Eligibility
                                                            </button>
                                                        </h2>
                                                        <div id="coupon-edit-accordion-eligibility-sub-section-body" class="accordion-collapse collapse" aria-labelledby="coupon-edit-accordion-eligibility-sub-section-heading" data-bs-parent="#coupon-edit-accordion-main-section">
                                                            <div class="accordion-body">

                                                                <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
                                                                    <div class="col col-11 col-md-11">

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="customer_eligibility">Eligible Customers</label>
                                                                                    <select class="form-control" id="customer_eligibility" name="customer_eligibility" >
                                                                                        @foreach($customerEligibilityList as $eligibleKey => $eligibleEl)
                                                                                            <option value="{{ $eligibleKey }}" {{ ($givenCouponData->customer_eligibility == $eligibleKey) ? "selected" : "" }}>{{ $eligibleEl }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group mb-8" id="coupon_customers_block" style="{{ ($givenCouponData->customer_eligibility == 0) ? "display: none;" : "" }}">
                                                                            <div class="form-group row">

                                                                                <label class="col-form-label col-lg-3 col-sm-12 text-lg-right" for="coupon_customers">Customers</label>
                                                                                <div class="col-lg-9 col-xl-6">
                                                                                    <select class="form-control couponCustomerSearch" name="coupon_customers[]" id="coupon_customers" multiple style="width: 100%">
                                                                                        @if(!is_null($appliedCustomers) && is_array($appliedCustomers) && (count($appliedCustomers) > 0))
                                                                                            @foreach($appliedCustomers as $itemEl)
                                                                                                <option value="{{ $itemEl['id'] }}" selected>
                                                                                                    {{ $itemEl['label'] }}
                                                                                                </option>
                                                                                            @endforeach
                                                                                        @endif
                                                                                    </select>
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

                                    <div class="card-footer text-right">
                                        <button type="submit" id="edit_coupon_submit_btn" class="btn btn-primary font-weight-bold mr-2">
                                            <i class="la la-save"></i>Update Coupon
                                        </button>
                                        <button type="button" id="edit_coupon_cancel_btn" class="btn btn-light-primary font-weight-bold">Cancel</button>
                                    </div>

                                </form>

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

        let CouponCustomJsBlocks = function () {

            let initEditPageActions = function (hostUrl, token) {

                $('#start_date').flatpickr({
                    enableTime: false,
                    altInput: true,
                    altFormat: "d/m/Y",
                    dateFormat: "Y-m-d",
                    onChange: function(selectedDates, dateStr, instance) {
                        let dObj = new Date(dateStr);
                        let dateString = '';
                        if (!isNaN(dObj)) {
                            let dayValue = dObj.getDate();
                            let dayStr = (dayValue <= 9) ? '0' + dayValue : dayValue;
                            let monthValue = dObj.getMonth() + 1;
                            let monthStr = (monthValue <= 9) ? '0' + monthValue : monthValue;
                            let yearString = dObj.getFullYear();
                            dateString = '' + yearString + '-' + monthStr + '-' + dayStr;
                            let dateStringDisplay = '' + dayStr + '-' + monthStr + '-' + yearString;
                        }
                        $('#start_date_value').val(dateString);
                    },
                });

                $('#end_date').flatpickr({
                    enableTime: false,
                    altInput: true,
                    altFormat: "d/m/Y",
                    dateFormat: "Y-m-d",
                    onChange: function(selectedDates, dateStr, instance) {
                        let dObj = new Date(dateStr);
                        let dateString = '';
                        if (!isNaN(dObj)) {
                            let dayValue = dObj.getDate();
                            let dayStr = (dayValue <= 9) ? '0' + dayValue : dayValue;
                            let monthValue = dObj.getMonth() + 1;
                            let monthStr = (monthValue <= 9) ? '0' + monthValue : monthValue;
                            let yearString = dObj.getFullYear();
                            dateString = '' + yearString + '-' + monthStr + '-' + dayStr;
                            let dateStringDisplay = '' + dayStr + '-' + monthStr + '-' + yearString;
                        }
                        $('#end_date_value').val(dateString);
                    },
                });

                $("select.couponItemSearch").select2({
                    multiple: true,
                    placeholder: "Search Items",
                    minimumInputLength: 2,
                    minimumResultsForSearch: 20,
                    ajax: {
                        url: "{{ route('priceRule.cart.coupons.searchItems') }}",
                        dataType: 'json',
                        type: "get",
                        data: function (params) {
                            return {
                                term: params.term,
                                applyId: $('#entity_id').val()
                            };
                        },
                        processResults: function (data) {
                            let result = data.items
                            let items = []
                            $.map(result, function (item) {
                                items.push({
                                    text: item.itemLabel,
                                    id: item.itemId,
                                });
                            });
                            return {
                                results : items
                            };
                        }

                    }
                });

                $("select.couponCustomerSearch").select2({
                    multiple: true,
                    placeholder: "Search Customers",
                    minimumInputLength: 2,
                    minimumResultsForSearch: 20,
                    ajax: {
                        url: "{{ route('priceRule.cart.coupons.searchCustomers') }}",
                        dataType: 'json',
                        type: "get",
                        data: function (params) {
                            return {
                                term: params.term
                            };
                        },
                        processResults: function (data) {
                            let result = data.items
                            let items = []
                            $.map(result, function (item) {
                                items.push({
                                    text: item.customerName,
                                    id: item.customerId,
                                });
                            });
                            return {
                                results : items
                            };
                        }

                    }
                });

                $('select#has_max_limit').on('change', function (e) {
                    if ($(this).val() === '1') {
                        $('#max_discount_value_block').show().find('input').attr('required', true);
                    } else if ($(this).val() === '0') {
                        $('#max_discount_value_block').hide().find('input').attr('required', false);
                    }
                });

                $('select#order_eligibility').on('change', function (e) {
                    if ($(this).val() === '1') {
                        $('#order_condition_value_block').hide().find('input').attr('required', false);
                    } else {
                        $('#order_condition_value_block').show().find('input').attr('required', true);
                    }
                });

                $('select#entity_id').on('change', function (e) {
                    let appCode = $(this).find(':selected').data('code');
                    $('select.couponItemSearch').val(null).trigger('change');
                    if (appCode === 'all') {
                        $('#coupon_items_block').hide();
                    } else {
                        $('#coupon_items_block').show();
                    }
                });

                $('select#customer_eligibility').on('change', function (e) {
                    $('select.couponCustomerSearch').val(null).trigger('change');
                    if ($(this).val() === '1') {
                        $('#coupon_customers_block').show();
                    } else if ($(this).val() === '0') {
                        $('#coupon_customers_block').hide();
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

                    setTimeout(function () {
                        $("div.custom_alert_trigger_messages_area").empty();
                    }, duration);

                }
            };

            return {
                editPage: function (hostUrl, token) {
                    initEditPageActions(hostUrl, token);
                }
            };

        } ();

        jQuery(document).ready(function() {
            CouponCustomJsBlocks.editPage('{{ url('/') }}', '{{ csrf_token() }}');
        });

    </script>

@endsection
