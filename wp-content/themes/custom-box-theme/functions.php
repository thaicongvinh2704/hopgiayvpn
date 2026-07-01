<?php
/**
 * Theme bootstrap.
 *
 * Keep this file small: feature code lives in /inc.
 */

$custom_box_inc_files = array(
    'inc/setup.php',
    'inc/dev.php',
    'inc/enqueue.php',
    'inc/seo.php',
    'inc/breadcrumbs.php',
    'inc/admin-editor.php',
    'inc/admin-unused-page-cleanup.php',
    'inc/search-suggestions.php',
    'inc/cosmetic-packaging-post-sync.php',
    'inc/how-paper-boxes-post-sync.php',
    'inc/paper-box-manufacturing-process-post-sync.php',
    'inc/paper-box-printing-guide-post-sync.php',
    'inc/cmyk-vs-pantone-paper-box-printing-post-sync.php',
    'inc/matte-vs-gloss-lamination-post-sync.php',
    'inc/foil-stamping-embossing-post-sync.php',
    'inc/paper-box-dieline-post-sync.php',
    'inc/paper-material-selection-post-sync.php',
    'inc/paper-thickness-post-sync.php',
    'inc/paper-box-durability-post-sync.php',
    'inc/paper-box-structure-selection-post-sync.php',
    'inc/paper-box-quality-control-checklist-post-sync.php',
    'inc/common-paper-box-defects-post-sync.php',
    'inc/how-inserts-protect-products-paper-boxes-post-sync.php',
    'inc/reduce-paper-box-damage-shipping-post-sync.php',
    'inc/logo-printing-post-sync.php',
    'inc/cardboard-wine-cartons-materials-uses-post-sync.php',
    'inc/artwork-printed-paper-boxes-post-sync.php',
    'inc/printed-packaging-sample-check-post-sync.php',
    'inc/what-affects-paper-packaging-lead-time-post-sync.php',
    'inc/obsolete-packaging-materials-page.php',
    'inc/supplier-audit-post-sync.php',
    'inc/admin-category-fields.php',
    'inc/product-specifications.php',
    'inc/product-category-migration.php',
    'inc/admin-reviewed-category-sync.php',
    'inc/admin-local-product-category-sync.php',
    'inc/sports-packaging-category-sync.php',
    'inc/admin-unused-product-category-cleanup.php',
    'inc/admin-product-sample-deploy.php',
    'inc/quote-form-handler.php',
    'inc/woocommerce.php',
);

foreach ($custom_box_inc_files as $custom_box_inc_file) {
    require_once get_template_directory() . '/' . $custom_box_inc_file;
}
