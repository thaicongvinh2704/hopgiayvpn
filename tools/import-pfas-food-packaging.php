<?php
/**
 * Local CLI runner for the PFAS food-packaging post sync.
 *
 * Usage: php tools/import-pfas-food-packaging.php [import|audit|repair-test]
 */

if ('cli' !== PHP_SAPI) {
    exit("CLI only.\n");
}

require_once dirname(__DIR__) . '/wp-load.php';

$admins = get_users(array(
    'role' => 'administrator',
    'number' => 1,
    'fields' => 'ID',
));
if (!$admins) {
    fwrite(STDERR, "No administrator account is available for the sync.\n");
    exit(1);
}
wp_set_current_user((int) $admins[0]);

$sync_file = get_template_directory() . '/inc/pfas-food-packaging-ban-us-rules-post-sync.php';
if (!function_exists('custom_box_sync_pfas_food_packaging_post')) {
    require_once $sync_file;
}

function pfas_import_report(): array
{
    $data = custom_box_pfas_food_packaging_post_data();
    $post = custom_box_find_pfas_food_packaging_post($data['slug'], $data['title']);
    if (!$post) {
        return array('complete' => false, 'error' => 'Post not found.');
    }

    $attachment_ids = array();
    $duplicate_counts = array();
    global $wpdb;
    foreach (custom_box_pfas_food_packaging_images() as $key => $image) {
        $attachment_ids[$key] = custom_box_find_pfas_food_packaging_attachment($image['base']);
        $matches = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
            '%' . $wpdb->esc_like($image['base']) . '%'
        ));
        $exact = array_filter($matches, static function ($id) use ($image) {
            $file = (string) get_post_meta((int) $id, '_wp_attached_file', true);
            return $image['base'] === pathinfo(wp_basename($file), PATHINFO_FILENAME);
        });
        $duplicate_counts[$key] = count($exact);
    }

    $content = (string) $post->post_content;
    $categories = wp_get_post_terms($post->ID, 'category', array('fields' => 'slugs'));
    $tags = wp_get_post_terms($post->ID, 'post_tag', array('fields' => 'slugs'));

    return array(
        'complete' => custom_box_pfas_food_packaging_is_complete((int) $post->ID),
        'post_id' => (int) $post->ID,
        'status' => $post->post_status,
        'slug' => $post->post_name,
        'featured_id' => (int) get_post_thumbnail_id((int) $post->ID),
        'inline_markers' => substr_count($content, '<!-- pfas-food-packaging-ban-us-rules-image:slot_'),
        'figures' => preg_match_all('/<figure\b/i', $content),
        'remaining_slots' => preg_match_all('/IMAGE_SLOT_[0-9]+/', $content),
        'categories' => is_wp_error($categories) ? array() : $categories,
        'tags' => is_wp_error($tags) ? array() : $tags,
        'rank_math' => array(
            'title' => (string) get_post_meta($post->ID, 'rank_math_title', true),
            'description' => (string) get_post_meta($post->ID, 'rank_math_description', true),
            'focus_keyword' => (string) get_post_meta($post->ID, 'rank_math_focus_keyword', true),
        ),
        'attachment_ids' => $attachment_ids,
        'attachment_exact_counts' => $duplicate_counts,
        'validation_failures' => (array) get_option('custom_box_pfas_food_packaging_validation_failures', array()),
        'sync_version' => (string) get_option(CUSTOM_BOX_PFAS_FOOD_PACKAGING_VERSION_OPTION, ''),
    );
}

$mode = $argv[1] ?? 'import';
if (!in_array($mode, array('import', 'audit', 'repair-test'), true)) {
    fwrite(STDERR, "Unknown mode: {$mode}\n");
    exit(1);
}

if ('import' === $mode) {
    custom_box_sync_pfas_food_packaging_post();
    $report = pfas_import_report();
} elseif ('audit' === $mode) {
    $report = pfas_import_report();
} else {
    custom_box_sync_pfas_food_packaging_post();
    $before = pfas_import_report();
    if (empty($before['complete'])) {
        fwrite(STDERR, "Cannot run repair test before a complete import.\n");
        exit(1);
    }

    $post_id = (int) $before['post_id'];
    $post = get_post($post_id);
    $damaged = preg_replace(
        '/<!-- pfas-food-packaging-ban-us-rules-image:slot_2 -->\s*<figure\b.*?<\/figure>/is',
        '',
        (string) $post->post_content,
        1
    );
    wp_update_post(array('ID' => $post_id, 'post_content' => $damaged));
    delete_post_thumbnail($post_id);
    wp_set_post_terms($post_id, array(), 'post_tag', false);
    delete_post_meta($post_id, 'rank_math_focus_keyword');

    $damaged_report = pfas_import_report();
    custom_box_sync_pfas_food_packaging_post();
    $after = pfas_import_report();
    $same_attachments = $before['attachment_ids'] === $after['attachment_ids'];
    $no_duplicates = !in_array(false, array_map(static fn($count) => 1 === $count, $after['attachment_exact_counts']), true);
    $report = array(
        'complete_before_damage' => $before['complete'],
        'complete_after_damage' => $damaged_report['complete'],
        'complete_after_repair' => $after['complete'],
        'same_post_id' => $post_id === (int) $after['post_id'],
        'same_attachment_ids' => $same_attachments,
        'no_duplicate_attachments' => $no_duplicates,
        'after' => $after,
    );
}

echo wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
exit(empty($report['complete']) && 'repair-test' !== $mode ? 1 : 0);
