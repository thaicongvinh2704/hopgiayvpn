(function () {
    'use strict';
    var root = document.querySelector('[data-hero-slideshow]');
    if (!root) return;
    var slides = Array.from(root.querySelectorAll('[data-hero-slide]'));
    var buttons = Array.from(root.querySelectorAll('[data-hero-select]'));
    var controls = root.querySelector('[data-hero-controls]');
    if (slides.length < 2 || !controls) return;

    var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    var paused = motion.matches;
    var hovered = false;
    var inView = false;
    var current = 0;
    var request = 0;
    var timer;
    slides.forEach(function (slide) { slide.hidden = false; });
    controls.hidden = false;

    function canRotate() {
        return !paused && !hovered && inView && !document.hidden && !root.contains(document.activeElement);
    }

    function schedule() {
        window.clearTimeout(timer);
        if (canRotate()) {
            timer = window.setTimeout(function () { show((current + 1) % slides.length, false); }, 5000);
        }
    }

    async function show(index, manual) {
        window.clearTimeout(timer);
        var latest = ++request;
        var img = slides[index].querySelector('img');
        // Decode the next image before switching; a slow/failed image leaves the current one visible.
        try {
            img.loading = 'eager';
            await img.decode();
        } catch (error) {
            if (latest === request) schedule();
            return;
        }
        if (latest !== request || (!manual && !canRotate())) return;
        slides.forEach(function (slide, i) {
            slide.classList.toggle('is-active', i === index);
            slide.setAttribute('aria-hidden', i === index ? 'false' : 'true');
            buttons[i].setAttribute('aria-pressed', i === index ? 'true' : 'false');
        });
        current = index;
        schedule();
    }

    buttons.forEach(function (button, index) {
        button.addEventListener('click', function () { show(index, true); });
    });
    root.addEventListener('mouseenter', function () { hovered = true; schedule(); });
    root.addEventListener('mouseleave', function () { hovered = false; schedule(); });
    root.addEventListener('focusin', schedule);
    root.addEventListener('focusout', function () { window.setTimeout(schedule, 0); });
    document.addEventListener('visibilitychange', schedule);
    motion.addEventListener('change', function () { paused = motion.matches; schedule(); });
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            inView = entries[0].isIntersecting;
            schedule();
        }, { threshold: 0.15 }).observe(root);
    } else {
        inView = true;
        schedule();
    }
}());
