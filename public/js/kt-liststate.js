/*
 * Krys&Tell — List State + Return URL helpers
 *
 * Goals:
 * - Keep client-side filters (q/sort/tab/etc.) synced to the URL query string
 *   so browser back/forward + refresh restore state.
 * - Inject `return=<current list url>` into action links and forms to enable
 *   "Facebook/YouTube back" behavior via server-side redirects.
 *
 * Usage (per page):
 *   KTListState.bindInput('#search', 'q');
 *   KTListState.bindSelect('#sort', 'sort');
 *   KTListState.bindTabs('[data-kt-tab]', 'tab', { defaultValue: 'cash' });
 *
 * Mark elements to receive return URL injection:
 *   <a data-kt-return href="...">Edit</a>
 *   <form data-kt-return ...> ... </form>
 *   <tr data-kt-href="/staff/..."> ... </tr>
 */
(function () {
    function debounce(fn, wait) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function toUrl(url) {
        try {
            return new URL(url, window.location.origin);
        } catch (_) {
            return null;
        }
    }

    function setQueryParamOnUrl(urlStr, key, value) {
        const u = toUrl(urlStr);
        if (!u) return urlStr;

        if (value === null || value === undefined || value === '') {
            u.searchParams.delete(key);
        } else {
            u.searchParams.set(key, String(value));
        }

        // preserve hash
        // Return relative if original was relative
        const rel = u.pathname + (u.search ? u.search : '') + (u.hash ? u.hash : '');
        // If original looks absolute (starts with http), return absolute
        if (/^https?:\/\//i.test(urlStr)) return u.toString();
        return rel;
    }

    function replaceUrlParams(patch) {
        const u = new URL(window.location.href);
        Object.keys(patch || {}).forEach((k) => {
            const v = patch[k];
            if (v === null || v === undefined || v === '') u.searchParams.delete(k);
            else u.searchParams.set(k, String(v));
        });
        window.history.replaceState(null, '', u.toString());
    }

    const KTListState = {
        params() {
            return new URLSearchParams(window.location.search);
        },

        // On list pages: returns current full URL.
        // On nested pages (show/edit/create): returns ?return if present, else current.
        currentReturnValue() {
            const p = this.params();
            return p.get('return') || window.location.href;
        },

        setParam(key, value) {
            replaceUrlParams({ [key]: value });
            this.injectReturn();
        },

        setParams(patch) {
            replaceUrlParams(patch);
            this.injectReturn();
        },

        bindInput(selectorOrEl, key, opts = {}) {
            const el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl;
            if (!el) return;

            const p = this.params();
            const initial = p.get(key);
            if (initial !== null && initial !== undefined) {
                el.value = initial;
                // Let existing listeners run
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const wait = typeof opts.debounce === 'number' ? opts.debounce : 200;
            const handler = debounce(() => {
                this.setParam(key, el.value.trim());
            }, wait);

            el.addEventListener('input', handler);
        },

        bindSelect(selectorOrEl, key, opts = {}) {
            const el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl;
            if (!el) return;

            const p = this.params();
            const initial = p.get(key);
            if (initial !== null && initial !== undefined) {
                el.value = initial;
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }

            el.addEventListener('change', () => {
                this.setParam(key, el.value);
            });
        },

        // Buttons must have data-kt-tab-value="cash" etc.
        bindTabs(buttonSelector, key, opts = {}) {
            const btns = Array.from(document.querySelectorAll(buttonSelector || '[data-kt-tab-value]'));
            if (!btns.length) return;

            const activeClass = opts.activeClass || 'active';
            const defaultValue = opts.defaultValue || btns[0].getAttribute('data-kt-tab-value');
            const p = this.params();
            const initial = p.get(key) || defaultValue;

            const activate = (val, updateUrl = true) => {
                btns.forEach((b) => {
                    const v = b.getAttribute('data-kt-tab-value');
                    if (v === val) b.classList.add(activeClass);
                    else b.classList.remove(activeClass);
                });
                if (typeof opts.onActivate === 'function') opts.onActivate(val);
                if (updateUrl) this.setParam(key, val);
            };

            // Initial activation without re-setting URL if it's already present
            activate(initial, !(p.get(key) !== null));

            btns.forEach((b) => {
                b.addEventListener('click', (e) => {
                    e.preventDefault();
                    const v = b.getAttribute('data-kt-tab-value');
                    if (!v) return;
                    activate(v, true);
                });
            });
        },

        injectReturn(root) {
            const scope = root || document;
            const returnValue = this.currentReturnValue();

            // Links
            scope.querySelectorAll('a[data-kt-return]').forEach((a) => {
                const href = a.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                a.setAttribute('href', setQueryParamOnUrl(href, 'return', returnValue));
            });

            // Generic elements that store a base href in data-kt-href
            scope.querySelectorAll('[data-kt-href]').forEach((el) => {
                const base = el.getAttribute('data-kt-href');
                if (!base) return;
                const next = setQueryParamOnUrl(base, 'return', returnValue);
                // For row-click patterns
                el.setAttribute('data-href', next);
                if (el.tagName && el.tagName.toLowerCase() === 'a') {
                    el.setAttribute('href', next);
                }
            });

            // Forms
            scope.querySelectorAll('form[data-kt-return]').forEach((f) => {
                let input = f.querySelector('input[name="return"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'return';
                    f.appendChild(input);
                }
                input.value = returnValue;
            });
        },
    };

    window.KTListState = KTListState;

    document.addEventListener('DOMContentLoaded', () => {
        // Always keep return-injection fresh on first load.
        KTListState.injectReturn();
    });
})();
