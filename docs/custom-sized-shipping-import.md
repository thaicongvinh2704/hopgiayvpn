# Custom-Sized Boxes for Shipping — public after pull/deploy

The owner approved publication of this article. Pull `main` into the hosting WordPress root and deploy all new files under `wp-content/mu-plugins/`:

- `vpn-shipping-article-import.php`
- `vpn-shipping-article/` (manifest, article and five original WebP images)
- `vpn-shipping-calculator.php`
- `vpn-shipping-calculator/` (CSS and JS)

No plugin activation, Product Sample Deploy, import button or editor publication is required. The first uncached WordPress request imports the package into the hosting database and publishes it. If pages are served completely from CDN/static cache, open `/wp-admin/` once after deployment to run WordPress PHP. Expected public URL:

https://hopgiayvpn.com/custom-sized-boxes-for-shipping/

The package uses one featured image and three inline images; the fifth image is attached to the article as a reference, matching the supplied metadata. All original images are 1600 × 900 WebP below 100 KB. The supplied title, excerpt, Packaging Guides category and three Rank Math fields are imported without relying on local database IDs. The calculator runs from scoped CSS/JS assets, preserving inert form controls through the theme's HTML sanitizer. The import neither grants anonymous visitors permissions nor accepts request-selected content.

Before creating content the importer checks checksums, image format/size and required calculator files. It creates a draft first and publishes only after images, SEO and calculator HTML pass readback. An unrelated existing slug stops the import and reports an admin notice. A database lock prevents concurrent imports; incomplete deployment retries after five minutes. Empty interrupted drafts resume using previously imported image hashes. A completed import is reused, including local post 8919 if the database was copied. It does not overwrite unrelated posts or later owner edits, and a successful completion marker prevents later automatic republication after the owner unpublishes the article.

Status/error options: `vpn_shipping_autopublish_20261010_done`, `vpn_shipping_autopublish_20261010_error`, `vpn_shipping_autopublish_20261010_retry`. The done option stores the hosting post ID (which can differ from local). A pre-publication snapshot is retained in `_vpn_shipping_before_autopublish_20261010` post meta. Local is already published; hosting publication is not claimed until deployment and a public HTTP check.

This release changes no theme files, About page, products, credentials, or database configuration. Removing importer code stops future migration attempts but does not delete the article/media. Keep the calculator plugin/assets while the article uses the calculator. Do not restore an older theme over existing live changes.
