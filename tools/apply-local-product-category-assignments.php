<?php
/**
 * Apply the committed local product/category manifest.
 *
 * Usage:
 *   php tools/apply-local-product-category-assignments.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_local_product_category_sync_apply')) {
    fwrite(STDERR, "Local product category sync helper is not available.\n");
    exit(1);
}

$result = custom_box_local_product_category_sync_apply();

if (!empty($result['error'])) {
    fwrite(STDERR, 'Sync failed: ' . $result['error'] . PHP_EOL);
    exit(1);
}

echo 'Sync completed.' . PHP_EOL;
echo 'Updated products: ' . (int) $result['updated'] . PHP_EOL;
echo 'Unchanged products: ' . (int) $result['unchanged'] . PHP_EOL;
echo 'Missing products: ' . (int) $result['missing_products'] . PHP_EOL;
echo 'Detached extra products: ' . (int) $result['detached_extra_products'] . PHP_EOL;
echo 'Backup table: ' . $result['backup_table'] . PHP_EOL;
