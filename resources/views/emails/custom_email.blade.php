<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-body {
            padding: 20px;
            color: #333333;
            line-height: 1.6;
        }
        .email-body p {
            margin: 10px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
    <title>Email Template</title>
</head>
<body>
<div class="email-container">
    {!! $headerContent !!}
    <div class="email-body">
        {!! $htmlContent !!}
    </div>
    {!! $footerContent !!}
</div>
</body>
</html>
