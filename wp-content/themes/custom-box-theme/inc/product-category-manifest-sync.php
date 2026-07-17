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

            continue;
        }

        $created = wp_insert_term(
            $name,
            'product_cat',
            array(
                'slug'   => $slug,
                'parent' => $parent_id,
            )
        );

        if (is_wp_error($created)) {
            return;
        }
    }

    update_option('custom_box_product_category_manifest_sync', $sync_key, false);
    clean_term_cache(array($parent_id), 'product_cat');
}
add_action('init', 'custom_box_product_category_manifest_sync', 30);
