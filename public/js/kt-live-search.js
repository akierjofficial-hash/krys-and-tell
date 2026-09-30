(function () {
    'use strict';

    function start(form) {
        const input = form.querySelector('[name="q"]');
        const target = document.querySelector(form.dataset.liveTarget || '[data-live-results]');
        const extra = form.dataset.liveExtra ? document.querySelector(form.dataset.liveExtra) : null;
        if (!input || !target) return;
        if (!input.hasAttribute('aria-label') && !input.labels?.length) {
            input.setAttribute('aria-label', input.placeholder || 'Search records');
        }

        const status = document.createElement('span');
        status.className = 'kt-live-search-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.setAttribute('aria-atomic', 'true');
        status.style.cssText = 'display:block;min-height:16px;font-size:11px;color:#64748b;margin:3px 0;';
        (target.tagName === 'TBODY' ? form : target).insertAdjacentElement(target.tagName === 'TBODY' ? 'afterend' : 'beforebegin', status);

        let timer;
        let controller;
        let sequence = 0;
        let skeletonTimer;
        let skeleton;

        function showResultsSkeleton() {
            if (skeleton) return;
            const host = target.tagName === 'TBODY'
                ? (target.closest('.table-wrap') || target.closest('table')?.parentElement || target.parentElement)
                : target;
            host.classList.add('kt-loading-host');
            skeleton = document.createElement('div');
            skeleton.className = 'kt-results-skeleton';
            skeleton.setAttribute('aria-hidden', 'true');
            skeleton.innerHTML = Array.from({length: 4}, () => '<div class="kt-skeleton-row"><span class="kt-skeleton-line" style="width:80%"></span><span class="kt-skeleton-line" style="width:65%"></span><span class="kt-skeleton-line" style="width:72%"></span><span class="kt-skeleton-line" style="width:55%"></span></div>').join('');
            host.append(skeleton);
            target.style.opacity = '0.72';
        }

        function hideResultsSkeleton() {
            clearTimeout(skeletonTimer);
            skeleton?.remove();
            skeleton = null;
        }

        function urlForForm() {
            const url = new URL(form.action || location.href, location.href);
            // Keep parameters set by separate tab, sort, and pagination controls.
            const current = new URL(location.href);
            if (current.pathname === url.pathname) url.search = current.search;
            url.searchParams.delete('page');
            form.querySelectorAll('[name]').forEach(field => url.searchParams.delete(field.name));
            new FormData(form).forEach((value, key) => {
                if (String(value) !== '') url.searchParams.append(key, String(value));
            });
            return url;
        }

        async function load(url, push) {
            clearTimeout(timer);
            controller?.abort();
            controller = new AbortController();
            const ticket = ++sequence;
            target.setAttribute('aria-busy', 'true');
            clearTimeout(skeletonTimer);
            if (!skeleton) skeletonTimer = setTimeout(showResultsSkeleton, 450);
            status.textContent = 'Searching…';

            try {
                const response = await fetch(url, {
                    signal: controller.signal,
                    credentials: 'same-origin',
                    headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest', 'X-KT-Live-Search': '1' },
                });
                if (response.redirected || [401, 403, 419].includes(response.status)) {
                    throw new Error('Your session ended or access changed. Sign in again to search these records.');
                }
                if (response.status === 429) throw new Error('Too many searches. Wait a moment, then retry.');
                if (!response.ok) throw new Error('Results are temporarily unavailable. Your filters have not changed; retry or refresh this page.');
                const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                const incoming = documentResult.querySelector(form.dataset.liveTarget || '[data-live-results]');
                if (!incoming) throw new Error('The results could not be read. Refresh this page and try again.');
                if (ticket !== sequence) return;
                target.innerHTML = incoming.innerHTML;
                if (extra) {
                    const newExtra = documentResult.querySelector(form.dataset.liveExtra);
                    if (newExtra) extra.innerHTML = newExtra.innerHTML;
                }
                if (form.closest('.patients-page')) {
                    for (const [containerSelector, linkSelector] of [
                        ['.patient-search', '.clear-search'],
                        ['.patient-filter-actions', 'a.pbtn'],
                    ]) {
                        const container = form.querySelector(containerSelector);
                        const oldLink = container?.querySelector(linkSelector);
                        const newLink = documentResult.querySelector(`.patients-page ${containerSelector} ${linkSelector}`);
                        oldLink?.remove();
                        if (newLink) container?.append(newLink);
                    }
                }
                if (push && location.href !== url.href) history.pushState({ ktLiveSearch: true }, '', url);
                const payments = form.closest('.payments-page');
                payments?.querySelectorAll('.tabs a').forEach(link => {
                    const tabUrl = new URL(link.href);
                    ['q', 'patient_id', 'date_from', 'date_to'].forEach(key => {
                        const value = url.searchParams.get(key);
                        if (value) tabUrl.searchParams.set(key, value);
                        else tabUrl.searchParams.delete(key);
                    });
                    link.href = tabUrl.href;
                });
                document.querySelectorAll('[data-live-keep-q], .patients-page .alphabet a').forEach(link => {
                    const linkUrl = new URL(link.href);
                    const term = url.searchParams.get('q');
                    if (term) linkUrl.searchParams.set('q', term);
                    else linkUrl.searchParams.delete('q');
                    link.href = linkUrl.href;
                });
                document.querySelectorAll('form:not([data-live-search]) input[type="hidden"][name="q"]').forEach(field => {
                    field.value = input.value;
                });
                if (window.KTListState) window.KTListState.injectReturn(target);
                status.textContent = 'Results updated';
                document.dispatchEvent(new CustomEvent('kt:live-search:updated', { detail: { target, url } }));
            } catch (error) {
                if (error.name !== 'AbortError' && ticket === sequence) {
                    status.textContent = error instanceof TypeError
                        ? 'Connection lost while searching. Check your connection and retry.'
                        : error.message;
                }
            } finally {
                if (ticket === sequence) {
                    hideResultsSkeleton();
                    target.removeAttribute('aria-busy');
                    target.style.opacity = '';
                }
            }
        }

        function queue() {
            clearTimeout(timer);
            controller?.abort();
            ++sequence;
            clearTimeout(skeletonTimer);
            target.setAttribute('aria-busy', 'true');
            status.textContent = 'Searching…';
            timer = setTimeout(() => load(urlForForm(), true), 120);
        }

        input.addEventListener('input', queue);
        form.addEventListener('submit', event => {
            event.preventDefault();
            load(urlForForm(), true);
        });
        window.addEventListener('popstate', () => {
            const url = new URL(location.href);
            input.value = url.searchParams.get('q') || '';
            form.querySelectorAll('input[type="hidden"], select, input[type="checkbox"]').forEach(field => {
                if (!field.name) return;
                if (field.type === 'checkbox') field.checked = url.searchParams.has(field.name);
                else if (url.searchParams.has(field.name)) field.value = url.searchParams.get(field.name);
                else if (field.tagName === 'SELECT') field.selectedIndex = 0;
                else field.value = '';
            });
            load(url, false);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[data-live-search]').forEach(start);
    });
})();
