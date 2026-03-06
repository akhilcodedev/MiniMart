@extends('base::layouts.mt-main')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Newsletter Subscription</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('newsletters.update', $newsletter->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ $newsletter->email }}" required>
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status:</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="subscribed" {{ $newsletter->status === 'subscribed' ? 'selected' : '' }}>Subscribed</option>
                            <option value="unsubscribed" {{ $newsletter->status === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Newsletter</button>
                </form>
            </div>
        </div>
    </div>
@endsection