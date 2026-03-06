@extends('base::layouts.mt-main')

@section('content')

    <div class="container">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

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

                                <form class="form" id="payment_method_edit_form" action="{{ route('admin.paymentMethods.update', ['methodId' => $givenPaymentMethodData->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="card-body">

                                        <div class="row border-bottom mb-7 mt-7 justify-content-center">
                                            <div class="col col-12 col-md-12">

                                                <div class="accordion accordion-solid accordion-toggle-arrow" id="payment-method-edit-accordion-main-section">

                                                    <div class="accordion-item" id="payment-method-edit-accordion-details-sub-section">
                                                        <h2 class="accordion-header" id="payment-method-edit-accordion-details-sub-section-heading">
                                                            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#payment-method-edit-accordion-details-sub-section-body" aria-expanded="true" aria-controls="payment-method-edit-accordion-details-sub-section-body">
                                                                Payment Method Details
                                                            </button>
                                                        </h2>
                                                        <div id="payment-method-edit-accordion-details-sub-section-body" class="accordion-collapse collapse show" aria-labelledby="payment-method-edit-accordion-details-sub-section-heading" data-bs-parent="#payment-method-edit-accordion-main-section">
                                                            <div class="accordion-body"  id="payment-method-details-main-area">

                                                                <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
                                                                    <div class="col col-11 col-md-11">

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="form-label" for="code">Code</label>
                                                                                    <input type="text" name="code" id="code" value="{{ $givenPaymentMethodData->code }}" class="form-control" readonly placeholder="Code"/>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="form-label" for="name">Name*</label>
                                                                                    <input type="text" name="name" id="name" value="{{ $givenPaymentMethodData->name }}" class="form-control" required placeholder="Name"/>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="form-label" for="name">Sort Order*</label>
                                                                                    <input type="text" name="sort_order" id="sort_order" value="{{ $givenPaymentMethodData->sort_order }}" class="form-control" required placeholder="Sort Order"/>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-12 mb-4">
                                                                                    <label for="description">Description</label>
                                                                                    <textarea class="form-control" name="description" id="description" rows="3" ><?= $givenPaymentMethodData->description ?></textarea>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="test_mode">Test Mode*</label>
                                                                                    <select class="form-control" id="test_mode" name="test_mode" required>
                                                                                        @foreach($testModeList as $typeKey => $typeEl)
                                                                                            <option value="{{ $typeKey }}" {{ ($givenPaymentMethodData->test_mode == $typeKey) ? "selected" : "" }}>{{ $typeEl }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="is_online">Online Status*</label>
                                                                                    <select class="form-control" id="is_online" name="is_online" required>
                                                                                        @foreach($onlineStatusList as $typeKey => $typeEl)
                                                                                            <option value="{{ $typeKey }}" {{ ($givenPaymentMethodData->is_online == $typeKey) ? "selected" : "" }}>{{ $typeEl }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                                <div class="col-md-4 mb-4">
                                                                                    <label class="" for="is_active">Active Status*</label>
                                                                                    <select class="form-control" id="is_active" name="is_active" required>
                                                                                        @foreach($activeStatusList as $typeKey => $typeEl)
                                                                                            <option value="{{ $typeKey }}" {{ ($givenPaymentMethodData->is_active == $typeKey) ? "selected" : "" }}>{{ $typeEl }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item" id="payment-method-edit-accordion-settings-sub-section">
                                                        <h2 class="accordion-header" id="payment-method-edit-accordion-settings-sub-section-heading">
                                                            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#payment-method-edit-accordion-settings-sub-section-body" aria-expanded="true" aria-controls="payment-method-edit-accordion-settings-sub-section-body">
                                                                Payment Method Credentials
                                                            </button>
                                                        </h2>
                                                        <div id="payment-method-edit-accordion-settings-sub-section-body" class="accordion-collapse collapse" aria-labelledby="payment-method-edit-accordion-settings-sub-section-heading" data-bs-parent="#payment-method-edit-accordion-main-section">
                                                            <div class="accordion-body" id="payment-method-settings-main-area">

                                                                <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
                                                                    <div class="col col-11 col-md-11">

                                                                        <div class="form-group mb-8">
                                                                            <div class="form-group row">

                                                                                @foreach($credentialValueList as $modeKey => $modeEl)
                                                                                    <div class="col-md-3 mb-4">
                                                                                        <label class="" for="{{ $modeEl['name'] }}">{{ $modeEl['label'] }}</label>
                                                                                        <input type="text" id="{{ $modeEl['name'] }}" name="credentials[{{ $modeEl['name'] }}]" value="{{ $modeEl['value'] }}" class="form-control" placeholder="Type {{ $modeEl['label'] }}"/>
                                                                                    </div>
                                                                                @endforeach

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
                                        <button type="submit" id="edit_payment_method_submit_btn" class="btn btn-primary font-weight-bold mr-2">
                                            <i class="la la-save"></i>Update Payment Method
                                        </button>
                                        <button type="button" id="edit_payment_method_cancel_btn" class="btn btn-light-primary font-weight-bold">Cancel</button>
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

        let PaymentMethodsCustomJsBlocks = function () {

            let initEditPageActions = function (hostUrl, token) {

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
            PaymentMethodsCustomJsBlocks.editPage('{{ url('/') }}', '{{ csrf_token() }}');
        });

    </script>

@endsection
