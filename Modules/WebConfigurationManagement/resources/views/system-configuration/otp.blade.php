@extends('webconfigurationmanagement::configurations.core-config')

@section('container')
<div class="container">
    <h1>OTP Configuration</h1>

    <!-- Existing form for sending a one-time (random) OTP -->
    <form method="POST" action="{{ route('otp.store') }}">
        @csrf
        <div class="form-group">
            <input type="text"
                   name="mobile_number"
                   id="mobile_number"
                   class="form-control"
                   placeholder="e.g., 7306588186"
                   required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Send OTP</button>
    </form>

    <!-- Static OTP Settings -->
    <h2 class="mt-5">Static OTP</h2>
    <form method="POST" action="{{ route('otp.saveStatic') }}">
        @csrf
        <!-- Hidden input to ensure a value is always sent -->
        <input type="hidden" name="static_otp_enabled" value="0">
        
        <!-- Toggle Switch -->
        <div class="form-check form-switch mb-3">
            <input type="checkbox"
                   class="form-check-input"
                   id="static_otp_enabled"
                   name="static_otp_enabled"
                   value="1"
                   {{ old('static_otp_enabled', ($settings->static_otp_enabled ?? '0')) == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="static_otp_enabled">
                Enable
            </label>
        </div>

        <div class="form-group">
            <input type="text"
                   class="form-control"
                   id="static_otp_code"
                   name="static_otp_code"
                   value="{{ old('static_otp_code', $settings->static_otp_code ?? '123456') }}"
                   placeholder="123456">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save</button>
    </form>
</div>

<!-- JavaScript to toggle the OTP code input -->
<script>
    function toggleOtpCodeInput() {
    var otpCheckbox = document.getElementById('static_otp_enabled');
    var otpCodeInput = document.getElementById('static_otp_code');
    otpCodeInput.disabled = !otpCheckbox.checked;
}
document.addEventListener('DOMContentLoaded', toggleOtpCodeInput);
document.getElementById('static_otp_enabled').addEventListener('change', toggleOtpCodeInput);

</script>
@endsection
