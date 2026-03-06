@extends('webconfigurationmanagement::configurations.core-config')

@section('container')
    <div class="container">
        <h2>Test Mail Configuration</h2>


        <form action="{{ route('system.config.send-test-mail') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Enter Test Email</label>
                <input type="email" name="test_email" class="form-control" placeholder="Enter email address" required>
            </div>

            <button type="submit" class="btn btn-primary">Send Test Mail</button>
        </form>
    </div>
@endsection
