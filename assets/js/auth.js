// auth.js — JavaScript for login and register pages

document.addEventListener('DOMContentLoaded', function () {

    // ── Password show/hide toggle ──
    // Works for any button with class .toggle-password and data-target="<input id>"
    const toggleBtns = document.querySelectorAll('.toggle-password');

    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = 'Hide';
            } else {
                input.type = 'password';
                this.textContent = 'Show';
            }
        });
    });

    // ── Client-side email domain hint ──
    // Shows an inline warning if the user types a non-ashesi.edu.gh email
    const emailInput = document.getElementById('email');
    const emailHint = document.querySelector('.form-hint');

    if (emailInput && emailHint) {
        emailInput.addEventListener('blur', function () {
            const val = this.value.trim().toLowerCase();
            if (val !== '' && !val.endsWith('@ashesi.edu.gh')) {
                emailHint.textContent = 'Only @ashesi.edu.gh addresses are accepted.';
                emailHint.style.color = '#ef4444';
                this.classList.add('is-invalid');
            } else {
                emailHint.textContent = 'Must be an @ashesi.edu.gh address.';
                emailHint.style.color = '';
                this.classList.remove('is-invalid');
            }
        });
    }

});
