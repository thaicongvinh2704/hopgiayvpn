# Approved category images — pull/deploy release

The owner approved all 38 local category card images, including five completely new v2 designs, for hosting publication on 2026-10-10.

## Deployment

Pull `main` and deploy the committed files, including `wp-content/mu-plugins/`, the theme `inc/setup.php` and `assets/images/category-studio-20261010/`.

The MU plugin is automatically loaded by WordPress. It replaces the theme category-group image URLs with the fixed approved manifest. The homepage cards and WooCommerce category image helper use this same mapping. No Media Library import, database migration or Product Sample Deploy button is needed.

All 38 committed images are 450 × 570 px WebP below 100,000 bytes each. Five approved filenames end in `-v2.webp`; the previous five versions are excluded from this release. The manifest records filenames, dimensions, byte counts and SHA-256 hashes. Full-resolution working files, original backups and local review tools remain outside this commit.

Image URLs are generated from the active theme URL, so local URLs do not leak onto hosting. An absent manifest or image leaves the previous theme URL intact. Unlisted categories and individual product photos are unchanged.

## Cache and verification

On the first uncached WordPress request, the plugin requests one LiteSpeed purge for this release when LiteSpeed's purge hook is registered. It records completion in `vpn_category_studio_cache_release`. All image files must exist first. Reference: [LiteSpeed purge API](https://docs.litespeedtech.com/lscache/lscwp/api/#litespeed_purge_all).

Opening `/wp-admin/` after deploy triggers WordPress even when public pages are cached. A public page served entirely by a CDN does not execute PHP. An independent Cloudflare HTML cache is not cleared by this package; if it serves stale HTML, purge those pages using the hosting/CDN deployment cache step. New image filenames avoid collisions with the old site's images.

After deploy, check the homepage category grid and `/products/` and confirm the image paths contain `category-studio-20261010/`, with `-v2.webp` for Folding Carton, Magnetic Closure, Lid and Base, Beauty and Skincare, and Supplement. Check an image returns HTTP 200. Hosting deployment and live visual verification must be reported separately from a successful Git push.

## Rollback

Revert this release or remove `vpn-category-studio.php` to use previous category URLs. No term thumbnails or product records are overwritten, and original media files remain intact.
