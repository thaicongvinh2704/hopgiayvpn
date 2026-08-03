<?php
/** Runs the cardboard boxes made post sync and mandatory repair test locally. */

require_once dirname(__DIR__) . '/wp-load.php';
require_once dirname(__DIR__) . '/wp-content/themes/custom-box-theme/inc/how-are-cardboard-boxes-made-post-sync.php';

$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$admins) {
    fwrite(STDERR, 'No administrator account is available.' . PHP_EOL);
    exit(1);
}
wp_set_current_user((int) $admins[0]);

$data = custom_box_cardboard_boxes_made_post_data();
$post_id = custom_box_upsert_cardboard_boxes_made_post();
if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}
$post_id = (int) $post_id;
if (!custom_box_cardboard_boxes_made_is_complete($post_id)) {
    fwrite(STDERR, 'Normal sync failed: ' . wp_json_encode(get_option('custom_box_cardboard_boxes_made_validation_failures')) . PHP_EOL);
    exit(1);
}

update_option(CUSTOM_BOX_CARDBOARD_BOXES_MADE_VERSION_OPTION, CUSTOM_BOX_CARDBOARD_BOXES_MADE_SYNC_VERSION, false);
$post = get_post($post_id);
$damaged_content = preg_replace(
    '/<!-- cardboard-boxes-made-image:slot_2 -->\s*<figure>.*?<\/figure>/is',
    '<!-- IMAGE_SLOT_2 -->',
    (string) $post->post_content,
    1
);
wp_update_post(array('ID' => $post_id, 'post_content' => $damaged_content));
delete_post_thumbnail($post_id);
wp_set_post_terms($post_id, array(), 'post_tag', false);
delete_post_meta($post_id, 'rank_math_focus_keyword');

if (custom_box_cardboard_boxes_made_is_complete($post_id)) {
    fwrite(STDERR, 'Damage phase did not make validation fail.' . PHP_EOL);
    exit(1);
}

custom_box_sync_cardboard_boxes_made_post();
$repaired = custom_box_find_cardboard_boxes_made_post($data['slug'], $data['title']);
if (
    !$repaired
    || (int) $repaired->ID !== $post_id
    || !custom_box_cardboard_boxes_made_is_complete($post_id)
) {
    fwrite(STDERR, 'Repair failed: ' . wp_json_encode(get_option('custom_box_cardboard_boxes_made_validation_failures')) . PHP_EOL);
    exit(1);
}

custom_box_sync_cardboard_boxes_made_post();
if (!custom_box_cardboard_boxes_made_is_complete($post_id)) {
    fwrite(STDERR, 'Second normal run failed: ' . wp_json_encode(get_option('custom_box_cardboard_boxes_made_validation_failures')) . PHP_EOL);
    exit(1);
}

global $wpdb;
$attachment_ids = array();
$attachment_counts = array();
foreach (custom_box_cardboard_boxes_made_images() as $image) {
    $attachment_id = custom_box_find_cardboard_boxes_made_attachment($image['base']);
    $attachment_ids[] = $attachment_id;
    $candidates = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($image['base']) . '%'
    ));
    $attachment_counts[$image['base']] = count(array_filter($candidates, static function ($candidate_id) use ($image) {
        $file = (string) get_post_meta((int) $candidate_id, '_wp_attached_file', true);
        return $image['base'] === pathinfo(wp_basename($file), PATHINFO_FILENAME);
    }));
}

$post_matches = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status <> 'trash' AND (post_name = %s OR post_title = %s)",
    $data['slug'],
    $data['title']
));
$duplicate_attachment_counts = array_filter($attachment_counts, static function ($count) {
    return 1 !== $count;
});
if (
    1 !== $post_matches
    || $duplicate_attachment_counts
    || count($attachment_ids) !== count(array_unique($attachment_ids))
) {
    fwrite(
        STDERR,
        'Duplicate validation failed: posts=' . $post_matches
            . '; attachments=' . wp_json_encode($attachment_counts)
            . PHP_EOL
    );
    exit(1);
}

$content = (string) get_post_field('post_content', $post_id);
echo wp_json_encode(array(
    'post_id' => $post_id,
    'status' => get_post_status($post_id),
    'featured_image_id' => get_post_thumbnail_id($post_id),
    'figures' => substr_count($content, '<figure>'),
    'images' => substr_count($content, '<img '),
    'markers' => substr_count($content, '<!-- cardboard-boxes-made-image:'),
    'remaining_slots' => substr_count($content, 'IMAGE_SLOT_'),
    'category' => wp_get_post_terms($post_id, 'category', array('fields' => 'slugs')),
    'tags' => wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs')),
    'attachments' => $attachment_ids,
    'attachment_counts' => $attachment_counts,
    'unique_attachments' => count(array_unique($attachment_ids)),
    'version' => get_option(CUSTOM_BOX_CARDBOARD_BOXES_MADE_VERSION_OPTION),
    'complete' => true,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
