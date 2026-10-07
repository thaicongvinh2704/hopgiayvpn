<?php
define('WP_ADMIN', true);
ob_start();
require dirname(__DIR__) . '/live-chat/runtime/wp/wp-load.php';
if (DB_NAME !== 'vpn_chat_test' || DB_HOST !== '127.0.0.1:3311' || wp_get_environment_type() !== 'local') {
    exit(1);
}
require_once get_template_directory() . '/inc/custom-shipping-boxes-with-company-logo-post-sync.php';
wp_set_current_user(get_user_by('login', 'chat-test-manager')->ID);
$data = custom_box_custom_shipping_boxes_with_company_logo_post_data();
$id = custom_box_upsert_custom_shipping_boxes_with_company_logo_post();
if (is_wp_error($id) || !$id || !custom_box_custom_shipping_boxes_with_company_logo_is_complete((int) $id) || 'draft' !== get_post_status($id)) {
    throw new RuntimeException('Article sync did not produce a complete draft.');
}
$post = get_post($id);
$images = custom_box_custom_shipping_boxes_with_company_logo_images();
$report = array(
    'post_id' => (int) $id,
    'status' => $post->post_status,
    'slug' => $post->post_name,
    'content_bytes' => strlen((string) $post->post_content),
    'featured_image' => (int) get_post_thumbnail_id($id),
    'inline_figures' => substr_count((string) $post->post_content, '<figure>'),
    'images' => count($images),
    'category' => $data['category']['slug'],
    'tags' => array_values($data['tags']),
    'rank_math_title' => get_post_meta($id, 'rank_math_title', true),
    'mode' => 'isolated WordPress database only; article remains draft; no production writes',
);
file_put_contents(dirname(__DIR__, 2) . '/artifacts/admin-performance/article-import.json', wp_json_encode($report, JSON_PRETTY_PRINT));
wp_delete_post($id, true);
ob_end_clean();
echo wp_json_encode($report, JSON_PRETTY_PRINT);
