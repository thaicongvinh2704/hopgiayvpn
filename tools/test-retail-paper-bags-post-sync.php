<?php
/**
 * Runs the retail paper bags post sync and its mandatory repair test locally.
 */

require_once dirname(__DIR__) . '/wp-load.php';
require_once get_template_directory() . '/inc/how-paper-bags-support-retail-packaging-post-sync.php';

$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$admins) {
    fwrite(STDERR, 'No administrator account is available for the admin_init entry-point test.' . PHP_EOL);
    exit(1);
}
wp_set_current_user((int) $admins[0]);

$post_id = custom_box_upsert_retail_paper_bags_post();
if (is_wp_error($post_id)) {
    fwrite(STDERR, $post_id->get_error_message() . PHP_EOL);
    exit(1);
}

$post_id = (int) $post_id;
if (!custom_box_retail_paper_bags_is_complete($post_id)) {
    fwrite(STDERR, 'Normal sync failed: ' . wp_json_encode(get_option('custom_box_retail_paper_bags_validation_failures')) . PHP_EOL);
    exit(1);
}

update_option(CUSTOM_BOX_RETAIL_PAPER_BAGS_VERSION_OPTION, CUSTOM_BOX_RETAIL_PAPER_BAGS_SYNC_VERSION, false);
$post = get_post($post_id);
$damaged_content = preg_replace('/<!-- retail-paper-bags-image:slot_2 -->\s*<figure>.*?<\/figure>/is', '<!-- IMAGE_SLOT_2 -->', (string) $post->post_content, 1);
wp_update_post(array('ID' => $post_id, 'post_content' => $damaged_content));
delete_post_thumbnail($post_id);
wp_set_post_terms($post_id, array(), 'post_tag', false);
delete_post_meta($post_id, 'rank_math_focus_keyword');

if (custom_box_retail_paper_bags_is_complete($post_id)) {
    fwrite(STDERR, 'Damage phase did not make validation fail.' . PHP_EOL);
    exit(1);
}

custom_box_sync_retail_paper_bags_post();
$repaired = custom_box_find_retail_paper_bags_post('how-paper-bags-support-retail-packaging', 'How Paper Bags Support Retail Paper Packaging Systems');
if (!$repaired || (int) $repaired->ID !== $post_id || !custom_box_retail_paper_bags_is_complete($post_id)) {
    fwrite(STDERR, 'Repair failed: ' . wp_json_encode(get_option('custom_box_retail_paper_bags_validation_failures')) . PHP_EOL);
    exit(1);
}

$content = (string) get_post_field('post_content', $post_id);
$attachment_ids = array();
$attachment_counts = array();
foreach (custom_box_retail_paper_bags_images() as $image) {
    $attachment_ids[] = custom_box_find_retail_paper_bags_attachment($image['base']);
    global $wpdb;
    $candidates = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($image['base']) . '%'
    ));
    $attachment_counts[$image['base']] = count(array_filter($candidates, static function ($candidate_id) use ($image) {
        $file = (string) get_post_meta((int) $candidate_id, '_wp_attached_file', true);
        return $image['base'] === pathinfo(wp_basename($file), PATHINFO_FILENAME);
    }));
}

echo wp_json_encode(array(
    'post_id' => $post_id,
    'status' => get_post_status($post_id),
    'featured_image_id' => get_post_thumbnail_id($post_id),
    'figures' => substr_count($content, '<figure>'),
    'markers' => substr_count($content, '<!-- retail-paper-bags-image:'),
    'tags' => wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs')),
    'attachments' => $attachment_ids,
    'attachment_counts' => $attachment_counts,
    'unique_attachments' => count(array_unique($attachment_ids)),
    'complete' => true,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
