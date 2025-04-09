<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'FarmMarket' }}</title>

    <!-- Global Styles -->
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/themes.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/slideshow.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">

    <!-- Page-Specific Styles -->
    @yield('styles')
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC:wght@700&family=Georgia&display=swap" rel="stylesheet">
</head>
<body data-theme="{{ session('theme', 'original') }}">
    @if (session('signup_success'))
        <div id="signupModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" id="closeSignupModal">×</span>
                <p>Sign up successful! A confirmation email has been sent to your email address.</p>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('signupModal');
                const closeBtn = document.getElementById('closeSignupModal');
                modal.style.display = 'block';
                closeBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });
                window.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        </script>
        @php session()->forget('signup_success') @endphp
    @endif

    <header>
        @include('partials.navbar')
        <nav>
            <ul>
                <!-- Removed profile link -->
            </ul>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        @include('partials.footer')
    </footer>
    @if (View::hasSection('slideshow'))
        <script src="{{ asset('js/slideshow.js') }}"></script>
    @endif
</body>
</html>