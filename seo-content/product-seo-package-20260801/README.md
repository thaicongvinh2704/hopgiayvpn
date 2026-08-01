# Product SEO content release 2026-08-01

This release contains only the content payload for 179 existing published
WooCommerce products. It does not create products, delete products, modify
theme/plugin code, or touch the 20 newer products outside this package.

Changed fields only:

- `wp_posts.post_excerpt`
- `wp_posts.post_content`
- `rank_math_title`
- `rank_math_description`
- `rank_math_focus_keyword`

The payload uses the live URL `https://hopgiayvpn.com`. Do not replace it with
the local URL.

## One-command deploy on hosting

After the first Git pull that delivers this release, run this single command
from the WordPress root:

```bash
bash tools/sync-product-seo-content-20260801.sh
```

The tool pulls `master`, runs the production preflight, applies the content-only
release and runs final QA. It stops immediately if any step fails.

## Manual deploy alternative

Run these commands from the WordPress root after the Git pull:

```bash
git pull --ff-only origin master
php tools/deploy-product-seo-content-20260801.php dry-run
php tools/deploy-product-seo-content-20260801.php apply
php tools/deploy-product-seo-content-20260801.php qa
```

The dry-run must report `status: ready`. The apply command stops if any Product
ID, title, slug or publish status does not match. It then verifies all 179
products and exits non-zero on any mismatch.

The migration is idempotent: running `apply` again applies the same five fields
and does not create duplicate products or content blocks.

If the host uses cPanel Git Version Control, configure the deployment hook to
run the three PHP commands above from the WordPress root. If it does not have
SSH/PHP CLI, run the same commands through cPanel Terminal or invoke the script
with the host's PHP binary.

This content migration intentionally does not create a database backup. Keep
the hosting provider's normal database snapshot/rollback facility available.
