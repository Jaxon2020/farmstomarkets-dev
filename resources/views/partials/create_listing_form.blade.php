<div class="marketplace-sidebar">
    <div class="create-listing-form">
        <h3>Create Item Listing</h3>
        @if (!empty($message))
            <p class="{{ str_contains($message, 'Failed') ? 'failure' : 'success' }}">
                {{ htmlspecialchars($message) }}
            </p>
        @endif
        <form method="POST" enctype="multipart/form-data" id="create-listing-form">
            @csrf
            <input type="hidden" name="create_listing" value="1">
            <input type="hidden" name="form_token" value="{{ htmlspecialchars($formToken) }}">
            <div class="form-group">
                <label for="listing-title">Title</label>
                <input type="text" id="listing-title" name="title" placeholder="e.g. Chicken - 6 Months Old" value="{{ old('title') }}" required>
            </div>
            <div class="form-group">
                <label for="listing-price">Price</label>
                <input type="number" id="listing-price" name="price" placeholder="$" step="0.01" value="{{ old('price') }}" required>
            </div>
            <div class="form-group">
                <label for="listing-type">Type</label>
                <input type="text" id="listing-type" name="type" placeholder="e.g. Chicken" value="{{ old('type') }}" required>
            </div>
            <div class="form-group">
                <label for="listing-location">Location</label>
                <input type="text" id="listing-location" name="location" placeholder="e.g. Springfield, IL" value="{{ old('location') }}" required>
            </div>
            <div class="form-group">
                <label for="listing-description">Description (Optional)</label>
                <textarea id="listing-description" name="description" placeholder="e.g. Healthy laying hen, vaccinated, friendly temperament" rows="4">{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label for="contact-email">Contact Email</label>
                <input type="email" id="contact-email" name="contact_email" 
                       placeholder="e.g. seller@example.com" 
                       value="{{ old('contact_email') }}"
                       data-user-email="{{ $userProfile['email'] ?? '' }}">
                <div class="checkbox-below">
                    <input type="checkbox" id="use-account-email" name="use_account_email">
                    <label for="use-account-email">Use my account email</label>
                </div>
            </div>
            <div class="form-group">
                <label for="contact-phone">Contact Phone</label>
                <input type="tel" id="contact-phone" name="contact_phone" 
                       placeholder="e.g. (555) 123-4567" 
                       value="{{ old('contact_phone') }}"
                       data-user-phone="{{ $userProfile['phone'] ?? '' }}">
                <div class="checkbox-below">
                    <input type="checkbox" id="use-account-phone" name="use_account_phone">
                    <label for="use-account-phone">Use my account phone</label>
                </div>
            </div>
            <div class="form-group">
                <label for="listing-image">Upload Image</label>
                <input type="file" id="listing-image" name="image" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="preferred-contact">Preferred Contact Method</label>
                <select id="preferred-contact" name="preferred_contact" required>
                    <option value="">Select a method</option>
                    <option value="email" {{ old('preferred_contact') === 'email' ? 'selected' : '' }}>Email</option>
                    <option value="phone" {{ old('preferred_contact') === 'phone' ? 'selected' : '' }}>Phone</option>
                </select>
            </div>
            <div class="form-buttons">
                <button type="submit" class="create-btn">Create Listing</button>
            </div>
        </form>
    </div>
</div>