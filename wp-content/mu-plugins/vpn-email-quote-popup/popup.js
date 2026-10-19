function vpnStartEmailPopup() {
  'use strict';
  const cfg = window.vpnEmailPopup, dialog = document.getElementById('vpn-email-popup');
  if (!cfg || !dialog || !dialog.showModal) return;
  const form = dialog.querySelector('form'), status = dialog.querySelector('.vep-status');
  const key = 'vpn-email-popup-' + cfg.scope, visitKey = key + '-visit', cookie = 'vpn_quote_received_' + cfg.scope;
  const DAY = 86400000, WINDOW = 30 * DAY;
  // wp_localize_script serializes a PHP boolean as "1" or an empty string.
  const preview = cfg.preview === true || cfg.preview === '1';
  let previewShown = false;
  let state, visit, busy = false, previousFocus, lastTick = Date.now(), ready = true;
  const read = (storage, name) => JSON.parse(storage.getItem(name) || 'null');
  const rollWindow = value => {
    const history = Array.isArray(value.shownAt) ? value.shownAt : Array(Math.min(2, Math.max(0, value.count))).fill(value.last);
    if (history.some(time => !Number.isFinite(time))) throw new Error('Invalid history');
    const shownAt = history.filter(time => Date.now() - time < WINDOW);
    return { ...value, shownAt, count: shownAt.length, last: shownAt.length ? Math.max(...shownAt) : 0 };
  };
  const persist = () => { localStorage.setItem(key, JSON.stringify(state)); sessionStorage.setItem(visitKey, JSON.stringify(visit)); };
  const received = () => document.cookie.split(';').some(item => item.trim() === cookie + '=1');
  try {
    state = read(localStorage, key) || { start: Date.now(), count: 0, last: 0, captured: false };
    visit = read(sessionStorage, visitKey) || { elapsed: 0, shown: false, engaged: false, active: Date.now() };
    if (!Number.isFinite(state.start) || !Number.isFinite(state.count) || !Number.isFinite(state.last) || !Number.isFinite(visit.elapsed) || !Number.isFinite(visit.active)) { dialog.dataset.vepStatus = 'invalid_storage'; return; }
    state = rollWindow(state);
    if (Date.now() - visit.active > 30 * 60000) visit = { elapsed: 0, shown: false, engaged: false, active: Date.now() };
    persist();
  } catch (_) { dialog.dataset.vepStatus = 'storage_unavailable'; return; } // No persistent storage: do not repeat the invitation on every page.
  const chatActive = () => sessionStorage.getItem('vpn-chat-active') === '1' || !!document.querySelector('#vpn-chat-panel:not([hidden])');
  const otherModalVisible = () => [...document.querySelectorAll('dialog[open], [aria-modal="true"]:not([hidden])')].some(element => element.getClientRects().length && getComputedStyle(element).visibility !== 'hidden' && element.getAttribute('aria-hidden') !== 'true');
  const blockedReason = () => {
    if (preview) {
      if (previewShown) return 'preview_complete';
      if (document.querySelector('#vpn-chat-panel:not([hidden])')) return 'chat_open';
      if (otherModalVisible()) return 'another_modal';
      if (document.hidden) return 'background_tab';
      return '';
    }
    if (received() || state.captured) return 'quote_received';
    if (visit.shown) return 'shown_this_visit';
    if (visit.engaged) return 'form_engaged';
    if (state.count >= 2) return 'limit_reached';
    if (state.last && Date.now() - state.last < DAY) return 'cooldown_24h';
    if (chatActive()) return 'chat_active';
    if (otherModalVisible()) return 'another_modal';
    if (document.hidden) return 'background_tab';
    return '';
  };
  const blocked = () => !!blockedReason();
  const diagnostic = () => { dialog.dataset.vepStatus = dialog.open ? 'open' : blockedReason() || (visit.elapsed < 30000 ? 'waiting_30s' : 'eligible'); dialog.dataset.vepSeconds = String(Math.floor(visit.elapsed / 1000)); };
  diagnostic();
  const close = () => { if (dialog.open) dialog.close(); };
  dialog.querySelector('.vep-close').addEventListener('click', close);
  dialog.querySelector('.vep-not-now').addEventListener('click', close);
  dialog.addEventListener('click', event => { if (event.target === dialog) { const rect = dialog.getBoundingClientRect(); if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) close(); } });
  dialog.addEventListener('close', () => { document.documentElement.classList.remove('vep-open'); if (previousFocus?.isConnected) previousFocus.focus({ preventScroll: true }); });
  document.addEventListener('input', event => {
    if (!dialog.contains(event.target) && event.target.closest('form') && !event.target.matches('[type="search"], [name="s"], [name="search"]')) {
      visit.engaged = true; try { persist(); } catch (_) { ready = false; }
    }
  });
  document.addEventListener('focusin', event => {
    if (!dialog.contains(event.target) && event.target.closest('form') && event.target.matches('textarea,select,input:not([type="search"]):not([name="s"]):not([type="hidden"])')) {
      visit.engaged = true; try { persist(); } catch (_) { ready = false; }
    }
  });
  window.addEventListener('storage', event => { if (event.key === key) { try { state = read(localStorage, key); if (!state || state.captured || state.last > Date.now() - 5000) close(); } catch (_) { ready = false; close(); } } });
  async function show() {
    if (!ready || blocked()) return;
    const attempt = () => {
      state = rollWindow(read(localStorage, key));
      if (!state || blocked()) return;
      previousFocus = document.activeElement;
      dialog.showModal(); document.documentElement.classList.add('vep-open');
      prepareInbox().catch(() => {});
      if (preview) { previewShown = true; }
      else { state.last = Date.now(); state.shownAt.push(state.last); state.count = state.shownAt.length; visit.shown = true; persist(); }
    };
    try {
      if (navigator.locks) await navigator.locks.request(key, { ifAvailable: true }, lock => { if (lock) attempt(); });
      else attempt();
    } catch (_) { ready = false; close(); }
  }
  setInterval(() => {
    const now = Date.now(), delta = Math.min(now - lastTick, 1500); lastTick = now;
    diagnostic();
    if (!ready || document.hidden) return;
    visit.elapsed += delta; visit.active = now;
    try { sessionStorage.setItem(visitKey, JSON.stringify(visit)); } catch (_) { ready = false; return; }
    if ((preview || visit.elapsed >= 30000) && !busy) { busy = true; show().finally(() => { busy = false; }); }
  }, 1000);
  document.addEventListener('visibilitychange', () => { lastTick = Date.now(); });
  let pendingQuote = null;
  let inboxSession = null, preparingInbox = null;
  const messages = {
    email_required: 'Please enter your email address.', invalid_email: 'Please enter a valid email address.',
    invalid_text: 'Please keep the project details within 1,000 characters.',
    rate_limited: 'Please wait a moment before sending another request.',
    session_expired: 'Your session expired. Please try sending again.', csrf_required: 'Please try sending again to refresh your session.',
    new_chat_unavailable: 'Our inbox is temporarily unavailable. Please try again shortly.',
    storage_unavailable: 'We could not save your request. Please try again shortly.'
  };
  async function inboxRequest(url, payload, signal, csrf) {
    if (new URL(url, location.href).origin !== location.origin) throw new Error('unavailable');
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (csrf) headers['X-VPN-CSRF'] = csrf;
    const response = await fetch(url, { method: 'POST', credentials: 'same-origin', headers, body: JSON.stringify(payload), signal });
    const result = await response.json();
    if (!response.ok) {
      const error = new Error(messages[result.code] || 'We could not save your request. Please try again shortly.');
      error.code = result.code;
      throw error;
    }
    return result;
  }
  async function prepareInbox() {
    if (inboxSession && Date.now() - inboxSession.readyAt < 5 * 60000) return inboxSession;
    if (preparingInbox) return preparingInbox;
    dialog.dataset.vepInboxReady = '0';
    preparingInbox = (async () => {
      const controller = new AbortController(), timeout = setTimeout(() => controller.abort(), 12000);
      try {
        let bootstrapUrl = cfg.bootstrapUrl, inboxUrl = cfg.inboxUrl;
        // Older cached HTML can still discover the endpoints without breaking delivery.
        if (!bootstrapUrl || !inboxUrl) {
          const response = await fetch(cfg.tokenUrl, { credentials: 'same-origin', cache: 'no-store', signal: controller.signal });
          const session = await response.json();
          if (!response.ok || !session.success || session.data?.delivery !== 'customer_inbox') throw new Error('unavailable');
          bootstrapUrl = session.data.bootstrap_url; inboxUrl = session.data.inbox_url;
        }
        const boot = await inboxRequest(bootstrapUrl, {}, controller.signal);
        if (typeof boot.csrf !== 'string' || !boot.csrf) throw new Error('unavailable');
        inboxSession = { inboxUrl, csrf: boot.csrf, readyAt: Date.now() };
        dialog.dataset.vepInboxReady = '1';
        return inboxSession;
      } finally { clearTimeout(timeout); }
    })().finally(() => { preparingInbox = null; });
    return preparingInbox;
  }
  form.addEventListener('focusin', () => { prepareInbox().catch(() => {}); });
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (form.dataset.sending || !form.reportValidity()) return;
    form.dataset.sending = '1'; const button = form.querySelector('[type="submit"]'); button.disabled = true; button.textContent = 'Sending…'; status.hidden = true;
    const controller = new AbortController(), timeout = setTimeout(() => controller.abort(), 25000);
    try {
      const metadata = {}, query = new URLSearchParams(location.search);
      for (const field of ['utm_source', 'utm_medium', 'utm_campaign']) if (query.has(field)) metadata[field] = query.get(field).slice(0, 100);
      const payload = { email: form.elements.email.value.trim(), message: form.elements.message.value.trim(),
        product_title: (document.querySelector('h1')?.textContent.trim() || '').slice(0, 160), source_path: location.pathname, metadata };
      const fingerprint = JSON.stringify(payload);
      if (!pendingQuote || pendingQuote.fingerprint !== fingerprint) pendingQuote = { fingerprint, client: 'popup-' + crypto.randomUUID().replaceAll('-', '') };
      payload.client_message_id = pendingQuote.client;
      let session = await prepareInbox();
      let result;
      try {
        result = await inboxRequest(session.inboxUrl, payload, controller.signal, session.csrf);
      } catch (error) {
        if (!['session_expired', 'csrf_required'].includes(error.code)) throw error;
        inboxSession = null;
        session = await prepareInbox();
        result = await inboxRequest(session.inboxUrl, payload, controller.signal, session.csrf);
      }
      if (result.saved !== true || !/^vpn-inbox-[a-f0-9-]{36}-\d+$/.test(result.receipt || '')) throw new Error('unavailable');
      // Receipt is returned only after the private inbox transaction commits.
      state.captured = true;
      try { persist(); } catch (_) {}
      document.cookie = cookie + '=1; Max-Age=31536000; Path=' + cfg.cookiePath + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
      form.querySelectorAll('label,details,.vep-submit,.vep-contact-notice').forEach(node => { node.hidden = true; node.style.display = 'none'; });
      form.reset(); pendingQuote = null; dialog.classList.add('vep-saved');
      const title = dialog.querySelector('h2');
      title.textContent = "We've received your email.";
      dialog.querySelector('.vep-intro p').textContent = 'Thank you for getting in touch. Our team will review your packaging enquiry and contact you as soon as possible.';
      dialog.querySelectorAll('.vep-success-mark,.vep-success-eyebrow,.vep-success-email').forEach(node => { node.hidden = false; });
      const confirmedEmail = dialog.querySelector('.vep-success-email strong');
      if (confirmedEmail) confirmedEmail.textContent = payload.email;
      status.hidden = false; status.textContent = 'Your email has been received. Our team will contact you as soon as possible.';
      dialog.querySelector('.vep-not-now').textContent = 'Continue browsing';
      dialog.querySelector('.vep-close').setAttribute('aria-label', 'Close confirmation');
      title.setAttribute('tabindex', '-1'); title.focus({ preventScroll: true });
      window.dispatchEvent(new CustomEvent('vpn:quote-received', { detail: { source: 'website_email_popup', receipt: result.receipt, id: result.id } }));
    } catch (error) {
      status.hidden = false;
      status.textContent = error.message === 'unavailable' || error instanceof SyntaxError ? 'We could not confirm your request. Please try again shortly.' : error.name === 'AbortError' || error instanceof TypeError ? 'Connection interrupted. Your details are still here; please try again.' : error.message;
    } finally { clearTimeout(timeout); delete form.dataset.sending; button.disabled = false; button.textContent = 'Request a quote'; }
  });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', vpnStartEmailPopup, { once: true });
else vpnStartEmailPopup();
