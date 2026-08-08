<?php
/**
 * Syncs the prepared shoe-box dimensions post with existing Media Library images.
 * No post or image files are created when the prepared local/hosting records are missing.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_SHOE_BOX_DIMENSIONS_SYNC_VERSION = '2026-08-08-v1';
const CUSTOM_BOX_SHOE_BOX_DIMENSIONS_VERSION_OPTION = 'custom_box_shoe_box_dimensions_sync_version';
const CUSTOM_BOX_SHOE_BOX_DIMENSIONS_NOTICE_OPTION = 'custom_box_shoe_box_dimensions_sync_notice';
const CUSTOM_BOX_SHOE_BOX_DIMENSIONS_MISSING_IMAGES_OPTION = 'custom_box_shoe_box_dimensions_missing_images';
const CUSTOM_BOX_SHOE_BOX_DIMENSIONS_MISSING_SLOTS_OPTION = 'custom_box_shoe_box_dimensions_missing_slots';
const CUSTOM_BOX_SHOE_BOX_DIMENSIONS_VALIDATION_FAILURES_OPTION = 'custom_box_shoe_box_dimensions_validation_failures';

add_action('admin_init', 'custom_box_sync_shoe_box_dimensions_post');
add_action('admin_notices', 'custom_box_shoe_box_dimensions_admin_notice');

function custom_box_sync_shoe_box_dimensions_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_shoe_box_dimensions_post_data();
    $post = custom_box_find_shoe_box_dimensions_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_SHOE_BOX_DIMENSIONS_SYNC_VERSION === get_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_VERSION_OPTION)
        && $post
        && custom_box_shoe_box_dimensions_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_shoe_box_dimensions_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_VERSION_OPTION);
        update_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_shoe_box_dimensions_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_VERSION_OPTION, CUSTOM_BOX_SHOE_BOX_DIMENSIONS_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Shoe box dimensions post synced: post ID %d, featured image %d, 4 inline figures and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_VERSION_OPTION);
    update_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Shoe box dimensions sync is incomplete. Missing images: '
            . implode(', ', (array) get_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_MISSING_IMAGES_OPTION, array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_MISSING_SLOTS_OPTION, array()),
                (array) get_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_VALIDATION_FAILURES_OPTION, array())
            )),
    ), false);
}

function custom_box_shoe_box_dimensions_post_data(): array
{
    return array(
        'title' => 'What Are the Dimensions of a Shoe Box? Common Sizes by Product Type',
        'slug' => 'shoe-box-dimensions',
        'excerpt' => 'Learn common shoe box dimensions for sneakers, dress shoes, kids\' shoes and boots, plus how to measure internal size, set clearance and approve custom packaging.',
        'category' => array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
        'tags' => array(
            'Shoe Packaging' => 'shoe-packaging',
            'Box Dimensions' => 'box-dimensions',
            'Custom Packaging' => 'custom-packaging',
            'Footwear Packaging' => 'footwear-packaging',
        ),
        'seo_title' => 'Shoe Box Dimensions: Common Sizes & How to Measure',
        'seo_description' => 'See common shoe box dimensions by footwear type and learn how to measure internal size, allow clearance and specify a custom shoe box correctly.',
        'focus_keyword' => 'what are the dimensions of a shoe box',
    );
}

function custom_box_shoe_box_dimensions_images(): array
{
    return array(
        'featured' => array(
            'base' => 'shoe-box-dimensions-size-guide',
            'alt' => 'Common shoe box dimensions for different footwear types',
            'title' => 'Shoe Box Dimensions Size Guide',
            'caption' => 'Common shoe box dimensions vary by footwear type, fit and required clearance.',
        ),
        'slot_1' => array(
            'base' => 'common-shoe-box-sizes-by-footwear',
            'alt' => 'Shoe box sizes for sneakers, dress shoes, kids shoes and boots',
            'title' => 'Common Shoe Box Sizes by Footwear',
            'caption' => 'Compare starting shoe-box sizes by footwear type before confirming the finished product fit.',
        ),
        'slot_2' => array(
            'base' => 'how-to-measure-shoes-for-box-size',
            'alt' => 'Measuring packed shoes for custom shoe box dimensions',
            'title' => 'How to Measure Shoes for Box Size',
            'caption' => 'Measure the packed footwear at its maximum points and specify usable internal clearance.',
        ),
        'slot_3' => array(
            'base' => 'internal-vs-external-shoe-box-dimensions',
            'alt' => 'Internal and external dimensions of a shoe box',
            'title' => 'Internal vs External Shoe Box Dimensions',
            'caption' => 'Internal dimensions control product fit; board thickness and construction affect external size.',
        ),
        'slot_4' => array(
            'base' => 'shoe-box-fit-sample-approval',
            'alt' => 'Shoe box sample fit and lid clearance inspection',
            'title' => 'Shoe Box Fit Sample Approval',
            'caption' => 'Approve a physical sample to confirm shoe fit, lid clearance and assembled box behavior.',
        ),
    );
}

function custom_box_find_shoe_box_dimensions_post(string $slug, string $title): ?WP_Post
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

function custom_box_upsert_shoe_box_dimensions_post()
{
    $data = custom_box_shoe_box_dimensions_post_data();
    $post = custom_box_find_shoe_box_dimensions_post($data['slug'], $data['title']);
    if (!$post) {
        return new WP_Error('shoe_box_dimensions_target_missing', 'Prepared shoe box dimensions draft was not found; no new post was created.');
    }

    $payload = array(
        'ID' => (int) $post->ID,
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_type' => 'post',
        'post_excerpt' => $data['excerpt'],
        'post_status' => in_array($post->post_status, array('publish', 'private'), true) ? $post->post_status : 'draft',
    );
    if (custom_box_shoe_box_dimensions_content_needs_restore($post->post_content)) {
        $payload['post_content'] = custom_box_shoe_box_dimensions_content();
    }

    $result = wp_update_post($payload, true);
    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_shoe_box_dimensions_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_shoe_box_dimensions_images($post_id);

    return $post_id;
}

function custom_box_shoe_box_dimensions_content_needs_restore(string $content): bool
{
    if ('' === trim($content) || false !== strpos($content, 'IMAGE_SLOT_')) {
        return true;
    }
    if (4 !== substr_count($content, '<!-- shoe-box-dimensions-image:')) {
        return true;
    }
    if (4 !== preg_match_all('/<figure\\b/i', $content, $unused)) {
        return true;
    }
    return 4 !== preg_match_all('/<img\\s/i', $content, $unused);
}

function custom_box_sync_shoe_box_dimensions_terms(int $post_id, array $data): void
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

function custom_box_sync_shoe_box_dimensions_images(int $post_id): void
{
    $images = custom_box_shoe_box_dimensions_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_shoe_box_dimensions_attachment($image['base']);
        if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
            $missing_images[] = $image['base'];
            if ('featured' !== $key) {
                $missing_slots[] = $key;
            }
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

        $marker = '<!-- shoe-box-dimensions-image:' . $key . ' -->';
        $url = wp_get_attachment_url($attachment_id);
        $figure = $marker . "\n<figure><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\\s*<figure\\b.*?<\\/figure>/is';
        $wrapped_pattern = '/<span\\b[^>]*>\\s*' . preg_quote($slot, '/') . '\\s*<\\/span>/i';
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
    update_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_MISSING_IMAGES_OPTION, array_values(array_unique($missing_images)), false);
    update_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_MISSING_SLOTS_OPTION, array_values(array_unique($missing_slots)), false);
}

function custom_box_find_shoe_box_dimensions_attachment(string $base): int
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

function custom_box_shoe_box_dimensions_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_shoe_box_dimensions_post_data();
    $images = custom_box_shoe_box_dimensions_images();
    $failures = array();
    if (!$post || $data['slug'] !== $post->post_name || $data['title'] !== $post->post_title || $data['excerpt'] !== $post->post_excerpt) {
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
    if (4 !== substr_count($content, '<!-- shoe-box-dimensions-image:') || 4 !== preg_match_all('/<figure\\b/i', $content, $unused) || 4 !== preg_match_all('/<img\\s/i', $content, $unused)) {
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
    if ($data['seo_title'] !== get_post_meta($post_id, 'rank_math_title', true) || $data['seo_description'] !== get_post_meta($post_id, 'rank_math_description', true) || $data['focus_keyword'] !== get_post_meta($post_id, 'rank_math_focus_keyword', true)) {
        $failures[] = 'Rank Math metadata';
    }
    if ((array) get_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_MISSING_IMAGES_OPTION, array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_MISSING_SLOTS_OPTION, array())) {
        $failures[] = 'missing slots';
    }
    update_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_VALIDATION_FAILURES_OPTION, array_values(array_unique($failures)), false);
    return !$failures;
}

function custom_box_shoe_box_dimensions_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $notice = get_option(CUSTOM_BOX_SHOE_BOX_DIMENSIONS_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }
    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}

function custom_box_shoe_box_dimensions_content(): string
{
    $content = base64_decode('PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgdHlwaWNhbCBhZHVsdCBzaG9lIGJveCBpcyByb3VnaGx5IDEy4oCTMTQgaW5jaGVzICgzMOKAkzM2IGNtKSBsb25nLCA34oCTOSBpbmNoZXMgKDE44oCTMjMgY20pIHdpZGUsIGFuZCA04oCTNiBpbmNoZXMgKDEw4oCTMTUgY20pIGhpZ2guIEJ1dCB0aGVyZSBpcyBubyB1bml2ZXJzYWwgc2hvZSBib3ggZGltZW5zaW9uOiBhIHNsaW0gcGFpciBvZiBmbGF0cywgY2h1bmt5IHNuZWFrZXJzLCBoaWtpbmcgc2hvZXMsIGFuZCB0YWxsIGJvb3RzIGNhbiByZXF1aXJlIHZlcnkgZGlmZmVyZW50IGludGVybmFsIHNwYWNlIGV2ZW4gd2hlbiB0aGUgZm9vdHdlYXIgY2FycmllcyBhIHNpbWlsYXIgc2l6ZSBudW1iZXIuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9yIGJyYW5kcyBvcmRlcmluZyBjdXN0b20gcGFja2FnaW5nLCB0aG9zZSBjb21tb24gZGltZW5zaW9ucyBhcmUgdXNlZnVsIGFzIGEgc3RhcnRpbmcgcmVmZXJlbmNl4oCUbm90IGFzIGEgcHJvZHVjdGlvbiBzcGVjaWZpY2F0aW9uLiBUaGUgc2FmZXIgbWV0aG9kIGlzIHRvIG1lYXN1cmUgdGhlIHNob2VzIGluIHRoZWlyIGFjdHVhbCBwYWNraW5nIHBvc2l0aW9uLCBlc3RhYmxpc2ggdGhlIHJlcXVpcmVkIGludGVybmFsIGNsZWFyYW5jZSwgc3BlY2lmeSB3aGV0aGVyIGRpbWVuc2lvbnMgYXJlIGludGVybmFsIG9yIGV4dGVybmFsLCBhbmQgYXBwcm92ZSB0aGUgZmluYWwgc2l6ZSB0aHJvdWdoIGEgcGh5c2ljYWwgcGFjay1vdXQgc2FtcGxlLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF8xIC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29tbW9uIHNob2UgYm94IGRpbWVuc2lvbnM6IHVzZSB0aGVzZSBhcyByZWZlcmVuY2Ugc2l6ZXMsIG5vdCBmaXhlZCBzdGFuZGFyZHM8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgdGFibGUgYmVsb3cgc2hvd3MgcHJhY3RpY2FsIHJlZmVyZW5jZSByYW5nZXMgY29tbW9ubHkgZm91bmQgYWNyb3NzIGZvb3R3ZWFyIHBhY2thZ2luZy4gVGhlIGRpbWVuc2lvbnMgYXJlIGRlbGliZXJhdGVseSBwcmVzZW50ZWQgYXMgYXBwcm94aW1hdGUgcmFuZ2VzIGJlY2F1c2Ugc2hvZSBnZW9tZXRyeSB2YXJpZXMgc3Vic3RhbnRpYWxseSBiZXR3ZWVuIGJyYW5kcyBhbmQgcHJvZHVjdCBjYXRlZ29yaWVzLjwvc3Bhbj4NCjx0YWJsZT4NCjx0aGVhZD4NCjx0cj4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9vdHdlYXIgdHlwZTwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BcHByb3guIGJveCBzaXplIGluIGluY2hlcyAoTCDDlyBXIMOXIEgpPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFwcHJveC4gc2l6ZSBpbiBjbTwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NYWluIHNpemluZyBjb25jZXJuPC9zcGFuPjwvdGg+DQo8L3RyPg0KPC90aGVhZD4NCjx0Ym9keT4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V29tZW4ncyBmbGF0cyBhbmQgbG93LXByb2ZpbGUgc2hvZXM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MTLigJMxMyDDlyA34oCTOCDDlyA04oCTNSBpbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij4zMC414oCTMzMgw5cgMTcuOOKAkzIwLjMgw5cgMTAuMuKAkzEyLjcgY208L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QXZvaWQgZXhjZXNzIGhlaWdodCBhbmQgbGF0ZXJhbCBtb3ZlbWVudDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TWVuJ3MgZHJlc3MgYW5kIGNhc3VhbCBzaG9lczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij4xM+KAkzE0IMOXIDfigJM5IMOXIDTigJM1LjUgaW48L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+MzPigJMzNS42IMOXIDE3LjjigJMyMi45IMOXIDEwLjLigJMxNCBjbTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BbGxvdyBmb3Igb3V0c29sZSBsZW5ndGggYW5kIHRvZSB3aWR0aDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U3RhbmRhcmQgc25lYWtlcnM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QWJvdXQgMTPigJMxNCDDlyA44oCTOSDDlyA0LjXigJM2IGluPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFib3V0IDMz4oCTMzUuNiDDlyAyMC4z4oCTMjIuOSDDlyAxMS404oCTMTUuMiBjbTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGlja2VyIHNvbGVzIGFuZCBoZWVsIGNvdW50ZXJzIGluY3JlYXNlIHZvbHVtZTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2hpbGRyZW4ncyBzaG9lczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BYm91dCA44oCTMTAgw5cgNeKAkzYgw5cgM+KAkzQgaW48L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QWJvdXQgMjAuM+KAkzI1LjQgw5cgMTIuN+KAkzE1LjIgw5cgNy424oCTMTAuMiBjbTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5MYXJnZSB2YXJpYXRpb24gYWNyb3NzIGFnZSBhbmQgc2hvZSBzdHlsZTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QW5rbGUgYm9vdHMgLyBidWxreSBmb290d2Vhcjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BYm91dCAxMy414oCTMTUgw5cgOeKAkzEwIMOXIDUuNeKAkzcgaW48L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QWJvdXQgMzQuM+KAkzM4LjEgw5cgMjIuOeKAkzI1LjQgw5cgMTTigJMxNy44IGNtPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNoYWZ0LCBjb2xsYXIgYW5kIG91dHNvbGUgYnVsayBjb250cm9sIHRoZSBib3ggc2l6ZTwvc3Bhbj48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+SW1wb3J0YW50Ojwvc3Ryb25nPiB0aGVzZSBudW1iZXJzIHNob3VsZCBhbnN3ZXIg4oCcd2hhdCBzaXplIGlzIGEgc2hvZSBib3g/4oCdIGF0IGEgcmVmZXJlbmNlIGxldmVsIG9ubHkuIFRoZXkgc2hvdWxkIG5vdCBiZSBjb3BpZWQgZGlyZWN0bHkgaW50byBhIGRpZWxpbmUgb3IgcHVyY2hhc2Ugb3JkZXIuIEEgbm9taW5hbCBVUyBzaXplIDkgc25lYWtlciB3aXRoIGEgdGhpY2sgcGVyZm9ybWFuY2Ugc29sZSBjYW4gb2NjdXB5IG1vcmUgcGFja2FnaW5nIHZvbHVtZSB0aGFuIGFub3RoZXIgVVMgc2l6ZSA5IHNob2Ugd2l0aCBhIG5hcnJvdyBwcm9maWxlLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoYXQgZGlzdGluY3Rpb24gaXMgd2hlcmUgbWFueSBzaG9lLWJveCBzaXppbmcgZ3VpZGVzIHN0b3AgdG9vIGVhcmx5LiBBIGZvb3R3ZWFyIHNpemUgdGVsbHMgeW91IGFib3V0IGZpdCBvbiB0aGUgZm9vdDsgaXQgZG9lcyBub3QgZnVsbHkgZGVzY3JpYmUgdGhlIHRocmVlLWRpbWVuc2lvbmFsIGVudmVsb3BlIHRoYXQgdGhlIHBhY2thZ2luZyBtdXN0IGNvbnRhaW4uPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NZWFzdXJlIHRoZSBwYWNrZWQgcGFpciwgbm90IHRoZSBzaG9lLXNpemUgbnVtYmVyPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hlbiBkZXZlbG9waW5nIGEgY3VzdG9tIHNob2UgYm94LCBzdGFydCB3aXRoIGEgcmVhbCByZXByZXNlbnRhdGl2ZSBwYWlyLiBJZiBzZXZlcmFsIFNLVXMgd2lsbCBzaGFyZSBvbmUgYm94LCB1c2UgdGhlIG1vZGVscyB0aGF0IGNyZWF0ZSB0aGUgbG9uZ2VzdCwgd2lkZXN0IGFuZCB0YWxsZXN0IHBhY2tlZCBjb25maWd1cmF0aW9ucyByYXRoZXIgdGhhbiBhc3N1bWluZyB0aGUgbGFyZ2VzdCBudW1iZXJlZCBzaG9lIHNpemUgYXV0b21hdGljYWxseSByZXByZXNlbnRzIGV2ZXJ5IG1heGltdW0uPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QXJyYW5nZSB0aGUgcGFpciBleGFjdGx5IGFzIGl0IHdpbGwgYmUgcGFja2VkIGluIHByb2R1Y3Rpb24uIEluY2x1ZGUgdGlzc3VlIHBhcGVyLCBkdXN0IGJhZ3MsIHRhZ3MsIHNwYXJlIGxhY2VzLCBjYXJkcyBvciBzdHJ1Y3R1cmFsIGluc2VydHMgaWYgdGhlc2Ugd2lsbCBzaGlwIGluc2lkZSB0aGUgYm94Ljwvc3Bhbj4NCjxvbD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPk1lYXN1cmUgcGFja2VkIGxlbmd0aC48L3N0cm9uZz4gUmVjb3JkIHRoZSBtYXhpbXVtIHRvZS10by1oZWVsIGVudmVsb3BlIGFmdGVyIHRoZSB0d28gc2hvZXMgYXJlIHBvc2l0aW9uZWQgaW4gdGhlIGludGVuZGVkIG9yaWVudGF0aW9uLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+TWVhc3VyZSBwYWNrZWQgd2lkdGguPC9zdHJvbmc+IE1lYXN1cmUgdGhlIHdpZGVzdCBwb2ludCBvZiB0aGUgY29tcGxldGUgcGFpciwgbm90IGp1c3QgdGhlIG91dHNvbGUgb2Ygb25lIHNob2UuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5NZWFzdXJlIHBhY2tlZCBoZWlnaHQuPC9zdHJvbmc+IFVzZSB0aGUgaGlnaGVzdCBwYWNrZWQgcG9pbnQsIHdoaWNoIG1heSBiZSB0aGUgY29sbGFyLCBoZWVsLCBvdXRzb2xlIG9yIGJvb3Qgc2hhZnQuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5BZGQgZnVuY3Rpb25hbCBjbGVhcmFuY2UuPC9zdHJvbmc+IEFkZCBvbmx5IGVub3VnaCByb29tIGZvciBwYWNraW5nLCByZW1vdmFsIGFuZCBtYXRlcmlhbCBtb3ZlbWVudCB3aXRob3V0IGNvbXByZXNzaW5nIHRoZSBzaG9lIG9yIGFsbG93aW5nIHVuY29udHJvbGxlZCBtb3ZlbWVudC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPlByb3RvdHlwZSB0aGUgcmVzdWx0aW5nIGludGVybmFsIHNpemUuPC9zdHJvbmc+IFRoZSBjYWxjdWxhdGVkIGRpbWVuc2lvbiBpcyBhIHN0YXJ0aW5nIHNwZWNpZmljYXRpb24uIEEgcGh5c2ljYWwgc2FtcGxlIGRldGVybWluZXMgd2hldGhlciB0aGUgZml0IGFjdHVhbGx5IHdvcmtzLjwvc3Bhbj48L2xpPg0KPC9vbD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48IS0tIElNQUdFX1NMT1RfMiAtLT48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGlzIGFwcHJvYWNoIG1hdHRlcnMgcGFydGljdWxhcmx5IGZvciBzbmVha2Vycy4gQSB3aWRlIG91dHNvbGUsIHNjdWxwdGVkIG1pZHNvbGUgb3IgcmVpbmZvcmNlZCBoZWVsIGNvdW50ZXIgY2FuIGluY3JlYXNlIHRoZSBwYWNrZWQgZW52ZWxvcGUgd2l0aG91dCBjaGFuZ2luZyB0aGUgbGFiZWxlZCBzaG9lIHNpemUuIEhpZ2ggaGVlbHMgY3JlYXRlIGEgZGlmZmVyZW50IHByb2JsZW06IHRoZWlyIHRvdGFsIHZvbHVtZSBtYXkgYmUgbW9kZXN0LCB5ZXQgYW4gdW5zdXBwb3J0ZWQgaGVlbCBjYW4gY3JlYXRlIGEgY29uY2VudHJhdGVkIGNvbnRhY3QgcG9pbnQgYWdhaW5zdCB0aGUgd2FsbCBvciBsaWQuPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JbnRlcm5hbCBhbmQgZXh0ZXJuYWwgZGltZW5zaW9ucyBzb2x2ZSB0d28gZGlmZmVyZW50IHByb2JsZW1zPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBzaG9lLWJveCBSRlEgc2hvdWxkIG5ldmVyIGNvbnRhaW4gb25seSDigJwzNTAgw5cgMjIwIMOXIDEyMCBtbeKAnSB3aXRob3V0IHNheWluZyB3aGF0IHRob3NlIG51bWJlcnMgcmVwcmVzZW50Ljwvc3Bhbj4NCjx0YWJsZT4NCjx0aGVhZD4NCjx0cj4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RGltZW5zaW9uPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlVzZSBpdCB0byBldmFsdWF0ZTwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaGF0IGNhbiBnbyB3cm9uZyBpZiBjb25mdXNlZDwvc3Bhbj48L3RoPg0KPC90cj4NCjwvdGhlYWQ+DQo8dGJvZHk+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkludGVybmFsIGRpbWVuc2lvbnM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2hvZSBmaXQsIHRpc3N1ZSwgaW5zZXJ0cywgY2xlYXJhbmNlIGFuZCBsaWQgcHJlc3N1cmU8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGZpbmlzaGVkIGludGVyaW9yIGNhbiBiZWNvbWUgdG9vIHNtYWxsIGZvciB0aGUgcGFja2VkIHNob2U8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkV4dGVybmFsIGRpbWVuc2lvbnM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmV0YWlsIHNoZWx2aW5nLCBtYXN0ZXIgY2FydG9ucywgd2FyZWhvdXNlIHNwYWNlIGFuZCBwYXJjZWwgc2hpcHBpbmc8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2FydG9uaXphdGlvbiBvciBmcmVpZ2h0IGNhbGN1bGF0aW9ucyBjYW4gYmUgaW5hY2N1cmF0ZTwvc3Bhbj48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkJvYXJkIGNvbnN0cnVjdGlvbiBtYWtlcyB0aGlzIGRpc3RpbmN0aW9uIGltcG9ydGFudC4gQSBjb3JydWdhdGVkIHdhbGwsIHJpZ2lkLWJvYXJkIHdhbGwgYW5kIHRoaW4gcGFwZXJib2FyZCB3YWxsIGRvIG5vdCBjb25zdW1lIHRoZSBzYW1lIGFtb3VudCBvZiBzcGFjZS4gVHdvIGJveGVzIHdpdGggaWRlbnRpY2FsIGV4dGVybmFsIGRpbWVuc2lvbnMgY2FuIHRoZXJlZm9yZSBwcm92aWRlIGRpZmZlcmVudCB1c2FibGUgaW50ZXJuYWwgdm9sdW1lcy48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JbmR1c3RyeSBndWlkYW5jZSByZWZsZWN0cyB0aGlzIGRpc3RpbmN0aW9uLiBGRUZDTyBtYWludGFpbnMgYSBzcGVjaWZpYyByZWNvbW1lbmRhdGlvbiBmb3IgZGV0ZXJtaW5pbmcgdGhlIGludGVybmFsIGRpbWVuc2lvbnMgb2YgZmxhcC10eXBlIGFuZCBvbmUtcGllY2UgY29ycnVnYXRlZCBjYXNlcywgd2hpbGUgRUNNQSBjYXJ0b24gZGVzaWduIGNvbnZlbnRpb25zIGRlZmluZSBkaW1lbnNpb25zIHJlbGF0aXZlIHRvIGZvbGRpbmcgYW5kIGNyZWFzaW5nIGdlb21ldHJ5LiBGb3IgYSBidXllciwgdGhlIHByYWN0aWNhbCBsZXNzb24gaXMgc2ltcGxlOiA8c3Ryb25nPnN0YXRlIHRoZSBtZWFzdXJlbWVudCBjb252ZW50aW9uIG9uIHRoZSBkcmF3aW5nIGFuZCBSRlEgaW5zdGVhZCBvZiBsZWF2aW5nIHRoZSBzdXBwbGllciB0byBpbmZlciBpdC48L3N0cm9uZz48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48IS0tIElNQUdFX1NMT1RfMyAtLT48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNsZWFyYW5jZSBhbmQgbWFudWZhY3R1cmluZyB0b2xlcmFuY2UgYXJlIG5vdCB0aGUgc2FtZSBudW1iZXI8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGlzIGlzIG9uZSBvZiB0aGUgbW9zdCBpbXBvcnRhbnQgZGV0YWlscyB0byBnZXQgcmlnaHQgd2hlbiBzcGVjaWZ5aW5nIGN1c3RvbSBzaG9lIHBhY2thZ2luZy48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPlByb2R1Y3QgY2xlYXJhbmNlPC9zdHJvbmc+IGlzIHNwYWNlIGludGVudGlvbmFsbHkgZGVzaWduZWQgYXJvdW5kIHRoZSBwYWNrZWQgc2hvZXMuIEl0IGhlbHBzIHByZXZlbnQgY29tcHJlc3Npb24sIG1ha2VzIHBhY2tpbmcgcHJhY3RpY2FsIGFuZCBhY2NvbW1vZGF0ZXMgdGlzc3VlIG9yIG90aGVyIHJlcXVpcmVkIGNvbXBvbmVudHMuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5NYW51ZmFjdHVyaW5nIHRvbGVyYW5jZTwvc3Ryb25nPiBpcyB0aGUgcGVybWl0dGVkIGRpZmZlcmVuY2UgYmV0d2VlbiB0aGUgYXBwcm92ZWQgbm9taW5hbCBib3ggZGltZW5zaW9uIGFuZCB0aGUgZGltZW5zaW9ucyBwcm9kdWNlZCBkdXJpbmcgbWFudWZhY3R1cmluZy48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGV5IHNob3VsZCBuZXZlciBiZSB0cmVhdGVkIGFzIGludGVyY2hhbmdlYWJsZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb3IgZXhhbXBsZSwgYSBkZXNpZ25lciBtaWdodCBkZWxpYmVyYXRlbHkgY3JlYXRlIGNsZWFyYW5jZSBhcm91bmQgYSBzbmVha2VyIHNvIHRoYXQgaXRzIGNvbGxhciBkb2VzIG5vdCBwcmVzcyBhZ2FpbnN0IHRoZSBsaWQuIFRoYXQgY2xlYXJhbmNlIGRvZXMgbm90IG1lYW4gdGhlIHNhbWUgZGltZW5zaW9uYWwgdmFyaWF0aW9uIGNhbiBhdXRvbWF0aWNhbGx5IGJlIGFjY2VwdGVkIGluIHByb2R1Y3Rpb24uPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlcmUgaXMgYWxzbyBubyByZXNwb25zaWJsZSB1bml2ZXJzYWwgc3RhdGVtZW50IHN1Y2ggYXMg4oCcZXZlcnkgc2hvZSBib3ggc2hvdWxkIGhhdmUgYSDCsTMgbW0gdG9sZXJhbmNlLuKAnSBBcHByb3ByaWF0ZSB0b2xlcmFuY2VzIGRlcGVuZCBvbiB0aGUgYm94IHN0cnVjdHVyZSwgYm9hcmQsIGNvbnZlcnRpbmcgbWV0aG9kLCBkaW1lbnNpb24gYmVpbmcgbWVhc3VyZWQgYW5kIHN1cHBsaWVyJ3MgcHJvZHVjdGlvbiBjYXBhYmlsaXR5LiBUaGUgdG9sZXJhbmNlIHNob3VsZCB0aGVyZWZvcmUgYmUgYWdyZWVkIG9uIHRoZSBhcHByb3ZlZCBzcGVjaWZpY2F0aW9uIG9yIGRpZWxpbmUgYW5kIGNoZWNrZWQgYWdhaW5zdCByZXByZXNlbnRhdGl2ZSBwcm9kdWN0aW9uIHNhbXBsZXMuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5BIHVzZWZ1bCBSRlEgc2VwYXJhdGVzIHRocmVlIHZhbHVlczo8L3N0cm9uZz4gbm9taW5hbCBpbnRlcm5hbCBkaW1lbnNpb24sIHJlcXVpcmVkIGZ1bmN0aW9uYWwgY2xlYXJhbmNlLCBhbmQgYWNjZXB0YWJsZSBtYW51ZmFjdHVyaW5nIHRvbGVyYW5jZS48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBib3ggY2FuIG1hdGNoIHRoZSBzaG9lIGFuZCBzdGlsbCBmYWlsIHdoZW4gdGhlIGxpZCBjbG9zZXM8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIGJveCBpcyBub3QgYXBwcm92ZWQganVzdCBiZWNhdXNlIHRoZSBzaG9lcyBjYW4gcGh5c2ljYWxseSBiZSBwbGFjZWQgaW5zaWRlIGl0Ljwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNvbnNpZGVyIGEgYnVsa3kgc25lYWtlciB3aG9zZSBvdXRzb2xlIGZpdHMgdGhlIGJhc2UgcGVyZmVjdGx5LiBPbmNlIHRpc3N1ZSBpcyB3cmFwcGVkIGFyb3VuZCB0aGUgc2hvZSBhbmQgdGhlIHNlY29uZCBzaG9lIGlzIG5lc3RlZCBhYm92ZSBpdCwgdGhlIGhlZWwgY29sbGFyIG1heSBzaXQgc2xpZ2h0bHkgaGlnaGVyIHRoYW4gZXhwZWN0ZWQuIFRoZSBsaWQgc3RpbGwgY2xvc2VzLCBidXQgaXQgYm93cyB1cHdhcmQgb3IgcHVzaGVzIGFnYWluc3QgdGhlIHVwcGVyLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk9uIGEgZGltZW5zaW9uIHNoZWV0LCB0aGF0IGJveCDigJxmaXRzLuKAnSBJbiB1c2UsIGl0IGZhaWxzIHRoZSBwYWNrYWdpbmcgdGFzay48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHVzZWZ1bCBzYW1wbGUgYXBwcm92YWwgdGhlcmVmb3JlIGNoZWNrcyB0aGUgY29tcGxldGUgcGFja2VkIGNvbmRpdGlvbjo8L3NwYW4+DQo8dWw+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGxpZCBzaXRzIG5hdHVyYWxseSB3aXRob3V0IGJlaW5nIGZvcmNlZCBkb3duLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSB1cHBlciwgdG9lLCBoZWVsIGFuZCBjb2xsYXIgYXJlIG5vdCB2aXNpYmx5IGNvbXByZXNzZWQuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIHBhaXIgY2FuIGJlIHJlbW92ZWQgd2l0aG91dCBkcmFnZ2luZyBhZ2FpbnN0IHRoZSB3YWxscy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgc2hvZXMgZG8gbm90IGhhdmUgZW5vdWdoIGZyZWUgc3BhY2UgdG8gcmVwZWF0ZWRseSBzdHJpa2UgdGhlIHdhbGxzIGR1cmluZyBub3JtYWwgaGFuZGxpbmcuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGlzc3VlLCBpbnNlcnRzIGFuZCBhY2Nlc3NvcmllcyByZW1haW4gaW4gdGhlaXIgaW50ZW5kZWQgcG9zaXRpb25zLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBib3gga2VlcHMgaXRzIGludGVuZGVkIHNoYXBlIGFmdGVyIGNsb3NpbmcgYW5kIHN0YWNraW5nLjwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JZiBleHRyYSBlbXB0eSBzcGFjZSBpcyBuZWNlc3Nhcnkgb25seSBiZWNhdXNlIHNldmVyYWwgU0tVcyBtdXN0IHNoYXJlIG9uZSBib3gsIHVzZSB0aXNzdWUsIGEgcGFwZXIgaW5zZXJ0IG9yIGFub3RoZXIgYXBwcm9wcmlhdGUgc3RhYmlsaXphdGlvbiBtZXRob2Qgd2hlcmUgcmVxdWlyZWQgcmF0aGVyIHRoYW4gYXNzdW1pbmcgYSBsYXJnZXIgZW1wdHkgYm94IGlzIGF1dG9tYXRpY2FsbHkgc2FmZXIuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PCEtLSBJTUFHRV9TTE9UXzQgLS0+PC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5PbmUgYm94IHNpemUgZm9yIGV2ZXJ5IGZvb3R3ZWFyIFNLVSBjYW4gY3JlYXRlIGEgZGlmZmVyZW50IGNvc3QgcHJvYmxlbTwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgYnJhbmQgd2l0aCB0ZW4gc2hvZSBTS1VzIGRvZXMgbm90IG5lY2Vzc2FyaWx5IG5lZWQgdGVuIGJveCBkaW1lbnNpb25zLiBJdCBhbHNvIHNob3VsZCBub3QgYXV0b21hdGljYWxseSBmb3JjZSBhbGwgdGVuIGludG8gb25lIG92ZXJzaXplZCBib3guPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBiZXR0ZXIgYXBwcm9hY2ggaXMgdG8gY3JlYXRlIDxzdHJvbmc+c2l6ZSBmYW1pbGllczwvc3Ryb25nPi48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5GaXJzdCwgbWVhc3VyZSB0aGUgcGFja2VkIEwgw5cgVyDDlyBIIGVudmVsb3BlIG9mIGVhY2ggU0tVLiBUaGVuIGdyb3VwIHByb2R1Y3RzIHdob3NlIHBhY2tlZCBkaW1lbnNpb25zIGFuZCBwcm90ZWN0aW9uIHJlcXVpcmVtZW50cyBhcmUgc2ltaWxhci4gVGVzdCB0aGUgbGFyZ2VzdCBjcml0aWNhbCBwcm9kdWN0IGluIGVhY2ggcHJvcG9zZWQgYm94IGZhbWlseSBhbmQgYWxzbyB0ZXN0IHRoZSBzbWFsbGVzdCBwcm9kdWN0IHRvIG1ha2Ugc3VyZSBleGNlc3NpdmUgbW92ZW1lbnQgaGFzIG5vdCBiZWVuIGludHJvZHVjZWQuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhpcyBjcmVhdGVzIGEgdXNlZnVsIGRlY2lzaW9uIHJ1bGU6PC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5JZiB0aGUgbGFyZ2VzdCBTS1UgaXMgY29tcHJlc3NlZCwgdGhlIGZhbWlseSBpcyB0b28gc21hbGwuIElmIHRoZSBzbWFsbGVzdCBTS1UgcmVxdWlyZXMgZXhjZXNzaXZlIGZpbGxlciBvciByZW1haW5zIHVuc3RhYmxlLCB0aGUgZmFtaWx5IG1heSBiZSB0b28gYnJvYWQuPC9zdHJvbmc+PC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhpcyBhcHByb2FjaCBjYW4gcmVkdWNlIHRoZSBudW1iZXIgb2YgZGllbGluZXMgYW5kIHBhY2thZ2luZyBTS1VzIHdoaWxlIGF2b2lkaW5nIGEgdW5pdmVyc2FsIGJveCB0aGF0IHdhc3RlcyBtYXRlcmlhbCBhbmQgc2hpcHBpbmcgdm9sdW1lLjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2hlY2sgdGhlIHNoaXBwaW5nIGRpbWVuc2lvbiBiZWZvcmUgZnJlZXppbmcgdGhlIGRpZWxpbmU8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5SZXRhaWwgYm94IHNpemluZyBzaG91bGQgbm90IGJlIGZpbmFsaXplZCBpbiBpc29sYXRpb24gZnJvbSBsb2dpc3RpY3MuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFyY2VsIGNhcnJpZXJzIG1heSB1c2UgZXh0ZXJuYWwgcGFja2FnZSBkaW1lbnNpb25zIHdoZW4gZGV0ZXJtaW5pbmcgZGltZW5zaW9uYWwgb3Igdm9sdW1ldHJpYyB3ZWlnaHQuIFVQUywgZm9yIGV4YW1wbGUsIGN1cnJlbnRseSBjYWxjdWxhdGVzIGRpbWVuc2lvbmFsIHdlaWdodCBmcm9tIGxlbmd0aCDDlyB3aWR0aCDDlyBoZWlnaHQgZGl2aWRlZCBieSB0aGUgYXBwbGljYWJsZSBkaXZpc29yLCB3aXRoIHJ1bGVzIHZhcnlpbmcgYnkgc2VydmljZSBhbmQgbWFya2V0LiBDYXJyaWVyIHJ1bGVzIGNhbiBjaGFuZ2UsIHNvIHRoZXkgc2hvdWxkIGJlIGNoZWNrZWQgZm9yIHRoZSBhY3R1YWwgc2hpcHBpbmcgc2VydmljZSBiZWZvcmUgcHJvZHVjdGlvbiBkaW1lbnNpb25zIGFyZSBmcm96ZW4uPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGRpZmZlcmVuY2UgaXMgZXNwZWNpYWxseSByZWxldmFudCBpZiB0aGUgcmV0YWlsIHNob2UgYm94IHdpbGwgYmUgcGxhY2VkIGluc2lkZSBhIHNlcGFyYXRlIGNvcnJ1Z2F0ZWQgc2hpcHBlci4gQSBzbWFsbCBpbmNyZWFzZSBpbiB0aGUgcmV0YWlsIGJveCBjYW4gcHJvcGFnYXRlIGludG8gdGhlIG91dGVyIGNhcnRvbiBhbmQgYWZmZWN0IGhvdyBtYW55IHVuaXRzIGZpdCBpbnRvIGEgbWFzdGVyIGNhcnRvbi48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5VU1BTIG9mZmVycyBhbm90aGVyIHVzZWZ1bCBpbGx1c3RyYXRpb24gb2Ygd2h5IOKAnHNob2UgYm94IHNpemXigJ0gaXMgbm90IGEgdW5pdmVyc2FsIG1hbnVmYWN0dXJpbmcgc3RhbmRhcmQ6IGl0cyBjdXJyZW50IFByaW9yaXR5IE1haWwgU2hvZSBCb3ggaGFzIHNlcGFyYXRlIHB1Ymxpc2hlZCBpbnNpZGUgYW5kIG91dHNpZGUgZGltZW5zaW9ucy4gSXQgaXMgYSBzaGlwcGluZyBwcm9kdWN0IGRlc2lnbmVkIGZvciBhIHBhcnRpY3VsYXIgcG9zdGFsIHN5c3RlbSwgbm90IGEgc3BlY2lmaWNhdGlvbiB0aGF0IGV2ZXJ5IHJldGFpbCBzaG9lIGJveCBzaG91bGQgY29weS48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgc2hvZS1ib3ggc3BlY2lmaWNhdGlvbiB0aGF0IGEgcGFja2FnaW5nIHN1cHBsaWVyIGNhbiBhY3R1YWxseSB1c2U8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5CZWZvcmUgcmVxdWVzdGluZyBhIGRpZWxpbmUgb3IgcXVvdGF0aW9uIGZyb20gYSA8YSBocmVmPSJodHRwczovL2hvcGdpYXl2cG4uY29tL2N1c3RvbS1wYWNrYWdpbmctYm94ZXMtbWFudWZhY3R1cmVyLyI+Y3VzdG9tIHBhY2thZ2luZyBib3ggbWFudWZhY3R1cmVyPC9hPiwgdGhlIHVzZWZ1bCBpbnB1dCBpcyBub3Qgc2ltcGx5IOKAnG1lbidzIHNuZWFrZXIgYm94LuKAnSBTZW5kIGEgY29tcGFjdCBwcm9kdWN0IHNwZWNpZmljYXRpb24gaW5zdGVhZDo8L3NwYW4+DQo8dWw+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9vdHdlYXIgdHlwZSBhbmQgZnVsbCBTS1Uvc2l6ZSByYW5nZTwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlBhY2tlZCBtYXhpbXVtIGxlbmd0aCwgd2lkdGggYW5kIGhlaWdodCBmb3IgcmVwcmVzZW50YXRpdmUgc2hvZXM8L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5SZXF1aXJlZCBib3ggc3R5bGUsIHN1Y2ggYXMgbGlkLWFuZC1iYXNlLCBmb2xkaW5nIGNhcnRvbiBvciBjb3JydWdhdGVkIG1haWxlcjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZXRoZXIgZGltZW5zaW9ucyBvbiB0aGUgYnJpZWYgYXJlIGludGVybmFsIG9yIGV4dGVybmFsPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGlzc3VlLCBkdXN0IGJhZywgaW5zZXJ0LCBzcGFyZSBsYWNlcyBhbmQgb3RoZXIgY29tcG9uZW50cyBpbnNpZGUgdGhlIGJveDwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJlcXVpcmVkIGZpdCBiZWhhdmlvcjogc251ZyBwcmVzZW50YXRpb24sIGNvbnRyb2xsZWQgY2xlYXJhbmNlIG9yIGFkZGl0aW9uYWwgdHJhbnNpdCBwcm90ZWN0aW9uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmV0YWlsIHZlcnN1cyBlLWNvbW1lcmNlIHVzZTwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkV4dGVybmFsLXNpemUgb3IgbWFzdGVyLWNhcnRvbiBjb25zdHJhaW50czwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJlcXVlc3RlZCBkaW1lbnNpb25hbCB0b2xlcmFuY2UgdG8gYmUgcmV2aWV3ZWQgYW5kIGNvbmZpcm1lZCB3aXRoIHRoZSBjb252ZXJ0ZXI8L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TYW1wbGVzL1NLVXMgdGhhdCBtdXN0IHBhc3MgdGhlIGZpbmFsIHBhY2stb3V0IHRlc3Q8L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SWYgdGhlIHNob2UgYm94IGl0c2VsZiBtdXN0IGhhbmRsZSBtb3JlIGRlbWFuZGluZyBkaXN0cmlidXRpb24gY29uZGl0aW9ucywgYSA8YSBocmVmPSJodHRwczovL2hvcGdpYXl2cG4uY29tL3Byb2R1Y3QvY3VzdG9tLWNvcnJ1Z2F0ZWQtc2hvZS1tYWlsZXItYm94LyI+Y29ycnVnYXRlZCBzaG9lIG1haWxlcjwvYT4gbWF5IG5lZWQgdG8gYmUgZXZhbHVhdGVkIHNlcGFyYXRlbHkgZnJvbSBhIHJldGFpbCBwcmVzZW50YXRpb24gYm94LiBGb3IgZm9vdHdlYXIgcHJpbWFyaWx5IGZvY3VzZWQgb24gc2hlbGYgYW5kIHVuYm94aW5nIHByZXNlbnRhdGlvbiwgdGhlIDxhIGhyZWY9Imh0dHBzOi8vaG9wZ2lheXZwbi5jb20vcHJvZHVjdC9jdXN0b20tc2hvZS1wYWNrYWdpbmctYm94LyI+Y3VzdG9tIHNob2UgcGFja2FnaW5nPC9hPiBzdHJ1Y3R1cmUgY2FuIGluc3RlYWQgYmUgc2l6ZWQgYXJvdW5kIHRoZSBhY3R1YWwgZm9vdHdlYXIgYW5kIHByZXNlbnRhdGlvbiByZXF1aXJlbWVudHMuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIG1vc3QgcmVsaWFibGUgYW5zd2VyIHRvIOKAnHdoYXQgYXJlIHRoZSBkaW1lbnNpb25zIG9mIGEgc2hvZSBib3g/4oCdIGlzIHRoZXJlZm9yZSB0d28tcGFydDogPHN0cm9uZz5hYm91dCAxMuKAkzE0IMOXIDfigJM5IMOXIDTigJM2IGluY2hlcyBpcyBhIHVzZWZ1bCBhZHVsdCByZWZlcmVuY2UgcmFuZ2UsIGJ1dCB0aGUgcHJvZHVjdGlvbiBzaXplIHNob3VsZCBjb21lIGZyb20gdGhlIHBhY2tlZCBzaG9l4oCUbm90IHRoZSBhdmVyYWdlLjwvc3Ryb25nPiBTZW5kIHRoZSBwYWNrZWQgcHJvZHVjdCBkaW1lbnNpb25zLCBzaG9lIHR5cGUsIHNpemUgcmFuZ2UsIGJveCBzdHlsZSBhbmQgaW50ZXJuYWwgY29tcG9uZW50cyB3aGVuIHJlcXVlc3RpbmcgYSBjdXN0b20gZGllbGluZS4gVGhhdCBnaXZlcyB0aGUgcGFja2FnaW5nIHN1cHBsaWVyIGVub3VnaCBpbmZvcm1hdGlvbiB0byBkZXZlbG9wIGEgc2l6ZSB0aGF0IGNhbiBiZSB2YWxpZGF0ZWQgYnkgc2FtcGxlIGluc3RlYWQgb2YgZ3Vlc3NlZCBmcm9tIGEgZ2VuZXJpYyBzaG9lLWJveCBjaGFydC48L3NwYW4+', true);
    return is_string($content) ? $content : '';
}
