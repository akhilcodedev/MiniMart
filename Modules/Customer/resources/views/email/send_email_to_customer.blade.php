<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #007bff;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .email-header img {
            max-height: 50px;
        }
        .email-content {
            padding: 20px;
        }
        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #777777;
            padding: 15px 20px;
            background: #f1f1f1;
        }
        .email-footer a {
            color: #007bff;
            text-decoration: none;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header Section -->
    <div class="email-header">
        <img src="{{ asset('logo.png') }}" alt="Company Logo">
        <h1>{{ $headerTitle ?? 'Company Name' }}</h1>
    </div>

    <!-- Content Section -->
    <div class="email-content">
        @yield('content')
    </div>

    <!-- Footer Section -->
    <div class="email-footer">
        <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p><a href="{{ url('/') }}">Visit our website</a></p>
    </div>
</div>
</body>
</html>
