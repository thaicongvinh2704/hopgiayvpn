# Custom Bakery & Bread Paper Bags — local implementation

Date: 26 October 2026. Implementation and verification took place on local WordPress. This release is prepared for Git delivery; the owner will pull and deploy it. Hosting has not been changed by this task.

## Result

- URL: http://localhost/hopgiayvpn/products/custom-bakery-bread-paper-bags/
- Category ID on local: 1264; parent: Paper Bags with Logo. IDs differ on hosting; the importer resolves product and category slugs.
- Nine existing published products assigned additively. Product titles, content, excerpts, images, URLs and previous category assignments were preserved and checked against the scoped backup.
- Existing header, navigation, archive hero, catalog cards, palette and button styles retained. The new guide uses the existing category components. On mobile, the two comparison tables become stacked rows with labels, avoiding horizontal scrolling.

## Keyword and page roles

Primary keyword: **custom bakery paper bags**.

SEO title: **Custom Bakery Paper Bags & Bread Bags Wholesale | VPN** (53 characters).

Meta description: **Custom bakery paper bags for bread, baguettes and pastries. Order wholesale with your logo, custom sizes and window options from VPN in Vietnam. Get a quote.** (157 characters).

H1/category name: **Custom Bakery & Bread Paper Bags**.

Supporting topics are used where relevant: custom printed bread bags, bakery bags wholesale, baguette paper bags, paper bread bags with window, greaseproof bakery bags, and repeat supply. No keyword volume or ranking outcome is claimed.

The new category is an application collection covering bakery formats, including greaseproof options. Kraft remains a material collection. Its existing bread/bakery advertising landing, title and URL were not replaced. Cross-links help customers compare the ranges; future GSC data should determine whether the kraft landing needs further differentiation. No redirects, deletions, canonical consolidation or Ads URL changes were made.

The paper bag manufacturer money page remains the general manufacturing and enquiry destination. New category links were added naturally to its existing catalog paragraph, the kraft landing and the kraft catalog guide. The parent category also exposes the new child through its existing category navigation.

## Content and evidence

The English guide includes:

- Six bag construction/use comparisons linked to existing products.
- Paper choice, grease barrier and window specifications.
- Instructions for filled dimensions, printing and artwork placement.
- Direct food contact, time/temperature and destination requirements, without unsupported universal food-safe or certification claims.
- General MOQ of 1,000, quantities per design, large recurring orders and shipment batches.
- Design support, sample fees by design, sampling 7–15 days, refund of 50% of sample fee on orders **over** 5,000 pieces, production 7–45 days after sample approval; timelines conditional on the order, shipping separate.
- QC at each stage and carton/shrink-bundle packing.
- Three existing VPN workshop images with captions and the existing factory tour video launch control. These are explicitly presented as the wider packaging operation, not proof of a dedicated bakery bag production line. Catalog product imagery is not described as verified customer projects.
- Eight visible FAQs and quotation calls to action.

No invented author, certification, production capacity, customer logo or review was added.

## SEO and technical implementation

- Saved Rank Math term title, description, focus keyword, index/follow settings and social metadata; confirmed the actual local plugin is Rank Math 1.0.269.
- Scoped CollectionPage, ItemList and BreadcrumbList. Product ItemList follows the visible 12-item catalog pagination and sorting; default and date sorting were checked.
- Canonical URL is resolved dynamically for the current domain and page; no localhost canonical is stored for deployment.
- The existing local isolation intentionally emits `noindex, nofollow` and suppresses the canonical tag. This protection was preserved. Production indexability and sitemap output must be checked after an approved deployment; local completion is not a claim of indexing or AI inclusion.
- No extra scripts were added for this category. Workshop video uses the existing click-to-load player. Category-specific CSS is loaded only for this category.
- Release import is versioned and repeatable. It validates the nine published products/images first, refuses an unmanaged category with the same slug, and appends category assignments. It does not import or recreate products. A missing deployment dependency is recorded in `vpn_bakery_category_sync_status`; failures are throttled to avoid a database scan on every visitor request.

## Verification performed

- PHP syntax checks passed for all changed PHP templates/features.
- Scoped importer ran twice: same category ID, nine assignments, no duplicate category, no unexpected product/category changes.
- Rendered HTTP checks: category, local images, stylesheets and internal content links returned 200. See `outputs/bakery-category-20261026/verification.json` for the resource list.
- One H1, exact saved title/description, nine visible products, eight FAQs, three workshop photographs and video launch control verified.
- ItemList matches product display for default and newest sorting. Incoming links from parent, kraft and manufacturer pages verified.
- Browser checked at the existing desktop viewport and 390 × 844 mobile viewport. No horizontal page overflow. Both mobile tables fit their containers; FAQ expansion worked. Loaded workshop images verified.
- Clicked the category quote CTA: destination is `/custom-paper-bags-manufacturer/#quote`, with a required email field and `autocomplete="email"`. No test customer enquiry was submitted.
- Desktop/mobile screenshots saved under `outputs/bakery-category-20261026/`.

Local apply command: `C:/xampp/php/php.exe tools/apply-bakery-category-local-20261026.php --apply`. It is CLI-only, rejects non-local environments and saves a scoped before-state. The theme itself contains the versioned deploy sync, following the existing category deployment pattern. A complete hosting database backup remains part of the owner's normal deployment process; these scoped files are not a substitute.

## Research references

Category language and specification comparison, not copied sales claims:

- [KRPA — Window Bread Bags](https://www.krpa-form.cz/en/products/paper-bags/window-bread-bags)
- [Wisconsin Converting — Bread and Bakery Bags](https://www.wcibags.com/wp-content/uploads/2019/02/Bread-and-Bakery-Bags-Product-Flyer.pdf)

Primary references linked in the food-contact section:

- [FDA — Food Types & Conditions of Use](https://www.fda.gov/food/packaging-food-contact-substances-fcs/food-types-conditions-use-food-contact-substances)
- [European Commission — Food contact materials](https://food.ec.europa.eu/food-safety/chemical-safety/food-contact-materials/general-information-and-contacts_en)

AI visibility approach follows [Google Search Central — AI features and your website](https://developers.google.com/search/docs/appearance/ai-features): useful textual content, crawlable internal links, supporting media and structured data consistent with the page. No special AI schema or guaranteed citation/ranking is claimed.

## Changed files for a later scoped Git review

New theme files:

- `inc/bakery-bread-paper-bags-category.php`
- `inc/bakery-bread-paper-bags-category.json`
- `assets/css/bakery-bread-paper-bags.css`
- `template-parts/woocommerce/bakery-bread-paper-bags-guide.php`

Small integration changes in the existing theme:

- `functions.php`: load the new feature.
- `woocommerce/archive-product.php`: new category context, jump links and contextual bottom CTA.
- `template-parts/woocommerce/archive-hero.php`: parent breadcrumb for bakery.
- `template-parts/woocommerce/product-list.php`: descriptive product alt text scoped to bakery.
- `template-parts/woocommerce/related-categories.php`: related bag/kraft/bakery-box links scoped to bakery.
- `template-parts/woocommerce/kraft-workshop.php`: optional order text/CTA arguments, existing defaults retained.
- `template-parts/woocommerce/kraft-paper-bags-guide.php`: one relevant link.
- `template-parts/woocommerce/kraft-bakery-landing.php`: one relevant link.
- `page-custom-paper-bags-manufacturer.php`: one contextual link in the existing catalog paragraph.

Some files were already modified by earlier work. Stage only this task's reviewed hunks if committing; do not include unrelated pending changes.
