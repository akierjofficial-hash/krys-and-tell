(function () {
    'use strict';

    document.addEventListener('submit', function (event) {
        if (event.defaultPrevented) return;
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.allowDuplicate === '1') return;
        if (form.dataset.submitting === '1') {
            event.preventDefault();
            return;
        }
        if (!form.checkValidity()) return;
        form.dataset.submitting = '1';
        const button = form.querySelector('button[type="submit"]');
        if (button && !button.closest('[data-no-loading]')) {
            button.dataset.originalLabel = button.innerHTML;
            button.classList.add('is-loading');
            button.setAttribute('aria-busy', 'true');
            window.setTimeout(function () { button.disabled = true; }, 0);
        }
    });

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-submitting="1"]').forEach(function (form) {
            delete form.dataset.submitting;
            const button = form.querySelector('button[aria-busy="true"]');
            if (!button) return;
            button.disabled = false;
            button.classList.remove('is-loading');
            button.removeAttribute('aria-busy');
            if (button.dataset.originalLabel) button.innerHTML = button.dataset.originalLabel;
        });
    });

    document.querySelectorAll('[title]').forEach(function (element) {
        if (!element.getAttribute('aria-label') && !element.textContent.trim()) {
            element.setAttribute('aria-label', element.getAttribute('title'));
        }
    });
})();
