<?php
/**
 * Syncs metadata, content, and images for the cosmetic brand perception guide.
 *
 * Usage:
 *   php tools/sync-cosmetic-brand-perception-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_upsert_cosmetic_brand_perception_post')) {
    fwrite(STDERR, "Cosmetic brand perception post sync helper is not available.\n");
    exit(1);
}

$post_id = custom_box_upsert_cosmetic_brand_perception_post(true);

if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}

if (!$post_id) {
    fwrite(STDERR, "The cosmetic brand perception draft was not found.\n");
    exit(1);
}

$missing_images = get_option('custom_box_cosmetic_brand_perception_missing_images', array());
$missing_slots = get_option('custom_box_cosmetic_brand_perception_missing_slots', array());

echo 'Cosmetic brand perception post synced: ' . get_permalink($post_id) . PHP_EOL;
echo 'Post ID: ' . (int) $post_id . PHP_EOL;
echo 'Status: ' . get_post_status($post_id) . PHP_EOL;
echo 'Featured image ID: ' . (int) get_post_thumbnail_id($post_id) . PHP_EOL;
echo 'Missing images: ' . (empty($missing_images) ? 'none' : implode(', ', $missing_images)) . PHP_EOL;
echo 'Missing slots: ' . (empty($missing_slots) ? 'none' : implode(', ', $missing_slots)) . PHP_EOL;
