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
    'inc/custom-vial-box-product-sync.php',
    'inc/pharmaceutical-paper-packaging-information-post-sync.php',
    'inc/quote-form-handler.php',
    'inc/woocommerce.php',
);

foreach ($custom_box_inc_files as $custom_box_inc_file) {
    require_once get_template_directory() . '/' . $custom_box_inc_file;
}

function custom_box_should_load_post_sync_files(): bool
{
    if (!is_admin()) {
        return false;
    }

    if ((function_exists('wp_doing_ajax') && wp_doing_ajax()) || (defined('REST_REQUEST') && REST_REQUEST) || (defined('DOING_CRON') && DOING_CRON)) {
        return false;
    }

    if (isset($_GET['custom_box_run_post_syncs']) && '1' === sanitize_text_field(wp_unslash($_GET['custom_box_run_post_syncs']))) {
        return true;
    }

    $pagenow = isset($GLOBALS['pagenow']) ? (string) $GLOBALS['pagenow'] : '';

    return in_array($pagenow, array('index.php', 'tools.php'), true);
}

$custom_box_post_sync_files = array(
    'inc/cosmetic-paper-packaging-design-post-sync.php',
    'inc/skincare-paper-packaging-selection-post-sync.php',
    'inc/food-paper-packaging-selection-post-sync.php',
    'inc/bakery-paper-packaging-material-selection-post-sync.php',
    'inc/cosmetic-brand-perception-post-sync.php',
    'inc/perfume-paper-box-structure-post-sync.php',
    'inc/jewelry-paper-box-packaging-post-sync.php',
    'inc/how-to-choose-candle-packaging-materials-post-sync.php',
    'inc/how-to-package-chocolate-gift-sets-post-sync.php',
    'inc/how-to-create-premium-food-packaging-with-paper-boxes-post-sync.php',
    'inc/how-to-protect-bottles-in-paper-gift-packaging-post-sync.php',
    'inc/how-to-design-supplement-packaging-layout-post-sync.php',
);

if (custom_box_should_load_post_sync_files()) {
    foreach ($custom_box_post_sync_files as $custom_box_post_sync_file) {
        require_once get_template_directory() . '/' . $custom_box_post_sync_file;
    }
}
