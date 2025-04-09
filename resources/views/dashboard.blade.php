@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/marketplace.css') }}">
@endsection

@section('content')
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Slideshow Section -->
            <section class="slideshow mb-12">
                @if(!empty($featuredListings))
                    @include('partials.slideshow', ['featuredListings' => $featuredListings])
                @else
                    <p class="text-center text-gray-600">No featured listings available at the moment.</p>
                @endif
            </section>

            <!-- Dashboard Section -->
            <section class="marketplace">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-semibold text-gray-800">Dashboard</h1>
                    <p class="text-gray-600 mt-2">Manage your published listings and view your liked items.</p>
                </div>

                <!-- Search Form -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form method="POST" action="{{ route('dashboard') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                        @csrf
                        <input type="text" name="animal-type" placeholder="Animal Type" value="{{ $searchCriteria['animal-type'] }}" class="search-input border rounded-lg p-3">
                        <input type="text" name="location" placeholder="Location" value="{{ $searchCriteria['location'] }}" class="search-input border rounded-lg p-3">
                        <input type="number" name="min-price" placeholder="Min Price" value="{{ $searchCriteria['min-price'] }}" class="search-input border rounded-lg p-3">
                        <input type="number" name="max-price" placeholder="Max Price" value="{{ $searchCriteria['max-price'] }}" class="search-input border rounded-lg p-3">
                        <button type="submit" name="search" class="search-button bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700 transition">Search</button>
                        <button type="submit" name="reset_search" class="search-button bg-gray-300 text-gray-800 rounded-lg p-3 hover:bg-gray-400 transition">Reset</button>
                    </form>
                </div>

                <!-- Messages -->
                @if ($message)
                    <div class="p-4 rounded-lg mb-8 {{ strpos($message, 'successfully') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $message }}
                    </div>
                @endif

                <!-- Listings Sections -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Published Listings -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Published Listings</h2>
                    <div class="listings-section">
                        @if(empty($userListings))
                            <p class="text-gray-600">You haven’t published any listings yet.</p>
                        @else
                            @include('partials.product_card_section', [
                                'listings' => $userListings,
                                'selectedListing' => $selectedListing,
                                'selectedListingHasLiked' => $selectedListingHasLiked,
                                'formToken' => $formToken,
                                'isAuthenticated' => $isAuthenticated,
                                'routeName' => 'dashboard' // Added
                            ])
                        @endif
                    </div>
                </div>

                <!-- Liked Listings -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Liked Listings</h2>
                    <div class="listings-section">
                        @if(empty($likedListings))
                            <p class="text-gray-600">You haven’t liked any listings yet.</p>
                        @else
                            @include('partials.product_card_section', [
                                'listings' => $likedListings,
                                'selectedListing' => $selectedListing,
                                'selectedListingHasLiked' => $selectedListingHasLiked,
                                'formToken' => $formToken,
                                'isAuthenticated' => $isAuthenticated,
                                'routeName' => 'dashboard' // Added
                            ])
                        @endif
                    </div>
                </div>



                </div>
            </section>
        </div>
    </div>
@endsection