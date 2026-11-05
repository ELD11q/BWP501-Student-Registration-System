/*
 * auth.js - used by login.html and register.html
 *  1) Shows error/success messages that the PHP handlers send back in the URL (?error=... / ?success=...)
 *  2) Client-side validation (empty fields, email format, password strength, password confirmation)
 */
(function () {
    'use strict';

    const MESSAGES = {
        // login errors
        invalid: 'Invalid username/email or password. Please try again.',
        login_required: 'Please log in to access that page.',
        // register errors
        empty: 'Please fill in all fields.',
        username: 'Username must be 3+ characters (letters, numbers, underscore).',
        email: 'Please enter a valid email address.',
        weak: 'Password must be at least 8 characters and include a letter and a number.',
        mismatch: 'Passwords do not match.',
        username_taken: 'This username is already taken.',
        email_taken: 'An account with this email already exists.',
        // success
        registered: 'Account created successfully! You can now log in.',
        logged_out: 'You have been logged out.'
    };

    // ---- 1) Messages from the URL ----
    const params = new URLSearchParams(window.location.search);
    const box = document.getElementById('message');
    const error = params.get('error');
    const success = params.get('success');
    if (box && (error || success)) {
        box.textContent = MESSAGES[error || success] || 'Something went wrong.';
        box.classList.remove('d-none');
        box.classList.add(error ? 'alert-danger' : 'alert-success');
    }

    // ---- 2) Validation helpers ----
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function setValid(input, ok, message) {
        input.classList.toggle('is-invalid', !ok);
        input.classList.toggle('is-valid', ok);
        if (message) {
            const fb = input.parentElement.querySelector('.invalid-feedback');
            if (fb) fb.textContent = message;
        }
        return ok;
    }

    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const login = document.getElementById('login');
            const pass = document.getElementById('password');
            const ok = [
                setValid(login, login.value.trim() !== ''),
                setValid(pass, pass.value !== '')
            ].every(Boolean);
            if (!ok) e.preventDefault();
        });
    }

    // Register form
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const pass = document.getElementById('password');
        const confirm = document.getElementById('confirm_password');

        const checks = {
            username: () => setValid(username, /^[A-Za-z0-9_]{3,100}$/.test(username.value.trim())),
            email: () => setValid(email, emailRe.test(email.value.trim())),
            password: () => setValid(pass, pass.value.length >= 8 && /[A-Za-z]/.test(pass.value) && /[0-9]/.test(pass.value)),
            confirm: () => setValid(confirm, confirm.value !== '' && confirm.value === pass.value)
        };

        username.addEventListener('input', checks.username);
        email.addEventListener('input', checks.email);
        pass.addEventListener('input', function () {
            checks.password();
            if (confirm.value) checks.confirm();
        });
        confirm.addEventListener('input', checks.confirm);

        regForm.addEventListener('submit', function (e) {
            const ok = Object.values(checks).map(fn => fn()).every(Boolean);
            if (!ok) e.preventDefault();
        });
    }
})();
