@extends('layouts.app')

@section('content')
    <!-- Include Slideshow -->
    @include('partials.slideshow', ['featuredListings' => $featuredListings])

    <!-- Tagline and CTA -->
    <section class="tagline">
        <h1>Your Trusted Farm Animal Marketplace</h1>
        <p>Connect with local farmers & traders and find the perfect animals for your farm.</p>
        <a href="{{ url('marketplace') }}" class="cta">Browse Animals</a>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="feature-card">
            <h3>Wide Selection</h3>
            <p>Browse through various farm animals from a community of sellers across the country.</p>
        </div>
        <div class="feature-card">
            <h3>Direct Communication</h3>
            <p>Connect directly with sellers through our secure messaging system.</p>
        </div>
        <div class="feature-card">
            <h3>Safe Trading</h3>
            <p>Our platform aims at creating a trusted community where people can trade freely & openly.</p>
        </div>
    </section>
@endsection