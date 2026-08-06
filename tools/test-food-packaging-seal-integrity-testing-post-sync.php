<?php
/** Runs the food packaging seal integrity post sync and its repair test locally. */

require_once dirname(__DIR__) . '/wp-load.php';
require_once dirname(__DIR__) . '/wp-content/themes/custom-box-theme/inc/food-packaging-seal-integrity-testing-post-sync.php';

$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$admins) {
    fwrite(STDERR, 'No administrator account is available.' . PHP_EOL);
    exit(1);
}
wp_set_current_user((int) $admins[0]);

custom_box_sync_food_packaging_seal_integrity_testing_post();
$data = custom_box_food_packaging_seal_integrity_testing_post_data();
$post = custom_box_find_food_packaging_seal_integrity_testing_post($data['slug'], $data['title']);
if (!$post || !custom_box_food_packaging_seal_integrity_testing_is_complete((int) $post->ID)) {
    fwrite(STDERR, 'Normal sync failed: ' . wp_json_encode(get_option('custom_box_food_packaging_seal_integrity_testing_validation_failures')) . PHP_EOL);
    exit(1);
}

$post_id = (int) $post->ID;
update_option(CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_VERSION_OPTION, CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_SYNC_VERSION, false);
$damaged_content = preg_replace(
    '/<!-- food-packaging-seal-integrity-testing-image:slot_2 -->\s*<figure>.*?<\/figure>/is',
    '<!-- IMAGE_SLOT_2 -->',
    (string) $post->post_content,
    1
);
wp_update_post(array('ID' => $post_id, 'post_content' => $damaged_content));
delete_post_thumbnail($post_id);
wp_set_post_terms($post_id, array(), 'post_tag', false);
delete_post_meta($post_id, 'rank_math_focus_keyword');

if (custom_box_food_packaging_seal_integrity_testing_is_complete($post_id)) {
    fwrite(STDERR, 'Damage phase did not make validation fail.' . PHP_EOL);
    exit(1);
}

custom_box_sync_food_packaging_seal_integrity_testing_post();
$repaired = custom_box_find_food_packaging_seal_integrity_testing_post($data['slug'], $data['title']);
if (!$repaired || (int) $repaired->ID !== $post_id || !custom_box_food_packaging_seal_integrity_testing_is_complete($post_id)) {
    fwrite(STDERR, 'Repair failed: ' . wp_json_encode(get_option('custom_box_food_packaging_seal_integrity_testing_validation_failures')) . PHP_EOL);
    exit(1);
}

$content = (string) get_post_field('post_content', $post_id);
$attachment_ids = array();
$attachment_counts = array();
global $wpdb;
foreach (custom_box_food_packaging_seal_integrity_testing_images() as $image) {
    $attachment_id = custom_box_find_food_packaging_seal_integrity_testing_attachment($image['base']);
    $attachment_ids[] = $attachment_id;
    $candidate_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($image['base']) . '%'
    ));
    $attachment_counts[$image['base']] = count(array_filter($candidate_ids, static function ($candidate_id) use ($image) {
        $file = (string) get_post_meta((int) $candidate_id, '_wp_attached_file', true);
        return $image['base'] === pathinfo(wp_basename($file), PATHINFO_FILENAME);
    }));
}

custom_box_sync_food_packaging_seal_integrity_testing_post();

echo wp_json_encode(array(
    'post_id' => $post_id,
    'status' => get_post_status($post_id),
    'featured_image_id' => get_post_thumbnail_id($post_id),
    'figures' => preg_match_all('/<figure\b/i', $content),
    'markers' => substr_count($content, '<!-- food-packaging-seal-integrity-testing-image:'),
    'remaining_slots' => substr_count($content, 'IMAGE_SLOT_'),
    'category' => wp_get_post_terms($post_id, 'category', array('fields' => 'slugs')),
    'tags' => wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs')),
    'attachments' => $attachment_ids,
    'attachment_counts' => $attachment_counts,
    'unique_attachments' => count(array_unique($attachment_ids)),
    'stored_version' => get_option(CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_VERSION_OPTION),
    'complete' => custom_box_food_packaging_seal_integrity_testing_is_complete($post_id),
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
