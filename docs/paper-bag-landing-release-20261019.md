# Paper bag manufacturer landing release — 2026-10-19

URL: `/custom-paper-bags-manufacturer/` (unchanged).

## Included

- Keep manufacturer as the primary intent. Title: `Custom Paper Bag Manufacturer | Wholesale Logo Bags | VPN`. Supporting copy covers custom printed bags, wholesale, bulk orders and supplier selection.
- Present Vietnam manufacturing to international buyers, with the full WooCommerce destination list. Public copy does not restrict enquiries to the US and Canada.
- Restore the shared site header, navigation, blue palette and product card styles. Render up to 12 published catalog products and four additional Christmas products, with real product permalinks and quote selection. Product selection resolves by slug with catalog fallbacks, without environment-specific product IDs.
- Use existing catalog images. Hero selection prioritizes twisted-handle kraft, white kraft with twisted handles and printed flat bread bags. This release does not change product media records.
- Show the owner-confirmed general MOQ of 1,000, sample time of 7–15 days, production of 7–45 days after sample approval, and 50% sample fee refund for orders strictly over 5,000 pcs. Include 14 buying FAQs and project-specific shipping guidance.
- Use the existing shared quote handler, with server-confirmed receipt before conversion events. Preserve attribution, upload validation and native POST fallback. The shared handler and theme includes already exist in the preceding commits.
- Include the new `template-parts/woocommerce/paper-bag-landing-card.php` partial, scoped CSS/JS, landing helper/template and footer integration together. The workshop image and shared product styles are already tracked.

## Hosting deployment

Pull `main`, run the normal deployment, then purge WordPress/server/CDN caches and refresh the page. This code-backed landing does not require SQL import, a Page editor update or Product Sample Deploy. Products, media and categories are resolved from the hosting site's existing published WooCommerce catalog; missing products fall back to available catalog entries.

Verify the updated title/H1, international wording, product links, Christmas row and quote form after deployment. Confirm a real lead is saved and mail is delivered using the hosting environment. Local mocks do not establish production email delivery or Google Ads reporting. Search impressions and rankings require subsequent Google crawling and are not guaranteed by deployment.

## Validation

Prior local validation on October 15–16 covered responsive layouts, catalog/SEO markup, international destination selection, quote interactions and mocked receipt/conversion flows. The final international revision passed 156 browser checks and 10 public-mode SEO checks. These are historical checks, not a fresh hosting result.

For this release, PHP syntax checks pass for the helper, landing template, shared card and footer; JavaScript syntax and scoped diff whitespace checks pass. Dependencies were checked against tracked files and HEAD theme includes. A fresh database-backed local end-to-end test was not performed because the local `wp_posts` table has an existing engine error. No database repair or migration is part of this release.

Separate unpublished product-image importers, global SEO changes and other working-tree edits are outside this release.
