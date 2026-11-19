<?php
/**
 * Plugin Name: VPN Vial and Window Bread Bag Release
 * Description: Installs the approved copy and SEO fields for two existing WooCommerce products.
 * Version: 2026.11.19.1
 */

defined('ABSPATH') || exit;

function vpn_vial_bread_20261119_fingerprint(string $html): string {
    $text = html_entity_decode(preg_replace('~<[^>]*>~', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return hash('sha256', trim(preg_replace('~[^\p{L}\p{N}]+~u', ' ', $text)));
}

function vpn_vial_bread_20261119_replace(string $content, array $edit): string {
    if (substr_count($content, $edit['new']) === 1) {
        return $content;
    }
    foreach ($edit['old'] as $previous) {
        if (substr_count($content, $previous) === 1) {
            return str_replace($previous, $edit['new'], $content);
        }
    }
    throw new RuntimeException('A reviewed paragraph has been edited since it was checked.');
}

function vpn_vial_bread_20261119_plan(WP_Post $post, array $row, string $directory): array {
    $id = $post->ID;
    if ($post->post_type !== 'product' || $post->post_status !== 'publish' || $post->post_title !== $row['post_title']) {
        throw new RuntimeException('The destination must be the reviewed published product.');
    }

    $current_excerpt = vpn_vial_bread_20261119_fingerprint($post->post_excerpt);
    if (!in_array($current_excerpt, [...$row['old_excerpt'], vpn_vial_bread_20261119_fingerprint($row['excerpt'])], true)) {
        throw new RuntimeException('The short description differs from the reviewed copy.');
    }

    $meta = ['rank_math_title' => $row['seo_title'], 'rank_math_description' => $row['description']];
    foreach (['rank_math_title' => 'old_seo_title', 'rank_math_description' => 'old_description'] as $key => $previous) {
        if (!in_array(get_post_meta($id, $key, true), [...$row[$previous], $meta[$key]], true)) {
            throw new RuntimeException('An SEO field differs from the reviewed copy: ' . $key);
        }
    }
    $content = $post->post_content;
    if (isset($row['body_file'])) {
        $template = file_get_contents($directory . '/' . $row['body_file']);
        if (!is_string($template) || $template === '') {
            throw new RuntimeException('The approved bread bag description is missing.');
        }
        $approved = strtr($template, [
            '{{UPLOADS}}' => untrailingslashit(wp_get_upload_dir()['baseurl']),
            '{{HOME}}' => untrailingslashit(home_url('/')),
        ]);
        $current_body = vpn_vial_bread_20261119_fingerprint($content);
        if (!in_array($current_body, [...$row['old_body'], vpn_vial_bread_20261119_fingerprint($approved)], true)) {
            throw new RuntimeException('The bread bag description differs from the reviewed copy.');
        }
        $content = $approved;

        $featured_id = get_post_thumbnail_id($id);
        $featured = (string) get_attached_file($featured_id);
        if (wp_basename($featured) !== $row['featured'] || !wp_get_attachment_url($featured_id)) {
            throw new RuntimeException('The featured image differs from the verified bread bag photo.');
        }
    } else {
        foreach ($row['body_edits'] as $edit) {
            $content = vpn_vial_bread_20261119_replace($content, $edit);
        }
        $key = '_custom_box_product_faq_html';
        $meta[$key] = vpn_vial_bread_20261119_replace((string) get_post_meta($id, $key, true), $row['faq_edit']);
    }

    $key = '_custom_box_product_specs';
    $specs = get_post_meta($id, $key, true);
    if (!is_array($specs) || array_diff_key($row['specs'], array_flip(array_column($specs, 'label')))) {
        throw new RuntimeException('The destination specifications differ from the reviewed product.');
    }
    $meta[$key] = array_map(static function (array $spec) use ($row): array {
        $spec['value'] = $row['specs'][$spec['label']] ?? $spec['value'];
        return $spec;
    }, $specs);

    return ['content' => $content, 'excerpt' => $row['excerpt'], 'meta' => $meta];
}

function vpn_vial_bread_20261119_apply(WP_Post $post, array $row, string $directory): void {
    $id = $post->ID;
    $plan = vpn_vial_bread_20261119_plan($post, $row, $directory);
    $fields = ['ID' => $id, 'post_content' => $plan['content'], 'post_excerpt' => $plan['excerpt']];
    $before = [
        'ID' => $id, 'post_content' => $post->post_content, 'post_excerpt' => $post->post_excerpt,
    ];
    $before_meta = [];
    foreach ($plan['meta'] as $key => $value) {
        $before_meta[$key] = get_post_meta($id, $key, true);
    }
    if ($fields === $before && $plan['meta'] === $before_meta) {
        return;
    }
    $backup_key = 'vpn_vial_bread_20261119_before_' . $id;
    if (false === get_option($backup_key, false) && !add_option($backup_key, ['fields' => $before, 'meta' => $before_meta], '', false)) {
        throw new RuntimeException('Could not save the original product fields.');
    }

    $allow_decoding = static function (array $tags, string $context): array {
        if ($context === 'post' && isset($tags['img'])) {
            $tags['img']['decoding'] = true;
        }
        return $tags;
    };
    add_filter('wp_kses_allowed_html', $allow_decoding, 10, 2);
    try {
        if ($fields !== $before) {
            $result = wp_update_post(wp_slash($fields), true);
            if (is_wp_error($result) || !$result) {
                throw new RuntimeException(is_wp_error($result) ? $result->get_error_message() : 'Could not save product copy.');
            }
            $saved = get_post($id);
            if ($saved->post_content !== $fields['post_content'] || $saved->post_excerpt !== $fields['post_excerpt']) {
                throw new RuntimeException('The stored copy differs from the approved HTML.');
            }
        }
        foreach ($plan['meta'] as $key => $value) {
            if ($before_meta[$key] !== $value) {
                update_post_meta($id, $key, wp_slash($value));
            }
            if (get_post_meta($id, $key, true) !== $value) {
                throw new RuntimeException('Could not verify ' . $key);
            }
        }
        wc_delete_product_transients($id);
    } catch (Throwable $error) {
        if ($fields !== $before) {
            wp_update_post(wp_slash($before));
        }
        foreach ($before_meta as $key => $value) {
            if (get_post_meta($id, $key, true) !== $value) {
                update_post_meta($id, $key, wp_slash($value));
            }
        }
        throw $error;
    } finally {
        remove_filter('wp_kses_allowed_html', $allow_decoding, 10);
    }
}

add_action('admin_init', static function (): void {
    $marker = 'vpn_vial_bread_20261119_done';
    $error_key = 'vpn_vial_bread_20261119_error';
    if (!current_user_can('manage_options') || !current_user_can('manage_woocommerce')
        || wp_doing_ajax() || wp_doing_cron() || !function_exists('wc_delete_product_transients')
        || !in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['hopgiayvpn.com', 'www.hopgiayvpn.com'], true)
        || (int) get_option($marker) === 1 || get_transient($error_key)) {
        return;
    }

    global $wpdb;
    $lock = 'vpn_vial_bread_' . md5(DB_NAME . $wpdb->prefix);
    if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== '1') {
        return;
    }
    try {
        wp_cache_delete($marker, 'options');
        wp_cache_delete('notoptions', 'options');
        if ((int) get_option($marker) === 1) {
            return;
        }
        $directory = __DIR__ . '/vpn-vial-bread-20261119';
        $rows = json_decode(file_get_contents($directory . '/release.json'), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($rows) || count($rows) !== 2) {
            throw new RuntimeException('The two-product release package is incomplete.');
        }
        $errors = [];
        foreach ($rows as $slug => $row) {
            try {
                $post = get_page_by_path($slug, OBJECT, 'product');
                if (!$post) {
                    throw new RuntimeException('Published product not found.');
                }
                if (get_post_meta($post->ID, '_' . $marker, true) !== '1') {
                    vpn_vial_bread_20261119_apply($post, $row, $directory);
                    if (!update_post_meta($post->ID, '_' . $marker, '1')) {
                        throw new RuntimeException('Could not record the installed product update.');
                    }
                }
            } catch (Throwable $error) {
                $errors[] = $slug . ': ' . $error->getMessage();
            }
        }
        if ($errors) {
            throw new RuntimeException(implode(' | ', $errors));
        }
        update_option($marker, 1, false);
    } catch (Throwable $error) {
        set_transient($error_key, $error->getMessage(), 5 * MINUTE_IN_SECONDS);
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}, 110);

add_action('admin_notices', static function (): void {
    $error = get_transient('vpn_vial_bread_20261119_error');
    if ($error && current_user_can('manage_options')) {
        echo '<div class="notice notice-warning"><p>' . esc_html('VPN product update: ' . $error) . '</p></div>';
    }
});
