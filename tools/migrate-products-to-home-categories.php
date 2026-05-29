<?php
/**
 * Move WooCommerce products into the 20 homepage categories.
 *
 * Usage:
 *   php tools/migrate-products-to-home-categories.php --dry-run
 *   php tools/migrate-products-to-home-categories.php --apply
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/wp-load.php';

$helper = get_template_directory() . '/inc/product-category-migration.php';
if (file_exists($helper) && !function_exists('custom_box_category_migration_products')) {
    require_once $helper;
}

$apply = in_array('--apply', $argv, true);
$dry_run = in_array('--dry-run', $argv, true) || !$apply;

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

if (
    !function_exists('custom_box_category_migration_targets') ||
    !function_exists('custom_box_category_migration_get_or_create_term') ||
    !function_exists('custom_box_category_migration_target_for_product') ||
    !function_exists('custom_box_category_migration_products')
) {
    fwrite(STDERR, "Migration helper functions are not loaded.\n");
    exit(1);
}

$targets = custom_box_category_migration_targets();
$target_ids = array();

foreach ($targets as $slug => $name) {
    $target_ids[$slug] = custom_box_category_migration_get_or_create_term($slug, $name, $apply);
}

$products = custom_box_category_migration_products();
$updated = 0;
$missing_targets = array();

printf("%s mode\n", $dry_run ? 'Dry-run' : 'Apply');
printf("Products found: %d\n\n", count($products));

foreach ($products as $product_id) {
    $target_slug = custom_box_category_migration_target_for_product($product_id);
    $target_name = $targets[$target_slug] ?? $target_slug;

    $current_terms = get_the_terms($product_id, 'product_cat');
    $current = array();

    if (!empty($current_terms) && !is_wp_error($current_terms)) {
        foreach ($current_terms as $term) {
            $current[] = $term->slug;
        }
    }

    printf(
        "#%d %s\n  current: %s\n  target:  %s\n",
        $product_id,
        get_the_title($product_id),
        $current ? implode(', ', $current) : 'none',
        $target_slug
    );

    if (empty($target_ids[$target_slug])) {
        $missing_targets[$target_slug] = $target_name;
        echo "  skipped: target category is missing\n\n";
        continue;
    }

    if ($apply) {
        wp_set_object_terms($product_id, array((int) $target_ids[$target_slug]), 'product_cat', false);
        echo "  updated\n\n";
    } else {
        echo "  preview only\n\n";
    }

    $updated++;
}

if ($apply) {
    flush_rewrite_rules(false);
}

printf("Products %s: %d\n", $apply ? 'updated' : 'previewed', $updated);

if ($missing_targets) {
    echo "Missing target categories:\n";
    foreach ($missing_targets as $slug => $name) {
        printf("- %s (%s)\n", $name, $slug);
    }
}
