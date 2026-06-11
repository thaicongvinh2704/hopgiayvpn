<?php
/**
 * Sync the Sports Packaging Boxes category thumbnail from the theme asset.
 *
 * Usage:
 *   php tools/sync-sports-category-thumbnail.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!taxonomy_exists('product_cat')) {
    fwrite(STDERR, "WooCommerce product_cat taxonomy is not available.\n");
    exit(1);
}

if (!function_exists('custom_box_sync_sports_packaging_category_thumbnail')) {
    fwrite(STDERR, "Sports category thumbnail sync helper is not available.\n");
    exit(1);
}

$term = get_term_by('slug', 'sports-packaging-boxes', 'product_cat');

if (!$term || is_wp_error($term)) {
    fwrite(STDERR, "Missing product category: sports-packaging-boxes\n");
    exit(1);
}

$attachment_id = custom_box_sync_sports_packaging_category_thumbnail((int) $term->term_id);

if (!$attachment_id) {
    fwrite(STDERR, "Could not sync sports category thumbnail.\n");
    exit(1);
}

echo 'Sports Packaging Boxes thumbnail attachment_id=' . $attachment_id . PHP_EOL;
echo 'thumbnail_id=' . (int) get_term_meta((int) $term->term_id, 'thumbnail_id', true) . PHP_EOL;
echo 'custom_box_category_image_id=' . (int) get_term_meta((int) $term->term_id, 'custom_box_category_image_id', true) . PHP_EOL;
