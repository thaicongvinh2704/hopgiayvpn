<?php
/**
 * Plugin Name: VPN Collapsible Article Import
 * Description: One-click draft import of the supplied Collapsible Rigid Boxes article.
 */
if (!defined('ABSPATH')) {
    exit;
}

function vpn_cbx_import_package(): array {
    $dir = __DIR__ . '/vpn-collapsible-article';
    $data = json_decode((string) file_get_contents($dir . '/manifest.json'), true);
    if (!is_array($data) || ($data['slug'] ?? '') !== 'collapsible-rigid-boxes' || count($data['images'] ?? []) !== 5) {
        throw new RuntimeException('Invalid article manifest.');
    }
    // Git checkouts may use CRLF on Windows and LF on the hosting server.
    $content = str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($dir . '/article.html'));
    if (!hash_equals($data['content_sha256'], hash('sha256', $content))) {
        throw new RuntimeException('Article checksum mismatch.');
    }
    foreach ($data['images'] as $image) {
        $path = $dir . '/images/' . $image['file'];
        if (basename($image['file']) !== $image['file'] || !is_file($path)
            || !hash_equals($image['sha256'], hash_file('sha256', $path))) {
            throw new RuntimeException('Image missing or checksum mismatch.');
        }
        $size = wp_getimagesize($path);
        if (!$size || $size[0] !== 1600 || $size[1] !== 900 || $size['mime'] !== 'image/webp' || filesize($path) >= 100000) {
            throw new RuntimeException('Unexpected image format, dimensions or size.');
        }
        if (isset($image['slot']) && substr_count($content, '<!-- IMAGE_SLOT_' . $image['slot'] . ' -->') !== 1) {
            throw new RuntimeException('Missing or duplicated article image slot.');
        }
    }
    return [$data, $content, $dir];
}

function vpn_cbx_import_run(): int {
    if (!current_user_can('manage_options') || !current_user_can('edit_posts') || !current_user_can('upload_files')) {
        throw new RuntimeException('Administrator and upload permissions required.');
    }
    [$data, $content, $dir] = vpn_cbx_import_package();
    $release = 'collapsible-rigid-boxes-20261010';
    $matches = get_posts([
        'post_type' => 'post', 'post_status' => array_keys(get_post_stati()),
        'name' => $data['slug'], 'numberposts' => 2,
    ]);
    if (count($matches) > 1) {
        throw new RuntimeException('Multiple existing posts have this slug; review them manually.');
    }
    $post_id = $matches ? (int) $matches[0]->ID : 0;
    if ($post_id) {
        if (get_post_meta($post_id, '_vpn_article_import', true) !== $release) {
            throw new RuntimeException('A post already uses this slug. It was not overwritten.');
        }
        if (get_post_meta($post_id, '_vpn_article_import_complete', true)) {
            return $post_id;
        }
        if ($matches[0]->post_status !== 'draft' || trim($matches[0]->post_content) !== '') {
            throw new RuntimeException('An unfinished import has been edited. Review it before retrying.');
        }
    }
    if (!$post_id) {
        $created = wp_insert_post(wp_slash([
            'post_type' => 'post', 'post_status' => 'draft', 'post_title' => $data['title'],
            'post_name' => $data['slug'], 'post_excerpt' => $data['excerpt'],
            'post_content' => '', 'post_author' => get_current_user_id(),
            'meta_input' => ['_vpn_article_import' => $release],
        ]), true);
        if (is_wp_error($created)) {
            throw new RuntimeException($created->get_error_message());
        }
        $post_id = (int) $created;
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $featured_id = 0;
    foreach ($data['images'] as $image) {
        $existing = get_posts([
            'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1,
            'post_parent' => $post_id, 'meta_key' => '_vpn_cbx_source_sha256', 'meta_value' => $image['sha256'],
        ]);
        $media_id = $existing ? (int) $existing[0]->ID : 0;
        if (!$media_id) {
            $tmp = wp_tempnam($image['file']);
            if (!$tmp || !copy($dir . '/images/' . $image['file'], $tmp)) {
                if ($tmp) { wp_delete_file($tmp); }
                throw new RuntimeException('Could not prepare image upload.');
            }
            $uploaded = media_handle_sideload(['name' => $image['file'], 'tmp_name' => $tmp], $post_id, null, [
                'post_title' => $image['title'], 'post_excerpt' => $image['caption'],
                'meta_input' => ['_vpn_cbx_source_sha256' => $image['sha256'], '_wp_attachment_image_alt' => $image['alt']],
            ]);
            if (is_wp_error($uploaded)) {
                wp_delete_file($tmp);
                throw new RuntimeException($uploaded->get_error_message());
            }
            $media_id = (int) $uploaded;
        }
        if (!get_attached_file($media_id) || !is_file(get_attached_file($media_id))) {
            throw new RuntimeException('Imported attachment file is missing.');
        }
        if (!empty($image['featured'])) {
            $featured_id = $media_id;
        } else {
            $markup = wp_get_attachment_image($media_id, 'large', false, [
                'alt' => $image['alt'], 'loading' => 'lazy', 'decoding' => 'async',
                'class' => 'wp-image-' . $media_id,
            ]);
            if (!$markup) { throw new RuntimeException('Could not render an imported image.'); }
            $figure = '<figure class="wp-block-image size-large">' . $markup
                . '<figcaption>' . esc_html($image['caption']) . '</figcaption></figure>';
            $content = str_replace('<!-- IMAGE_SLOT_' . $image['slot'] . ' -->', $figure, $content);
        }
    }
    if (!$featured_id || strpos($content, 'IMAGE_SLOT_') !== false) {
        throw new RuntimeException('The article is missing required images.');
    }
    $taxonomies = ['category' => [$data['category']], 'post_tag' => $data['tags']];
    foreach ($taxonomies as $taxonomy => $terms) {
        $ids = [];
        foreach ($terms as $term) {
            $existing = get_term_by('slug', $term['slug'], $taxonomy);
            if ($existing) {
                $ids[] = (int) $existing->term_id;
            } else {
                $created = wp_insert_term($term['name'], $taxonomy, ['slug' => $term['slug']]);
                if (is_wp_error($created)) { throw new RuntimeException($created->get_error_message()); }
                $ids[] = (int) $created['term_id'];
            }
        }
        $assigned = wp_set_object_terms($post_id, $ids, $taxonomy, false);
        if (is_wp_error($assigned)) { throw new RuntimeException($assigned->get_error_message()); }
    }
    foreach ($data['meta'] as $key => $value) {
        if (!in_array($key, ['rank_math_title', 'rank_math_description', 'rank_math_focus_keyword'], true)) {
            throw new RuntimeException('Unexpected metadata key.');
        }
        update_post_meta($post_id, $key, wp_slash($value));
    }
    set_post_thumbnail($post_id, $featured_id);
    if ((int) get_post_thumbnail_id($post_id) !== $featured_id) {
        throw new RuntimeException('Featured image could not be assigned.');
    }
    $saved = wp_update_post(wp_slash(['ID' => $post_id, 'post_content' => $content]), true);
    if (is_wp_error($saved)) { throw new RuntimeException($saved->get_error_message()); }
    update_post_meta($post_id, '_vpn_article_import_complete', '1');
    return $post_id;
}

add_action('admin_menu', static function () {
    add_management_page('Article Import', 'Article Import', 'manage_options', 'vpn-article-import', static function () {
        echo '<div class="wrap"><h1>Article Import</h1><h2>Collapsible Rigid Boxes</h2>';
        echo '<p>Import one draft article with 5 images, category, tags and Rank Math metadata. Existing unrelated posts will not be overwritten. Re-running a completed import returns the same post.</p>';
        $notice = get_transient('vpn_cbx_import_notice_' . get_current_user_id());
        if (is_array($notice)) {
            delete_transient('vpn_cbx_import_notice_' . get_current_user_id());
            echo '<div class="notice ' . (!empty($notice['id']) ? 'notice-success' : 'notice-error') . '"><p>' . esc_html($notice['message']);
            if (!empty($notice['id'])) {
                echo ' <a href="' . esc_url(get_edit_post_link($notice['id'])) . '">Edit draft</a> | <a href="' . esc_url(get_preview_post_link($notice['id'])) . '">Preview</a>';
            }
            echo '</p></div>';
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('vpn_cbx_import');
        echo '<input type="hidden" name="action" value="vpn_cbx_import">';
        submit_button('Import Collapsible Rigid Boxes draft');
        echo '</form></div>';
    });
});

add_action('admin_post_vpn_cbx_import', static function () {
    if (!current_user_can('manage_options')) { wp_die('Administrator permission required.', '', ['response' => 403]); }
    check_admin_referer('vpn_cbx_import');
    $lock = 'vpn_cbx_import_lock';
    // Release a lock abandoned by a terminated import after ten minutes.
    $started = (int) get_option($lock, 0);
    if ($started && $started < time() - 600) { delete_option($lock); }
    $locked = add_option($lock, time(), '', false);
    try {
        if (!$locked) { throw new RuntimeException('An import is already running. Try again after it finishes.'); }
        @set_time_limit(300);
        $id = vpn_cbx_import_run();
        $notice = ['id' => $id, 'message' => 'Draft ready. Post ID: ' . $id . '. Review it before publishing.'];
    } catch (Throwable $error) {
        $notice = ['message' => $error->getMessage() . ' No existing unrelated article was overwritten.'];
    } finally {
        if ($locked) { delete_option($lock); }
    }
    set_transient('vpn_cbx_import_notice_' . get_current_user_id(), $notice, 600);
    wp_safe_redirect(admin_url('tools.php?page=vpn-article-import'));
    exit;
});
