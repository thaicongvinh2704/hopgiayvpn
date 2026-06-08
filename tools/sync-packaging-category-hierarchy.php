<?php
/**
 * Sync WooCommerce packaging category hierarchy to the active theme taxonomy.
 *
 * Usage:
 *   php tools/sync-packaging-category-hierarchy.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

if (!function_exists('custom_box_category_migration_sync_hierarchy') || !function_exists('custom_box_get_packaging_parent_category')) {
    fwrite(STDERR, "Custom packaging category sync helpers are not available.\n");
    exit(1);
}

$parent = custom_box_get_packaging_parent_category();

if (!$parent || is_wp_error($parent)) {
    fwrite(STDERR, "Missing parent category: custom-packaging-boxes.\n");
    exit(1);
}

$result = custom_box_category_migration_sync_hierarchy();

if (is_wp_error($result)) {
    fwrite(STDERR, $result->get_error_message() . "\n");
    exit(1);
}

echo 'Packaging parent: ' . $parent->name . ' (' . $parent->slug . ')' . PHP_EOL;
echo 'Active categories attached: ' . (int) $result['attached'] . PHP_EOL;
echo 'Inactive old children detached: ' . (int) $result['detached'] . PHP_EOL;

if (!empty($result['missing'])) {
    echo 'Missing active categories: ' . implode(', ', $result['missing']) . PHP_EOL;
}
