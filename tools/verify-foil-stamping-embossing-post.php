<?php
/**
 * Verifies the foil stamping and embossing paper boxes draft import.
 *
 * Usage:
 *   php tools/verify-foil-stamping-embossing-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

$post_id = 1161;
$post = get_post($post_id);

if (!$post) {
    fwrite(STDERR, "Post {$post_id} not found.\n");
    exit(1);
}

$categories = wp_get_post_terms($post_id, 'category', array('fields' => 'names'));
$tags = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'names'));
$content = (string) $post->post_content;

echo 'Post ID: ' . (int) $post_id . PHP_EOL;
echo 'Title: ' . $post->post_title . PHP_EOL;
echo 'Slug: ' . $post->post_name . PHP_EOL;
echo 'Status: ' . $post->post_status . PHP_EOL;
echo 'Excerpt set: ' . ($post->post_excerpt ? 'yes' : 'no') . PHP_EOL;
echo 'Featured image ID: ' . (int) get_post_thumbnail_id($post_id) . PHP_EOL;
echo 'Figure count: ' . substr_count($content, '<figure') . PHP_EOL;
echo 'Remaining image slots: ' . preg_match_all('/IMAGE_SLOT_\d+/', $content) . PHP_EOL;
echo 'Rank Math title: ' . get_post_meta($post_id, 'rank_math_title', true) . PHP_EOL;
echo 'Rank Math description set: ' . (get_post_meta($post_id, 'rank_math_description', true) ? 'yes' : 'no') . PHP_EOL;
echo 'Rank Math focus keyword: ' . get_post_meta($post_id, 'rank_math_focus_keyword', true) . PHP_EOL;
echo 'Categories: ' . implode(', ', $categories) . PHP_EOL;
echo 'Tags: ' . implode(', ', $tags) . PHP_EOL;
