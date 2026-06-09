<?php
/**
 * Creates the Sports Packaging Boxes product category.
 *
 * @package Custom_Box_Theme
 */

function custom_box_sync_sports_packaging_category()
{
    if (!is_admin() || !current_user_can('manage_woocommerce')) {
        return;
    }

    $sync_version = 'sports-packaging-category-20260609-v1';

    if (get_option('custom_box_sports_packaging_category_sync_version') === $sync_version) {
        return;
    }

    if (!taxonomy_exists('product_cat')) {
        return;
    }

    $parent = get_term_by('slug', 'custom-packaging-boxes', 'product_cat');

    if (!$parent || is_wp_error($parent)) {
        return;
    }

    $term = get_term_by('slug', 'sports-packaging-boxes', 'product_cat');

    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term(
            'Sports Packaging Boxes',
            'product_cat',
            array(
                'slug'        => 'sports-packaging-boxes',
                'parent'      => (int) $parent->term_id,
                'description' => 'Custom sports packaging boxes for fitness products, athletic accessories, sports equipment and branded retail packaging.',
            )
        );

        if (is_wp_error($created)) {
            return;
        }
    } elseif ((int) $term->parent !== (int) $parent->term_id) {
        $updated = wp_update_term(
            (int) $term->term_id,
            'product_cat',
            array('parent' => (int) $parent->term_id)
        );

        if (is_wp_error($updated)) {
            return;
        }
    }

    update_option('custom_box_sports_packaging_category_sync_version', $sync_version, false);
}
add_action('admin_init', 'custom_box_sync_sports_packaging_category', 10);
