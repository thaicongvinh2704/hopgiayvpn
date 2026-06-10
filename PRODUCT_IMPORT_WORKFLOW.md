# Product Import Workflow

This file is the working rulebook for creating WooCommerce product pages from uploaded packaging images for VPN Paper Box Manufacturer. Read this before creating, rewriting, importing, or deploying product batches.

## Goal

Create international B2B WooCommerce product pages for custom paper boxes, paper bags, and paper packaging products. Content must be useful, specific to the product, SEO-friendly, and not thin/template-like.

Work local first. Do not commit, push, or deploy product/media changes until the user approves.

## Image Grouping

1. Source folder is usually `wp-content/uploads/2026/05`.
2. Ignore generated thumbnails and resized files:
   - `-100x100`
   - `-150x150`
   - `-300x300`
   - any `-[number]x[number]`
   - `-scaled`
3. Treat original files as product source images.
4. Group product images by base filename:
   - `custom-ampoule-packaging-box-1.webp`
   - `custom-ampoule-packaging-box-2.webp`
   - `custom-ampoule-packaging-box-3.webp`
5. Use `-1` or the most vertical/hero-like image as featured image when available.
6. Use related numbered images as gallery images.
7. Insert 3-4 product images inside the long description when gallery has enough images.
8. If a product has fewer than 3 images, do not repeat the same image unnaturally just to reach a count.
9. Do not treat different filenames as enough by themselves. Images in one product must show clear visual variety, such as closed box, open box, insert/detail, side angle, retail display, or different structure. If several images are the same composition with only small color, garment, logo, or crop changes, mark the product as image-duplicate risk and replace or reduce those images before import.

## Product Fields To Keep

When rewriting existing generated products, preserve:

- Product name
- Slug
- Category
- Tags
- Focus keyword
- SEO title
- Meta description
- Featured image filename
- Gallery image filenames
- Existing image attachments

Rewrite only content and supporting SEO text when requested:

- Short description
- Long description
- Headings inside long description
- CTA
- Internal links
- Product-specific details
- Specifications if needed

## Naming Rules

Product name comes from the image filename.

Example:

- Filename: `custom-drawer-box-1.webp`
- Product name: `CUSTOM DRAWER BOX`
- Slug: `custom-drawer-box`

If slug duplicates an existing product, create a meaningful variation, not a random suffix.

## Short Description

Write in English.

Length target: 120-180 words.

Must include:

- Product name or keyword
- Industry/product use
- Specific packaging problem
- Structure/material/customization options
- Buyer type
- MOQ if appropriate

Avoid generic repeated phrasing such as:

> suitable for protection, retail display, brand identity, and export packaging

Use product-specific language instead.

## Long Description

Write in English for international B2B buyers.

Length target: 1500-2000 words per product.

Important:

- Do not include an H1 in long description. Product template already has product H1.
- Use H2/H3 only inside product content.
- Use 7-10 sections.
- Do not use the same heading set across all products.
- Do not use the same paragraph structure across all products.
- Do not simply replace product names in a template.
- At least 50% of the content must be unique to the product/industry.
- Each product must include at least 8 product-specific details.

Recommended content balance:

- 30% product-specific packaging problem
- 20% box/bag structure
- 15% insert/protection/display logic
- 15% industry-specific print layout
- 10% material and finishing
- 10% CTA and quote information

## Product DNA

Before writing each product, define Product DNA:

- Product name
- Main keyword
- Product category
- Industry
- Target buyer
- Product inside
- Main packaging problem
- Best box/bag structure
- Insert/protection design
- Retail/display/shipping requirement
- Printing information needed
- Material recommendation
- Finishing recommendation
- B2B use case
- Quote information needed
- Internal links
- Duplicate group

## Duplicate Risk Rules

High-risk pairs require strong differentiation:

- Colored Pencil Box vs Crayon Box
- Ampoule Box vs Cosmetic Box
- Charging Cable Box vs Phone Case Box
- Corporate Gift Set Box vs Supplement Drawer Box

After writing, assign duplicate risk score:

- 1-3: Low
- 4-6: Medium, acceptable if product details are distinct
- 7-10: Too high, rewrite before showing user

If risk is high, change:

- Angle
- Heading set
- Product examples
- Insert/protection details
- Buyer type
- CTA
- Internal link mix
- Material/printing explanation

## Content Angles

Use different angles by product group.

### Fragile Product Packaging

For ampoule, perfume, essential oil, candle, dinnerware, glass bottles.

Focus on:

- Fragile protection
- Anti-movement insert
- Paper tray / EVA / molded pulp / corrugated partition
- Shipping protection
- Premium shelf presentation
- Export packing requirement

### Retail Display Packaging

For charging cable, phone case, screen protector, small electronics.

Focus on:

- Hang tab
- Window display
- Model/SKU information
- Compatibility icons
- Barcode/warranty/QR layout
- High-volume retail consistency

### Premium Gift Packaging

For corporate gift set, watch box, jewelry box, wine gift box, magnetic rigid box.

Focus on:

- Unboxing experience
- Rigid structure
- Insert layout
- Campaign/gifting purpose
- Premium finishing
- Brand presentation

Supplement drawer boxes may look premium, but should focus more on health-product trust, bottle fit, dosage information, certification marks, and routine kits. Do not write them like generic corporate gift boxes.

### Stationery / School / Art Supplies Packaging

For colored pencil, crayon, marker, notebook set, stationery kit.

Colored pencil focus:

- Color order
- Pencil count
- Color chart
- Artist/student positioning
- Sharpened tip protection
- Reusable tray

Crayon focus:

- Child-friendly graphics
- Non-toxic/safety marks
- Age range
- Easy opening
- Wax stick divider
- Classroom/back-to-school bulk use

## Internal Links

Each product should have 3-5 internal links.

Must include:

- 1 link to parent category
- 1-2 links to related products
- 1 link to supporting guide/blog if available
- 1 CTA link to Contact/Catalog when appropriate

Use natural anchor text. Do not use repeated anchors across every product.

Avoid anchors like:

- click here
- read more
- this page
- here

Good anchors:

- cosmetic packaging boxes for skincare lines
- phone case retail packaging with model labels
- paper material options for fragile beauty packaging
- request a supplement drawer box quote

Do not link to irrelevant products just to increase link count.

## In-Content Images

Use product images inside long description.

Rules:

- Target 3-4 images per product if enough gallery images exist.
- Use featured image plus gallery images.
- Place images naturally between sections.
- Do not duplicate the same image repeatedly if the product only has one image.
- Do not insert multiple near-identical images just because they have different filenames. Prefer 2 strong, distinct images over 4 images that look like the same generated mockup.
- Product content images should not be too large.
- Current CSS class used for inline content images:
  - `product-inline-figure`
  - `product-inline-figure-small`
  - optional `is-narrow`
- Do not create a new inline image class unless the theme CSS is updated for it.
- Batch scripts must output figures like:
  - `<figure class="product-inline-figure product-inline-figure-small">`
  - `<figure class="product-inline-figure product-inline-figure-small is-narrow">`
- The current theme CSS limits these images to about `560px` wide, or `480px` wide with `is-narrow`.
- Avoid `<figure class="vpn-product-inline-image">`; it has no size rule and can make product description images too large.

Long description image captions should be product-specific.

## Specifications

Specifications are stored in product meta:

`_custom_box_product_specs`

Use the theme format from `wp-content/themes/custom-box-theme/inc/product-specifications.php`.

Required rows:

- Feature
- Industrial Use
- Paper Type
- Box Type
- Shape
- Place of Origin
- Model Number
- Brand Name
- Province
- Accessories
- Custom Order
- Liner Type
- Logo Printing
- Printing Handling
- Color
- Size
- Thickness
- Single Piece Price
- Minimum Order Quantity (MOQ)
- Product Name
- Design

Global fixed values:

- Place of Origin: `Vietnam`
- Brand Name: `VPN`
- Province: `Ho Chi Minh City`
- Custom Order: `Accept`
- Logo Printing: `Custom logo`
- Size: `Customized size`
- Thickness: `Customized thickness`
- Minimum Order Quantity (MOQ): `1000 boxes`
- Design: `Customer's Specific Requirement`

Other values should be product-specific.

## SEO Fields

SEO title format:

`PRODUCT NAME | VPN PAPER BOX MANUFACTURER`

Meta description:

- Under 155 characters when possible
- Product-specific
- Mention product use and customization

Focus keyword:

Use the main keyword derived from filename.

Example:

- `custom-pill-packaging-box-3.webp`
- Focus keyword: `pill packaging box`

Image alt text formula:

`[Product type] + [industry use] + [visible feature in image]`

## Local First Workflow

1. Generate/import products locally first.
2. Product status can be `publish` for local review if the user needs to see frontend.
3. Do not add to Git until user approves.
4. Do not push media/products before approval.
5. If user rejects, delete or rewrite local products only.

## Verification Commands / Scripts

Existing helper scripts may include:

- `tools/import-product-samples-10.php`
- `tools/verify-product-samples-10.php`
- `tools/update-product-samples-rich-content.php`
- `tools/rewrite-product-samples-batch-1.php`
- `tools/rewrite-product-samples-batch-1-extra.php`
- `tools/expand-product-samples-batch-1.php`
- `tools/top-up-product-samples-batch-1.php`
- `tools/export-product-sample-descriptions.php`
- `tools/verify-rich-product-samples-10.php`
- `tools/verify-product-samples-content-shape.php`

Before telling user the batch is ready, verify:

- Product pages return HTTP 200
- Content has 0 H1 inside long description
- Word count is 1500-2000 where requested
- Specifications exist and MOQ is `1000 boxes`
- Product images are attached
- In-content figures are present when enough images exist
- Text-only export file is updated

## Review Files

When user wants to check content similarity, export text-only descriptions:

`product-samples-10-descriptions-text-only.md`

When user wants duplicate audit:

`product-samples-10-rewrite-audit.md`

These files are for review and should not be treated as final deploy artifacts unless user approves.

## Current Batch 1 Products

1. Custom Ampoule Packaging Box
2. Custom Charging Cable Packaging Box
3. Custom Colored Pencil Packaging Box
4. Custom Corporate Gift Set Packaging Boxes
5. Custom Cosmetic Packaging Box
6. Custom Cosmetic Paper Bag
7. Custom Crayon Packaging Box
8. Custom Dinnerware Packaging Box
9. Custom Phone Case Packaging Box
10. Custom Supplement Drawer Packaging Box

## Current Batch 2 Products

Local WooCommerce batch marker:

- `_vpn_sample_import = product-samples-batch-2-five`

Products:

1. Custom Double Wine Bottle Gift Box
2. Custom Drawer Gift Box
3. Custom Fountain Pen Gift Box
4. Custom Knife Set Packaging Box
5. Custom Kraft Paper Bag for Supplement Packaging

Review/export file:

- `product-samples-batch-2-five-descriptions-text-only.md`

Main scripts:

- `tools/import-product-samples-batch-2-five.php`
- `tools/verify-product-samples-batch-2-five.php`
- `tools/export-product-samples-batch-2-five-descriptions.php`

## Current Batch 3 Products

Local WooCommerce batch marker:

- `_vpn_sample_import = product-samples-batch-3-ten`

Products:

1. Custom Luxury Gift Box With Paper Bag
2. Custom Magnetic Gift Box
3. Custom Medical Kit Packaging Box
4. Custom Mug Packaging Box With Window
5. Custom Paper Tube Food Packaging Box
6. Custom Phone Packaging Box
7. Custom Pill Packaging Box
8. Custom Printed Corrugated Pet Food Box
9. Custom Rigid Gift Box
10. Custom Single Wine Bottle Gift Box

Review/export file:

- `product-samples-batch-3-ten-descriptions-text-only.md`

Main scripts:

- `tools/import-product-samples-batch-3-ten.php`
- `tools/top-up-product-samples-batch-3-ten.php`
- `tools/top-up-product-samples-batch-3-final.php`
- `tools/top-up-product-samples-batch-3-text-floor.php`
- `tools/verify-product-samples-batch-3-ten.php`
- `tools/export-product-samples-batch-3-ten-descriptions.php`

## Current Remaining Custom Image Groups

After batch 3, `tools/audit-unused-upload-product-groups.php` reports:

- Custom product groups found: 53
- Already imported groups: 31
- Unused custom product groups: 22
- Unused original images in those groups: 54

Some remaining filename groups are separate angles of one product and should be grouped before import, for example:

- `custom-red-paper-shopping-bag`, `custom-red-paper-shopping-bag-inside`, `custom-red-paper-shopping-bag-open`
- `custom-teal-rigid-gift-box`, `custom-teal-rigid-gift-box-detail`, `custom-teal-rigid-gift-box-inside`, `custom-teal-rigid-gift-box-open`

## Fashion and Sportswear Batch

Local WooCommerce batch marker:

- `_vpn_sample_import = product-samples-fashion-sportswear`

Temporary source image folder:

- `wp-content/themes/fasion`

Important:

- The `fasion` folder is only a temporary grouping/reference folder.
- It shows which images belong to which product.
- The import script must copy those images into the WordPress uploads folder and create Media Library attachment records.
- Product featured images, galleries, and inline content images must use Media Library attachments, not direct theme-folder image paths.
- Keep the `fasion` folder in Git only until hosting has pulled and run the import. After hosting import is complete and verified, the temporary folder can be removed if it is no longer needed.

Products:

1. Custom Shoe Packaging Box
2. Custom Belt Packaging Box
3. Custom Men Underwear Packaging Box
4. Custom Sportswear Packaging Box
5. Custom T-Shirt Packaging Box
6. Custom Wallet Packaging Box

Main scripts:

- `tools/import-fashion-sportswear-products.php`
- `tools/verify-fashion-sportswear-products.php`

## Deploying Product Samples From Git

Important:

- WooCommerce products are database records, so Git cannot deploy products by committing the local database rows.
- The safe workflow is to commit:
  - original product images in `wp-content/uploads/2026/05`
  - product import/migration scripts in `tools/`
  - theme code and workflow files
- After `git pull` on hosting, run one deploy command:
  - `php tools/deploy-product-samples-all.php`

The master deploy script:

- checks each product batch by `_vpn_sample_import`
- skips batches that already have the expected product count, 1500+ word content, 0 content H1, 21 specs rows, and MOQ `1000 boxes`
- runs only incomplete/missing batches
- imports products, image attachments, categories, tags, specs, SEO meta, featured images, galleries, and long descriptions
- runs verify scripts after import

Recommended hosting deploy order:

1. `git pull`
2. `php tools/deploy-product-samples-all.php`
3. clear cache if the hosting has page/object cache

### Sports Packaging batch (June 2026)

The sports batch imports four products from 18 bundled images:

- `CUSTOM SPORTS SHOE PACKAGING BOX`
- `PREMIUM PICKLEBALL SET RIGID PAPER BOX`
- `CUSTOM KNEE SUPPORT PACKAGING BOX`
- `CUSTOM SPORTS UNDERWEAR PACKAGING BOX`

Files used:

- `tools/import-sports-packaging-products.php`
- `tools/verify-sports-packaging-products.php`
- `wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets/uploads/2026/06/`

After `git pull` on hosting, either run:

- `php tools/deploy-product-samples-all.php`

or open **Tools > Product Sample Deploy** and run the latest batch. The importer creates missing WordPress media attachments from the bundled theme images before publishing the products.

Do not manually create these products in WooCommerce admin unless the deploy script fails and the issue has been diagnosed.

Current batch 1 local verification after rewrite:

- All 10 products published locally for preview
- Content description has 0 H1
- Specifications have 21 rows
- MOQ is `1000 boxes`
- Text-only export exists
- Duplicate audit exists

Do not assume batch 1 is approved for Git/deploy until user explicitly approves.
