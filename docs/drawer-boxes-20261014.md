# Drawer Boxes: pull/deploy release, 2026-10-14

This release includes the approved Drawer category UI, five featured design directions, 18 product SEO profiles, policy FAQs, quote prefills and image assets.

## Deployment

Pull main and deploy the complete committed files, including wp-content/mu-plugins. No Product Sample Deploy button, SQL import, media upload or manual PHP command is needed for this release.

The fixed MU migration runs on the first WordPress init request after deployment, including an anonymous PHP request. It resolves products by slug, updates category/product SEO, titles, excerpts, MOQ and FAQs, and assigns the Drawer category while preserving other product categories. Existing product bodies and galleries are retained. If Botanical is missing, it imports the bundled body and seven WebPs, sets one featured plus six gallery images, resolves four inline images to hosting URLs, and publishes after readback.

Git itself cannot execute WordPress or update a database. Requests served entirely by static/CDN cache do not run PHP. The deployment's normal cache purge should allow a fresh WordPress request; opening wp-admin also triggers the migration without running any tool. LiteSpeed cache is purged once when the migration completes. A separate Cloudflare HTML cache still follows the hosting deployment's cache-purge process.

## Reliability and scope

Before any database mutation, the migration checks the full file bundle and all reviewed product slugs. It stores the before-state in vpn_drawer_20261014_before, uses a lock, retries failures after five minutes, and records completion only after product/metadata readback. Errors appear in wp-admin notices.

The 17 previously reviewed published products must exist. An unrelated draft/private product is preserved and reported. Only the approved Botanical slug has a bundled creation fallback. Interrupted attachment registrations are repaired rather than duplicated; upload copies use temporary files and rename. After the one-time completion marker, later database edits and unpublishing are not automatically reset.

The fixed data contains MOQ 1,000 units; sampling 7–15 days; production 7–45 days after sample approval; orders strictly over 5,000 pcs qualify for 50% of the sample fee refunded; design support; stage-by-stage QC; carton or shrink-wrapped-bundle packing. Reference sizes remain customizable suggestions.

Drawer code is independent of the uncommitted commercial/factory helpers on the local checkout. This commit excludes unrelated About, other landing pages, factory-gallery changes, query guards, local configs and generated sitemap caches. There are no analytics/index submissions or customer-email actions.

## Verification

77 actual WordPress checks on a disposable local database passed: fresh anonymous migration from 17 products, injected media interruption, resumed publication, seven attachments and four inline images, exact saved copy/SEO/policy for 18 products, rerun without duplicates, later owner edits preserved, and blocked preflight without mutation. The fixture database was removed.

The prior UI checks passed on five viewport sizes (104) and functional/SEO checks (170). Release-only theme PHP is syntax checked independently of unrelated working-tree changes; public-mode page-2 canonical and category rendering are also checked. Local noindex and database isolation are unchanged by this release.
