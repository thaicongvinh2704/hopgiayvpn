(function () {
    "use strict";

    const overlay = document.querySelector("[data-page-transition]");

    if (!overlay) {
        return;
    }

    const status = overlay.querySelector('[data-transition-status]');
    const dismiss = overlay.querySelector('[data-transition-dismiss]');
    const root = document.documentElement;
    const isInitialHomeLoad = root.classList.contains('vpn-initial-page-loading')
        || root.classList.contains('vpn-initial-page-loading-pending');
    let recoveryTimer = null;
    let stalledTimer = null;
    let frame = null;

    const clearInitialTimers = function () {
        window.clearTimeout(window.vpnInitialLoaderShowTimer);
        window.clearTimeout(window.vpnInitialLoaderSafetyTimer);
    };

    const showTransition = function () {
        window.clearTimeout(recoveryTimer);
        window.clearTimeout(stalledTimer);
        // Keep the overlay after widgets injected near the end of <body> so it
        // wins when a third-party widget also uses the browser's maximum layer.
        document.body.appendChild(overlay);
        overlay.hidden = false;
        overlay.classList.remove('is-stalled');
        dismiss.hidden = true;
        overlay.setAttribute("aria-hidden", "false");
        root.classList.add('vpn-page-transition-active');
        document.body.classList.add("vpn-page-transitioning");
        if (status) status.textContent = status.dataset.loadingText;
        window.cancelAnimationFrame(frame);
        frame = window.requestAnimationFrame(function () {
            overlay.classList.add("is-active");
        });
        // Restore the page if a download, cancelled navigation or stalled request
        // never replaces this document. Never trap the visitor behind the loader.
        recoveryTimer = window.setTimeout(hideTransition, 12000);
        stalledTimer = window.setTimeout(function () {
            overlay.classList.add('is-stalled');
            dismiss.hidden = false;
        }, 4000);
    };

    const hideTransition = function () {
        window.clearTimeout(recoveryTimer);
        window.clearTimeout(stalledTimer);
        window.cancelAnimationFrame(frame);
        overlay.classList.remove("is-active", "is-stalled");
        dismiss.hidden = true;
        overlay.setAttribute("aria-hidden", "true");
        document.body.classList.remove("vpn-page-transitioning");
        root.classList.remove(
            'vpn-initial-page-loading',
            'vpn-initial-page-loading-pending',
            'vpn-page-transition-active'
        );
        clearInitialTimers();

        overlay.hidden = true;
        if (status) status.textContent = '';
    };

    const isEligibleLink = function (link, event) {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey ||
            link.hasAttribute("download") ||
            link.closest("[data-no-page-transition], #wpadminbar") ||
            link.matches(".ajax_add_to_cart, .remove, [role='button']")
        ) {
            return false;
        }

        const baseTarget = document.querySelector('base[target]');
        const target = (link.getAttribute("target") || (baseTarget && baseTarget.target) || "").toLowerCase();
        if (target && target !== "_self") {
            return false;
        }

        let destination;
        try {
            destination = new URL(link.href, window.location.href);
        } catch (error) {
            return false;
        }

        if (
            destination.origin !== window.location.origin ||
            !/^https?:$/.test(destination.protocol) ||
            /\.(pdf|zip|rar|7z|png|jpe?g|webp|svg|gif|mp4|mp3|docx?|xlsx?|csv)$/i.test(destination.pathname) ||
            /\/(wp-admin|wp-login\.php)(\/|$)/i.test(destination.pathname) ||
            destination.searchParams.has('add-to-cart')
        ) {
            return false;
        }

        const sameDocument = destination.pathname === window.location.pathname
            && destination.search === window.location.search;

        if (sameDocument) {
            return false;
        }

        return destination.href !== window.location.href;
    };

    window.addEventListener("click", function (event) {
        const link = event.target instanceof Element ? event.target.closest("a[href]") : null;

        if (!link || !isEligibleLink(link, event)) {
            return;
        }

        // Let the browser navigate immediately; allow other handlers to cancel
        // gallery, AJAX and menu actions before deciding to show the overlay.
        queueMicrotask(function () {
            if (!event.defaultPrevented) showTransition();
        });
    });

    if (isInitialHomeLoad) {
        if (root.classList.contains('vpn-initial-page-loading')) {
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('vpn-page-transitioning');
            if (status) status.textContent = status.dataset.loadingText;
        }

        // A deferred script runs once the HTML is parsed. Do not wait for every
        // image, font and third-party request: reveal useful page content on the
        // next paint so the entrance animation cannot hold back LCP.
        window.requestAnimationFrame(hideTransition);
    }

    window.addEventListener("pageshow", function (event) {
        if (event.persisted || !isInitialHomeLoad) {
            hideTransition();
        }
    });
    window.addEventListener("pagehide", hideTransition);

    dismiss.addEventListener('click', hideTransition);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !overlay.hidden) {
            hideTransition();
        }
    });
}());
