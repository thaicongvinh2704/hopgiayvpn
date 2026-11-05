# Paper Bags with Logo: pagination canonical correction

Date: 2026-11-05. Status: implemented and checked locally; owner approved Git delivery. Hosting has not been deployed.

## Problem and result

The public hosting verification in `outputs/paper-category-seo-20261105/hosting-verification.json` recorded page 2 declaring the first archive page as canonical. The theme's priority-20 canonical filter returns the base term URL without considering pagination.

The reviewed metadata MU plugin now adds a priority-90 canonical filter scoped strictly to the `paper-bags-with-logo` product category. Page 1 declares the base archive URL. Subsequent pages declare their respective `/page/N/` URL. The generated URL does not include incoming tracking, sorting or filter parameters. Existing robots rules are preserved. This is canonical normalization, not a new filter-indexing policy. Error pages and feeds are excluded. Open Graph URL uses the same page URL.

This follows Google's pagination guidance: https://developers.google.com/search/docs/specialty/ecommerce/pagination-and-incremental-page-loading

There are no database migrations, metadata overwrites, new categories, redirects, layout, CSS, product or permalink changes. The earlier approved SEO metadata release version remains unchanged; the canonical correction runs independently of that migration's applied-state option.

## Validation

- PHP syntax check passed.
- `tools/verify-paper-bag-canonical-20261105.php`: 105 assertions passed against the real local WordPress filter chain. Covers pages 1–4, tracking/sorting/filter parameters, three unrelated categories on pages 1–2, and 404 passthrough. The test removes an additional local-only commercial canonical filter in memory to verify the fix independently of that unshipped filter.
- `tools/verify-paper-category-seo-20261105.py after`: 67 assertions passed across seven HTTP pages. Visible body structure/content, styles/scripts, H1 and previously reviewed title/description remain unchanged.
- Database snapshot inspection: 44 categories checked, no unexpected changes; product content hash unchanged.
- Evidence: `outputs/paper-category-seo-20261105/canonical-verification.json`, `http-verification.json`, `database-verification.json`.

Local noindex policy suppresses rendered canonical tags. Therefore local filter verification establishes the generated canonical values; it does not claim a live hosting fix or Google-selected canonical. After approved deployment, purge the archive/pagination caches and inspect the rendered hosting head for a single self-canonical on pages 1, 2 and 3. Google may choose a different canonical until recrawl and evaluation.
