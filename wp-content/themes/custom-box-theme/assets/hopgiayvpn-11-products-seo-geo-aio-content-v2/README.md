# HopgiayVPN — 11 product content V2

This package replaces the earlier thin descriptions. Each product folder contains separate WooCommerce short and long descriptions, SEO fields, product DNA, 21 specification rows, image-token manifest, and implementation notes.

## Content standard

- 120–180 visible English words in the short description.
- 1,500–2,000 visible English words in the long description, with no content H1.
- Product-specific buyer intent, suitability limits, structure, sizing, insert, artwork, material, sampling, quality control, RFQ checklist, and FAQs.
- 3–4 inline images where the source gallery supports them; Product 08 uses two images and remains Draft.
- 4+ natural internal links per page.
- No invented price, lead time, certification, rating, review, or test result.

## Import

Use the files in each product folder. Replace `{{MEDIA_URL:filename}}` tokens after matching filenames to WordPress Media Library attachments. Keep the visible FAQ in the page; do not add FAQ structured data unless it accurately mirrors the visible content and is supported by the active SEO setup.

See `quality-audit.csv` for machine checks and `schema-and-import-guidance.md` for SEO/GEO/AIO notes.
