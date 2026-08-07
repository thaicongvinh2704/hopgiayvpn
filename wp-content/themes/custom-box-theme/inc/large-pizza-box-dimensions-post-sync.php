<?php
/**
 * Deploys the large pizza box dimensions draft and its five local images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_SYNC_VERSION = '2026-08-07-v1';
const CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_VERSION_OPTION = 'custom_box_large_pizza_box_dimensions_sync_version';
const CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_NOTICE_OPTION = 'custom_box_large_pizza_box_dimensions_sync_notice';

add_action('admin_init', 'custom_box_sync_large_pizza_box_dimensions_post');
add_action('admin_notices', 'custom_box_large_pizza_box_dimensions_admin_notice');

function custom_box_sync_large_pizza_box_dimensions_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_large_pizza_box_dimensions_post_data();
    $post = custom_box_find_large_pizza_box_dimensions_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_SYNC_VERSION === get_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_VERSION_OPTION)
        && $post
        && custom_box_large_pizza_box_dimensions_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_large_pizza_box_dimensions_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_VERSION_OPTION);
        update_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_large_pizza_box_dimensions_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_VERSION_OPTION, CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Large pizza box dimensions draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Guides, 5 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_VERSION_OPTION);
    delete_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_NOTICE_OPTION);
    update_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Large pizza box dimensions sync is incomplete. Missing images: '
            . implode(', ', (array) get_option('custom_box_large_pizza_box_dimensions_missing_images', array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option('custom_box_large_pizza_box_dimensions_missing_slots', array()),
                (array) get_option('custom_box_large_pizza_box_dimensions_validation_failures', array())
            )),
    ), false);
}
function custom_box_large_pizza_box_dimensions_post_data(): array
{
    return array(
        'title' => 'What Are the Dimensions of a Large Pizza Box? Size and Board Guide',
        'slug' => 'large-pizza-box-dimensions',
        'excerpt' => 'Large pizza boxes commonly range from about 14 × 14 × 2 to 18 × 18 × 2 inches, but “large” is not a universal standard. Learn how to measure usable inside dimensions, define clearance and tolerance, and choose B- or E-flute board for a custom pizza box.',
        'category' => array(
            'name' => 'Packaging Guides',
            'slug' => 'packaging-guides',
        ),
        'tags' => array(
            'Pizza Box Dimensions' => 'pizza-box-dimensions',
            'Corrugated Pizza Box' => 'corrugated-pizza-box',
            'Custom Pizza Boxes' => 'custom-pizza-boxes',
            'Box Size Guide' => 'box-size-guide',
            'Packaging Specifications' => 'packaging-specifications',
        ),
        'seo_title' => 'Large Pizza Box Dimensions: Size, Fit & Board Guide',
        'seo_description' => 'Large pizza boxes are commonly 14–18 inches square. Learn how to measure inside dimensions, set clearance and tolerance, and choose B- or E-flute board.',
        'focus_keyword' => 'what are the dimensions of a large pizza box',
    );
}

function custom_box_large_pizza_box_dimensions_images(): array
{
    return array(
        'featured' => array(
            'base' => 'large-pizza-box-dimensions-size-board-guide',
            'alt' => 'Large pizza box dimensions with corrugated board and measurement tools',
            'title' => 'Large Pizza Box Dimensions Guide',
            'caption' => 'Large pizza box dimensions depend on the finished pizza, usable inside space, and corrugated board specification.',
        ),
        'slot_1' => array(
            'base' => 'large-pizza-box-size-comparison',
            'alt' => 'Comparison of common large pizza box sizes',
            'title' => 'Common Large Pizza Box Sizes',
            'caption' => '“Large” can refer to several different pizza-box footprints depending on the supplier and pizza diameter.',
        ),
        'slot_2' => array(
            'base' => 'measure-large-pizza-box-inside-dimensions',
            'alt' => 'Measuring the inside dimensions of a corrugated pizza box',
            'title' => 'How to Measure Pizza Box Inside Dimensions',
            'caption' => 'Product fit should be based on usable internal length, width, and height after assembly.',
        ),
        'slot_3' => array(
            'base' => 'pizza-box-clearance-tolerance-fit',
            'alt' => 'Pizza clearance inside a large corrugated pizza box',
            'title' => 'Pizza Box Clearance and Fit',
            'caption' => 'Small controlled clearance protects the crust while avoiding unnecessary movement inside the box.',
        ),
        'slot_4' => array(
            'base' => 'b-flute-vs-e-flute-pizza-box-board',
            'alt' => 'B-flute and E-flute corrugated board for pizza boxes',
            'title' => 'B-Flute vs E-Flute Pizza Box Board',
            'caption' => 'Corrugated flute choice changes board thickness, surface appearance, and structural behavior.',
        ),
    );
}

function custom_box_find_large_pizza_box_dimensions_post(string $slug, string $title): ?WP_Post
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

function custom_box_upsert_large_pizza_box_dimensions_post()
{
    $data = custom_box_large_pizza_box_dimensions_post_data();
    $post = custom_box_find_large_pizza_box_dimensions_post($data['slug'], $data['title']);
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
            $payload['post_content'] = custom_box_large_pizza_box_dimensions_content();
        }
        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = custom_box_large_pizza_box_dimensions_content();
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_large_pizza_box_dimensions_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_large_pizza_box_dimensions_images($post_id);

    return $post_id;
}

function custom_box_sync_large_pizza_box_dimensions_terms(int $post_id, array $data): void
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

function custom_box_sync_large_pizza_box_dimensions_images(int $post_id): void
{
    $images = custom_box_large_pizza_box_dimensions_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_large_pizza_box_dimensions_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_large_pizza_box_dimensions_attachment($image['base'], $post_id, $image);
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

        $marker = '<!-- large-pizza-box-dimensions-image:' . $key . ' -->';
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

    update_option('custom_box_large_pizza_box_dimensions_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_large_pizza_box_dimensions_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_find_large_pizza_box_dimensions_attachment(string $base): int
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

function custom_box_create_large_pizza_box_dimensions_attachment(string $base, int $post_id, array $image): int
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

function custom_box_large_pizza_box_dimensions_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_large_pizza_box_dimensions_post_data();
    $images = custom_box_large_pizza_box_dimensions_images();
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
        4 !== substr_count($content, '<!-- large-pizza-box-dimensions-image:')
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
    if ((array) get_option('custom_box_large_pizza_box_dimensions_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_large_pizza_box_dimensions_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_large_pizza_box_dimensions_validation_failures', array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_large_pizza_box_dimensions_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_LARGE_PIZZA_BOX_DIMENSIONS_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}

function custom_box_large_pizza_box_dimensions_content(): string
{
    $content = base64_decode('PHN0cm9uZz5BIGxhcmdlIHBpenphIGJveCBpcyBjb21tb25seSBzb21ld2hlcmUgYXJvdW5kIDE0IMOXIDE0IMOXIDIgaW5jaGVzIHRvIDE4IMOXIDE4IMOXIDIgaW5jaGVzICgzNS42IMOXIDM1LjYgw5cgNS4xIGNtIHRvIDQ1Ljcgw5cgNDUuNyDDlyA1LjEgY20pLCBidXQgdGhlcmUgaXMgbm8gdW5pdmVyc2FsIOKAnGxhcmdl4oCdIHNpemUuPC9zdHJvbmc+IEEgcmVzdGF1cmFudCBzZXJ2aW5nIGEgMTQtaW5jaCBwaXp6YSBtYXkgdXNlIGEgYm94IGFyb3VuZCAxNOKAkzE1IGluY2hlcyBzcXVhcmUsIHdoaWxlIGEgMTYtaW5jaCBwaXp6YSBtYXkgcmVxdWlyZSBhIGJveCBjbG9zZXIgdG8gMTbigJMxOCBpbmNoZXMgc3F1YXJlIGRlcGVuZGluZyBvbiBjcnVzdCBkaWFtZXRlciwgdG9wcGluZ3MsIGxvYWRpbmcgY2xlYXJhbmNlLCBhbmQgaG93IHRoZSBzdXBwbGllciBkZWZpbmVzIGJveCBkaW1lbnNpb25zLg0KDQpGb3IgcGFja2FnaW5nIGJ1eWVycywgdGhlIHVzZWZ1bCBxdWVzdGlvbiBpcyB0aGVyZWZvcmUgbm90IHNpbXBseSDigJxIb3cgYmlnIGlzIGEgbGFyZ2UgcGl6emEgYm94P+KAnSBJdCBpczogPHN0cm9uZz53aGF0IHVzYWJsZSBpbnNpZGUgZGltZW5zaW9ucyB3aWxsIGhvbGQgdGhlIGZpbmlzaGVkIHBpenphIHdpdGhvdXQgY3J1c2hpbmcgdGhlIGNydXN0LCBhbGxvd2luZyBleGNlc3NpdmUgbW92ZW1lbnQsIG9yIGxldHRpbmcgdGhlIGxpZCB0b3VjaCB0aGUgdG9wcGluZ3M/PC9zdHJvbmc+IFRoYXQgZGlzdGluY3Rpb24gYmVjb21lcyBlc3BlY2lhbGx5IGltcG9ydGFudCB3aGVuIG9yZGVyaW5nIGN1c3RvbSBjb3JydWdhdGVkIHBpenphIGJveGVzLg0KDQo8IS0tIElNQUdFX1NMT1RfMSAtLT4NCjxoMj5MYXJnZSBQaXp6YSBCb3ggRGltZW5zaW9ucyBhdCBhIEdsYW5jZTwvaDI+DQpUaGUgd29yZCA8ZW0+bGFyZ2U8L2VtPiBpcyBhIG1lbnUgbGFiZWwsIG5vdCBhIHBhY2thZ2luZyBzdGFuZGFyZC4gU3RvY2sgcGl6emEtYm94IGNhdGFsb2dzIGFuZCBwYWNrYWdpbmcgZ3VpZGVzIHVzZSBkaWZmZXJlbnQgbGFiZWxzIGZvciBzaW1pbGFyIGRpbWVuc2lvbnMsIHNvIGJ1eWVycyBzaG91bGQgY29tcGFyZSB0aGUgYWN0dWFsIGxlbmd0aCwgd2lkdGgsIGFuZCBkZXB0aCByYXRoZXIgdGhhbiByZWx5aW5nIG9uIOKAnG1lZGl1bSzigJ0g4oCcbGFyZ2Us4oCdIG9yIOKAnFhMLuKAnQ0KPHRhYmxlPg0KPHRoZWFkPg0KPHRyPg0KPHRoPkZpbmlzaGVkIFBpenphIERpYW1ldGVyPC90aD4NCjx0aD5Db21tb24gQm94IFNpemUgQ2FuZGlkYXRlczwvdGg+DQo8dGg+QXBwcm94LiBNZXRyaWMgU2l6ZTwvdGg+DQo8dGg+UHJhY3RpY2FsIE5vdGU8L3RoPg0KPC90cj4NCjwvdGhlYWQ+DQo8dGJvZHk+DQo8dHI+DQo8dGQ+MTLigJMxMyBpbjwvdGQ+DQo8dGQ+MTMgw5cgMTMgw5cgMS43NeKAkzIgaW4gb3IgMTQgw5cgMTQgw5cgMiBpbjwvdGQ+DQo8dGQ+MzPigJMzNS42IGNtIHNxdWFyZTwvdGQ+DQo8dGQ+T2Z0ZW4gc29sZCBhcyBtZWRpdW0gb3IgbWVkaXVtLWxhcmdlIGRlcGVuZGluZyBvbiB0aGUgc3VwcGxpZXIuPC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+MTQgaW48L3RkPg0KPHRkPjE0IMOXIDE0IMOXIDIgaW4gb3IgMTUgw5cgMTUgw5cgMiBpbjwvdGQ+DQo8dGQ+MzUuNuKAkzM4LjEgY20gc3F1YXJlPC90ZD4NCjx0ZD5BIGNvbW1vbiDigJxsYXJnZeKAnSBwaXp6YSByYW5nZSwgYnV0IHVzYWJsZSBjbGVhcmFuY2UgbXVzdCBzdGlsbCBiZSBjaGVja2VkLjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjE1IGluPC90ZD4NCjx0ZD4xNiDDlyAxNiDDlyAyIGluPC90ZD4NCjx0ZD40MC42IMOXIDQwLjYgw5cgNS4xIGNtPC90ZD4NCjx0ZD5Qcm92aWRlcyBhZGRpdGlvbmFsIGhhbmRsaW5nIGNsZWFyYW5jZSBhcm91bmQgdGhlIGNydXN0LjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjE2IGluPC90ZD4NCjx0ZD4xNiDDlyAxNiDDlyAyIGluLCAxNyDDlyAxNyDDlyAyIGluLCBvciAxOCDDlyAxOCDDlyAyIGluPC90ZD4NCjx0ZD40MC424oCTNDUuNyBjbSBzcXVhcmU8L3RkPg0KPHRkPlN1cHBsaWVyIG5hbWluZyB2YXJpZXMgZnJvbSBsYXJnZSB0byBYTC48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD5UaGljayBvciBkZWVwIHBpenphPC90ZD4NCjx0ZD5Gb290cHJpbnQgYmFzZWQgb24gYWN0dWFsIGRpYW1ldGVyOyBpbmNyZWFzZWQgYm94IGRlcHRoPC90ZD4NCjx0ZD5Qcm9qZWN0LXNwZWNpZmljPC90ZD4NCjx0ZD5IZWlnaHQgc2hvdWxkIGJlIGRlZmluZWQgZnJvbSB0aGUgZmluaXNoZWQgcGl6emEsIG5vdCBmcm9tIGEgc3RhbmRhcmQgY2hhcnQuPC90ZD4NCjwvdHI+DQo8L3Rib2R5Pg0KPC90YWJsZT4NClRoZXNlIGRpbWVuc2lvbnMgc2hvdWxkIGJlIHRyZWF0ZWQgYXMgPHN0cm9uZz5zdGFydGluZyByZWZlcmVuY2VzIHJhdGhlciB0aGFuIHByb2R1Y3Rpb24gc3BlY2lmaWNhdGlvbnM8L3N0cm9uZz4uIFR3byBib3hlcyBkZXNjcmliZWQgYXMgMTUgw5cgMTUgw5cgMiBpbmNoZXMgY2FuIHByb3ZpZGUgc2xpZ2h0bHkgZGlmZmVyZW50IHVzYWJsZSBzcGFjZSBkZXBlbmRpbmcgb24gd2hldGhlciB0aGUgbnVtYmVyIHJlZmVycyB0byBub21pbmFsLCBpbnNpZGUsIG9yIG91dHNpZGUgZGltZW5zaW9ucyBhbmQgaG93IHRoZSBjb3JydWdhdGVkIHN0cnVjdHVyZSBpcyBjb252ZXJ0ZWQuDQo8aDI+TWVhc3VyZSB0aGUgRmluaXNoZWQgUGl6emEsIE5vdCB0aGUgTWVudSBMYWJlbDwvaDI+DQpBIOKAnDE0LWluY2ggcGl6emHigJ0gZG9lcyBub3QgYXV0b21hdGljYWxseSBjcmVhdGUgYSBjb21wbGV0ZSBib3ggc3BlY2lmaWNhdGlvbi4gRG91Z2ggZXhwYW5zaW9uLCBjcnVzdCBzaGFwZSwgdG9wcGluZ3MsIGJha2luZyBtZXRob2QsIGFuZCBwcm9kdWN0aW9uIHZhcmlhdGlvbiBjYW4gYWxsIGNoYW5nZSB0aGUgZmluaXNoZWQgZm9vdHByaW50Lg0KDQpGb3IgYSBjdXN0b20gcHJvamVjdCwgbWVhc3VyZSB0aGUgcHJvZHVjdCB0aGF0IHdpbGwgYWN0dWFsbHkgZW50ZXIgdGhlIGJveDoNCjxvbD4NCiAJPGxpPjxzdHJvbmc+TWVhc3VyZSB0aGUgZmluaXNoZWQgcGl6emEgYXQgaXRzIHdpZGVzdCBwb2ludC48L3N0cm9uZz4gVXNlIHRoZSBiYWtlZCBwcm9kdWN0IHJhdGhlciB0aGFuIHJhdyBkb3VnaCBvciB0aGUgYmFraW5nIHBhbi48L2xpPg0KIAk8bGk+PHN0cm9uZz5DaGVjayBzZXZlcmFsIHBpenphcyByYXRoZXIgdGhhbiBvbmUgcGVyZmVjdCBzYW1wbGUuPC9zdHJvbmc+IFRoZSBib3ggaGFzIHRvIGFjY29tbW9kYXRlIG5vcm1hbCBwcm9kdWN0aW9uIHZhcmlhdGlvbiwgbm90IG9ubHkgdGhlIG5vbWluYWwgcmVjaXBlIHNpemUuPC9saT4NCiAJPGxpPjxzdHJvbmc+TWVhc3VyZSB0aGUgaGlnaGVzdCBwb2ludCBvZiB0aGUgZmluaXNoZWQgcGl6emEuPC9zdHJvbmc+IFRoaWNrIGNydXN0LCByYWlzZWQgZWRnZXMsIGNoZWVzZSwgdG9wcGluZ3MsIG9yIGEgcGl6emEgc2F2ZXIgY2FuIGNoYW5nZSB0aGUgcmVxdWlyZWQgaW50ZXJuYWwgaGVpZ2h0LjwvbGk+DQogCTxsaT48c3Ryb25nPkRlY2lkZSBob3cgbXVjaCBsYXRlcmFsIGNsZWFyYW5jZSBpcyBhY2NlcHRhYmxlLjwvc3Ryb25nPiBUb28gbGl0dGxlIGNsZWFyYW5jZSBjYW4gcHJlc3MgdGhlIGNydXN0IGFnYWluc3QgdGhlIHNpZGUgd2FsbDsgdG9vIG11Y2ggY2FuIGFsbG93IHVubmVjZXNzYXJ5IG1vdmVtZW50IGR1cmluZyBoYW5kbGluZy48L2xpPg0KIAk8bGk+PHN0cm9uZz5TcGVjaWZ5IHVzYWJsZSBpbnNpZGUgZGltZW5zaW9ucyBhZnRlciBhc3NlbWJseS48L3N0cm9uZz4gRG8gbm90IGxlYXZlIHRoZSBzdXBwbGllciB0byBndWVzcyB3aGV0aGVyIHlvdXIgbnVtYmVycyBkZXNjcmliZSB0aGUgcGl6emEsIHRoZSBib3ggZXh0ZXJpb3IsIG9yIHRoZSBpbnRlcm5hbCBjYXZpdHkuPC9saT4NCjwvb2w+DQo8IS0tIElNQUdFX1NMT1RfMiAtLT4NCg0KQ29ycnVnYXRlZCBwYWNrYWdpbmcgaXMgbm9ybWFsbHkgc3BlY2lmaWVkIGFzIDxzdHJvbmc+TGVuZ3RoIMOXIFdpZHRoIMOXIEhlaWdodDwvc3Ryb25nPi4gRm9yIGEgc3F1YXJlIHBpenphIGJveCwgbGVuZ3RoIGFuZCB3aWR0aCBtYXkgYmUgaWRlbnRpY2FsLCBidXQgdGhlIG1lYXN1cmVtZW50IGNvbnZlbnRpb24gc3RpbGwgbWF0dGVycyB3aGVuIGEgZGllbGluZSwgcXVvdGF0aW9uLCBzYW1wbGUsIG9yIFFDIHJlcG9ydCBpcyByZXZpZXdlZC4NCjxoMj5UaGUgVG9sZXJhbmNlIFByb2JsZW0gTW9zdCBQaXp6YS1Cb3ggU2l6ZSBDaGFydHMgU2tpcDwvaDI+DQpBIHNpemUgY2hhcnQgY2FuIHRlbGwgeW91IHRoYXQgYSAxNSDDlyAxNSDDlyAyLWluY2ggYm94IGV4aXN0cy4gSXQgY2Fubm90IHRlbGwgeW91IHdoZXRoZXIgdGhhdCBib3ggd2lsbCByZWxpYWJseSBmaXQgeW91ciBwcm9kdWN0IGFmdGVyIGRpZS1jdXR0aW5nLCBzY29yaW5nLCBmb2xkaW5nLCBhc3NlbWJseSwgYW5kIG5vcm1hbCBwcm9kdWN0aW9uIHZhcmlhdGlvbi4NCg0KVGhpcyBpcyB3aGVyZSBhIHB1cmNoYXNpbmcgc3BlY2lmaWNhdGlvbiBiZWNvbWVzIG1vcmUgdXNlZnVsIHRoYW4gYSBub21pbmFsIHNpemUuDQoNCkZvciBhIGNvbnZlbnRpb25hbCByb3VuZCBwaXp6YSwgYSBwcmFjdGljYWwgZGV2ZWxvcG1lbnQgcHJvY2VzcyBtYXkgYmVnaW4gd2l0aCBhIHNtYWxsIGNvbnRyb2xsZWQgY2xlYXJhbmNlIGFyb3VuZCB0aGUgZmluaXNoZWQgY3J1c3QuIFJvdWdobHkgPHN0cm9uZz4wLjI14oCTMC41IGluY2ggKDbigJMxMyBtbSkgcGVyIHNpZGU8L3N0cm9uZz4gY2FuIGJlIHVzZWQgYXMgYW4gaW5pdGlhbCBkZXNpZ24gYWxsb3dhbmNlIGluIHNvbWUgYXBwbGljYXRpb25zLCBidXQgaXQgc2hvdWxkIG5vdCBiZSB0cmVhdGVkIGFzIGEgdW5pdmVyc2FsIHBpenphLWJveCBydWxlLiBUaGUgZmluYWwgY2xlYXJhbmNlIHNob3VsZCBiZSBjb25maXJtZWQgd2l0aCBhIHBoeXNpY2FsIHNhbXBsZSBhbmQgdGhlIGFjdHVhbCBob3QgcHJvZHVjdC4NCg0KQSBzaW1wbGUgc2l6aW5nIHJlbGF0aW9uc2hpcCBpczoNCg0KPHN0cm9uZz5UYXJnZXQgaW5zaWRlIGxlbmd0aC93aWR0aCA9IG1heGltdW0gZmluaXNoZWQgcGl6emEgZGlhbWV0ZXIgKyByZXF1aXJlZCBjbGVhcmFuY2Ugb24gYm90aCBzaWRlczwvc3Ryb25nPg0KDQo8c3Ryb25nPlRhcmdldCBpbnNpZGUgaGVpZ2h0ID0gbWF4aW11bSBmaW5pc2hlZCBwcm9kdWN0IGhlaWdodCArIHNhZmUgbGlkIGNsZWFyYW5jZTwvc3Ryb25nPg0KPGgzPkEgd29ya2VkIGV4YW1wbGUgZm9yIGEgbm9taW5hbCAxNC1pbmNoIHBpenphPC9oMz4NClN1cHBvc2UgYSBtZW51IGNhbGxzIHRoZSBwcm9kdWN0IGEgMTQtaW5jaCBwaXp6YSwgYnV0IHNldmVyYWwgZmluaXNoZWQgc2FtcGxlcyBtZWFzdXJlIGNsb3NlIHRvIDE0LjIgaW5jaGVzIGF0IHRoZSB3aWRlc3QgY3J1c3QgZWRnZS4gSWYgdGhlIHBhY2thZ2luZyB0ZWFtIGRlY2lkZXMgdG8gYmVnaW4gd2l0aCBhcHByb3hpbWF0ZWx5IDAuMyBpbmNoIG9mIGNsZWFyYW5jZSBvbiBlYWNoIHNpZGUsIHRoZSByZXF1aXJlZCBpbnRlcm5hbCBmb290cHJpbnQgYmVjb21lcyBhYm91dCAxNC44IGluY2hlcy4NCg0KVGhhdCBwb2ludHMgdG93YXJkIGEgPHN0cm9uZz5ub21pbmFsIDE1LWluY2gtc3F1YXJlIGJveDwvc3Ryb25nPiBhcyBhIHNlbnNpYmxlIHByb3RvdHlwZSByYXRoZXIgdGhhbiBhdXRvbWF0aWNhbGx5IG9yZGVyaW5nIGEgMTQgw5cgMTQtaW5jaCBib3ggYmVjYXVzZSB0aGUgbWVudSBzYXlzIOKAnDE0IGluY2gu4oCdDQoNCklmIHRoZSBoaWdoZXN0IGZpbmlzaGVkIHBpenphIG1lYXN1cmVzIGFwcHJveGltYXRlbHkgMS40IGluY2hlcywgYSAyLWluY2ggbm9taW5hbCBkZXB0aCBtYXkgcHJvdmlkZSBlbm91Z2ggd29ya2luZyByb29tIGluIHRoaXMgaWxsdXN0cmF0aXZlIGV4YW1wbGUuIEEgdGhpY2tlciBwaXp6YSBvciB0YWxsZXIgdG9wcGluZ3MgY291bGQgcmVxdWlyZSBhIGRpZmZlcmVudCBkZXB0aC4NCg0KVGhlIGltcG9ydGFudCBwb2ludCBpcyBub3QgdGhlIGV4YWN0IG51bWJlcnMgaW4gdGhlIGV4YW1wbGUuIEl0IGlzIHRoZSBzZXF1ZW5jZToNCg0KPHN0cm9uZz5tZWFzdXJlIOKGkiBhZGQgZnVuY3Rpb25hbCBjbGVhcmFuY2Ug4oaSIHByb3RvdHlwZSDihpIgbG9hZCB0aGUgcmVhbCBwcm9kdWN0IOKGkiBhcHByb3ZlIHRoZSBmaW5pc2hlZCBib3guPC9zdHJvbmc+DQoNCjwhLS0gSU1BR0VfU0xPVF8zIC0tPg0KPGgzPldoYXQgc2hvdWxkIGFjdHVhbGx5IGhhdmUgYSB0b2xlcmFuY2U/PC9oMz4NCldoZW4gcHVyY2hhc2luZyBhIGN1c3RvbSBwaXp6YSBib3gsIGFzayB0aGUgY29udmVydGVyIHRvIGNsYXJpZnkgdGhlIHRvbGVyYW5jZSBhbmQgbWVhc3VyZW1lbnQgbWV0aG9kIGZvciB0aGUgZGltZW5zaW9ucyB0aGF0IGFmZmVjdCBmaXQuIFRoZSBzcGVjaWZpY2F0aW9uIGNhbiBjb3ZlcjoNCjx1bD4NCiAJPGxpPmZpbmlzaGVkIGludGVybmFsIGxlbmd0aDs8L2xpPg0KIAk8bGk+ZmluaXNoZWQgaW50ZXJuYWwgd2lkdGg7PC9saT4NCiAJPGxpPmZpbmlzaGVkIGludGVybmFsIGhlaWdodDs8L2xpPg0KIAk8bGk+c2NvcmUgYW5kIGZvbGQgcG9zaXRpb24gd2hlcmUgaXQgYWZmZWN0cyB0aGUgY2F2aXR5OzwvbGk+DQogCTxsaT5saWQgYWxpZ25tZW50IGFuZCBjb3JuZXIgbG9ja2luZzs8L2xpPg0KIAk8bGk+Y29ycnVnYXRlZCBib2FyZCBjb25zdHJ1Y3Rpb24gb3IgY2FsaXBlcjs8L2xpPg0KIAk8bGk+YXNzZW1ibGVkLWJveCBzcXVhcmVuZXNzOzwvbGk+DQogCTxsaT5hbnkgdmVudGlsYXRpb24gb3IgbG9ja2luZyBmZWF0dXJlIHRoYXQgY2hhbmdlcyB0aGUgdXNhYmxlIGFyZWEuPC9saT4NCjwvdWw+DQpUaGVyZSBpcyBubyByZWFzb24gdG8gaW52ZW50IG9uZSB1bml2ZXJzYWwgZGltZW5zaW9uYWwgdG9sZXJhbmNlIGZvciBldmVyeSBwaXp6YS1ib3ggZmFjdG9yeSwgYm9hcmQgZ3JhZGUsIGZsdXRlLCBhbmQgY29udmVydGluZyBwcm9jZXNzLiBQdXQgdGhlIGFncmVlZCB0b2xlcmFuY2Ugb24gdGhlIHNwZWNpZmljYXRpb24gb3IgYXBwcm92ZWQgZHJhd2luZyBpbnN0ZWFkLiBUaGF0IGdpdmVzIHB1cmNoYXNpbmcgYW5kIFFDIHRlYW1zIHNvbWV0aGluZyBtZWFzdXJhYmxlIHdoZW4gdGhlIGJ1bGsgb3JkZXIgYXJyaXZlcy4NCjxoMj5CLUZsdXRlIHZzLiBFLUZsdXRlIGZvciBhIExhcmdlIFBpenphIEJveDwvaDI+DQpCb3ggZGltZW5zaW9ucyBhbG9uZSBkbyBub3QgZGV0ZXJtaW5lIHdoZXRoZXIgYSBsYXJnZSBwaXp6YSBib3ggcGVyZm9ybXMgd2VsbC4gSW5jcmVhc2luZyB0aGUgZm9vdHByaW50IGFsc28gaW5jcmVhc2VzIHRoZSB1bnN1cHBvcnRlZCBzcGFuIGFjcm9zcyB0aGUgbGlkIGFuZCBiYXNlLCBzbyBib2FyZCBjb25zdHJ1Y3Rpb24gYmVjb21lcyBtb3JlIGltcG9ydGFudCBhcyB0aGUgYm94IGdldHMgbGFyZ2VyLg0KPHRhYmxlPg0KPHRoZWFkPg0KPHRyPg0KPHRoPkJvYXJkPC90aD4NCjx0aD5BcHByb3guIENvcnJ1Z2F0ZWQgVGhpY2tuZXNzPC90aD4NCjx0aD5UeXBpY2FsIEFkdmFudGFnZTwvdGg+DQo8dGg+V2hhdCB0byBDaGVjayBmb3IgUGl6emEgUGFja2FnaW5nPC90aD4NCjwvdHI+DQo8L3RoZWFkPg0KPHRib2R5Pg0KPHRyPg0KPHRkPjxzdHJvbmc+Qi1mbHV0ZTwvc3Ryb25nPjwvdGQ+DQo8dGQ+QWJvdXQgMyBtbSAvIDHigYQ4IGluPC90ZD4NCjx0ZD5UaGlja2VyIHByb2ZpbGUgYW5kIGNvbW1vbmx5IHVzZWQgZm9yIGNvcnJ1Z2F0ZWQgcGl6emEgYm94ZXM8L3RkPg0KPHRkPlVzZWZ1bCB3aGVuIHN0aWZmbmVzcywgaGFuZGxpbmcsIGFuZCBzdGFja2VkIGRlbGl2ZXJ5IGxvYWRzIG1hdHRlci48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3Ryb25nPkUtZmx1dGU8L3N0cm9uZz48L3RkPg0KPHRkPkFib3V0IDHigJMxLjUgbW0gLyAx4oGEMTYgaW48L3RkPg0KPHRkPlRoaW5uZXIgcHJvZmlsZSB3aXRoIGEgcmVsYXRpdmVseSBzbW9vdGggc3VyZmFjZSBmb3IgZGV0YWlsZWQgcHJpbnRpbmc8L3RkPg0KPHRkPkNhbiBzdWl0IHByb2plY3RzIHByaW9yaXRpemluZyBjb21wYWN0IGNvbnN0cnVjdGlvbiBhbmQgcHJpbnQgcHJlc2VudGF0aW9uLCBwcm92aWRlZCB0aGUgY29tcGxldGUgYm9hcmQgZ3JhZGUgYW5kIHN0cnVjdHVyZSBtZWV0IHRoZSByZXF1aXJlZCBwZXJmb3JtYW5jZS48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KRG8gbm90IHNlbGVjdCBib2FyZCBvbmx5IGJ5IGZsdXRlIGxldHRlci4gTGluZXIgYW5kIG1lZGl1bSBwYXBlcnMsIGJvYXJkIHdlaWdodCwgc3RyZW5ndGggZ3JhZGUsIGJveCBnZW9tZXRyeSwgaHVtaWRpdHksIGdyZWFzZSBleHBvc3VyZSwgc3RhY2tpbmcgY29uZGl0aW9ucywgZGVsaXZlcnkgaGFuZGxpbmcsIGFuZCB0aGUgc2l6ZSBvZiB0aGUgdW5zdXBwb3J0ZWQgbGlkIGFyZWEgY2FuIGFsbCBhZmZlY3QgcmVhbCBwZXJmb3JtYW5jZS4NCg0KQSBoZWF2aWVyIEItZmx1dGUgc3BlY2lmaWNhdGlvbiBpcyB0aGVyZWZvcmUgbm90IGF1dG9tYXRpY2FsbHkgbmVjZXNzYXJ5IGZvciBldmVyeSDigJxsYXJnZeKAnSBwaXp6YSwgd2hpbGUgYW4gRS1mbHV0ZSBib3ggc2hvdWxkIG5vdCBhdXRvbWF0aWNhbGx5IGJlIHNlbGVjdGVkIGp1c3QgYmVjYXVzZSBpdCBsb29rcyBtb3JlIHJlZmluZWQuIFRoZSByaWdodCBib2FyZCBpcyB0aGUgb25lIHRoYXQgcGFzc2VzIHRoZSByZXF1aXJlZCB1c2UgY29uZGl0aW9ucyB3aXRoIGFuIGFjY2VwdGFibGUgbWF0ZXJpYWwgYW5kIHByb2R1Y3Rpb24gY29zdC4NCjxoMj5Cb2FyZCBUaGlja25lc3MgQWxzbyBDaGFuZ2VzIE91dHNpZGUgRGltZW5zaW9uczwvaDI+DQpUaGlzIGRldGFpbCBtYXR0ZXJzIHdoZW4gcmVzdGF1cmFudCBvcGVyYXRvcnMgc3RhY2sgZW1wdHkgYm94ZXMgb24gc2hlbHZlcywgcGFjayBidW5kbGVzIGludG8gbWFzdGVyIGNhcnRvbnMsIG9yIGRlc2lnbiBvdGhlciBlcXVpcG1lbnQgYXJvdW5kIGEgZml4ZWQgb3V0c2lkZSBzaXplLg0KDQpJZiBhIHN1cHBsaWVyIHNwZWNpZmllcyB0aGUgYm94IGJ5IGl0cyA8c3Ryb25nPmluc2lkZSBkaW1lbnNpb25zPC9zdHJvbmc+LCB0aGUgZmluaXNoZWQgZXh0ZXJpb3Igd2lsbCBuZWNlc3NhcmlseSBiZSBsYXJnZXIgYmVjYXVzZSB0aGUgYm9hcmQgaGFzIHBoeXNpY2FsIHRoaWNrbmVzcy4gTW92aW5nIGZyb20gYSByZWxhdGl2ZWx5IHRoaW4gY29ycnVnYXRlZCBjb25zdHJ1Y3Rpb24gdG8gYSB0aGlja2VyIG9uZSBjYW4gdGhlcmVmb3JlIGNoYW5nZSB0aGUgZXh0ZXJuYWwgZm9vdHByaW50IGV2ZW4gd2hlbiB0aGUgdXNhYmxlIGludGVybmFsIGNhdml0eSByZW1haW5zIHRoZSBzYW1lLg0KDQpGb3IgdGhhdCByZWFzb24sIGEgY29tcGxldGUgcHVyY2hhc2luZyBicmllZiBzaG91bGQgaWRlbnRpZnkgd2hpY2ggZGltZW5zaW9uIG1hdHRlcnMgZm9yIGVhY2ggZnVuY3Rpb246DQo8dWw+DQogCTxsaT48c3Ryb25nPkluc2lkZSBkaW1lbnNpb25zOjwvc3Ryb25nPiBwcm9kdWN0IGZpdCBhbmQgdG9wcGluZyBjbGVhcmFuY2UuPC9saT4NCiAJPGxpPjxzdHJvbmc+T3V0c2lkZSBkaW1lbnNpb25zOjwvc3Ryb25nPiBzdG9yYWdlIHJhY2tzLCBkZWxpdmVyeSBiYWdzLCBtYXN0ZXIgY2FydG9ucywgYW5kIGxvZ2lzdGljcyBjb25zdHJhaW50cy48L2xpPg0KIAk8bGk+PHN0cm9uZz5GbGF0IGJsYW5rIGRpbWVuc2lvbnM6PC9zdHJvbmc+IG1hbnVmYWN0dXJpbmcsIGJ1bmRsZSBzaXplLCBtYXRlcmlhbCB1c2FnZSwgYW5kIGZhY3RvcnkgcGxhbm5pbmcuPC9saT4NCjwvdWw+DQpUaGVzZSBudW1iZXJzIHNob3VsZCBub3QgYmUgdHJlYXRlZCBhcyBpbnRlcmNoYW5nZWFibGUuDQo8aDI+QnVpbGQgdGhlIEJveCBTcGVjaWZpY2F0aW9uIEFyb3VuZCB0aGUgUGl6emE8L2gyPg0KT25jZSBzdG9jayBkaW1lbnNpb25zIHN0b3AgZml0dGluZyByZWxpYWJseSwgdGhlIGJldHRlciBhcHByb2FjaCBpcyB0byBnaXZlIGEgPGEgaHJlZj0iaHR0cHM6Ly9ob3BnaWF5dnBuLmNvbS9jdXN0b20tcGFja2FnaW5nLWJveGVzLW1hbnVmYWN0dXJlci8iPmN1c3RvbSBwYWNrYWdpbmcgYm94IG1hbnVmYWN0dXJlcjwvYT4gdGhlIHByb2R1Y3QgaW5mb3JtYXRpb24gcmVxdWlyZWQgdG8gZGV2ZWxvcCB0aGUgc3RydWN0dXJlIGFyb3VuZCB0aGUgcmVhbCBwaXp6YSByYXRoZXIgdGhhbiBhc2tpbmcgb25seSBmb3Ig4oCcYSBsYXJnZSBwaXp6YSBib3gu4oCdDQoNCkEgdXNlZnVsIFJGUSBjYW4gbG9vayBsaWtlIHRoaXM6DQo8dGFibGU+DQo8dGhlYWQ+DQo8dHI+DQo8dGg+U3BlY2lmaWNhdGlvbiBJdGVtPC90aD4NCjx0aD5JbmZvcm1hdGlvbiB0byBQcm92aWRlPC90aD4NCjwvdHI+DQo8L3RoZWFkPg0KPHRib2R5Pg0KPHRyPg0KPHRkPlBpenphIGZvcm1hdDwvdGQ+DQo8dGQ+Um91bmQsIHNxdWFyZSwgcmVjdGFuZ3VsYXIsIGRlZXAgZGlzaCwgcGFuLCBmbGF0YnJlYWQsIG9yIG90aGVyIGZvcm1hdDwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPkZpbmlzaGVkIG1heGltdW0gZGlhbWV0ZXIgLyBmb290cHJpbnQ8L3RkPg0KPHRkPk1lYXN1cmVkIGZyb20gYWN0dWFsIGJha2VkIHByb2R1Y3Q8L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD5NYXhpbXVtIHByb2R1Y3QgaGVpZ2h0PC90ZD4NCjx0ZD5JbmNsdWRlIGNydXN0LCB0b3BwaW5ncywgYW5kIGFueSBwaXp6YSBzYXZlciBpZiB1c2VkPC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+VGFyZ2V0IGluc2lkZSBib3ggc2l6ZTwvdGQ+DQo8dGQ+TCDDlyBXIMOXIEggd2l0aCB1bml0IGNsZWFybHkgc3RhdGVkPC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+RGltZW5zaW9uYWwgdG9sZXJhbmNlPC90ZD4NCjx0ZD5BZ3JlZSB3aXRoIHN1cHBsaWVyIGR1cmluZyBkcmF3aW5nL3NhbXBsZSBhcHByb3ZhbDwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPkJvYXJkIGRpcmVjdGlvbjwvdGQ+DQo8dGQ+Qi1mbHV0ZSwgRS1mbHV0ZSwgb3Igc3VwcGxpZXIgcmVjb21tZW5kYXRpb24gYmFzZWQgb24gcmVxdWlyZWQgcGVyZm9ybWFuY2U8L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD5Vc2UgY29uZGl0aW9uczwvdGQ+DQo8dGQ+VGFrZWF3YXksIHNob3J0IGRlbGl2ZXJ5LCBzdGFja2VkIGRlbGl2ZXJ5LCByZXRhaWwsIGZyb3plbiBwcm9kdWN0LCBldGMuPC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+UHJpbnRpbmc8L3RkPg0KPHRkPkxvZ28sIGNvbG9ycywgYXJ0d29yayBjb3ZlcmFnZSwgaW5zaWRlL291dHNpZGUgcHJpbnRpbmcgaWYgcmVxdWlyZWQ8L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD5Gb29kLWNvbnRhY3QgcmVxdWlyZW1lbnQ8L3RkPg0KPHRkPlN0YXRlIGludGVuZGVkIGZvb2QgY29udGFjdCwgZ3JlYXNlIGV4cG9zdXJlLCBsaW5lci9jb2F0aW5nIG5lZWRzLCBhbmQgZGVzdGluYXRpb24tbWFya2V0IHJlcXVpcmVtZW50cyBmb3Igc3VwcGxpZXIgY29uZmlybWF0aW9uPC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+UXVhbnRpdHkgYW5kIGRlc3RpbmF0aW9uPC90ZD4NCjx0ZD5SZXF1aXJlZCBvcmRlciB2b2x1bWUgYW5kIGRlbGl2ZXJ5IGNvdW50cnk8L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPCEtLSBJTUFHRV9TTE9UXzQgLS0+DQo8aDI+V2hlbiBJcyBhIFN0b2NrIExhcmdlIFBpenphIEJveCBHb29kIEVub3VnaD88L2gyPg0KQ3VzdG9tIHNpemluZyBpcyBub3QgYXV0b21hdGljYWxseSBiZXR0ZXIuIEEgc3RvY2sgYm94IGNhbiBiZSB0aGUgbW9yZSBlZmZpY2llbnQgY2hvaWNlIHdoZW46DQo8dWw+DQogCTxsaT55b3VyIGZpbmlzaGVkIHBpenphcyBzdGF5IHdpdGhpbiBhIHByZWRpY3RhYmxlIGRpYW1ldGVyIHJhbmdlOzwvbGk+DQogCTxsaT5hIHN0YW5kYXJkIGJveCBwcm92aWRlcyBhY2NlcHRhYmxlIGNydXN0IGFuZCBsaWQgY2xlYXJhbmNlOzwvbGk+DQogCTxsaT50aGUgYXNzZW1ibGVkIGJveCBwYXNzZXMgeW91ciBhY3R1YWwgZGVsaXZlcnkgdGVzdDs8L2xpPg0KIAk8bGk+c3RvY2sgYm9hcmQgc3RyZW5ndGggaXMgYWRlcXVhdGUgZm9yIHlvdXIgc3RhY2tpbmcgY29uZGl0aW9uczs8L2xpPg0KIAk8bGk+eW91IGRvIG5vdCBuZWVkIHN0cnVjdHVyYWwgY2hhbmdlcyB0byB0aGUgbGlkLCBsb2NraW5nIHRhYnMsIHZlbnRzLCBvciBsaW5lcjs8L2xpPg0KIAk8bGk+c3RhbmRhcmQgcHJpbnRpbmcgb3IgbGFiZWxpbmcgbWVldHMgdGhlIGJyYW5kaW5nIHJlcXVpcmVtZW50LjwvbGk+DQo8L3VsPg0KQ3VzdG9tIGRldmVsb3BtZW50IGJlY29tZXMgbW9yZSB1c2VmdWwgd2hlbiB5b3VyIHByb2R1Y3QgY29uc2lzdGVudGx5IHNpdHMgYmV0d2VlbiBzdG9jayBzaXplcywgaGFzIGFuIHVudXN1YWwgc2hhcGUsIHVzZXMgYSB0aGljayBjcnVzdCwgY2FycmllcyB0YWxsIHRvcHBpbmdzLCByZXF1aXJlcyBhIGRpZmZlcmVudCBib3ggZGVwdGgsIG5lZWRzIGEgcGFydGljdWxhciBib2FyZCBjb25zdHJ1Y3Rpb24sIG9yIG11c3QgaW50ZWdyYXRlIHNwZWNpZmljIGJyYW5kaW5nIGFuZCBvcGVyYXRpb25hbCBmZWF0dXJlcy4NCjxoMj5RdWljayBBbnN3ZXJzIEFib3V0IExhcmdlIFBpenphIEJveCBEaW1lbnNpb25zPC9oMj4NCjxoMz5XaGF0IGFyZSB0aGUgZGltZW5zaW9ucyBvZiBhIGxhcmdlIHBpenphIGJveD88L2gzPg0KVGhlcmUgaXMgbm8gc2luZ2xlIHN0YW5kYXJkLiBMYXJnZSBwaXp6YSBib3hlcyBjb21tb25seSBmYWxsIHNvbWV3aGVyZSBhcm91bmQgPHN0cm9uZz4xNCDDlyAxNCDDlyAyIGluY2hlcyB0byAxOCDDlyAxOCDDlyAyIGluY2hlczwvc3Ryb25nPiwgZGVwZW5kaW5nIG9uIHRoZSBwaXp6YSBkaWFtZXRlciBhbmQgdGhlIHN1cHBsaWVyJ3MgbmFtaW5nIHN5c3RlbS4NCjxoMz5XaGF0IHNpemUgYm94IHNob3VsZCBJIHVzZSBmb3IgYSAxNC1pbmNoIHBpenphPzwvaDM+DQpBIDE0LWluY2ggcGl6emEgY29tbW9ubHkgbGVhZHMgYnV5ZXJzIHRvd2FyZCBhcHByb3hpbWF0ZWx5IGEgPHN0cm9uZz4xNC0gdG8gMTUtaW5jaC1zcXVhcmUgYm94IHdpdGggYXJvdW5kIGEgMi1pbmNoIGRlcHRoPC9zdHJvbmc+LiBNZWFzdXJlIHRoZSBmaW5pc2hlZCBwaXp6YSBhbmQgY29uZmlybSB0aGUgdXNhYmxlIGludGVybmFsIGRpbWVuc2lvbnMgYmVmb3JlIGJ1bGsgb3JkZXJpbmcuDQo8aDM+U2hvdWxkIHRoZSBwaXp6YSBib3ggYmUgYmlnZ2VyIHRoYW4gdGhlIHBpenphPzwvaDM+DQpVc3VhbGx5LCBhIHNtYWxsIGFtb3VudCBvZiBjb250cm9sbGVkIGNsZWFyYW5jZSBpcyBkZXNpcmFibGUgc28gdGhlIGNydXN0IGlzIG5vdCBmb3JjZWQgYWdhaW5zdCB0aGUgd2FsbHMuIEhvd2V2ZXIsIGV4Y2Vzc2l2ZSBzcGFjZSBjYW4gYWxsb3cgdGhlIHByb2R1Y3QgdG8gbW92ZS4gVXNlIHRoZSBhY3R1YWwgZmluaXNoZWQgcGl6emEgdG8gZGV0ZXJtaW5lIHRoZSBjbGVhcmFuY2UgYW5kIHZhbGlkYXRlIGl0IHdpdGggYSBwaHlzaWNhbCBzYW1wbGUuDQo8aDM+QXJlIHBpenphIGJveCBkaW1lbnNpb25zIG1lYXN1cmVkIGluc2lkZSBvciBvdXRzaWRlPzwvaDM+DQpGb3IgYSBtYW51ZmFjdHVyaW5nIHNwZWNpZmljYXRpb24sIDxzdHJvbmc+aW5zaWRlIGRpbWVuc2lvbnMgYXJlIHRoZSBzYWZlc3QgcmVmZXJlbmNlIGZvciBwcm9kdWN0IGZpdDwvc3Ryb25nPi4gQWx3YXlzIHN0YXRlIGV4cGxpY2l0bHkgd2hldGhlciB0aGUgcXVvdGF0aW9uLCBkcmF3aW5nLCBvciBjYXRhbG9nIGRpbWVuc2lvbiBpcyBpbnRlcm5hbCwgZXh0ZXJuYWwsIG9yIG5vbWluYWwuDQo8aDI+V2hhdCB0byBTZW5kIFlvdXIgU3VwcGxpZXIgQmVmb3JlIEFwcHJvdmluZyBhIExhcmdlIFBpenphIEJveDwvaDI+DQpEbyBub3Qgc2VuZCBvbmx5IHRoZSB3b3JkcyDigJxsYXJnZSBwaXp6YSBib3gu4oCdIFNlbmQgdGhlIDxzdHJvbmc+bWF4aW11bSBmaW5pc2hlZCBwaXp6YSBkaWFtZXRlciwgbWF4aW11bSBoZWlnaHQsIGRlc2lyZWQgaW50ZXJuYWwgY2xlYXJhbmNlLCBkZWxpdmVyeSBjb25kaXRpb25zLCBib2FyZCBkaXJlY3Rpb24sIHByaW50aW5nIHJlcXVpcmVtZW50cywgb3JkZXIgcXVhbnRpdHksIGFuZCBkZXN0aW5hdGlvbjwvc3Ryb25nPi4NCg0KVGhlbiBhcHByb3ZlIHRoZSBzdHJ1Y3R1cmUgdXNpbmcgYSBwaHlzaWNhbCBzYW1wbGUgbG9hZGVkIHdpdGggdGhlIHJlYWwgcHJvZHVjdC4gSWYgdGhlIHBpenphIGZpdHMgd2l0aG91dCBjb21wcmVzc2VkIGNydXN0LCBleGNlc3NpdmUgbW92ZW1lbnQsIGxpZCBjb250YWN0LCBvciB1bmFjY2VwdGFibGUgYm94IGRlZm9ybWF0aW9uIHVuZGVyIHlvdXIgYWN0dWFsIGhhbmRsaW5nIGNvbmRpdGlvbnMsIHlvdSBoYXZlIGEgdXNlZnVsIHByb2R1Y3Rpb24gc3BlY2lmaWNhdGlvbuKAlG5vdCBqdXN0IGEgc2l6ZSB0YWtlbiBmcm9tIGEgY2hhcnQu', true);

    return is_string($content) ? $content : '';
}
