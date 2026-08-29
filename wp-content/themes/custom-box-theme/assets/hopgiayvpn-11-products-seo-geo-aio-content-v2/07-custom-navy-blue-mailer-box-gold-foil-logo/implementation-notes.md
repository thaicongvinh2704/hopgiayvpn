# Implementation notes — Custom Navy Blue Mailer Box with Gold Foil Logo

- Target status: **Publish**.
- Replace every `{{MEDIA_URL:filename}}` token with the exact WordPress Media Library URL/attachment for that filename.
- Paste `short-description.html` into WooCommerce short description and `long-description.html` into the main description. Do not add an H1 inside either field.
- Import the 21 rows in `specifications.json` into `_custom_box_product_specs` using the theme's existing data format.
- Set Rank Math title, description, and focus keyword from `seo-fields.json`.
- Use WooCommerce's existing Product schema integration. Do not add a second competing Product object, fake reviews, ratings, prices, availability, certifications, or FAQ schema unless the visible FAQ and site implementation support it.
- Product 08 must remain Draft because only two source images are available.
