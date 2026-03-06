@extends('webconfigurationmanagement::configurations.core-config')
@section('container')
    <div id="customer-support-form" class="form-container" style="{{ request('form') === 'customer-support-form' ? 'display: block;' : 'display: none;' }}">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="row h-100">
                <div class="col-12">
                    <form method="POST" action="{{ route('core.config.update') }}" class="h-100">
                        @csrf
                        <div class="card h-100">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h4 class="header-title mb-0">Customer Support</h4>
                                    <button class="btn btn-info btn-sm d-block" type="submit">Save</button>
                                </div>
                                <p class="sub-header">
                                    View and update your Customer Support, its API key and Application ID
                                </p>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label" for="customer_support">Customer Support</label>
                                            <select class="form-select" id="customer_support" name="customer_support" data-control="select2" data-hide-search="true">
{{--                                                <option value="zen_desk" {{ isset($preference) && getConfigValue($preference, 'customer_support') == 'zen_desk' ? 'selected' : '' }}>--}}
{{--                                                    Zen Desk--}}
{{--                                                </option>--}}
                                            </select>
                                            @if ($errors->has('customer_support'))
                                                <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('customer_support') }}</strong>
                                        </span>
                                            @endif
                                        </div>
                                        <div class="form-group mt-5 mb-0">
                                            <label class="form-label" for="customer_support_key">API Key</label>
{{--                                            <input type="password" name="customer_support_key" id="customer_support_key"--}}
{{--                                                   placeholder="Please enter key" class="form-control"--}}
{{--                                                   value="{{ old('customer_support_key', getConfigValue($preference,'customer_support_key') ?? '') }}">--}}
                                            @if ($errors->has('customer_support_key'))
                                                <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('customer_support_key') }}</strong>
                                        </span>
                                            @endif
                                        </div>
                                        <div class="form-group mt-5 mb-0">
                                            <label class="form-label" for="customer_support_application_id">Application ID</label>
{{--                                            <input type="password" name="customer_support_application_id" id="customer_support_application_id"--}}
{{--                                                   placeholder="Please enter application ID" class="form-control"--}}
{{--                                                   value="{{ old('customer_support_application_id', getConfigValue($preference,'customer_support_application_id') ?? '') }}">--}}
                                            @if ($errors->has('customer_support_application_id'))
                                                <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('customer_support_application_id') }}</strong>
                                        </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Customer support form fields here -->
    </div>
    @endsection
