<?php
/**
 * Keep deployable WooCommerce product categories in sync with the category manifest.
 */

defined('ABSPATH') || exit;

function custom_box_product_category_manifest_sync_path(): string {
    return __DIR__ . '/product-category-assignment-manifest.json';
}

function custom_box_product_category_manifest_sync_data(): array {
    $path = custom_box_product_category_manifest_sync_path();

    if (!is_readable($path)) {
        return array();
    }

    $manifest = json_decode((string) file_get_contents($path), true);

    return is_array($manifest) ? $manifest : array();
}

function custom_box_product_category_manifest_attachment(string $relative_asset, string $category_name): int {
    $relative_asset = ltrim(wp_normalize_path($relative_asset), '/');

    if (!$relative_asset || false !== strpos($relative_asset, '..')) {
        return 0;
    }

    $source_path = wp_normalize_path(dirname(__DIR__) . '/' . $relative_asset);

    if (!is_readable($source_path)) {
        return 0;
    }

    $source_hash = md5_file($source_path);
    $existing = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_custom_box_product_category_asset',
        'meta_value'     => $relative_asset,
    ));

    if (!empty($existing[0])) {
        $attachment_id = (int) $existing[0];
        $attached_file = get_attached_file($attachment_id);
        $stored_hash = (string) get_post_meta($attachment_id, '_custom_box_product_category_asset_hash', true);

        if (!$attached_file || !file_exists($attached_file)) {
            $uploads = wp_upload_dir();
            $stored_file = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
            $candidate = trailingslashit($uploads['path']) . wp_basename($stored_file);

            if (!empty($uploads['path']) && file_exists($candidate)) {
                update_attached_file($attachment_id, $candidate);
                $attached_file = $candidate;
            }
        }

        if (!$attached_file || !file_exists($attached_file)) {
            return 0;
        }

        if ($attached_file && $source_hash && $stored_hash !== $source_hash) {
            if (!copy($source_path, $attached_file)) {
                return 0;
            }

            require_once ABSPATH . 'wp-admin/includes/image.php';
            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata($attachment_id, $attached_file)
            );
            update_post_meta($attachment_id, '_custom_box_product_category_asset_hash', $source_hash);
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $category_name);

        return $attachment_id;
    }

    $upload_relative = '';
    if (preg_match('#(?:^|/)uploads/([0-9]{4}/[0-9]{2}/[^/]+)$#', $relative_asset, $matches)) {
        $upload_relative = (string) $matches[1];
    }

    if ($upload_relative) {
        global $wpdb;
        $attachment_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT p.ID
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
             WHERE p.post_type = 'attachment'
               AND p.post_status = 'inherit'
               AND pm.meta_key = '_wp_attached_file'
               AND pm.meta_value = %s
             ORDER BY p.ID DESC
             LIMIT 1",
            $upload_relative
        ));
        $attached_file = $attachment_id ? get_attached_file($attachment_id) : '';
        $attached_hash = $attached_file && file_exists($attached_file)
            ? md5_file($attached_file)
            : false;

        if ($attachment_id && $source_hash && $attached_hash === $source_hash) {
            update_post_meta($attachment_id, '_custom_box_product_category_asset', $relative_asset);
            update_post_meta($attachment_id, '_custom_box_product_category_asset_hash', $source_hash);

            return $attachment_id;
        }
    }

    $uploads = wp_upload_dir();

    if (!empty($uploads['error']) || empty($uploads['path'])) {
        return 0;
    }

    if (!wp_mkdir_p($uploads['path'])) {
        return 0;
    }

    $filename = wp_unique_filename($uploads['path'], sanitize_file_name(wp_basename($source_path)));
    $destination = trailingslashit($uploads['path']) . $filename;

    if (!copy($source_path, $destination)) {
        return 0;
    }

    $filetype = wp_check_filetype($filename);
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => trailingslashit($uploads['url']) . $filename,
            'post_mime_type' => $filetype['type'] ?: 'image/webp',
            'post_title'     => $category_name,
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $destination
    );

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return 0;
    }

    $attachment_id = (int) $attachment_id;
    update_attached_file($attachment_id, $destination);
    update_post_meta($attachment_id, '_custom_box_product_category_asset', $relative_asset);
    update_post_meta($attachment_id, '_custom_box_product_category_asset_hash', $source_hash);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $category_name);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata(
        $attachment_id,
        wp_generate_attachment_metadata($attachment_id, $destination)
    );

    return $attachment_id;
}

function custom_box_product_category_manifest_sync(): void {
    if (!taxonomy_exists('product_cat')) {
        return;
    }

    $manifest = custom_box_product_category_manifest_sync_data();

    if (empty($manifest['categories']) || !is_array($manifest['categories'])) {
        return;
    }

    $version = isset($manifest['version']) ? (string) $manifest['version'] : '0';
    $sync_key = 'manifest-v' . $version . '-' . md5((string) wp_json_encode($manifest['categories']));

    if (get_option('custom_box_product_category_manifest_sync') === $sync_key) {
        return;
    }

    $parent = get_term_by('slug', 'custom-packaging-boxes', 'product_cat');

    if (!$parent || is_wp_error($parent)) {
        $created_parent = wp_insert_term(
            'Custom Packaging Boxes',
            'product_cat',
            array('slug' => 'custom-packaging-boxes')
        );

        if (is_wp_error($created_parent) || empty($created_parent['term_id'])) {
            return;
        }

        $parent = get_term((int) $created_parent['term_id'], 'product_cat');
    }

    if (!$parent || is_wp_error($parent)) {
        return;
    }

    $parent_id = (int) $parent->term_id;

    foreach ($manifest['categories'] as $key => $category) {
        if (!is_array($category)) {
            return;
        }

        $slug = sanitize_title(!empty($category['slug']) ? $category['slug'] : $key);
        $name = !empty($category['name']) ? sanitize_text_field($category['name']) : '';

        if (!$slug || !$name || 'custom-packaging-boxes' === $slug) {
            return;
        }

        $term = get_term_by('slug', $slug, 'product_cat');

        $term_id = 0;

        if ($term && !is_wp_error($term)) {
            $updated = wp_update_term(
                (int) $term->term_id,
                'product_cat',
                array(
                    'name'   => $name,
                    'parent' => $parent_id,
                )
            );

            if (is_wp_error($updated)) {
                return;
            }

            $term_id = (int) $term->term_id;
        } else {
            $created = wp_insert_term(
                $name,
                'product_cat',
                array(
                    'slug'   => $slug,
                    'parent' => $parent_id,
                )
            );

            if (is_wp_error($created) || empty($created['term_id'])) {
                return;
            }

            $term_id = (int) $created['term_id'];
        }

        if (!empty($category['thumbnail_asset'])) {
            $attachment_id = custom_box_product_category_manifest_attachment(
                (string) $category['thumbnail_asset'],
                $name
            );

            if (!$attachment_id) {
                return;
            }

            update_term_meta($term_id, 'thumbnail_id', $attachment_id);
            update_term_meta($term_id, 'custom_box_category_image_id', $attachment_id);
        }
    }

    update_option('custom_box_product_category_manifest_sync', $sync_key, false);
    clean_term_cache(array($parent_id), 'product_cat');
}
function custom_box_maybe_sync_product_category_manifest(): void {
    if (
        !is_admin()
        || !current_user_can('manage_options')
        || (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)
    ) {
        return;
    }

    custom_box_product_category_manifest_sync();
}
add_action('admin_init', 'custom_box_maybe_sync_product_category_manifest', 30);
