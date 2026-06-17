<?php
/**
 * Sync curated WooCommerce products into the Cosmetic Paper Boxes category.
 *
 * Usage:
 *   php tools/sync-cosmetic-paper-box-products.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

$category_slug = 'cosmetic-paper-boxes';
$category = get_term_by('slug', $category_slug, 'product_cat');

if (!$category || is_wp_error($category)) {
    fwrite(STDERR, "Missing product category: {$category_slug}.\n");
    exit(1);
}

$include_slugs = array(
    'custom-ampoule-packaging-box',
    'custom-cosmetic-drawer-box-with-insert',
    'custom-cosmetic-packaging-box',
    'custom-cosmetic-paper-bag',
    'custom-essential-oil-packaging-box-with-insert',
    'custom-perfume-box-with-insert',
    'custom-perfume-display-box-with-sleeve',
    'custom-perfume-gift-set-box-with-insert',
    'custom-round-jar-drawer-box',
    'custom-skincare-gift-box-with-insert',
    'custom-skincare-jar-packaging-box-with-insert',
);

$exclude_slugs = array(
    'custom-cosmetic-tube-packaging-box-with-insert',
);

$added = 0;
$removed = 0;
$missing = array();

foreach ($include_slugs as $product_slug) {
    $product = get_page_by_path($product_slug, OBJECT, 'product');

    if (!$product) {
        $missing[] = $product_slug;
        continue;
    }

    $terms = wp_get_object_terms((int) $product->ID, 'product_cat', array('fields' => 'ids'));

    if (is_wp_error($terms)) {
        fwrite(STDERR, $terms->get_error_message() . "\n");
        exit(1);
    }

    if (!in_array((int) $category->term_id, array_map('intval', $terms), true)) {
        $result = wp_set_object_terms((int) $product->ID, array((int) $category->term_id), 'product_cat', true);

        if (is_wp_error($result)) {
            fwrite(STDERR, $result->get_error_message() . "\n");
            exit(1);
        }

        $added++;
    }
}

foreach ($exclude_slugs as $product_slug) {
    $product = get_page_by_path($product_slug, OBJECT, 'product');

    if (!$product) {
        continue;
    }

    $terms = wp_get_object_terms((int) $product->ID, 'product_cat', array('fields' => 'ids'));

    if (is_wp_error($terms)) {
        fwrite(STDERR, $terms->get_error_message() . "\n");
        exit(1);
    }

    $term_ids = array_map('intval', $terms);

    if (!in_array((int) $category->term_id, $term_ids, true)) {
        continue;
    }

    $terms = array_values(array_diff($term_ids, array((int) $category->term_id)));
    $result = wp_set_object_terms((int) $product->ID, $terms, 'product_cat', false);

    if (is_wp_error($result)) {
        fwrite(STDERR, $result->get_error_message() . "\n");
        exit(1);
    }

    $removed++;
}

wp_update_term_count_now(array((int) $category->term_taxonomy_id), 'product_cat');

if (function_exists('wc_delete_product_transients')) {
    wc_delete_product_transients();
}

echo 'Category synced: ' . $category->name . ' (' . $category->slug . ')' . PHP_EOL;
echo 'Products added: ' . $added . PHP_EOL;
echo 'Excluded products removed: ' . $removed . PHP_EOL;

if (!empty($missing)) {
    echo 'Missing products: ' . implode(', ', $missing) . PHP_EOL;
}
