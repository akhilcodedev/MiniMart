@extends('base::layouts.mt-main')

@section('content')

<div>
    <h1>Newsletter Subscription Confirmation</h1>
    <p>Thank you for subscribing to our newsletter with email: {{ $email }}</p>
    <p>Subscribed at: {{ $subscribedAt }}</p>
</div>

@endsection
