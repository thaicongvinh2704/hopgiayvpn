<?php
/**
 * Sync the "How to Print a Logo on Paper Boxes" draft metadata and images.
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_upsert_logo_printing_post')) {
    fwrite(STDERR, "Logo printing post sync function is not available.\n");
    exit(1);
}

$post_id = custom_box_upsert_logo_printing_post();

if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . "\n");
    exit(1);
}

echo 'Synced logo printing post ID: ' . (int) $post_id . "\n";
