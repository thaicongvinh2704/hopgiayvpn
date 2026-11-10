(function () {
    'use strict';
    var root = document.querySelector('[data-pizza-gallery]');
    if (!root) return;
    var track = root.querySelector('[data-pizza-track]');
    var slides = Array.from(root.querySelectorAll('[data-pizza-slide]'));
    var dots = Array.from(root.querySelectorAll('[data-pizza-select]'));
    if (!track || slides.length < 2 || dots.length !== slides.length) return;
    root.querySelector('[data-pizza-controls]').hidden = false;
    var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    var current = 0, timer, scrolling, inView = false, hovered = false, manual = false, drag = null;
    function schedule() {
        clearTimeout(timer);
        if (!manual && !motion.matches && inView && !hovered && !document.hidden && !root.contains(document.activeElement)) {
            timer = setTimeout(function () { show((current + 1) % slides.length, false); }, 5000);
        }
    }
    function sync(index) {
        current = Math.max(0, Math.min(index, slides.length - 1));
        dots.forEach(function (dot, i) { dot.setAttribute('aria-pressed', i === current ? 'true' : 'false'); });
        slides.forEach(function (slide, i) { slide.setAttribute('aria-hidden', i === current ? 'false' : 'true'); });
    }
    async function show(index, user) {
        if (user) manual = true;
        clearTimeout(timer);
        var image = slides[index].querySelector('img');
        try { image.loading = 'eager'; if (image.decode) await image.decode(); } catch (error) { schedule(); return; }
        track.scrollTo({ left: index * track.clientWidth, behavior: motion.matches ? 'auto' : 'smooth' });
        sync(index); schedule();
    }
    dots.forEach(function (dot, i) { dot.addEventListener('click', function () { show(i, true); }); });
    track.addEventListener('keydown', function (event) {
        var next;
        if (event.key === 'ArrowRight') next = (current + 1) % slides.length;
        else if (event.key === 'ArrowLeft') next = (current + slides.length - 1) % slides.length;
        else if (event.key === 'Home') next = 0;
        else if (event.key === 'End') next = slides.length - 1;
        else return;
        event.preventDefault(); show(next, true);
    });
    track.addEventListener('scroll', function () {
        clearTimeout(scrolling);
        scrolling = setTimeout(function () { sync(Math.round(track.scrollLeft / track.clientWidth)); schedule(); }, 100);
    }, { passive: true });
    track.addEventListener('pointerdown', function (event) {
        manual = true; clearTimeout(timer);
        if (event.pointerType !== 'mouse' || event.button !== 0) return;
        drag = { x: event.clientX, left: track.scrollLeft, pointer: event.pointerId };
        track.setPointerCapture(event.pointerId); track.classList.add('is-dragging');
    });
    track.addEventListener('pointermove', function (event) {
        if (!drag) return;
        track.scrollLeft = drag.left + drag.x - event.clientX;
    });
    function finish(event) {
        if (!drag) return;
        var pointer = drag.pointer; drag = null; track.classList.remove('is-dragging');
        if (track.hasPointerCapture(pointer)) track.releasePointerCapture(pointer);
        show(Math.max(0, Math.min(slides.length - 1, Math.round(track.scrollLeft / track.clientWidth))), true);
    }
    track.addEventListener('pointerup', finish); track.addEventListener('pointercancel', finish);
    track.addEventListener('lostpointercapture', finish);
    root.addEventListener('mouseenter', function () { hovered = true; schedule(); });
    root.addEventListener('mouseleave', function () { hovered = false; schedule(); });
    root.addEventListener('focusin', schedule);
    root.addEventListener('focusout', function () { setTimeout(schedule, 0); });
    document.addEventListener('visibilitychange', schedule); motion.addEventListener('change', schedule);
    if ('IntersectionObserver' in window) new IntersectionObserver(function (entries) { inView = entries[0].isIntersecting; schedule(); }, { threshold: 0.2 }).observe(root);
    if ('ResizeObserver' in window) new ResizeObserver(function () { track.scrollTo({ left: current * track.clientWidth, behavior: 'instant' }); }).observe(track);
}());
