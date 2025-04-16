// js/navbar.js
document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.querySelector('.navbar-toggle');
    const menu = document.querySelector('.navbar-menu');

    toggleButton.addEventListener('click', () => {
        toggleButton.classList.toggle('active');
        menu.classList.toggle('active');
    });

    // Close menu when clicking a link (optional, improves UX)
    menu.querySelectorAll('a, .logout-button').forEach(link => {
        link.addEventListener('click', () => {
            toggleButton.classList.remove('active');
            menu.classList.remove('active');
        });
    });
});