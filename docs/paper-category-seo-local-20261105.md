# Three paper bag category metadata — local review, 5 November 2026

Status: implemented and verified on local. Approved by the owner for Git delivery on 5 November 2026. Hosting deployment remains the owner's next step.

## Scope

Only the existing `paper-bags-with-logo`, `custom-kraft-paper-bags`, and `custom-bakery-bread-paper-bags` categories receive seven Rank Math metadata fields: title, description, focus keywords, Facebook/Twitter title and description. No term names, slugs, descriptions shown in the body, canonical settings, images, assignments, H1, CSS, scripts, forms or product records are edited.

- Logo catalogue: `Custom Paper Bags with Logo Wholesale | Vietnam Factory`.
- Kraft/material collection: `Custom Kraft Paper Bags | Wholesale Printed Bags | VPN`.
- Bakery/application collection: `Custom Bakery Paper Bags Wholesale | Vietnam Manufacturer`.

The manufacturer money page and the other category metadata are preserved. Vietnam is used selectively in titles and appears naturally in descriptions. Keywords describe the existing offering; no search volume, ranking, certification, fixed delivery deadline or food-contact guarantee is invented.

## Implementation

`wp-content/mu-plugins/vpn-paper-category-seo-20261105.php` loads a versioned `release.json`. An explicit local CLI application or subsequent administrator request can apply the release by slug, never by local term IDs. The current three core SEO fields must match a reviewed local/hosting tuple or the desired values. Unreviewed changes are preserved for manual review. Original metadata is backed up in private, non-autoload WordPress options. Each category is marked applied only after readback succeeds. Repeat application does nothing, including preserving later manual edits.

Scoped late filters make saved metadata win over older category-specific defaults for these three terms only. No frontend database update is performed. Pagination receives a page number in the HTML title; Rank Math retains its base archive title in social tags. The local theme already has additional commercial SEO support that was not present in the earlier public HTML snapshot; this release does not depend on shipping those unrelated files.

Existing Kraft and Bakery data files and release version markers are unchanged, so those importers do not reassign products or regenerate the visible body as part of this metadata release.

## Validation

- PHP lint passed for plugin and CLI helper.
- Compared all 44 local categories before/after: unrelated term metadata, category data and all term assignments unchanged. Product title/status/slug/content/excerpt hash unchanged.
- Seven HTTP URLs: three first pages, logo and Kraft page 2, cosmetic category and manufacturer money page as controls. All 67 checks passed.
- Compared body structure/text, H1, assets and canonical output before/after. All unchanged. Local indexing controls are preserved; this is not proof of hosting indexing status.
- Verified HTML title and one description tag, plus Facebook/Twitter metadata. Existing control-page `article:published_time` varies per request and is excluded from metadata equality, while other control tags are compared.
- Repeated local application: three `already_applied`, zero errors.

Review: `http://localhost/hopgiayvpn/outputs/paper-category-seo-20261105/review.html`. This is a local review page, not a Google SERP screenshot.

## Later deployment, only after approval

The two new MU-plugin release files are sufficient for the metadata implementation. After a later authorized Git push and hosting pull/deploy, an administrator visit applies the approved matching records. Purge hosting/CDN archive pagination caches and check public HTML. No database export/import, product sample deploy, permalink change or index submission is part of this work.

Snapshots and checks live in `outputs/paper-category-seo-20261105/`. Original fields can be restored from the `vpn_paper_category_seo_before_20261105_<slug>` options after disabling this release; no rollback has been applied.
