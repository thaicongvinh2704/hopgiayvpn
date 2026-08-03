# Product long-description v3 release — 2026-08-03

This release contains the approved long descriptions from
`hopgiayvpn-all-product-content-seo-2026-08-01-v3-release.zip` for 179
existing published WooCommerce products.

Changed field only:

- `wp_posts.post_content`

The sync does not change short descriptions, Rank Math fields, titles, slugs,
images, galleries, categories, SKU, price, stock, status, or variations. The
payload uses the live URL `https://hopgiayvpn.com`.

## Hosting deploy through WordPress Admin

1. Pull/deploy the latest commit from `origin/main`.
2. Sign in to WordPress Admin.
3. Open **Tools → SEO Content Sync**.
4. Confirm the preflight reports the expected number of products to update.
5. Click **Đồng bộ 179 long description v3**.
6. Confirm the success notice reports `179/179` passed QA.

The admin tool validates the release schema, 179 checksums, Product IDs, titles,
slugs, publish status, live hostname, and absence of local/staging URLs before
writing. It verifies all 179 stored long descriptions after the update. If a
write or final QA fails, it attempts to restore content changed in that run.

The migration is idempotent. Running it again skips products that already match
v3.

## One-command hosting alternative

From the WordPress root on branch `main`:

```bash
bash tools/sync-product-seo-content-20260801.sh
```

The script pulls `origin/main`, runs production preflight, applies only
`post_content`, and runs final QA.

## Manual CLI alternative

```bash
git pull --ff-only origin main
php tools/deploy-product-seo-content-20260801.php dry-run
php tools/deploy-product-seo-content-20260801.php apply
php tools/deploy-product-seo-content-20260801.php qa
```

The dry-run must report `status: ready`. The final QA must report
`passed_count: 179` and `failed_count: 0`.
