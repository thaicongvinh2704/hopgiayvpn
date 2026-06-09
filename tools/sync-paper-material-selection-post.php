<?php
/**
 * Creates or updates the paper material selection blog draft.
 *
 * Usage:
 *   php tools/sync-paper-material-selection-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_upsert_paper_material_selection_post')) {
    fwrite(STDERR, "Paper material selection post sync helper is not available.\n");
    exit(1);
}

$post_id = custom_box_upsert_paper_material_selection_post();

if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}

if (!$post_id) {
    fwrite(STDERR, "The post could not be created or updated.\n");
    exit(1);
}

echo 'Paper material selection draft ready: ' . get_permalink($post_id) . PHP_EOL;
