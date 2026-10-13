# Paper bags — five products, 30 supplied WebP images

Release: 2026-10-13. The owner requested local preparation and will pull/deploy hosting.

## After pull/deploy

Deploy `wp-content/mu-plugins/vpn-paper-bags-20261013-import.php` and the **entire** `wp-content/mu-plugins/vpn-paper-bags-20261013/` directory. This is a fixed MU migration; it requires no plugin activation and no Product Sample Deploy action.

The first uncached WordPress request imports and publishes the five products after all content, image and SEO readbacks pass. Opening `/wp-admin/` after deployment triggers PHP even when public pages are served from a CDN cache. Ordinary requests do nothing after the completion marker has been written.

Required existing dependencies: WooCommerce, Rank Math with its `updateMeta` REST route, Paper Bags with Logo category (`paper-bags-with-logo`), the `how-to-make-paper-bags-stronger` post and Contact page. Records and internal links are resolved by slug, never by local database ID. SEO values are saved via Rank Math's own sanitizer route; WooCommerce product writes use its CRUD API.

Expected production URLs:

- `/product/custom-twisted-handle-kraft-paper-bags/`
- `/product/custom-flat-handle-kraft-paper-bags/`
- `/product/custom-brown-kraft-grocery-bags/`
- `/product/custom-white-kraft-paper-bags-twisted-handles/`
- `/product/custom-die-cut-handle-kraft-paper-bags/`

Each product has one featured image, five gallery images, three inline images, six visible FAQ answers with FAQPage microdata, 21 specifications, a unique primary keyword and supplied Rank Math title/description. All 30 images are original 450×570 WebP files under 100 KB. No local hostname or local IDs are stored in deployed content.

## Recovery and preservation

The migration runs only on hopgiayvpn.com/www or localhost/127.0.0.1 and accepts no HTTP-selected payload. A database lock prevents concurrent imports. Errors remain visible to administrators and retry after five minutes. Source and image hashes are validated before import. An unrelated slug/SKU conflict stops the migration; no existing unrelated product is overwritten.

Completed products, including later owner edits or unpublishing, are preserved. An interrupted draft may resume only while its generated content hash and expected title match; existing image attachments are reused by the batch's private asset marker. Product and media records are prepared before publication. An existing local import is recognized without duplicating its 30 images.

The importer temporarily selects an existing administrator only while executing this fixed migration, then restores the original request user in `finally`. It does not add visitor roles or expose an import endpoint. Cache purge hooks cover the five product URLs and the product/category archive; a separately cached Cloudflare page may still need the deployment's normal cache purge.

Removing the new MU importer disables future attempts but does not delete already imported products or images. Those remain manageable in WordPress normally.

## Verified locally

The five local products are 8933–8937; images 8938–8967. HTTP, titles, descriptions, 30 image URLs, internal links, one H1 per page, Product JSON-LD and FAQ markup passed. The deployment lifecycle simulation passed fresh production-style import, repeat, edit/unpublish preservation, slug conflict, interrupted resume without duplicate media, lock/host guards and visitor identity restoration.

Local remains intentionally noindex. After the owner deploys hosting, check production robots and self-canonicals, plus rendered title/meta, images and schema. This release has not been deployed or verified on hosting by Codex.

Local review: http://localhost/hopgiayvpn-main/outputs/paper-bags-20261013/review.html
