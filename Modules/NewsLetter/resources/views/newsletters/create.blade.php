@extends('base::layouts.mt-main')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add New Newsletter Subscription</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('newsletters.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status:</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="subscribed" {{ old('status') == 'subscribed' ? 'selected' : '' }}>Subscribed</option>
                            <option value="unsubscribed" {{ old('status') == 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Newsletter</button>
                </form>
            </div>
        </div>
    </div>
@endsection