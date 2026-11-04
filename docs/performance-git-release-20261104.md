# Performance release — 04/11/2026

This release preserves the existing visual design, content, menus, image crop and conversion IDs.

Included: two frontend MU plugins; byte-identical public featured-image cache bundle and workshop responsive sources; eager paper-bag hero images; dependency-aware deferred scripts; batched category-menu lookups; first-visit homepage loader-logo preload.

## Hosting rollout

1. Pull `main` and run the normal deployment. No database import or product sample deployment tool is required.
2. Open a WordPress admin page once so PHP executes the new MU plugins even if public HTML remains in LiteSpeed cache. The native WebP plugin regenerates Converter rules for the hosting paths, backs up the original rules in a private non-autoloaded WordPress option, and records revision `2026-11-04.2`. A database advisory lock prevents simultaneous rollout requests from rewriting the files together.
3. Clear the site's HTML/page cache as part of deployment, then reload the public pages. Cloudflare HTML cache rules have not been changed by this release.

Optional CLI status, from the WordPress root:

```sh
php tools/manage-native-webp-cache.php status
```

Expected `disabled: false` and `revision: "2026-11-04.2"`. This requires the installed Converter for Media plugin, Apache/LiteSpeed and writable converter `.htaccess` files. Failed preflight checks leave the old rules in place and retry later; the image/JS/menu changes otherwise continue to work. An already-applied revision is not rewritten on subsequent requests.

Do not copy XAMPP-generated `wp-content/.htaccess` or `uploads/.htaccess` onto hosting. They are deliberately excluded from this commit. The `.htaccess` inside the new static image bundle is portable and included.

## Verification and rollback

- Offline bundle integrity: `python tools/verify-performance-release.py` (Python standard library).
- Local checks passed: public image identity/render attributes and script dependencies; seven-page HTTP regression; native WebP MIME/cache/privacy and original/converted JPG/PNG behavior; menu groups/order/content before–after; mobile navigation; first versus repeat-visit loader preload. No customer lead or Ads conversion was generated.
- After deploy, verify native WebP `Cache-Control: public` and Cloudflare MISS/HIT behavior, galleries, chat, forms, mobile/desktop layout and tracking. Re-run PageSpeed; no post-deploy speed gain is claimed yet.
- Native format policy rollback: `php tools/manage-native-webp-cache.php rollback`. This restores its saved rules and disables the policy; stored Converter settings were left unchanged. To re-enable, use `enable`, then make another WordPress request. The rollback command is provided; end-to-end restore has not been rehearsed in this release.

The Cloudflare HTML-cache pilot and Google Tag consolidation remain proposals. This release neither applies CDN rules nor removes tracking destinations. Detailed local findings are in `performance-local-fixes-20261103.md` and `performance-comprehensive-review-20261104.md`.
