@extends('layouts.app')

@section('styles')
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            text-align: center;
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
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 5px rgba(46, 125, 50, 0.3);
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2e7d32;
            color: #ffffff !important;
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
        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-top: 10px;
        }
        .links {
            margin-top: 20px;
            font-size: 14px;
        }
        .links a {
            color: #2e7d32;
            text-decoration: none;
            margin-right: 15px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }
        .footer a {
            color: #2e7d32;
            text-decoration: none;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="header">
            <img src="https://hemsunqtfchweiefnxmd.supabase.co/storage/v1/object/public/listings-images/public/farmstomarkets_logo.jpg" alt="FarmsToMarkets Logo">
        </div>
        <div class="content">
            <h2>Register</h2>
            <p>Create a new FarmsToMarkets account to get started.</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <button type="submit" class="button">Register</button>
                </div>

                <div class="links">
                    <a href="{{ route('login') }}">Already registered?</a>
                </div>
            </form>
        </div>
        <div class="footer">
            <p>Need help? <a href="https://farmstomarkets.com/support">Contact Support</a></p>
            <p>© 2025 FarmsToMarkets. All rights reserved.</p>
        </div>
    </div>
@endsection