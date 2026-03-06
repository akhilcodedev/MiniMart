@extends('webconfigurationmanagement::configurations.core-config')

@section('container')
<div class="container-fluid px-4">

    <div class="forms">
        <!-- Time Zone Form -->
        <div id="timezone-form" class="form-container" style="{{ request('form') === 'timezone-form' ? 'display: block;' : 'display: none;' }}">
            <div class="col-lg-4 col-xl-4 mb-3">
                <div class="card h-100">
                    <div class="card-body mb-0 h-100 p-6">
                        <form method="POST" class="h-100" action="{{ route('core.config.update') }}">
                            @csrf
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="header-title mb-0">Time Zone Configuration</h4>
                                <input type="hidden" name="send_to" id="send_to" value="customize">
                                <button class="btn btn-info btn-sm d-block" type="submit">Save</button>
                            </div>
                            <p class="sub-header">
                                Select your preferred time zone
                            </p>
                            <div class="d-flex justify-content-end mb-4">
                                <button type="button"
                                    class="btn btn-light-primary btn-sm flex-shrink-0 align-self-center py-3 px-4 fs-7"
                                    data-bs-toggle="modal"
                                    data-bs-target="#showImportModal">Import
                                </button>
                            </div>
                            <div class="row col-spacing">
                                <div class="col-xl-12 mb-2">
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label" for="timezone">Time Zone</label>
                                        <select class="form-select al_box_height" id="timezone" name="timezone" data-control="select2">
                                            @foreach ($timezones as $timezone)
                                            <option value="{{ $timezone->id }}"
                                                {{ (isset($preference) && getConfigValue($preference,
                                                    'timezone') == $timezone->id) ? 'selected' : '' }}>
                                                {{ $timezone->timezone }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>


        <!-- Date, Time & Currency Form -->
        <div id="datetime-form" class="form-container" style="{{ request('form') === 'datetime-form' ? 'display: block;' : 'display: none;' }}">
            <div class="col-lg-4 col-md-6">
                <form method="POST" class="h-100" action="{{route('core.config.update')}}">
                    @csrf
                    <div class="card h-100">
                        <div class="card-body p-6 mb-0 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h4 class="header-title mb-0">Date, Time & Currency</h4>
                                <input type="hidden" name="send_to" id="send_to" value="customize">
                                <button class="btn btn-info btn-sm d-block" type="submit">Save</button>
                            </div>
                            <p class="sub-header">
                                View and update the date, time & currency format.
                            </p>
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group mb-5">
                                        <label class="form-label" for="date_format">Date Format</label>
                                        <select class="form-select al_box_height" id="date_format" name="date_format" data-control="select2" data-hide-search="true">
                                            <!-- Options removed for simplicity -->
                                        </select>
                                        @if($errors->has('date_format'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('date_format') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-5">
                                        <label class="form-label" for="time_format">Time Format</label>
                                        <select class="form-select al_box_height" id="time_format" name="time_format" data-control="select2" data-hide-search="true">
                                            <!-- Options removed for simplicity -->
                                        </select>
                                        @if($errors->has('time_format'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('time_format') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-5">
                                        <label class="form-label" for="time_format">Currency Format (Digits After Decimal)</label>
                                        <select class="form-select al_box_height" id="digit_after_decimal" name="digit_after_decimal" data-control="select2">
                                            <!-- Options removed for simplicity -->
                                        </select>
                                        @if($errors->has('time_format'))
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $errors->first('time_format') }}</strong>
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


        <!-- Languages & Currencies Form -->
        <div id="languages-form" class="form-container" style="{{ request('form') === 'languages-form' ? 'display: block;' : 'display: none;' }}">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-body mb-0 h-100 p-6">
                        <form method="POST" class="h-100" action="{{ route('core.config.update') }}">
                            @csrf
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="header-title mb-0">Languages & Currencies</h4>
                                <input type="hidden" name="send_to" id="send_to" value="customize">
                                <button class="btn btn-info btn-sm d-block" type="submit">Save</button>
                            </div>
                            <p class="sub-header">
                                Define and update the languages and currencies
                            </p>
                            <div class="d-flex justify-content-end mb-4">
                                <button type="button"
                                    class="btn btn-light-primary btn-sm flex-shrink-0 align-self-center py-3 px-4 fs-7"
                                    data-bs-toggle="modal"
                                    data-bs-target="#showImportModal">Import
                                </button>
                            </div>
                            <div class="row g-6 col-spacing">
                                <!-- Primary Country Selection -->
                                <div class="col-lg-12">
                                    <label class="form-label" for="country">Primary Country</label>
                                    <select class="form-select al_box_height" id="primary_country" name="primary_country" data-control="select2">
                                        <option value="" disabled selected>Select Country</option>
                                        <!-- Loop through countries and select the one that matches stored preference -->
                                        {{-- @foreach($countries as $country)--}}
                                        {{-- <option value="{{ $country->id }}"--}}
                                        {{-- {{ (isset($preference) && getConfigValue($preference, 'primary_country') == $country->id) ? 'selected' : '' }}>--}}
                                        {{-- {{ $country->name }}--}}
                                        {{-- </option>--}}
                                        {{-- @endforeach--}}
                                    </select>
                                </div>

                                <!-- Primary Language Selection -->
                                <div class="col-lg-12">
                                    <label class="form-label" for="languages">Primary Language</label>
                                    <select class="form-select al_box_height" id="primary_language" name="primary_language" data-control="select2">
                                        <option value="" disabled selected>Select Language</option>
                                        {{-- @foreach($languages as $lang)--}}
                                        {{-- <option value="{{ $lang->id }}"--}}
                                        {{-- {{ (isset($preference) && getConfigValue($preference, 'primary_language') == $lang->id) ? 'selected' : '' }}>--}}
                                        {{-- {{ $lang->name }}--}}
                                        {{-- </option>--}}
                                        {{-- @endforeach--}}
                                    </select>
                                </div>

                                <!-- Primary Currency Selection -->
                                <div class="col-lg-12">
                                    <label class="form-label" for="primary_currency">Primary Currency</label>
                                    <select class="form-select al_box_height" id="primary_currency" name="primary_currency" data-control="select2">
                                        <option value="" disabled selected>Select Currency</option>
                                        {{-- @foreach($currencies as $currency)--}}
                                        {{-- <option iso="{{ $currency->iso_code . ' ' . $currency->symbol }}"--}}
                                        {{-- value="{{ $currency->id }}"--}}
                                        {{-- {{ (isset($preference) && getConfigValue($preference, 'primary_currency') == $currency->id) ? 'selected' : '' }}>--}}
                                        {{-- {{ $currency->iso_code . ' ' . $currency->symbol }}--}}
                                        {{-- </option>--}}
                                        {{-- @endforeach--}}
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Example Forms -->
        <div id="example-form-1" class="form-container" style="{{ request('form') === 'example-form-1' ? 'display: block;' : 'display: none;' }}">
            <h2>Example Form 1</h2>
        </div>

        <div id="example-form-2" class="form-container" style="{{ request('form') === 'example-form-2' ? 'display: block;' : 'display: none;' }}">
            <h2>Example Form 2</h2>
            <!-- Example form 2 fields here -->
        </div>

        <div id="example-form-3" class="form-container" style="{{ request('form') === 'example-form-3' ? 'display: block;' : 'display: none;' }}">
            <h2>Example Form 3</h2>
            <!-- Example form 3 fields here -->
        </div>

        <!-- Customer Support Form -->
        <div id="customer-support-form" class="form-container" style="{{ request('form') === 'customer-support-form' ? 'display: block;' : 'display: none;' }}">
            <h2>Customer Support</h2>
            <!-- Customer support form fields here -->
        </div>
    </div>
    <div class="modal fade" id="showImportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="showImportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="showImportModalLabel">Import</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="file" class="form-control">
                        </div>
                        <label class="d-inline-block me-3">
                            <select name="type" class="form-select form-select-sm select2-hidden-accessible"
                                data-control="select2" data-placeholder="Select an option" data-allow-clear="true"
                                multiple=""
                                required>
                                <option value="currency">Currency</option>
                                <option value="language">Language</option>
                                <option value="country">Country</option>
                                <option value="timezone">TimeZone</option>
                            </select>
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">Import</button>
                    </form>

                    <div class="mt-6">
                        <h5 class="mb-4">Download Sample Files:</h5>
                        <div class="d-flex flex-wrap gap-5 flex-stack">
                            <a href="{{ route('download.sample.file', ['filename' => 'currency_sample.csv']) }}"
                                class="btn btn-light-primary btn-sm">Currency Sample</a>
                            <a href="{{ route('download.sample.file', ['filename' => 'language_sample.csv']) }}"
                                class="btn btn-light-primary btn-sm">Language Sample</a>
                            <a href="{{ route('download.sample.file', ['filename' => 'country_sample.csv']) }}"
                                class="btn btn-light-primary btn-sm">Country Sample</a>
                            <a href="{{ route('download.sample.file', ['filename' => 'timezone_sample.csv']) }}"
                                class="btn btn-light-primary btn-sm">Timezone Sample</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection