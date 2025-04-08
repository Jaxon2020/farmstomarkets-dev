<div class="listings-container">
    @if ($selectedListing)
        <!-- Product Details Section -->
        <div class="product-details-overlay">
            <div class="product-details-content">
                <h3>{{ $selectedListing['title'] ?? 'Untitled Listing' }}</h3>
                <img src="{{ $selectedListing['image_url'] ?? '/images/placeholder.jpg' }}" alt="{{ $selectedListing['title'] ?? 'Untitled Listing' }}" class="product-details-image">
                <p><strong>Price:</strong> ${{ number_format($selectedListing['price'] ?? 0, 2) }}</p>
                <p><strong>Type:</strong> {{ $selectedListing['type'] ?? 'Not specified' }}</p>
                <p><strong>Location:</strong> {{ $selectedListing['location'] ?? 'Not specified' }}</p>
                @if (!empty($selectedListing['description']))
                    <p><strong>Description:</strong> {{ $selectedListing['description'] }}</p>
                @endif
                <h4>Contact Information</h4>
                <p><strong>Email:</strong> {{ $selectedListing['contact_email'] ?? 'Not available' }}</p>
                <p><strong>Phone:</strong> {{ $selectedListing['contact_phone'] ?? 'Not available' }}</p>
                <p><strong>Preferred Contact Method:</strong> {{ $selectedListing['preferred_contact'] ?? 'Not specified' }}</p>

                <!-- Add Like Button -->
                @if ($isAuthenticated && session('supabase_user_id') !== ($selectedListing['user_id'] ?? null))
                    <form method="POST" action="{{ route('marketplace') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="like_listing" value="1">
                        <input type="hidden" name="listing_id" value="{{ $selectedListing['id'] }}">
                        <input type="hidden" name="form_token" value="{{ $formToken }}">
                        <button type="submit" class="like-btn btn {{ $selectedListingHasLiked ? 'liked' : '' }}">
                            {{ $selectedListingHasLiked ? 'Unlike' : 'Like' }}
                        </button>
                    </form>
                @endif

                <a href="{{ route('marketplace') }}#listing-{{ $selectedListing['id'] }}" class="back-btn">Back to Listings</a>
            </div>
        </div>
    @else
        <!-- Show Listings if No Product Details are Selected -->
        <h3>Available Listings</h3>
        @if (empty($listings))
            <p class="no-results">No listings found. Try adjusting your search criteria.</p>
        @else
            <div class="card-grid">
                @foreach ($listings as $listing)
                    <div class="card" id="listing-{{ $listing['id'] }}">
                        <img src="{{ $listing['image_url'] ?? '/images/placeholder.jpg' }}" alt="{{ $listing['title'] ?? 'Untitled Listing' }}">
                        <h4>{{ $listing['title'] ?? 'Untitled Listing' }}</h4>
                        <p class="price">${{ number_format($listing['price'] ?? 0, 2) }}</p>
                        <p><strong>Type:</strong> {{ $listing['type'] ?? 'Not specified' }}</p>
                        <p><strong>Location:</strong> {{ $listing['location'] ?? 'Not specified' }}</p>
                        @if (!empty($listing['description']))
                            <p class="description">{{ $listing['description'] }}</p>
                        @endif
                        <div class="card-buttons">
                            <a href="{{ route('marketplace') }}?view_details={{ $listing['id'] }}" class="view-details btn">View Details</a>
                            @if ($isAuthenticated && isset($listing['user_id']) && session('supabase_user_id') === $listing['user_id'])
                                <!-- Edit Button -->
                                <a href="{{ url('edit-listing') }}?listing_id={{ $listing['id'] }}" class="edit-btn btn">Edit</a>
                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('marketplace') }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                    @csrf
                                    <input type="hidden" name="delete_listing" value="1">
                                    <input type="hidden" name="listing_id" value="{{ $listing['id'] }}">
                                    <input type="hidden" name="form_token" value="{{ $listing['delete_form_token'] }}">
                                    <button type="submit" class="delete-btn btn">Delete</button>
                                </form>
                            @elseif ($isAuthenticated && isset($listing['user_id']))
                                <!-- Like/Unlike Button -->
                                <form method="POST" action="{{ route('marketplace') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="like_listing" value="1">
                                    <input type="hidden" name="listing_id" value="{{ $listing['id'] }}">
                                    <input type="hidden" name="form_token" value="{{ $formToken }}">
                                    <button type="submit" class="like-btn btn {{ $listing['hasLiked'] ? 'liked' : '' }}">
                                        {{ $listing['hasLiked'] ? 'Unlike' : 'Like' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>