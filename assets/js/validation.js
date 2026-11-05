/*
 * validation.js - AJAX form validation for the Add / Update Student forms.
 *  - When a field loses focus, it is sent to validate_student.php and the error is shown under it.
 *  - On submit, the whole form is validated with AJAX first; it is only submitted if the server says it is valid.
 *  - The page never reloads to show validation errors.
 */
(function () {
    'use strict';

    const form = document.getElementById('studentForm');
    if (!form) return;

    const FIELDS = ['name', 'email', 'phone', 'address'];
    const alertBox = document.getElementById('formAlert');
    const submitBtn = document.getElementById('submitBtn');
    const spinner = submitBtn.querySelector('.spinner-border');
    const fileInput = document.getElementById('profile_picture');
    const preview = document.getElementById('picturePreview');

    function showError(field, message) {
        const input = form.elements[field];
        const fb = form.querySelector('.invalid-feedback[data-for="' + field + '"]');
        if (!input) return;
        if (message) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            if (fb) fb.textContent = message;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    }

    /** Sends the form text fields (not the file) to the AJAX validator. */
    function ajaxValidate(onlyField) {
        const data = new FormData();
        FIELDS.forEach(f => data.append(f, form.elements[f].value));
        if (form.elements.id) data.append('id', form.elements.id.value);
        if (onlyField) data.append('field', onlyField);

        return fetch('validate_student.php', {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(res => {
            if (res.status === 401) {
                window.location.href = 'login.html?error=login_required';
                throw new Error('unauthorised');
            }
            return res.json();
        });
    }

    // Real-time validation of each field when it loses focus (and while typing if already invalid)
    FIELDS.forEach(function (field) {
        const input = form.elements[field];
        let timer;
        const run = () => ajaxValidate(field)
            .then(r => showError(field, r.errors[field] || ''))
            .catch(() => {});
        input.addEventListener('blur', run);
        input.addEventListener('input', function () {
            if (!input.classList.contains('is-invalid')) return;
            clearTimeout(timer);
            timer = setTimeout(run, 400);
        });
    });

    // Client-side check + preview for the optional picture
    function checkFile() {
        const file = fileInput.files[0];
        if (!file) { fileInput.classList.remove('is-invalid'); return true; }
        const okType = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type);
        const okSize = file.size <= 2 * 1024 * 1024;
        const msg = !okType ? 'Only JPG, PNG, GIF or WEBP images are allowed.'
                  : !okSize ? 'Image must be 2 MB or smaller.' : '';
        showError('profile_picture', msg);
        return !msg;
    }
    fileInput.addEventListener('change', function () {
        if (checkFile() && fileInput.files[0]) {
            preview.src = URL.createObjectURL(fileInput.files[0]);
            preview.classList.remove('d-none');
        }
    });

    // Validate everything with AJAX before submitting
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        ajaxValidate().then(function (r) {
            FIELDS.forEach(f => showError(f, r.errors[f] || ''));
            const fileOk = checkFile();
            if (r.valid && fileOk) {
                alertBox.classList.add('d-none');
                form.submit();               // all good -> real submit (with the file)
                return;
            }
            alertBox.classList.remove('d-none');
            const firstBad = form.querySelector('.is-invalid');
            if (firstBad) firstBad.focus();
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }).catch(function () {
            // If AJAX fails, fall back to normal submit (server validates again anyway)
            form.submit();
        });
    });
})();
