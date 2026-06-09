<?php
/**
 * Apply the active packaging category targets to WooCommerce products.
 *
 * Usage:
 *   php tools/apply-packaging-category-targets.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

if (!function_exists('custom_box_category_migration_apply_products_to_targets')) {
    fwrite(STDERR, "Custom packaging category migration helper is not available.\n");
    exit(1);
}

$updated = custom_box_category_migration_apply_products_to_targets();

echo 'Updated products: ' . (int) $updated . PHP_EOL;
