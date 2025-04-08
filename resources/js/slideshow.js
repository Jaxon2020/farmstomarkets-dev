function showSlides() {
    const slides = document.querySelectorAll('.slide');
    if (!slides || slides.length === 0) return; // Prevent errors if no slides exist

    let slideIndex = 0;
    slides.forEach((slide, index) => {
        slide.style.display = index === slideIndex ? 'block' : 'none';
    });

    // Add logic for slideshow navigation if necessary
}

// Ensure the function runs only when the DOM is ready
document.addEventListener('DOMContentLoaded', showSlides);