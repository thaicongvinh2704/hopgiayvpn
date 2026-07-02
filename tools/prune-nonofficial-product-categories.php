<?php
/**
 * Prune WooCommerce product categories that are not part of the local manifest set.
 *
 * Usage:
 *   php tools/prune-nonofficial-product-categories.php --dry-run
 *   php tools/prune-nonofficial-product-categories.php --apply
 *   php tools/prune-nonofficial-product-categories.php --apply --no-sync
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

if (!function_exists('custom_box_product_category_local_prune_preview')) {
    fwrite(STDERR, "Local product category prune helper is not available.\n");
    exit(1);
}

$apply = in_array('--apply', $argv, true);
$dry_run = in_array('--dry-run', $argv, true) || !$apply;
$sync_first = !in_array('--no-sync', $argv, true);

$preview = custom_box_product_category_local_prune_preview();

if (is_wp_error($preview)) {
    fwrite(STDERR, $preview->get_error_message() . PHP_EOL);
    exit(1);
}

echo 'Current product categories: ' . $preview['total_terms'] . PHP_EOL;
echo 'Local protected set: ' . $preview['protected_count'] . PHP_EOL;
echo 'Non-local categories selected: ' . $preview['delete_count'] . PHP_EOL;
echo 'Projected category count: ' . $preview['projected_total'] . PHP_EOL;

foreach ($preview['matches'] as $term) {
    echo sprintf(
        "- %s (%s) products:%d parent:%s term_id:%d\n",
        $term['name'],
        $term['slug'],
        (int) $term['count'],
        $term['parent_slug'] ?: 'none',
        (int) $term['id']
    );
}

if ($dry_run) {
    echo "Dry run only. Re-run with --apply to back up and delete these categories.\n";
    exit(0);
}

$result = custom_box_product_category_local_prune_apply($sync_first);

if (is_wp_error($result)) {
    fwrite(STDERR, $result->get_error_message() . PHP_EOL);
    exit(1);
}

echo 'Applied local manifest before prune: ' . ($sync_first ? 'yes' : 'no') . PHP_EOL;
echo 'Backup file: ' . $result['backup_file'] . PHP_EOL;
echo 'Deleted categories: ' . count($result['deleted']) . PHP_EOL;

if (!empty($result['deleted'])) {
    echo implode(', ', $result['deleted']) . PHP_EOL;
}

if (!empty($result['skipped'])) {
    echo 'Skipped categories: ' . implode(', ', $result['skipped']) . PHP_EOL;
}

echo 'Category count: ' . $result['before_total'] . ' -> ' . $result['after_total'] . PHP_EOL;
echo 'Remaining extra categories: ' . $result['remaining_extra'] . PHP_EOL;
