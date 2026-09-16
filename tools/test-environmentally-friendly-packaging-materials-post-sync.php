<?php
/** Runs the environmentally friendly packaging materials sync and mandatory repair test locally. */

require_once dirname(__DIR__) . '/wp-load.php';
require_once dirname(__DIR__) . '/wp-content/themes/custom-box-theme/inc/environmentally-friendly-packaging-materials-post-sync.php';

$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$admins) {
    fwrite(STDERR, 'No administrator account is available.' . PHP_EOL);
    exit(1);
}
wp_set_current_user((int) $admins[0]);

$post_id = custom_box_upsert_env_packaging_materials_post();
if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}
$post_id = (int) $post_id;
if (!custom_box_env_packaging_materials_is_complete($post_id)) {
    fwrite(STDERR, 'Normal sync failed: ' . wp_json_encode(get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VALIDATION_OPTION)) . PHP_EOL);
    exit(1);
}

update_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VERSION_OPTION, CUSTOM_BOX_ENV_PACKAGING_MATERIALS_SYNC_VERSION, false);
$post = get_post($post_id);
$damaged_content = preg_replace(
    '/<!-- environmentally-friendly-packaging-materials-image:slot_2 -->\s*<figure>.*?<\/figure>/is',
    '<!-- IMAGE_SLOT_2 -->',
    (string) $post->post_content,
    1
);
wp_update_post(array('ID' => $post_id, 'post_content' => $damaged_content));
delete_post_thumbnail($post_id);
wp_set_post_terms($post_id, array(), 'post_tag', false);
delete_post_meta($post_id, 'rank_math_focus_keyword');

if (custom_box_env_packaging_materials_is_complete($post_id)) {
    fwrite(STDERR, 'Damage phase did not make validation fail.' . PHP_EOL);
    exit(1);
}

custom_box_sync_env_packaging_materials_post();
$repaired = custom_box_find_env_packaging_materials_post(
    'environmentally-friendly-packaging-materials',
    'Environmentally Friendly Packaging Materials: Performance, Cost and End-of-Life Compared'
);
if (!$repaired || (int) $repaired->ID !== $post_id || !custom_box_env_packaging_materials_is_complete($post_id)) {
    fwrite(STDERR, 'Repair failed: ' . wp_json_encode(get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VALIDATION_OPTION)) . PHP_EOL);
    exit(1);
}

$content = (string) get_post_field('post_content', $post_id);
$attachment_ids = array();
$attachment_counts = array();
$image_sizes = array();
foreach (custom_box_env_packaging_materials_images() as $image) {
    $attachment_id = custom_box_find_env_packaging_materials_attachment($image['base']);
    $attachment_ids[] = $attachment_id;
    global $wpdb;
    $candidates = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($image['base']) . '%'
    ));
    $attachment_counts[$image['base']] = count(array_filter($candidates, static function ($candidate_id) use ($image) {
        $file = (string) get_post_meta((int) $candidate_id, '_wp_attached_file', true);
        return $image['base'] === pathinfo(wp_basename($file), PATHINFO_FILENAME);
    }));
    $relative = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
    $path = trailingslashit(wp_get_upload_dir()['basedir']) . $relative;
    $image_sizes[$image['base']] = file_exists($path) ? filesize($path) : 0;
}

echo wp_json_encode(array(
    'post_id' => $post_id,
    'status' => get_post_status($post_id),
    'featured_image_id' => get_post_thumbnail_id($post_id),
    'figures' => substr_count($content, '<figure>'),
    'markers' => substr_count($content, '<!-- environmentally-friendly-packaging-materials-image:'),
    'remaining_slots' => substr_count($content, 'IMAGE_SLOT_'),
    'categories' => wp_get_post_terms($post_id, 'category', array('fields' => 'slugs')),
    'tags' => wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs')),
    'attachments' => $attachment_ids,
    'attachment_counts' => $attachment_counts,
    'image_sizes' => $image_sizes,
    'unique_attachments' => count(array_unique($attachment_ids)),
    'complete' => true,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
