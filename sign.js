// Client-side validation & AJAX for sign-up
document.addEventListener("DOMContentLoaded", function() {
    const signupForm = document.getElementById('signup-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm-password');
    const pwError = document.getElementById('pw-error');
    const contactInput = document.querySelector('input[name="contact_number"]');

    if (signupForm) {
        // Real-time password validation
        [password, confirmPassword].forEach(input => {
            input.addEventListener('input', () => {
                if (password.value === confirmPassword.value) {
                    pwError.classList.add('hidden');
                    confirmPassword.classList.remove('is-invalid');
                } else if (confirmPassword.value !== '') {
                    pwError.classList.remove('hidden');
                }
            });
        });

        // Contact number restriction (digits only)
        if (contactInput) {
            contactInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 11);
            });
        }

        // AJAX Registration
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Re-check passwords
            if (password.value !== confirmPassword.value) {
                pwError.classList.remove('hidden');
                confirmPassword.classList.add('is-invalid');
                window.notifier.error('Mismatch', 'Passwords do not match.');
                return;
            }

            const submitBtn = signupForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
            
            const formData = new FormData(signupForm);
            formData.append('register', '1');
            formData.append('ajax', '1');

            try {
                const response = await fetch('sign.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    window.notifier.success('Welcome!', result.message);
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 2000);
                } else {
                    window.notifier.error('Registration Failed', result.message);
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