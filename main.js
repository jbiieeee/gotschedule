// Modern Entrance Animation & UI Logic
document.addEventListener("DOMContentLoaded", function() {
    const textElement = document.getElementById("gotschedule-text");
    const container = document.getElementById("animation-container");
    const loginSection = document.querySelector('.login-section');
    const header = document.querySelector('.app-header');
    const footer = document.querySelector('footer');

    // 1. Initial State Check
    if (!textElement || !container) return;

    // 2. Step-by-Step Sequence
    
    // Phase A: Reveal the Logo Text
    setTimeout(() => {
        textElement.style.transition = "all 1.0s cubic-bezier(0.2, 0.8, 0.2, 1)";
        textElement.style.opacity = "1";
        textElement.style.transform = "scale(1) translateY(0)";
    }, 300);

    // Phase B: Transition from Splash to Main Content
    setTimeout(() => {
        container.style.transition = "opacity 0.8s ease-out, filter 0.8s ease-out";
        container.style.opacity = "0";
        container.style.filter = "blur(10px)";
        
        setTimeout(() => {
            container.classList.add("hidden");
            
            // Phase C: Reveal UI Elements
            if (loginSection) loginSection.classList.remove('hidden');
            if (header) header.classList.remove('hidden');
            if (footer) footer.classList.remove('hidden');
            
            // Phase D: Final Loaded State
            document.body.classList.add('loaded');
        }, 800);
    }, 2800); // Allow sufficient time for the user to read the logo (readability fix)
});

// UI Helper: Log out with confirmation if needed
function confirmLogout() {
    return confirm("Are you sure you want to log out?");
}
