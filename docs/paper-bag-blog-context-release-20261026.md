# Six paper bag blog examples — release 2026-10-26

Adds the six reviewed bakery examples and nine contextual links to the existing
published paper bag blogs. The package contains paragraph patches only: no local
mirror imports, full-article replacement, media uploads, metadata or layout edits.

## Deployment

1. Pull `main` and deploy the MU plugin PHP file with its adjacent
   `vpn-paper-blog-context-20261026/patches.json` directory.
2. Open the dashboard or a normal WordPress page. A single synchronization pass
   resolves the six existing posts by slug and inserts the approved paragraphs.
3. Clear the hosting/page cache and review the six blog pages. No Product Sample
   Deploy action, SQL import or manual post creation is needed.

Each insertion requires one matching original paragraph. Text matching tolerates
HTML spans, encoded entities and WordPress smart quotes. An existing approved
example is skipped; changed or ambiguous anchors/examples are preserved and
reported in the WordPress dashboard. A missing or unpublished post is never
created or published by this release. Other posts continue if one requires review.

Results: `vpn_paper_blog_context_20261026_results` option. Original content for
updated posts: `vpn_paper_blog_context_20261026_before_<hosting-post-id>` option.
The once-per-version attempt marker avoids repeated work on ordinary page loads.
After resolving a reported conflict, an administrator can rerun
`vpn_paper_blog_context_apply()` through the site's normal trusted maintenance
workflow. No public retry endpoint is added.

Reverting Git does not undo saved database copy. Restoring the saved before-state
requires checking for editorial changes made after this release; do not overwrite
newer article content blindly.

## Included posts

- `custom-paper-bag-cost`: separate bakery bag quantities by format and artwork.
- `paper-bag-sizes`: check actual baguette and gusseted-loaf dimensions.
- `types-of-paper-bags`: compare bakery body constructions and handover needs.
- `kraft-vs-coated-paper-bags`: distinguish print surface from grease/contact needs.
- `paper-bag-handle-types`: compare grips using the same filled pastry box.
- `paper-bag-printing`: review the folded closure and window artwork placement.

Local checks cover exact content insertion, preservation of metadata/images/dates,
repeat execution, changed and duplicate anchors, and missing posts. The six local
pages were also checked on desktop/mobile. Hosting remains for the owner to deploy.
