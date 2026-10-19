# Four owner-supplied products — local publication, 19 October 2026

Source: `Website_4_Products_24_Images_WebP_Under_100KB.zip`. The archive contains four product folders, six WebP images per folder, and no written product specification. All 24 originals are 450 × 570 pixels, approximately 46–81 KB. Originals are retained without further lossy compression; WordPress creates smaller display sizes.

## Keyword and page mapping

| Product | Primary keyword | Supporting commercial intent | SEO title |
|---|---|---|---|
| Custom Happy Birthday Gift Paper Bags | birthday gift paper bags | birthday gift bags wholesale; custom birthday bags; birthday gift bag supplier | Birthday Gift Paper Bags \| Custom Wholesale Orders \| VPN |
| Custom Brown Kraft Bakery Paper Bags | brown kraft bakery paper bags | kraft bakery bags wholesale; custom bakery paper bags; bakery bag supplier | Brown Kraft Bakery Paper Bags \| Custom Wholesale \| VPN |
| Custom Printed White Flat Paper Bags | white flat paper bags | custom printed white paper bags; white flat paper bags wholesale; flat paper bag supplier | White Flat Paper Bags \| Custom Printing Wholesale \| VPN |
| Custom White Kraft Mailer Boxes | white kraft mailer boxes | white mailer boxes wholesale; custom printed white mailer boxes; white mailer box manufacturer | White Kraft Mailer Boxes \| Custom Wholesale Supply \| VPN |

Every title and meta description includes its primary keyword. Titles are 54–56 characters; descriptions are 144–148 characters. Supporting phrases are used where useful to the buying decision, without repeating every keyword variant in every heading. No measured search volume or ranking guarantee is claimed.

## Search and competition review

Live search results were reviewed for all four commercial product groups. This is a review of returned results, not a location-controlled rank report. These primary supplier pages informed the intent analysis, without copying their descriptions, prices, certifications or lead times:

- [Jialan: custom birthday gift bags](https://www.good-package.com/birthday-gift-bag.html): manufacturer/wholesale intent, artwork and size selection. The VPN page develops the coordinated collection, handle construction and retail assortment brief.
- [ADCOPAC: custom paper bread bags wholesale](https://adcopac.com/product/custom-bread-bags/): specific food application, size, material and order configuration. The new bakery page distinguishes ordinary kraft appearance from an agreed grease barrier and food-contact specification.
- [QIS: custom printed white flat paper bags](https://www.qispackaging.com.au/collections/white-paper-printed): the phrase identifies handleless counter bags, not carriers with flat handles. VPN's page makes that distinction explicit and addresses usable size, opacity and artwork.
- [Tria: white mailer box](https://triapackaging.com/mailer-boxes/white-mailer-box/): white outer print surface, liner selection and closure are relevant purchase criteria. The new page focuses on a white exterior/kraft interior and a tuck closure, without importing the competitor's board grade or delivery promises.

The local catalog was checked before creating slugs. Existing candle-print and present-print birthday products, flat bread bags, logo mailers and tear-strip mailers are retained. The new pages focus respectively on a coordinated happy-birthday collection, gusseted handleless bakery bags, handleless white flat bags, and plain white/kraft-interior mailers.

## Content and trust information

Each product has a distinct 1,500–2,000-word description, a 120–180-word excerpt, six visible FAQ answers, 21 specification rows, three inline figures and a six-image featured/gallery sequence. Image titles, alt text and captions describe the visible view. Each description links naturally to its parent category, a related product, a relevant guide, the corresponding money page and Contact.

`wp-content/mu-plugins/vpn-four-products-20261019-seo.php` scopes a structured-data correction to this batch marker: Product.name matches the visible product name, description uses the product excerpt, and no unverified price or rating is emitted. It does not change other products or search titles.

Copy includes only owner-confirmed service policies: design support; made-to-request samples; sample fees depend on requirements; orders **above** 5,000 pieces receive a 50% sample-fee refund under agreed terms; sampling 7–15 days depending on order; production 7–45 days after sample approval depending on order; checks at production stages; carton or shrink-wrapped bundle packing. General MOQ is a 1,000-unit planning baseline, with specific size/artwork minimums confirmed in the quote.

Exact dimensions, paper grades, food-contact suitability, grease resistance, carrying capacity, flute and transport performance are not inferred from images. No reviews, certifications, fixed prices or exact delivery promises were invented. Visible direct answers, clear headings and specific buyer requirements support machine understanding without claiming a special GEO/AIO certification or guaranteed appearance in AI results.

## Local import and recovery

- Canonical content/manifest: `wp-content/themes/custom-box-theme/inc/product-content/four-products-20261019/`.
- Bundled originals: `wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets/uploads/2026/10/vpn-four-products-20261019/`.
- Source builder: `tools/build-four-products-20261019.py`.
- CLI-only importer/verifier: `tools/import-four-products-20261019-local.php`.
- Run with PHP GD enabled: `php -d extension=gd tools/import-four-products-20261019-local.php`.
- Read-only verification: same command with `--verify`.
- Local IDs: 9007, 9014, 9021, 9028. All are `publish`, have no password and are visible in the catalog.
- Existing products are protected by exact slug/SKU checks. This batch uses a private marker. Attachments are reused on a partial retry, and all four drafts must validate before publication. A completed rerun preserves the products instead of overwriting later edits.
- A full local SQL backup was taken before import at `project-private-backups/before-four-products-20261019.sql` (private, ignored by Git).
- XAMPP MySQL and Apache were stopped initially and were started normally. The local database was readable; no repair, restoration or replacement was performed.
- The first draft validation detected that CLI GD was disabled, so no products were published at that point. The importer now requires WebP-capable GD. Running with `-d extension=gd` created responsive sizes and completed publication using the same 24 attachments.

The owner subsequently authorized Git publication. The release now includes all content, 24 originals, structured-data support and the authenticated database migration below. The local CLI wrapper remains restricted to the local database; hosting uses the admin migration, without copying the local SQL backup.

## Hosting release

1. Pull/deploy the complete commit on `main`.
2. Open any normal WordPress Admin page while signed in as an administrator. The registered `admin_init` sync runs before older pending syncs and creates the four public products, their media records and responsive sizes. Wait for **Four-product release complete: 4 products, 24 images, product content and SEO metadata verified.**
3. Purge the normal page/CDN cache, then check the four public product URLs.

No SQL import, manual image upload or Product Sample Deploy button is required. Opening WP Admin once is the database trigger; a Git pull alone updates only files. Existing local product IDs are never used on hosting. The target installation resolves categories, related posts, image URLs and content links using slugs and its own home URL.

Deployment files: `inc/four-products-20261019-support.php`, `inc/four-products-20261019-product-sync.php`, the new registry entry in `inc/post-sync-loader.php`, the content/asset directories above and `wp-content/mu-plugins/vpn-four-products-20261019-seo.php`. Existing theme loading already includes the registry; no unrelated `functions.php` edits are part of this release.

The migration checks source hashes, every image, existing slug/SKU ownership, category and link dependencies, Rank Math's own metadata route and WebP image support before importing. Incomplete drafts can resume. An atomic option lock prevents simultaneous writes. Completed products are preserved, including later owner content edits or unpublishing. Missing/changed media produces an admin warning and never records a successful release. Frontend, anonymous, AJAX, REST and cron requests do not load this migration through the loader.

The actual admin/core lifecycle passed 17 checks on isolated WordPress/WooCommerce/Rank Math fixture tables at loopback 3311: fresh import, strict saved-state verification, 24 media records, repeat safety, unrelated slug conflict, concurrency lock, owner edits/unpublishing, missing-file failure/recovery, loader registration and frontend/anonymous exclusion. Fixture tables were cleaned afterward. Mail and outbound HTTP were intercepted by the existing test sink. Hosting has not been deployed or verified by Codex.

## Verification evidence

Evidence is under `outputs/four-products-20261019/`: source contact sheet, before-import record, database import/readback reports, browser review and desktop/mobile screenshots. Local search-engine noindex protection remains enabled; product public status means visitors can view the local pages, not that localhost should be indexed.

Final validation: PHP syntax passes; database readback confirms all four published products and exactly 24 attachments, featured/gallery order, alt text, generated sizes, categories, tags, specifications and Rank Math metadata. A completed rerun creates no duplicate products or media. The real local browser review passed 228 checks at 1440px and 390px, covering HTTP 200, exact title/meta, one H1, six gallery views, inline figures, FAQ headings, Product structured data without invented offers/ratings, contextual links and content fit. All product image originals and linked destinations returned 200. Screenshots were inspected on desktop and mobile. Existing local noindex suppresses canonicals; production canonical/indexability must be checked after a separately authorized deployment. Pairwise five-word content overlap was 0.5–1.2%, with distinct buying angles and shared service policies.
