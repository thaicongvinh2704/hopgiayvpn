<?php
/**
 * Deploy the Custom Wine Bottle Packaging Box product gallery from Git.
 *
 * The image originals live in the theme's deploy asset bundle because the
 * normal uploads directory is intentionally ignored by Git. This sync only
 * changes the product's featured/gallery relationships and attachment data;
 * it does not change the product content, slug, SEO fields, or taxonomy.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_WINE_BOTTLE_GALLERY_SYNC_VERSION = '2026-08-20-wine-bottle-gallery-v1';
const CUSTOM_BOX_WINE_BOTTLE_GALLERY_SYNC_OPTION = 'custom_box_wine_bottle_gallery_sync_version';
const CUSTOM_BOX_WINE_BOTTLE_GALLERY_FAILURE_OPTION = 'custom_box_wine_bottle_gallery_sync_failures';

add_action('admin_init', 'custom_box_maybe_sync_wine_bottle_packaging_box_gallery');
add_action('admin_notices', 'custom_box_wine_bottle_packaging_box_gallery_admin_notice');

function custom_box_wine_bottle_packaging_box_images(): array
{
    return array(
        array(
            'base' => 'custom-wine-bottle-packaging-box-open-standing-view',
            'relative' => '2026/08/custom-wine-bottle-packaging-box-open-standing-view.webp',
            'title' => 'Custom Wine Bottle Packaging Box Open Standing View',
            'alt' => 'Custom wine bottle packaging box with book-style opening and fitted insert',
            'caption' => '',
        ),
        array(
            'base' => 'custom-wine-bottle-packaging-box-closed-with-bottle',
            'relative' => '2026/08/custom-wine-bottle-packaging-box-closed-with-bottle.webp',
            'title' => 'Custom Wine Bottle Packaging Box Closed With Bottle',
            'alt' => 'Custom wine bottle packaging box with premium printed finish',
            'caption' => '',
        ),
        array(
            'base' => 'custom-wine-bottle-packaging-box-open-insert-top-view',
            'relative' => '2026/08/custom-wine-bottle-packaging-box-open-insert-top-view.webp',
            'title' => 'Custom Wine Bottle Packaging Box Open Insert Top View',
            'alt' => 'Open custom wine bottle packaging box with protective bottle insert',
            'caption' => '',
        ),
        array(
            'base' => 'custom-wine-bottle-packaging-box-bottom-view-with-bottle',
            'relative' => '2026/08/custom-wine-bottle-packaging-box-bottom-view-with-bottle.webp',
            'title' => 'Custom Wine Bottle Packaging Box Bottom View With Bottle',
            'alt' => 'Rigid custom wine bottle packaging box with premium box construction',
            'caption' => '',
        ),
        array(
            'base' => 'custom-wine-bottle-packaging-box-surface-detail',
            'relative' => '2026/08/custom-wine-bottle-packaging-box-surface-detail.webp',
            'title' => 'Custom Wine Bottle Packaging Box Surface Detail',
            'alt' => 'Luxury wine bottle packaging box with gold foil and embossed details',
            'caption' => '',
        ),
        array(
            'base' => 'custom-wine-bottle-packaging-box-open-empty-insert',
            'relative' => '2026/08/custom-wine-bottle-packaging-box-open-empty-insert.webp',
            'title' => 'Custom Wine Bottle Packaging Box Open Empty Insert',
            'alt' => 'Custom wine bottle gift box with fitted protective insert',
            'caption' => '',
        ),
    );
}

function custom_box_find_wine_bottle_gallery_attachment(string $base): int
{
    global $wpdb;

    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
        '%' . $wpdb->esc_like($base) . '%'
    ));

    foreach ($ids as $id) {
        $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
        if ($base === pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
            return (int) $id;
        }
    }

    return 0;
}

function custom_box_ensure_wine_bottle_gallery_attachment(int $product_id, array $image): int
{
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }

    $relative = ltrim((string) $image['relative'], '/');
    $upload_path = trailingslashit($uploads['basedir']) . $relative;
    $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;

    if (!file_exists($upload_path) && file_exists($bundle_path)) {
        if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
            return 0;
        }
    }
    if (!file_exists($upload_path)) {
        return 0;
    }

    $attachment_id = custom_box_find_wine_bottle_gallery_attachment((string) $image['base']);
    if (!$attachment_id) {
        $type = wp_check_filetype(wp_basename($upload_path), null);
        $attachment_id = wp_insert_attachment(array(
            'post_mime_type' => $type['type'] ?: 'image/webp',
            'post_title' => $image['title'],
            'post_excerpt' => $image['caption'],
            'post_status' => 'inherit',
            'post_parent' => $product_id,
        ), $upload_path, $product_id, true);
        if (is_wp_error($attachment_id)) {
            return 0;
        }
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_wp_attached_file', $relative);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload_path);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);
    wp_update_post(array(
        'ID' => (int) $attachment_id,
        'post_parent' => $product_id,
        'post_title' => $image['title'],
        'post_excerpt' => $image['caption'],
    ));

    return (int) $attachment_id;
}

function custom_box_validate_wine_bottle_gallery(int $product_id, array $images, array $ids): array
{
    $failures = array();
    $product = get_post($product_id);

    if (!$product || 'product' !== $product->post_type || 'custom-wine-bottle-packaging-box' !== $product->post_name) {
        $failures[] = 'The expected product slug was not found.';
        return $failures;
    }

    if (count($ids) !== count($images) || count(array_unique($ids)) !== count($images)) {
        $failures[] = 'The product gallery does not contain six unique attachments.';
    }

    $thumbnail_id = (int) get_post_thumbnail_id($product_id);
    if (empty($ids[0]) || $thumbnail_id !== (int) $ids[0]) {
        $failures[] = 'The featured image is not the open-standing image.';
    }

    $stored_gallery = (string) get_post_meta($product_id, '_product_image_gallery', true);
    $stored_ids = array_values(array_filter(array_map('absint', explode(',', $stored_gallery))));
    $expected_gallery = array_map('absint', array_slice($ids, 1));
    if ($stored_ids !== $expected_gallery) {
        $failures[] = 'The product gallery order does not match the deploy specification.';
    }

    $uploads = wp_upload_dir();
    foreach ($images as $index => $image) {
        $attachment_id = isset($ids[$index]) ? (int) $ids[$index] : 0;
        if (!$attachment_id || 'attachment' !== get_post_type($attachment_id)) {
            $failures[] = sprintf('Attachment %d is missing.', $index + 1);
            continue;
        }

        $attached = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
        if ((string) $image['base'] !== pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
            $failures[] = sprintf('Attachment %d has the wrong filename.', $index + 1);
        }
        if ((string) $image['alt'] !== (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) {
            $failures[] = sprintf('Attachment %d has the wrong alt text.', $index + 1);
        }

        $attachment = get_post($attachment_id);
        if (!$attachment || $product_id !== (int) $attachment->post_parent) {
            $failures[] = sprintf('Attachment %d is not assigned to the product.', $index + 1);
        }
        if ($attachment && ((string) $image['title'] !== $attachment->post_title || (string) $image['caption'] !== $attachment->post_excerpt)) {
            $failures[] = sprintf('Attachment %d has the wrong title or caption.', $index + 1);
        }
        if (!empty($uploads['basedir']) && !file_exists(trailingslashit($uploads['basedir']) . ltrim((string) $image['relative'], '/'))) {
            $failures[] = sprintf('The original file for attachment %d is missing.', $index + 1);
        }
    }

    return array_values(array_unique($failures));
}

/**
 * @return int|WP_Error
 */
function custom_box_sync_wine_bottle_packaging_box_gallery()
{
    $product = get_page_by_path('custom-wine-bottle-packaging-box', OBJECT, 'product');
    if (!$product) {
        delete_option(CUSTOM_BOX_WINE_BOTTLE_GALLERY_SYNC_OPTION);
        return new WP_Error('wine_bottle_product_missing', 'The Custom Wine Bottle Packaging Box product was not found.');
    }

    $product_id = (int) $product->ID;
    $images = custom_box_wine_bottle_packaging_box_images();
    $ids = array();

    foreach ($images as $image) {
        $attachment_id = custom_box_ensure_wine_bottle_gallery_attachment($product_id, $image);
        if (!$attachment_id) {
            delete_option(CUSTOM_BOX_WINE_BOTTLE_GALLERY_SYNC_OPTION);
            return new WP_Error('wine_bottle_attachment_failed', 'One or more bundled gallery images could not be copied or registered.');
        }
        $ids[] = $attachment_id;
    }

    set_post_thumbnail($product_id, $ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($ids, 1)));
    clean_post_cache($product_id);
    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients($product_id);
    }

    $failures = custom_box_validate_wine_bottle_gallery($product_id, $images, $ids);
    if ($failures) {
        delete_option(CUSTOM_BOX_WINE_BOTTLE_GALLERY_SYNC_OPTION);
        update_option(CUSTOM_BOX_WINE_BOTTLE_GALLERY_FAILURE_OPTION, $failures, false);
        return new WP_Error('wine_bottle_gallery_validation_failed', implode(' ', $failures));
    }

    update_option(CUSTOM_BOX_WINE_BOTTLE_GALLERY_SYNC_OPTION, CUSTOM_BOX_WINE_BOTTLE_GALLERY_SYNC_VERSION, false);
    delete_option(CUSTOM_BOX_WINE_BOTTLE_GALLERY_FAILURE_OPTION);

    return $product_id;
}

function custom_box_maybe_sync_wine_bottle_packaging_box_gallery(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $result = custom_box_sync_wine_bottle_packaging_box_gallery();
    if (is_wp_error($result)) {
        update_option(CUSTOM_BOX_WINE_BOTTLE_GALLERY_FAILURE_OPTION, array($result->get_error_message()), false);
    }
}

function custom_box_wine_bottle_packaging_box_gallery_admin_notice(): void
{
    $failures = get_option(CUSTOM_BOX_WINE_BOTTLE_GALLERY_FAILURE_OPTION, array());
    if (!$failures || !current_user_can('manage_options')) {
        return;
    }

    printf(
        '<div class="notice notice-error"><p><strong>Wine bottle packaging gallery sync failed:</strong> %s</p></div>',
        esc_html(implode(' ', (array) $failures))
    );
}
