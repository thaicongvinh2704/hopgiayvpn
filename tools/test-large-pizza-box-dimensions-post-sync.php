<?php
/** Runs the large pizza box dimensions sync and its mandatory local repair test. */

require_once dirname(__DIR__) . '/wp-load.php';
require_once dirname(__DIR__) . '/wp-content/themes/custom-box-theme/inc/large-pizza-box-dimensions-post-sync.php';

$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$admins) {
    fwrite(STDERR, 'No administrator account is available.' . PHP_EOL);
    exit(1);
}
wp_set_current_user((int) $admins[0]);

delete_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_VERSION_OPTION);
custom_box_sync_large_pizza_box_dimensions_post();
$data = custom_box_large_pizza_box_dimensions_post_data();
$post = custom_box_find_large_pizza_box_dimensions_post($data['slug'], $data['title']);
if (!$post || !custom_box_large_pizza_box_dimensions_is_complete((int) $post->ID)) {
    fwrite(STDERR, 'Normal sync failed: ' . wp_json_encode(get_option('custom_box_large_pizza_box_dimensions_validation_failures')) . PHP_EOL);
    exit(1);
}

$post_id = (int) $post->ID;
$before_attachment_ids = array();
$attachment_counts = array();
global $wpdb;
foreach (custom_box_large_pizza_box_dimensions_images() as $image) {
    $before_attachment_ids[$image['base']] = custom_box_find_large_pizza_box_dimensions_attachment($image['base']);
    $candidate_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($image['base']) . '%'
    ));
    $attachment_counts[$image['base']] = count(array_filter($candidate_ids, static function ($candidate_id) use ($image) {
        $file = (string) get_post_meta((int) $candidate_id, '_wp_attached_file', true);
        return $image['base'] === pathinfo(wp_basename($file), PATHINFO_FILENAME);
    }));
}

update_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_VERSION_OPTION, CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_SYNC_VERSION, false);
$damaged_content = preg_replace(
    '/<!-- large-pizza-box-dimensions-image:slot_2 -->\s*<figure>.*?<\/figure>/is',
    '<!-- IMAGE_SLOT_2 -->',
    (string) get_post_field('post_content', $post_id),
    1
);
wp_update_post(array('ID' => $post_id, 'post_content' => $damaged_content));
delete_post_thumbnail($post_id);
wp_set_post_terms($post_id, array(), 'post_tag', false);
delete_post_meta($post_id, 'rank_math_focus_keyword');

if (custom_box_large_pizza_box_dimensions_is_complete($post_id)) {
    fwrite(STDERR, 'Damage phase did not make validation fail.' . PHP_EOL);
    exit(1);
}

custom_box_sync_large_pizza_box_dimensions_post();
$repaired = custom_box_find_large_pizza_box_dimensions_post($data['slug'], $data['title']);
if (!$repaired || (int) $repaired->ID !== $post_id || !custom_box_large_pizza_box_dimensions_is_complete($post_id)) {
    fwrite(STDERR, 'Repair failed: ' . wp_json_encode(get_option('custom_box_large_pizza_box_dimensions_validation_failures')) . PHP_EOL);
    exit(1);
}

$content = (string) get_post_field('post_content', $post_id);
$after_attachment_ids = array();
foreach (custom_box_large_pizza_box_dimensions_images() as $image) {
    $after_attachment_ids[$image['base']] = custom_box_find_large_pizza_box_dimensions_attachment($image['base']);
}

if ($before_attachment_ids !== $after_attachment_ids || count(array_unique($after_attachment_ids)) !== 5) {
    fwrite(STDERR, 'Attachment IDs changed or are not unique.' . PHP_EOL);
    exit(1);
}

custom_box_sync_large_pizza_box_dimensions_post();

echo wp_json_encode(array(
    'post_id' => $post_id,
    'status' => get_post_status($post_id),
    'featured_image_id' => get_post_thumbnail_id($post_id),
    'figures' => substr_count($content, '<figure>'),
    'images' => substr_count($content, '<img '),
    'markers' => substr_count($content, '<!-- large-pizza-box-dimensions-image:'),
    'remaining_slots' => substr_count($content, 'IMAGE_SLOT_'),
    'category' => wp_get_post_terms($post_id, 'category', array('fields' => 'slugs')),
    'tags' => wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs')),
    'attachments' => $after_attachment_ids,
    'attachment_counts' => $attachment_counts,
    'stored_version' => get_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_VERSION_OPTION),
    'complete' => custom_box_large_pizza_box_dimensions_is_complete($post_id),
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
