<?php
/**
 * Delete empty product categories that are not part of the active packaging taxonomy.
 *
 * Usage:
 *   php tools/delete-unused-product-categories.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

if (!function_exists('custom_box_category_migration_targets')) {
    fwrite(STDERR, "Custom packaging category migration helper is not available.\n");
    exit(1);
}

$keep_slugs = array_keys(custom_box_category_migration_targets());
$keep_slugs[] = 'custom-packaging-boxes';
$keep_slugs = array_values(array_unique($keep_slugs));

$terms = get_terms(array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
));

if (is_wp_error($terms)) {
    fwrite(STDERR, $terms->get_error_message() . PHP_EOL);
    exit(1);
}

$deleted = array();
$skipped = array();

foreach ($terms as $term) {
    if (in_array($term->slug, $keep_slugs, true)) {
        continue;
    }

    if ((int) $term->count > 0) {
        $skipped[] = $term->slug;
        continue;
    }

    $result = wp_delete_term((int) $term->term_id, 'product_cat');

    if (is_wp_error($result)) {
        $skipped[] = $term->slug;
        continue;
    }

    $deleted[] = $term->slug;
}

echo 'Deleted empty categories: ' . count($deleted) . PHP_EOL;

if ($deleted) {
    echo implode(', ', $deleted) . PHP_EOL;
}

if ($skipped) {
    echo 'Skipped non-empty or failed categories: ' . implode(', ', $skipped) . PHP_EOL;
}
