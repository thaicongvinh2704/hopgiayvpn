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

    $sync_version = 'sports-packaging-category-20260612-v2';

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

        $term = get_term((int) $created['term_id'], 'product_cat');
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

    if ($term && !is_wp_error($term)) {
        custom_box_sync_sports_packaging_category_thumbnail((int) $term->term_id);
    }

    update_option('custom_box_sports_packaging_category_sync_version', $sync_version, false);
}
add_action('admin_init', 'custom_box_sync_sports_packaging_category', 10);

function custom_box_sync_sports_packaging_category_thumbnail($term_id)
{
    $attachment_id = custom_box_get_sports_packaging_thumbnail_attachment_id();

    if (!$attachment_id) {
        return 0;
    }

    update_term_meta($term_id, 'thumbnail_id', $attachment_id);
    update_term_meta($term_id, 'custom_box_category_image_id', $attachment_id);

    return $attachment_id;
}

function custom_box_get_sports_packaging_thumbnail_attachment_id()
{
    $filename = 'sport-packaging-box-thumbnail.webp';
    $source_path = get_template_directory() . '/assets/images/' . $filename;

    if (!file_exists($source_path)) {
        return 0;
    }

    $existing = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_attached_file',
        'meta_value'     => $filename,
        'meta_compare'   => 'LIKE',
    ));

    if (!empty($existing[0])) {
        return (int) $existing[0];
    }

    $upload_dir = wp_upload_dir();

    if (!empty($upload_dir['error'])) {
        return 0;
    }

    $target_dir = trailingslashit($upload_dir['path']);

    if (!wp_mkdir_p($target_dir)) {
        return 0;
    }

    $target_filename = wp_unique_filename($target_dir, $filename);
    $target_path = $target_dir . $target_filename;

    if (!copy($source_path, $target_path)) {
        return 0;
    }

    $filetype = wp_check_filetype($target_filename, null);
    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => $filetype['type'] ?: 'image/webp',
            'post_title'     => 'Sports Packaging Boxes Thumbnail',
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $target_path
    );

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return 0;
    }

    $attached_file = ltrim(trailingslashit($upload_dir['subdir']) . $target_filename, '/');
    update_post_meta($attachment_id, '_wp_attached_file', $attached_file);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', 'Sports packaging boxes thumbnail');

    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $target_path));

    return (int) $attachment_id;
}
