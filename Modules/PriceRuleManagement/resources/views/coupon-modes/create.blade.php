@extends('base::layouts.mt-main')

@section('content')

    <div class="container">

        <div class="post d-flex flex-column-fluid" id="kt_post">

            <div id="kt_content_container" class="container-xxl">

                <div class="card card-flush">

                    <div class="row">
                        <div class="col-md-12">

                            <div class="card card-custom">

                                <div class="card-header flex-wrap border-0 pt-6 pb-0">
                                    <div class="card-title">
                                        <h3 class="card-label">Create Coupon Mode</h3>
                                    </div>
{{--                                    <div class="card-toolbar">--}}
{{--                                        <a href="{{ route('priceRule.cart.couponModes.index') }}" class="btn btn-light-primary font-weight-bolder">--}}
{{--                                            <span class="svg-icon svg-icon-md">--}}
{{--                                                <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">--}}
{{--                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">--}}
{{--                                                        <rect x="0" y="0" width="24" height="24"/>--}}
{{--                                                        <path d="M14.2928932,16.7071068 C13.9023689,17.0976311 13.9023689,17.7291864 14.2928932,18.1197107 C14.6834175,18.5102349 15.3149728,18.5102349 15.7054971,18.1197107 L21.4142136,12.4142136 C21.7262722,12.102155 21.7262722,11.5857864 21.4142136,11.2737278 L15.7054971,5.56494174 C15.3149728,5.17441749 14.6834175,5.17441749 14.2928932,5.56494174 C13.9023689,5.95546603 13.9023689,6.58702128 14.2928932,6.97754557 L18.7573593,11.4419116 L14.2928932,15.9063777 C13.9023689,16.296902 13.9023689,16.9284572 14.2928932,16.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.5"/>--}}
{{--                                                        <path d="M3.70710678,15.7071068 C3.31658249,16.0976311 3.31658249,16.7291864 3.70710678,17.1197107 C4.09763107,17.5102349 4.72918631,17.5102349 5.11971061,17.1197107 L10.8284271,11.4142136 C11.1404858,11.102155 11.1404858,10.5857864 10.8284271,10.2737278 L5.11971061,4.56494174 C4.72918631,4.17441749 4.09763107,4.17441749 3.70710678,4.56494174 C3.31658249,4.95546603 3.31658249,5.58702128 3.70710678,5.97754557 L8.17157288,10.4419116 L3.70710678,14.9063777 C3.31658249,15.296902 3.31658249,15.9284572 3.70710678,15.7071068 Z" fill="#000000" fill-rule="nonzero"/>--}}
{{--                                                    </g>--}}
{{--                                                </svg>--}}
{{--                                            </span>--}}
{{--                                            Back--}}
{{--                                        </a>--}}
{{--                                    </div>--}}
                                </div>

                                <div class="card-body">

                                    <form id="create_coupon_mode_form" method="POST" action="{{ route('priceRule.cart.couponModes.store') }}">
                                        @csrf

                                        <div class="form-group">
                                            <label for="code">Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="code" name="code" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="name">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="sort_order">Sort Order <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="sort_order" name="sort_order" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="is_active">Status</label>
                                            <select class="form-control" id="is_active" name="is_active">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn btn-primary mr-2">Save</button>
                                        <a href="{{ route('priceRule.cart.couponModes.index') }}" class="btn btn-secondary">Cancel</a>

                                    </form>

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

    <script>
        $(document).ready(function() {

            $('#create_coupon_mode_form').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            showAlertMessage(response.success, 'success'); //Use response.success not static message.
                            window.location.href = '{{ route('priceRule.cart.couponModes.index') }}';  // Redirect back to index
                        } else {
                            showAlertMessage('Error creating coupon mode.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'An error occurred: ';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += value[0] + '<br>';
                            });
                        } else {
                            errorMessage += error;
                        }
                        showAlertMessage(errorMessage, 'error');

                    }
                });
            });

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
        });
    </script>

@endsection

