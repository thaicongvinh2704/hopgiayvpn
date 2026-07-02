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
    'inc/admin-category-fields.php',
    'inc/product-specifications.php',
    'inc/product-category-migration.php',
    'inc/admin-local-product-category-sync.php',
    'inc/admin-product-category-local-prune.php',
    'inc/admin-unused-product-category-cleanup.php',
    'inc/admin-product-sample-deploy.php',
    'inc/quote-form-handler.php',
    'inc/woocommerce.php',
);

foreach ($custom_box_inc_files as $custom_box_inc_file) {
    require_once get_template_directory() . '/' . $custom_box_inc_file;
}
