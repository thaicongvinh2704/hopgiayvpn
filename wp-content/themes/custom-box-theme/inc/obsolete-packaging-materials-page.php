<?php
/**
 * Removes and redirects the obsolete paper materials support page.
 *
 * @package Custom_Box_Theme
 */

function custom_box_delete_obsolete_packaging_materials_page()
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    if (get_option('custom_box_obsolete_packaging_materials_page_deleted')) {
        return;
    }

    $page = get_page_by_path('paper-materials-for-custom-paper-boxes', OBJECT, 'page');

    if ($page) {
        $deleted = wp_delete_post($page->ID, true);

        if (!$deleted) {
            return;
        }
    }

    update_option('custom_box_obsolete_packaging_materials_page_deleted', 1, false);
}
add_action('admin_init', 'custom_box_delete_obsolete_packaging_materials_page', 5);

function custom_box_redirect_obsolete_packaging_materials_page()
{
    $request_path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

    if ('paper-materials-for-custom-paper-boxes' !== $request_path) {
        return;
    }

    $replacement = get_page_by_path(
        'how-to-choose-paper-material-for-product-packaging',
        OBJECT,
        'post'
    );

    if ($replacement && 'publish' === $replacement->post_status) {
        $target_url = get_permalink($replacement);
    } else {
        $target_url = home_url('/products/');
    }

    wp_safe_redirect($target_url, 301);
    exit;
}
add_action('template_redirect', 'custom_box_redirect_obsolete_packaging_materials_page', 1);
