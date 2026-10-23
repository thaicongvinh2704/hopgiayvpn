# Custom Kraft Paper Bags — local implementation, 23 October 2026

Status: created and verified in local WordPress. Release prepared for Git deployment; hosting has not been changed.

Preview: http://localhost/hopgiayvpn/products/custom-kraft-paper-bags/

## Purpose and keyword scope

This material-based collection sits below Paper Bags with Logo and brings together existing kraft options. It is separate in purpose from the manufacturer money page: this category helps buyers select a bag style; `/custom-paper-bags-manufacturer/#quote` handles the broader manufacturing enquiry and quote form.

- Main keyword: `custom kraft paper bags`.
- Related topics covered naturally: wholesale orders, logo printing, brown and white kraft, SOS/no-handle bags, square-bottom bags, bread/bakery formats and handled carriers.
- No search-volume or ranking claims were made. The prior research distinguished kraft (paper family) from SOS (construction), so the entire collection is not labeled SOS.
- SEO title: **Custom Kraft Paper Bags | Wholesale Logo Printing | VPN**.
- Meta description: **Custom kraft paper bags made in Vietnam for wholesale orders. Compare SOS, flat and handled styles, brown or white kraft, sizes and logo printing. Request a quote.**
- Rank Math is installed locally (1.0.269); saved term title, description, focus keyword, index/follow, canonical and social metadata use the site's established Rank Math fields. Title and canonical filters are scoped to this category; pagination has a distinct title and self URL.

## Product selection and information sources

14 existing published products were added, using slugs rather than IDs because local and production IDs differ. The release JSON records the product selection and the existing material-specification reason for each. All old category relationships, titles, publication statuses, content, excerpts and featured images were preserved.

The first styles are grocery/SOS and bakery bags without handles, followed by bread, baguette, window and handled formats. Products with only a generic grease-resistant paper specification or ambiguous art-paper alternatives were not automatically included. Material options are confirmed by project; images are existing product references and are not described as proof of a particular factory order.

Content uses the owner's confirmed terms already present in the paper-bag money page: general MOQ 1,000; design support; samples 7–15 days depending on order; production 7–45 days after sample approval; design-dependent sample fees; 50% sample-fee refund for orders over 5,000 pieces; checks at each production stage; cartons or shrink-wrapped bundles. Transit is separate. No FSC/ISO/FDA certificates, manufacturing capacities, test results or reviews were invented.

The category has a concise introduction, an existing product hero photograph, existing product-grid styling, a style comparison table, paper/print/size guidance, RFQ inputs and seven visible FAQ answers. CollectionPage and ItemList markup describe the visible catalog; no fabricated price, Offer or rating is added. These features support readability and machine understanding; they do not guarantee indexing or inclusion in AI answers.

## Discovery and design

- Registered in the existing category manifest and existing Paper Bags & Packaging Add-ons menu/product-hub group.
- Actual parent is Paper Bags with Logo; breadcrumb names that parent.
- Added links point to actual products, the manufacturer quote page, About and relevant guides.
- Two published guides (`paper-bag-sizes`, post 8555; `kraft-vs-coated-paper-bags`, post 8557) were verified via the live WordPress connection. They are absent from the current local database, so the local preview links to their real production URLs. Production uses its own normal post permalinks.
- Existing header, menu structure, colors, product cards and responsive guide styling are reused; there is no redesign or new frontend dependency.

## Verification

- Local category ID 1260, parent `paper-bags-with-logo`, 14 products.
- Both category pages return HTTP 200, contain one H1, valid parsed structured data and a single CollectionPage with 12 and 2 catalog entries respectively.
- 30 local product, supporting-page and upload-image URLs checked successfully.
- Parent category and product hub contain discovery links to the new category.
- Database verification confirms the scoped metadata, all assignments and preservation of existing product content/images/status/categories.
- Computed canonical is self-referencing for page 1 and page 2. Local safety settings deliberately keep rendered local pages noindex/nofollow; these settings were not disabled. Production rendering must be checked after an authorized deployment.
- Browser visual review at default desktop size and 390 × 844 mobile size: no horizontal page overflow; hero and product imagery load; FAQ disclosure opens and shows the confirmed schedule/refund terms.
- Modified PHP files pass syntax checks; scoped tracked-file diff passes whitespace checks.

Evidence: `outputs/kraft-category-20261023/database-verification.json`, `http-verification.json`, HTML snapshots, `desktop.jpg`, `mobile-top.jpg`, `mobile-guide.jpg` and `mobile-faq.jpg`. A before-state backup was saved before the local category was created.

## Repeatable deployment

`inc/kraft-paper-bags-category.php` is loaded by the existing theme bootstrap. Its one-time versioned setup creates/updates this category and appends the selected existing products. Missing products are recorded in the release option instead of triggering a full scan on every frontend request. It does not import products or images and does not remove old assignments. The shared manifest retains the new category and assignments for existing admin-sync tools. Optional `parent_slug` support keeps this category below Paper Bags with Logo during manifest/hierarchy maintenance; defaults for all other categories remain as before.

The local apply tool saves a before-state and rejects non-local databases. It was run with `--apply`. A future deployment should include the new module, its JSON and template plus the scoped integration/manifest changes. No SQL dump or local-only WP configuration/plugin should be shipped. Review the missing-product list and live rendering after deployment before claiming production completion.
