# Homepage hero slideshow — local review, 2 November 2026

Review: http://localhost/hopgiayvpn/

Three images rotate in the existing right-hand hero area: the original watch box, the owner's selected black shopping bag with silver VPN branding, and the approved pair of kraft bakery bags. Text, headings, quote buttons, navigation and other homepage sections were not edited for this change.

- Crossfade every 5 seconds, with three centred image-selection dots. The visible Play/Pause button was removed at the owner's request.
- Rotation waits while hovered, keyboard focus is inside, the tab is hidden or the slideshow is off screen. Reduced-motion preference disables automatic rotation by default.
- Images use `object-fit: contain` in a fixed-ratio stage. The next image decodes before the visible image changes; a load failure keeps the previous image visible.
- First image retains its responsive sources/high priority. The other images are lazy-loaded WebPs. The script and stylesheet load on the homepage only; no carousel library or database import.
- Without JavaScript, the original watch image remains visible.

Final image selection approved by the owner:

- Shopping bag: the owner's supplied `tải xuống.png`, preserving its design and existing alpha. Exported as `vpn-black-paper-bag-approved-480.webp` (12,806 bytes) and `vpn-black-paper-bag-approved-960.webp` (30,658 bytes).
- Bread bags: approved image `exec-accefedd-2f0c-4ff5-bbeb-bd37d289a367.png`, created with the built-in image_gen tool. Exported as `vpn-bread-paper-bags-approved-480.webp` (18,454 bytes) and `vpn-bread-paper-bags-approved-960.webp` (56,052 bytes).

Exports are included in `assets/images/home-hero/` so deployment does not depend on local media IDs. Both selected PNGs retain alpha transparency in their responsive WebP exports. Only proportional downscaling and encoding were applied to the selected designs. Alt text describes them as design illustrations; they are not presented as documentary factory or customer-order photographs. Earlier rejected image variants are not referenced by the template.

Selected source copies, SHA-256 checks and export verification are retained locally in `outputs/home-hero-slideshow-20261102/approved/`.

Validation: PHP and JavaScript syntax; 6 image URLs including responsive alternatives returned HTTP 200; 1 H1 retained; slideshow assets absent on Contact. Browser checks confirmed all three slides, automatic rotation, manual selection, automatic pausing during interaction, and stable stage dimensions. The final rendered version has three selection dots and no Play/Pause button. Stage stays within the page at 320, 390, 768 and 1280px, with uncropped images. An existing separate featured-bags navigation button extends past the page at 390px; it is outside this change.

Screenshots: `outputs/home-hero-slideshow-20261102/approved/`. Owner approved the final slideshow for Git release on 2 November 2026. Hosting deployment remains with the owner.

Deployment: pull and deploy the hero template, enqueue change, dedicated CSS/JS and all four `*-approved-*.webp` images together, then purge homepage and hosting/CDN caches. No database import or Product Sample Deploy operation is needed. The separately requested mobile-menu email change in `header.php` is also included in this release, placing `paperbox@hopgiayvpn.com` before `sales.vpn@hopgiayvpn.com` in both mobile menu variants. No hosting rendering is claimed before deployment.
