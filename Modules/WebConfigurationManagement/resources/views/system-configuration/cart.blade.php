@extends('webconfigurationmanagement::configurations.core-config')

@section('container')
    <div class="container">
        <h2>Abandoned Cart Configuration</h2>

        <form action="{{ route('system.config.abandoned-cart.submit') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Send Notification to User?</label>
                <select name="send_notification" class="form-control">
                    <option value="1" {{ old('send_notification', $sendNotification) == '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('send_notification', $sendNotification) == '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Abandoned Cart Duration</label>
                <select name="abandoned_cart_days" class="form-control">
                    @for ($i = 1; $i <= 30; $i++)
                        <option value="{{ $i }}" {{ old('abandoned_cart_days', $abandonedCartDays) == $i ? 'selected' : '' }}>
                            {{ $i }} day{{ $i > 1 ? 's' : '' }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Maximum Number of Emails Allowed</label>
                <select name="no_of_mails" class="form-control">
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ old('no_of_mails', $noOfMails) == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>            

            <div class="mb-3">
                <label class="form-label">Gap Between Sent Emails (in Days)</label>
                <select name="email_gap_days" class="form-control">
                    @for ($i = 1; $i <= 7; $i++)
                        <option value="{{ $i }}" {{ old('email_gap_days', $emailGapDays) == $i ? 'selected' : '' }}>
                            {{ $i }} day{{ $i > 1 ? 's' : '' }}
                        </option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection
