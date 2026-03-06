@extends('webconfigurationmanagement::configurations.core-config')

@section('container')
<div class="container">
    <h1>Out of Stock Configuration</h1>

    <form action="{{ route('oss-configurations.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <!-- <label for="product_id">Product ID:</label> -->
            <input type="text" name="product_id" id="product_id" placeholder="Product ID" class="form-control" required>
        </div>
<br>
        @guest
        <div class="form-group">
            <label for="guest_email">Your Email:</label>
            <input type="email" name="guest_email" id="guest_email" class="form-control" required>
        </div>
        @endguest

        <button type="submit" class="btn btn-primary">Save Configuration</button>
    </form>
</div>
@endsection