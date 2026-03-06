<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Platform</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
            text-align: center;
        }

        p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            text-align: center;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #888;
        }

        .button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #5cb85c;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
        }

        .button:hover {
            background-color: #4cae4c;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 100px;
        }

        .header {
            background-color: #004d99;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Welcome to Our Platform</h1>
    </div>
    <div class="logo">
        <img src="https://via.placeholder.com/100" alt="Logo">
    </div>
    <h1>Hello, {{ $customerName }}!</h1>
    <p>We are thrilled to have you on board. Thank you for registering with us. We're here to assist you every step of the way!</p>
    <p>To get started, you can log in to your account using the button below:</p>
    <p>If you have any questions or need assistance, feel free to <a href="mailto:support@ourplatform.com" style="color: #5cb85c;">contact our support team</a>.</p>
    <div class="footer">
        <p>Best regards,</p>
        <p>The Team at Our Platform</p>
        <p><a href="https://ourplatform.com" style="color: #5cb85c;">Visit our website</a></p>
    </div>
</div>
</body>
</html>
