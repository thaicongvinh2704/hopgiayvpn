<?php
/**
 * Load the product category manifest sync independently of the active theme bootstrap.
 */

$custom_box_product_category_sync_file = WP_CONTENT_DIR . '/themes/custom-box-theme/inc/product-category-manifest-sync.php';

if (is_readable($custom_box_product_category_sync_file)) {
    require_once $custom_box_product_category_sync_file;
}
