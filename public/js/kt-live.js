/*!
 * KT Live (AJAX "realtime" via snapshot polling + soft reload)
 * - Polls a lightweight JSON snapshot endpoint.
 * - Reloads the page only when data changes (count/max(updated_at) key changed).
 * - Preserves search/filter inputs + scroll position across reloads.
 */
(function () {
  'use strict';

  const KTLive = {};
  const LS_PREFIX = 'kt_live:';
  const STATE_TTL_MS = 2 * 60 * 1000; // 2 minutes

  function now() { return Date.now(); }

  function getCsrf() {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function safeJsonParse(str) {
    try { return JSON.parse(str); } catch (_) { return null; }
  }

  function stateKey() {
    return LS_PREFIX + 'state:' + (location.pathname + location.search);
  }

  function scrollKey() {
    return LS_PREFIX + 'scroll:' + (location.pathname + location.search);
  }

  function isInteractiveFocus() {
    const el = document.activeElement;
    if (!el) return false;
    const tag = (el.tagName || '').toLowerCase();
    if (tag === 'input' || tag === 'textarea' || tag === 'select') return true;
    if (el.isContentEditable) return true;
    return false;
  }

  function preserveState() {
    const data = { t: now(), fields: {} };

    // Save common filter inputs/selects (only visible + with id or name)
    const els = Array.from(document.querySelectorAll('input, select, textarea'))
      .filter(el => {
        const type = (el.getAttribute('type') || '').toLowerCase();
        if (type === 'password' || type === 'hidden' || type === 'file') return false;
        if (el.disabled) return false;
        // store if it looks like a filter UI (has id or name)
        return !!(el.id || el.name);
      });

    for (const el of els) {
      const key = el.id ? ('#' + el.id) : ('[name="' + el.name + '"]');
      const type = (el.getAttribute('type') || '').toLowerCase();
      if (type === 'checkbox' || type === 'radio') {
        data.fields[key] = { kind: 'check', v: !!el.checked };
      } else {
        data.fields[key] = { kind: 'value', v: String(el.value ?? '') };
      }
    }

    try {
      localStorage.setItem(stateKey(), JSON.stringify(data));
      localStorage.setItem(scrollKey(), JSON.stringify({ t: now(), y: window.scrollY || 0 }));
    } catch (_) {}
  }

  function restoreState() {
    const raw = localStorage.getItem(stateKey());
    const parsed = safeJsonParse(raw || '');
    if (!parsed || !parsed.fields || !parsed.t) return;

    if ((now() - Number(parsed.t || 0)) > STATE_TTL_MS) return;

    // Restore fields
    for (const sel of Object.keys(parsed.fields)) {
      const entry = parsed.fields[sel];
      const el = document.querySelector(sel);
      if (!el) continue;

      if (entry.kind === 'check') {
        el.checked = !!entry.v;
      } else if (entry.kind === 'value') {
        el.value = entry.v ?? '';
      }

      // Trigger listeners (your pages rely on input/change)
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Restore scroll
    const sraw = localStorage.getItem(scrollKey());
    const s = safeJsonParse(sraw || '');
    if (s && s.t && (now() - Number(s.t || 0)) <= STATE_TTL_MS) {
      requestAnimationFrame(() => window.scrollTo(0, Number(s.y || 0)));
    }
  }

  function toast(type, title, msg) {
    if (window.KTToast && typeof window.KTToast.show === 'function') {
      window.KTToast.show(type, title, msg, 2200);
      return;
    }

    // Minimal fallback (no bootstrap JS needed)
    const wrapId = 'kt-live-toast-wrap';
    let wrap = document.getElementById(wrapId);
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.id = wrapId;
      wrap.style.position = 'fixed';
      wrap.style.right = '14px';
      wrap.style.bottom = '14px';
      wrap.style.zIndex = '9999';
      wrap.style.maxWidth = '340px';
      document.body.appendChild(wrap);
    }

    const el = document.createElement('div');
    el.style.background = 'rgba(15,23,42,.92)';
    el.style.color = '#fff';
    el.style.borderRadius = '14px';
    el.style.padding = '10px 12px';
    el.style.marginTop = '10px';
    el.style.boxShadow = '0 18px 40px rgba(0,0,0,.25)';
    el.style.fontFamily = 'system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif';
    el.style.fontSize = '13px';
    el.innerHTML = `<div style="font-weight:800;margin-bottom:2px;">${escapeHtml(title || 'Info')}</div><div style="opacity:.92">${escapeHtml(msg || '')}</div>`;
    wrap.appendChild(el);

    setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(6px)'; }, 1800);
    setTimeout(() => { el.remove(); }, 2400);
  }

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }

  KTLive.watch = function watch(opts) {
    const scope = String(opts.scope || '').trim();
    const snapshotUrl = String(opts.snapshotUrl || '').trim();
    const intervalMs = Math.max(2500, Number(opts.intervalMs || 8000));

    if (!scope || !snapshotUrl) return;

    let lastKey = null;
    let polling = false;

    async function poll() {
      if (polling) return;
      if (document.hidden) return;

      // avoid annoying reload while user is typing / selecting
      if (isInteractiveFocus()) return;

      polling = true;
      try {
        const url = snapshotUrl + (snapshotUrl.includes('?') ? '&' : '?') +
          'scope=' + encodeURIComponent(scope) + '&_=' + String(now());

        const res = await fetch(url, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrf()
          },
          cache: 'no-store'
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data || data.ok !== true) throw new Error('snapshot failed');

        const key = String(data.key || '');
        if (!key) return;

        if (lastKey === null) {
          lastKey = key;
          return;
        }

        if (key !== lastKey) {
          lastKey = key;

          preserveState();
          toast('info', 'Updated', 'New changes detected. Refreshing…');

          // Give the toast a moment to show
          setTimeout(() => location.reload(), 450);
          return;
        }
      } catch (_) {
        // silent
      } finally {
        polling = false;
      }
    }

    // initial restore (for the previous reload)
    restoreState();

    // start polling
    poll();
    setInterval(poll, intervalMs);

    // helpful global hook
    window.addEventListener('kt:live:refresh', () => poll());
  };

  KTLive.auto = function auto() {
    const body = document.body;
    if (!body) return;

    const scope = body.dataset.ktLiveScope || '';
    const snapshotUrl = body.dataset.ktLiveSnapshotUrl || '';
    const intervalMs = body.dataset.ktLiveInterval ? Number(body.dataset.ktLiveInterval) : 8000;

    if (!scope || !snapshotUrl) return;
    KTLive.watch({ scope, snapshotUrl, intervalMs });
  };

  // expose
  window.KTLive = KTLive;

  document.addEventListener('DOMContentLoaded', () => {
    try { KTLive.auto(); } catch (_) {}
  });
})();
