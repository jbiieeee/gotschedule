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

// Login AJAX Handling
document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
            
            const formData = new FormData(loginForm);
            formData.append('login', '1');
            formData.append('ajax', '1');

            try {
                const response = await fetch('main.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    window.notifier.success('Access Granted', result.message);
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1200);
                } else {
                    window.notifier.error('Login Failed', result.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                window.notifier.error('Network Error', 'Something went wrong. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }

    // Password Visibility Toggle
    document.querySelectorAll('.btn-password-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = btn.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
                window.notifier.info('Peak Mode', 'Be careful of who is watching!');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });
});
