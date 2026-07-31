# Template change log

## Exact sources identified

- `wp-content/themes/custom-box-theme/woocommerce/single-product.php` generated the repeated product heading, customization boilerplate, fabricated testimonial, global FAQ include and newest-product fallback.
- `wp-content/themes/custom-box-theme/template-parts/home/quote-form.php` generated repeated, unverified marketing claims next to the quote form.
- `wp-content/themes/custom-box-theme/template-parts/home/faq.php` remains unchanged and is no longer injected into product pages; it may still be used on non-product routes.

## Changes applied

- Preserved navigation, breadcrumb, gallery, product specifications, quote form fields, workflow, WooCommerce-related products, contact CTA and product-specific content.
- Suppressed the automatic generic H2 whenever a product already has long content.
- Removed the four-card generic customization block from every product page.
- Removed the fabricated five-star testimonial and `Verified Packaging Client` identity.
- Removed the global FAQ include from product pages; each rewritten product carries its own useful intent-specific FAQ in `post_content`.
- Removed the fallback that filled sparse related-product lists with newest unrelated products; only WooCommerce relationship output remains.
- Replaced `Request Free Sample` with `Discuss a Structural Sample`.
- Reworded seven quote-form marketing claims as neutral project-input guidance (product type, dimensions, materials, artwork, quantity, destination and constraints).
- Added `wp-content/mu-plugins/hopgiayvpn-local-safety.php`, active only on local hosts/environment, to block outbound email/HTTP, WooCommerce gateways, webhooks and WooCommerce email delivery during local QA.

## File integrity

| File | Before SHA-256 | After SHA-256 | PHP lint |
| --- | --- | --- | --- |
| single-product.php | `13b1237189dc8846688a1b2e376a9473de81d84fc5e1873b00554b585973359a` | `b41cdb376f95e20fdc8405d90974676493cdb743bfa9ca6115452ce672812bcf` | PASS |
| quote-form.php | `2172f5c1b39a5745f317ff24a791617a7c04da098c74c537b00ab00d41b4a873` | `82e78a25b9bf01fa2ab5aa521330bc88b96ab02eebfdf87af7e5a71d363b039c` | PASS |

Frontend QA confirmed the removed blocks and banned claims are absent across **179/179** product pages.
