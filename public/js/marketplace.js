// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Disable submit button on form submission for create listing
document.getElementById('create-listing-form').addEventListener('submit', function(event) {
    const submitButton = this.querySelector('.create-btn');
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';
});

// Disable submit button on form submission for search
document.getElementById('search-form').addEventListener('submit', function(event) {
    const submitButton = this.querySelector('.search-btn');
    submitButton.disabled = true;
    submitButton.textContent = 'Searching...';
});

// Modal JavaScript
const modal = document.getElementById('productModal');
const closeBtn = document.querySelector('.close-btn');
const messageSellerContainer = document.getElementById('message-seller-container');

// Close modal when clicking the close button
closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
});

// Close modal when clicking outside the modal content
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        modal.style.display = 'none';
    }
});

// Function to open the modal and populate it with data
function openModal(listing) {
    if (!listing) {
        console.error("Listing data is missing or invalid.");
        return;
    }
    document.getElementById('modal-image').src = listing.image_url || '/images/placeholder.jpg';
    document.getElementById('modal-title').textContent = listing.title || 'N/A';
    document.getElementById('modal-price').textContent = '$' + (listing.price ? Number(listing.price).toFixed(2) : 'N/A');
    document.getElementById('modal-type').textContent = listing.type || 'N/A';
    document.getElementById('modal-location').textContent = listing.location || 'N/A';
    const descriptionContainer = document.getElementById('modal-description-container');
    if (listing.description) {
        descriptionContainer.innerHTML = '<p><strong>Description:</strong> <span>' + listing.description + '</span></p>';
    } else {
        descriptionContainer.innerHTML = '';
    }
    document.getElementById('modal-email').textContent = listing.contact_email || 'Not available';
    document.getElementById('modal-phone').textContent = listing.contact_phone || 'Not available';
    document.getElementById('modal-preferred-contact').textContent = listing.preferred_contact || 'Not specified';

    // Add "Message Seller" button to the modal if the user is logged in and not the seller
    const currentUserId = document.body.getAttribute('data-user-id');
    if (currentUserId && currentUserId !== listing.user_id) {
        messageSellerContainer.innerHTML = '<a href="/messages.php?chat_with=' + listing.user_id + '" class="message-seller-btn">Message Seller</a>';
    } else {
        messageSellerContainer.innerHTML = '';
    }

    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    closeBtn.setAttribute('aria-label', 'Close modal');
}
