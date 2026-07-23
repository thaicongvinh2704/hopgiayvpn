<?php
/**
 * Deploys the standard shipping box dimensions guide draft and images.
 */

const CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_SYNC_VERSION = '2026-07-23-v1';
const CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_VERSION_OPTION = 'custom_box_shipping_box_dimensions_sync_version';
const CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_NOTICE_OPTION = 'custom_box_shipping_box_dimensions_sync_notice';

add_action('admin_init', 'custom_box_sync_shipping_box_dimensions_post');
add_action('admin_notices', 'custom_box_shipping_box_dimensions_admin_notice');

function custom_box_sync_shipping_box_dimensions_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_shipping_box_dimensions_post_data();
    $post = custom_box_find_shipping_box_dimensions_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_SYNC_VERSION === get_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_VERSION_OPTION)
        && $post
        && custom_box_shipping_box_dimensions_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_shipping_box_dimensions_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_VERSION_OPTION);
        update_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_shipping_box_dimensions_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_VERSION_OPTION, CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Shipping box dimensions draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Guides, 5 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_VERSION_OPTION);
    delete_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_NOTICE_OPTION);
    update_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Shipping box dimensions sync is incomplete. Missing images: '
            . implode(', ', (array) get_option('custom_box_shipping_box_dimensions_missing_images', array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option('custom_box_shipping_box_dimensions_missing_slots', array()),
                (array) get_option('custom_box_shipping_box_dimensions_validation_failures', array())
            )),
    ), false);
}

function custom_box_shipping_box_dimensions_post_data(): array
{
    return array(
        'title' => 'Standard Box Dimensions for Shipping: Size Chart and Selection Guide',
        'slug' => 'standard-box-dimensions-for-shipping',
        'excerpt' => 'Compare common shipping box dimensions, calculate the space required by the packed product, understand dimensional weight and decide when a stock or custom corrugated box makes sense.',
        'category' => array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
        'tags' => array(
            'Shipping Boxes' => 'shipping-boxes',
            'Box Dimensions' => 'box-dimensions',
            'Corrugated Boxes' => 'corrugated-boxes',
            'Ecommerce Packaging' => 'ecommerce-packaging',
            'Dimensional Weight' => 'dimensional-weight',
        ),
        'seo_title' => 'Standard Box Dimensions for Shipping: Size Chart',
        'seo_description' => 'Compare standard box dimensions for shipping, calculate DIM weight and choose stock or custom cartons without wasting space or protection.',
        'focus_keyword' => 'standard box dimensions for shipping',
    );
}

function custom_box_shipping_box_dimensions_images(): array
{
    return array(
        'featured' => array(
            'base' => 'standard-box-dimensions-for-shipping-guide',
            'alt' => 'Standard box dimensions for shipping shown with corrugated cartons',
            'title' => 'Standard Box Dimensions for Shipping',
            'caption' => 'Common shipping box dimensions are starting points, not universal standards.',
        ),
        'slot_1' => array(
            'base' => 'common-standard-shipping-box-sizes',
            'alt' => 'Common small medium and large corrugated shipping box sizes',
            'title' => 'Common Shipping Box Sizes',
            'caption' => 'Common stock sizes cover different product volumes and packing needs.',
        ),
        'slot_2' => array(
            'base' => 'inside-vs-outside-box-dimensions',
            'alt' => 'Inside and outside dimensions measured on a corrugated shipping box',
            'title' => 'Inside vs Outside Box Dimensions',
            'caption' => 'Inside dimensions control product fit; outside dimensions affect carrier calculations.',
        ),
        'slot_3' => array(
            'base' => 'shipping-box-dimensional-weight-comparison',
            'alt' => 'Dimensional weight comparison between right-sized and oversized shipping boxes',
            'title' => 'Shipping Box DIM Weight Comparison',
            'caption' => 'Oversized packaging can increase billable weight even when product weight stays unchanged.',
        ),
        'slot_4' => array(
            'base' => 'custom-shipping-box-size-specification',
            'alt' => 'Shipping box sample reviewed with product dimensions and packing insert',
            'title' => 'Shipping Box Size Specification',
            'caption' => 'A useful box specification combines product fit, protection and final outside dimensions.',
        ),
    );
}

function custom_box_find_shipping_box_dimensions_post(string $slug, string $title): ?WP_Post
{
    $post = get_page_by_path($slug, OBJECT, 'post');
    if ($post && 'trash' !== $post->post_status) {
        return $post;
    }

    global $wpdb;
    $post_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status <> 'trash' AND post_title = %s ORDER BY ID DESC LIMIT 1",
        $title
    ));

    return $post_id ? get_post($post_id) : null;
}

function custom_box_upsert_shipping_box_dimensions_post()
{
    $data = custom_box_shipping_box_dimensions_post_data();
    $post = custom_box_find_shipping_box_dimensions_post($data['slug'], $data['title']);
    $payload = array(
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_type' => 'post',
        'post_excerpt' => $data['excerpt'],
    );

    if ($post) {
        $payload['ID'] = (int) $post->ID;
        $payload['post_status'] = in_array($post->post_status, array('publish', 'private'), true) ? $post->post_status : 'draft';
        $existing = (string) $post->post_content;
        if (
            !in_array($post->post_status, array('publish', 'private'), true)
            || '' === trim($existing)
            || false !== strpos($existing, 'IMAGE_SLOT_')
        ) {
            $payload['post_content'] = custom_box_shipping_box_dimensions_content();
        }
        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = custom_box_shipping_box_dimensions_content();
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_shipping_box_dimensions_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_shipping_box_dimensions_images($post_id);

    return $post_id;
}

function custom_box_sync_shipping_box_dimensions_terms(int $post_id, array $data): void
{
    $category = get_term_by('slug', $data['category']['slug'], 'category');
    if (!$category || is_wp_error($category)) {
        $created = wp_insert_term($data['category']['name'], 'category', array('slug' => $data['category']['slug']));
        if (!is_wp_error($created)) {
            $category = get_term((int) $created['term_id'], 'category');
        }
    }
    if ($category && !is_wp_error($category)) {
        wp_set_post_categories($post_id, array((int) $category->term_id), false);
    }

    $tag_ids = array();
    foreach ($data['tags'] as $name => $slug) {
        $tag = get_term_by('slug', $slug, 'post_tag');
        if (!$tag || is_wp_error($tag)) {
            $created = wp_insert_term($name, 'post_tag', array('slug' => $slug));
            if (!is_wp_error($created)) {
                $tag_ids[] = (int) $created['term_id'];
            }
        } else {
            $tag_ids[] = (int) $tag->term_id;
        }
    }
    wp_set_post_terms($post_id, $tag_ids, 'post_tag', false);
}

function custom_box_sync_shipping_box_dimensions_images(int $post_id): void
{
    $images = custom_box_shipping_box_dimensions_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_shipping_box_dimensions_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_shipping_box_dimensions_attachment($image['base'], $post_id, $image);
        }
        if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
            $missing_images[] = $image['base'];
            continue;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
        wp_update_post(array(
            'ID' => $attachment_id,
            'post_title' => $image['title'],
            'post_excerpt' => $image['caption'],
            'post_parent' => $post_id,
        ));

        if ('featured' === $key) {
            set_post_thumbnail($post_id, $attachment_id);
            continue;
        }

        $marker = '<!-- shipping-box-dimensions-image:' . $key . ' -->';
        $url = wp_get_attachment_url($attachment_id);
        $figure = $marker . "\n<figure><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
        $wrapped_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

        if (preg_match($marker_pattern, $content)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
        } elseif (preg_match($wrapped_pattern, $content)) {
            $content = preg_replace($wrapped_pattern, $figure, $content, 1);
        } elseif (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
        } else {
            $missing_slots[] = $key;
        }
    }

    if ($post && $content !== (string) $post->post_content) {
        wp_update_post(array('ID' => $post_id, 'post_content' => $content));
    }

    update_option('custom_box_shipping_box_dimensions_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_shipping_box_dimensions_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_find_shipping_box_dimensions_attachment(string $base): int
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

function custom_box_create_shipping_box_dimensions_attachment(string $base, int $post_id, array $image): int
{
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }

    foreach (array('webp', 'png', 'jpg', 'jpeg') as $extension) {
        $candidate_relative = '2026/07/' . $base . '.' . $extension;
        $upload_path = trailingslashit($uploads['basedir']) . $candidate_relative;
        $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $candidate_relative;

        if (!file_exists($upload_path) && file_exists($bundle_path)) {
            if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
                continue;
            }
        }
        if (!file_exists($upload_path)) {
            continue;
        }

        $type = wp_check_filetype(wp_basename($upload_path), null);
        $attachment_id = wp_insert_attachment(array(
            'post_mime_type' => $type['type'] ?: 'image/webp',
            'post_title' => $image['title'],
            'post_excerpt' => $image['caption'],
            'post_status' => 'inherit',
            'post_parent' => $post_id,
        ), $upload_path, $post_id, true);

        if (is_wp_error($attachment_id)) {
            return 0;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        update_post_meta((int) $attachment_id, '_wp_attached_file', $candidate_relative);
        $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload_path);
        if (is_array($metadata)) {
            wp_update_attachment_metadata((int) $attachment_id, $metadata);
        }
        update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);

        return (int) $attachment_id;
    }

    return 0;
}

function custom_box_shipping_box_dimensions_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_shipping_box_dimensions_post_data();
    $images = custom_box_shipping_box_dimensions_images();
    $failures = array();

    if (!$post || $data['slug'] !== $post->post_name || $data['excerpt'] !== $post->post_excerpt) {
        $failures[] = 'post identity or excerpt';
    }
    if (!$post || !in_array($post->post_status, array('draft', 'publish', 'private'), true)) {
        $failures[] = 'post status';
    }

    $featured_id = get_post_thumbnail_id($post_id);
    $featured_file = $featured_id ? (string) get_post_meta($featured_id, '_wp_attached_file', true) : '';
    if (!$featured_id || $images['featured']['base'] !== pathinfo(wp_basename($featured_file), PATHINFO_FILENAME)) {
        $failures[] = 'featured image';
    }

    $content = $post ? (string) $post->post_content : '';
    if (
        4 !== substr_count($content, '<!-- shipping-box-dimensions-image:')
        || 4 !== substr_count($content, '<figure>')
        || 4 !== substr_count($content, '<img ')
    ) {
        $failures[] = 'inline image counts';
    }
    foreach (array('slot_1', 'slot_2', 'slot_3', 'slot_4') as $key) {
        if (false === strpos($content, $images[$key]['base'])) {
            $failures[] = $key;
        }
    }
    if (preg_match('/IMAGE_SLOT_[0-9]+/', $content)) {
        $failures[] = 'image placeholders';
    }

    $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    if (is_wp_error($categories) || !in_array($data['category']['slug'], $categories, true)) {
        $failures[] = 'category';
    }

    $tags = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs'));
    $expected_tags = array_values($data['tags']);
    if (is_wp_error($tags)) {
        $failures[] = 'tags';
    } else {
        sort($tags);
        sort($expected_tags);
        if ($tags !== $expected_tags) {
            $failures[] = 'exact tags';
        }
    }

    if (
        $data['seo_title'] !== get_post_meta($post_id, 'rank_math_title', true)
        || $data['seo_description'] !== get_post_meta($post_id, 'rank_math_description', true)
        || $data['focus_keyword'] !== get_post_meta($post_id, 'rank_math_focus_keyword', true)
    ) {
        $failures[] = 'Rank Math metadata';
    }
    if ((array) get_option('custom_box_shipping_box_dimensions_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_shipping_box_dimensions_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_shipping_box_dimensions_validation_failures', array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_shipping_box_dimensions_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_SHIPPING_BOX_DIMENSIONS_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}

function custom_box_shipping_box_dimensions_content(): string
{
    $content = base64_decode('PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZXJlIGlzIG5vIHNpbmdsZSB1bml2ZXJzYWwg4oCcc3RhbmRhcmTigJ0gc2hpcHBpbmcgYm94IHNpemUuIFdoYXQgdGhlIHBhY2thZ2luZyBpbmR1c3RyeSBjYWxscyBzdGFuZGFyZCBzaXplcyBhcmUgc3RvY2sgZGltZW5zaW9ucyB0aGF0IGFyZSB3aWRlbHkgYXZhaWxhYmxlIGZyb20gY29ycnVnYXRlZCBib3ggc3VwcGxpZXJzLiBBbiA4IMOXIDYgw5cgNC1pbmNoIGJveCBtYXkgYmUgY29tbW9uLCBidXQgaXQgaXMgb25seSBlZmZpY2llbnQgd2hlbiB0aGUgcGFja2VkIHByb2R1Y3TigJRub3QganVzdCB0aGUgYmFyZSBwcm9kdWN04oCUZml0cyBjb3JyZWN0bHkuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9yIGJ1c2luZXNzZXMsIGNob29zaW5nIGEgc2hpcHBpbmcgYm94IGludm9sdmVzIGZvdXIgbWVhc3VyZW1lbnRzOiB0aGUgcHJvZHVjdCwgdGhlIHByb3RlY3RpdmUgcGFja2FnaW5nLCB0aGUgZmluaXNoZWQgaW5zaWRlIGRpbWVuc2lvbnMsIGFuZCB0aGUgb3V0c2lkZSBkaW1lbnNpb25zIHVzZWQgYnkgdGhlIGNhcnJpZXIuIElnbm9yaW5nIGFueSBvbmUgb2YgdGhlbSBjYW4gcmVzdWx0IGluIGRhbWFnZWQgcHJvZHVjdHMsIHVubmVjZXNzYXJ5IHZvaWQgZmlsbCwgb3IgYSBoaWdoZXIgZGltZW5zaW9uYWwtd2VpZ2h0IGNoYXJnZS48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNvbW1vbiBTdGFuZGFyZCBCb3ggRGltZW5zaW9ucyBmb3IgU2hpcHBpbmc8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgZm9sbG93aW5nIGNoYXJ0IGxpc3RzIGNvbW1vbiBjb3JydWdhdGVkIHNoaXBwaW5nIGJveCBkaW1lbnNpb25zLiBBdmFpbGFiaWxpdHkgdmFyaWVzIGJ5IHN1cHBsaWVyIGFuZCBjb3VudHJ5LCBzbyB0cmVhdCB0aGVzZSBhcyB1c2VmdWwgc3RhcnRpbmcgcG9pbnRzIHJhdGhlciB0aGFuIG1hbmRhdG9yeSBpbmR1c3RyeSBzdGFuZGFyZHMuPC9zcGFuPg0KPHRhYmxlIHN0eWxlPSJ3aWR0aDogNzAuNzA0MyU7IGhlaWdodDogNTIwcHg7Ij4NCjx0aGVhZD4NCjx0ciBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+DQo8dGggc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Cb3ggZGltZW5zaW9ucyAoTCDDlyBXIMOXIEgpPC9zcGFuPjwvdGg+DQo8dGggc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NZXRyaWMgZXF1aXZhbGVudDwvc3Bhbj48L3RoPg0KPHRoIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Vm9sdW1lPC9zcGFuPjwvdGg+DQo8dGggc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Fc3RpbWF0ZWQgRElNIHdlaWdodCBhdCAxMzk8L3NwYW4+PC90aD4NCjx0aCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlR5cGljYWwgcGFja2VkIHByb2R1Y3RzPC9zcGFuPjwvdGg+DQo8L3RyPg0KPC90aGVhZD4NCjx0Ym9keT4NCjx0ciBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij40IMOXIDQgw5cgNCBpbjwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MTAuMiDDlyAxMC4yIMOXIDEwLjIgY208L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjY0IGluwrM8L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEgbGI8L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNtYWxsIGNvbXBvbmVudHMsIHNhbXBsZXMsIGNvbXBhY3QgYWNjZXNzb3JpZXM8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHIgc3R5bGU9ImhlaWdodDogNTJweDsiPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+NiDDlyA2IMOXIDQgaW48L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjE1LjIgw5cgMTUuMiDDlyAxMC4yIGNtPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij4xNDQgaW7Cszwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MiBsYjwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U21hbGwgY29zbWV0aWNzLCBzdXBwbGVtZW50cywgZWxlY3Ryb25pYyBhY2Nlc3Nvcmllczwvc3Bhbj48L3RkPg0KPC90cj4NCjx0ciBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij44IMOXIDYgw5cgNCBpbjwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MjAuMyDDlyAxNS4yIMOXIDEwLjIgY208L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjE5MiBpbsKzPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij4yIGxiPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Cb29rcywgc2tpbmNhcmUgc2V0cywgZm9sZGVkIGFwcGFyZWwsIHNtYWxsIGdpZnRzPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEwIMOXIDggw5cgNiBpbjwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MjUuNCDDlyAyMC4zIMOXIDE1LjIgY208L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjQ4MCBpbsKzPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij40IGxiPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TaG9lcywgY2FuZGxlcywga2l0Y2hlbiBhY2Nlc3NvcmllcywgcHJvZHVjdCBraXRzPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEyIMOXIDkgw5cgNiBpbjwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MzAuNSDDlyAyMi45IMOXIDE1LjIgY208L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjY0OCBpbsKzPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij41IGxiPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BcHBhcmVsIGJ1bmRsZXMsIG11bHRpcGxlIGJvb2tzLCBib3hlZCByZXRhaWwgcHJvZHVjdHM8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHIgc3R5bGU9ImhlaWdodDogNTJweDsiPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MTIgw5cgMTAgw5cgOCBpbjwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MzAuNSDDlyAyNS40IMOXIDIwLjMgY208L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjk2MCBpbsKzPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij43IGxiPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TdWJzY3JpcHRpb24ga2l0cywgZm9vdHdlYXIsIGhvbWUgcHJvZHVjdHM8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHIgc3R5bGU9ImhlaWdodDogNTJweDsiPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MTYgw5cgMTIgw5cgOCBpbjwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+NDAuNiDDlyAzMC41IMOXIDIwLjMgY208L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEsNTM2IGluwrM8L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEyIGxiPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Cb2FyZCBnYW1lcywgZWxlY3Ryb25pY3Mga2l0cywgbGFyZ2VyIGFwcGFyZWwgb3JkZXJzPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjE4IMOXIDE0IMOXIDEyIGluPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij40NS43IMOXIDM1LjYgw5cgMzAuNSBjbTwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MywwMjQgaW7Cszwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MjIgbGI8L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNtYWxsIGFwcGxpYW5jZXMsIGhvbWV3YXJlLCBtdWx0aS1wcm9kdWN0IHNoaXBtZW50czwvc3Bhbj48L3RkPg0KPC90cj4NCjx0ciBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij4yNCDDlyAxOCDDlyAxOCBpbjwvc3Bhbj48L3RkPg0KPHRkIHN0eWxlPSJoZWlnaHQ6IDUycHg7Ij48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+NjEuMCDDlyA0NS43IMOXIDQ1LjcgY208L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjcsNzc2IGluwrM8L3NwYW4+PC90ZD4NCjx0ZCBzdHlsZT0iaGVpZ2h0OiA1MnB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjU2IGxiPC9zcGFuPjwvdGQ+DQo8dGQgc3R5bGU9ImhlaWdodDogNTJweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5CdWxreSBnb29kcywgYmVkZGluZyBhbmQgY29uc29saWRhdGVkIHNoaXBtZW50czwvc3Bhbj48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxlbT5UaGUgRElNLXdlaWdodCBjb2x1bW4gaXMgYW4gaWxsdXN0cmF0aW9uIGJhc2VkIG9uIGEgZGl2aXNvciBvZiAxMzkgYW5kIHJvdW5kaW5nIHVwIHRvIHRoZSBuZXh0IHBvdW5kLiBJdCBpcyBub3QgYSBzaGlwcGluZyBxdW90ZS4gQ2Fycmllciwgc2VydmljZSwgZGVzdGluYXRpb24gYW5kIGN1c3RvbWVyLXJhdGUgcnVsZXMgbXVzdCBiZSBjaGVja2VkIGJlZm9yZSB1c2UuPC9lbT48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48IS0tIElNQUdFX1NMT1RfMSAtLT48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkhvdyBTaGlwcGluZyBCb3ggRGltZW5zaW9ucyBBcmUgV3JpdHRlbjwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNvcnJ1Z2F0ZWQgYm94IGRpbWVuc2lvbnMgYXJlIG5vcm1hbGx5IHdyaXR0ZW4gaW4gdGhpcyBvcmRlcjo8L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkxlbmd0aCDDlyBXaWR0aCDDlyBIZWlnaHQ8L3N0cm9uZz48L3NwYW4+DQo8dWw+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5MZW5ndGg6PC9zdHJvbmc+IHRoZSBsb25nZXN0IHNpZGUgb2YgdGhlIGJveCBvcGVuaW5nLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+V2lkdGg6PC9zdHJvbmc+IHRoZSBzaG9ydGVyIHNpZGUgb2YgdGhlIG9wZW5pbmcuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5IZWlnaHQ6PC9zdHJvbmc+IHRoZSBkaXN0YW5jZSBmcm9tIHRoZSBvcGVuaW5nIHRvIHRoZSBib3R0b20gd2hlbiB0aGUgYm94IGlzIGFzc2VtYmxlZC48L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSAxMiDDlyA5IMOXIDYtaW5jaCBib3ggdGhlcmVmb3JlIGhhcyBhIDEyLWJ5LTktaW5jaCBvcGVuaW5nIGFuZCBhIGZpbmlzaGVkIGRlcHRoIG9mIDYgaW5jaGVzLiBLZWVwaW5nIHRoaXMgb3JkZXIgY29uc2lzdGVudCBtYXR0ZXJzIHdoZW4gcmVxdWVzdGluZyBxdW90YXRpb25zLCBhcHByb3ZpbmcgZGllbGluZXMgYW5kIGVudGVyaW5nIHBhcmNlbCBkYXRhIGludG8gYSBjYXJyaWVyIHN5c3RlbS48L3NwYW4+DQo8aDM+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkluc2lkZSBkaW1lbnNpb25zIGRldGVybWluZSBwcm9kdWN0IGZpdDwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlN0b2NrIGNvcnJ1Z2F0ZWQgYm94ZXMgYXJlIGNvbW1vbmx5IHNvbGQgYnkgdGhlaXIgaW5zaWRlIGRpbWVuc2lvbnMuIFRoZXNlIG1lYXN1cmVtZW50cyB0ZWxsIHRoZSBwYWNrZXIgaG93IG11Y2ggdXNhYmxlIHNwYWNlIGlzIGF2YWlsYWJsZSBhZnRlciB0aGUgYm94IGhhcyBiZWVuIGZvcm1lZC48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Ib3dldmVyLCBzdXBwbGllcnMgZG8gbm90IGFsbCB1c2UgaWRlbnRpY2FsIGNvbnZlbnRpb25zLiBBIHB1cmNoYXNlIHNwZWNpZmljYXRpb24gc2hvdWxkIHN0YXRlIDxzdHJvbmc+ZmluaXNoZWQgaW5zaWRlIGRpbWVuc2lvbnM8L3N0cm9uZz4gcmF0aGVyIHRoYW4gcmVseWluZyBvbiBhbiB1bmxhYmVsZWQgc2V0IG9mIG51bWJlcnMuPC9zcGFuPg0KPGgzPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5PdXRzaWRlIGRpbWVuc2lvbnMgZGV0ZXJtaW5lIHNoaXBwaW5nIHZvbHVtZTwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNhcnJpZXJzIG1lYXN1cmUgdGhlIG91dHNpZGUgb2YgdGhlIGNsb3NlZCBwYXJjZWwsIGluY2x1ZGluZyBhbnkgYnVsZ2luZyBwYW5lbHMgb3IgcHJvamVjdGluZyBlZGdlcy4gQ29ycnVnYXRlZCBib2FyZCB0aGlja25lc3MsIG92ZXJsYXBwaW5nIGZsYXBzIGFuZCBwYWNraW5nIHByZXNzdXJlIG1ha2UgdGhlIGV4dGVybmFsIG1lYXN1cmVtZW50IGxhcmdlciB0aGFuIHRoZSBpbnRlcm5hbCBtZWFzdXJlbWVudC48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGlzIGRpc3RpbmN0aW9uIGJlY29tZXMgaW1wb3J0YW50IHdoZW4gYSBwYXJjZWwgaXMgY2xvc2UgdG8gYSBkaW1lbnNpb25hbC13ZWlnaHQgb3Igb3ZlcnNpemUgdGhyZXNob2xkLiBEbyBub3QgY29weSB0aGUgc3VwcGxpZXLigJlzIGluc2lkZSBkaW1lbnNpb25zIGRpcmVjdGx5IGludG8gc2hpcHBpbmcgc29mdHdhcmUuIEFzc2VtYmxlLCBmaWxsIGFuZCBjbG9zZSBhIHByb2R1Y3Rpb24gc2FtcGxlLCB0aGVuIG1lYXN1cmUgaXRzIGxvbmdlc3QgZXh0ZXJuYWwgcG9pbnRzLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF8yIC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2FsY3VsYXRlIEJveCBTaXplIGZyb20gdGhlIFBhY2tlZCBQcm9kdWN0PC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIHNhZmVzdCB3YXkgdG8gY2hvb3NlIGEgYm94IGlzIHRvIGJlZ2luIHdpdGggdGhlIHBhY2tlZCBwcm9kdWN0IGVudmVsb3BlLiBUaGlzIG1lYW5zIG1lYXN1cmluZyB0aGUgcHJvZHVjdCB0b2dldGhlciB3aXRoIGV2ZXJ5IGNvbXBvbmVudCB0aGF0IHdpbGwgdHJhdmVsIGluc2lkZSB0aGUgc2hpcHBpbmcgY2FydG9uOjwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5SZXRhaWwgb3IgcHJpbWFyeSBwYWNrYWdpbmc8L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db3JydWdhdGVkIGRpdmlkZXJzIG9yIHBhcGVyIGluc2VydHM8L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db3JuZXIgYW5kIGVkZ2UgcHJvdGVjdGlvbjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlBhcGVyIGN1c2hpb25pbmcgb3Igb3RoZXIgdm9pZCBmaWxsPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SW5zdHJ1Y3Rpb24gY2FyZHMgYW5kIGFjY2Vzc29yaWVzPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2VhbGVkIGJhZ3Mgb3IgbW9pc3R1cmUtcHJvdGVjdGlvbiBjb21wb25lbnRzPC9zcGFuPjwvbGk+DQo8L3VsPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgdXNlZnVsIHN0YXJ0aW5nIGZvcm11bGEgaXM6PC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5SZXF1aXJlZCBpbnNpZGUgbGVuZ3RoID0gcHJvZHVjdCBsZW5ndGggKyBwcm90ZWN0aW9uIG9uIGJvdGggZW5kcyArIGxvYWRpbmcgdG9sZXJhbmNlPC9zdHJvbmc+PC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIHNhbWUgY2FsY3VsYXRpb24gYXBwbGllcyB0byB3aWR0aCBhbmQgaGVpZ2h0LiDigJxQcm90ZWN0aW9uIG9uIGJvdGggZW5kc+KAnSBtZWFucyB0aGF0IDIwIG1tIG9mIGN1c2hpb25pbmcgb24gZWFjaCBlbmQgYWRkcyA0MCBtbSB0byB0aGUgcmVxdWlyZWQgaW5zaWRlIGxlbmd0aC48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Mb2FkaW5nIHRvbGVyYW5jZSBzaG91bGQgYmUgZW5vdWdoIGZvciByZWxpYWJsZSBwYWNraW5nIHdpdGhvdXQgYWxsb3dpbmcgdW5jb250cm9sbGVkIG1vdmVtZW50LiBUb28gbGl0dGxlIGNsZWFyYW5jZSBzbG93cyB0aGUgcGFja2luZyBsaW5lIGFuZCBjYW4gY3J1c2ggY29ybmVycy4gVG9vIG11Y2ggY2xlYXJhbmNlIHRyYW5zZmVycyB0aGUgcHJvYmxlbSB0byB2b2lkIGZpbGwgYW5kIG1heSBhbGxvdyB0aGUgcHJvZHVjdCB0byBnYWluIG1vbWVudHVtIGJlZm9yZSBhbiBpbXBhY3QuPC9zcGFuPg0KPHRhYmxlPg0KPHRoZWFkPg0KPHRyPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Qcm9kdWN0IGNvbmRpdGlvbjwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TaXppbmcgYXBwcm9hY2g8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TWFpbiByaXNrIHRvIGNvbnRyb2w8L3NwYW4+PC90aD4NCjwvdHI+DQo8L3RoZWFkPg0KPHRib2R5Pg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Tb2Z0IGFwcGFyZWwgb3IgdGV4dGlsZXM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VXNlIHRoZSBzdGFibGUgZm9sZGVkIG9yIGJhZ2dlZCBkaW1lbnNpb25zOyBhdm9pZCB1bm5lY2Vzc2FyeSBhaXIgc3BhY2U8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29tcHJlc3Npb24sIHdyaW5rbGluZyBhbmQgaW5jb25zaXN0ZW50IHBhY2sgaGVpZ2h0PC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EdXJhYmxlIGJveGVkIHByb2R1Y3Q8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QWRkIG9ubHkgdGhlIGNsZWFyYW5jZSBuZWVkZWQgZm9yIGxvYWRpbmcgYW5kIHRoZSBzZWxlY3RlZCBwYXBlciBwcm90ZWN0aW9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb2R1Y3QgbW92ZW1lbnQgYW5kIGNvcm5lciBhYnJhc2lvbjwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+R2xhc3MgYm90dGxlLCBjYW5kbGUgb3IgY2VyYW1pYyBpdGVtPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRlc2lnbiB0aGUgaW5zZXJ0IGFuZCBpbXBhY3QtcHJvdGVjdGlvbiBzeXN0ZW0gYmVmb3JlIGZpbmFsaXppbmcgdGhlIG91dGVyIGJveDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TaWRlIGltcGFjdCwgdG9wIGxvYWQsIGJyZWFrYWdlIGFuZCBjYXAgZGFtYWdlPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TZXZlcmFsIHByb2R1Y3RzIGluIG9uZSBzaGlwbWVudDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NZWFzdXJlIHRoZSBjb21wbGV0ZSBhcnJhbmdlZCBwYWNrLCBpbmNsdWRpbmcgcGFydGl0aW9ucyBhbmQgb3JpZW50YXRpb248L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHJvZHVjdC10by1wcm9kdWN0IGNvbnRhY3QgYW5kIGxvYWQgY29uY2VudHJhdGlvbjwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SXJyZWd1bGFyIHByb2R1Y3Q8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TWVhc3VyZSB0aGUgbWF4aW11bSBwYWNrZWQgcG9pbnRzIGFmdGVyIHByb3RydXNpb25zIGFyZSBwcm90ZWN0ZWQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFuZWwgYnVsZ2luZyBhbmQgaW5jb3JyZWN0IGNhcnJpZXIgZGltZW5zaW9uczwvc3Bhbj48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgYmxhbmtldCBpbnN0cnVjdGlvbiBzdWNoIGFzIOKAnGFkZCB0d28gaW5jaGVzIHRvIGV2ZXJ5IHNpZGXigJ0gaXMgbm90IGEgcmVsaWFibGUgc3BlY2lmaWNhdGlvbi4gQSBzb2Z0IGFwcGFyZWwgcGFjayBhbmQgYSBnbGFzcyBkaWZmdXNlciBoYXZlIHZlcnkgZGlmZmVyZW50IHByb3RlY3Rpb24gcmVxdWlyZW1lbnRzLiBDdXNoaW9uaW5nIHRoaWNrbmVzcyBzaG91bGQgY29tZSBmcm9tIHRoZSBwcm9kdWN04oCZcyBmcmFnaWxpdHksIHdlaWdodCwgZGlzdHJpYnV0aW9uIHJvdXRlIGFuZCBwYWNrYWdpbmcgdGVzdOKAlG5vdCBmcm9tIGEgZ2VuZXJpYyBib3gtc2l6ZSBjaGFydC48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkhvdyBCb3ggU2l6ZSBDaGFuZ2VzIERpbWVuc2lvbmFsIFdlaWdodDwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNhcnJpZXJzIGNvbXBhcmUgaG93IG11Y2ggYSBwYXJjZWwgd2VpZ2hzIHdpdGggaG93IG11Y2ggdmVoaWNsZSBzcGFjZSBpdCBvY2N1cGllcy4gQSBsYXJnZSwgbGlnaHR3ZWlnaHQgYm94IG1heSB0aGVyZWZvcmUgYmUgYmlsbGVkIGJ5IGRpbWVuc2lvbmFsIHdlaWdodCByYXRoZXIgdGhhbiBzY2FsZSB3ZWlnaHQuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGNvbW1vbiBjYWxjdWxhdGlvbiBpczo8L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkRpbWVuc2lvbmFsIHdlaWdodCA9IG91dHNpZGUgbGVuZ3RoIMOXIG91dHNpZGUgd2lkdGggw5cgb3V0c2lkZSBoZWlnaHQgw7cgRElNIGRpdmlzb3I8L3N0cm9uZz48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb3IgZXhhbXBsZSwgY29uc2lkZXIgYSBwcm9kdWN0IHdpdGggYW4gYWN0dWFsIHBhY2tlZCB3ZWlnaHQgb2YgMyBsYjo8L3NwYW4+DQo8dGFibGU+DQo8dGhlYWQ+DQo8dHI+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk91dHNpZGUgYm94IHNpemU8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q3ViaWMgdm9sdW1lPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRJTSBjYWxjdWxhdGlvbiBhdCAxMzk8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SWxsdXN0cmF0aXZlIGJpbGxhYmxlIHdlaWdodDwvc3Bhbj48L3RoPg0KPC90cj4NCjwvdGhlYWQ+DQo8dGJvZHk+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEyIMOXIDEwIMOXIDggaW48L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+OTYwIGluwrM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+OTYwIMO3IDEzOSA9IDYuOTE8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+NyBsYjwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MTYgw5cgMTIgw5cgOCBpbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij4xLDUzNiBpbsKzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEsNTM2IMO3IDEzOSA9IDExLjA1PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEyIGxiPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPC90Ym9keT4NCjwvdGFibGU+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TW92aW5nIHRoZSBzYW1lIDMgbGIgcHJvZHVjdCBpbnRvIHRoZSBsYXJnZXIgYm94IGluY3JlYXNlcyB0aGUgaWxsdXN0cmF0aXZlIGJpbGxhYmxlIHdlaWdodCBmcm9tIDcgbGIgdG8gMTIgbGIuIFRoZSBleHRyYSBzcGFjZSBtYXkgbG9vayBoYXJtbGVzcyBhdCB0aGUgcGFja2luZyB0YWJsZSwgYnV0IHRoZSBlZmZlY3QgaXMgbXVsdGlwbGllZCBhY3Jvc3MgZXZlcnkgcGFyY2VsIHNoaXBwZWQuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2FycmllciBydWxlcyBhcmUgbm90IGlkZW50aWNhbC4gRmVkRXggcHVibGlzaGVzIGEgZGl2aXNvciBvZiAxMzkgZm9yIHJlbGV2YW50IFUuUy4sIFB1ZXJ0byBSaWNvIGFuZCBpbnRlcm5hdGlvbmFsIHNoaXBtZW50cy4gVVBTIGN1cnJlbnRseSBpZGVudGlmaWVzIGRpZmZlcmVudCBmYWN0b3JzIGZvciBEYWlseSBhbmQgUmV0YWlsIFJhdGVzLiBVU1BTIGFwcGxpZXMgaXRzIHB1Ymxpc2hlZCBkaW1lbnNpb25hbC13ZWlnaHQgbWV0aG9kIHRvIHF1YWxpZnlpbmcgbGFyZ2UsIGxpZ2h0d2VpZ2h0IHBhcmNlbHMuIEFsd2F5cyB2ZXJpZnkgdGhlIHNlcnZpY2UgYW5kIHJhdGUgYWdyZWVtZW50IHVzZWQgYnkgeW91ciBidXNpbmVzcy48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48IS0tIElNQUdFX1NMT1RfMyAtLT48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkJveCBEaW1lbnNpb25zIERvIE5vdCBUZWxsIFlvdSBCb3ggU3RyZW5ndGg8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Ud28gY2FydG9ucyB3aXRoIGlkZW50aWNhbCAxMiDDlyAxMCDDlyA4LWluY2ggZGltZW5zaW9ucyBjYW4gcGVyZm9ybSBkaWZmZXJlbnRseS4gVGhlIGRpbWVuc2lvbnMgZGVzY3JpYmUgdXNhYmxlIHNwYWNlOyB0aGV5IGRvIG5vdCBzcGVjaWZ5IHRoZSBjb3JydWdhdGVkIGJvYXJkIGdyYWRlLCBmbHV0ZSwgZWRnZSBjcnVzaCByZXNpc3RhbmNlLCBjbG9zdXJlIG1ldGhvZCBvciBzdGFja2luZyBwZXJmb3JtYW5jZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaGVuIHNlbGVjdGluZyBhIHNoaXBwaW5nIGNhcnRvbiwgZXZhbHVhdGUgc2l6ZSB0b2dldGhlciB3aXRoOjwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BY3R1YWwgcGFja2VkIHByb2R1Y3Qgd2VpZ2h0PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2luZ2xlLXdhbGwgb3IgZG91YmxlLXdhbGwgY29uc3RydWN0aW9uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rmx1dGUgcHJvZmlsZSBhbmQgdG90YWwgYm9hcmQgdGhpY2tuZXNzPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RWRnZSBDcnVzaCBUZXN0IG9yIG90aGVyIHN1cHBsaWVyIHN0cmVuZ3RoIHNwZWNpZmljYXRpb248L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Qcm9kdWN0IGZyYWdpbGl0eSBhbmQgaW50ZXJuYWwgc3VwcG9ydDwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlN0YWNraW5nIGhlaWdodCBhbmQgd2FyZWhvdXNlIGh1bWlkaXR5PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFyY2VsLCBwYWxsZXQgb3IgbWl4ZWQgZGlzdHJpYnV0aW9uIHJvdXRlPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGFwZSBwYXR0ZXJuIGFuZCBjbG9zdXJlIGludGVncml0eTwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIGxhcmdlciBjYXJ0b24gaXMgbm90IGF1dG9tYXRpY2FsbHkgc3Ryb25nZXIuIEluIGZhY3QsIHdpZGUgdW5zdXBwb3J0ZWQgcGFuZWxzIG1heSBib3cgb3IgY29sbGFwc2Ugd2hlbiB0aGUgYm94IGhhcyBleGNlc3NpdmUgZW1wdHkgc3BhY2UuIEEgYmV0dGVyIGZpdCwgY29ycmVjdGx5IHNwZWNpZmllZCBjb3JydWdhdGVkIGJvYXJkIGFuZCBhIHdlbGwtZGVzaWduZWQgaW5zZXJ0IGNhbiBiZSBtb3JlIGVmZmVjdGl2ZSB0aGFuIHNpbXBseSBtb3ZpbmcgdG8gYSBiaWdnZXIgY2FydG9uLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkZvciBwcm9kdWN0cyB0aGF0IG5lZWQgY29udHJvbGxlZCBpbnRlcm5hbCBzdXBwb3J0LCByZXZpZXcgaG93IDxhIGhyZWY9Imh0dHBzOi8vaG9wZ2lheXZwbi5jb20vaG93LWluc2VydHMtcHJvdGVjdC1wcm9kdWN0cy1wYXBlci1ib3hlcy8iPmluc2VydHMgcHJvdGVjdCBwcm9kdWN0cyBpbnNpZGUgcGFwZXIgYm94ZXM8L2E+IGJlZm9yZSBmaW5hbGl6aW5nIHRoZSBzaGlwcGluZyBib3ggZGltZW5zaW9ucy48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJTQyBTaGlwcGluZyBDYXJ0b25zIHZzIENvcnJ1Z2F0ZWQgTWFpbGVyIEJveGVzPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIHNpemUgY2hhcnQgYWJvdmUgaXMgbW9zdCBkaXJlY3RseSBhcHBsaWNhYmxlIHRvIHJlZ3VsYXIgc2xvdHRlZCBjb250YWluZXJzLCBvciBSU0NzLiBUaGVzZSBib3hlcyBoYXZlIGZvdXIgdG9wIGFuZCBmb3VyIGJvdHRvbSBmbGFwcyBhbmQgYXJlIGNvbW1vbmx5IHVzZWQgZm9yIGdlbmVyYWwgc2hpcHBpbmcsIGNhc2UgcGFja2luZyBhbmQgd2FyZWhvdXNlIGRpc3RyaWJ1dGlvbi48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EaWUtY3V0IGNvcnJ1Z2F0ZWQgbWFpbGVyIGJveGVzIHVzZSBmb2xkZWQgc2lkZSB3YWxscywgbG9ja2luZyB0YWJzIGFuZCBhbiBhdHRhY2hlZCBsaWQuIFRoZWlyIG5vbWluYWwgaW5zaWRlIGRpbWVuc2lvbnMgbWF5IGJlIHNpbWlsYXIsIGJ1dCB0aGUgZmxhdCBibGFuaywgbWF0ZXJpYWwgY29uc3VtcHRpb24sIHVzYWJsZSBjb3JuZXIgc3BhY2UgYW5kIHBhY2tpbmcgcHJvY2VzcyBhcmUgZGlmZmVyZW50Ljwvc3Bhbj4NCjx0YWJsZT4NCjx0aGVhZD4NCjx0cj4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RGVjaXNpb24gZmFjdG9yPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJTQyBzaGlwcGluZyBjYXJ0b248L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29ycnVnYXRlZCBtYWlsZXIgYm94PC9zcGFuPjwvdGg+DQo8L3RyPg0KPC90aGVhZD4NCjx0Ym9keT4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VHlwaWNhbCByb2xlPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkdlbmVyYWwgc2hpcHBpbmcsIG1hc3RlciBjYXJ0b25zIGFuZCBidWxrIGRpc3RyaWJ1dGlvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5FY29tbWVyY2UsIHN1YnNjcmlwdGlvbiBhbmQgYnJhbmRlZCBkaXJlY3QtdG8tY3VzdG9tZXIgcGFja2FnaW5nPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5DbG9zdXJlPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlVzdWFsbHkgcmVxdWlyZXMgdGFwZTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EaWUtY3V0IGxvY2tpbmcgdGFiczsgdGFwZSBtYXkgc3RpbGwgYmUgdXNlZCBmb3IgdHJhbnNpdCBzZWN1cml0eTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SW50ZXJuYWwgc3BhY2U8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2ltcGxlIHJlY3Rhbmd1bGFyIHZvbHVtZTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb2xkZWQgc2lkZSB3YWxscyBtYXkgYWZmZWN0IHVzYWJsZSBjb3JuZXIgc3BhY2U8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkN1c3RvbWl6YXRpb248L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2l6ZSwgYm9hcmQgZ3JhZGUgYW5kIHByaW50aW5nIGNhbiBiZSBjdXN0b21pemVkPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNpemUsIGxvY2sgZGVzaWduLCBwcmludGluZyBhbmQgaW5zZXJ0IHN5c3RlbSBjYW4gYmUgY3VzdG9taXplZDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QmVzdCByZWFzb24gdG8gY2hvb3NlPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkVmZmljaWVudCBnZW5lcmFsLXB1cnBvc2Ugc2hpcHBpbmcgYW5kIGNhc2UgcGFja2luZzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JbnRlZ3JhdGVkIHByZXNlbnRhdGlvbiBhbmQgc2hpcHBpbmcgc3RydWN0dXJlPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPC90Ym9keT4NCjwvdGFibGU+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QnJhbmRzIHRoYXQgbmVlZCBhIHByaW50ZWQgZWNvbW1lcmNlIHN0cnVjdHVyZSBjYW4gY29tcGFyZSBhdmFpbGFibGUgPGEgaHJlZj0iaHR0cHM6Ly9ob3BnaWF5dnBuLmNvbS9wcm9kdWN0cy9jb3JydWdhdGVkLW1haWxlci1ib3hlcy8iPmN1c3RvbSBjb3JydWdhdGVkIG1haWxlciBib3hlczwvYT4uIENvbmZpcm0gZmluaXNoZWQgaW5zaWRlIGRpbWVuc2lvbnMgdXNpbmcgYSBwaHlzaWNhbCBzYW1wbGUgYmVjYXVzZSBmb2xkZWQgcGFuZWxzIGFuZCB0YWJzIGNhbiBhZmZlY3QgdGhlIHVzYWJsZSBmaXQuPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaGVuIGEgU3RhbmRhcmQgQm94IElzIHRoZSBCZXR0ZXIgQ2hvaWNlPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBzdG9jayBzaGlwcGluZyBib3ggaXMgdXN1YWxseSB0aGUgcHJhY3RpY2FsIGNob2ljZSB3aGVuOjwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgcGFja2VkIHByb2R1Y3QgZml0cyB3aXRob3V0IGV4Y2Vzc2l2ZSB2b2lkIGZpbGwuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+T3JkZXIgdm9sdW1lIGlzIHVuY2VydGFpbiBvciBkaXZpZGVkIGFjcm9zcyBtYW55IHByb2R1Y3QgdHlwZXMuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGJ1c2luZXNzIG5lZWRzIGNhcnRvbnMgaW1tZWRpYXRlbHkuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QnJhbmQgcHJpbnRpbmcgaXMgbm90IHJlcXVpcmVkIG9uIHRoZSBzaGlwcGluZyBib3guPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBzbWFsbCBzZXQgb2Ygc3RvY2sgc2l6ZXMgY2FuIGNvdmVyIG1vc3Qgb3JkZXJzIGVmZmljaWVudGx5Ljwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Vc2luZyBzdG9jayBib3hlcyBkb2VzIG5vdCBtZWFuIHVzaW5nIGEgZGlmZmVyZW50IGJveCBmb3IgZXZlcnkgcHJvZHVjdC4gTWFueSBmdWxmaWxsbWVudCBvcGVyYXRpb25zIHdvcmsgbW9yZSBlZmZpY2llbnRseSB3aXRoIGEgY29udHJvbGxlZCBzZXQgb2YgY29yZSBzaXplcy4gUmV2aWV3IG9yZGVyIGhpc3RvcnksIGdyb3VwIHBhY2tlZCBkaW1lbnNpb25zIGludG8gc2l6ZSByYW5nZXMsIGFuZCBjYWxjdWxhdGUgaG93IG11Y2ggdW51c2VkIHZvbHVtZSBlYWNoIHByb3Bvc2VkIGJveCBjcmVhdGVzLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgdXNlZnVsIGJveC1zaXplIHJhdGlvbmFsaXphdGlvbiByZXZpZXcgaW5jbHVkZXM6PC9zcGFuPg0KPG9sPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkV4cG9ydCB0aGUgcGFja2VkIGRpbWVuc2lvbnMgYW5kIHdlaWdodHMgb2YgcmVwcmVzZW50YXRpdmUgb3JkZXJzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPklkZW50aWZ5IHRoZSBwcm9kdWN0cyBvciBvcmRlciBjb21iaW5hdGlvbnMgcmVzcG9uc2libGUgZm9yIG1vc3Qgc2hpcG1lbnRzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRlc3QgdGhyZWUgdG8gZml2ZSBjYW5kaWRhdGUgY2FydG9uIHNpemVzIGFnYWluc3QgdGhvc2Ugb3JkZXJzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNhbGN1bGF0ZSB2b2lkIHZvbHVtZSBhbmQgZXN0aW1hdGVkIERJTSB3ZWlnaHQgZm9yIGV2ZXJ5IGNhbmRpZGF0ZS48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5LZWVwIGEgc3BlY2lhbHR5IHNpemUgb25seSB3aGVuIGl0IG1hdGVyaWFsbHkgcmVkdWNlcyBkYW1hZ2UsIHBhY2tpbmcgdGltZSBvciBmcmVpZ2h0IGNvc3QuPC9zcGFuPjwvbGk+DQo8L29sPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaGVuIEN1c3RvbSBTaGlwcGluZyBEaW1lbnNpb25zIEFyZSBXb3J0aCBDb25zaWRlcmluZzwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgY3VzdG9tIGNvcnJ1Z2F0ZWQgYm94IGJlY29tZXMgbW9yZSB1c2VmdWwgd2hlbiBhIHN0b2NrIGNhcnRvbiByZXBlYXRlZGx5IGNyZWF0ZXMgYSBtZWFzdXJhYmxlIG9wZXJhdGlvbmFsIHByb2JsZW0uPC9zcGFuPg0KPHRhYmxlPg0KPHRoZWFkPg0KPHRyPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5PYnNlcnZlZCBwcm9ibGVtPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoYXQgY3VzdG9tIHNpemluZyBtYXkgaW1wcm92ZTwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaGF0IG11c3Qgc3RpbGwgYmUgdmVyaWZpZWQ8L3NwYW4+PC90aD4NCjwvdHI+DQo8L3RoZWFkPg0KPHRib2R5Pg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5MYXJnZSBxdWFudGl0eSBvZiB2b2lkIGZpbGwgcGVyIG9yZGVyPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNsb3NlciBmaXQgYW5kIGxvd2VyIG1hdGVyaWFsIGNvbnN1bXB0aW9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkN1c2hpb25pbmcgYW5kIGRyb3AtdGVzdCBwZXJmb3JtYW5jZTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RElNIHdlaWdodCBpcyBjb25zaXN0ZW50bHkgaGlnaGVyIHRoYW4gYWN0dWFsIHdlaWdodDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5SZWR1Y2VkIGV4dGVybmFsIGN1YmU8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2FycmllciByYXRlIGFuZCBkaXZpc29yIHVzZWQgYnkgdGhlIGJ1c2luZXNzPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5GcmFnaWxlIHByb2R1Y3QgbW92ZXMgaW5zaWRlIHRoZSBjYXJ0b248L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHVycG9zZS1idWlsdCBpbnNlcnQgYW5kIGNvbnRyb2xsZWQgY2xlYXJhbmNlPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkltcGFjdCwgdmlicmF0aW9uIGFuZCBjb21wcmVzc2lvbiBwZXJmb3JtYW5jZTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFja2luZyBzdGFmZiB1c2UgZGlmZmVyZW50IGFycmFuZ2VtZW50czwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5SZXBlYXRhYmxlIG9yaWVudGF0aW9uIGFuZCBmYXN0ZXIgcGFja2luZzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Mb2FkaW5nIHRvbGVyYW5jZSBhbmQgYXNzZW1ibHkgaW5zdHJ1Y3Rpb25zPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5DYXJ0b25zIHN0YWNrIGluZWZmaWNpZW50bHkgb24gcGFsbGV0czwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5CZXR0ZXIgbGF5ZXIgYXJyYW5nZW1lbnQgYW5kIHBhbGxldCB1dGlsaXphdGlvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5PdmVyaGFuZywgdG90YWwgc3RhY2sgaGVpZ2h0IGFuZCBjb21wcmVzc2lvbiBzdHJlbmd0aDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIHNoaXBwZXIgYWxzbyBuZWVkcyBicmFuZGVkIHByZXNlbnRhdGlvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JbnRlZ3JhdGVkIHByaW50aW5nIGFuZCBlY29tbWVyY2UgZXhwZXJpZW5jZTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TY3VmZmluZywgdGFwZSBwbGFjZW1lbnQgYW5kIGxhYmVsIGFyZWE8L3NwYW4+PC90ZD4NCjwvdHI+DQo8L3Rib2R5Pg0KPC90YWJsZT4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5DdXN0b20gZG9lcyBub3QgYXV0b21hdGljYWxseSBtZWFuIGNoZWFwZXIuIFRvb2xpbmcsIHByaW50aW5nLCBvcmRlciBxdWFudGl0eSwgYm9hcmQgdXRpbGl6YXRpb24gYW5kIHN0b3JhZ2UgcmVxdWlyZW1lbnRzIG11c3QgYmUgY29tcGFyZWQgd2l0aCB0aGUgY29udGludWluZyBjb3N0IG9mIG92ZXJzaXplZCBzdG9jayBjYXJ0b25zLiBUaGUgY29ycmVjdCBkZWNpc2lvbiBpcyBiYXNlZCBvbiB0b3RhbCBwYWNraW5nIGFuZCBsb2dpc3RpY3MgY29zdCwgbm90IGNhcnRvbiB1bml0IHByaWNlIGFsb25lLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF80IC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2hpcHBpbmcgQm94IFNwZWNpZmljYXRpb24gQ2hlY2tsaXN0PC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QmVmb3JlIGFza2luZyBhIHBhY2thZ2luZyBzdXBwbGllciB0byByZWNvbW1lbmQgZGltZW5zaW9ucywgcHJlcGFyZSBhIGJyaWVmIGNvbnRhaW5pbmc6PC9zcGFuPg0KPHVsPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb2R1Y3QgZGltZW5zaW9ucyBhbmQgd2VpZ2h0PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RGltZW5zaW9ucyBhbmQgd2VpZ2h0IGFmdGVyIHJldGFpbCBwYWNrYWdpbmc8L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5RdWFudGl0eSBvZiBwcm9kdWN0cyBwYWNrZWQgcGVyIHNoaXBwaW5nIGJveDwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJlcXVpcmVkIHByb2R1Y3Qgb3JpZW50YXRpb248L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5GcmFnaWxlIHBvaW50cywgcHJvdHJ1c2lvbnMgYW5kIHN1cmZhY2VzIHRoYXQgY2Fubm90IGJlIHNjcmF0Y2hlZDwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByZWZlcnJlZCBjdXNoaW9uaW5nLCBkaXZpZGVyIG9yIGluc2VydCBzeXN0ZW08L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UYXJnZXQgZmluaXNoZWQgaW5zaWRlIGRpbWVuc2lvbnMsIGNsZWFybHkgbGFiZWxlZDwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk1heGltdW0gb3V0c2lkZSBkaW1lbnNpb25zIGlmIGEgY2FycmllciB0aHJlc2hvbGQgYXBwbGllczwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFjdHVhbCBzaGlwcGluZyByb3V0ZTogcGFyY2VsLCBwYWxsZXQsIGV4cG9ydCBjYXJ0b24gb3IgbWl4ZWQgZGlzdHJpYnV0aW9uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RXhwZWN0ZWQgc3RhY2tpbmcgY29uZGl0aW9uczwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByaW50aW5nLCBsYWJlbCBhbmQgdGFwZSB6b25lczwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJlcHJlc2VudGF0aXZlIHBoeXNpY2FsIHByb2R1Y3RzIGZvciBzYW1wbGUgZml0dGluZzwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BcHByb3ZlIHRoZSBib3ggd2l0aCB0aGUgcmVhbCBwYWNrZWQgcHJvZHVjdCwgbm90IGFuIGVtcHR5IGNhcnRvbi4gQ2hlY2sgbG9hZGluZyB0aW1lLCBsaWQgY2xvc3VyZSwgcGFuZWwgYnVsZ2luZywgcHJvZHVjdCBtb3ZlbWVudCBhbmQgZmluYWwgZXh0ZXJuYWwgZGltZW5zaW9ucy4gRm9yIGEgcmVjdXJyaW5nIG9yIGhpZ2gtdm9sdW1lIHNoaXBtZW50LCB0cmlhbCBjYXJ0b25zIHNob3VsZCBhbHNvIGJlIGV2YWx1YXRlZCB1bmRlciBjb25kaXRpb25zIHRoYXQgcmVmbGVjdCB0aGUgYWN0dWFsIGRpc3RyaWJ1dGlvbiByb3V0ZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JZiBhIHN0b2NrIHNpemUgaXMgY3JlYXRpbmcgZXhjZXNzIHNwYWNlIG9yIGluY29uc2lzdGVudCBwcm90ZWN0aW9uLCBzZW5kIHRoZSBwcm9kdWN0IGRpbWVuc2lvbnMsIHBhY2tlZCB3ZWlnaHQsIHNoaXBwaW5nIG1ldGhvZCBhbmQgdGFyZ2V0IG91dHNpZGUtc2l6ZSBsaW1pdCB0byBhIDxhIGhyZWY9Imh0dHBzOi8vaG9wZ2lheXZwbi5jb20vY3VzdG9tLXBhY2thZ2luZy1ib3hlcy1tYW51ZmFjdHVyZXIvIj5jdXN0b20gcGFja2FnaW5nIGJveGVzIG1hbnVmYWN0dXJlcjwvYT4uIFRoZXNlIGlucHV0cyBhbGxvdyB0aGUgc3VwcGxpZXIgdG8gYXNzZXNzIHdoZXRoZXIgYSBzdGFuZGFyZCBSU0MsIGEgY3VzdG9tIGNvcnJ1Z2F0ZWQgbWFpbGVyIG9yIGEgcHVycG9zZS1idWlsdCBpbnNlcnQgaXMgdGhlIG1vcmUgcHJhY3RpY2FsIHN0cnVjdHVyZS48L3NwYW4+', true);

    return is_string($content) ? $content : '';
}
