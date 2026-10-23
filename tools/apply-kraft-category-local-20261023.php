<?php
/** Back up the scoped category state, then create the local collection. */
if ('cli' !== PHP_SAPI) { http_response_code(404); exit; }
define('VPN_KRAFT_SKIP_SYNC', true);
require dirname(__DIR__) . '/wp-load.php';
if ('local' !== wp_get_environment_type() || !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('localhost', '127.0.0.1'), true)) {
    fwrite(STDERR, "Only the local WordPress database is allowed.\n"); exit(1);
}
$data = vpn_kraft_category_data();
$dir = dirname(__DIR__) . '/outputs/kraft-category-20261023';
wp_mkdir_p($dir);
$term = get_term_by('slug', $data['slug'], 'product_cat');
$before = array('term' => $term ?: null, 'meta' => $term ? get_term_meta($term->term_id) : array(), 'products' => array());
foreach ($data['products'] as $item) {
    $post = get_page_by_path($item['slug'], OBJECT, 'product');
    if (!$post || 'publish' !== $post->post_status || !get_post_thumbnail_id($post->ID)) {
        fwrite(STDERR, 'Missing published product/image: ' . $item['slug'] . "\n"); exit(1);
    }
    $before['products'][$item['slug']] = array('id' => $post->ID, 'title' => $post->post_title,
        'status' => $post->post_status, 'categories' => wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'slugs')),
        'thumbnail' => get_post_thumbnail_id($post->ID), 'content_hash' => md5($post->post_content), 'excerpt_hash' => md5($post->post_excerpt));
}
if (!in_array('--apply', $argv, true)) {
    echo wp_json_encode(array('mode' => 'preview', 'products' => count($before['products']), 'existing_category' => $term ? $term->term_id : null)), PHP_EOL; exit;
}
$backup = $dir . '/before-' . gmdate('Ymd-His') . '.json';
if (false === file_put_contents($backup, wp_json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
    throw new RuntimeException('Cannot save before-state');
}
$result = vpn_kraft_sync_category();
file_put_contents($dir . '/applied.json', wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
if (!empty($result['error']) || !empty($result['missing'])) {
    fwrite(STDERR, wp_json_encode($result) . PHP_EOL); exit(1);
}
echo wp_json_encode(array('result' => $result, 'backup' => $backup, 'url' => vpn_kraft_category_url(), 'hosting_changed' => false), JSON_UNESCAPED_SLASHES), PHP_EOL;
