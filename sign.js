// Client-side validation for sign-up
document.addEventListener("DOMContentLoaded", function() {
    const signupForm = document.getElementById('signup-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm-password');
    const pwError = document.getElementById('pw-error');
    const contactInput = document.querySelector('input[name="contact_number"]');

    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                pwError.classList.remove('hidden');
                confirmPassword.classList.add('is-invalid');
            }
        });

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
    }
});