<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FarmsToMarkets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 400px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .header img {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .content h2 {
            color: #2e7d32;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 5px rgba(46, 125, 50, 0.3);
        }
        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            color: #2e7d32;
            font-size: 14px;
            margin-top: 5px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2e7d32;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #1b5e20;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }
        .footer a {
            color: #2e7d32;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://hemsunqtfchweiefnxmd.supabase.co/storage/v1/object/public/listings-images/public/farmstomarkets_logo.jpg" alt="FarmsToMarkets Logo">
        </div>
        <div class="content">
            <h2>Reset Your Password</h2>
            <p>Enter your new password below to reset your FarmsToMarkets account.</p>

            @if (session('status'))
                <div class="success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input id="password" type="password" name="password" required>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                    @error('password_confirmation')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <button type="submit" class="button">Reset Password</button>
                </div>
            </form>
        </div>
        <div class="footer">
            <p>Need help? <a href="https://farmstomarkets.com/support">Contact Support</a></p>
            <p>© 2025 FarmsToMarkets. All rights reserved.</p>
        </div>
    </div>
</body>
</html>