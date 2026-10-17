<?php
/**
 * Plugin Name: VPN SBS Paperboard Article Import
 * Description: Installs the approved SBS paperboard guide and its five images once after deployment.
 */
defined('ABSPATH') || exit;

function vpn_sbs_paperboard_package(): array {
    $dir = __DIR__ . '/vpn-sbs-paperboard-article';
    foreach (['manifest.json', 'article.html'] as $file) {
        if (!is_readable($dir . '/' . $file)) {
            throw new RuntimeException('SBS paperboard package is not fully deployed.');
        }
    }
    $data = json_decode((string) file_get_contents($dir . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $content = str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($dir . '/article.html'));
    if (($data['slug'] ?? '') !== 'sbs-paperboard' || ($data['status'] ?? '') !== 'publish'
        || count($data['images'] ?? []) !== 5 || count($data['tags'] ?? []) !== 6
        || !hash_equals((string) ($data['content_sha256'] ?? ''), hash('sha256', $content))) {
        throw new RuntimeException('Invalid SBS paperboard article package or checksum.');
    }
    $slots = [];
    foreach ($data['images'] as $image) {
        if (basename((string) ($image['file'] ?? '')) !== $image['file']) {
            throw new RuntimeException('Invalid SBS image filename.');
        }
        $path = $dir . '/images/' . $image['file'];
        $size = is_file($path) ? wp_getimagesize($path) : false;
        if (!$size || $size['mime'] !== 'image/webp' || $size[0] !== 1600 || $size[1] !== 900
            || filesize($path) >= 100000 || !hash_equals((string) $image['sha256'], hash_file('sha256', $path))) {
            throw new RuntimeException('SBS image preflight failed: ' . $image['file']);
        }
        if (!empty($image['featured']) && isset($image['slot'])) {
            throw new RuntimeException('An SBS image cannot be both featured and inline.');
        }
        if (isset($image['slot'])) {
            $slot = (int) $image['slot'];
            $token = '<!-- IMAGE_SLOT_' . $slot . ' -->';
            if ($slot < 1 || $slot > 4 || substr_count($content, $token) !== 1 || isset($slots[$slot])) {
                throw new RuntimeException('SBS article has a missing or duplicated image slot.');
            }
            $slots[$slot] = true;
        }
    }
    if (count($slots) !== 4 || !isset($slots[1], $slots[2], $slots[3], $slots[4])) {
        throw new RuntimeException('SBS article requires four inline images.');
    }
    return [$data, $content, $dir];
}

function vpn_sbs_paperboard_term_id(string $name, string $slug, string $taxonomy): int {
    $term = get_term_by('slug', $slug, $taxonomy);
    if (!$term) {
        $created = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
        if (is_wp_error($created)) {
            throw new RuntimeException($created->get_error_message());
        }
        return (int) $created['term_id'];
    }
    return (int) $term->term_id;
}

function vpn_sbs_paperboard_import_run(bool $publish = true): int {
    $current_user_id = get_current_user_id();
    try {
        return vpn_sbs_paperboard_import_run_as_author($publish);
    } finally {
        wp_set_current_user($current_user_id);
    }
}

function vpn_sbs_paperboard_import_run_as_author(bool $publish): int {
    [$data, $content, $dir] = vpn_sbs_paperboard_package();
    $matches = get_posts([
        'post_type' => 'post',
        'post_status' => array_keys(get_post_stati()),
        'name' => $data['slug'],
        'numberposts' => 2,
    ]);
    if (count($matches) > 1) {
        throw new RuntimeException('Multiple posts use the SBS paperboard slug; review manually.');
    }
    $post_id = $matches ? (int) $matches[0]->ID : 0;
    if ($post_id && get_post_meta($post_id, '_vpn_sbs_paperboard_import', true) !== '20261017') {
        throw new RuntimeException('An unrelated post already uses /sbs-paperboard/; it was preserved.');
    }

    if ($post_id && get_post_meta($post_id, '_vpn_sbs_paperboard_complete', true) === '1') {
        $post = get_post($post_id);
        $expected = (string) get_post_meta($post_id, '_vpn_sbs_paperboard_final_sha256', true);
        if (!$post || !hash_equals($expected, hash('sha256', (string) $post->post_content))) {
            throw new RuntimeException('The completed SBS article has changed since import; it was preserved.');
        }
        if ($publish && $post->post_status === 'draft') {
            $saved = wp_update_post(['ID' => $post_id, 'post_status' => 'publish'], true);
            if (is_wp_error($saved) || get_post_status($post_id) !== 'publish') {
                throw new RuntimeException(is_wp_error($saved) ? $saved->get_error_message() : 'SBS article publication readback failed.');
            }
        } elseif (!in_array($post->post_status, ['draft', 'publish'], true)) {
            throw new RuntimeException('SBS article status changed; automatic publication stopped.');
        }
        return $post_id;
    }
    if ($post_id && get_post_status($post_id) !== 'draft') {
        throw new RuntimeException('An interrupted SBS import is no longer a draft; it was preserved.');
    }

    $authors = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID']);
    $author_id = $authors ? (int) $authors[0] : 0;
    if (!$author_id || !user_can($author_id, 'publish_posts')) {
        throw new RuntimeException('No administrator author can publish the SBS article.');
    }
    if (!$post_id) {
        $post_id = wp_insert_post(wp_slash([
            'post_type' => 'post',
            'post_status' => 'draft',
            'post_title' => $data['title'],
            'post_name' => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => '',
            'post_author' => $author_id,
            'meta_input' => [
                '_vpn_sbs_paperboard_import' => '20261017',
                '_vpn_sbs_paperboard_source_sha256' => $data['content_sha256'],
            ],
        ]), true);
        if (is_wp_error($post_id)) {
            throw new RuntimeException($post_id->get_error_message());
        }
    } elseif (get_post_meta($post_id, '_vpn_sbs_paperboard_source_sha256', true) !== $data['content_sha256']) {
        throw new RuntimeException('Interrupted SBS import source does not match; it was preserved.');
    }

    wp_set_current_user($author_id);
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $featured_id = 0;
    foreach ($data['images'] as $image) {
        $found = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'numberposts' => 1,
            'post_parent' => $post_id,
            'meta_key' => '_vpn_sbs_paperboard_source_sha256',
            'meta_value' => $image['sha256'],
        ]);
        $id = $found ? (int) $found[0]->ID : 0;
        if (!$id) {
            $temp = wp_tempnam($image['file']);
            if (!$temp || !copy($dir . '/images/' . $image['file'], $temp)) {
                if ($temp) { wp_delete_file($temp); }
                throw new RuntimeException('Cannot stage SBS image: ' . $image['file']);
            }
            $id = media_handle_sideload(['name' => $image['file'], 'tmp_name' => $temp], $post_id, null, [
                'post_title' => $image['title'],
                'post_excerpt' => $image['caption'],
                'post_author' => $author_id,
                'meta_input' => [
                    '_wp_attachment_image_alt' => $image['alt'],
                    '_vpn_sbs_paperboard_source_sha256' => $image['sha256'],
                ],
            ]);
            if (is_wp_error($id)) {
                wp_delete_file($temp);
                throw new RuntimeException($id->get_error_message());
            }
        }
        $file = get_attached_file($id);
        if (!$file || !is_file($file) || !hash_equals($image['sha256'], hash_file('sha256', $file))) {
            throw new RuntimeException('Imported SBS image is missing or changed: ' . $image['file']);
        }
        update_post_meta($id, '_wp_attachment_image_alt', wp_slash($image['alt']));
        if (!empty($image['featured'])) {
            $featured_id = (int) $id;
        }
        if (isset($image['slot'])) {
            $html = wp_get_attachment_image($id, 'large', false, [
                'loading' => 'lazy',
                'decoding' => 'async',
                'alt' => $image['alt'],
            ]);
            if (!$html) {
                throw new RuntimeException('Cannot render an imported SBS image.');
            }
            $figure = '<figure class="wp-block-image size-large">' . $html
                . '<figcaption>' . esc_html($image['caption']) . '</figcaption></figure>';
            $content = str_replace('<!-- IMAGE_SLOT_' . (int) $image['slot'] . ' -->', $figure, $content);
        }
    }
    if (!$featured_id || strpos($content, 'IMAGE_SLOT_') !== false) {
        throw new RuntimeException('SBS article images are incomplete.');
    }

    $category_id = vpn_sbs_paperboard_term_id($data['category']['name'], $data['category']['slug'], 'category');
    $assigned = wp_set_object_terms($post_id, [$category_id], 'category');
    if (is_wp_error($assigned)) {
        throw new RuntimeException($assigned->get_error_message());
    }
    $tag_ids = [];
    foreach ($data['tags'] as $tag_name) {
        $tag_ids[] = vpn_sbs_paperboard_term_id($tag_name, sanitize_title($tag_name), 'post_tag');
    }
    $assigned = wp_set_object_terms($post_id, $tag_ids, 'post_tag');
    if (is_wp_error($assigned)) {
        throw new RuntimeException($assigned->get_error_message());
    }
    foreach ($data['meta'] as $key => $value) {
        if (!in_array($key, ['rank_math_title', 'rank_math_description', 'rank_math_focus_keyword'], true)) {
            throw new RuntimeException('Unexpected SBS SEO metadata key.');
        }
        update_post_meta($post_id, $key, wp_slash($value));
        if (get_post_meta($post_id, $key, true) !== $value) {
            throw new RuntimeException('SBS SEO metadata readback failed: ' . $key);
        }
    }
    set_post_thumbnail($post_id, $featured_id);
    if ((int) get_post_thumbnail_id($post_id) !== $featured_id) {
        throw new RuntimeException('Cannot assign the SBS featured image.');
    }

    $existing = get_post($post_id);
    if ($existing && $existing->post_content !== '' && $existing->post_content !== $content) {
        throw new RuntimeException('Interrupted SBS draft content differs from the package; it was preserved.');
    }
    $saved = wp_update_post(wp_slash([
        'ID' => $post_id,
        'post_title' => $data['title'],
        'post_excerpt' => $data['excerpt'],
        'post_content' => $content,
        'post_author' => $author_id,
    ]), true);
    if (is_wp_error($saved)) {
        throw new RuntimeException($saved->get_error_message());
    }
    $readback = get_post($post_id);
    if (!$readback || strpos($readback->post_content, 'IMAGE_SLOT_') !== false
        || substr_count($readback->post_content, '<figure') !== 4 || $readback->post_title !== $data['title']) {
        throw new RuntimeException('SBS article content readback failed; it remains unpublished.');
    }
    update_post_meta($post_id, '_vpn_sbs_paperboard_final_sha256', hash('sha256', (string) $readback->post_content));
    update_post_meta($post_id, '_vpn_sbs_paperboard_complete', '1');

    if ($publish) {
        $published = wp_update_post(['ID' => $post_id, 'post_status' => 'publish'], true);
        if (is_wp_error($published) || get_post_status($post_id) !== 'publish') {
            throw new RuntimeException(is_wp_error($published) ? $published->get_error_message() : 'SBS article publication readback failed.');
        }
    }
    return (int) $post_id;
}

function vpn_sbs_paperboard_deploy_once(): void {
    if (get_option('vpn_sbs_paperboard_20261017_done')) { return; }
    if (defined('WP_INSTALLING') && WP_INSTALLING) { return; }
    $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    if (!in_array($host, ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true)) { return; }
    if ((int) get_option('vpn_sbs_paperboard_20261017_retry', 0) > time()) { return; }
    $lock = 'vpn_sbs_paperboard_20261017_lock';
    $started = (int) get_option($lock, 0);
    if ($started && $started < time() - 600) { delete_option($lock); }
    if (!add_option($lock, time(), '', false)) { return; }
    try {
        @set_time_limit(300);
        $post_id = vpn_sbs_paperboard_import_run(true);
        update_option('vpn_sbs_paperboard_20261017_done', $post_id, false);
        delete_option('vpn_sbs_paperboard_20261017_error');
        delete_option('vpn_sbs_paperboard_20261017_retry');
        try {
            if (function_exists('wp_cache_post_change')) { wp_cache_post_change($post_id); }
            do_action('litespeed_purge_post', $post_id);
            do_action('litespeed_purge_url', home_url('/blog/'));
        } catch (Throwable $cache_error) {
            error_log('SBS paperboard article published; cache purge: ' . $cache_error->getMessage());
        }
    } catch (Throwable $error) {
        update_option('vpn_sbs_paperboard_20261017_error', $error->getMessage(), false);
        update_option('vpn_sbs_paperboard_20261017_retry', time() + 300, false);
        error_log('SBS paperboard article deployment: ' . $error->getMessage());
    } finally {
        delete_option($lock);
    }
}
add_action('init', 'vpn_sbs_paperboard_deploy_once', 100);

add_action('admin_notices', static function (): void {
    if (!current_user_can('manage_options')) { return; }
    $error = get_option('vpn_sbs_paperboard_20261017_error');
    if ($error) {
        echo '<div class="notice notice-error"><p>SBS paperboard article deployment: ' . esc_html($error) . ' Automatic retry in five minutes.</p></div>';
    }
});
