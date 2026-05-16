(function () {
    'use strict';

    function activateFlipbook(preview) {
        var src = preview.getAttribute('data-flipbook-src');
        var title = preview.getAttribute('data-flipbook-title') || 'Catalog flipbook';

        if (!src || preview.classList.contains('is-live')) {
            return;
        }

        var iframe = document.createElement('iframe');
        iframe.src = src;
        iframe.title = title;
        iframe.loading = 'lazy';
        iframe.setAttribute('allowfullscreen', '');

        preview.textContent = '';
        preview.appendChild(iframe);
        preview.classList.add('is-live');
        preview.removeAttribute('role');
        preview.removeAttribute('tabindex');
        preview.removeAttribute('aria-label');
    }

    document.addEventListener('click', function (event) {
        var preview = event.target.closest('.catalog-flipbook-cover');

        if (preview) {
            activateFlipbook(preview);
        }
    });

    document.addEventListener('keydown', function (event) {
        if ('Enter' !== event.key && ' ' !== event.key) {
            return;
        }

        var preview = event.target.closest('.catalog-flipbook-cover');

        if (preview) {
            event.preventDefault();
            activateFlipbook(preview);
        }
    });
}());
