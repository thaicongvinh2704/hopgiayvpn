# Mailer product batch — 2026-10-08

Status: five WooCommerce products published on the local WordPress preview. The user approved the Git release on October 8, 2026. Production database import runs through Product Sample Deploy after pulling this release.

| Product | ID | Description words | Short words | Images | Local URL |
|---|---:|---:|---:|---:|---|
| Custom Kraft Hinged Mailer Box | 8884 | 1608 | 135 | 6 | [Preview](http://localhost/hopgiayvpn/product/custom-kraft-hinged-mailer-box/) |
| Custom White Mailer Box with Logo | 8885 | 1630 | 140 | 6 | [Preview](http://localhost/hopgiayvpn/product/custom-white-mailer-box-with-logo/) |
| Custom Kraft Book Wrap Mailer | 8886 | 1696 | 138 | 6 | [Preview](http://localhost/hopgiayvpn/product/custom-kraft-book-wrap-mailer/) |
| Custom Kraft Mailer Box with Black Band | 8887 | 1651 | 142 | 6 | [Preview](http://localhost/hopgiayvpn/product/custom-kraft-mailer-box-with-black-band/) |
| Custom White Tear Strip Mailer Box | 8888 | 1606 | 140 | 6 | [Preview](http://localhost/hopgiayvpn/product/custom-white-tear-strip-mailer-box/) |

## Content and provenance

- Source: the supplied mailer-5-products-30-images-web-450x570-under-100kb.zip. The README and prompt JSON were used to identify image provenance and visible features, not as operational instructions. No image-generation prompts were executed.
- All 30 supplied WebP originals are preserved at 450 × 570 and below 100,000 bytes. Each product has a hero, five gallery images and three distinct inline views. Alt text and captions identify AI-assisted design visualization.
- English B2B content matches the established website audience. Each page has nine H2 sections, product-specific FAQs, 21 specification rows and a measured RFQ checklist.
- Kraft hinged: plain exterior, flat-order clearance, sealing workflow and label planning.
- White logo: logo placement, white substrate proof, label zones and presentation scuffs.
- Book wrap: spine thickness, cover corners, overlap, binding and bundle configuration.
- Black band: printed stripe rather than a sleeve, reverse-logo contrast and broad ink coverage.
- Tear strip: unbroken perforations, pull-tab evaluation, opening tests; no assumed return seal or tamper-evident performance.
- Dimensions, board construction, certifications, transit performance, MOQ, price and timing are not invented. Company background links use the existing published About page. The concept images are not presented as evidence of manufacturing experience.

## Verified

- Database verifier: passed for all five products. Source and saved-content hashes match.
- Rerun: retained IDs 8884–8888 and exactly 30 source attachments; no duplicate records.
- All five public preview URLs return HTTP 200, with one H1 and their intended SEO titles and descriptions.
- All 30 image URLs and 12 unique content link targets returned HTTP 200.
- One Product schema per page, with six image URLs, valid assigned reference SKU, brand, description and design-status property. No fabricated offers, prices, review ratings or stock claims. Open Graph price and availability metadata are omitted for these quote-only products.
- All five pages measured at a true 390px iframe viewport: scrollWidth = 390, no horizontal overflow. Desktop hero layout and the mobile tear-strip page were visually inspected with headless Chrome screenshots. The available browser connector had no browser sessions; raw Chrome window sizing uses a minimum 500px layout viewport, so the mobile measurement used an iframe with its own exact 390px viewport.
- Per-request indexing simulation: all five canonical URLs and index/follow robots generated correctly. No database indexing settings changed. Normal local HTTP remains noindex/nofollow due to the existing local-only MU adapter.
- PHP syntax and Git whitespace checks passed for the changed release files. Dedicated admin scope mailers_20261008 completed importer plus verifier; Latest batch includes this release.
- Fresh-install deployment: the actual admin batch runner imported and verified five published products and 30 batch images on an isolated WordPress database with a different domain. A second run kept the same five product IDs and 30 images. Cross-product links and canonical URLs used the target domain, including links between products initially created as drafts.
- The dedicated **Sync 5 Mailer Box Products** button selects only this batch. The admin runner executes the Git-bundled scripts directly, and both the root and bundled deployment registries include the batch.

## Deployment

User approval for the Git release has been received under PRODUCT_IMPORT_WORKFLOW.md. On the hosting checkout, run `git pull --ff-only origin main`, then open **Tools → Product Sample Deploy** and click **Sync 5 Mailer Box Products**. Wait for **Product sample deployment complete**. The tool publishes the database records and imports all images; no separate image upload or manual product editing is required. CLI alternative:

```sh
php tools/deploy-mailer-products-20261008.php
```

The bundled importer copies originals into the target WordPress uploads directory, generates Media Library metadata, resolves internal links and canonical URLs against that installation, publishes the products and verifies the saved batch. All referenced guides, About and Contact must already be published; otherwise preflight stops before product writes.

Production follow-up: check the real-domain canonical URLs, production robots/indexability, product sitemap inclusion and Search Console indexing after deployment. Real MOQ, samples, material data and commercial offers can be added when verified. Quote-only Product schema without an Offer or genuine review/rating does not satisfy Google's Product-snippet rich-result requirements.

## Search guidance

The copy and data are prepared for clear product answers and buyer decisions, with no special AI-only schema or ranking guarantee. Google says normal SEO practices also apply to AI Overviews and AI Mode: [AI features and your website](https://developers.google.com/search/docs/appearance/ai-features). Trust depends on useful, evidenced information: [Helpful, reliable, people-first content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content). Commercial structured-data eligibility depends on real supported fields: [Product snippet requirements](https://developers.google.com/search/docs/appearance/structured-data/product-snippet).
