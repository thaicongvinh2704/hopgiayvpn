<?php
/**
 * Delete empty product categories that are not part of the active packaging taxonomy.
 *
 * Usage:
 *   php tools/delete-unused-product-categories.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_unused_product_category_cleanup_delete')) {
    fwrite(STDERR, "Product category cleanup helper is not available.\n");
    exit(1);
}

$result = custom_box_unused_product_category_cleanup_delete();

if (is_wp_error($result)) {
    fwrite(STDERR, $result->get_error_message() . PHP_EOL);
    exit(1);
}

echo 'Deleted empty categories: ' . count($result['deleted']) . PHP_EOL;

if ($result['deleted']) {
    echo implode(', ', $result['deleted']) . PHP_EOL;
}

if ($result['skipped']) {
    echo 'Skipped non-empty or failed categories: ' . implode(', ', $result['skipped']) . PHP_EOL;
}
