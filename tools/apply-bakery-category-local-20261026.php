<?php
/** Scoped backup, import and verification. Never connects to hosting. */
if ('cli' !== PHP_SAPI) { http_response_code(404); exit; }
define('VPN_BAKERY_SKIP_SYNC', true);
require dirname(__DIR__) . '/wp-load.php';
if ('local' !== wp_get_environment_type() || !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('localhost', '127.0.0.1'), true)) {
    throw new RuntimeException('Only the local WordPress database is allowed.');
}
$data = vpn_bakery_category_data();
$dir = dirname(__DIR__) . '/outputs/bakery-category-20261026';
wp_mkdir_p($dir);
$snapshot = static function () use ($data) {
    $term = get_term_by('slug', $data['slug'], 'product_cat');
    $result = array('term' => $term ?: null, 'meta' => $term ? get_term_meta($term->term_id) : array(), 'products' => array());
    foreach ($data['products'] as $slug) {
        $post = get_page_by_path($slug, OBJECT, 'product');
        if (!$post || 'publish' !== $post->post_status) { throw new RuntimeException('Missing published product: ' . $slug); }
        $image = (int) get_post_thumbnail_id($post->ID);
        if (!$image || !is_readable(get_attached_file($image))) { throw new RuntimeException('Missing image: ' . $slug); }
        $result['products'][$slug] = array('id' => $post->ID, 'title' => $post->post_title, 'status' => $post->post_status,
            'categories' => wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'slugs')),
            'image' => $image, 'content_hash' => hash('sha256', $post->post_content), 'excerpt_hash' => hash('sha256', $post->post_excerpt));
    }
    return $result;
};
$before = $snapshot();
if (!in_array('--apply', $argv, true)) {
    echo wp_json_encode(array('mode' => 'preview', 'products' => count($before['products']), 'existing_term' => $before['term'], 'rank_math' => defined('RANK_MATH_VERSION') ? RANK_MATH_VERSION : null), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL; exit;
}
$backup = $dir . '/before-' . gmdate('Ymd-His') . '.json';
if (false === file_put_contents($backup, wp_json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) { throw new RuntimeException('Cannot save scoped backup'); }
$sync = vpn_bakery_sync_category();
if (!empty($sync['error'])) { throw new RuntimeException(wp_json_encode($sync)); }
$after = $snapshot();
$errors = array();
foreach ($before['products'] as $slug => $product) {
    $next = $after['products'][$slug];
    foreach (array('id', 'title', 'status', 'image', 'content_hash', 'excerpt_hash') as $field) {
        if ($product[$field] !== $next[$field]) { $errors[] = $slug . ': changed ' . $field; }
    }
    $expected = array_values(array_unique(array_merge($product['categories'], array($data['slug']))));
    $actual = $next['categories'];
    sort($expected); sort($actual);
    if ($expected !== $actual) { $errors[] = $slug . ': unexpected category changes'; }
}
foreach (array('rank_math_title' => 'title', 'rank_math_description' => 'description', 'rank_math_focus_keyword' => 'keyword') as $key => $field) {
    if (get_term_meta($sync['term_id'], $key, true) !== $data[$field]) { $errors[] = 'SEO field mismatch: ' . $key; }
}
// Repeat explicitly to establish that a deployment rerun does not duplicate the term or product assignments.
$again = vpn_bakery_sync_category();
if (($again['term_id'] ?? 0) !== $sync['term_id'] || count($again['assigned'] ?? array()) !== count($data['products'])) { $errors[] = 'Idempotence failure'; }
$result = array('url' => vpn_bakery_category_url(), 'term_id' => $sync['term_id'], 'products' => count($after['products']),
    'term_count' => get_term($sync['term_id'], 'product_cat')->count, 'seo_title' => $data['title'], 'meta_description' => $data['description'],
    'backup' => $backup, 'product_content_unchanged' => !$errors, 'hosting_changed' => false, 'errors' => $errors);
file_put_contents($dir . '/applied.json', wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
exit($errors ? 1 : 0);
