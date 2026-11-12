(function () {
    'use strict';

    const preferences = document.querySelector('[data-vpn-apollo-mode]');
    if (!preferences) return;

    const storageKey = 'vpn_apollo_company_tracking_v1';
    const status = preferences.querySelector('[data-vpn-apollo-status]');
    const isLive = preferences.dataset.vpnApolloMode === 'live' &&
        ['hopgiayvpn.com', 'www.hopgiayvpn.com'].includes(location.hostname);
    const campaignParams = new Set([
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'utm_id', 'gclid', 'gbraid', 'wbraid', 'msclkid', 'fbclid'
    ]);
    let choice;
    let started = false;

    try {
        choice = localStorage.getItem(storageKey);
    } catch (error) {
        // Keep the current page choice available when browser storage is blocked.
    }

    const showStatus = () => {
        status.textContent = choice === 'allow' ? 'Apollo is allowed.' :
            choice === 'deny' ? 'Apollo is declined.' : 'Apollo is off until you allow it.';
    };

    function startTracker() {
        if (started || !isLive || choice !== 'allow') return;
        // Keep unknown query values and fragments out of tracking.
        const url = new URL(location.href);
        if (
            url.protocol !== 'https:' || url.hash ||
            /\/(?:wp-admin|wp-login\.php|wp-json|cart|checkout|my-account)(?:\/|$)/i.test(url.pathname) ||
            [...url.searchParams.keys()].some(key => !campaignParams.has(key))
        ) return;
        if (document.querySelector('script[src^="https://assets.apollo.io/micro/website-tracker/"]')) return;

        started = true;
        // Vendor snippet supplied by the owner. The surrounding code controls when it runs.
        function initApollo(){var n=Math.random().toString(36).substring(7),o=document.createElement("script");
        o.src="https://assets.apollo.io/micro/website-tracker/tracker.iife.js?nocache="+n,o.async=!0,o.defer=!0,
        o.onload=function(){window.trackingFunctions.onLoad({appId:"6af43ca6b46ed2000c5998f1"})},
        document.head.appendChild(o)}initApollo();
    }

    function applyChoice(next) {
        if (next !== 'allow' && next !== 'deny') return;
        try {
            localStorage.setItem(storageKey, next);
        } catch (error) {
            status.textContent = 'Your browser blocked saving this choice. Please check your browser storage settings.';
            return;
        }
        choice = next;
        showStatus();
        if (started && next === 'deny') {
            // End the current document's tracker session after consent is withdrawn.
            location.reload();
        } else {
            startTracker();
        }
    }

    preferences.querySelectorAll('[data-vpn-apollo-choice]').forEach(button => {
        button.addEventListener('click', () => applyChoice(button.dataset.vpnApolloChoice));
    });
    window.addEventListener('storage', event => {
        if (event.key !== storageKey && event.key !== null) return;
        choice = event.newValue;
        showStatus();
        if (started && choice !== 'allow') location.reload();
    });

    preferences.hidden = false;
    showStatus();
    startTracker();
}());
