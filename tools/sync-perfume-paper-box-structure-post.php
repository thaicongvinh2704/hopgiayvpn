<?php
/**
 * Syncs the perfume paper box structure guide draft and images.
 *
 * Usage:
 *   php tools/sync-perfume-paper-box-structure-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_upsert_perfume_paper_box_structure_post')) {
    fwrite(STDERR, "Perfume paper box structure post sync helper is not available.\n");
    exit(1);
}

$post_id = custom_box_upsert_perfume_paper_box_structure_post();

if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}

if (!$post_id) {
    fwrite(STDERR, "The perfume paper box structure draft was not found.\n");
    exit(1);
}

$missing_images = (array) get_option('custom_box_perfume_paper_box_structure_missing_images', array());
$missing_slots = (array) get_option('custom_box_perfume_paper_box_structure_missing_slots', array());

echo 'Perfume paper box structure draft synced: ' . get_permalink($post_id) . PHP_EOL;
echo 'Missing images: ' . (empty($missing_images) ? 'none' : implode(', ', $missing_images)) . PHP_EOL;
echo 'Missing slots: ' . (empty($missing_slots) ? 'none' : implode(', ', $missing_slots)) . PHP_EOL;
