/* Krys & Tell — Web Push helper
   - Requires a user gesture to request permission (we bind to a button)
   - Stores subscription in Laravel via POST /push/subscribe
*/

(function () {
  function $(sel) {
    return document.querySelector(sel);
  }

  function getCSRF() {
    const t = document.querySelector('meta[name="csrf-token"]');
    return t ? t.getAttribute('content') : '';
  }

  function getVapidPublicKey() {
    const m = document.querySelector('meta[name="vapid-public-key"]');
    return m ? (m.getAttribute('content') || '') : '';
  }

  // Convert URL-safe base64 to Uint8Array (for pushManager.subscribe)
  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
      .replace(/-/g, '+')
      .replace(/_/g, '/');

    const raw = window.atob(base64);
    const outputArray = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; ++i) outputArray[i] = raw.charCodeAt(i);
    return outputArray;
  }

  async function getRegistration() {
    if (!('serviceWorker' in navigator)) throw new Error('Service Worker not supported');
    // Ensure SW is registered (layout registers on load)
    return await navigator.serviceWorker.ready;
  }

  async function getExistingSubscription() {
    const reg = await getRegistration();
    return await reg.pushManager.getSubscription();
  }

  async function subscribe() {
    const vapidPublicKey = getVapidPublicKey();
    if (!vapidPublicKey) throw new Error('Missing VAPID public key');

    if (!('PushManager' in window)) throw new Error('Push not supported');

    const perm = await Notification.requestPermission();
    if (perm !== 'granted') throw new Error('Notifications permission denied');

    const reg = await getRegistration();
    const sub = await reg.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
    });

    await fetch('/push/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCSRF(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ subscription: sub }),
    });

    return sub;
  }

  async function unsubscribe() {
    const sub = await getExistingSubscription();
    if (!sub) return;

    try {
      await fetch('/push/unsubscribe', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCSRF(),
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ endpoint: sub.endpoint }),
      });
    } catch (e) {
      // ignore
    }

    await sub.unsubscribe();
  }

  async function refreshButtonState(btn) {
    if (!btn) return;

    try {
      const sub = await getExistingSubscription();
      const granted = (typeof Notification !== 'undefined') && Notification.permission === 'granted';

      if (sub && granted) {
        btn.dataset.enabled = '1';
        btn.title = 'Push notifications enabled (tap to disable)';
        btn.classList.add('kt-push-enabled');
        // icon swap if present
        const i = btn.querySelector('i');
        if (i) {
          i.classList.remove('fa-bell');
          i.classList.add('fa-bell');
        }
      } else {
        btn.dataset.enabled = '0';
        btn.title = 'Enable push notifications';
        btn.classList.remove('kt-push-enabled');
      }
    } catch (e) {
      btn.dataset.enabled = '0';
    }
  }

  function toast(type, title, body) {
    if (window.KTToast && typeof window.KTToast.show === 'function') {
      window.KTToast.show(type || 'info', title || '', body || '', 2500);
      return;
    }
    // fallback
    alert((title ? title + '\n' : '') + (body || ''));
  }

  function bindButton(btn) {
    if (!btn) return;

    refreshButtonState(btn);

    btn.addEventListener('click', async () => {
      try {
        btn.disabled = true;

        if (btn.dataset.enabled === '1') {
          await unsubscribe();
          toast('info', 'Notifications', 'Push notifications disabled.');
        } else {
          await subscribe();
          toast('success', 'Notifications', 'Push notifications enabled!');
        }

        await refreshButtonState(btn);
      } catch (e) {
        toast('danger', 'Notifications', e.message || 'Failed to enable push notifications.');
      } finally {
        btn.disabled = false;
      }
    });
  }

  window.KTPush = {
    bind: function (selector) {
      bindButton($(selector));
    },
    subscribe,
    unsubscribe,
  };
})();
