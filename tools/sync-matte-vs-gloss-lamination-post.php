<?php
/**
 * Syncs metadata and images for the matte vs gloss lamination packaging guide.
 *
 * Usage:
 *   php tools/sync-matte-vs-gloss-lamination-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_upsert_matte_vs_gloss_lamination_post')) {
    fwrite(STDERR, "Matte vs gloss lamination post sync helper is not available.\n");
    exit(1);
}

$post_id = custom_box_upsert_matte_vs_gloss_lamination_post();

if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}

if (!$post_id) {
    fwrite(STDERR, "The matte vs gloss lamination draft was not found.\n");
    exit(1);
}

$missing = get_option('custom_box_matte_vs_gloss_lamination_missing_images', array());

echo 'Matte vs gloss lamination draft synced: ' . get_permalink($post_id) . PHP_EOL;
echo 'Missing images: ' . (empty($missing) ? 'none' : implode(', ', $missing)) . PHP_EOL;
