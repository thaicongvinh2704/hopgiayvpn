<?php
/**
 * Syncs metadata and images for the paper box durability guide.
 *
 * Usage:
 *   php tools/sync-paper-box-durability-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_upsert_paper_box_durability_post')) {
    fwrite(STDERR, "Paper box durability post sync helper is not available.\n");
    exit(1);
}

$post_id = custom_box_upsert_paper_box_durability_post();

if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}

if (!$post_id) {
    fwrite(STDERR, "The paper box durability draft was not found.\n");
    exit(1);
}

$missing = get_option('custom_box_paper_box_durability_missing_images', array());

echo 'Paper box durability draft synced: ' . get_permalink($post_id) . PHP_EOL;
echo 'Missing images: ' . (empty($missing) ? 'none' : implode(', ', $missing)) . PHP_EOL;
