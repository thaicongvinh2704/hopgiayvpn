<?php
/** Local-only inspection and application of the approved sportswear copy. */
if ('cli' !== PHP_SAPI) { http_response_code(404); exit; }
require dirname(__DIR__) . '/wp-load.php';
if ('local' !== wp_get_environment_type() || !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('localhost', '127.0.0.1'), true)) {
    fwrite(STDERR, "Local WordPress required.\n"); exit(1);
}
add_filter('pre_wp_mail', '__return_false');
$post = get_page_by_path('custom-sportswear-packaging-box', OBJECT, 'product');
if (!$post) { fwrite(STDERR, "Product not found.\n"); exit(1); }
if (in_array('--apply', $argv, true)) {
    $admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (!$admins || !function_exists('vpn_sportswear_release_apply')) { fwrite(STDERR, "Missing admin or release module.\n"); exit(1); }
    wp_set_current_user((int) $admins[0]);
    $result = vpn_sportswear_release_apply();
    echo wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
    exit(isset($result['error']) ? 1 : 0);
}
$product = wc_get_product($post->ID);
$terms = wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'slugs'));
$relevant = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'numberposts' => 40,
    'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => 'fashion-sportswear-packaging'))));
$data = array('id' => $post->ID, 'home' => home_url(), 'theme' => get_stylesheet(), 'status' => $post->post_status,
    'title' => $post->post_title, 'slug' => $post->post_name, 'date' => $post->post_date,
    'content' => $post->post_content, 'content_sha256' => hash('sha256', $post->post_content), 'excerpt' => $post->post_excerpt,
    'meta' => get_post_meta($post->ID), 'terms' => $terms,
    'seo_plugins' => array_values(array_filter((array) get_option('active_plugins'), static fn($p) => str_contains($p, 'seo') || str_contains($p, 'rank'))),
    'related_candidates' => array_map(static fn($p) => array('id' => $p->ID, 'slug' => $p->post_name, 'title' => $p->post_title), $relevant),
    'images' => array(), 'factory_images' => array());
foreach (array_unique(array_filter(array_merge(array($product->get_image_id()), $product->get_gallery_image_ids()))) as $id) {
    $data['images'][] = array('id' => $id, 'path' => get_attached_file($id), 'url' => wp_get_attachment_url($id), 'alt' => get_post_meta($id, '_wp_attachment_image_alt', true));
}
if (function_exists('custom_box_factory_gallery_image')) {
    foreach (array('printing', 'finishing') as $key) {
        $data['factory_images'][$key] = custom_box_factory_gallery_image($key);
    }
}
$out = dirname(__DIR__) . '/outputs/sportswear-ctr-20261024';
wp_mkdir_p($out);
$snapshot = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (!file_exists($out . '/local-before.json')) { file_put_contents($out . '/local-before.json', $snapshot); }
file_put_contents($out . '/local-inspection-latest.json', $snapshot);
echo wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;
