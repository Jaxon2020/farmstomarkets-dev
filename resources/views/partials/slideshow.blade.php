@isset($featuredListings)
    <section class="hero">
        <div class="slideshow-container">
            @foreach ($featuredListings as $index => $listing)
                <div class="slide fade">
                    <img src="{{ $listing['image_url'] ?? '/images/placeholder.jpg' }}" alt="Listing Image">
                    <div class="animal-info">
                        <h3>{{ $listing['title'] ?? 'Untitled Listing' }}</h3>
                        <p>${{ number_format($listing['price'] ?? 0, 2) }}</p>
                    </div>
                </div>
            @endforeach

            <!-- Navigation Buttons -->
            <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" onclick="plusSlides(1)">&#10095;</a>

            <!-- Dots for Navigation -->
            <div class="dots">
                @foreach ($featuredListings as $index => $listing)
                    <span class="dot" onclick="currentSlide({{ $index + 1 }})"></span>
                @endforeach
            </div>
        </div>
    </section>
@endisset
<script src="{{ asset('js/slideshow.js') }}"></script>