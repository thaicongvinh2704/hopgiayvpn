<?php
/**
 * Apply the reviewed product-category assignments.
 *
 * Usage:
 *   php tools/reclassify-product-categories-20260610.php --dry-run
 *   php tools/reclassify-product-categories-20260610.php --apply
 */

require dirname(__DIR__) . '/wp-load.php';

$assignments = custom_box_reviewed_category_sync_assignments();
foreach ($assignments as $product_id => $slugs) {
    echo get_post_field('post_name', $product_id) . ': ' . implode(', ', $slugs) . PHP_EOL;
}

if (!in_array('--apply', $argv, true)) {
    echo "Dry run only. Re-run with --apply to update categories.\n";
    exit(0);
}

$result = custom_box_reviewed_category_sync_apply();
if ($result['error']) {
    fwrite(STDERR, $result['error'] . PHP_EOL);
    exit(1);
}

echo 'Category migration completed. Updated: ' . $result['updated'] . '; unchanged: ' . $result['unchanged'] . PHP_EOL;
echo 'Backup table: ' . $result['backup_table'] . PHP_EOL;
