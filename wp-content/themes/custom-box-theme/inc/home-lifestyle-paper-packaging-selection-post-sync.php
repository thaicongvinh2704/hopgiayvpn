<?php
/**
 * Deploys the home and lifestyle paper packaging guide draft and images.
 */

const CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_SYNC_VERSION = '2026-07-23-v1';
const CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_VERSION_OPTION = 'custom_box_home_lifestyle_packaging_sync_version';
const CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_NOTICE_OPTION = 'custom_box_home_lifestyle_packaging_sync_notice';

add_action('admin_init', 'custom_box_sync_home_lifestyle_packaging_post');
add_action('admin_notices', 'custom_box_home_lifestyle_packaging_admin_notice');

function custom_box_sync_home_lifestyle_packaging_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_home_lifestyle_packaging_post_data();
    $post = custom_box_find_home_lifestyle_packaging_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_SYNC_VERSION === get_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_VERSION_OPTION)
        && $post
        && custom_box_home_lifestyle_packaging_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_home_lifestyle_packaging_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_VERSION_OPTION);
        update_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_home_lifestyle_packaging_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_VERSION_OPTION, CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Home and lifestyle packaging draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Guides, 5 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_VERSION_OPTION);
    delete_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_NOTICE_OPTION);
    update_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Home and lifestyle packaging sync is incomplete. Missing images: '
            . implode(', ', (array) get_option('custom_box_home_lifestyle_packaging_missing_images', array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option('custom_box_home_lifestyle_packaging_missing_slots', array()),
                (array) get_option('custom_box_home_lifestyle_packaging_validation_failures', array())
            )),
    ), false);
}

function custom_box_home_lifestyle_packaging_post_data(): array
{
    return array(
        'title' => 'How to Choose Paper Packaging for Home and Lifestyle Products',
        'slug' => 'choose-paper-packaging-home-lifestyle-products',
        'excerpt' => 'A practical buyer guide to choosing paper packaging for home and lifestyle products based on weight, fragility, sales channel, inserts, shelf display and finishing requirements.',
        'category' => array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
        'tags' => array(
            'Home & Lifestyle Packaging' => 'home-lifestyle-packaging',
            'Paper Packaging' => 'paper-packaging',
            'Packaging Design' => 'packaging-design',
            'Product Protection' => 'product-protection',
            'Custom Boxes' => 'custom-boxes',
        ),
        'seo_title' => 'How to Choose Paper Packaging for Home & Lifestyle Products',
        'seo_description' => 'Choose paper packaging for home and lifestyle products by weight, fragility, retail display, shipping method, inserts and finishing requirements.',
        'focus_keyword' => 'how to choose packaging for home lifestyle products',
    );
}

function custom_box_home_lifestyle_packaging_images(): array
{
    return array(
        'featured' => array(
            'base' => 'home-lifestyle-paper-packaging-selection-guide',
            'alt' => 'Paper packaging options for home and lifestyle products',
            'title' => 'Choosing Paper Packaging for Home and Lifestyle Products',
            'caption' => 'Product weight, fragility and sales channel should guide the packaging structure.',
        ),
        'slot_1' => array(
            'base' => 'home-lifestyle-packaging-decision-factors',
            'alt' => 'Home lifestyle products with suitable paper packaging structures',
            'title' => 'Home Lifestyle Packaging Decision Factors',
            'caption' => 'Different products require different structures, inserts and protection levels.',
        ),
        'slot_2' => array(
            'base' => 'paper-box-structures-by-product-risk',
            'alt' => 'Folding carton rigid box and corrugated mailer for lifestyle products',
            'title' => 'Paper Box Structures by Product Risk',
            'caption' => 'Box structure should follow product weight, fragility and distribution risk.',
        ),
        'slot_3' => array(
            'base' => 'fragile-home-product-paper-insert-protection',
            'alt' => 'Paper insert protecting glass home fragrance products',
            'title' => 'Paper Insert Protection for Fragile Home Products',
            'caption' => 'A fitted insert controls movement between fragile components and the box walls.',
        ),
        'slot_4' => array(
            'base' => 'paper-packaging-finishing-for-lifestyle-products',
            'alt' => 'Matte foil embossing and spot UV finishes on paper packaging',
            'title' => 'Paper Packaging Finishing Options',
            'caption' => 'Finishing should support shelf presentation without replacing structural performance.',
        ),
    );
}

function custom_box_find_home_lifestyle_packaging_post(string $slug, string $title): ?WP_Post
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

function custom_box_upsert_home_lifestyle_packaging_post()
{
    $data = custom_box_home_lifestyle_packaging_post_data();
    $post = custom_box_find_home_lifestyle_packaging_post($data['slug'], $data['title']);
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
            $payload['post_content'] = custom_box_home_lifestyle_packaging_content();
        }
        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = custom_box_home_lifestyle_packaging_content();
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_home_lifestyle_packaging_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_home_lifestyle_packaging_images($post_id);

    return $post_id;
}

function custom_box_sync_home_lifestyle_packaging_terms(int $post_id, array $data): void
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

function custom_box_sync_home_lifestyle_packaging_images(int $post_id): void
{
    $images = custom_box_home_lifestyle_packaging_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_home_lifestyle_packaging_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_home_lifestyle_packaging_attachment($image['base'], $post_id, $image);
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

        $marker = '<!-- home-lifestyle-packaging-image:' . $key . ' -->';
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

    update_option('custom_box_home_lifestyle_packaging_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_home_lifestyle_packaging_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_find_home_lifestyle_packaging_attachment(string $base): int
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

function custom_box_create_home_lifestyle_packaging_attachment(string $base, int $post_id, array $image): int
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

function custom_box_home_lifestyle_packaging_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_home_lifestyle_packaging_post_data();
    $images = custom_box_home_lifestyle_packaging_images();
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
        4 !== substr_count($content, '<!-- home-lifestyle-packaging-image:')
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
    if ((array) get_option('custom_box_home_lifestyle_packaging_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_home_lifestyle_packaging_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_home_lifestyle_packaging_validation_failures', array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_home_lifestyle_packaging_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_HOME_LIFESTYLE_PACKAGING_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}

function custom_box_home_lifestyle_packaging_content(): string
{
    $content = base64_decode('PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkhvbWUgYW5kIGxpZmVzdHlsZSBwcm9kdWN0cyBkbyBub3Qgc2hhcmUgb25lIHBhY2thZ2luZyByZXF1aXJlbWVudC4gQSBmb2xkZWQgdGV4dGlsZSBpdGVtIG1heSBvbmx5IG5lZWQgcHJvdGVjdGlvbiBmcm9tIGR1c3QgYW5kIHN1cmZhY2UgbWFya3MsIHdoaWxlIGEgY2VyYW1pYyBtdWcsIGdsYXNzIGNhbmRsZSBvciBkaWZmdXNlciBib3R0bGUgbXVzdCBzdXJ2aXZlIGltcGFjdCwgdmlicmF0aW9uIGFuZCBwcm9kdWN0IG1vdmVtZW50LiBBIG11bHRpLXBpZWNlIGdpZnQgc2V0IGNyZWF0ZXMgYW5vdGhlciBjaGFsbGVuZ2U6IGV2ZXJ5IGNvbXBvbmVudCBtdXN0IHJlbWFpbiBpbiB0aGUgY29ycmVjdCBwb3NpdGlvbiB3aXRob3V0IG1ha2luZyB0aGUgYm94IHVubmVjZXNzYXJpbHkgbGFyZ2UuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhpcyBpcyB3aHkgdGhlIGJveCBzdHlsZSBzaG91bGQgbm90IGJlIHRoZSBmaXJzdCBkZWNpc2lvbi4gU3RhcnQgd2l0aCB0aGUgcHJvZHVjdOKAmXMgd2VpZ2h0LCBmcmFnaWxpdHksIGdlb21ldHJ5LCBzYWxlcyBjaGFubmVsIGFuZCBwcmVzZW50YXRpb24gcmVxdWlyZW1lbnRzLiBPbmNlIHRob3NlIGZhY3RvcnMgYXJlIGNsZWFyLCBpdCBiZWNvbWVzIGVhc2llciB0byBjaG9vc2UgdGhlIGFwcHJvcHJpYXRlIHBhcGVyYm9hcmQsIGJveCBzdHJ1Y3R1cmUsIGluc2VydCBhbmQgc3VyZmFjZSBmaW5pc2guPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIG9iamVjdGl2ZSBpcyBub3QgdG8gY3JlYXRlIHRoZSBzdHJvbmdlc3Qgb3IgbW9zdCBleHBlbnNpdmUgcGFja2FnZS4gSXQgaXMgdG8gdXNlIGVub3VnaCBzdHJ1Y3R1cmUgYW5kIHByb3RlY3Rpb24gZm9yIHRoZSBhY3R1YWwgcmlzayB3aGlsZSBwcmVzZXJ2aW5nIHRoZSBwcmVzZW50YXRpb24gZXhwZWN0ZWQgZnJvbSA8YSBocmVmPSJodHRwczovL2hvcGdpYXl2cG4uY29tL3Byb2R1Y3RzL2hvbWUtbGlmZXN0eWxlLXBhY2thZ2luZy8iPmhvbWUgYW5kIGxpZmVzdHlsZSBwYWNrYWdpbmc8L2E+Ljwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF8xIC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QmVnaW4gV2l0aCB0aGUgUHJvZHVjdOKAmXMgTW9zdCBMaWtlbHkgRmFpbHVyZSBNb2RlPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QmVmb3JlIGNvbXBhcmluZyByaWdpZCBib3hlcywgZm9sZGluZyBjYXJ0b25zIG9yIGNvcnJ1Z2F0ZWQgbWFpbGVycywgaWRlbnRpZnkgd2hhdCBjb3VsZCByZWFsaXN0aWNhbGx5IGdvIHdyb25nIGJldHdlZW4gcGFja2luZyBhbmQgZmluYWwgdXNlLiBEaWZmZXJlbnQgZmFpbHVyZSBtb2RlcyByZXF1aXJlIGRpZmZlcmVudCBwYWNrYWdpbmcgc29sdXRpb25zLjwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkNydXNoaW5nOjwvc3Ryb25nPiBUaGUgYm94IHBhbmVscyBib3csIHRoZSBib3R0b20gc2FncyBvciBwcm9kdWN0cyBhdCB0aGUgYm90dG9tIG9mIGEgbWFzdGVyIGNhcnRvbiBjYXJyeSB0b28gbXVjaCBzdGFja2luZyBsb2FkLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+SW1wYWN0IGRhbWFnZTo8L3N0cm9uZz4gQSBjZXJhbWljLCBnbGFzcyBvciBkZWxpY2F0ZSBkZWNvcmF0aXZlIGl0ZW0gYnJlYWtzIGFmdGVyIGEgZHJvcCBvciBjb2xsaXNpb24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5Qcm9kdWN0IG1vdmVtZW50Ojwvc3Ryb25nPiBUaGUgaXRlbSByb3RhdGVzLCBzbGlkZXMgb3IgbGlmdHMgb3V0IG9mIHBvc2l0aW9uIGFuZCBzdHJpa2VzIHRoZSBib3ggd2FsbC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPlN1cmZhY2UgZGFtYWdlOjwvc3Ryb25nPiBBIHBvbGlzaGVkLCBwYWludGVkLCBjb2F0ZWQgb3IgdGV4dGlsZSBzdXJmYWNlIGJlY29tZXMgc2NyYXRjaGVkLCBtYXJrZWQgb3IgZHVzdHkuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5QcmVzZW50YXRpb24gZmFpbHVyZTo8L3N0cm9uZz4gQ29tcG9uZW50cyBhcnJpdmUgaW50YWN0IGJ1dCBhcHBlYXIgZGlzb3JnYW5pemVkLCB0aWx0ZWQgb3IgcG9vcmx5IGFsaWduZWQgd2hlbiB0aGUgY3VzdG9tZXIgb3BlbnMgdGhlIGJveC48L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhpcyBkaWFnbm9zaXMgZ2l2ZXMgdGhlIGJ1eWVyIGEgdXNlZnVsIGRlY2lzaW9uIHNob3J0Y3V0LiBJZiBtb3ZlbWVudCBpcyB0aGUgbWFpbiByaXNrLCBpbXByb3ZlIHRoZSBpbnNlcnQgYW5kIHByb2R1Y3QgZml0IGJlZm9yZSBpbmNyZWFzaW5nIGJvYXJkIHRoaWNrbmVzcy4gSWYgdGhlIGJveCBiZW5kcyB1bmRlciBsb2FkLCByZXZpZXcgdGhlIHN0cnVjdHVyZSwgYm9hcmQgZ3JhZGUgYW5kIG1hc3Rlci1jYXJ0b24gYXJyYW5nZW1lbnQuIElmIHRoZSBwcm9kdWN0IHJlYWNoZXMgdGhlIGN1c3RvbWVyIHNhZmVseSBidXQgbG9va3Mgd29ybiwgY29uY2VudHJhdGUgb24gc3VyZmFjZSBwcm90ZWN0aW9uLCBmaW5pc2hpbmcgZHVyYWJpbGl0eSBhbmQgaGFuZGxpbmcuPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Vc2UgYSBQcm9kdWN0LXRvLVBhY2thZ2UgRGVjaXNpb24gTWF0cml4PC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGZvbGxvd2luZyBtYXRyaXggaXMgYSBwcmFjdGljYWwgc3RhcnRpbmcgcG9pbnQgcmF0aGVyIHRoYW4gYSBmaXhlZCBzcGVjaWZpY2F0aW9uLiBGaW5hbCBtYXRlcmlhbCB0aGlja25lc3MgYW5kIGNvbnN0cnVjdGlvbiBzaG91bGQgYmUgY29uZmlybWVkIHdpdGggYSBsb2FkZWQgc2FtcGxlIGJlY2F1c2UgZGltZW5zaW9ucywgd2VpZ2h0IGRpc3RyaWJ1dGlvbiBhbmQgc2hpcHBpbmcgY29uZGl0aW9ucyBjYW4gY2hhbmdlIHRoZSByZXN1bHQuPC9zcGFuPg0KPHRhYmxlPg0KPHRoZWFkPg0KPHRyPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Qcm9kdWN0IHByb2ZpbGU8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U3VpdGFibGUgc3RhcnRpbmcgc3RydWN0dXJlPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb3RlY3Rpb24gcHJpb3JpdHk8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RGlzcGxheSBhbmQgZmluaXNoaW5nIGRpcmVjdGlvbjwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NYWluIHJpc2sgdG8gY2hlY2s8L3NwYW4+PC90aD4NCjwvdHI+DQo8L3RoZWFkPg0KPHRib2R5Pg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb2xkZWQgdGV4dGlsZXMsIHRhYmxlIGxpbmVucyBvciBsaWdodHdlaWdodCBzb2Z0IGdvb2RzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkZvbGRpbmcgY2FydG9uLCBwYXBlcmJvYXJkIHNsZWV2ZSBvciBjb3JydWdhdGVkIG1haWxlcjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EdXN0LCBtb2lzdHVyZSBleHBvc3VyZSBhbmQgc3VyZmFjZSBwcmVzZW50YXRpb248L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2xlYW4gcHJpbnRpbmcsIHVuY29hdGVkIHRleHR1cmUgb3IgbWF0dGUgbGFtaW5hdGlvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5PdmVyc2l6ZWQgcGFja2FnaW5nIGFuZCBjcnVzaGVkIGNvcm5lcnM8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNpbmdsZSBjYW5kbGUgaW4gYSBnbGFzcyB2ZXNzZWw8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9sZGluZyBjYXJ0b24gd2l0aCBmaXR0ZWQgaW5zZXJ0IG9yIGNvbXBhY3QgcmlnaWQgYm94PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNvbnRyb2wgbW92ZW1lbnQgYXJvdW5kIHRoZSBiYXNlLCBzaWRlcyBhbmQgcmltPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk1hdHRlIHN1cmZhY2Ugd2l0aCBvbmUgcmVzdHJhaW5lZCBwcmVtaXVtIGRldGFpbDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Mb29zZSBmaXQsIGltcGFjdCBhdCB0aGUgZ2xhc3MgcmltIGFuZCBib3R0b20gZHJvcDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2VyYW1pYyBtdWcsIHNtYWxsIHZhc2Ugb3IgZnJhZ2lsZSBkw6ljb3I8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29ycnVnYXRlZCBwcm90ZWN0aXZlIGJveCB3aXRoIGZpdHRlZCBpbnNlcnQ7IGRlY29yYXRpdmUgaW5uZXIgYm94IHdoZW4gcmVxdWlyZWQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHJvdGVjdCBoYW5kbGVzLCBjb3JuZXJzIGFuZCB1bnN1cHBvcnRlZCBwcm9qZWN0aW9uczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5LZWVwIGRlY29yYXRpdmUgc3VyZmFjZXMgc2VwYXJhdGUgZnJvbSB0aGUgc2hpcHBpbmcgZnVuY3Rpb248L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SGFuZGxlIGNvbnRhY3QsIHNpZGUgaW1wYWN0IGFuZCBwcm9kdWN0LXRvLXdhbGwgbW92ZW1lbnQ8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRpZmZ1c2VyIG9yIGhvbWUtZnJhZ3JhbmNlIGdpZnQgc2V0PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJpZ2lkIGxpZC1hbmQtYmFzZSBib3ggb3IgY29ycnVnYXRlZCBtYWlsZXIgd2l0aCBkaXZpZGVkIGluc2VydDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TZXBhcmF0ZSBnbGFzcyBjb21wb25lbnRzIGFuZCBwcmV2ZW50IHZlcnRpY2FsIGxpZnQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9pbCwgZW1ib3NzaW5nIG9yIHNwb3QgVVYgY2FuIGRlZmluZSB0aGUgZ2lmdCBwb3NpdGlvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db21wb25lbnQgY29sbGlzaW9uLCBsZWFrYWdlIGFuZCBkaWZmaWN1bHQgcmVtb3ZhbDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U3RhdGlvbmVyeSwgc21hbGwgb3JnYW5pemVycyBvciBsaWdodHdlaWdodCBhY2Nlc3Nvcmllczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb2xkaW5nIGNhcnRvbiwgZHJhd2VyLXN0eWxlIHBhcGVyIGJveCBvciBzbWFsbCBtYWlsZXI8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+S2VlcCBtdWx0aXBsZSBwYXJ0cyBvcmdhbml6ZWQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHJpb3JpdGl6ZSBjbGVhciBmcm9udC1wYW5lbCBjb21tdW5pY2F0aW9uIGFuZCB0aWR5IG9wZW5pbmc8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TWlzc2luZyBjb21wYXJ0bWVudHMgYW5kIGV4Y2Vzc2l2ZSBlbXB0eSBzcGFjZTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SGVhdnkgb3IgaXJyZWd1bGFyIGhvbWUgYWNjZXNzb3JpZXM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q3VzdG9tIGNvcnJ1Z2F0ZWQgc3RydWN0dXJlIHdpdGggcmVpbmZvcmNlZCBiYXNlIGFuZCBpbnRlcm5hbCBzdXBwb3J0PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkxvYWQgZGlzdHJpYnV0aW9uIGFuZCBlZGdlIHByb3RlY3Rpb248L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VXNlIGEgcHJpbnRlZCBzbGVldmUgb3IgbGFiZWwgcmF0aGVyIHRoYW4gb3Zlci1maW5pc2hpbmcgdGhlIHNoaXBwZXI8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Qm90dG9tIGZhaWx1cmUsIHRpcHBpbmcgYW5kIHdlYWsgY2xvc3VyZTwvc3Bhbj48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF8yIC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TGV0IFByb2R1Y3QgV2VpZ2h0IERldGVybWluZSB0aGUgU3RydWN0dXJlIGFuZCBDbG9zdXJlPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2VpZ2h0IGFmZmVjdHMgbW9yZSB0aGFuIHRoZSB0aGlja25lc3Mgb2YgdGhlIHBhcGVyLiBJdCBhbHNvIGNoYW5nZXMgaG93IG11Y2ggZm9yY2UgaXMgYXBwbGllZCB0byB0aGUgYm90dG9tIHBhbmVsLCBnbHVlIHNlYW1zLCBjbG9zaW5nIGZsYXBzLCBpbnNlcnQgYW5kIHNpZGV3YWxscyB3aGVuIHRoZSBwYWNrYWdlIGlzIGxpZnRlZCBvciBkcm9wcGVkLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgY29tcGFjdCBwcm9kdWN0IGNhbiBjcmVhdGUgbW9yZSBzdHJ1Y3R1cmFsIHN0cmVzcyB0aGFuIGEgbGFyZ2VyIGJ1dCBsaWdodGVyIGl0ZW0uIEZvciBleGFtcGxlLCBhIGRlbnNlIGNhbmRsZSBzZXQgbWF5IGNvbmNlbnRyYXRlIG1vc3Qgb2YgaXRzIGxvYWQgaW4gdHdvIHNtYWxsIGFyZWFzLiBTaW1wbHkgc3BlY2lmeWluZyBoZWF2aWVyIHBhcGVyYm9hcmQgbWF5IG5vdCBzb2x2ZSB0aGUgcHJvYmxlbSBpZiB0aGUgaW5zZXJ0IHRyYW5zZmVycyB0aGF0IHdlaWdodCB0byBhbiB1bnN1cHBvcnRlZCBzZWN0aW9uIG9mIHRoZSBiYXNlLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZW4gcmV2aWV3aW5nIHBhY2thZ2luZyBmb3IgYSByZWxhdGl2ZWx5IGhlYXZ5IGhvbWUgcHJvZHVjdCwgY2hlY2sgdGhlIGZvbGxvd2luZzo8L3NwYW4+DQo8dWw+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciB0aGUgYm90dG9tIHBhbmVsIHJlbWFpbnMgZmxhdCB3aGVuIHRoZSBjb21wbGV0ZSBwcm9kdWN0IGlzIGxvYWRlZC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaGV0aGVyIHRoZSBwcm9kdWN0IHdlaWdodCBpcyBkaXN0cmlidXRlZCBhY3Jvc3MgdGhlIGluc2VydCByYXRoZXIgdGhhbiBjb25jZW50cmF0ZWQgYXQgb25lIHBvaW50Ljwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZXRoZXIgdGhlIGdsdWUgc2VhbSwgbG9jayB0YWJzIG9yIG1hZ25ldGljIGNsb3N1cmUgcmVtYWluIHNlY3VyZSBkdXJpbmcgaGFuZGxpbmcuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciB0aGUgc2lkZXdhbGxzIGZsZXggd2hlbiB0aGUgYm94IGlzIGxpZnRlZCBmcm9tIG9uZSBlZGdlLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZXRoZXIgYSBkZWNvcmF0aXZlIHJldGFpbCBib3ggYWxzbyBuZWVkcyBhIHNlcGFyYXRlIGNvcnJ1Z2F0ZWQgc2hpcHBpbmcgY29udGFpbmVyLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZXRoZXIgbWFzdGVyLWNhcnRvbiBzdGFja2luZyBwcmVzc3VyZSBjb3VsZCBtYXJrIG9yIGRlZm9ybSB0aGUgcmV0YWlsIHBhY2thZ2UuPC9zcGFuPjwvbGk+DQo8L3VsPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgcmlnaWQgcHJlc2VudGF0aW9uIGJveCBjYW4gZmVlbCBzdHJvbmcgaW4gdGhlIGN1c3RvbWVy4oCZcyBoYW5kcywgYnV0IGl0IHNob3VsZCBub3QgYXV0b21hdGljYWxseSBiZSB0cmVhdGVkIGFzIHRoZSBjb21wbGV0ZSB0cmFuc3BvcnQgcGFja2FnZS4gRm9yIHBhcmNlbCBkZWxpdmVyeSwgYSBwcm90ZWN0aXZlIG91dGVyIGNvcnJ1Z2F0ZWQgYm94IG1heSBzdGlsbCBiZSBuZWNlc3NhcnksIGVzcGVjaWFsbHkgd2hlbiB0aGUgcmV0YWlsIGJveCBoYXMgZGVjb3JhdGl2ZSB3cmFwcGluZyBwYXBlciwgZm9pbCwgc2hhcnAgY29ybmVycyBvciBhIHN1cmZhY2UgdGhhdCBjYW4gYmUgc2NyYXRjaGVkLjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VHJlYXQgRnJhZ2lsaXR5IGFzIGEgTW92ZW1lbnQtQ29udHJvbCBQcm9ibGVtPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RnJhZ2lsZS1wcm9kdWN0IHBhY2thZ2luZyBpcyBvZnRlbiBkZXNjcmliZWQgYXMgYSBxdWVzdGlvbiBvZiBjdXNoaW9uaW5nLiBJbiBwcmFjdGljZSwgdGhlIG1vcmUgdXNlZnVsIHF1ZXN0aW9uIGlzOiA8ZW0+SG93IGNhbiB0aGUgcHJvZHVjdCBtb3ZlIGluc2lkZSB0aGUgYm94PzwvZW0+PC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlcmUgYXJlIHRocmVlIG1vdmVtZW50cyB0byBjb250cm9sOjwvc3Bhbj4NCjxvbD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPlNpZGUtdG8tc2lkZSBtb3ZlbWVudDo8L3N0cm9uZz4gVGhlIHByb2R1Y3Qgc2xpZGVzIGludG8gdGhlIHdhbGxzIG9yIGFub3RoZXIgY29tcG9uZW50Ljwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+VmVydGljYWwgbW92ZW1lbnQ6PC9zdHJvbmc+IFRoZSBwcm9kdWN0IGxpZnRzIG91dCBvZiBpdHMgY2F2aXR5IHdoZW4gdGhlIGJveCBpcyB0dXJuZWQgb3IgZHJvcHBlZC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPlJvdGF0aW9uOjwvc3Ryb25nPiBBbiBpcnJlZ3VsYXIgaXRlbSB0dXJucyB1bnRpbCBhIGhhbmRsZSwgY29ybmVyLCBuZWNrIG9yIHJpbSBiZWNvbWVzIHRoZSBmaXJzdCBwb2ludCBvZiBpbXBhY3QuPC9zcGFuPjwvbGk+DQo8L29sPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgZml0dGVkIGluc2VydCBzaG91bGQgYWRkcmVzcyBhbGwgdGhyZWUuIEEgY2VyYW1pYyBtdWcsIGZvciBleGFtcGxlLCBzaG91bGQgbm90IGJlIHNlY3VyZWQgb25seSBhcm91bmQgaXRzIGNpcmN1bGFyIGJvZHkgd2hpbGUgdGhlIGhhbmRsZSByZW1haW5zIHVuc3VwcG9ydGVkLiBBIGRpZmZ1c2VyIGJvdHRsZSBzaG91bGQgbm90IGJlIGhlbGQgdGlnaHRseSBhdCB0aGUgYmFzZSB3aGlsZSB0aGUgbmVjayBjYW4gc3RyaWtlIHRoZSBsaWQuIEEgZ2xhc3MgY2FuZGxlIHZlc3NlbCBuZWVkcyBjbGVhcmFuY2UgdGhhdCBwcm90ZWN0cyB0aGUgcmltIHdpdGhvdXQgYWxsb3dpbmcgdGhlIGNvbXBsZXRlIHByb2R1Y3QgdG8gcmF0dGxlLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNvbW1vbiBwYXBlci1iYXNlZCBpbnNlcnQgb3B0aW9ucyBpbmNsdWRlOjwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkZvbGRlZCBwYXBlcmJvYXJkIHRyYXlzOjwvc3Ryb25nPiBTdWl0YWJsZSBmb3IgbGlnaHR3ZWlnaHQgcHJvZHVjdHMgYW5kIGNsZWFuIHJldGFpbCBwcmVzZW50YXRpb24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5Db3JydWdhdGVkIGluc2VydHM6PC9zdHJvbmc+IFVzZWZ1bCB3aGVuIGdyZWF0ZXIgZWRnZSBwcm90ZWN0aW9uLCBjb21wcmVzc2lvbiByZXNpc3RhbmNlIG9yIHNlcGFyYXRpb24gaXMgbmVlZGVkLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+UGFwZXJib2FyZCBkaXZpZGVyczo8L3N0cm9uZz4gQXBwcm9wcmlhdGUgZm9yIHNldHMgY29udGFpbmluZyBib3R0bGVzLCBqYXJzLCBjdXBzIG9yIGFjY2Vzc29yaWVzIHRoYXQgbXVzdCBub3QgY29udGFjdCBvbmUgYW5vdGhlci48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPk1vbGRlZCBwdWxwIGluc2VydHM6PC9zdHJvbmc+IFdvcnRoIGV2YWx1YXRpbmcgZm9yIGZpdHRlZCBwcm90ZWN0aW9uIHdoZW4gdGhlIHByb2R1Y3QgZ2VvbWV0cnkgYW5kIHByb2plY3Qgdm9sdW1lIGp1c3RpZnkgYSBkZWRpY2F0ZWQgZm9ybS48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkxheWVyZWQgcGFwZXIgcGxhdGZvcm1zOjwvc3Ryb25nPiBVc2VmdWwgZm9yIHByZXNlbnRpbmcgc2V2ZXJhbCBwcm9kdWN0cyBhdCBkaWZmZXJlbnQgaGVpZ2h0cywgcHJvdmlkZWQgdGhlIHN0cnVjdHVyZSBzdXBwb3J0cyB0aGUgcGFja2VkIHdlaWdodC48L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TG9vc2UgcGFwZXIgb3IgZGVjb3JhdGl2ZSBmaWxsZXIgY2FuIGltcHJvdmUgcHJlc2VudGF0aW9uLCBidXQgaXQgc2hvdWxkIG5vdCBiZSBleHBlY3RlZCB0byBwb3NpdGlvbiBhIGZyYWdpbGUgcHJvZHVjdCBieSBpdHNlbGYuIElmIHRoZSBpdGVtIGNhbiBzdGlsbCBtb3ZlIGFmdGVyIHRoZSBib3ggaXMgY2xvc2VkLCB0aGUgcHJvdGVjdGl2ZSBzeXN0ZW0gaXMgaW5jb21wbGV0ZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48IS0tIElNQUdFX1NMT1RfMyAtLT48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNob29zZSBQYWNrYWdpbmcgQXJvdW5kIFdoZXJlIHRoZSBQcm9kdWN0IElzIFNvbGQ8L3NwYW4+PC9oMj4NCjxoMz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmV0YWlsIHNoZWxmIHBhY2thZ2luZzwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJldGFpbCBwYWNrYWdpbmcgaGFzIHRvIGNvbW11bmljYXRlIHF1aWNrbHkgd2hpbGUgcmVtYWluaW5nIHN0YWJsZSBhbmQgZWFzeSB0byBzdG9jay4gVGhlIGZyb250IHBhbmVsIHNob3VsZCBzdGF5IGZsYXQsIHRoZSBib3ggc2hvdWxkIHN0YW5kIHNxdWFyZSBhbmQgZXNzZW50aWFsIGluZm9ybWF0aW9uIHNob3VsZCBub3QgZGlzYXBwZWFyIGFyb3VuZCBhbiBlZGdlIG9yIHVuZGVyIGEgY2xvc3VyZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb3Igc2hlbGYgZGlzcGxheSwgY29uc2lkZXI6PC9zcGFuPg0KPHVsPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBvcmllbnRhdGlvbiBpbiB3aGljaCB0aGUgcmV0YWlsZXIgd2lsbCBwbGFjZSB0aGUgcHJvZHVjdC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Ib3cgbWFueSB1bml0cyBtdXN0IGZpdCBhY3Jvc3MgYSBzaGVsZiBvciBpbnNpZGUgYSBkaXNwbGF5IHRyYXkuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciB0aGUgcGFja2FnZSBjYW4gYmUgc3RhY2tlZCB3aXRob3V0IGRhbWFnaW5nIHRoZSBmaW5pc2guPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciBhIHdpbmRvdyBpcyBuZWNlc3Nhcnkgb3Igd2hldGhlciBpdCB3ZWFrZW5zIHRoZSBzdHJ1Y3R1cmUgd2l0aG91dCBhZGRpbmcgdXNlZnVsIGluZm9ybWF0aW9uLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZXRoZXIgdGhlIGJhcmNvZGUsIHByb2R1Y3QgaW5mb3JtYXRpb24gYW5kIHJlcXVpcmVkIG1hcmtldC1zcGVjaWZpYyBjb3B5IGhhdmUgc3VmZmljaWVudCBzcGFjZS48L3NwYW4+PC9saT4NCjwvdWw+DQo8aDM+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRpcmVjdC10by1jb25zdW1lciBhbmQgZS1jb21tZXJjZSBwYWNrYWdpbmc8L3NwYW4+PC9oMz4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BbiBlLWNvbW1lcmNlIHBhY2thZ2UgZXhwZXJpZW5jZXMgbW9yZSBpbmRpdmlkdWFsIGhhbmRsaW5nIHRoYW4gYSBwcm9kdWN0IGRlbGl2ZXJlZCBpbiBhIHJldGFpbCBjYXNlLiBUaGUgcGFja2FnZSBtYXkgYmUgZHJvcHBlZCwgdHVybmVkLCBjb21wcmVzc2VkIG9yIHBsYWNlZCBiZXNpZGUgaGVhdmllciBwYXJjZWxzLiBBIGRlY29yYXRpdmUgcGFwZXIgYm94IHNob3VsZCB0aGVyZWZvcmUgYmUgZXZhbHVhdGVkIHRvZ2V0aGVyIHdpdGggaXRzIG91dGVyIHNoaXBwZXIsIHZvaWQgY29udHJvbCBhbmQgY2xvc3VyZSBtZXRob2QuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9yIHByb2R1Y3RzIHNvbGQgYm90aCBvbmxpbmUgYW5kIGluIHN0b3JlcywgaXQgaXMgb2Z0ZW4gbW9yZSBlZmZpY2llbnQgdG8gbWFpbnRhaW4gb25lIGNvbnNpc3RlbnQgcmV0YWlsIHBhY2thZ2UgYW5kIGRldmVsb3AgYSBzZXBhcmF0ZSBjb3JydWdhdGVkIHNoaXBwaW5nIHN5c3RlbSBhcm91bmQgaXQuIFRoaXMgcHJvdGVjdHMgdGhlIHZpc3VhbCBwYWNrYWdlIHdpdGhvdXQgZm9yY2luZyBldmVyeSByZXRhaWwgdW5pdCB0byBjYXJyeSB1bm5lY2Vzc2FyeSBzaGlwcGluZyBtYXRlcmlhbC48L3NwYW4+DQo8aDM+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldob2xlc2FsZSBhbmQgZXhwb3J0IHBhY2thZ2luZzwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldob2xlc2FsZSBidXllcnMgYWxzbyBuZWVkIHRvIGNvbnNpZGVyIGhvdyBpbmRpdmlkdWFsIGJveGVzIGludGVyYWN0IGluc2lkZSB0aGUgbWFzdGVyIGNhcnRvbi4gQSBzdHJvbmcgcmV0YWlsIGJveCBtYXkgc3RpbGwgYXJyaXZlIHdpdGggcnViYmVkIGNvcm5lcnMgb3IgY29tcHJlc3NlZCBsaWRzIGlmIHRoZSB1bml0cyBoYXZlIHRvbyBtdWNoIHNwYWNlLCBhcmUgcGFja2VkIGluIHRoZSB3cm9uZyBvcmllbnRhdGlvbiBvciBjYXJyeSBleGNlc3NpdmUgc3RhY2tpbmcgcHJlc3N1cmUuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIG1hc3Rlci1jYXJ0b24gcGxhbiBzaG91bGQgc3BlY2lmeSB1bml0IGNvdW50LCBvcmllbnRhdGlvbiwgaW50ZXJuYWwgZGl2aWRlcnMgd2hlbiBuZWNlc3NhcnkgYW5kIGFjY2VwdGFibGUgZW1wdHkgc3BhY2UuIFN0YW5kYXJkaXppbmcgYm94IGZvb3RwcmludHMgYWNyb3NzIGEgcHJvZHVjdCBmYW1pbHkgY2FuIGFsc28gaW1wcm92ZSBjYXJ0b24gdXRpbGl6YXRpb24sIGJ1dCBpdCBzaG91bGQgbm90IGJlIGRvbmUgYnkgZm9yY2luZyBkaWZmZXJlbnQgcHJvZHVjdHMgaW50byBvbmUgb3ZlcnNpemVkIGluc2VydC48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlVzZSBGaW5pc2hpbmcgdG8gU29sdmUgYSBEaXNwbGF5IG9yIEhhbmRsaW5nIFByb2JsZW08L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5GaW5pc2hpbmcgc2hvdWxkIHN1cHBvcnQgYSBjbGVhciBvYmplY3RpdmUuIEl0IG1heSBwcm90ZWN0IHRoZSBwcmludGVkIHN1cmZhY2UsIGNyZWF0ZSBjb250cmFzdCwgaGVscCBhIGxvZ28gc3RhbmQgb3V0IG9yIHByb3ZpZGUgYSB0YWN0aWxlIGN1ZS4gQWRkaW5nIHNldmVyYWwgcHJlbWl1bSBwcm9jZXNzZXMgd2l0aG91dCBhIGhpZXJhcmNoeSBjYW4gaW5jcmVhc2UgY29zdCBhbmQgcHJvZHVjdGlvbiBjb21wbGV4aXR5IHdoaWxlIG1ha2luZyB0aGUgcGFja2FnZSBsb29rIGxlc3MgY29udHJvbGxlZC48L3NwYW4+DQo8dGFibGU+DQo8dGhlYWQ+DQo8dHI+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkZpbmlzaDwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Vc2VmdWwgd2hlbjwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UcmFkZS1vZmYgdG8gZXZhbHVhdGU8L3NwYW4+PC90aD4NCjwvdHI+DQo8L3RoZWFkPg0KPHRib2R5Pg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NYXR0ZSBsYW1pbmF0aW9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgc21vb3RoLCBjb250cm9sbGVkIHN1cmZhY2UgYW5kIGFkZGVkIHByaW50IHByb3RlY3Rpb24gYXJlIHJlcXVpcmVkPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRhcmsgYXJlYXMgYW5kIGNvcm5lcnMgc2hvdWxkIGJlIGNoZWNrZWQgZm9yIHNjdWZmaW5nIGFuZCB2aXNpYmxlIGhhbmRsaW5nIG1hcmtzPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5HbG9zcyBsYW1pbmF0aW9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkhpZ2ggY29sb3Igc2F0dXJhdGlvbiwgd2lwZWFiaWxpdHkgb3IgYSBicmlnaHRlciByZXRhaWwgYXBwZWFyYW5jZSBpcyBwcmVmZXJyZWQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmVmbGVjdGlvbnMgY2FuIHJlZHVjZSByZWFkYWJpbGl0eSBhbmQgbWF5IGNvbmZsaWN0IHdpdGggYSBuYXR1cmFsIGxpZmVzdHlsZSBwb3NpdGlvbjwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VW5jb2F0ZWQgb3IgdGV4dHVyZWQgcGFwZXI8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGJyYW5kIG5lZWRzIGEgbmF0dXJhbCwgd2FybSBvciB0YWN0aWxlIHByZXNlbnRhdGlvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db2xvciByZXByb2R1Y3Rpb24sIHJ1YmJpbmcgYW5kIHN0YWluIHJlc2lzdGFuY2UgcmVxdWlyZSBjYXJlZnVsIHNhbXBsaW5nPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb2lsIHN0YW1waW5nPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgbG9nbyBvciBzbWFsbCBncmFwaGljIGVsZW1lbnQgbmVlZHMgY29udHJvbGxlZCBtZXRhbGxpYyBjb250cmFzdDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5MYXJnZSBmb2lsIGFyZWFzLCBmaW5lIGRldGFpbHMgYW5kIGZvbGQgcG9zaXRpb25zIGNhbiBjcmVhdGUgcHJvZHVjdGlvbiByaXNrczwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RW1ib3NzaW5nIG9yIGRlYm9zc2luZzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHN1YnRsZSB0YWN0aWxlIGZlYXR1cmUgY2FuIGNvbW11bmljYXRlIHF1YWxpdHkgd2l0aG91dCBhZGRpdGlvbmFsIGNvbG9yPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRlcHRoLCBwYXBlciBzZWxlY3Rpb24gYW5kIHRoZSByZXZlcnNlLXNpZGUgaW1wcmVzc2lvbiBtdXN0IGJlIHJldmlld2VkPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TcG90IFVWPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgc3BlY2lmaWMgcGF0dGVybiBvciBsb2dvIG5lZWRzIGdsb3NzIGNvbnRyYXN0IG92ZXIgYSBtYXR0ZSBiYWNrZ3JvdW5kPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJlZ2lzdHJhdGlvbiBhbmQgc3VyZmFjZSBjb25zaXN0ZW5jeSBzaG91bGQgYmUgY2hlY2tlZCBvbiB0aGUgcHJpbnRlZCBzYW1wbGU8L3NwYW4+PC90ZD4NCjwvdHI+DQo8L3Rib2R5Pg0KPC90YWJsZT4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb3IgbW9zdCBob21lIGFuZCBsaWZlc3R5bGUgcHJvZHVjdHMsIG9uZSBkb21pbmFudCBtYXRlcmlhbCBkaXJlY3Rpb24gYW5kIG9uZSBzdXBwb3J0aW5nIGZpbmlzaCBhcmUgZWFzaWVyIHRvIGNvbnRyb2wgdGhhbiBhIGNvbWJpbmF0aW9uIG9mIGZvaWwsIGVtYm9zc2luZywgZ2xvc3MgZWZmZWN0cyBhbmQgbXVsdGlwbGUgdGV4dHVyZXMuIEEgbmF0dXJhbCBjYW5kbGUgb3IgbGluZW4gYnJhbmQgbWF5IGJlbmVmaXQgbW9yZSBmcm9tIHRleHR1cmVkIHBhcGVyIGFuZCByZXN0cmFpbmVkIHByaW50aW5nIHRoYW4gZnJvbSBhIGhpZ2hseSByZWZsZWN0aXZlIHN1cmZhY2UuIEEgZm9ybWFsIGhvbWUtZnJhZ3JhbmNlIGdpZnQgc2V0IG1heSBqdXN0aWZ5IGEgc21hbGwgZm9pbCBsb2dvIG9yIGVtYm9zc2VkIHBhdHRlcm4gaWYgdGhlIGluc2VydCBhbmQgYm94IHN0cnVjdHVyZSBhbHJlYWR5IHBlcmZvcm0gY29ycmVjdGx5Ljwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF80IC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2V0IERpbWVuc2lvbnMgRnJvbSBSZWFsIFByb2R1Y3Rpb24gU2FtcGxlczwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk5vbWluYWwgcHJvZHVjdCBkaW1lbnNpb25zIGFyZSBub3QgYWx3YXlzIHN1ZmZpY2llbnQgZm9yIHBhY2thZ2luZyBkZXZlbG9wbWVudC4gSGFuZG1hZGUgY2VyYW1pY3MsIGZpbGxlZCB0ZXh0aWxlIHByb2R1Y3RzLCBhc3NlbWJsZWQgYWNjZXNzb3JpZXMgYW5kIGNvbXBvbmVudHMgZnJvbSBkaWZmZXJlbnQgc3VwcGxpZXJzIGNhbiB2YXJ5IGJldHdlZW4gcHJvZHVjdGlvbiBiYXRjaGVzLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkJlZm9yZSBmaW5hbGl6aW5nIHRoZSBkaWVsaW5lLCBtZWFzdXJlIHNldmVyYWwgcmVwcmVzZW50YXRpdmUgcHJvZHVjdHMgYW5kIHJlY29yZCB0aGUgc21hbGxlc3QsIGF2ZXJhZ2UgYW5kIGxhcmdlc3QgZGltZW5zaW9ucy4gSW5jbHVkZSBhdHRhY2hlZCBsYWJlbHMsIGNhcHMsIHByb3RlY3RpdmUgc2xlZXZlcywgY2FibGVzLCBpbnN0cnVjdGlvbiBjYXJkcyBhbmQgYW55IGFjY2Vzc29yeSB0aGF0IHdpbGwgYmUgcGFja2VkIHdpdGggdGhlIHByb2R1Y3QuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hlbiBzZXR0aW5nIHRoZSBpbnRlcm5hbCBkaW1lbnNpb25zLCBhbGxvdyBmb3I6PC9zcGFuPg0KPHVsPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk5vcm1hbCB2YXJpYXRpb24gYmV0d2VlbiBwcm9kdWN0IHVuaXRzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSB0aGlja25lc3Mgb2YgdGlzc3VlLCBzbGVldmVzIG9yIHByb3RlY3RpdmUgd3JhcHBpbmcuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U3BhY2UgbmVlZGVkIHRvIHJlbW92ZSB0aGUgcHJvZHVjdCB3aXRob3V0IHB1bGxpbmcgb24gZnJhZ2lsZSBwYXJ0cy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JbnNlcnQgZm9sZCBsaW5lcyBhbmQgbWF0ZXJpYWwgdGhpY2tuZXNzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlBvdGVudGlhbCBjb21wcmVzc2lvbiBvZiBzb2Z0IHByb2R1Y3RzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNsZWFyYW5jZSBiZXR3ZWVuIGZyYWdpbGUgY29tcG9uZW50cyBhbmQgdGhlIGJveCB3YWxsLjwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BbiBpbnNlcnQgdGhhdCBmaXRzIG9uZSBpZGVhbCBzYW1wbGUgcGVyZmVjdGx5IG1heSBiZSB0b28gdGlnaHQgZm9yIHRoZSBuZXh0IHByb2R1Y3Rpb24gYmF0Y2guIFRoZSBvcHBvc2l0ZSBwcm9ibGVtIGlzIGFsc28gY29tbW9uOiBleHRyYSBjbGVhcmFuY2UgaXMgYWRkZWQgZXZlcnl3aGVyZSwgbGVhdmluZyB0aGUgcHJvZHVjdCB2aXNpYmx5IGxvb3NlLiBUaGUgYmV0dGVyIGFwcHJvYWNoIGlzIHRvIGRlZmluZSB3aGVyZSBjbGVhcmFuY2UgaXMgbmVjZXNzYXJ5IGFuZCB3aGVyZSB0aGUgaW5zZXJ0IG11c3QgbWFrZSBjb250cm9sbGVkIGNvbnRhY3QuPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BcHByb3ZlIHRoZSBTYW1wbGUgQWdhaW5zdCBhIERlZmluZWQgQ2hlY2tsaXN0PC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBzYW1wbGUgc2hvdWxkIGJlIHJldmlld2VkIGFzIGEgcGFja2FnaW5nIHN5c3RlbSwgbm90IG9ubHkgYXMgYSBwcmludGVkIG9iamVjdC4gUGxhY2UgdGhlIGFjdHVhbCBwcm9kdWN0IGluc2lkZSBpdCwgYWRkIGV2ZXJ5IGFjY2Vzc29yeSBhbmQgc2ltdWxhdGUgaG93IHRoZSBjb21wbGV0ZSBwYWNrIHdpbGwgYmUgaGFuZGxlZC48L3NwYW4+DQo8b2w+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5Mb2FkZWQgc3RydWN0dXJlOjwvc3Ryb25nPiBDb25maXJtIHRoYXQgdGhlIGJhc2UsIHdhbGxzIGFuZCBjbG9zdXJlIGhvbGQgdGhlIGNvbXBsZXRlIHBhY2tlZCB3ZWlnaHQuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5Qcm9kdWN0IGZpdDo8L3N0cm9uZz4gQ2hlY2sgc2lkZSBtb3ZlbWVudCwgdmVydGljYWwgbGlmdCwgcm90YXRpb24gYW5kIGNvbXBvbmVudC10by1jb21wb25lbnQgY29udGFjdC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPlJlbW92YWw6PC9zdHJvbmc+IEVuc3VyZSB0aGUgY3VzdG9tZXIgY2FuIHJlbW92ZSB0aGUgcHJvZHVjdCB3aXRob3V0IGRhbWFnaW5nIGl0IG9yIHB1bGxpbmcgb24gYSBmcmFnaWxlIHBhcnQuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5PcGVuaW5nIGFuZCBjbG9zaW5nOjwvc3Ryb25nPiBSZXBlYXQgdGhlIG9wZW5pbmcgYWN0aW9uIGFuZCBjaGVjayB3aGV0aGVyIGZsYXBzLCBoaW5nZXMsIHRhYnMgb3IgbWFnbmV0aWMgYXJlYXMgcmVtYWluIGFsaWduZWQuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5QcmludCBhbmQgY29sb3I6PC9zdHJvbmc+IFJldmlldyBzb2xpZCBjb2xvcnMsIGZpbmUgdGV4dCwgZ3JhZGllbnRzIGFuZCBuZXV0cmFsIHRvbmVzIHVuZGVyIHJlYWxpc3RpYyBsaWdodGluZy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkZpbmlzaGluZzo8L3N0cm9uZz4gSW5zcGVjdCBmb2lsIGVkZ2VzLCBlbWJvc3NlZCBkZXRhaWxzLCBsYW1pbmF0ZWQgY29ybmVycyBhbmQgaGlnaC1jb250YWN0IHN1cmZhY2VzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+VHJhbnNpdCBwcmVwYXJhdGlvbjo8L3N0cm9uZz4gUGxhY2UgdGhlIHJldGFpbCBwYWNrYWdlIGluc2lkZSB0aGUgcHJvcG9zZWQgc2hpcHBlciBvciBtYXN0ZXIgY2FydG9uIGFuZCByZXZpZXcgdm9pZCBzcGFjZSBhbmQgb3JpZW50YXRpb24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5QYWNrLW91dCBwcm9jZXNzOjwvc3Ryb25nPiBDb25maXJtIHRoYXQgZmFjdG9yeSBzdGFmZiBjYW4gaW5zZXJ0IHRoZSBwcm9kdWN0IGNvbnNpc3RlbnRseSB3aXRob3V0IGZvcmNpbmcsIGJlbmRpbmcgb3Igc2NyYXRjaGluZyBjb21wb25lbnRzLjwvc3Bhbj48L2xpPg0KPC9vbD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TZXZlcmFsIHJlZCBmbGFncyBzaG91bGQgc3RvcCBhcHByb3ZhbDogdGhlIHN1cHBsaWVyIGhhcyBvbmx5IHNob3duIGFuIGVtcHR5IGJveCwgdGhlIGluc2VydCBoYXMgbm90IGJlZW4gdGVzdGVkIHdpdGggdGhlIGFjdHVhbCBwcm9kdWN0LCBhIGZyYWdpbGUgY29tcG9uZW50IHRvdWNoZXMgdGhlIG91dGVyIHdhbGwsIG9yIHRoZSByZXRhaWwgcGFja2FnZSBoYXMgYmVlbiBhcHByb3ZlZCB3aXRob3V0IGFueSBtYXN0ZXItY2FydG9uIHBsYW4uPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TZW5kIGEgUGFja2FnaW5nIEJyaWVmIFRoYXQgUHJldmVudHMgQXZvaWRhYmxlIFJldmlzaW9uczwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgbWFudWZhY3R1cmVyIGNhbiBwcm92aWRlIGEgbW9yZSB1c2VmdWwgc3RydWN0dXJhbCByZWNvbW1lbmRhdGlvbiB3aGVuIHRoZSBpbnF1aXJ5IGluY2x1ZGVzIHByb2R1Y3QgYW5kIGRpc3RyaWJ1dGlvbiBpbmZvcm1hdGlvbiByYXRoZXIgdGhhbiBvbmx5IGEgcmVmZXJlbmNlIGltYWdlLjwvc3Bhbj4NCjx0YWJsZT4NCjx0aGVhZD4NCjx0cj4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SW5mb3JtYXRpb24gdG8gcHJvdmlkZTwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaHkgaXQgbWF0dGVyczwvc3Bhbj48L3RoPg0KPC90cj4NCjwvdGhlYWQ+DQo8dGJvZHk+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb2R1Y3QgbmFtZSBhbmQgY2F0ZWdvcnk8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2xhcmlmaWVzIGhvdyB0aGUgcGFja2FnZSB3aWxsIGJlIGhhbmRsZWQgYW5kIHByZXNlbnRlZDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QWN0dWFsIHByb2R1Y3QgZGltZW5zaW9uczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EZWZpbmVzIGludGVybmFsIHNwYWNlIGFuZCBpbnNlcnQgZ2VvbWV0cnk8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk5ldCBhbmQgcGFja2VkIHdlaWdodDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JbmZsdWVuY2VzIGJvYXJkIHN0cmVuZ3RoLCBiYXNlIHN1cHBvcnQgYW5kIHNoaXBwaW5nIHN0cnVjdHVyZTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TWF0ZXJpYWwgYW5kIGZyYWdpbGUgYXJlYXM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SWRlbnRpZmllcyBzdXJmYWNlcywgaGFuZGxlcywgcmltcywgY29ybmVycyBvciBnbGFzcyBjb21wb25lbnRzIHJlcXVpcmluZyBwcm90ZWN0aW9uPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5OdW1iZXIgb2YgY29tcG9uZW50czwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EZXRlcm1pbmVzIHdoZXRoZXIgZGl2aWRlcnMsIGxheWVycyBvciBzZXBhcmF0ZSBjYXZpdGllcyBhcmUgcmVxdWlyZWQ8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJldGFpbCwgZS1jb21tZXJjZSBvciB3aG9sZXNhbGUgY2hhbm5lbDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5DaGFuZ2VzIGRpc3BsYXksIGhhbmRsaW5nIGFuZCBvdXRlci1wYWNrYWdpbmcgcmVxdWlyZW1lbnRzPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UYXJnZXQgbWFya2V0PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb3ZpZGVzIHNwYWNlLXBsYW5uaW5nIGNvbnRleHQgZm9yIHByb2R1Y3QgY29weSwgYmFyY29kZXMgYW5kIG1hcmtldCBpbmZvcm1hdGlvbjwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHJlZmVycmVkIGJveCBzdHlsZTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db21tdW5pY2F0ZXMgdGhlIHZpc3VhbCBkaXJlY3Rpb24gd2l0aG91dCBwcmV2ZW50aW5nIHN0cnVjdHVyYWwgYWx0ZXJuYXRpdmVzPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5QcmludGluZyBhbmQgZmluaXNoaW5nIHJlZmVyZW5jZXM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SGVscHMgdGhlIHN1cHBsaWVyIGV2YWx1YXRlIHByb2R1Y3Rpb24gZmVhc2liaWxpdHkgYW5kIHNhbXBsZSByZXF1aXJlbWVudHM8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk9yZGVyIHF1YW50aXR5IGFuZCBkZXN0aW5hdGlvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TdXBwb3J0cyBtYXRlcmlhbCBwbGFubmluZywgcGFja2luZyBjb25maWd1cmF0aW9uIGFuZCBxdW90YXRpb248L3NwYW4+PC90ZD4NCjwvdHI+DQo8L3Rib2R5Pg0KPC90YWJsZT4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHVzZWZ1bCBpbnF1aXJ5IGNhbiBiZSBzdW1tYXJpemVkIGluIG9uZSBzZW50ZW5jZTog4oCcVGhpcyBpcyBhIGdsYXNzIGhvbWUtZnJhZ3JhbmNlIHNldCBjb250YWluaW5nIG9uZSBjYW5kbGUgYW5kIG9uZSBkaWZmdXNlciBib3R0bGUsIHdpdGggdGhlIGZvbGxvd2luZyBkaW1lbnNpb25zIGFuZCBwYWNrZWQgd2VpZ2h0LCBzb2xkIHRocm91Z2ggcmV0YWlsIGFuZCBlLWNvbW1lcmNlLCByZXF1aXJpbmcgc2VwYXJhdGUgY2F2aXRpZXMgYW5kIGEgbWF0dGUgcGFwZXIgZmluaXNoLuKAnSBUaGF0IGJyaWVmIGdpdmVzIHRoZSBzdXBwbGllciBtb3JlIHRlY2huaWNhbCBkaXJlY3Rpb24gdGhhbiBhIHJlcXVlc3QgZm9yIOKAnGEgcHJlbWl1bSBnaWZ0IGJveC7igJ08L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgUHJhY3RpY2FsIFNlbGVjdGlvbiBTZXF1ZW5jZTwvc3Bhbj48L2gyPg0KPG9sPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJlY29yZCB0aGUgYWN0dWFsIHByb2R1Y3QgZGltZW5zaW9ucywgcGFja2VkIHdlaWdodCBhbmQgY29tcG9uZW50IGNvdW50Ljwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPklkZW50aWZ5IHRoZSBtb3N0IGxpa2VseSBmYWlsdXJlIG1vZGU6IGNydXNoaW5nLCBpbXBhY3QsIG1vdmVtZW50LCBzY3JhdGNoaW5nIG9yIHBvb3IgcHJlc2VudGF0aW9uLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRlY2lkZSB3aGV0aGVyIHRoZSByZXRhaWwgcGFja2FnZSBhbmQgc2hpcHBpbmcgcGFja2FnZSBjYW4gYmUgdGhlIHNhbWUgc3RydWN0dXJlLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNlbGVjdCB0aGUgYm94IGZhbWlseTogZm9sZGluZyBjYXJ0b24sIHJpZ2lkIGJveCwgZHJhd2VyIGJveCwgY29ycnVnYXRlZCBtYWlsZXIgb3IgYW5vdGhlciBwYXBlci1iYXNlZCBzdHJ1Y3R1cmUuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RGVzaWduIHRoZSBpbnNlcnQgYXJvdW5kIG1vdmVtZW50IGNvbnRyb2wgYW5kIHByb2R1Y3QgcmVtb3ZhbC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db25maXJtIHNoZWxmLCBlLWNvbW1lcmNlLCB3aG9sZXNhbGUgYW5kIG1hc3Rlci1jYXJ0b24gcmVxdWlyZW1lbnRzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNob29zZSBmaW5pc2hpbmcgb25seSBhZnRlciB0aGUgc3RydWN0dXJlIGFuZCBwcm9kdWN0IGZpdCBhcmUgc3RhYmxlLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFwcHJvdmUgYSBsb2FkZWQgc2FtcGxlIGFuZCBkb2N1bWVudCB0aGUgYWNjZXB0YW5jZSBjcml0ZXJpYSBiZWZvcmUgcHJvZHVjdGlvbi48L3NwYW4+PC9saT4NCjwvb2w+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hlbiByZXF1ZXN0aW5nIGEgcmVjb21tZW5kYXRpb24sIHNlbmQgdGhlIHByb2R1Y3QgZGltZW5zaW9ucywgd2VpZ2h0LCBmcmFnaWxlIGFyZWFzLCBzYWxlcyBjaGFubmVsLCBkZXN0aW5hdGlvbiBhbmQgcmVmZXJlbmNlIGltYWdlcy4gQSBxdWFsaWZpZWQgPGEgaHJlZj0iaHR0cHM6Ly9ob3BnaWF5dnBuLmNvbS9jdXN0b20tcGFja2FnaW5nLWJveGVzLW1hbnVmYWN0dXJlci8iPnBhY2thZ2luZyBib3hlcyBtYW51ZmFjdHVyZXI8L2E+IGNhbiB0aGVuIGNvbXBhcmUgc3VpdGFibGUgc3RydWN0dXJlcywgaW5zZXJ0IGRpcmVjdGlvbnMgYW5kIGZpbmlzaGluZyBvcHRpb25zIGluc3RlYWQgb2YgcXVvdGluZyBhbiBhdHRyYWN0aXZlIGJveCB0aGF0IG1heSBub3QgcGVyZm9ybSBpbiByZWFsIHVzZS48L3NwYW4+', true);

    return is_string($content) ? $content : '';
}
