@extends('layouts.app')

@section('styles')
    <!-- Include CSS references from the home page -->
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/marketplace.css') }}">

@endsection

@section('scripts')
    <script src="{{ asset('js/marketplace.js') }}" defer></script>
@endsection

@section('content')
    <!-- Slideshow Section -->
    <section class="slideshow">
        @include('partials.slideshow', ['featuredListings' => $featuredListings])
    </section>

    <section class="marketplace">
        <h1>Marketplace</h1>
        <p>Browse and search for farm animals available for sale.</p>

        <!-- Search Form -->
        <div class="search-bar-container">
            <form method="POST" action="{{ route('marketplace') }}" class="search-bar">
                @csrf
                <input type="text" name="animal-type" placeholder="Animal Type" value="{{ $searchCriteria['animal-type'] }}" class="search-input">
                <input type="text" name="location" placeholder="Location" value="{{ $searchCriteria['location'] }}" class="search-input">
                <input type="number" name="min-price" placeholder="Min Price" value="{{ $searchCriteria['min-price'] }}" class="search-input">
                <input type="number" name="max-price" placeholder="Max Price" value="{{ $searchCriteria['max-price'] }}" class="search-input">
                <button type="submit" name="search" class="search-button">Search</button>
                <button type="submit" name="reset_search" class="search-button">Reset</button>
            </form>
        </div>

        <!-- Display Messages -->
        @if ($message)
            <div class="message {{ strpos($message, 'successfully') !== false ? 'success' : 'error' }}">
                {{ $message }}
            </div>
        @endif

        <!-- Flex Container for Listings and Create Form -->
        <div class="marketplace-container">
            <!-- Product Card Section -->
            <div class="listings-section">
                @include('partials.product_card_section', [
                    'listings' => $listings,
                    'selectedListing' => $selectedListing,
                    'selectedListingHasLiked' => $selectedListingHasLiked,
                    'formToken' => $formToken,
                    'isAuthenticated' => $isAuthenticated
                ])
            </div>

            <!-- Create Listing Form -->
            @if ($isAuthenticated)
                <div class="create-listing-sidebar">
                    @include('partials.create_listing_form', [
                        'formToken' => $formToken,
                        'message' => $message ?? '',
                        'userProfile' => $userProfile ?? null
                    ])
                </div>
            @else
                <div class="create-listing-sidebar">
                    <p>Please <a href="{{ route('login') }}">log in</a> to create a listing.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
