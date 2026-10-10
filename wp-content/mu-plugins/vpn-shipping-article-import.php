<?php
/**
 * Plugin Name: VPN Custom-Sized Shipping Article Import
 * Description: Import and publish the approved shipping guide once after deployment.
 */
defined('ABSPATH') || exit;

function vpn_shipping_import_package(): array {
    $dir = __DIR__ . '/vpn-shipping-article';
    foreach (['manifest.json', 'article.html', '../vpn-shipping-calculator.php', '../vpn-shipping-calculator/calculator.css', '../vpn-shipping-calculator/calculator.js'] as $file) {
        if (!is_readable($dir . '/' . $file)) { throw new RuntimeException('Shipping article package is not fully deployed yet.'); }
    }
    if (!function_exists('vpn_shipping_calculator_allowed_html')) { throw new RuntimeException('Calculator plugin is not loaded yet.'); }
    $data = json_decode((string) file_get_contents($dir . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $content = str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($dir . '/article.html'));
    if (($data['slug'] ?? '') !== 'custom-sized-boxes-for-shipping' || ($data['status'] ?? '') !== 'publish'
        || count($data['images'] ?? []) !== 5 || !hash_equals($data['content_sha256'], hash('sha256', $content))) {
        throw new RuntimeException('Invalid shipping article package or checksum.');
    }
    foreach ($data['images'] as $image) {
        $path = $dir . '/images/' . $image['file'];
        $size = is_file($path) ? wp_getimagesize($path) : false;
        if (basename($image['file']) !== $image['file'] || !$size || $size['mime'] !== 'image/webp'
            || $size[0] !== 1600 || $size[1] !== 900 || filesize($path) >= 100000
            || !hash_equals($image['sha256'], hash_file('sha256', $path))) {
            throw new RuntimeException('Shipping image preflight failed.');
        }
        if (isset($image['slot']) && substr_count($content, '<!-- IMAGE_SLOT_' . $image['slot'] . ' -->') !== 1) {
            throw new RuntimeException('Missing or duplicated image slot.');
        }
    }
    return [$data, $content, $dir];
}

function vpn_shipping_import_run(): int {
    [$data, $content, $dir] = vpn_shipping_import_package();
    $matches = get_posts(['post_type' => 'post', 'post_status' => array_keys(get_post_stati()), 'name' => $data['slug'], 'numberposts' => 2]);
    if (count($matches) > 1) { throw new RuntimeException('Multiple shipping articles use this slug; review manually.'); }
    $post_id = $matches ? (int) $matches[0]->ID : 0;
    if ($post_id) {
        if (get_post_meta($post_id, '_vpn_shipping_guide_import', true) !== '20261010') {
            throw new RuntimeException('An unrelated article already uses this slug; it was preserved.');
        }
        if (get_post_meta($post_id, '_vpn_shipping_guide_complete', true)) { return $post_id; }
        if ($matches[0]->post_status !== 'draft' || trim($matches[0]->post_content) !== '') {
            throw new RuntimeException('An interrupted import has been edited; it was preserved.');
        }
    }
    $authors = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID']);
    $author_id = $authors ? (int) $authors[0] : 0;
    if (!$author_id || !user_can($author_id, 'publish_posts')) { throw new RuntimeException('No administrator author can publish.'); }
    if (!$post_id) {
        $post_id = wp_insert_post(wp_slash([
            'post_type' => 'post', 'post_status' => 'draft', 'post_title' => $data['title'],
            'post_name' => $data['slug'], 'post_excerpt' => $data['excerpt'], 'post_content' => '',
            'post_author' => $author_id, 'meta_input' => ['_vpn_shipping_guide_import' => '20261010'],
        ]), true);
        if (is_wp_error($post_id)) { throw new RuntimeException($post_id->get_error_message()); }
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $featured_id = 0;
    foreach ($data['images'] as $image) {
        $found = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1,
            'post_parent' => $post_id, 'meta_key' => '_vpn_shipping_source_sha256', 'meta_value' => $image['sha256']]);
        $id = $found ? (int) $found[0]->ID : 0;
        if (!$id) {
            $temp = wp_tempnam($image['file']);
            if (!$temp || !copy($dir . '/images/' . $image['file'], $temp)) {
                if ($temp) { wp_delete_file($temp); }
                throw new RuntimeException('Cannot stage shipping image.');
            }
            $id = media_handle_sideload(['name' => $image['file'], 'tmp_name' => $temp], $post_id, null, [
                'post_title' => $image['title'], 'post_excerpt' => $image['caption'], 'post_author' => $author_id,
                'meta_input' => ['_wp_attachment_image_alt' => $image['alt'], '_vpn_shipping_source_sha256' => $image['sha256']],
            ]);
            if (is_wp_error($id)) { wp_delete_file($temp); throw new RuntimeException($id->get_error_message()); }
        }
        $file = get_attached_file($id);
        if (!$file || !is_file($file) || !hash_equals($image['sha256'], hash_file('sha256', $file))) {
            throw new RuntimeException('Imported image is missing or changed.');
        }
        if (!empty($image['featured'])) { $featured_id = (int) $id; }
        if (isset($image['slot'])) {
            $html = wp_get_attachment_image($id, 'large', false, ['loading' => 'lazy', 'decoding' => 'async', 'alt' => $image['alt']]);
            if (!$html) { throw new RuntimeException('Cannot render an imported image.'); }
            $figure = '<figure class="wp-block-image size-large">' . $html . '<figcaption>' . esc_html($image['caption']) . '</figcaption></figure>';
            $content = str_replace('<!-- IMAGE_SLOT_' . $image['slot'] . ' -->', $figure, $content);
        }
    }
    if (strpos($content, 'IMAGE_SLOT_') !== false || !$featured_id) { throw new RuntimeException('Shipping images are incomplete.'); }
    $term = get_term_by('slug', $data['category']['slug'], 'category');
    if (!$term) {
        $created = wp_insert_term($data['category']['name'], 'category', ['slug' => $data['category']['slug']]);
        if (is_wp_error($created)) { throw new RuntimeException($created->get_error_message()); }
        $category_id = (int) $created['term_id'];
    } else { $category_id = (int) $term->term_id; }
    $assigned = wp_set_object_terms($post_id, [$category_id], 'category');
    if (is_wp_error($assigned)) { throw new RuntimeException($assigned->get_error_message()); }
    foreach ($data['meta'] as $key => $value) {
        if (!in_array($key, ['rank_math_title', 'rank_math_description', 'rank_math_focus_keyword'], true)) {
            throw new RuntimeException('Unexpected SEO metadata key.');
        }
        update_post_meta($post_id, $key, wp_slash($value));
        if (get_post_meta($post_id, $key, true) !== $value) { throw new RuntimeException('SEO metadata readback mismatch.'); }
    }
    set_post_thumbnail($post_id, $featured_id);
    if ((int) get_post_thumbnail_id($post_id) !== $featured_id) { throw new RuntimeException('Cannot assign featured image.'); }
    // Anonymous init requests have no unfiltered_html capability. Retain only the
    // inert calculator controls during this fixed import; never change visitor roles.
    add_filter('wp_kses_allowed_html', 'vpn_shipping_calculator_allowed_html', 20, 2);
    try {
        $saved = wp_update_post(wp_slash(['ID' => $post_id, 'post_content' => $content]), true);
    } finally {
        remove_filter('wp_kses_allowed_html', 'vpn_shipping_calculator_allowed_html', 20);
    }
    if (is_wp_error($saved)) { throw new RuntimeException($saved->get_error_message()); }
    $readback = get_post($post_id);
    if ($readback->post_status !== 'draft' || $readback->post_content !== $content) { throw new RuntimeException('Article readback mismatch; left unpublished.'); }
    update_post_meta($post_id, '_vpn_shipping_guide_complete', '1');
    return (int) $post_id;
}

function vpn_shipping_import_publish(int $post_id): void {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'post' || $post->post_name !== 'custom-sized-boxes-for-shipping'
        || get_post_meta($post_id, '_vpn_shipping_guide_import', true) !== '20261010'
        || get_post_meta($post_id, '_vpn_shipping_guide_complete', true) !== '1') {
        throw new RuntimeException('The shipping article is not ready to publish.');
    }
    if (!in_array($post->post_status, ['draft', 'publish'], true)) { throw new RuntimeException('Article status changed; automatic publication stopped.'); }
    if (strpos($post->post_content, 'IMAGE_SLOT_') !== false || substr_count($post->post_content, '<figure') !== 3
        || strpos($post->post_content, 'id="vpn-box-size-calculator"') === false
        || substr_count($post->post_content, '<input ') !== 10 || !get_post_thumbnail_id($post_id)) {
        throw new RuntimeException('Article images or calculator are incomplete.');
    }
    foreach (['rank_math_title', 'rank_math_description', 'rank_math_focus_keyword'] as $key) {
        if (!get_post_meta($post_id, $key, true)) { throw new RuntimeException('SEO metadata is incomplete.'); }
    }
    if ($post->post_status === 'publish') { return; }
    // Preserve the previous state if this is an existing completed local/draft import.
    add_post_meta($post_id, '_vpn_shipping_before_autopublish_20261010', wp_slash(wp_json_encode([
        'status' => $post->post_status, 'content' => $post->post_content, 'excerpt' => $post->post_excerpt,
        'title' => $post->post_title, 'date' => $post->post_date,
    ])), true);
    // wp_update_post re-filters existing content even for a status-only update.
    add_filter('wp_kses_allowed_html', 'vpn_shipping_calculator_allowed_html', 20, 2);
    try {
        $published = wp_update_post(['ID' => $post_id, 'post_status' => 'publish'], true);
    } finally {
        remove_filter('wp_kses_allowed_html', 'vpn_shipping_calculator_allowed_html', 20);
    }
    if (is_wp_error($published)) { throw new RuntimeException($published->get_error_message()); }
    $readback = get_post($post_id);
    if ($readback->post_status !== 'publish' || $readback->post_content !== $post->post_content) {
        throw new RuntimeException('Publication readback mismatch.');
    }
}

// Fixed owner-approved deployment migration. No request-selected files or content.
function vpn_shipping_import_deploy(): void {
    if (get_option('vpn_shipping_autopublish_20261010_done')) { return; }
    $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    if (!in_array($host, ['hopgiayvpn.com', 'www.hopgiayvpn.com', 'localhost', '127.0.0.1'], true)
        || (defined('WP_INSTALLING') && WP_INSTALLING)) { return; }
    if ((int) get_option('vpn_shipping_autopublish_20261010_retry', 0) > time()) { return; }
    $lock = 'vpn_shipping_import_lock';
    $started = (int) get_option($lock, 0);
    if ($started && $started < time() - 600) { delete_option($lock); }
    if (!add_option($lock, time(), '', false)) { return; }
    try {
        @set_time_limit(300);
        $post_id = vpn_shipping_import_run();
        vpn_shipping_import_publish($post_id);
        update_option('vpn_shipping_autopublish_20261010_done', $post_id, false);
        delete_option('vpn_shipping_autopublish_20261010_error');
        delete_option('vpn_shipping_autopublish_20261010_retry');
        try {
            if (function_exists('wp_cache_post_change')) { wp_cache_post_change($post_id); }
            do_action('litespeed_purge_post', $post_id);
            do_action('litespeed_purge_url', home_url('/blog/'));
        } catch (Throwable $cache_error) { error_log('Shipping article published; cache purge: ' . $cache_error->getMessage()); }
    } catch (Throwable $error) {
        update_option('vpn_shipping_autopublish_20261010_error', $error->getMessage(), false);
        update_option('vpn_shipping_autopublish_20261010_retry', time() + 300, false);
        error_log('Shipping article deployment: ' . $error->getMessage());
    } finally { delete_option($lock); }
}
add_action('init', 'vpn_shipping_import_deploy', 100);

add_action('admin_notices', static function () {
    if (!current_user_can('manage_options')) { return; }
    $error = get_option('vpn_shipping_autopublish_20261010_error');
    if ($error) { echo '<div class="notice notice-error"><p>Shipping article auto-publication: ' . esc_html($error) . ' Automatic retry in five minutes.</p></div>'; }
});
