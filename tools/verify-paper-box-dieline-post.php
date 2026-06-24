<?php
/**
 * Verifies the paper box dieline guide after sync.
 *
 * Usage:
 *   php tools/verify-paper-box-dieline-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

$post = get_page_by_path('what-is-a-paper-box-dieline', OBJECT, 'post');

if (!$post || 'post' !== $post->post_type) {
    fwrite(STDERR, "Paper box dieline post was not found.\n");
    exit(1);
}

$content = (string) $post->post_content;
$category_names = wp_get_post_categories($post->ID, array('fields' => 'names'));
$tag_names = wp_get_post_tags($post->ID, array('fields' => 'names'));

echo 'Post ID: ' . (int) $post->ID . PHP_EOL;
echo 'Status: ' . $post->post_status . PHP_EOL;
echo 'Title: ' . $post->post_title . PHP_EOL;
echo 'Permalink: ' . get_permalink($post->ID) . PHP_EOL;
echo 'Featured image ID: ' . (int) get_post_thumbnail_id($post->ID) . PHP_EOL;
echo 'Inline dieline figures: ' . substr_count($content, 'vpn-paper-box-dieline-image:slot_') . PHP_EOL;
echo 'Remaining image slots: ' . preg_match_all('/IMAGE_SLOT_\d+/', $content) . PHP_EOL;
echo 'Categories: ' . implode(', ', $category_names) . PHP_EOL;
echo 'Tags: ' . implode(', ', $tag_names) . PHP_EOL;
echo 'Rank Math title: ' . get_post_meta($post->ID, 'rank_math_title', true) . PHP_EOL;
echo 'Rank Math description: ' . get_post_meta($post->ID, 'rank_math_description', true) . PHP_EOL;
echo 'Rank Math focus keyword: ' . get_post_meta($post->ID, 'rank_math_focus_keyword', true) . PHP_EOL;
echo 'Missing images: ' . implode(', ', (array) get_option('custom_box_paper_box_dieline_missing_images', array())) . PHP_EOL;
echo 'Missing slots: ' . implode(', ', (array) get_option('custom_box_paper_box_dieline_missing_slots', array())) . PHP_EOL;
