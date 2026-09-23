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
    const initialMinimumVisibleTime = 650;
    const navigationFallbackTime = 120;
    let recoveryTimer = null;
    let stalledTimer = null;
    let initialHideTimer = null;
    let navigationFrame = null;
    let navigationPaintFrame = null;
    let navigationFallbackTimer = null;
    let isLeavingPage = false;

    const clearInitialTimers = function () {
        window.clearTimeout(window.vpnInitialLoaderShowTimer);
        window.clearTimeout(window.vpnInitialLoaderSafetyTimer);
        window.clearTimeout(initialHideTimer);
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
        // WebKit can begin unloading before the next animation frame. Apply the
        // visible state synchronously so Safari never leaves the overlay at
        // opacity: 0 while the destination document is being requested.
        overlay.classList.add("is-active");
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

    const getEligibleDestination = function (link, event) {
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
            return null;
        }

        const baseTarget = document.querySelector('base[target]');
        const target = (link.getAttribute("target") || (baseTarget && baseTarget.target) || "").toLowerCase();
        if (target && target !== "_self") {
            return null;
        }

        let destination;
        try {
            destination = new URL(link.href, window.location.href);
        } catch (error) {
            return null;
        }

        if (
            destination.origin !== window.location.origin ||
            !/^https?:$/.test(destination.protocol) ||
            /\.(pdf|zip|rar|7z|png|jpe?g|webp|svg|gif|mp4|mp3|docx?|xlsx?|csv)$/i.test(destination.pathname) ||
            /\/(wp-admin|wp-login\.php)(\/|$)/i.test(destination.pathname) ||
            destination.searchParams.has('add-to-cart')
        ) {
            return null;
        }

        const sameDocument = destination.pathname === window.location.pathname
            && destination.search === window.location.search;

        if (sameDocument) {
            return null;
        }

        return destination.href !== window.location.href ? destination : null;
    };

    const navigateAfterPaint = function (destination) {
        let navigationCommitted = false;

        const navigate = function () {
            if (navigationCommitted) {
                return;
            }

            navigationCommitted = true;
            window.clearTimeout(navigationFallbackTimer);
            window.cancelAnimationFrame(navigationFrame);
            window.cancelAnimationFrame(navigationPaintFrame);
            isLeavingPage = true;
            window.location.assign(destination.href);
        };

        // Two animation frames guarantee one painted loader frame in WebKit.
        // The timeout keeps navigation reliable in background/throttled tabs.
        navigationFrame = window.requestAnimationFrame(function () {
            navigationPaintFrame = window.requestAnimationFrame(navigate);
        });
        navigationFallbackTimer = window.setTimeout(navigate, navigationFallbackTime);
    };

    window.addEventListener("click", function (event) {
        const link = event.target instanceof Element ? event.target.closest("a[href]") : null;
        const destination = link ? getEligibleDestination(link, event) : null;

        if (!destination) {
            return;
        }

        // By the time a bubbling event reaches window, target/document handlers
        // have had an opportunity to cancel AJAX, gallery and menu actions.
        // Briefly hold native navigation so Safari can paint the loader once.
        event.preventDefault();
        showTransition();
        navigateAfterPaint(destination);
    });

    if (isInitialHomeLoad) {
        if (root.classList.contains('vpn-initial-page-loading')) {
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('vpn-page-transitioning');
            if (status) status.textContent = status.dataset.loadingText;
        }

        const now = window.performance && window.performance.now
            ? window.performance.now()
            : Date.now();
        const shownAt = Number(window.vpnInitialLoaderShownAt || 0);
        const remainingVisibleTime = shownAt
            ? Math.max(0, initialMinimumVisibleTime - (now - shownAt))
            : 0;

        // Fast visits still skip the loader. Once it has become visible, keep it
        // on screen long enough to be perceived instead of flashing for 1 frame.
        if (shownAt && root.classList.contains('vpn-initial-page-loading')) {
            initialHideTimer = window.setTimeout(hideTransition, remainingVisibleTime);
        } else {
            window.requestAnimationFrame(hideTransition);
        }
    }

    window.addEventListener("pageshow", function (event) {
        isLeavingPage = false;
        if (event.persisted || !isInitialHomeLoad) {
            hideTransition();
        }
    });
    window.addEventListener("pagehide", function () {
        // Keep the transition visible while Safari waits for the next document.
        // pageshow removes it before a bfcache-restored page is painted again.
        if (!isLeavingPage) {
            hideTransition();
        }
    });

    dismiss.addEventListener('click', hideTransition);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !overlay.hidden) {
            hideTransition();
        }
    });
}());
