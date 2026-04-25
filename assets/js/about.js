// About page interactive elements
document.addEventListener("DOMContentLoaded", function() {
    // Reveal animations on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.glass-panel').forEach(card => {
        observer.observe(card);
    });
});
