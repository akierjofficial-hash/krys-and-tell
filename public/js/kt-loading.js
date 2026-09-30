(() => {
    'use strict';

    const page = document.createElement('div');
    page.className = 'kt-nav-skeleton';
    page.hidden = true;
    page.setAttribute('aria-hidden', 'true');
    document.body.append(page);
    let navigationTimer;
    let navigationResults;
    let activeButton;

    const line = (width = '100%', extra = '') => `<span class="kt-skeleton-line ${extra}" style="width:${width}"></span>`;
    const row = () => `<div class="kt-skeleton-row">${line('85%')}${line('68%')}${line('73%')}${line('56%')}</div>`;
    const card = () => `<div class="kt-skeleton-card">${line('48%')}${line('32%', 'kt-skeleton-title')}${line('72%')}</div>`;
    const field = () => `<div class="kt-skeleton-field">${line()}${line()}</div>`;

    function kindFor(url) {
        const path = url.pathname.toLowerCase();
        if (path.includes('dashboard') || path.endsWith('/admin') || path.endsWith('/staff')) return 'dashboard';
        if (/\/(create|edit|record-entry|new)(\/|$)/.test(path)) return 'form';
        if (/\/patients\/\d+(\/|$)/.test(path)) return 'profile';
        return 'table';
    }

    function showPage(kind = 'table') {
        const region = document.querySelector('.kt-staff .content, .admin-app #adminMain');
        if (!region) return;
        const topbar = region.querySelector('.staff-topbar, .admin-topbar');
        const rect = region.getBoundingClientRect();
        page.style.left = `${Math.max(0, rect.left)}px`;
        page.style.top = `${Math.max(0, topbar?.getBoundingClientRect().bottom ?? rect.top)}px`;
        const body = kind === 'dashboard'
            ? `<div class="kt-skeleton-cards">${Array.from({length: 6}, card).join('')}</div><div class="kt-skeleton-panel">${Array.from({length: 4}, row).join('')}</div>`
            : kind === 'form' || kind === 'profile'
                ? `<div class="kt-skeleton-panel"><div class="kt-skeleton-form">${Array.from({length: 8}, field).join('')}</div></div>${kind === 'profile' ? `<div class="kt-skeleton-panel">${Array.from({length: 3}, row).join('')}</div>` : ''}`
                : `<div class="kt-skeleton-panel">${Array.from({length: 7}, row).join('')}</div>`;
        page.innerHTML = `<div class="kt-nav-skeleton__inner">${line('60%', 'kt-skeleton-title')}${line('80%', 'kt-skeleton-subtitle')}${body}</div>`;
        page.hidden = false;
    }

    function hidePage() {
        clearTimeout(navigationTimer);
        page.hidden = true;
        navigationResults?.remove();
        navigationResults = null;
    }

    function showResults() {
        const target = document.querySelector('[data-live-results]');
        if (!target) return;
        const host = target.tagName === 'TBODY' ? (target.closest('.table-wrap') || target.parentElement) : target;
        host.classList.add('kt-loading-host');
        navigationResults = document.createElement('div');
        navigationResults.className = 'kt-results-skeleton';
        navigationResults.setAttribute('aria-hidden', 'true');
        navigationResults.innerHTML = Array.from({length: 4}, row).join('');
        host.append(navigationResults);
    }

    function schedulePage(url) {
        hidePage();
        const sameList = url.pathname === location.pathname && !!document.querySelector('[data-live-results]');
        navigationTimer = setTimeout(() => sameList ? showResults() : showPage(kindFor(url)), 180);
    }

    function actionText(form, button) {
        const words = `${form.action} ${button?.textContent || ''}`.toLowerCase();
        if (words.includes('import')) return 'Importing…';
        if (words.includes('payment') || words.includes('receipt')) return 'Recording…';
        if (words.includes('approve')) return 'Approving…';
        if (words.includes('decline')) return 'Declining…';
        if (words.includes('delete') || words.includes('destroy') || words.includes('remove')) return 'Deleting…';
        if (words.includes('save') || words.includes('store') || words.includes('update')) return 'Saving…';
        return 'Processing…';
    }

    function button(button, label = 'Processing…') {
        if (!button || button.dataset.ktLoading === '1') return () => {};
        const isInput = button instanceof HTMLInputElement;
        const original = isInput ? button.value : button.innerHTML;
        button.dataset.ktLoading = '1';
        button.setAttribute('aria-busy', 'true');
        button.setAttribute('aria-disabled', 'true');
        if (isInput) button.value = label;
        else button.innerHTML = `<span class="kt-action-spinner" aria-hidden="true"></span>${label}`;
        const restore = () => {
            if (isInput) button.value = original;
            else button.innerHTML = original;
            delete button.dataset.ktLoading;
            button.removeAttribute('aria-busy');
            button.removeAttribute('aria-disabled');
        };
        activeButton = restore;
        return restore;
    }

    window.KTLoading = {button, showPage, hidePage, prepareNavigation: url => schedulePage(new URL(url, location.href))};
    window.KTLoader = {show: () => schedulePage(new URL(location.href)), hide: hidePage};

    document.addEventListener('click', event => {
        const confirm = event.target.closest('#ktConfirmYes');
        if (!confirm) return;
        if (confirm.dataset.ktLoading === '1') {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }
        button(confirm, 'Processing…');
    }, true);

    document.addEventListener('change', event => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.files?.length) return;
        const triggerId = input.id.replace(/File$/, 'Btn');
        const trigger = document.getElementById(triggerId)
            || (input.id === 'patientImportFile' ? document.getElementById('patientImportButton') : null);
        if (trigger) button(trigger, 'Importing…');
    });

    document.addEventListener('click', event => {
        const anchor = event.target.closest('a[href]');
        if (!anchor || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (anchor.hasAttribute('download') || anchor.hasAttribute('data-no-loader') || anchor.hasAttribute('data-bs-toggle')) return;
        if (anchor.target && anchor.target !== '_self') return;
        const url = new URL(anchor.href, location.href);
        if (!['http:', 'https:'].includes(url.protocol) || url.origin !== location.origin) return;
        if (/(?:\.(?:pdf|csv|xlsx|zip)$|\/(?:download|export|print)(?:\/|$))/i.test(url.pathname)) return;
        if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
        schedulePage(url);
    });

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.matches('[data-live-search], [data-no-loader], .approval-form') || form.closest('#approvalPopover') || event.defaultPrevented) return;
        if (form.dataset.ktSubmitting === '1') { event.preventDefault(); return; }
        if ((form.getAttribute('method') || 'GET').toUpperCase() === 'GET') {
            schedulePage(new URL(form.action || location.href, location.href));
            return;
        }
        form.dataset.ktSubmitting = '1';
        const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
        const restore = button(submitter, actionText(form, submitter));
        setTimeout(() => {
            if (event.defaultPrevented) {
                delete form.dataset.ktSubmitting;
                restore();
            }
        }, 0);
    });

    window.addEventListener('pageshow', () => { hidePage(); activeButton?.(); activeButton = null; document.querySelectorAll('form[data-kt-submitting]').forEach(form => delete form.dataset.ktSubmitting); });
    window.addEventListener('pagehide', hidePage);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) hidePage(); });
})();
