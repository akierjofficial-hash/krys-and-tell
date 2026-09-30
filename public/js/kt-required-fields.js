(() => {
    'use strict';

    function labelsFor(field) {
        if (field.labels?.length) return [...field.labels];
        if (field.closest('label')) return [field.closest('label')];
        let parent = field.parentElement;
        for (let depth = 0; parent && depth < 3; depth++, parent = parent.parentElement) {
            const controls = parent.querySelectorAll('input:not([type="hidden"]), select, textarea');
            const labels = [...parent.children].filter(child => child.tagName === 'LABEL');
            if (controls.length === 1 && labels.length === 1) return labels;
        }
        return [];
    }

    function refresh(root = document) {
        root.querySelectorAll('label.kt-required-label').forEach(label => {
            const field = label.control || label.querySelector('input, select, textarea')
                || (label.parentElement?.querySelectorAll('input:not([type="hidden"]), select, textarea').length === 1
                    ? label.parentElement.querySelector('input:not([type="hidden"]), select, textarea') : null);
            if (field && !field.disabled && field.type !== 'hidden' && (field.required || field.getAttribute('aria-required') === 'true')) return;
            label.classList.remove('kt-required-label');
            label.querySelectorAll('.kt-required-mark').forEach(mark => mark.remove());
        });
        root.querySelectorAll('input[required], select[required], textarea[required], [aria-required="true"]').forEach(field => {
            if (field.type === 'hidden' || field.disabled) return;
            labelsFor(field).forEach(label => {
                label.classList.add('kt-required-label');
                if (label.querySelector('.kt-required-mark') || label.textContent.includes('*')) return;
                const mark = document.createElement('span');
                mark.className = 'kt-required-mark';
                mark.setAttribute('aria-hidden', 'true');
                mark.textContent = '*';
                const caption = label.querySelector('.re-field-caption');
                if (caption) caption.append(mark);
                else if (label.contains(field) && !['checkbox', 'radio'].includes(field.type)) field.before(mark);
                else label.append(mark);
            });
        });
    }

    window.KTRequiredFields = {refresh};
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => refresh(), {once:true});
    else refresh();
    document.addEventListener('kt:live-search:updated', event => refresh(event.detail.target));
    document.addEventListener('shown.bs.modal', event => refresh(event.target));
    document.addEventListener('change', event => {
        const form = event.target.closest('form');
        if (form) refresh(form);
    });
})();
