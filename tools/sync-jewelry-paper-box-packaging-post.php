<?php
/**
 * Syncs the jewelry paper box packaging guide draft and images.
 *
 * Usage:
 *   php tools/sync-jewelry-paper-box-packaging-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_upsert_jewelry_paper_box_packaging_post')) {
    fwrite(STDERR, "Jewelry paper box packaging post sync helper is not available.\n");
    exit(1);
}

$post_id = custom_box_upsert_jewelry_paper_box_packaging_post();

if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}

if (!$post_id) {
    fwrite(STDERR, "The jewelry paper box packaging draft was not found.\n");
    exit(1);
}

echo custom_box_jewelry_paper_box_packaging_sync_report((int) $post_id);
