<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>TrendZ Studio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f7f7;
        }

        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            color: #ffffff;
            background-color: #007bff;
            border-radius: 5px;
            text-decoration: none;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            <h2>Welcome to , {{ $merchant_name }}!</h2>
            <p>Your account has been created successfully. Here are your login credentials:</p>
            <p><strong>Email:</strong> {{ $mail }}</p>
            <p><strong>Password:</strong> {{ $password }}</p>
            <a href="#" class="button">Login to your account</a>
            <p>If you have any questions, feel free to reply to this email.</p>
            <p>Best regards,<br>TrendZ Studio - {{ $branch }}</p>
        </div>
    </div>
</body>

</html>
