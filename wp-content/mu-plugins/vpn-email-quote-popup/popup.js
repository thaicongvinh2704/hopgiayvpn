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
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (form.dataset.sending || !form.reportValidity()) return;
    form.dataset.sending = '1'; const button = form.querySelector('[type="submit"]'); button.disabled = true; button.textContent = 'Sending…'; status.hidden = true;
    const controller = new AbortController(), timeout = setTimeout(() => controller.abort(), 25000);
    try {
      const tokenResponse = await fetch(cfg.tokenUrl, { credentials: 'same-origin', cache: 'no-store', signal: controller.signal });
      const token = await tokenResponse.json(); if (!tokenResponse.ok || !token.success) throw new Error('token');
      const data = new FormData(form);
      data.set('custom_box_quote_nonce', token.data.nonce); data.set('custom_box_form_started_at', token.data.started); data.set('custom_box_form_signature', token.data.signature);
      data.set('current_page_url', location.origin + location.pathname);
      data.set('product_name', (document.querySelector('h1')?.textContent.trim() || 'Custom packaging enquiry').slice(0, 200));
      try { const ref = new URL(document.referrer); data.set('referrer_url', ref.origin + ref.pathname); } catch (_) {}
      const query = new URLSearchParams(location.search);
      for (const field of ['utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid']) if (query.has(field)) data.set(field, query.get(field).slice(0, 180));
      const response = await fetch(form.getAttribute('action'), { method: 'POST', credentials: 'same-origin', body: data, signal: controller.signal });
      const result = await response.json();
      if (!response.ok || !result.success || !/^vpn-quote-\d+$/.test(result.data?.receipt || '')) {
        const messages = { missing: 'Please enter a valid email address.', duplicate: 'This request was already received. Our team will contact you.', rate_limited: 'Please wait a few minutes and try again.' };
        throw new Error(messages[result.data?.status] || 'We could not confirm your request. Please try again or email sales.vpn@hopgiayvpn.com.');
      }
      // Only a confirmed private record counts as received. Never store contact details in the browser.
      state.captured = true;
      try { persist(); } catch (_) {}
      document.cookie = cookie + '=1; Max-Age=31536000; Path=' + cfg.cookiePath + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
      form.querySelectorAll('label,details,.vep-submit,.vep-contact-notice').forEach(node => { node.hidden = true; node.style.display = 'none'; });
      form.reset(); dialog.classList.add('vep-saved');
      dialog.querySelector('h2').textContent = 'Thank you — request received.';
      dialog.querySelector('.vep-intro p').textContent = 'Our team will contact you about your packaging quote.';
      status.hidden = false; status.textContent = 'Your request has been saved.';
      dialog.querySelector('.vep-not-now').textContent = 'Close';
      window.dispatchEvent(new CustomEvent('vpn:quote-received', { detail: { source: 'website_email_popup', receipt: result.data.receipt } }));
    } catch (error) {
      status.hidden = false; status.textContent = error.message === 'token' || error.name === 'AbortError' || error instanceof TypeError || error instanceof SyntaxError ? 'Connection interrupted. Please try again or email sales.vpn@hopgiayvpn.com.' : error.message;
    } finally { clearTimeout(timeout); delete form.dataset.sending; button.disabled = false; button.textContent = 'Request a quote'; }
  });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', vpnStartEmailPopup, { once: true });
else vpnStartEmailPopup();
