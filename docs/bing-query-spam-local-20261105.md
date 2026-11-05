# Bing query spam: local correction and investigation

Date: 2026-11-05. Scope: implemented and verified locally; owner approved Git delivery. Hosting has not been deployed or purged.

## Confirmed evidence

- Downloaded Bing Site Explorer's **All URLs** CSV: `hopgiayvpn.com_SiteExplorerUrls_11_5_2026.csv`, 600 rows. This is the downloaded batch, not a claim to cover every URL Bing knows.
- Identified 446 exact root-level bare query paths dated May–June 2025. Reviewed subjects are unrelated casinos, gambling, Germany topics, games and downloads. Includes `188bet`, `1xslots-casino`, `lixi88`, and the requested `community/`, `research/`, `case/`, `service/` families. Exact paths are bundled in `wp-content/mu-plugins/vpn-bing-query-guard/verified-queries.json`.
- Bing's historical status for these 446 entries: 5 HTTP 200, 277 HTTP 301, 164 code 0 (not a confirmed HTTP success or index status). Do not interpret code 0 as an indexed page.
- Read-only public HTTP checks of 12 representative URLs returned 301 to encoded/underscore-normalized query variants, rather than immediate 404. Some 301 responses exposed LiteSpeed cache headers. Cloudflare reported DYNAMIC for the sampled requests. The check did not establish the final status of all redirected live URLs.
- Local before-check similarly returned 301. The older theme guard was untracked, limited to certain families/suffixes, and checked only root `/` or `/index.php`; it missed the local `/hopgiayvpn/` base. It was not a complete solution.

## Implementation

`wp-content/mu-plugins/vpn-bing-query-guard.php` handles exact verified paths at `muplugins_loaded`, before normal plugins, theme redirects, homepage rendering and template selection.

- HTTP **404**, with no Location redirect and no homepage content. GET, HEAD and POST are covered.
- Match only the configured homepage root/index.php and exact verified query keys. The rule does not block every `community/` or `research/` prefix, arbitrary gambling words, or arbitrary query strings.
- Recognize percent-encoded forms (up to three decoding levels), an empty key assignment, and WordPress's dot/underscore suffix normalization. Query values are never inspected as spam keys.
- Preserve ordinary `gclid`, `gbraid`, `wbraid`, `utm_*`, `s`, cart, checkout, REST, AJAX, admin and product paths. Suppress the older theme-only guard when present to avoid broader unreviewed matching.
- `Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0`, `X-LiteSpeed-Cache-Control: no-cache`, and `X-Robots-Tag: noindex, follow`. No robots.txt disallow, homepage redirects, page deletion, database cleanup, content or design changes.
- No migration, new database tables, scheduled tasks or external service dependencies.

## Local investigation and limits

- Scanned 10,596 local source/content files in themes, plugins, MU plugins and uploads for the requested indicators, excluding the new defensive guard itself: no matches.
- Scanned 11 core WordPress content/configuration tables, 53 text/blob columns: no indicator matches. Counts only were saved; no customer records or secret values were exported.
- Upload executable inventory found only the existing `uploads/wpforms/cache/index.php`, whose content returns 404. No malicious code was found by these targeted checks; this is not a comprehensive malware clearance or a WordPress/plugin checksum verification.
- Local access/error logs: no matching indicators excluding this audit's own test requests. These local logs cannot establish the first external referrer or the history on hosting.
- Likely mechanism supported by code/response evidence: invented bare query keys reach normal WordPress canonical/query handling instead of being rejected as nonexistent URLs. The original publisher/referrer of those URLs remains unknown. Absence of indicators in local files/database does **not** prove that hosting has never been compromised.
- Hosting session had expired. Owner explicitly chose local work only. Hosting theme/plugin files, database, access/error logs and cache configuration were therefore not audited or changed. No claim that live is fixed or clean.

### Independent local database fault

The broad initial read-only metadata scan encountered InnoDB page corruption in `wp_woocommerce_shipping_zone_methods`, crashing local MySQL. No database data was deleted or repaired. A later GET of `/cart/` encountered the same local database problem; full cart/checkout HTTP verification is blocked. MySQL was restarted normally, without recovery-mode settings. Further database reads were limited to the WordPress content/configuration tables. The URL guard's matcher confirms cart/checkout parameters pass through, but this is not proof that the independently broken checkout works.

## Validation

- PHP lint: passed.
- Matcher checks: **11,616 passed**, covering all verified entries, root/subdirectory installations, raw/single/double/triple encoding, `index.php`, mixed tracking, parameter-value false positives and unreviewed URLs.
- Real local HTTP: **446 GETs return 404 directly**, 8 encoded HEADs return 404 with no body, 3 spam POSTs return 404, and 9 normal HTTP controls pass. No redirect on rejected URLs. Normal homepage, Ads parameters, search, paper-bag category, money page and REST continue responding.
- The existing seven-page category/landing regression suite also passed all 67 checks: visible content structure, H1, styles/scripts and approved SEO metadata remain unchanged.
- Cart/checkout request matching is verified, but full functionality is blocked by the separate local InnoDB fault described above.
- Evidence is retained under `outputs/bing-query-spam-20261105/`: Bing rows/summary, before live/local responses, after HTTP responses, matcher report and local source/database/log audit.

## Cache and later hosting rollout

Local has no `wp-content/cache` directory, `advanced-cache.php`, or `object-cache.php`; no persistent page-cache files were available to purge. Repeated HTTP checks reach the new 404 handler with no-store headers. Do not delete generated image/WebP files, WPForms files, or unrelated database content as a cache cleanup.

When the owner approves Git and deploys: deploy both the MU PHP file and its JSON denylist; then purge the affected raw and encoded/normalized URL variants from active hosting/CDN caches. Inspect query cache-key/bypass rules before claiming cached variants are cleared. Recheck public GET/HEAD status without following redirects, including normal Ads/search/cart/checkout controls. This live purge and verification have **not** been performed in the local-only scope.

Google documents that 404/410 signal missing content; search index cleanup requires recrawling and is not immediate: https://developers.google.com/crawling/docs/troubleshooting/http-status-codes
