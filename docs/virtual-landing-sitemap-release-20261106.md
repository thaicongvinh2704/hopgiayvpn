# Virtual landing sitemap release — 06/11/2026

The paper-bag and pizza manufacturer pages are template-backed virtual pages. They return 200 and indexable HTML on production, but Rank Math's database-backed page sitemap does not discover them automatically.

This release adds exactly these two public routes to the first page sitemap:

- `/custom-paper-bags-manufacturer/`
- `/custom-pizza-boxes-manufacturer/`

The MU plugin uses the installed Rank Math `rank_math/sitemap/page_content` hook. It preserves existing entries, skips duplicates, respects a private site's setting and defers to the normal provider whenever a real page exists. It adds no synthetic modification dates. Frontend content, templates, CSS, JavaScript, tracking, canonical URLs and forms are unchanged.

## Deploy

1. Pull `main` and deploy normally. No content/database import or Product Sample Deploy action is needed.
2. Open a WordPress admin page once to ensure PHP runs even if public HTML is cached. On the first eligible request, the MU plugin invalidates Rank Math's page-sitemap cache and requests LiteSpeed purges for `/page-sitemap.xml` and `/sitemap_index.xml`. It records revision `2026-11-06.1` in a non-autoloaded option. No Cloudflare configuration is changed.
3. Verify public `/page-sitemap.xml`: both routes must occur once, all previous page entries must remain, and the document must parse as XML. If hosting/CDN has an additional XML cache, clear those two XML URLs through the existing hosting cache controls before checking again.

The sitemap is a discovery signal; this release does not guarantee ranking gains or trigger individual Google Request Indexing actions. The two main paper-bag/custom-box money pages were already indexed when checked on 06/11.

## Validation completed locally

- PHP lint passed.
- 31 database-free checks exercised the installed Rank Math XML generator and real WordPress filter engine. Includes scope/pagination, duplicates, private sites, real pages and cache retry/one-time rollout behavior.
- 13 checks with real local WordPress, Rank Math and database passed: the page sitemap increased from 7 to 9 entries, adding only the two expected URLs and preserving every existing entry. Other sitemap types were unaffected. Public eligibility was simulated in the CLI process only; local noindex stayed unchanged.
- Actual local HTTP: both landing pages and the page XML returned 200 with their expected content types. Private local XML correctly excludes the two virtual routes.
- No hosting deployment or post-deploy verification has been performed by this release.

Repeat the independent generator checks from the WordPress root:

```sh
php tools/verify-virtual-landing-sitemap-20261106.php
```

## Rollback

Remove `wp-content/mu-plugins/vpn-virtual-landing-sitemap.php` and refresh Rank Math/LiteSpeed page-sitemap cache. There is no imported content to undo. The revision option has no effect without the plugin.
