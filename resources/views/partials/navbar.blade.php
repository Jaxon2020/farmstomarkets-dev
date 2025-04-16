<nav class="navbar">
    <div class="navbar-brand">
        <a href="{{ route('home') }}" class="brand-logo">FarmstoMarkets</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">
            <span class="hamburger-icon"></span>
        </button>
    </div>
    <ul class="navbar-menu">
        <li><a href="{{ route('home') }}">Home</a></li>
        <li><a href="{{ route('marketplace') }}">Marketplace</a></li>
        <li><a href="{{ route('about') }}">About</a></li>
        <li><a href="{{ route('agreement') }}">Agreement</a></li>
        @auth
            <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li><a href="{{ route('profile.edit') }}">Profile</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-button">Logout</button>
                </form>
            </li>
        @else
            <li><a href="{{ route('login') }}">Login</a></li>
            <li><a href="{{ route('register') }}">Register</a></li>
        @endauth
    </ul>
</nav>