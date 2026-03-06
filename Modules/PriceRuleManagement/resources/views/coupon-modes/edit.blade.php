@extends('base::layouts.mt-main')  <!-- or your base layout -->

@section('content')
    <div class="container">
        <h1>Edit Coupon Mode</h1>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(isset($couponMode))
            <form id="edit_coupon_mode_form" method="POST" action="{{route('priceRule.cart.couponModes.update', ['id' => $couponMode->id])}} ">
                @csrf

                <input type="hidden" id="modeId" name="modeId" value="{{ $couponMode->id }}">

                <div class="form-group">
                    <label for="code">Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code" value="{{ $couponMode->code }}" required>
                </div>

                <div class="form-group">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $couponMode->name }}" required>
                </div>

                <div class="form-group">
                    <label for="sort_order">Sort Order <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ $couponMode->sort_order }}" required>
                </div>

                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select class="form-control" id="is_active" name="is_active">
                        <option value="1" {{ $couponMode->is_active == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $couponMode->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('priceRule.cart.couponModes.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        @else
            <p>No Coupon Mode found.</p>
        @endif
    </div>
@endsection

@section('custom-js-section')
    <!--  Your JavaScript Section remains largely the same as provided before but *remove* the loadCouponModeData and URL splitting logic! It is NO LONGER NECESSARY. This will be server-side rendered  -->
    <script>
        $(document).ready(function() {
            $('#edit_coupon_mode_form').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let updateURL = form.attr('action');

                $("button[type='submit']").prop('disabled', true);

                $.ajax({
                    url: updateURL,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            showAlertMessage('Coupon Mode updated successfully!', 'success');

                            // Redirect to index page after success
                            setTimeout(() => {
                                window.location.href = "{{ route('priceRule.cart.couponModes.index') }}"; //Use named routes
                            }, 1500);
                        } else {
                            showAlertMessage('Failed to update Coupon Mode.', 'error');
                        }
                        $("button[type='submit']").prop('disabled', false);
                    },
                    error: function(xhr) {
                        $("button[type='submit']").prop('disabled', false);
                        let errorMessage = xhr.responseJSON?.errors
                            ? Object.values(xhr.responseJSON.errors).flat().join('<br>')
                            : 'An unexpected error occurred';
                        showAlertMessage(errorMessage, 'error');
                    }
                });
            });
        });

        // Show alert message function
        function showAlertMessage(message, type = 'info') {
            let divClass = type === 'success' ? 'alert-success' : 'alert-danger';
            let iconClass = type === 'success' ? 'flaticon2-check-mark' : 'flaticon2-warning';

            $("div.custom_alert_trigger_messages_area").html(`
        <div class="alert ${divClass} fade show" role="alert">
            <div class="alert-icon"><i class="${iconClass}"></i></div>
            <div class="alert-text">${message}</div>
            <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
        </div>
    `);
        }

    </script>
@endsection


