<?php
/**
 * Verifies the cosmetic brand perception guide after sync.
 *
 * Usage:
 *   php tools/verify-cosmetic-brand-perception-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

$post = get_page_by_path('how-paper-packaging-affects-cosmetic-brand-perception', OBJECT, 'post');

if (!$post || 'post' !== $post->post_type) {
    fwrite(STDERR, "Cosmetic brand perception post was not found.\n");
    exit(1);
}

$content = (string) $post->post_content;
$category_names = wp_get_post_categories($post->ID, array('fields' => 'names'));
$tag_names = wp_get_post_tags($post->ID, array('fields' => 'names'));
$featured_image_id = (int) get_post_thumbnail_id($post->ID);
$inline_figures = substr_count($content, 'vpn-cosmetic-brand-perception-image:slot_');
$remaining_slots = preg_match_all('/IMAGE_SLOT_\d+/', $content);
$content_h1_count = preg_match_all('/<h1\b/i', $content);
$missing_images = (array) get_option('custom_box_cosmetic_brand_perception_missing_images', array());
$missing_slots = (array) get_option('custom_box_cosmetic_brand_perception_missing_slots', array());

echo 'Post ID: ' . (int) $post->ID . PHP_EOL;
echo 'Status: ' . $post->post_status . PHP_EOL;
echo 'Title: ' . $post->post_title . PHP_EOL;
echo 'Slug: ' . $post->post_name . PHP_EOL;
echo 'Permalink: ' . get_permalink($post->ID) . PHP_EOL;
echo 'Excerpt set: ' . ($post->post_excerpt ? 'yes' : 'no') . PHP_EOL;
echo 'Featured image ID: ' . $featured_image_id . PHP_EOL;
echo 'Inline figures: ' . $inline_figures . PHP_EOL;
echo 'Figure tags: ' . substr_count($content, '<figure') . PHP_EOL;
echo 'Remaining image slots: ' . $remaining_slots . PHP_EOL;
echo 'Content H1 count: ' . $content_h1_count . PHP_EOL;
echo 'Word count: ' . str_word_count(wp_strip_all_tags($content)) . PHP_EOL;
echo 'Categories: ' . implode(', ', $category_names) . PHP_EOL;
echo 'Tags: ' . implode(', ', $tag_names) . PHP_EOL;
echo 'Rank Math title: ' . get_post_meta($post->ID, 'rank_math_title', true) . PHP_EOL;
echo 'Rank Math description: ' . get_post_meta($post->ID, 'rank_math_description', true) . PHP_EOL;
echo 'Rank Math focus keyword: ' . get_post_meta($post->ID, 'rank_math_focus_keyword', true) . PHP_EOL;
echo 'Missing images: ' . implode(', ', $missing_images) . PHP_EOL;
echo 'Missing slots: ' . implode(', ', $missing_slots) . PHP_EOL;

$failures = array();

if ('publish' !== $post->post_status) {
    $failures[] = 'post is not published';
}

if (!$featured_image_id) {
    $failures[] = 'featured image is missing';
}

if (4 !== $inline_figures) {
    $failures[] = 'inline figure count is not 4';
}

if (0 !== $remaining_slots) {
    $failures[] = 'image slots remain in content';
}

if (0 !== $content_h1_count) {
    $failures[] = 'content contains H1 tags';
}

if (!empty($missing_images)) {
    $failures[] = 'images are missing: ' . implode(', ', $missing_images);
}

if (!empty($missing_slots)) {
    $failures[] = 'slots are missing: ' . implode(', ', $missing_slots);
}

if (!empty($failures)) {
    fwrite(STDERR, 'Verification failed: ' . implode('; ', $failures) . PHP_EOL);
    exit(1);
}

echo 'Verification passed.' . PHP_EOL;
