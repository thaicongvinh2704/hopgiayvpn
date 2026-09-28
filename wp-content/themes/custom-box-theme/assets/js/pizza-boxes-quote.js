(function () {
    'use strict';
    var form = document.querySelector('.vpb-form[data-session-url]');
    if (!form || !window.fetch) return;
    var message = form.parentNode.querySelector('.vpb-form-status');
    var button = form.querySelector('button[type="submit"]');
    var sessionPromise = null;
    var validUntil = 0;
    var readyAt = 0;
    var pending = false;

    function notify(text, state) {
        if (!message) return;
        message.hidden = false;
        message.className = 'vpb-form-status quote-form-message vpb-form-status--' + state;
        message.textContent = text;
    }

    function setField(name, value) {
        var field = form.elements.namedItem(name);
        if (field) field.value = value;
    }

    function refreshSession() {
        if (sessionPromise) return sessionPromise;
        var controller = window.AbortController ? new AbortController() : null;
        var timeout = setTimeout(function () { if (controller) controller.abort(); }, 10000);
        sessionPromise = fetch(form.dataset.sessionUrl, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
            body: 'action=custom_box_pizza_quote_session',
            signal: controller ? controller.signal : undefined
        }).then(function (response) {
            if (!response.ok) throw new Error('Session unavailable.');
            return response.json();
        }).then(function (response) {
            var data = response.data;
            if (!response.success || !data || !data.nonce || !data.signature || data.context !== 'quote') {
                throw new Error('Session invalid.');
            }
            setField('custom_box_quote_nonce', data.nonce);
            setField('custom_box_form_started_at', data.started_at);
            setField('custom_box_form_context', data.context);
            setField('custom_box_form_signature', data.signature);
            // A refreshed timestamp must still satisfy the server's minimum age.
            readyAt = Date.now() + (Number(data.minimum_age || 1) + 1) * 1000;
            validUntil = Date.now() + 10 * 60 * 1000;
        }).finally(function () {
            clearTimeout(timeout);
            sessionPromise = null;
        });
        return sessionPromise;
    }

    // Fetch only when the buyer interacts with the form, keeping initial load light.
    form.addEventListener('focusin', function () {
        if (Date.now() >= validUntil) refreshSession().catch(function () {});
    });

    document.addEventListener('submit', function (event) {
        if (event.target !== form) return;
        if (!pending && Date.now() < validUntil && Date.now() >= readyAt) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        if (pending) return;
        pending = true;
        button.disabled = true;
        notify('Preparing your enquiry...', 'pending');
        var session = Date.now() < validUntil ? Promise.resolve() : refreshSession();
        session.then(function () {
            return new Promise(function (resolve) { setTimeout(resolve, Math.max(0, readyAt - Date.now())); });
        }).then(function () {
            pending = false;
            button.disabled = false;
            if (form.requestSubmit) form.requestSubmit(button);
            else if (form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }))) {
                HTMLFormElement.prototype.submit.call(form);
            }
        }).catch(function () {
            pending = false;
            button.disabled = false;
            notify('Your enquiry could not be submitted. Please try again or email sales.vpn@hopgiayvpn.com.', 'failed');
        });
    }, true);
}());
