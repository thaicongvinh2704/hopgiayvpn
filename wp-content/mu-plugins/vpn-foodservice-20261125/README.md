# VPN foodservice product release

Four WooCommerce products with English buyer guides, 21 WebP images, specifications, Rank Math metadata and Vietnam search terms.

The burger box, fry scoop and sandwich wedge each retain their original first five images. Their sixth gallery images and any corresponding inline figures have been removed. The delivery bag retains all six images. The filenames, alt text and SHA-256 checksums are recorded in `products.json`.

## Deploy

1. Pull the release commit and deploy `wp-content/mu-plugins/vpn-foodservice-20261125.php` and its matching `vpn-foodservice-20261125/` directory.
2. Open WordPress admin. In the **Foodservice product update ready** notice, select **Confirm product update**.
3. Confirm the success notice links to four published products. Open the links on the destination site to verify each product, its images and its Vietnam SEO title and description. The update notice disappears after successful completion.

**Tools → Foodservice Product Release → Sync 4 Foodservice Products** remains available for manual verification or retry. Both entry points run the same guarded importer. Confirmation requires an administrator with WooCommerce permissions and a valid WordPress nonce; no import runs just from opening a public page.

The import checks for existing product slugs, SKU ownership and image integrity. Running the sync again verifies existing products. The original baguette, window bread bag, SOS and flat-handle kraft bag listings are excluded from this release.
