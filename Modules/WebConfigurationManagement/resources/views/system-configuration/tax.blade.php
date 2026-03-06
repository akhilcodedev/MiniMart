@extends('webconfigurationmanagement::configurations.core-config')

@section('container')
    <div class="container-fluid px-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tax Support</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('tax-config.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8 col-sm-6">
                            <div class="mb-10">
                                <label class="required form-label">Country</label>
                                <select class="form-select form-select-solid" data-control="select2" id="country_id"
                                    name="country_id" required>
                                    <option value="0" disabled>{{ __('Select Country...') }}</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ (isset($latestTaxValue) && $latestTaxValue->country_id == $country->id) ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-10">
                                <label class="required form-label">Tax Value (%)</label>
                                <input type="hidden" name="config_path" value="web_configuration_tax">
                                <div class="input-group">
                                    <input type="number" name="value" id="value" class="form-control form-control-solid"
                                    placeholder="Enter Tax Value" required
                                    value="{{ old('value', $latestTaxValue->value ?? '') }}">

                                </div>
                                @error('value') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-10">
                                <label class="required form-label">Tax Type</label>
                                <div class="d-flex">
                                    <div class="form-check me-10">
                                        <input class="form-check-input" type="radio" name="tax_type" id="inclusive"
                                        value="inclusive" required
                                        {{ (isset($latestTaxType) && $latestTaxType->value === 'inclusive') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inclusive">Inclusive</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tax_type" id="exclusive"
                                        value="exclusive" required
                                        {{ (isset($latestTaxType) && $latestTaxType->value === 'exclusive') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="exclusive">Exclusive</label>
                                    </div>
                                </div>
                                @error('tax_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-lg-4 col-md-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
