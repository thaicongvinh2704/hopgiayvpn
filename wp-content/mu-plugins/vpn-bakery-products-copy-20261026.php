<?php
/**
 * Plugin Name: VPN Bakery Product Editorial Release 20261026
 * Description: One-time, guarded copy update for nine existing bakery bag products; no layout or image changes.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;
const VPN_BAKERY_COPY_RELEASE_VERSION = '2026-10-26.1';

function vpn_bakery_copy_data(): array {
    static $data;
    if (null === $data) {
        $path = __DIR__ . '/vpn-bakery-products-copy-20261026/release.json';
        $data = is_readable($path) ? json_decode((string) file_get_contents($path), true) : array();
    }
    return is_array($data) ? $data : array();
}

function vpn_bakery_copy_normalize(string $value): string {
    return str_replace(array(untrailingslashit(home_url('/')), 'http://localhost/hopgiayvpn', 'https://hopgiayvpn.com', 'http://hopgiayvpn.com', "\r\n"), array('{{HOME}}', '{{HOME}}', '{{HOME}}', '{{HOME}}', "\n"), $value);
}

function vpn_bakery_copy_meta(array $row): array {
    return array(
        'rank_math_title' => $row['title'],
        'rank_math_description' => $row['description'],
        'rank_math_focus_keyword' => $row['keywords'],
        '_custom_box_product_specs' => $row['specs'],
        '_custom_box_product_hero_bullets' => $row['bullets'],
    );
}

/** Pure preflight: unexpected edits are preserved for review, never overwritten. */
function vpn_bakery_copy_conflict(WP_Post $post, array $row, string $content): string {
    $hash = hash('sha256', vpn_bakery_copy_normalize($post->post_content));
    if (!in_array($hash, array($row['before_content_hash'], hash('sha256', vpn_bakery_copy_normalize($content))), true)) {
        return 'Description differs from the reviewed version.';
    }
    if (!in_array($post->post_excerpt, array($row['before_excerpt'], $row['excerpt']), true)) {
        return 'Short description differs from the reviewed version.';
    }
    foreach (vpn_bakery_copy_meta($row) as $key => $value) {
        $current = get_post_meta($post->ID, $key, true);
        if ($current !== $row['before_meta'][$key] && $current !== $value) {
            return 'Metadata differs from the reviewed version: ' . $key;
        }
    }
    return '';
}

function vpn_bakery_copy_apply_one(array $row, string $version): array {
    $post = get_page_by_path($row['slug'], OBJECT, 'product');
    if (!$post || 'publish' !== $post->post_status) { return array('error' => 'Existing published product required; no product created.'); }
    $id = (int) $post->ID;
    if ($version === get_post_meta($id, '_vpn_bakery_copy_version', true)) {
        return array('status' => 'already applied', 'id' => $id);
    }
    $path = __DIR__ . '/vpn-bakery-products-copy-20261026/' . $row['slug'] . '.html';
    if (!is_readable($path)) { return array('error' => 'Release content missing.', 'id' => $id); }
    $content = str_replace('{{HOME}}', untrailingslashit(home_url('/')), (string) file_get_contents($path));
    $conflict = vpn_bakery_copy_conflict($post, $row, $content);
    if ($conflict) { return array('error' => $conflict, 'id' => $id); }
    $meta = vpn_bakery_copy_meta($row);
    $before = array('content' => $post->post_content, 'excerpt' => $post->post_excerpt, 'meta' => array());
    foreach ($meta as $key => $value) {
        $before['meta'][$key] = array('exists' => metadata_exists('post', $id, $key), 'value' => get_post_meta($id, $key, true));
    }
    $backup_key = 'vpn_bakery_copy_20261026_before_' . $id;
    if (false === get_option($backup_key, false) && !add_option($backup_key, $before, '', false)) {
        return array('error' => 'Before-state could not be saved.', 'id' => $id);
    }
    $saved = wp_update_post(wp_slash(array('ID' => $id, 'post_content' => $content, 'post_excerpt' => $row['excerpt'],
        'post_date' => $post->post_date, 'post_date_gmt' => $post->post_date_gmt, 'edit_date' => true)), true);
    if (is_wp_error($saved)) { return array('error' => $saved->get_error_message(), 'id' => $id); }
    foreach ($meta as $key => $value) { update_post_meta($id, $key, $value); }
    clean_post_cache($id);
    wc_delete_product_transients($id);
    $after = get_post($id);
    $valid = $after->post_content === $content && $after->post_excerpt === $row['excerpt']
        && $after->post_title === $post->post_title && $after->post_name === $post->post_name
        && $after->post_status === $post->post_status && $after->post_date === $post->post_date;
    foreach ($meta as $key => $value) { $valid = $valid && get_post_meta($id, $key, true) === $value; }
    if (!$valid) {
        wp_update_post(wp_slash(array('ID' => $id, 'post_content' => $before['content'], 'post_excerpt' => $before['excerpt'],
            'post_date' => $post->post_date, 'post_date_gmt' => $post->post_date_gmt, 'edit_date' => true)));
        foreach ($before['meta'] as $key => $saved_meta) {
            if ($saved_meta['exists']) { update_post_meta($id, $key, $saved_meta['value']); }
            else { delete_post_meta($id, $key); }
        }
        wc_delete_product_transients($id);
        return array('error' => 'Readback failed; restored the pre-apply copy.', 'id' => $id);
    }
    update_post_meta($id, '_vpn_bakery_copy_version', $version);
    return array('status' => 'applied', 'id' => $id, 'url' => get_permalink($id), 'backup_option' => $backup_key);
}

function vpn_bakery_copy_apply(): array {
    if (!defined('RANK_MATH_VERSION') || !function_exists('wc_get_product')) { return array('error' => 'WooCommerce and Rank Math are required.'); }
    $data = vpn_bakery_copy_data();
    if (empty($data['version']) || count($data['products'] ?? array()) !== 9) { return array('error' => 'Nine-product release missing.'); }
    $results = array();
    foreach ($data['products'] as $row) {
        try { $results[$row['slug']] = vpn_bakery_copy_apply_one($row, $data['version']); }
        catch (Throwable $error) { $results[$row['slug']] = array('error' => $error->getMessage()); }
    }
    return $results;
}

// A single deployment pass. A conflict is reported, not retried on every visitor.
add_action('init', static function () {
    if (defined('VPN_BAKERY_COPY_SKIP_AUTO') && VPN_BAKERY_COPY_SKIP_AUTO) { return; }
    if (wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) { return; }
    if (get_option('vpn_bakery_copy_20261026_attempt') === VPN_BAKERY_COPY_RELEASE_VERSION) { return; }
    $data = vpn_bakery_copy_data();
    if (empty($data['version']) || get_option('vpn_bakery_copy_20261026_attempt') === $data['version']) { return; }
    if (!defined('RANK_MATH_VERSION') || !function_exists('wc_get_product')) { return; }
    $lock = 'vpn_bakery_copy_20261026_lock';
    $started = (int) get_option($lock, 0);
    if ($started && time() - $started > 600) { delete_option($lock); }
    if (!add_option($lock, time(), '', false)) { return; }
    try {
        update_option('vpn_bakery_copy_20261026_results', vpn_bakery_copy_apply(), false);
        update_option('vpn_bakery_copy_20261026_attempt', $data['version'], false);
    } finally { delete_option($lock); }
}, 110);

// Keep the existing Product entity, with the visible product name and category.
// Prices, reviews, certifications and extra entities are not invented.
add_filter('rank_math/json_ld', static function ($nodes) {
    if (!is_singular('product')) { return $nodes; }
    $id = get_queried_object_id();
    $slug = get_post_field('post_name', $id);
    $slugs = array_column(vpn_bakery_copy_data()['products'] ?? array(), 'slug');
    if (!in_array($slug, $slugs, true) || !get_post_meta($id, '_vpn_bakery_copy_version', true)) { return $nodes; }
    foreach ($nodes as &$node) {
        if (!is_array($node) || !in_array('Product', (array) ($node['@type'] ?? array()), true)) { continue; }
        $node['name'] = get_the_title($id);
        $node['description'] = wp_strip_all_tags(get_post_field('post_excerpt', $id));
        $category = get_term_by('slug', 'custom-bakery-bread-paper-bags', 'product_cat');
        if ($category && has_term($category->term_id, 'product_cat', $id)) { $node['category'] = html_entity_decode($category->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
    }
    unset($node);
    return $nodes;
}, 120);

add_action('admin_notices', static function () {
    if (!current_user_can('manage_options')) { return; }
    $errors = array();
    foreach ((array) get_option('vpn_bakery_copy_20261026_results', array()) as $slug => $row) {
        if (is_array($row) && isset($row['error'])) { $errors[] = $slug . ': ' . $row['error']; }
    }
    if ($errors) { echo '<div class="notice notice-warning"><p>' . esc_html('Bakery product copy needs review: ' . implode(' | ', $errors)) . '</p></div>'; }
});
