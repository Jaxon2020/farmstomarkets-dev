@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="header">
            <img src="https://hemsunqtfchweiefnxmd.supabase.co/storage/v1/object/public/listings-images/public/farmstomarkets_logo.jpg" alt="FarmsToMarkets Logo">
        </div>
        <div class="content">
            <h2>Register</h2>
            <p>Create your FarmsToMarkets account.</p>

            @if (session('success'))
                <div class="success">
                    {{ session('success') }}
                </div>
            @endif

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