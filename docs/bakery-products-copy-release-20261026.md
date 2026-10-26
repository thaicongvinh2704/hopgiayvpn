# Bakery product copy release — 2026-10-26

Updates nine existing published bakery/bread bag products. Each product has a
focused search title and description, a shorter introduction, product-specific
benefits, clearer wholesale terms and contextual links to the bakery category
and paper bag manufacturer page. Existing technical sections and FAQs remain.

No template, CSS, JavaScript, image, slug, publication status or category
assignment changes are included in this release.

## Deploy

1. Pull `main` and deploy both the MU plugin PHP file and its entire
   `wp-content/mu-plugins/vpn-bakery-products-copy-20261026/` directory.
2. Open a normal WordPress page or the dashboard after deployment. The plugin
   runs one synchronization pass, resolving existing products by slug rather
   than local database IDs. WooCommerce and Rank Math must be active.
3. Clear the hosting/page cache and inspect the product pages. No Product Sample
   Deploy action or database import is needed for these copy changes.
4. Check the dashboard for a “Bakery product copy needs review” notice. If a
   product differs from the reviewed source, it is skipped to preserve newer
   edits; other products continue. Do not force an overwrite without reviewing
   that difference. Recorded results are in the
   `vpn_bakery_copy_20261026_results` WordPress option.

The plugin stores each product's previous content and targeted metadata in
`vpn_bakery_copy_20261026_before_<hosting-product-id>` before applying changes.
It reads back saved fields and marks completed products so subsequent page
requests do not repeat the updates. Reverting Git alone does not restore
database copy; the saved before-state is needed for a content rollback.

## Validation completed on local

- Nine existing public products passed database readback and idempotence checks.
- Product IDs, publication dates, titles/H1s, slugs, images/captions, galleries,
  SKU, price and category assignments were preserved.
- All nine rendered pages returned HTTP 200 with the expected title/meta,
  visible order terms and consistent Product structured data.
- Six internal URLs and 35 inline image URLs returned HTTP 200.
- All nine pages were opened at desktop and mobile width without horizontal
  overflow. No frontend resource or layout changes were introduced.

Hosting has not been updated or verified by this release preparation. Verify
the synchronization result and live canonical/indexability after deployment;
the isolated local site's intentional noindex setting was left unchanged.
