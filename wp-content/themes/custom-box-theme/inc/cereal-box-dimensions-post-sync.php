<?php
/**
 * Deploys the cereal box dimensions draft and its five local images.
 */

const CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_SYNC_VERSION = '2026-08-06-v1';
const CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_VERSION_OPTION = 'custom_box_cereal_box_dimensions_sync_version';
const CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_NOTICE_OPTION = 'custom_box_cereal_box_dimensions_sync_notice';

add_action('admin_init', 'custom_box_sync_cereal_box_dimensions_post');
add_action('admin_notices', 'custom_box_cereal_box_dimensions_admin_notice');

function custom_box_sync_cereal_box_dimensions_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_cereal_box_dimensions_post_data();
    $post = custom_box_find_cereal_box_dimensions_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_SYNC_VERSION === get_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_VERSION_OPTION)
        && $post
        && custom_box_cereal_box_dimensions_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_cereal_box_dimensions_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_VERSION_OPTION);
        update_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_cereal_box_dimensions_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_VERSION_OPTION, CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Cereal box dimensions draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Design Guides, 5 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_VERSION_OPTION);
    delete_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_NOTICE_OPTION);
    update_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Cereal box dimensions sync is incomplete. Missing images: '
            . implode(', ', (array) get_option('custom_box_cereal_box_dimensions_missing_images', array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option('custom_box_cereal_box_dimensions_missing_slots', array()),
                (array) get_option('custom_box_cereal_box_dimensions_validation_failures', array())
            )),
    ), false);
}
function custom_box_cereal_box_dimensions_post_data(): array
{
    return array(
        'title' => 'What Are the Dimensions of a Cereal Box? Common Sizes and Design Factors',
        'slug' => 'cereal-box-dimensions',
        'excerpt' => 'Learn common cereal box dimensions in inches and centimeters, how to measure a folding carton correctly, and how product volume, inner bags, tolerances and shipping plans determine the final custom size.',
        'category' => array('name' => 'Packaging Design Guides', 'slug' => 'packaging-design-guides'),
        'tags' => array(
            'Cereal Packaging' => 'cereal-packaging',
            'Box Dimensions' => 'box-dimensions',
            'Folding Cartons' => 'folding-cartons',
            'Food Packaging' => 'food-packaging',
            'Packaging Design' => 'packaging-design',
        ),
        'seo_title' => 'Cereal Box Dimensions: Common Sizes and How to Measure',
        'seo_description' => 'See common cereal box dimensions in inches and cm, then learn how product volume, inner bags, tolerances and shelf plans determine a custom box size.',
        'focus_keyword' => 'what are the dimensions of a cereal box',
    );
}

function custom_box_cereal_box_dimensions_images(): array
{
    return array(
        'featured' => array(
            'base' => 'cereal-box-dimensions-size-guide',
            'alt' => 'Common cereal box dimensions measured across height, front width and depth',
            'title' => 'Cereal Box Dimensions Size Guide',
            'caption' => 'A common carton size is only a starting reference; final dimensions should match the filled product.',
        ),
        'slot_1' => array(
            'base' => 'common-cereal-box-size-ranges',
            'alt' => 'Mini, regular and family cereal cartons shown at different dimensions',
            'title' => 'Common Cereal Box Size Ranges',
            'caption' => 'Mini, regular and family cartons overlap in size because their labels are not fixed dimensional standards.',
        ),
        'slot_2' => array(
            'base' => 'cereal-carton-dimension-types',
            'alt' => 'External, internal and manufacturing dimensions of a folding cereal carton',
            'title' => 'Cereal Carton Dimension Types',
            'caption' => 'Finished external size, usable internal space and crease-to-crease manufacturing size serve different purposes.',
        ),
        'slot_3' => array(
            'base' => 'cereal-inner-bag-volume-box-fit',
            'alt' => 'Filled cereal bags with different product densities fitted into cartons',
            'title' => 'Cereal Inner Bag Volume and Box Fit',
            'caption' => 'Cereals with the same net weight may need different carton volumes because their packed densities differ.',
        ),
        'slot_4' => array(
            'base' => 'cereal-box-dimensional-tolerance-check',
            'alt' => 'Cereal carton dimensions checked with ruler, caliper and approved drawing',
            'title' => 'Cereal Box Dimensional Tolerance Check',
            'caption' => 'Dimensional tolerance should be stated on the approved drawing and verified on erected samples.',
        ),
    );
}

function custom_box_find_cereal_box_dimensions_post(string $slug, string $title): ?WP_Post
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

function custom_box_upsert_cereal_box_dimensions_post()
{
    $data = custom_box_cereal_box_dimensions_post_data();
    $post = custom_box_find_cereal_box_dimensions_post($data['slug'], $data['title']);
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
            $payload['post_content'] = custom_box_cereal_box_dimensions_content();
        }
        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = custom_box_cereal_box_dimensions_content();
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_cereal_box_dimensions_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_cereal_box_dimensions_images($post_id);

    return $post_id;
}

function custom_box_sync_cereal_box_dimensions_terms(int $post_id, array $data): void
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

function custom_box_sync_cereal_box_dimensions_images(int $post_id): void
{
    $images = custom_box_cereal_box_dimensions_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_cereal_box_dimensions_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_cereal_box_dimensions_attachment($image['base'], $post_id, $image);
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

        $marker = '<!-- cereal-box-dimensions-image:' . $key . ' -->';
        $url = wp_get_attachment_url($attachment_id);
        $figure = $marker . "\n<figure><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
        $wrapped_pattern = '/<span\\b[^>]*>\\s*' . preg_quote($slot, '/') . '\\s*<\\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\\s*<figure\\b.*?<\\/figure>/is';

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

    update_option('custom_box_cereal_box_dimensions_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_cereal_box_dimensions_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_find_cereal_box_dimensions_attachment(string $base): int
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

function custom_box_create_cereal_box_dimensions_attachment(string $base, int $post_id, array $image): int
{
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }

    foreach (array('webp', 'png', 'jpg', 'jpeg') as $extension) {
        $candidate_relative = '2026/08/' . $base . '.' . $extension;
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

function custom_box_cereal_box_dimensions_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_cereal_box_dimensions_post_data();
    $images = custom_box_cereal_box_dimensions_images();
    $failures = array();

    if (
        !$post
        || $data['slug'] !== $post->post_name
        || $data['title'] !== $post->post_title
        || $data['excerpt'] !== $post->post_excerpt
    ) {
        $failures[] = 'post identity, title or excerpt';
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
        4 !== substr_count($content, '<!-- cereal-box-dimensions-image:')
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
    if ((array) get_option('custom_box_cereal_box_dimensions_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_cereal_box_dimensions_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_cereal_box_dimensions_validation_failures', array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_cereal_box_dimensions_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_CEREAL_BOX_DIMENSIONS_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}

function custom_box_cereal_box_dimensions_content(): string
{
    $content = base64_decode('PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlR3byBjZXJlYWwgcHJvZHVjdHMgY2FuIGhhdmUgdGhlIHNhbWUgbmV0IHdlaWdodCBhbmQgc3RpbGwgcmVxdWlyZSBub3RpY2VhYmx5IGRpZmZlcmVudCBib3hlcy4gQSBkZW5zZSBncmFub2xhIHNldHRsZXMgaW50byBsZXNzIHNwYWNlIHRoYW4gbGlnaHR3ZWlnaHQgZmxha2VzIG9yIHB1ZmZlZCBjZXJlYWwuIFRoZSBpbm5lciBiYWcgbWF5IGFsc28gbmVlZCByb29tIGZvciBzZWFsaW5nLCBwcm9kdWN0IHNldHRsaW5nIGFuZCBwYWNraW5nLWxpbmUgaGFuZGxpbmcuIFRoaXMgaXMgd2h5IGNvcHlpbmcgYSBjb21wZXRpdG9y4oCZcyBjYXJ0b27igJRvciBzZWxlY3RpbmcgYSBib3ggb25seSBmcm9tIHRoZSBwcmludGVkIHdlaWdodOKAlGNhbiBwcm9kdWNlIGV4Y2Vzc2l2ZSBlbXB0eSBzcGFjZSwgY3J1c2hlZCBjZXJlYWwgb3IgYW4gaW5lZmZpY2llbnQgc2hpcHBpbmcgY2FzZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BcyBhIHByYWN0aWNhbCByZWZlcmVuY2UsIGEgcmVndWxhciByZXRhaWwgY2VyZWFsIGJveCBpcyBvZnRlbiBhcm91bmQgPHN0cm9uZz4xMiBpbmNoZXMgaGlnaCwgOCBpbmNoZXMgd2lkZSBhbmQgMiB0byAyLjUgaW5jaGVzIGRlZXA8L3N0cm9uZz4sIG9yIGFwcHJveGltYXRlbHkgPHN0cm9uZz4zMC41IMOXIDIwLjMgw5cgNS4x4oCTNi40IGNtPC9zdHJvbmc+LiBUaGF0IGlzIGEgdXNlZnVsIHN0YXJ0aW5nIHBvaW50LCBub3QgYSB1bml2ZXJzYWwgc3RhbmRhcmQuIFRoZSBwcm9kdWN0aW9uIHNpemUgc2hvdWxkIGJlIGJ1aWx0IGFyb3VuZCB0aGUgY2VyZWFsIHZvbHVtZSwgaW5uZXIgYmFnLCBjbG9zdXJlLCBzaGVsZiBwbGFuLCBib2FyZCBjb25zdHJ1Y3Rpb24gYW5kIHBhY2tpbmcgbWV0aG9kLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF8xIC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29tbW9uIENlcmVhbCBCb3ggRGltZW5zaW9ucyBhdCBhIEdsYW5jZTwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSByYW5nZXMgYmVsb3cgYXJlIHVzZWZ1bCBmb3IgZWFybHkgcGxhbm5pbmcsIGNvbXBldGl0b3IgY29tcGFyaXNvbiBhbmQgc2hlbGYgbW9ja3Vwcy4gVGhleSBzaG91bGQgbm90IGJlIHJlbGVhc2VkIGRpcmVjdGx5IGZvciBwcm9kdWN0aW9uIHdpdGhvdXQgYSBmaWxsZWQtcHJvZHVjdCB0ZXN0Ljwvc3Bhbj4NCjx0YWJsZT4NCjx0aGVhZD4NCjx0cj4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2FydG9uIGZvcm1hdDwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BcHByb3hpbWF0ZSBkaW1lbnNpb25zIGluIGluY2hlczwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BcHByb3hpbWF0ZSBkaW1lbnNpb25zIGluIGNlbnRpbWV0ZXJzPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlR5cGljYWwgcGxhbm5pbmcgdXNlPC9zcGFuPjwvdGg+DQo8L3RyPg0KPC90aGVhZD4NCjx0Ym9keT4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TWluaSBvciBzaW5nbGUtc2VydmUgY2FydG9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjbigJM4IEggw5cgNOKAkzUgVyDDlyAxLjXigJMyIEQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MTUuMuKAkzIwLjMgSCDDlyAxMC4y4oCTMTIuNyBXIMOXIDMuOOKAkzUuMSBEPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNhbXBsZXMsIGhvc3BpdGFsaXR5IHBhY2tzLCBwb3J0aW9uIHBhY2tzIG9yIHZhcmlldHkgc2V0czwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmVndWxhciByZXRhaWwgY2FydG9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEx4oCTMTIuNSBIIMOXIDfigJM4LjUgVyDDlyAy4oCTMyBEPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjI3LjnigJMzMS44IEggw5cgMTcuOOKAkzIxLjYgVyDDlyA1LjHigJM3LjYgRDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5FdmVyeWRheSBzdXBlcm1hcmtldCBjZXJlYWwgZm9ybWF0czwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RmFtaWx5LXNpemUgY2FydG9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjEyLjXigJMxNCBIIMOXIDjigJMxMCBXIMOXIDIuNeKAkzQgRDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij4zMS444oCTMzUuNiBIIMOXIDIwLjPigJMyNS40IFcgw5cgNi404oCTMTAuMiBEPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkxhcmdlciBmaWxsIHF1YW50aXRpZXMgYW5kIHZhbHVlIHBhY2tzPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5DbHViLCBtdWx0aXBhY2sgb3IgYnVsayBmb3JtYXQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHJvamVjdC1zcGVjaWZpYzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Qcm9qZWN0LXNwZWNpZmljPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk11bHRpcGxlIGlubmVyIGJhZ3MsIGxhcmdlLXZvbHVtZSBwYWNrcyBvciB3aG9sZXNhbGUgY2hhbm5lbHM8L3NwYW4+PC90ZD4NCjwvdHI+DQo8L3Rib2R5Pg0KPC90YWJsZT4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgcmFuZ2VzIG92ZXJsYXAgYmVjYXVzZSBuYW1lcyBzdWNoIGFzIOKAnHJlZ3VsYXIs4oCdIOKAnGxhcmdlLOKAnSDigJxmYW1pbHks4oCdIOKAnGdpYW504oCdIGFuZCDigJxtZWdh4oCdIGFyZSBtYXJrZXRpbmcgbGFiZWxzIHJhdGhlciB0aGFuIHJlbGlhYmxlIGRpbWVuc2lvbmFsIHNwZWNpZmljYXRpb25zLiBBIGZhbWlseS1zaXplIGNhcnRvbiBmcm9tIG9uZSBwcm9kdWN0IGxpbmUgbWF5IGJlIGNsb3NlIHRvIHRoZSByZWd1bGFyIGNhcnRvbiBvZiBhbm90aGVyLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZW4gcHJlcGFyaW5nIGFuIFJGUSwgZG8gbm90IHdyaXRlIG9ubHkg4oCcc3RhbmRhcmQgY2VyZWFsIGJveOKAnSBvciDigJxmYW1pbHktc2l6ZSBib3gu4oCdIFN0YXRlIGFsbCB0aHJlZSBkaW1lbnNpb25zLCB0aGUgbWVhc3VyZW1lbnQgb3JkZXIgYW5kIHdoZXRoZXIgdGhleSByZWZlciB0byB0aGUgaW5zaWRlIG9yIG91dHNpZGUgb2YgdGhlIGZpbmlzaGVkIGNhcnRvbi48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk1lYXN1cmUgSGVpZ2h0LCBGcm9udCBXaWR0aCBhbmQgRGVwdGggV2l0aG91dCBNaXhpbmcgdGhlIEF4ZXM8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5DZXJlYWwtYm94IGRpbWVuc2lvbnMgYXJlIGNvbW1vbmx5IGRlc2NyaWJlZCBhcyA8c3Ryb25nPmhlaWdodCDDlyBmcm9udCB3aWR0aCDDlyBkZXB0aDwvc3Ryb25nPiBiZWNhdXNlIHRoYXQgb3JkZXIgaXMgaW50dWl0aXZlIGZvciByZXRhaWwgZGlzcGxheS4gUGFja2FnaW5nIHN1cHBsaWVycyBtYXkgaW5zdGVhZCB1c2UgYSB0ZWNobmljYWwgb3JkZXIgc3VjaCBhcyBsZW5ndGggw5cgd2lkdGggw5cgaGVpZ2h0LiBOZWl0aGVyIGNvbnZlbnRpb24gaXMgdXNlZnVsIHVubGVzcyBlYWNoIGF4aXMgaXMgaWRlbnRpZmllZCBjbGVhcmx5Ljwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkhlaWdodDo8L3N0cm9uZz4gdGhlIGZpbmlzaGVkIGRpc3RhbmNlIGZyb20gdGhlIGJvdHRvbSBjbG9zdXJlIHRvIHRoZSB0b3AgY2xvc3VyZS48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkZyb250IHdpZHRoOjwvc3Ryb25nPiB0aGUgaG9yaXpvbnRhbCB3aWR0aCBvZiB0aGUgbWFpbiBkaXNwbGF5IHBhbmVsLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+RGVwdGg6PC9zdHJvbmc+IHRoZSBkaXN0YW5jZSBmcm9tIHRoZSBmcm9udCBwYW5lbCB0byB0aGUgYmFjayBwYW5lbC48L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TWVhc3VyZSB0aGUgZXJlY3RlZCBjYXJ0b24gb24gYSBmbGF0IHN1cmZhY2UuIEtlZXAgdGhlIHJ1bGVyIG9yIGNhbGlwZXIgcGVycGVuZGljdWxhciB0byB0aGUgcGFuZWwgYW5kIG1lYXN1cmUgYmV0d2VlbiB0aGUgZmluaXNoZWQgZWRnZXMgcmF0aGVyIHRoYW4gZm9sbG93aW5nIGEgYm93ZWQgc3VyZmFjZS4gSWYgdGhlIHNhbXBsZSBpcyBkaXN0b3J0ZWQsIGZpcnN0IHNxdWFyZSB0aGUgY2FydG9uIHdpdGhvdXQgZm9yY2luZyBpdCBiZXlvbmQgaXRzIG5hdHVyYWwgYXNzZW1ibGVkIHNoYXBlLjwvc3Bhbj4NCjxoMz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RG8gbm90IHRyZWF0IGEgZmxhdCBkaWVsaW5lIGFzIHRoZSBmaW5pc2hlZCBib3ggc2l6ZTwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBjdXR0aW5nIGZpbGUgaW5jbHVkZXMgdGhlIG1haW4gcGFuZWxzLCBzaWRlIHBhbmVscywgZ2x1ZSBmbGFwLCB0b3AgYW5kIGJvdHRvbSBjbG9zdXJlcywgZHVzdCBmbGFwcywgYmxlZWQgYW5kIHByb2R1Y3Rpb24gbWFya3MuIEl0cyB0b3RhbCBmbGF0IHdpZHRoIGFuZCBoZWlnaHQgYXJlIHRoZXJlZm9yZSBtdWNoIGxhcmdlciB0aGFuIHRoZSBlcmVjdGVkIGNhcnRvbiBkaW1lbnNpb25zLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgYnJpZWYgdGhhdCBzYXlzIOKAnHRoZSBhcnR3b3JrIGZpbGUgaXMgNTQwIMOXIDQyMCBtbeKAnSBkb2VzIG5vdCB0ZWxsIHRoZSBtYW51ZmFjdHVyZXIgdGhlIHJlcXVpcmVkIGJveCBzaXplLiBUaGUgc3BlY2lmaWNhdGlvbiBzaG91bGQgc2VwYXJhdGVseSBpZGVudGlmeTo8L3NwYW4+DQo8dWw+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+ZmluaXNoZWQgZXh0ZXJuYWwgZGltZW5zaW9uczs8L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5yZXF1aXJlZCBpbnRlcm5hbCBjbGVhcmFuY2U7PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+bWFudWZhY3R1cmluZyBkaW1lbnNpb25zIGJldHdlZW4gY3JlYXNlIGxpbmVzOzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPmRpZWxpbmUgb3IgYmxhbmsgZGltZW5zaW9uczs8L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5hcnR3b3JrIGJsZWVkIGFuZCBzYWZlIGFyZWFzLjwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48IS0tIElNQUdFX1NMT1RfMiAtLT48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkV4dGVybmFsLCBJbnRlcm5hbCBhbmQgTWFudWZhY3R1cmluZyBEaW1lbnNpb25zIEFyZSBOb3QgSW50ZXJjaGFuZ2VhYmxlPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBjYXJ0b24gY2FuIGhhdmUgbW9yZSB0aGFuIG9uZSBjb3JyZWN0IHNldCBvZiBkaW1lbnNpb25zLCBkZXBlbmRpbmcgb24gd2hhdCBpcyBiZWluZyBtZWFzdXJlZC48L3NwYW4+DQo8dGFibGU+DQo8dGhlYWQ+DQo8dHI+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRpbWVuc2lvbiB0eXBlPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoYXQgaXQgZGVzY3JpYmVzPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZW4gaXQgc2hvdWxkIGJlIHVzZWQ8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmlzayBpZiBtaXN1bmRlcnN0b29kPC9zcGFuPjwvdGg+DQo8L3RyPg0KPC90aGVhZD4NCjx0Ym9keT4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RXh0ZXJuYWwgZGltZW5zaW9uczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgb3ZlcmFsbCBzaXplIG9mIHRoZSBlcmVjdGVkIGNhcnRvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5SZXRhaWwgc2hlbGYgcGxhbm5pbmcsIG1hc3Rlci1jYXJ0b24gbGF5b3V0LCBzdG9yYWdlIGFuZCBwYWxsZXQgY2FsY3VsYXRpb25zPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBjYXJ0b24gZml0cyB0aGUgcHJvZHVjdCBidXQgZXhjZWVkcyB0aGUgc2hlbGYgb3Igc2hpcHBpbmctY2FzZSBsaW1pdDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SW50ZXJuYWwgZGltZW5zaW9uczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgdXNhYmxlIHNwYWNlIGF2YWlsYWJsZSBpbnNpZGUgdGhlIGVyZWN0ZWQgY2FydG9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPklubmVyLWJhZyBmaXQsIHByb2R1Y3QgY2xlYXJhbmNlIGFuZCBmaWxsaW5nIHRyaWFsczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgb3V0c2lkZSBhcHBlYXJzIGNvcnJlY3QsIGJ1dCB0aGUgZmlsbGVkIGJhZyBpcyBzcXVlZXplZCBvciBkaWZmaWN1bHQgdG8gaW5zZXJ0PC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NYW51ZmFjdHVyaW5nIGRpbWVuc2lvbnM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFuZWwgZGltZW5zaW9ucyBkZWZpbmVkIGJldHdlZW4gY3JlYXNlIG9yIHNjb3JlIGxpbmVzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRpZWxpbmUgZGV2ZWxvcG1lbnQsIGRpZS1jdXR0aW5nLCBmb2xkaW5nIGFuZCBwcm9kdWN0aW9uIGNvbnRyb2w8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGRlc2lnbmVyIG1vZGlmaWVzIGEgZmluaXNoZWQgZGltZW5zaW9uIHdpdGhvdXQgdXBkYXRpbmcgY3JlYXNlIGNvbXBlbnNhdGlvbjwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RmxhdCBibGFuayBkaW1lbnNpb25zPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBjb21wbGV0ZSB0d28tZGltZW5zaW9uYWwgY3V0IHNoYXBlIGJlZm9yZSBmb2xkaW5nPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNoZWV0IHBsYW5uaW5nLCB0b29saW5nLCBwcmludGluZyBsYXlvdXQgYW5kIG1hdGVyaWFsIGVzdGltYXRpb248L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Qmxhbmsgc2l6ZSBpcyBtaXN0YWtlbiBmb3IgdGhlIGZpbmlzaGVkIGNhcnRvbiBmb290cHJpbnQ8L3NwYW4+PC90ZD4NCjwvdHI+DQo8L3Rib2R5Pg0KPC90YWJsZT4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5QYXBlcmJvYXJkIGNhbGlwZXIsIGNyZWFzZSBkZXNpZ24sIGZvbGRpbmcgZGlyZWN0aW9uIGFuZCBnbHVlLXNlYW0gY29uc3RydWN0aW9uIGNyZWF0ZSBkaWZmZXJlbmNlcyBiZXR3ZWVuIG1hbnVmYWN0dXJpbmcsIGludGVybmFsIGFuZCBleHRlcm5hbCBkaW1lbnNpb25zLiBUaGUgc3VwcGxpZXIgc2hvdWxkIHRoZXJlZm9yZSBjb252ZXJ0IHRoZSBhcHByb3ZlZCBmaW5pc2hlZC1ib3ggcmVxdWlyZW1lbnQgaW50byBhIHByb2R1Y3Rpb24gZGllbGluZSBpbnN0ZWFkIG9mIGFza2luZyB0aGUgYXJ0d29yayBkZXNpZ25lciB0byBndWVzcyB0aGUgY3JlYXNlIHBvc2l0aW9ucy48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgMTQtT3VuY2UgR3Jhbm9sYSBCb3ggU2hvdWxkIE5vdCBJbmhlcml0IGEgMTQtT3VuY2UgRmxha2UgQm94PC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TmV0IHdlaWdodCBhbG9uZSBpcyBhIHdlYWsgc2l6aW5nIGlucHV0LiBUaGUgaW1wb3J0YW50IHZhcmlhYmxlIGlzIHRoZSA8c3Ryb25nPnBhY2tlZCB2b2x1bWUgdW5kZXIgdGhlIGFjdHVhbCBmaWxsaW5nIGNvbmRpdGlvbjwvc3Ryb25nPi48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db25zaWRlciB0d28gcHJvZHVjdHMgd2l0aCB0aGUgc2FtZSBwcmludGVkIHdlaWdodDo8L3NwYW4+DQo8dWw+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBjb21wYWN0IGdyYW5vbGEgd2l0aCBudXRzIG1heSBzZXR0bGUgaW50byBhIHJlbGF0aXZlbHkgc2hvcnQsIGRlbnNlIGlubmVyIGJhZy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5MYXJnZSBjb3JuIGZsYWtlcyBvciBwdWZmZWQgY2VyZWFsIG1heSBvY2N1cHkgc3Vic3RhbnRpYWxseSBtb3JlIHZvbHVtZSBhbmQgdHJhcCBtb3JlIGFpciBiZXR3ZWVuIHBpZWNlcy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIGZyYWdpbGUgY2VyZWFsIG1heSBuZWVkIGEgbG93ZXIgZmlsbCBwcmVzc3VyZSB0byByZWR1Y2UgYnJlYWthZ2UgZHVyaW5nIGJhZyBpbnNlcnRpb24gYW5kIGNhcnRvbiBjbG9zaW5nLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFuIGlycmVndWxhciBwcm9kdWN0IG1heSBjcmVhdGUgbG9jYWwgYnVsZ2VzIHRoYXQgYXJlIG5vdCB2aXNpYmxlIGluIGEgc2ltcGxlIHZvbHVtZSBjYWxjdWxhdGlvbi48L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RG8gbm90IGVzdGltYXRlIHRoZSBjYXJ0b24gZnJvbSB0aGVvcmV0aWNhbCBjdWJpYyB2b2x1bWUgYWxvbmUuIEZpbGwgdGhlIGludGVuZGVkIGlubmVyIGJhZyB1c2luZyB0aGUgcmVhbCBwcm9kdWN0LCBzZWFsIGl0IHVzaW5nIHRoZSBwbGFubmVkIGhlYWRzcGFjZSBhbmQgYWxsb3cgaXQgdG8gc2V0dGxlIHVuZGVyIGEgZGVmaW5lZCBjb25kaXRpb24uIE1lYXN1cmUgdGhlIGZpbGxlZCBiYWcgYXQgaXRzIHdpZGVzdCwgZGVlcGVzdCBhbmQgdGFsbGVzdCBzdGFibGUgcG9pbnRzLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoaXMgcHJvZHVjdC1maXJzdCBhcHByb2FjaCBpcyBhbHNvIGltcG9ydGFudCB3aGVuIHNlbGVjdGluZyA8YSBocmVmPSJodHRwczovL2hvcGdpYXl2cG4uY29tL3doYXQtdG8tY29uc2lkZXItZm9vZC1wYXBlci1wYWNrYWdpbmcvIj5wYXBlciBwYWNrYWdpbmcgZm9yIGZvb2QgcHJvZHVjdHM8L2E+LiBUaGUgcGFwZXIgY2FydG9uIGlzIHVzdWFsbHkgdGhlIHByaW50ZWQgb3V0ZXIgc3RydWN0dXJlLCB3aGlsZSBhIHNlYWxlZCBpbm5lciBiYWcgcHJvdmlkZXMgdGhlIG1haW4gcHJvZHVjdCBiYXJyaWVyLiBUaG9zZSB0d28gcGFja2FnaW5nIGxheWVycyBtdXN0IGJlIGRlc2lnbmVkIHRvZ2V0aGVyLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF8zIC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QnVpbGQgdGhlIENlcmVhbCBDYXJ0b24gRnJvbSB0aGUgSW5uZXIgQmFnIE91dHdhcmQ8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHJlbGlhYmxlIGN1c3RvbS1zaXplIHdvcmtmbG93IGJlZ2lucyB3aXRoIHRoZSBmaWxsZWQgcHJpbWFyeSBwYWNrLCBub3QgYSBzdG9jayBjYXJ0b24gY2F0YWxvZ3VlLjwvc3Bhbj4NCjxoMz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MS4gQ29uZmlybSB0aGUgcGFja2VkIHByb2R1Y3Qgdm9sdW1lPC9zcGFuPjwvaDM+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VXNlIHByb2R1Y3Rpb24tcmVwcmVzZW50YXRpdmUgY2VyZWFsIHJhdGhlciB0aGFuIGFuIGVtcHR5IGJhZyBvciBzdWJzdGl0dXRlIG1hdGVyaWFsLiBSZWNvcmQgbmV0IHdlaWdodCwgc2V0dGxpbmcgdGltZSBhbmQgd2hldGhlciB0aGUgcHJvZHVjdCBpcyBmaWxsZWQgYnkgd2VpZ2h0LCB2b2x1bWUgb3IgYSBjb21iaW5hdGlvbiBvZiBib3RoLjwvc3Bhbj4NCjxoMz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Mi4gU2VhbCB0aGUgaW5uZXIgYmFnIGFzIGl0IHdpbGwgYmUgc2VhbGVkIGluIHByb2R1Y3Rpb248L3NwYW4+PC9oMz4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JbmNsdWRlIHRoZSByZWFsIHRvcC1zZWFsIGFsbG93YW5jZSwgaGVhZHNwYWNlIGFuZCBtYXRlcmlhbCB0aGlja25lc3MuIEFuIHVuc2VhbGVkIGJhZyBjYW4gc3ByZWFkIGludG8gYSBzaGFwZSB0aGF0IHdpbGwgbmV2ZXIgb2NjdXIgb24gdGhlIHBhY2tpbmcgbGluZS48L3NwYW4+DQo8aDM+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjMuIERlZmluZSBmdW5jdGlvbmFsIGNsZWFyYW5jZTwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBjYXJ0b24gc2hvdWxkIG5vdCBzcXVlZXplIHRoZSBzZWFsZWQgYmFnLCBidXQgZXhjZXNzaXZlIGNsZWFyYW5jZSBhbGxvd3MgdGhlIGJhZyB0byBzbHVtcCwgd3JpbmtsZSBvciBtb3ZlLiBDbGVhcmFuY2Ugc2hvdWxkIGJlIGV2YWx1YXRlZCBhdCB0aGUgd2lkZXN0IGZpbGxlZC1iYWcgcG9pbnRzIGFuZCBhcm91bmQgdGhlIHRvcCBzZWFsLCBub3QgYWRkZWQgYXMgb25lIGFyYml0cmFyeSBudW1iZXIgdG8gZXZlcnkgcGFuZWwuPC9zcGFuPg0KPGgzPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij40LiBTZWxlY3QgdGhlIGNsb3N1cmUgYW5kIGdsdWUtc2VhbSBzdHJ1Y3R1cmU8L3NwYW4+PC9oMz4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHN0cmFpZ2h0LXR1Y2sgZW5kLCByZXZlcnNlLXR1Y2sgZW5kLCBnbHVlZCBlbmQgb3IgYXV0b21hdGljLWJvdHRvbSBzdHJ1Y3R1cmUgZG9lcyBub3QgdXNlIHRoZSBzYW1lIGZsYXAgZ2VvbWV0cnkuIFRoZSBmaWxsaW5nIGRpcmVjdGlvbiBhbmQgcGFja2luZyBzcGVlZCBhbHNvIGFmZmVjdCB0aGUgc3VpdGFibGUgY2xvc3VyZS48L3NwYW4+DQo8aDM+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjUuIENob29zZSBwYXBlcmJvYXJkIGZyb20gdGhlIGZpbmlzaGVkIGRpbWVuc2lvbnM8L3NwYW4+PC9oMz4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHRhbGxlciBvciB3aWRlciBjYXJ0b24gbWF5IHJlcXVpcmUgbW9yZSBzdGlmZm5lc3MgdG8ga2VlcCB0aGUgZnJvbnQgcGFuZWwgZmxhdCBhbmQgdGhlIGNvcm5lcnMgc3F1YXJlLiBTaW1wbHkgZW5sYXJnaW5nIGEgc21hbGwtYm94IGRpZWxpbmUgd2l0aG91dCByZXZpZXdpbmcgYm9hcmQgYmVoYXZpb3IgY2FuIGNyZWF0ZSBib3dpbmcsIHdlYWsgdG9wIHBhbmVscyBvciB1bnN0YWJsZSBzdGFja2luZy48L3NwYW4+DQo8aDM+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjYuIFRlc3QgdGhlIGNhcnRvbiBpbnNpZGUgdGhlIG1hc3RlciBjYXNlPC9zcGFuPjwvaDM+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RXh0ZXJuYWwgZGltZW5zaW9ucyBhZmZlY3QgaG93IG1hbnkgY2VyZWFsIGJveGVzIGZpdCBwZXIgcm93LCBsYXllciBhbmQgc2hpcHBpbmcgY2FzZS4gQSBkZXB0aCBpbmNyZWFzZSBvZiBvbmx5IGEgZmV3IG1pbGxpbWV0ZXJzIGNhbiByZWR1Y2UgY2FzZSBjb3VudCBvciBjcmVhdGUgdW51c2VkIHNwYWNlIGFjcm9zcyBhIGZ1bGwgcGFsbGV0IHBhdHRlcm4uPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Ub2xlcmFuY2UgQmVsb25ncyBpbiB0aGUgRHJhd2luZywgTm90IGluIGFuIEVtYWlsIENvbW1lbnQ8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5QYXBlcmJvYXJkIGNhcnRvbnMgYXJlIGNvbnZlcnRlZCBmcm9tIGZsZXhpYmxlIHNoZWV0IG1hdGVyaWFsLiBQcmludGluZywgZGllLWN1dHRpbmcsIGNyZWFzaW5nLCBnbHVpbmcsIG1vaXN0dXJlIGNvbmRpdGlvbiBhbmQgZXJlY3Rpb24gYWxsIGludHJvZHVjZSBub3JtYWwgdmFyaWF0aW9uLiBUaGUgY29ycmVjdCByZXNwb25zZSBpcyBub3QgdG8gZGVtYW5kIOKAnHplcm8gdG9sZXJhbmNlLOKAnSBidXQgdG8gaWRlbnRpZnkgd2hpY2ggZGltZW5zaW9ucyBhcmUgY3JpdGljYWwgYW5kIGFncmVlIG9uIG1lYXN1cmFibGUgbGltaXRzIGJlZm9yZSBwcm9kdWN0aW9uLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgZGltZW5zaW9uYWwgc3BlY2lmaWNhdGlvbiBzaG91bGQgc3RhdGU6PC9zcGFuPg0KPHVsPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPnRoZSBub21pbmFsIGZpbmlzaGVkIHNpemU7PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+d2hldGhlciB0aGUgbm9taW5hbCB2YWx1ZSBpcyBpbnRlcm5hbCwgZXh0ZXJuYWwgb3IgbWFudWZhY3R1cmluZyBzaXplOzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPnRoZSB0b2xlcmFuY2UgZm9yIGVhY2ggY29udHJvbGxlZCBkaW1lbnNpb247PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+dGhlIG1lYXN1cmluZyBtZXRob2QgYW5kIHNhbXBsZSBjb25kaXRpb247PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+dGhlIG51bWJlciBhbmQgbG9jYXRpb25zIG9mIHNhbXBsZXM7PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+dGhlIGFwcHJvdmVkIHBoeXNpY2FsIHNhbXBsZSBvciBkcmF3aW5nIHJldmlzaW9uOzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPnRoZSBhY3Rpb24gcmVxdWlyZWQgd2hlbiBhIHJlc3VsdCBpcyBvdXRzaWRlIHRoZSBsaW1pdC48L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9yIGV4YW1wbGUsIGEgcHJvamVjdCBkcmF3aW5nIG1pZ2h0IHN0YXRlOjwvc3Bhbj4NCjx0YWJsZT4NCjx0aGVhZD4NCjx0cj4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29udHJvbCBpdGVtPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPklsbHVzdHJhdGl2ZSBzcGVjaWZpY2F0aW9uIGZvcm1hdDwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaHkgaXQgbWF0dGVyczwvc3Bhbj48L3RoPg0KPC90cj4NCjwvdGhlYWQ+DQo8dGJvZHk+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkZpbmlzaGVkIGZyb250IHdpZHRoPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjIwMyBtbSwgZXh0ZXJuYWwsIGFncmVlZCBwcm9qZWN0IHRvbGVyYW5jZTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db250cm9scyBzaGVsZiBmYWNpbmdzIGFuZCBtYXN0ZXItY2FzZSBwYWNraW5nPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5GaW5pc2hlZCBkZXB0aDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij42NCBtbSwgZXh0ZXJuYWwsIGFncmVlZCBwcm9qZWN0IHRvbGVyYW5jZTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db250cm9scyBiYWcgY29tcHJlc3Npb24gYW5kIHRoZSBudW1iZXIgb2YgY2FydG9ucyBwZXIgY2FzZTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RmluaXNoZWQgaGVpZ2h0PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjMwNSBtbSwgZXh0ZXJuYWwsIGFncmVlZCBwcm9qZWN0IHRvbGVyYW5jZTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db250cm9scyBzaGVsZiBjbGVhcmFuY2UgYW5kIHRvcC1jbG9zdXJlIGZ1bmN0aW9uPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5HbHVlIHNlYW08L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UG9zaXRpb24gYW5kIGJvbmQgYXJlYSBzaG93biBvbiBhcHByb3ZlZCBkcmF3aW5nPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNvbnRyb2xzIGNhcnRvbiBzcXVhcmVuZXNzIGFuZCBwYW5lbCBhbGlnbm1lbnQ8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRvcCBhbmQgYm90dG9tIGNsb3N1cmU8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2xvc2VzIGZ1bGx5IHdpdGhvdXQgcGFuZWwgYnVja2xpbmcgb3IgYmFnIGludGVyZmVyZW5jZTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db250cm9scyBwYWNraW5nLWxpbmUgZnVuY3Rpb24gYW5kIGZpbmlzaGVkIGFwcGVhcmFuY2U8L3NwYW4+PC90ZD4NCjwvdHI+DQo8L3Rib2R5Pg0KPC90YWJsZT4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgbnVtYmVycyBhYm92ZSBpbGx1c3RyYXRlIGhvdyB0byB3cml0ZSBhIHNwZWNpZmljYXRpb247IHRoZXkgYXJlIG5vdCB1bml2ZXJzYWwgY2VyZWFsLWNhcnRvbiB0b2xlcmFuY2VzLiBUaGUgbWFudWZhY3R1cmVyIHNob3VsZCBjb25maXJtIGFjaGlldmFibGUgbGltaXRzIGJhc2VkIG9uIHRoZSBib3ggc2l6ZSwgcGFwZXJib2FyZCwgdG9vbGluZywgY29udmVydGluZyBwcm9jZXNzIGFuZCBwYWNraW5nIHJlcXVpcmVtZW50cy48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EaW1lbnNpb25hbCBpbnNwZWN0aW9uIHNob3VsZCBhbHNvIGJlIGNvbm5lY3RlZCB0byB0aGUgd2lkZXIgPGEgaHJlZj0iaHR0cHM6Ly9ob3BnaWF5dnBuLmNvbS9wYXBlci1ib3gtcXVhbGl0eS1jb250cm9sLWNoZWNrbGlzdC8iPnBhcGVyIGJveCBxdWFsaXR5IGNvbnRyb2wgY2hlY2tsaXN0PC9hPi4gQSBjYXJ0b24gY2FuIG1lZXQgaXRzIG5vbWluYWwgd2lkdGggYW5kIHN0aWxsIGZhaWwgYmVjYXVzZSB0aGUgZ2x1ZSBzZWFtIGlzIHNrZXdlZCwgdGhlIHRvcCBmbGFwIGludGVyZmVyZXMgd2l0aCB0aGUgYmFnIG9yIHRoZSBlcmVjdGVkIGJveCBkb2VzIG5vdCByZW1haW4gc3F1YXJlLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF80IC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2hlbGYgUHJlc2VuY2UgQ2FuIENvbmZsaWN0IFdpdGggUHJvZHVjdCBhbmQgTG9naXN0aWNzIEVmZmljaWVuY3k8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIGxhcmdlIGZyb250IHBhbmVsIGNyZWF0ZXMgbW9yZSByb29tIGZvciBwcm9kdWN0IHBob3RvZ3JhcGh5LCBicmFuZCBhc3NldHMgYW5kIG51dHJpdGlvbiBjb21tdW5pY2F0aW9uLiBJdCBtYXkgYWxzbyBpbmNyZWFzZSBwYXBlciB1c2UsIGVtcHR5IHZvbHVtZSBhbmQgdGhlIHNpemUgb2YgdGhlIG1hc3RlciBjYXJ0b24uPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmV2aWV3IHRoZSBmaW5hbCBkaW1lbnNpb25zIGFnYWluc3QgZm91ciBkaWZmZXJlbnQgc3lzdGVtczo8L3NwYW4+DQo8dGFibGU+DQo8dGhlYWQ+DQo8dHI+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlN5c3RlbTwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5RdWVzdGlvbiB0byBhbnN3ZXI8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UG9zc2libGUgc2l6aW5nIGNvbmZsaWN0PC9zcGFuPjwvdGg+DQo8L3RyPg0KPC90aGVhZD4NCjx0Ym9keT4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHJvZHVjdCBhbmQgaW5uZXIgYmFnPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNhbiB0aGUgZmlsbGVkIGJhZyBlbnRlciBhbmQgc2V0dGxlIHdpdGhvdXQgY3J1c2hpbmcgb3IgZXhjZXNzaXZlIG1vdmVtZW50Pzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHNsaW0gc2hlbGYgcHJvZmlsZSBtYXkgY29tcHJlc3MgYSBidWxreSBjZXJlYWwgYmFnPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5SZXRhaWwgc2hlbGY8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RG9lcyB0aGUgaGVpZ2h0IGZpdCB0aGUgcGxhbm9ncmFtIGFuZCBkb2VzIHRoZSBmcm9udCBwYW5lbCByZW1haW4gcmVhZGFibGU/PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgdGFsbGVyIGNhcnRvbiBnYWlucyBkaXNwbGF5IGFyZWEgYnV0IG1heSBleGNlZWQgc2hlbGYgY2xlYXJhbmNlPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5QYWNraW5nIGxpbmU8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2FuIHRoZSBjYXJ0b24gYmUgZXJlY3RlZCwgZmlsbGVkIGFuZCBjbG9zZWQgY29uc2lzdGVudGx5Pzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHZlcnkgdGlnaHQgY2FydG9uIG1heSB3b3JrIGJ5IGhhbmQgYnV0IGphbSBkdXJpbmcgYXV0b21hdGVkIHBhY2tpbmc8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk1hc3RlciBjYXJ0b24gYW5kIHBhbGxldDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Ib3cgbWFueSBmaW5pc2hlZCB1bml0cyBmaXQgcGVyIHJvdywgY2FzZSBhbmQgcGFsbGV0IGxheWVyPzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHNtYWxsIGRlcHRoIGNoYW5nZSBtYXkgcmVkdWNlIHNoaXBwaW5nLWNhc2UgZWZmaWNpZW5jeTwvc3Bhbj48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgZ29vZCBjZXJlYWwgYm94IGlzIG5vdCB0aGUgc21hbGxlc3QgcG9zc2libGUgY2FydG9uLiBJdCBpcyB0aGUgc21hbGxlc3QgcmVwZWF0YWJsZSBjYXJ0b24gdGhhdCBwcm90ZWN0cyB0aGUgZmlsbGVkIGJhZywgcnVucyByZWxpYWJseSwgcHJlc2VudHMgdGhlIHByb2R1Y3QgY2xlYXJseSBhbmQgdXNlcyBkaXN0cmlidXRpb24gc3BhY2UgZWZmaWNpZW50bHkuPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BcHByb3ZlIERpbWVuc2lvbnMgV2l0aCBGaWxsZWQgU2FtcGxlcywgTm90IEVtcHR5IENhcnRvbnM8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BbiBlbXB0eSBmb2xkaW5nIGNhcnRvbiBjYW4gYXBwZWFyIHNxdWFyZSBhbmQgd2VsbCBwcm9wb3J0aW9uZWQgd2hpbGUgaGlkaW5nIGEgc2VyaW91cyBmaXQgcHJvYmxlbS4gRmluYWwgc2l6ZSBhcHByb3ZhbCBzaG91bGQgdXNlIHJlcHJlc2VudGF0aXZlIGZpbGxlZCBiYWdzIGFuZCB0aGUgaW50ZW5kZWQgcGFja2luZyBzZXF1ZW5jZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EdXJpbmcgc2FtcGxlIGFwcHJvdmFsLCBjaGVjayB0aGUgZm9sbG93aW5nOjwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgZmlsbGVkIGJhZyBlbnRlcnMgd2l0aG91dCBleGNlc3NpdmUgZm9yY2UuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIHRvcCBzZWFsIGRvZXMgbm90IGZvbGQgaW50byBvciBvYnN0cnVjdCB0aGUgY2FydG9uIGNsb3N1cmUuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGJyb2FkIGZyb250IGFuZCBiYWNrIHBhbmVscyBkbyBub3QgYnVsZ2UgZXhjZXNzaXZlbHkuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGNlcmVhbCBpcyBub3QgY29tcHJlc3NlZCB3aGVuIHRoZSBjYXJ0b24gaXMgY2xvc2VkLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBwYWNrYWdlIHJlbWFpbnMgdXByaWdodCBvbiBhIGZsYXQgc2hlbGYuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIHR1Y2sgZmxhcCBvciBnbHVlZCBjbG9zdXJlIHJlbWFpbnMgc2VjdXJlLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByaW50ZWQgZ3JhcGhpY3MgYWxpZ24gY29ycmVjdGx5IGFmdGVyIGVyZWN0aW9uLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkJhcmNvZGUsIGluZ3JlZGllbnRzIGFuZCBudXRyaXRpb24gcGFuZWxzIHJlbWFpbiByZWFkYWJsZS48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgcmVxdWlyZWQgbnVtYmVyIG9mIHVuaXRzIGZpdHMgdGhlIGFwcHJvdmVkIG1hc3RlciBjYXJ0b24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmVwZWF0ZWQgc2FtcGxlcyByZW1haW4gd2l0aGluIHRoZSBhZ3JlZWQgZGltZW5zaW9uYWwgbGltaXRzLjwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5PbmUgcHJvdG90eXBlIGlzIG5vdCBlbm91Z2ggdG8gZXN0YWJsaXNoIHByb2R1Y3Rpb24gc3RhYmlsaXR5LiBBZnRlciB0aGUgaW5pdGlhbCBzdHJ1Y3R1cmFsIHNhbXBsZSBwYXNzZXMsIGFwcHJvdmUgYSBwcmludGVkIG9yIHByb2R1Y3Rpb24tcmVwcmVzZW50YXRpdmUgc2FtcGxlIHRoYXQgdXNlcyB0aGUgaW50ZW5kZWQgYm9hcmQsIGNyZWFzZSwgZ2x1ZSBzZWFtIGFuZCBzdXJmYWNlIGZpbmlzaC48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoYXQgdG8gU2VuZCBCZWZvcmUgUmVxdWVzdGluZyBhIEN1c3RvbSBDZXJlYWwgQm94PC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RG8gbm90IHNlbmQgb25seSBhIHRhcmdldCB3ZWlnaHQgYW5kIGEgcmVmZXJlbmNlIHBob3RvLiBBIHVzZWZ1bCBjZXJlYWwtcGFja2FnaW5nIGJyaWVmIHNob3VsZCBjb250YWluOjwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5jZXJlYWwgdHlwZSBhbmQgbmV0IGZpbGwgd2VpZ2h0Ozwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPmZpbGxlZCBhbmQgc2VhbGVkIGlubmVyLWJhZyBkaW1lbnNpb25zOzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPmlubmVyLWJhZyBtYXRlcmlhbCBhbmQgdG9wLXNlYWwgYWxsb3dhbmNlOzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPnRhcmdldCBleHRlcm5hbCBvciBpbnRlcm5hbCBjYXJ0b24gZGltZW5zaW9ucywgaWYgYWxyZWFkeSBkZWZpbmVkOzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPnJldGFpbCBzaGVsZiBvciBwbGFub2dyYW0gcmVzdHJpY3Rpb25zOzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPnBhY2tpbmctbGluZSBkaXJlY3Rpb24gYW5kIGNsb3N1cmUgcHJlZmVyZW5jZTs8L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5wYXBlcmJvYXJkIGFuZCBwcmludGluZyByZXF1aXJlbWVudHM7PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+YmFyY29kZSwgbnV0cml0aW9uIHBhbmVsIGFuZCByZWd1bGF0b3J5LWNvcHkgc3BhY2U7PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+dW5pdHMgcGVyIG1hc3RlciBjYXJ0b24gYW5kIHByZWZlcnJlZCBjYXNlIGRpbWVuc2lvbnM7PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+c2FtcGxlIHF1YW50aXR5IGFuZCBhY2NlcHRhbmNlIGNyaXRlcmlhLjwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgcHJhY3RpY2FsIGFuc3dlciB0byDigJx3aGF0IGFyZSB0aGUgZGltZW5zaW9ucyBvZiBhIGNlcmVhbCBib3g/4oCdIGlzIHRoZXJlZm9yZSBhIHJhbmdlLCBub3Qgb25lIGZpeGVkIG51bWJlci4gVXNlIGFwcHJveGltYXRlbHkgMTIgw5cgOCDDlyAy4oCTMi41IGluY2hlcyBhcyBhbiBlYXJseSByZWZlcmVuY2UgZm9yIGEgcmVndWxhciByZXRhaWwgY2FydG9uLCB0aGVuIHJlcGxhY2UgdGhhdCBhc3N1bXB0aW9uIHdpdGggbWVhc3VyZW1lbnRzIGZyb20gdGhlIGZpbGxlZCBpbm5lciBiYWcsIHJldGFpbCBwbGFuLCBwcm9kdWN0aW9uIHByb2Nlc3MgYW5kIHNoaXBwaW5nIGNvbmZpZ3VyYXRpb24uPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hlbiB0aG9zZSBpbnB1dHMgYXJlIHJlYWR5LCBzZW5kIHRoZSBmaWxsZWQtcHJvZHVjdCBkaW1lbnNpb25zLCBiYWcgZGV0YWlscywgdGFyZ2V0IG1hcmtldCBhbmQgY2FzZS1wYWNraW5nIHJlcXVpcmVtZW50IHRvIGEgPGEgaHJlZj0iaHR0cHM6Ly9ob3BnaWF5dnBuLmNvbS9jdXN0b20tcGFja2FnaW5nLWJveGVzLW1hbnVmYWN0dXJlci8iPmN1c3RvbSBwYWNrYWdpbmcgYm94IG1hbnVmYWN0dXJlcjwvYT4gc28gdGhlIGZpbmlzaGVkIGRpbWVuc2lvbnMsIGRpZWxpbmUgYW5kIHRvbGVyYW5jZSBwbGFuIGNhbiBiZSBkZXZlbG9wZWQgYXJvdW5kIHRoZSByZWFsIHByb2R1Y3QgcmF0aGVyIHRoYW4gYSBnZW5lcmljIGNlcmVhbC1ib3ggdGVtcGxhdGUuPC9zcGFuPg==', true);

    return is_string($content) ? $content : '';
}
