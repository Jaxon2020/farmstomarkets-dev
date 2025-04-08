<nav class="navbar">
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
        <!-- Theme Selector Dropdown -->
        <li>
            <form method="POST" action="{{ route('theme.switch') }}" class="theme-form">
                @csrf
                <select name="theme" onchange="this.form.submit()" class="theme-selector">
                    @foreach (config('themes.available') as $key => $label)
                        <option value="{{ $key }}" {{ session('theme', 'original') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>
        </li>
    </ul>
</nav>