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
    'inc/privacy-policy.php',
    'inc/paper-bag-ads-landing.php',
    'inc/custom-paper-bags-manufacturer-landing.php',
    'inc/breadcrumbs.php',
    'inc/search-suggestions.php',
    'inc/product-specifications.php',
    'inc/quote-form-handler.php',
    'inc/woocommerce.php',
);

if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
    $custom_box_inc_files = array_merge(
        $custom_box_inc_files,
        array(
            'inc/admin-editor.php',
            'inc/admin-unused-page-cleanup.php',
            'inc/admin-category-fields.php',
            'inc/product-category-manifest-sync.php',
            'inc/product-category-migration.php',
            'inc/admin-local-product-category-sync.php',
            'inc/admin-product-category-local-prune.php',
            'inc/admin-unused-product-category-cleanup.php',
            'inc/admin-product-sample-deploy.php',
            'inc/post-sync-loader.php',
        )
    );
}

foreach ($custom_box_inc_files as $custom_box_inc_file) {
    require_once get_template_directory() . '/' . $custom_box_inc_file;
}

if (is_admin()) {
    require_once get_template_directory() . '/inc/custom-vial-box-product-sync.php';
} else {
    add_action('wp', static function () {
        if (
            is_singular('product')
            && 'custom-vial-packaging-box' === get_post_field('post_name', get_queried_object_id())
        ) {
            require_once get_template_directory() . '/inc/custom-vial-box-product-sync.php';
        }
    }, 1);
}

if (function_exists('custom_box_post_sync_files_to_load')) {
    foreach (custom_box_post_sync_files_to_load() as $custom_box_post_sync_file) {
        require_once get_template_directory() . '/' . $custom_box_post_sync_file;
    }
}
