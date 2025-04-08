<div class="filter-bar">
    <form method="POST" id="search-form" class="search-form">
        @csrf
        <input type="hidden" name="search" value="1">
        <div class="form-group">
            <label for="animal-type">Animal Type</label>
            <input type="text" id="animal-type" name="animal-type" placeholder="e.g. Chicken" value="{{ htmlspecialchars($searchCriteria['animal-type']) }}">
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" placeholder="e.g. Springfield, IL" value="{{ htmlspecialchars($searchCriteria['location']) }}">
        </div>
        <div class="form-group">
            <label for="min-price">Min Price</label>
            <input type="number" id="min-price" name="min-price" placeholder="$" value="{{ htmlspecialchars($searchCriteria['min-price']) }}">
        </div>
        <div class="form-group">
            <label for="max-price">Max Price</label>
            <input type="number" id="max-price" name="max-price" placeholder="$" value="{{ htmlspecialchars($searchCriteria['max-price']) }}">
        </div>
        <div class="search-buttons">
            <button type="submit" class="search-btn btn">Search</button>
            <form method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="reset_search" value="1">
                <button type="submit" class="reset-btn btn">Reset</button>
            </form>
        </div>
    </form>
</div>