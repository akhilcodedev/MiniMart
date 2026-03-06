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

                                <form class="form" id="coupon_add_form" action="{{ route('priceRule.cart.coupons.save') }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="card-body">

                                        <div class="row border-bottom mb-7 mt-7 justify-content-center">
                                            <div class="col col-12 col-md-12">

                                                <div class="accordion accordion-solid accordion-toggle-arrow" id="coupon-add-accordion-main-section">

                                                    <div class="accordion accordion-icon-toggle" id="coupon-add-accordion-main-section"> <!-- Added accordion-icon-toggle for Metronic style -->
                                                        <div class="accordion-item mb-5"> <!-- Added mb-5 for spacing between accordion items if there are multiple -->
                                                            <h2 class="accordion-header" id="coupon-add-accordion-details-sub-section-heading">
                                                                <button class="accordion-button fs-4 fw-semibold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#coupon-add-accordion-details-sub-section-body" aria-expanded="false" aria-controls="coupon-add-accordion-details-sub-section-body">
                                                                    Coupon Details
                                                                </button> <!-- Note: Changed default state to collapsed 'aria-expanded="false"' and removed 'show' class from body if you want it closed by default -->
                                                            </h2>
                                                            <div id="coupon-add-accordion-details-sub-section-body" class="accordion-collapse collapse" aria-labelledby="coupon-add-accordion-details-sub-section-heading" data-bs-parent="#coupon-add-accordion-main-section">
                                                                 <!-- Use p-9 for standard Metronic padding inside content areas -->
                                                                <div class="accordion-body p-9" id="coupon-details-main-area">

                                                                    <!--begin::Form row-->
                                                                    <div class="row mb-8">
                                                                        <!--begin::Col-->
                                                                        <div class="col-md-4 fv-row mb-4"> <!-- fv-row is often used for FormValidation integration -->
                                                                            <!--begin::Label-->
                                                                            <label class="required form-label" for="name">Name</label>
                                                                            <!--end::Label-->
                                                                            <!--begin::Input-->
                                                                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control form-control-solid" required placeholder="Type Name"/>
                                                                            <!--end::Input-->
                                                                        </div>
                                                                        <!--end::Col-->

                                                                        <!--begin::Col-->
                                                                        <div class="col-md-4 fv-row mb-4">
                                                                            <!--begin::Label-->
                                                                            <label class="required form-label" for="code">Code</label>
                                                                            <!--end::Label-->
                                                                            <!--begin::Input-->
                                                                            <input type="text" id="code" name="code" value="{{ old('code') }}" class="form-control form-control-solid" required placeholder="Type Code"/>
                                                                            <!--end::Input-->
                                                                        </div>
                                                                        <!--end::Col-->

                                                                        <!--begin::Col-->
                                                                        <div class="col-md-4 fv-row mb-4">
                                                                            <!--begin::Label-->
                                                                            <label class="required form-label" for="coupon_type">Coupon Type</label>
                                                                            <!--end::Label-->
                                                                            <!--begin::Select-->
                                                                            <select class="form-select form-select-solid" id="coupon_type" name="coupon_type" data-control="select2" data-hide-search="true" data-placeholder="Select type" required> <!-- Added Metronic Select2 attributes -->
                                                                                <option></option> <!-- Placeholder option for Select2 -->
                                                                                @foreach($availableCouponTypes as $typeKey => $typeEl)
                                                                                    <option value="{{ $typeEl['id'] }}" {{ old('coupon_type') == $typeEl['id'] ? 'selected' : '' }}>{{ $typeEl['name'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            <!--end::Select-->
                                                                        </div>
                                                                        <!--end::Col-->
                                                                    </div>
                                                                    <!--end::Form row-->

                                                                    <!--begin::Form row-->
                                                                    <div class="row mb-8">
                                                                        <!--begin::Col-->
                                                                        <div class="col-md-3 fv-row mb-4">
                                                                            <!--begin::Label-->
                                                                            <label class="form-label" for="start_date">Start Date</label>
                                                                            <!--end::Label-->
                                                                            <!--begin::Input-->
                                                                             <!-- Add kt_datepicker class for JS initialization -->
                                                                            <input type="text" class="form-control form-control-solid kt_datepicker" id="start_date" name="start_date_value" value="{{ old('start_date_value', date('Y-m-d')) }}" readonly placeholder="Select Start Date"/>
                                                                            <!-- Hidden input might not be needed if JS directly sets the value or if backend parses the display format -->
                                                                            <!-- <input type="hidden" name="start_date_value" id="start_date_value" value="{{ old('start_date_value', date('Y-m-d')) }}" /> -->
                                                                            <!--end::Input-->
                                                                        </div>
                                                                        <!--end::Col-->

                                                                        <!--begin::Col-->
                                                                        <div class="col-md-3 fv-row mb-4">
                                                                            <!--begin::Label-->
                                                                            <label class="form-label" for="end_date">End Date</label>
                                                                            <!--end::Label-->
                                                                            <!--begin::Input-->
                                                                             <!-- Add kt_datepicker class for JS initialization -->
                                                                            <input type="text" class="form-control form-control-solid kt_datepicker" id="end_date" name="end_date_value" value="{{ old('end_date_value', date('Y-m-d')) }}" readonly placeholder="Select End Date"/>
                                                                            <!-- <input type="hidden" name="end_date_value" id="end_date_value" value="{{ old('end_date_value', date('Y-m-d')) }}" /> -->
                                                                            <!--end::Input-->
                                                                        </div>
                                                                        <!--end::Col-->

                                                                        <!--begin::Col-->
                                                                        <div class="col-md-4 fv-row mb-4">
                                                                            <!--begin::Label-->
                                                                            <label class="form-label" for="status">Status</label> <!-- Removed "Active/Inactive" from label, placeholder is better -->
                                                                            <!--end::Label-->
                                                                            <!--begin::Select-->
                                                                            <select class="form-select form-select-solid" id="status" name="status" data-control="select2" data-hide-search="true" data-placeholder="Select status">
                                                                                <option></option> <!-- Placeholder option for Select2 -->
                                                                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option> <!-- Default to Active -->
                                                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                                                            </select>
                                                                            <!--end::Select-->
                                                                        </div>
                                                                        <!--end::Col-->
                                                                    </div>
                                                                    <!--end::Form row-->

                                                                    <!--begin::Form row-->
                                                                    <div class="row mb-8">
                                                                        <!--begin::Col-->
                                                                        <div class="col-md-12 fv-row mb-4">
                                                                            <!--begin::Label-->
                                                                            <label class="form-label" for="description">Description</label>
                                                                            <!--end::Label-->
                                                                            <!--begin::Textarea-->
                                                                            <textarea class="form-control form-control-solid" name="description" id="description" rows="3" placeholder="Enter coupon description">{{ old('description') }}</textarea>
                                                                            <!--end::Textarea-->
                                                                        </div>
                                                                        <!--end::Col-->
                                                                    </div>
                                                                    <!--end::Form row-->

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item mb-5" id="coupon-add-accordion-settings-sub-section">
                                                        <h2 class="accordion-header" id="coupon-add-accordion-settings-sub-section-heading">
                                                            <button class="accordion-button fs-4 fw-semibold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#coupon-add-accordion-settings-sub-section-body" aria-expanded="false" aria-controls="coupon-add-accordion-settings-sub-section-body">
                                                                Coupon Settings
                                                            </button> <!-- Note: Default state is collapsed -->
                                                        </h2>
                                                        <div id="coupon-add-accordion-settings-sub-section-body" class="accordion-collapse collapse" aria-labelledby="coupon-add-accordion-settings-sub-section-heading" data-bs-parent="#coupon-add-accordion-main-section">
                                                            <!-- Use p-9 for standard Metronic padding -->
                                                            <div class="accordion-body p-9" id="coupon-settings-main-area">

                                                                <!--begin::Form row-->
                                                                <div class="row mb-8">
                                                                    <!--begin::Col-->
                                                                    <div class="col-md-3 fv-row mb-4">
                                                                        <!--begin::Label-->
                                                                        <label class="required form-label" for="coupon_mode">Coupon Mode</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Select-->
                                                                        <select class="form-select form-select-solid" id="coupon_mode" name="coupon_mode" data-control="select2" data-hide-search="true" data-placeholder="Select mode" required>
                                                                            <option></option> <!-- Placeholder for Select2 -->
                                                                            @foreach($availableCouponModes as $modeKey => $modeEl)
                                                                                <option value="{{ $modeEl['id'] }}" {{ old('coupon_mode') == $modeEl['id'] ? 'selected' : '' }}>{{ $modeEl['name'] }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <!--end::Select-->
                                                                    </div>
                                                                    <!--end::Col-->

                                                                    <!--begin::Col-->
                                                                    <div class="col-md-3 fv-row mb-4">
                                                                        <!--begin::Label-->
                                                                        <label class="required form-label" for="discount_value">Discount Value</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Input-->
                                                                        <input type="text" id="discount_value" name="discount_value" value="{{ old('discount_value') }}" class="form-control form-control-solid" required placeholder="Enter value"/>
                                                                        <!--end::Input-->
                                                                    </div>
                                                                    <!--end::Col-->

                                                                    <!--begin::Col-->
                                                                    <div class="col-md-3 fv-row mb-4">
                                                                        <!--begin::Label-->
                                                                        <label class="form-label" for="has_max_limit">Use Max Limit?</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Select-->
                                                                        <select class="form-select form-select-solid" id="has_max_limit" name="has_max_limit" data-control="select2" data-hide-search="true" data-placeholder="Select option">
                                                                             <option></option> <!-- Placeholder for Select2 -->
                                                                            <option value="0" {{ old('has_max_limit', '0') == '0' ? 'selected' : '' }}>No</option> <!-- Default to No -->
                                                                            <option value="1" {{ old('has_max_limit') == '1' ? 'selected' : '' }}>Yes</option>
                                                                        </select>
                                                                        <!--end::Select-->
                                                                    </div>
                                                                    <!--end::Col-->

                                                                    <!--begin::Col (Conditional)-->
                                                                    <div class="col-md-3 fv-row mb-4" id="max_discount_value_block" style="{{ old('has_max_limit') == '1' ? '' : 'display: none;' }}"> <!-- Add old() check for initial display -->
                                                                        <!--begin::Label-->
                                                                        <label class="form-label" for="max_discount_value">Max Discount Value</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Input-->
                                                                        <input type="text" id="max_discount_value" name="max_discount_value" value="{{ old('max_discount_value') }}" class="form-control form-control-solid" placeholder="Enter max value"/>
                                                                        <!--end::Input-->
                                                                    </div>
                                                                    <!--end::Col-->
                                                                </div>
                                                                <!--end::Form row-->

                                                                <!--begin::Form row-->
                                                                <div class="row mb-8">
                                                                    <!--begin::Col-->
                                                                    <div class="col-md-4 fv-row mb-4">
                                                                        <!--begin::Label-->
                                                                        <label class="form-label" for="min_cart_value">Minimum Cart Value</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Input-->
                                                                        <input type="text" id="min_cart_value" name="min_cart_value" value="{{ old('min_cart_value') }}" class="form-control form-control-solid" placeholder="e.g., 100.00"/>
                                                                        <!--end::Input-->
                                                                    </div>
                                                                    <!--end::Col-->

                                                                    <!--begin::Col-->
                                                                    <div class="col-md-4 fv-row mb-4">
                                                                        <!--begin::Label-->
                                                                        <label class="form-label" for="max_usage_count">Total Usage Limit</label> <!-- More specific label -->
                                                                        <!--end::Label-->
                                                                        <!--begin::Input-->
                                                                        <input type="number" id="max_usage_count" name="max_usage_count" value="{{ old('max_usage_count') }}" class="form-control form-control-solid" placeholder="Leave empty for unlimited"/>
                                                                        <!--end::Input-->
                                                                    </div>
                                                                    <!--end::Col-->

                                                                    <!--begin::Col-->
                                                                    <div class="col-md-4 fv-row mb-4">
                                                                        <!--begin::Label-->
                                                                        <label class="form-label" for="max_count_per_user">Limit Per User</label> <!-- More specific label -->
                                                                        <!--end::Label-->
                                                                        <!--begin::Input-->
                                                                        <input type="number" id="max_count_per_user" name="max_count_per_user" value="{{ old('max_count_per_user') }}" class="form-control form-control-solid" placeholder="e.g., 1"/>
                                                                        <!--end::Input-->
                                                                    </div>
                                                                    <!--end::Col-->
                                                                </div>
                                                                <!--end::Form row-->

                                                                <!--begin::Form row-->
                                                                <div class="row mb-8">
                                                                    <!--begin::Col-->
                                                                    <div class="col-md-4 fv-row mb-4">
                                                                        <!--begin::Label-->
                                                                        <label class="form-label" for="order_eligibility">Order Condition</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Select-->
                                                                        <select class="form-select form-select-solid" id="order_eligibility" name="order_eligibility" data-control="select2" data-hide-search="true" data-placeholder="Select condition">
                                                                            <option></option> <!-- Placeholder for Select2 -->
                                                                            @foreach($orderEligibilityList as $eligibleKey => $eligibleEl)
                                                                                 <!-- Use old() helper for sticky selection -->
                                                                                <option value="{{ $eligibleKey }}" {{ old('order_eligibility', 1) == $eligibleKey ? "selected" : "" }}>{{ $eligibleEl }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <!--end::Select-->
                                                                    </div>
                                                                    <!--end::Col-->

                                                                    <!--begin::Col (Conditional)-->
                                                                     <!-- Adjust old() check based on which eligibleKey requires the value block -->
                                                                    <div class="col-md-4 fv-row mb-4" id="order_condition_value_block" style="{{ old('order_eligibility', 1) != 1 ? '' : 'display: none;' }}"> <!-- Example: Show if NOT default (1) -->
                                                                        <!--begin::Label-->
                                                                        <label class="form-label" for="order_eligibility_value">Condition Value</label>
                                                                        <!--end::Label-->
                                                                        <!--begin::Input-->
                                                                        <input type="text" id="order_eligibility_value" name="order_eligibility_value" value="{{ old('order_eligibility_value') }}" class="form-control form-control-solid" placeholder="Enter condition value"/>
                                                                        <!--end::Input-->
                                                                    </div>
                                                                    <!--end::Col-->
                                                                </div>
                                                                <!--end::Form row-->

                                                            </div>
                                                            <!--end::Accordion body-->
                                                        </div>
                                                        <!--end::Accordion body-->
                                                    </div>

                                                    <div class="accordion-item" id="coupon-add-accordion-applications-sub-section">
                                                        <h2 class="accordion-header" id="coupon-add-accordion-applications-sub-section-heading">
                                                            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#coupon-add-accordion-applications-sub-section-body"
                                                                aria-expanded="true"
                                                                aria-controls="coupon-add-accordion-applications-sub-section-body">
                                                                Coupon Entities
                                                            </button>
                                                        </h2>

                                                        <div id="coupon-add-accordion-applications-sub-section-body"
                                                            class="accordion-collapse collapse show"
                                                            aria-labelledby="coupon-add-accordion-applications-sub-section-heading"
                                                            data-bs-parent="#coupon-add-accordion-main-section">

                                                            <div class="accordion-body" id="coupon-applications-main-area">

                                                                <div class="row py-8 px-8 py-md-27 px-md-0">
                                                                    <div class="col-md-11">

                                                                        {{-- Apply To Section --}}
                                                                        <div class="mb-8 fv-row">
                                                                            <label class="form-label required" for="entity_id">Apply To</label>
                                                                            <select class="form-select form-select-solid" id="entity_id" name="entity_id" required>
                                                                                @foreach($availableCouponEntities as $applyKey => $applyEl)
                                                                                    <option value="{{ $applyEl['id'] }}"
                                                                                        data-code="{{ $applyEl['code'] }}"
                                                                                        data-name="{{ $applyEl['name'] }}"
                                                                                        {{ ($applyEl['code'] == 'all') ? "selected" : "" }}>
                                                                                        {{ $applyEl['name'] }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>

                                                                        {{-- Coupon Items Block (Initially Hidden) --}}
                                                                        <div class="mb-8 fv-row" id="coupon_items_block" style="display: none;">
                                                                            <label class="form-label" for="coupon_items">Items</label>
                                                                            <select class="form-select form-select-solid couponItemSearch"
                                                                                name="coupon_items[]" id="coupon_items" multiple>
                                                                            </select>
                                                                        </div>

                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="accordion accordion-icon-toggle" id="coupon-add-accordion-eligibility-sub-section">
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header">
                                                                <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse"
                                                                    data-bs-target="#coupon-add-accordion-eligibility-sub-section-body" aria-expanded="true"
                                                                    aria-controls="coupon-add-accordion-eligibility-sub-section-body">
                                                                    <i class="ki-duotone ki-badge fs-2 me-2"></i> Coupon Eligibility
                                                                </button>
                                                            </h2>
                                                            <div id="coupon-add-accordion-eligibility-sub-section-body" class="accordion-collapse collapse show"
                                                                data-bs-parent="#coupon-add-accordion-main-section">
                                                                <div class="accordion-body" id="coupon-eligibility-main-area">

                                                                    <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
                                                                        <div class="col-md-11">
                                                                            <div class="mb-8">
                                                                                <label class="form-label fw-bold" for="customer_eligibility">Eligible Customers</label>
                                                                                <select class="form-select form-select-solid" id="customer_eligibility"
                                                                                    name="customer_eligibility">
                                                                                    @foreach($customerEligibilityList as $eligibleKey => $eligibleEl)
                                                                                    <option value="{{ $eligibleKey }}" {{ ($eligibleKey == 0) ? "selected" : "" }}>
                                                                                        {{ $eligibleEl }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>

                                                                            <div class="mb-8" id="coupon_customers_block" style="display: none;">
                                                                                <label class="form-label fw-bold" for="coupon_customers">Customers</label>
                                                                                <select class="form-select form-select-solid couponCustomerSearch" name="coupon_customers[]"
                                                                                    id="coupon_customers" multiple>
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

                                    <div class="card-footer text-right">
                                        <button type="submit" id="add_coupon_submit_btn" class="btn btn-primary font-weight-bold mr-2">
                                            <i class="la la-save"></i>Save Coupon
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="window.location='{{ route('priceRule.cart.coupons.index') }}'">Cancel</button>

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

            let initNewPageActions = function (hostUrl, token) {

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
                newPage: function (hostUrl, token) {
                    initNewPageActions(hostUrl, token);
                }
            };

        } ();

        jQuery(document).ready(function() {
            CouponCustomJsBlocks.newPage('{{ url('/') }}', '{{ csrf_token() }}');
        });

    </script>

@endsection
