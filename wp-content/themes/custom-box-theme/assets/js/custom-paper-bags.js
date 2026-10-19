(function () {
    'use strict';
    var form = document.getElementById('custom-paper-bags-quote-form');
    if (!form) return;
    var message = document.getElementById('cpb-form-status');
    var button = form.querySelector('[type="submit"]');
    var buttonText = button.innerHTML;
    var messages = (window.vpnPaperBagQuote || {}).messages || {};
    var sourceKey = 'vpn_paper_bag_attribution_v2';
    var receiptKey = 'vpn_paper_bag_receipts_v2';
    var sourceFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid'];
    var inFlight = false;
    var started = false;
    var receipts = [];
    function storage(method, key, value) {
        try {
            if (method === 'get') return JSON.parse(window.sessionStorage.getItem(key) || 'null');
            window.sessionStorage.setItem(key, JSON.stringify(value));
        } catch (e) { return null; }
    }
    receipts = storage('get', receiptKey) || [];
    if (!Array.isArray(receipts)) receipts = [];
    function track(event, params) {
        // gtag events work with the site's existing tags; no GTM container is required.
        var data = Object.assign({product_category: 'paper_bags', form_name: 'paper_bag_quote'}, params || {});
        if (typeof window.gtag === 'function') window.gtag('event', event, data);
    }
    function attribution() {
        var query = new URLSearchParams(window.location.search);
        var incoming = sourceFields.some(function (key) { return query.has(key) && query.get(key); });
        var saved = storage('get', sourceKey) || {};
        // A new campaign replaces the previous attribution set rather than mixing old click IDs.
        if (incoming) {
            saved = {};
            sourceFields.forEach(function (key) { saved[key] = (query.get(key) || '').slice(0, key.indexOf('utm_') === 0 ? 150 : 180); });
            try { var ref = new URL(document.referrer); saved.referrer_url = (ref.origin + ref.pathname).slice(0, 500); } catch (e) { saved.referrer_url = ''; }
            storage('set', sourceKey, saved);
        }
        if (!saved.referrer_url && document.referrer) {
            try { var firstRef = new URL(document.referrer); saved.referrer_url = (firstRef.origin + firstRef.pathname).slice(0, 500); storage('set', sourceKey, saved); } catch (e) {}
        }
        sourceFields.concat(['referrer_url']).forEach(function (key) { if (form.elements[key]) form.elements[key].value = saved[key] || ''; });
    }
    function status(code, fallback) {
        message.hidden = false;
        message.dataset.status = code;
        message.textContent = messages[code] || fallback || messages.failed || 'Please try again or email sales.vpn@hopgiayvpn.com.';
    }
    function busy(active) {
        inFlight = active;
        button.disabled = active;
        button.innerHTML = active ? 'Sending your request…' : buttonText;
        form.setAttribute('aria-busy', active ? 'true' : 'false');
    }
    function validate() {
        var file = form.elements.artwork.files && form.elements.artwork.files[0];
        form.elements.artwork.setCustomValidity('');
        if (file && (file.size > 10 * 1024 * 1024 || !/\.(png|pdf|jpe?g|webp|docx?|gif|psd|cdr|eps)$/i.test(file.name))) {
            form.elements.artwork.setCustomValidity(messages.file || 'Use a supported file no larger than 10MB.');
        }
        var hasSize = ['length', 'width', 'depth'].some(function (key) { return form.elements[key].value; });
        form.elements.unit.setCustomValidity(hasSize && !form.elements.unit.value ? 'Please choose the unit for your dimensions.' : '');
        var invalid = form.querySelector(':invalid');
        if (invalid) {
            var details = invalid.closest('details');
            if (details) details.open = true;
            form.reportValidity();
            return false;
        }
        return true;
    }
    form.addEventListener('input', function (event) { event.target.setCustomValidity && event.target.setCustomValidity(''); });
    form.addEventListener('change', function (event) { event.target.setCustomValidity && event.target.setCustomValidity(''); });
    form.addEventListener('focusin', function () {
        document.body.classList.add('cpb-form-focused');
        if (!started) { track('paper_bag_form_start'); started = true; }
    });
    form.addEventListener('focusout', function () {
        window.setTimeout(function () { if (!form.contains(document.activeElement)) document.body.classList.remove('cpb-form-focused'); }, 0);
    });
    document.querySelectorAll('[data-bag-type]').forEach(function (link) {
        link.addEventListener('click', function () {
            form.elements.product_name.value = link.dataset.bagType;
            track('paper_bag_select_style', {bag_style: link.dataset.bagType});
        });
    });
    document.querySelectorAll('[data-track]').forEach(function (element) {
        element.addEventListener('click', function () { track(element.dataset.track, {cta_location: element.closest('section,header,nav')?.id || 'navigation'}); });
    });
    document.querySelectorAll('a[href="#quote"]').forEach(function (link) {
        link.addEventListener('click', function () {
            var heading = document.getElementById('cpb-quote-title');
            heading.setAttribute('tabindex', '-1');
            heading.focus({preventScroll: true});
        });
    });
    attribution();
    if (window.IntersectionObserver) {
        new IntersectionObserver(function (entries) {
            document.body.classList.toggle('cpb-form-in-view', entries[0].isIntersecting);
        }).observe(document.getElementById('quote'));
    }
    if (!window.fetch || !window.FormData || !window.AbortController) return; // Native POST remains available.
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (inFlight || !validate()) return;
        attribution();
        var data = new FormData(form);
        data.set('response_format', 'paper_bag_json');
        var controller = new AbortController();
        var timer = window.setTimeout(function () { controller.abort(); }, 45000);
        busy(true);
        status('pending', 'Sending your request…');
        try {
            var response = await fetch(form.getAttribute('action'), {method: 'POST', body: data, credentials: 'same-origin', headers: {'Accept': 'application/json'}, signal: controller.signal});
            var result = await response.json();
            if (!response.ok || result.success !== true) {
                status(result.data && result.data.status || 'failed');
                track('paper_bag_form_error', {error_type: result.data && result.data.status || 'failed'});
                return;
            }
            var receipt = result.data && result.data.receipt;
            if (!/^vpn-quote-\d+$/.test(receipt || '') || !['success', 'received'].includes(result.data.status)) throw new Error('Unconfirmed receipt');
            status(result.data.status);
            if (!receipts.includes(receipt)) {
                // Conversion follows server-confirmed storage, including an email queued for retry.
                track('generate_lead', {quote_id: receipt});
                if (typeof window.gtag === 'function') window.gtag('event', 'conversion', {send_to: 'AW-18190091085/6FzwCNKm0NscEM2G2-FD', transaction_id: receipt});
                receipts.push(receipt);
                receipts = receipts.slice(-50);
                storage('set', receiptKey, receipts);
            }
            form.reset();
            attribution();
            form.elements['g-recaptcha-response'] && (form.elements['g-recaptcha-response'].value = '');
            message.focus();
        } catch (error) {
            status('connection', 'We could not confirm the response. Your request may have been received. Please email sales.vpn@hopgiayvpn.com before resending.');
            track('paper_bag_form_error', {error_type: 'connection'});
        } finally {
            window.clearTimeout(timer);
            busy(false);
        }
    });
}());
