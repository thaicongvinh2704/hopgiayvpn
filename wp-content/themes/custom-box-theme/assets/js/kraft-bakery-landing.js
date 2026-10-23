(function () {
    'use strict';
    const form = document.getElementById('kb-quote-form'), cfg = window.vpnKraftBakery;
    if (!form || !cfg) return;
    const button = form.querySelector('[type="submit"]'), status = document.getElementById('kb-status');
    const success = document.getElementById('kb-success');
    let session = null, preparing = null, pending = null, sending = false, started = false;
    const errors = {
        invalid_email: 'Please enter a valid email address.', email_required: 'Please enter your email address.',
        rate_limited: 'Please wait a moment before sending another request.',
        new_chat_unavailable: 'Our inbox is temporarily unavailable. Your details are still here; please try again shortly.',
        storage_unavailable: 'We could not confirm receipt. Your details are still here; please try again shortly.'
    };
    const attributionFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid'];
    let attribution = {};
    try {
        const query = new URLSearchParams(location.search), key = 'vpn-kraft-bakery-attribution';
        attribution = JSON.parse(sessionStorage.getItem(key) || '{}');
        if (!attribution || typeof attribution !== 'object' || Array.isArray(attribution)) attribution = {};
        if (attributionFields.some(field => query.get(field))) {
            attribution = {};
            attributionFields.forEach(field => { if (query.get(field)) attribution[field] = query.get(field).slice(0, 180); });
            sessionStorage.setItem(key, JSON.stringify(attribution));
        }
    } catch (_) {}
    function track(name, values) {
        if (!cfg.measure || typeof window.gtag !== 'function') return;
        try { window.gtag('event', name, Object.assign({ form_name: 'kraft_bakery_wholesale', product_category: 'paper_bags' }, values)); } catch (_) {}
    }
    async function request(url, payload, signal, csrf) {
        if (new URL(url, location.href).origin !== location.origin) throw new Error('unavailable');
        const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
        if (csrf) headers['X-VPN-CSRF'] = csrf;
        const response = await fetch(url, {method: 'POST', credentials: 'same-origin', cache: 'no-store', headers, body: JSON.stringify(payload), signal});
        const result = await response.json();
        if (!response.ok) { const error = new Error('request_failed'); error.code = result.code; throw error; }
        return result;
    }
    function prepare() {
        if (session && Date.now() - session.at < 240000) return Promise.resolve(session);
        if (preparing) return preparing;
        preparing = (async () => {
            const controller = new AbortController(), timer = setTimeout(() => controller.abort(), 12000);
            try {
                const result = await request(cfg.bootstrap, {}, controller.signal);
                if (typeof result.csrf !== 'string' || !result.csrf) throw new Error('unavailable');
                session = {csrf: result.csrf, at: Date.now()};
                return session;
            } finally { clearTimeout(timer); }
        })().finally(() => { preparing = null; });
        return preparing;
    }
    form.addEventListener('focusin', () => {
        if (cfg.available) prepare().catch(() => {});
        if (!started) { started = true; track('paper_bag_form_start'); }
    });
    document.querySelectorAll('[data-kb-style]').forEach(link => link.addEventListener('click', () => {
        form.elements.style.value = link.dataset.kbStyle;
        track('paper_bag_select_style', {bag_style: link.dataset.kbStyle});
        document.getElementById('kb-quote-title').setAttribute('tabindex', '-1');
        document.getElementById('kb-quote-title').focus({preventScroll: true});
    }));
    // Keep the site's established mobile bar, with its quote action on this landing.
    const mobileQuote = document.querySelector('.mobile-conversion-bar__quote');
    if (mobileQuote) mobileQuote.setAttribute('href', '#quote');
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (sending || !form.reportValidity()) return;
        status.hidden = true;
        if (!cfg.available) {
            status.textContent = 'Our quote inbox is temporarily unavailable. Please use the contact options below.';
            status.hidden = false; return;
        }
        const selected = form.elements.style.value;
        const detail = ['Form: Bread, bakery & SOS wholesale quote', 'Bag style: ' + selected,
            'Estimated quantity: ' + form.elements.quantity.value,
            'Delivery country: ' + (form.elements.country.value || 'To be confirmed'),
            'Size and unit: ' + (form.elements.size.value.trim() || 'To be confirmed'),
            'Project details: ' + form.elements.message.value.trim()].join('\n');
        const payload = {email: form.elements.email.value.trim(), message: detail.slice(0, 1000),
            product_title: 'Bread, bakery and SOS paper bags — wholesale enquiry', source_path: location.pathname,
            guard: form.elements.vpn_quote_guard.value, metadata: {}};
        for (const key of ['utm_source', 'utm_medium', 'utm_campaign']) {
            if (typeof attribution[key] === 'string') payload.metadata[key] = attribution[key].slice(0, 100);
        }
        const fingerprint = JSON.stringify(payload);
        if (!pending || pending.fingerprint !== fingerprint) {
            const bytes = crypto.getRandomValues(new Uint8Array(16));
            pending = {fingerprint, id: 'bakery-' + Array.from(bytes, value => value.toString(16).padStart(2, '0')).join('')};
        }
        payload.client_message_id = pending.id;
        const controller = new AbortController(), timer = setTimeout(() => controller.abort(), 25000);
        sending = true; button.disabled = true; button.textContent = 'Sending…'; form.setAttribute('aria-busy', 'true');
        try {
            let active = await prepare(), result;
            try { result = await request(cfg.inbox, payload, controller.signal, active.csrf); }
            catch (error) {
                if (!['session_expired', 'csrf_required'].includes(error.code)) throw error;
                session = null; active = await prepare(); result = await request(cfg.inbox, payload, controller.signal, active.csrf);
            }
            if (result.saved !== true || !/^vpn-inbox-[a-f0-9-]{36}-\d+$/.test(result.receipt || '')) throw new Error('unconfirmed');
            // Only a committed inbox receipt counts as a lead. No email or form text enters analytics.
            track('generate_lead', {quote_id: result.receipt, bag_style: selected, quantity_band: form.elements.quantity.value});
            if (cfg.measure && /^AW-\d+\/[A-Za-z0-9_-]+$/.test(cfg.adsGoal || '') && typeof window.gtag === 'function') {
                try { window.gtag('event', 'conversion', {send_to: cfg.adsGoal, transaction_id: result.receipt}); } catch (_) {}
            }
            document.cookie = 'vpn_quote_received_' + cfg.popupScope + '=1; Max-Age=31536000; Path=' + cfg.cookiePath + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
            form.hidden = true; success.hidden = false;
            document.getElementById('kb-quote-title').textContent = "We've received your email.";
            document.querySelector('.kb-quote > p:not(.product-eyebrow)').hidden = true;
            success.querySelector('.kb-success-email').textContent = 'Reply to: ' + payload.email;
            success.focus({preventScroll: true});
            window.dispatchEvent(new CustomEvent('vpn:quote-received', {detail: {source: 'kraft_bakery_wholesale', receipt: result.receipt, id: result.id}}));
            pending = null;
        } catch (error) {
            status.textContent = errors[error.code] || 'We could not confirm receipt yet. Your details are still here. Please try again; the same request will not be duplicated.';
            status.hidden = false; track('paper_bag_form_error', {error_type: error.code || 'connection'});
        } finally {
            clearTimeout(timer); sending = false; button.disabled = false; button.innerHTML = 'Request my bag quote <span aria-hidden="true">→</span>'; form.setAttribute('aria-busy', 'false');
        }
    });
}());
