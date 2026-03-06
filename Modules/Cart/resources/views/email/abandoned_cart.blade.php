<!DOCTYPE html>
<html>
<head>
    <title>Abandoned Cart Reminder</title>
</head>
<body>
<p>Hi {{ $cart->customer->name }},</p>
<p>We noticed you left some items in your cart! Complete your purchase before they run out.</p>
<p><a href="{{ url('/cart') }}">View Your Cart</a></p>
<p>Best Regards,<br> Your Store Team</p>
</body>
</html>
