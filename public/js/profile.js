// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Disable submit buttons on form submission
document.getElementById('profile-form')?.addEventListener('submit', function () {
    const submitButton = document.getElementById('submit-profile');
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';
});

document.getElementById('signout-form')?.addEventListener('submit', function () {
    const signoutButton = document.getElementById('signout-button');
    signoutButton.disabled = true;
    signoutButton.textContent = 'Signing Out...';
});
