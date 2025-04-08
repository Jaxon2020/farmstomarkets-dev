@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/agreement.css') }}">
@endsection

@section('content')
    <section class="agreement-content">
        <div class="agreement-container">
            <h1>User Agreement</h1>
            <p>Welcome to FarmstoMarkets! By using our platform, you agree to the following terms and conditions:</p>

            <div class="agreement-section">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using FarmstoMarkets, you agree to comply with and be bound by these terms. If you do not agree, please do not use our platform.</p>
            </div>

            <div class="agreement-section">
                <h2>2. User Responsibilities</h2>
                <ul>
                    <li>You are responsible for providing accurate and truthful information when creating listings or interacting with other users.</li>
                    <li>You must not engage in fraudulent or illegal activities on the platform.</li>
                    <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                </ul>
            </div>

            <div class="agreement-section">
                <h2>3. Prohibited Activities</h2>
                <ul>
                    <li>Posting false or misleading information.</li>
                    <li>Harassing or abusing other users.</li>
                    <li>Uploading harmful or malicious content.</li>
                    <li>Violating any applicable laws or regulations.</li>
                </ul>
            </div>

            <div class="agreement-section">
                <h2>4. Limitation of Liability</h2>
                <p>FarmstoMarkets is not responsible for any disputes, damages, or losses arising from transactions or interactions between users. We provide the platform as-is and make no guarantees regarding the accuracy or reliability of listings or user interactions.</p>
            </div>

            <div class="agreement-section">
                <h2>5. Termination</h2>
                <p>We reserve the right to suspend or terminate your account if you violate these terms or engage in prohibited activities.</p>
            </div>

            <div class="agreement-section">
                <h2>6. Changes to the Agreement</h2>
                <p>FarmstoMarkets reserves the right to update or modify this agreement at any time. Continued use of the platform constitutes acceptance of the updated terms.</p>
            </div>

            <div class="agreement-section">
                <h2>7. Contact Us</h2>
                <p>If you have any questions or concerns about this agreement, please contact us at <a href="mailto:support@farmstomarkets.com">support@farmstomarkets.com</a>.</p>
            </div>
        </div>
    </section>
@endsection