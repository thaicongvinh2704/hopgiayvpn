# Category-grid query optimization — 06/11/2026

The shared category grid on the homepage and custom-packaging manufacturer page performed repeated taxonomy lookups and Media Library searches for static theme images. This release reuses the existing request-level category resolver and skips attachment database queries for static theme assets when no plugin URL resolver is registered.

The fallback lookup remains available. Uploaded images and custom attachment resolvers retain the previous behavior. No persistent content cache or database migration is introduced.

## Validation completed on local

- PHP lint and diff whitespace checks passed.
- Isolated grid: 82 → 5 database queries; 50,503 bytes of HTML before and after with identical SHA-256 `72f75a79ca8babcd6f145666629690b294f34534d5868cf0ac006f36d6fc6c3e`.
- Full packaging manufacturer page profiling: 149 → 73 queries in the observed runs. Counts differ from the isolated grid due to request/cache context. These are local measurements, not guaranteed hosting latency improvements.
- All 18 HTTP comparisons across the homepage, box manufacturer page and bag manufacturer page passed: stylesheets, external scripts, images, links, form structure and visible text unchanged. Hidden nonce values were excluded from comparison.
- Browser checks: 39 category cards, category/quote CTA targets, mobile menu and contact emails retained; no horizontal overflow at the actual 390px CSS viewport. Email fields retained required/autocomplete attributes. No lead form or conversion was submitted.

The production change is confined to `wp-content/themes/custom-box-theme/template-parts/home/packaging-category-groups.php`. It changes no UI, tracking, CSS, JavaScript, image files, URLs or form handling.

## Deploy

1. Pull `main` and run the usual deployment.
2. Purge the homepage and `/custom-packaging-boxes-manufacturer/` HTML/page cache so the next render uses the new template. Other pages that reuse this template should be purged through the existing deployment process as applicable.
3. Verify both pages and the category links, then measure server response/cache MISS and HIT separately on hosting.

No database import or Product Sample Deploy action is needed for this change. It does not alter LiteSpeed/Cloudflare settings or consolidate Google tags. The live WordPress admin separately displayed **Database Update Required** during the preceding audit; that core upgrade requires the owner's normal backup/update procedure and is not performed by this release.

## Rollback

Revert only this template change and purge the affected HTML cache. No content/database rollback is required.
