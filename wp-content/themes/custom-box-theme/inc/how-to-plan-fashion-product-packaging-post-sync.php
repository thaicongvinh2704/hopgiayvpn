<?php
/**
 * Deploys the fashion and sportswear paper packaging draft and images.
 */

const CUSTOM_BOX_FASHION_PACKAGING_SYNC_VERSION = '2026-07-22-v1';
const CUSTOM_BOX_FASHION_PACKAGING_VERSION_OPTION = 'custom_box_fashion_packaging_sync_version';
const CUSTOM_BOX_FASHION_PACKAGING_NOTICE_OPTION = 'custom_box_fashion_packaging_sync_notice';

add_action('admin_init', 'custom_box_sync_fashion_packaging_post');
add_action('admin_notices', 'custom_box_fashion_packaging_admin_notice');

function custom_box_sync_fashion_packaging_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_fashion_packaging_post_data();
    $post = custom_box_find_fashion_packaging_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_FASHION_PACKAGING_SYNC_VERSION === get_option(CUSTOM_BOX_FASHION_PACKAGING_VERSION_OPTION)
        && $post
        && custom_box_fashion_packaging_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_fashion_packaging_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_FASHION_PACKAGING_VERSION_OPTION);
        update_option(CUSTOM_BOX_FASHION_PACKAGING_NOTICE_OPTION, array('success' => false, 'message' => $post_id->get_error_message()), false);
        return;
    }

    if (custom_box_fashion_packaging_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_FASHION_PACKAGING_VERSION_OPTION, CUSTOM_BOX_FASHION_PACKAGING_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_FASHION_PACKAGING_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Fashion packaging draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Guides, 6 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_FASHION_PACKAGING_VERSION_OPTION);
    delete_option(CUSTOM_BOX_FASHION_PACKAGING_NOTICE_OPTION);
    update_option(CUSTOM_BOX_FASHION_PACKAGING_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Fashion packaging sync is incomplete. Missing images: ' . implode(', ', (array) get_option('custom_box_fashion_packaging_missing_images', array())) . '; missing slots or validation failures: ' . implode(', ', array_merge((array) get_option('custom_box_fashion_packaging_missing_slots', array()), (array) get_option('custom_box_fashion_packaging_validation_failures', array()))),
    ), false);
}
function custom_box_fashion_packaging_post_data(): array
{
    return array(
        'title' => 'How to Plan Paper Packaging for Fashion and Sportswear Products',
        'slug' => 'how-to-plan-fashion-product-packaging',
        'excerpt' => 'A practical B2B guide to planning paper boxes, branded paper bags, logo printing, product fit, retail presentation, ecommerce protection and packaging samples for fashion and sportswear products.',
        'category' => array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
        'tags' => array(
            'Fashion Packaging' => 'fashion-packaging',
            'Sportswear Packaging' => 'sportswear-packaging',
            'Apparel Boxes' => 'apparel-boxes',
            'Paper Bags' => 'paper-bags',
            'Logo Printing' => 'logo-printing',
            'Retail Packaging' => 'retail-packaging',
        ),
        'seo_title' => 'How to Plan Fashion & Sportswear Paper Packaging',
        'seo_description' => 'Plan paper boxes, branded paper bags, logo printing and retail presentation for fashion and sportswear products with a practical B2B checklist.',
        'focus_keyword' => 'how to plan fashion product packaging',
    );
}

function custom_box_fashion_packaging_images(): array
{
    return array(
        'featured' => array(
            'base' => 'how-to-plan-fashion-product-packaging',
            'alt' => 'Paper packaging plan with apparel box, sportswear carton and branded retail paper bag',
            'title' => 'Fashion and Sportswear Paper Packaging Plan',
            'caption' => 'A coordinated paper box and retail bag system planned for fashion and sportswear products.',
        ),
        'slot_1' => array(
            'base' => 'fashion-packaging-system-box-bag-mailer',
            'alt' => 'Three-layer packaging system with apparel box, paper shopping bag and shipping mailer',
            'title' => 'Three-Layer Fashion Packaging System',
            'caption' => 'Product-facing packaging, retail carry packaging and transport packaging perform different jobs.',
        ),
        'slot_2' => array(
            'base' => 'fashion-packaging-structure-fit-comparison',
            'alt' => 'Comparison of folding carton, shoe box and rigid apparel presentation box',
            'title' => 'Paper Box Structures for Fashion Products',
            'caption' => 'Different fashion products require different box structures, fit and presentation levels.',
        ),
        'slot_3' => array(
            'base' => 'logo-printing-fashion-box-paper-bag',
            'alt' => 'Logo printing and finishing details on fashion paper box and matching paper bag',
            'title' => 'Fashion Packaging Logo Printing Details',
            'caption' => 'Logo scale, paper surface and finishing should remain consistent across boxes and retail bags.',
        ),
        'slot_4' => array(
            'base' => 'fashion-packaging-sample-qc-checklist',
            'alt' => 'Fashion packaging sample inspection with folded garment, dieline and color swatches',
            'title' => 'Fashion Packaging Sample and QC Inspection',
            'caption' => 'A production sample should be checked with the real product, artwork and packing method.',
        ),
    );
}

function custom_box_find_fashion_packaging_post(string $slug, string $title): ?WP_Post
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

function custom_box_upsert_fashion_packaging_post()
{
    $data = custom_box_fashion_packaging_post_data();
    $post = custom_box_find_fashion_packaging_post($data['slug'], $data['title']);
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
        if (!in_array($post->post_status, array('publish', 'private'), true) || '' === trim($existing) || false !== strpos($existing, 'IMAGE_SLOT_')) {
            $payload['post_content'] = custom_box_fashion_packaging_content();
        }
        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = custom_box_fashion_packaging_content();
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_fashion_packaging_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_fashion_packaging_images($post_id);
    return $post_id;
}

function custom_box_sync_fashion_packaging_terms(int $post_id, array $data): void
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

function custom_box_sync_fashion_packaging_images(int $post_id): void
{
    $images = custom_box_fashion_packaging_images();
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_fashion_packaging_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_fashion_packaging_attachment($image['base'], $post_id, $image);
        }
        if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
            $missing_images[] = $image['base'];
            continue;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
        wp_update_post(array('ID' => $attachment_id, 'post_title' => $image['title'], 'post_excerpt' => $image['caption'], 'post_parent' => $post_id));

        if ('featured' === $key) {
            set_post_thumbnail($post_id, $attachment_id);
            continue;
        }

        $marker = '<!-- fashion-packaging-image:' . $key . ' -->';
        $url = wp_get_attachment_url($attachment_id);
        $figure = $marker . "\n<figure><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
        $wrapped_pattern = '/<span[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure>.*?<\/figure>/is';

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
    update_option('custom_box_fashion_packaging_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_fashion_packaging_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_find_fashion_packaging_attachment(string $base): int
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

function custom_box_create_fashion_packaging_attachment(string $base, int $post_id, array $image): int
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

function custom_box_fashion_packaging_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_fashion_packaging_post_data();
    $images = custom_box_fashion_packaging_images();
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
    if (4 !== substr_count($content, '<!-- fashion-packaging-image:') || 4 !== substr_count($content, '<figure>') || 4 !== substr_count($content, '<img ')) {
        $failures[] = 'inline image counts';
    }
    foreach (array('slot_1', 'slot_2', 'slot_3', 'slot_4') as $key) {
        if (false === strpos($content, $images[$key]['base'])) {
            $failures[] = $key;
        }
    }
    if (false !== strpos($content, 'IMAGE_SLOT_')) {
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
    if ((array) get_option('custom_box_fashion_packaging_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_fashion_packaging_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_fashion_packaging_validation_failures', array_values(array_unique($failures)), false);
    return !$failures;
}

function custom_box_fashion_packaging_admin_notice(): void
{
    $notice = get_option(CUSTOM_BOX_FASHION_PACKAGING_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }
    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}

function custom_box_fashion_packaging_content(): string
{
    $content = base64_decode('PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgYmV0dGVyIGFwcHJvYWNoIGlzIHRvIHBsYW4gdGhlIHBhY2thZ2luZyBhcyBhIHN5c3RlbS4gVGhlIHBhcGVyIGJveCwgcGFwZXIgYmFnLCBsb2dvIHRyZWF0bWVudCwgcHJvZHVjdCBsYWJlbCwgaW5uZXIgcHJlc2VudGF0aW9uIGFuZCBzaGlwcGluZyBjYXJ0b24gc2hvdWxkIGVhY2ggcGVyZm9ybSBhIGRlZmluZWQgam9iLiBPbmNlIHRob3NlIGpvYnMgYXJlIGNsZWFyLCBtYXRlcmlhbCBhbmQgZmluaXNoaW5nIGRlY2lzaW9ucyBiZWNvbWUgZWFzaWVyIHRvIGNvbnRyb2wuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5EZWNpc2lvbiBzaG9ydGN1dDo8L3N0cm9uZz4gc3RhcnQgd2l0aCB0aGUgcGFja2VkIHByb2R1Y3QsIHNlbGxpbmcgY2hhbm5lbCBhbmQgcmVxdWlyZWQgcHJlc2VudGF0aW9uIGxldmVsLiBDaG9vc2UgdGhlIGJveCBzdHJ1Y3R1cmUgb25seSBhZnRlciB0aG9zZSB0aHJlZSBwb2ludHMgaGF2ZSBiZWVuIGNvbmZpcm1lZC48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlN0YXJ0IFdpdGggdGhlIFByb2R1Y3QgYW5kIFNlbGxpbmcgQ2hhbm5lbDwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgVC1zaGlydCwgcnVubmluZyBzaG9lLCBiZWx0IGFuZCBwcmVtaXVtIHRlYW0ga2l0IHNob3VsZCBub3QgYmUgcGxhY2VkIGludG8gdGhlIHNhbWUgcGFja2FnaW5nIHN0cnVjdHVyZSBzaW1wbHkgYmVjYXVzZSB0aGV5IGJlbG9uZyB0byB0aGUgZmFzaGlvbiBvciBzcG9ydHN3ZWFyIGNhdGVnb3J5LiBFYWNoIHByb2R1Y3QgYmVoYXZlcyBkaWZmZXJlbnRseSB3aGVuIGZvbGRlZCwgc3RhY2tlZCwgZGlzcGxheWVkIGFuZCB0cmFuc3BvcnRlZC48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5CZWZvcmUgcmVxdWVzdGluZyBhIGRpZWxpbmUgb3IgcGFja2FnaW5nIHF1b3RhdGlvbiwgZG9jdW1lbnQgdGhlIGZvbGxvd2luZzo8L3NwYW4+DQo8dWw+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIHByb2R1Y3TigJlzIGRpbWVuc2lvbnMgaW4gaXRzIGZpbmFsIGZvbGRlZCBvciBhcnJhbmdlZCBjb25kaXRpb24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SXRzIHdlaWdodCBhbmQgd2hldGhlciBpdCBjYW4gYmUgY29tcHJlc3NlZCB3aXRob3V0IGFmZmVjdGluZyBwcmVzZW50YXRpb24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciB0aGUgY3VzdG9tZXIgd2lsbCBzZWUgdGhlIHBhY2thZ2Ugb24gYSByZXRhaWwgc2hlbGYsIHJlY2VpdmUgaXQgZnJvbSBhIHNhbGVzIGFzc2lzdGFudCBvciBvcGVuIGl0IGFmdGVyIGVjb21tZXJjZSBkZWxpdmVyeS48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGUgbnVtYmVyIG9mIHNpemVzLCBjb2xvcnMsIGNvbGxlY3Rpb25zIGFuZCBTS1VzIHVzaW5nIHRoZSBzYW1lIHBhY2thZ2luZyBmYW1pbHkuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciB0aGUgcGFja2FnZSBpcyBpbnRlbmRlZCBmb3IgZXZlcnlkYXkgcmV0YWlsLCBhIHNlYXNvbmFsIGxhdW5jaCwgZ2lmdGluZyBvciBhIGxpbWl0ZWQgY29sbGVjdGlvbi48L3NwYW4+PC9saT4NCjwvdWw+DQo8dGFibGU+DQo8dGhlYWQ+DQo8dHI+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb2R1Y3QgZ3JvdXA8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFja2FnaW5nIGJlaGF2aW9yPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByYWN0aWNhbCBzdGFydGluZyBzdHJ1Y3R1cmU8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TWFpbiBwbGFubmluZyByaXNrPC9zcGFuPjwvdGg+DQo8L3RyPg0KPC90aGVhZD4NCjx0Ym9keT4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VC1zaGlydHMsIGplcnNleXMgYW5kIGxpZ2h0d2VpZ2h0IHRvcHM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U29mdCwgZm9sZGFibGUgYW5kIHJlbGF0aXZlbHkgbGlnaHR3ZWlnaHQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9sZGluZyBjYXJ0b24sIHBhcGVyIHNsZWV2ZSwgYmVsbHkgYmFuZCBvciBjb21wYWN0IG1haWxlcjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Vc2luZyBhbiBvdmVyc2l6ZWQgYm94IHRoYXQgYWxsb3dzIHRoZSBmb2xkIHRvIG1vdmUgYW5kIGxvb2sgdW50aWR5PC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5MZWdnaW5ncywgYmFzZSBsYXllcnMgYW5kIGFjdGl2ZXdlYXIgc2V0czwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db21wcmVzc2libGUgYnV0IG9mdGVuIHNvbGQgaW4gbWFueSBzaXplcyBhbmQgY29sb3JzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkZvbGRpbmcgY2FydG9uLCBzbGVldmUgb3IgcHJpbnRlZCBtYWlsZXIgd2l0aCBhIGNsZWFyIFNLVSBsYWJlbCBhcmVhPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNyZWF0aW5nIGFydHdvcmsgdGhhdCBtYWtlcyBzaXplIGFuZCBjb2xvciBpZGVudGlmaWNhdGlvbiBkaWZmaWN1bHQ8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNob2VzIGFuZCBzbmVha2Vyczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5IZWF2aWVyLCBzaGFwZWQgYW5kIGNvbW1vbmx5IHN0YWNrZWQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TGlkLWFuZC1iYXNlIHBhcGVyYm9hcmQgYm94IG9yIGNvcnJ1Z2F0ZWQgc2hvZSBib3g8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2VhayBjb3JuZXJzLCB1bnN1aXRhYmxlIHN0YWNraW5nIHN0cmVuZ3RoIG9yIHBvb3IgdmVudGlsYXRpb24gcGxhbm5pbmc8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkJlbHRzLCB3YWxsZXRzIGFuZCBjb21wYWN0IGFjY2Vzc29yaWVzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNtYWxsIHByb2R1Y3RzIHRoYXQgbWF5IHJlcXVpcmUgY29udHJvbGxlZCBwb3NpdGlvbmluZzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EcmF3ZXIgYm94LCBsaWQtYW5kLWJhc2UgYm94LCBmb2xkaW5nIGNhcnRvbiBvciByaWdpZCBwcmVzZW50YXRpb24gYm94PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb2R1Y3QgbW92ZW1lbnQsIHNjcmF0Y2hlZCBoYXJkd2FyZSBvciBhbiBpbnNlcnQgdGhhdCBpcyBkaWZmaWN1bHQgdG8gcGFjazwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHJlbWl1bSBhcHBhcmVsIHNldHMgYW5kIHRlYW0ga2l0czwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NdWx0aXBsZSBpdGVtcyBtdXN0IGJlIGFycmFuZ2VkIGFzIG9uZSBwcmVzZW50YXRpb248L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmlnaWQgYm94LCBsYXJnZSBsaWQtYW5kLWJhc2UgYm94IG9yIGNvcnJ1Z2F0ZWQgcHJlc2VudGF0aW9uIGJveCB3aXRoIGRpdmlkZXJzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkluY29ycmVjdCBjYXZpdHkgcGxhbm5pbmcgYW5kIGV4Y2Vzc2l2ZSBlbXB0eSBzcGFjZTwvc3Bhbj48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5QbGFuIFRocmVlIFBhY2thZ2luZyBKb2JzIEluc3RlYWQgb2YgT25lIFBhY2thZ2U8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5GYXNoaW9uIGJyYW5kcyBvZnRlbiBhc2sgb25lIGJveCB0byBwZXJmb3JtIGV2ZXJ5IGZ1bmN0aW9uLiBJbiBwcmFjdGljZSwgYSBwYWNrYWdpbmcgc3lzdGVtIG1heSBuZWVkIHRocmVlIGRpZmZlcmVudCBsYXllcnMuPC9zcGFuPg0KPGgzPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij4xLiBQcm9kdWN0LWZhY2luZyBwYWNrYWdpbmc8L3NwYW4+PC9oMz4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGlzIGlzIHRoZSBib3gsIHNsZWV2ZSwgd3JhcCBvciBiYW5kIHRoYXQgZGlyZWN0bHkgcHJlc2VudHMgdGhlIGZvbGRlZCBwcm9kdWN0LiBJdHMgbWFpbiBqb2IgaXMgdG8ga2VlcCB0aGUgaXRlbSBvcmdhbml6ZWQsIGNvbW11bmljYXRlIHRoZSBicmFuZCBhbmQgc3VwcG9ydCB0aGUgaW50ZW5kZWQgb3BlbmluZyBleHBlcmllbmNlLjwvc3Bhbj4NCjxoMz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Mi4gUmV0YWlsIGNhcnJ5IHBhY2thZ2luZzwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgcHJpbnRlZCBwYXBlciBiYWcgZ2l2ZXMgdGhlIGN1c3RvbWVyIGEgY29udmVuaWVudCB3YXkgdG8gY2FycnkgdGhlIHB1cmNoYXNlIHdoaWxlIGV4dGVuZGluZyB0aGUgdmlzdWFsIGlkZW50aXR5IGJleW9uZCB0aGUgY2hlY2tvdXQgY291bnRlci4gSXRzIGRpbWVuc2lvbnMsIGhhbmRsZSwgYm90dG9tIHJlaW5mb3JjZW1lbnQgYW5kIGxvZ28gcG9zaXRpb24gc2hvdWxkIGJlIHBsYW5uZWQgYXJvdW5kIHRoZSBhY3R1YWwgYm94ZWQgb3IgZm9sZGVkIHB1cmNoYXNl4oCUbm90IHNlbGVjdGVkIG9ubHkgZnJvbSBhIHN0YW5kYXJkIGJhZyBjYXRhbG9nLjwvc3Bhbj4NCjxoMz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+My4gVHJhbnNwb3J0IHBhY2thZ2luZzwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSByZXRhaWwgYm94IG9yIHBhcGVyIGJhZyBzaG91bGQgbm90IGF1dG9tYXRpY2FsbHkgYmUgdHJlYXRlZCBhcyBhIHNoaXBwaW5nIHBhY2thZ2UuIEVjb21tZXJjZSBhbmQgZXhwb3J0IGhhbmRsaW5nIG1heSByZXF1aXJlIGEgY29ycnVnYXRlZCBtYWlsZXIsIHByb3RlY3RpdmUgb3V0ZXIgY2FydG9uLCB0aXNzdWUsIHBhcGVyIGN1c2hpb25pbmcgb3IgZGl2aWRlcnMuIEtlZXBpbmcgdGhlIHByZXNlbnRhdGlvbiBsYXllciBzZXBhcmF0ZSBmcm9tIHRoZSB0cmFuc3BvcnQgbGF5ZXIgY2FuIHByZXZlbnQgc2N1ZmZpbmcgYW5kIGNydXNoZWQgY29ybmVycyB3aXRob3V0IG1ha2luZyB0aGUgcmV0YWlsIHBhY2thZ2UgdW5uZWNlc3NhcmlseSBoZWF2eS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48IS0tIElNQUdFX1NMT1RfMSAtLT48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UaGlzIHRocmVlLWxheWVyIG1vZGVsIGRvZXMgbm90IG1lYW4gZXZlcnkgb3JkZXIgbmVlZHMgdGhyZWUgcGFja2FnZXMuIEEgbGlnaHR3ZWlnaHQgc3BvcnRzd2VhciBwcm9kdWN0IHNvbGQgb25saW5lIG1heSBvbmx5IG5lZWQgYSBicmFuZGVkIHBhcGVyIHdyYXAgYW5kIGEgY29ycmVjdGx5IHNpemVkIGNvcnJ1Z2F0ZWQgbWFpbGVyLiBBIHByZW1pdW0gaW4tc3RvcmUgYXBwYXJlbCBzZXQgbWF5IHVzZSBhIHJpZ2lkIGJveCB3aXRoIGEgbWF0Y2hpbmcgcGFwZXIgYmFnIGJ1dCBubyBpbmRpdmlkdWFsIHNoaXBwaW5nIG1haWxlci4gVGhlIGNvcnJlY3QgY29tYmluYXRpb24gZGVwZW5kcyBvbiB0aGUgY2hhbm5lbC48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNob29zZSBhIFBhcGVyIFN0cnVjdHVyZSBmb3IgdGhlIEpvYiBJdCBNdXN0IFBlcmZvcm08L3NwYW4+PC9oMj4NCjx0YWJsZT4NCjx0aGVhZD4NCjx0cj4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U3RydWN0dXJlPC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlN1aXRhYmxlIGFwcGxpY2F0aW9uczwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5NYWluIGFkdmFudGFnZXM8L3NwYW4+PC90aD4NCjx0aD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VHJhZGUtb2ZmIHRvIGNoZWNrPC9zcGFuPjwvdGg+DQo8L3RyPg0KPC90aGVhZD4NCjx0Ym9keT4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFwZXIgc2xlZXZlIG9yIGJlbGx5IGJhbmQ8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9sZGVkIHNoaXJ0cywgc29ja3MsIHVuZGVyd2VhciBhbmQgbGlnaHR3ZWlnaHQgYmFzaWNzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkxvdyBtYXRlcmlhbCB1c2UsIHZpc2libGUgcHJvZHVjdCBhbmQgZWZmaWNpZW50IHBhY2tpbmc8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UHJvdmlkZXMgbGltaXRlZCBlZGdlIGFuZCB0cmFuc2l0IHByb3RlY3Rpb248L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkZvbGRpbmcgY2FydG9uPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFwcGFyZWwgYmFzaWNzLCBzbWFsbCBhY2Nlc3NvcmllcyBhbmQgcmV0YWlsIG11bHRpLXBhY2tzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNhbiBzaGlwIGZsYXQsIHN1cHBvcnRzIGRldGFpbGVkIHByaW50aW5nIGFuZCB1c2VzIHJldGFpbCBzcGFjZSBlZmZpY2llbnRseTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Cb2FyZCBzdGlmZm5lc3MgYW5kIGNsb3N1cmUgc3R5bGUgbXVzdCBtYXRjaCB0aGUgcGFja2VkIHdlaWdodDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TGlkLWFuZC1iYXNlIHBhcGVyYm9hcmQgYm94PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlNob2VzLCBhcHBhcmVsIGdpZnQgcGFja3MgYW5kIG1lZGl1bS1zaXplIGZhc2hpb24gcHJvZHVjdHM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2ltcGxlIG9wZW5pbmcsIGdvb2Qgc3RhY2tpbmcgc2hhcGUgYW5kIGEgZmFtaWxpYXIgcmV0YWlsIHByZXNlbnRhdGlvbjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Mb29zZSBsaWRzLCB3ZWFrIGNvcm5lcnMgYW5kIHBvb3IgcHJvZHVjdCBmaXQgY2FuIHJlZHVjZSBwZXJjZWl2ZWQgcXVhbGl0eTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmlnaWQgYm94PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByZW1pdW0gY29sbGVjdGlvbnMsIGFjY2Vzc29yaWVzLCBwcmVzZW50YXRpb24ga2l0cyBhbmQgZ2lmdGluZzwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TdHJvbmcgc2hhcGUsIGNvbnRyb2xsZWQgcmV2ZWFsIGFuZCBhIGhpZ2hlciBwcmVzZW50YXRpb24gbGV2ZWw8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VXNlcyBtb3JlIHN0b3JhZ2UgYW5kIHNoaXBwaW5nIHZvbHVtZSBiZWNhdXNlIGl0IG5vcm1hbGx5IGRvZXMgbm90IGZvbGQgZmxhdDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29ycnVnYXRlZCBtYWlsZXI8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RWNvbW1lcmNlIGFwcGFyZWwsIHNob2VzLCBzdWJzY3JpcHRpb25zIGFuZCBzcG9ydHN3ZWFyIGtpdHM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SW1wcm92ZWQgdHJhbnNpdCBwcm90ZWN0aW9uIGFuZCBwcmFjdGljYWwgc2VsZi1sb2NraW5nIHN0cnVjdHVyZXM8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGlubmVyIHByZXNlbnRhdGlvbiBtYXkgZmVlbCBiYXNpYyB1bmxlc3MgdGlzc3VlLCBwcmludGluZyBvciBhbiBpbnNlcnQgaXMgcGxhbm5lZDwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFwZXIgc2hvcHBpbmcgYmFnPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkJvdXRpcXVlIHJldGFpbCwgbGF1bmNoZXMsIGV4aGliaXRpb25zIGFuZCBnaWZ0IHB1cmNoYXNlczwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db252ZW5pZW50IGNhcnJ5aW5nIHN1cmZhY2Ugd2l0aCBzdHJvbmcgYnJhbmQgdmlzaWJpbGl0eTwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5IYW5kbGUgc3RyZW5ndGggYW5kIGJvdHRvbSBjb25zdHJ1Y3Rpb24gbXVzdCBtYXRjaCB0aGUgZXhwZWN0ZWQgbG9hZDwvc3Bhbj48L3RkPg0KPC90cj4NCjwvdGJvZHk+DQo8L3RhYmxlPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBzdHJ1Y3R1cmUgc2hvdWxkIGZvbGxvdyB0aGUgcHJvZHVjdCBhbmQgY2hhbm5lbCByYXRoZXIgdGhhbiB0aGUgb3RoZXIgd2F5IGFyb3VuZC4gRm9yIGV4YW1wbGUsIGEgcmlnaWQgbWFnbmV0aWMgYm94IG1heSBsb29rIGltcHJlc3NpdmUgaW4gYSBjb25jZXB0IGltYWdlLCBidXQgaXQgbWF5IGJlIHVubmVjZXNzYXJ5IGZvciBhIGhpZ2gtdm9sdW1lIGJhc2ljIFQtc2hpcnQuIEEgd2VsbC1zaXplZCBmb2xkaW5nIGNhcnRvbiB3aXRoIGNvbnRyb2xsZWQgdHlwb2dyYXBoeSBhbmQgYSBjbGVhbiBtYXR0ZSBzdXJmYWNlIGNhbiBzb21ldGltZXMgY3JlYXRlIGEgbW9yZSBhcHByb3ByaWF0ZSByZXRhaWwgcmVzdWx0Ljwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjwhLS0gSU1BR0VfU0xPVF8yIC0tPjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q2FsY3VsYXRlIFNpemUgRnJvbSB0aGUgUGFja2VkIFByb2R1Y3QsIE5vdCB0aGUgR2FybWVudCBQYXR0ZXJuPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGRpbWVuc2lvbnMgb2YgYW4gdW5mb2xkZWQgZ2FybWVudCBkbyBub3QgdGVsbCBhIHBhY2thZ2luZyBtYW51ZmFjdHVyZXIgaG93IHRoZSBwcm9kdWN0IHdpbGwgc2l0IGluc2lkZSBhIGJveC4gUGFja2FnaW5nIHNpemUgc2hvdWxkIGJlIGRldmVsb3BlZCBmcm9tIGEgcmVhbCBwYWNrZWQgc2FtcGxlLjwvc3Bhbj4NCjxvbD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb2xkIG9yIGFycmFuZ2UgdGhlIHByb2R1Y3QgdXNpbmcgdGhlIGV4YWN0IG1ldGhvZCBpbnRlbmRlZCBmb3IgcHJvZHVjdGlvbi48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BZGQgYW55IHRpc3N1ZSBwYXBlciwgaW5uZXIgY2FyZCwgc2xlZXZlLCByaWJib24sIGluc2VydCBvciBhY2Nlc3NvcnkgdGhhdCB3aWxsIGJlIHBhY2tlZCB3aXRoIGl0Ljwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPk1lYXN1cmUgdGhlIGZpbmFsIHBhY2tlZCBsZW5ndGgsIHdpZHRoIGFuZCBoZWlnaHQgd2l0aG91dCBjb21wcmVzc2luZyB0aGUgcHJvZHVjdCBtb3JlIHRoYW4gdGhlIHJldGFpbCB0ZWFtIGNvbnNpZGVycyBhY2NlcHRhYmxlLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNvbmZpcm0gaG93IGVhc2lseSB3b3JrZXJzIGNhbiBpbnNlcnQgYW5kIHJlbW92ZSB0aGUgcHJvZHVjdC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5UZXN0IHdoZXRoZXIgdGhlIHBhY2tlZCBwcm9kdWN0IHN0aWxsIGxvb2tzIG9yZ2FuaXplZCBhZnRlciB0aGUgYm94IGhhcyBiZWVuIG1vdmVkLCBzdGFja2VkIGFuZCByZW9wZW5lZC48L3NwYW4+PC9saT4NCjwvb2w+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VG9vIG11Y2ggaW50ZXJuYWwgc3BhY2UgY2FuIGFsbG93IGEgZ2FybWVudCB0byBzaGlmdCBhbmQgbG9zZSBpdHMgaW50ZW5kZWQgZm9sZC4gVG9vIGxpdHRsZSBzcGFjZSBjYW4gY3JlYXRlIGJ1bGdpbmcgcGFuZWxzLCBkaWZmaWN1bHQgY2xvc3VyZXMgYW5kIHNsb3cgcGFja2luZy4gVGhlIGNvcnJlY3QgYWxsb3dhbmNlIGRlcGVuZHMgb24gdGhlIGZhYnJpYyB0aGlja25lc3MsIG51bWJlciBvZiBpdGVtcywgZm9sZGluZyBtZXRob2QsIGJvYXJkIHN0cnVjdHVyZSBhbmQgd2hldGhlciB0aGUgcHJvZHVjdCBtdXN0IGJlIHJlbW92ZWQgd2l0aG91dCBkaXN0dXJiaW5nIHRoZSBwcmVzZW50YXRpb24uPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9yIHByb2R1Y3RzIHdpdGggbXVsdGlwbGUgc2l6ZXMsIGRvIG5vdCBhc3N1bWUgZXZlcnkgc2l6ZSBuZWVkcyBhIGRpZmZlcmVudCBib3guIEEgYnJhbmQgbWF5IGJlIGFibGUgdG8gdXNlIG9uZSBzdHJ1Y3R1cmFsIGZvb3RwcmludCBmb3Igc2V2ZXJhbCBTS1VzIGFuZCBtYW5hZ2UgdGhlIHZhcmlhdGlvbiB0aHJvdWdoIGZvbGRpbmcgaW5zdHJ1Y3Rpb25zLCBsYWJlbHMsIHNsZWV2ZXMgb3IgYSBsaW1pdGVkIG51bWJlciBvZiBib3ggZGVwdGhzLiBUaGlzIHNob3VsZCBiZSBjb25maXJtZWQgd2l0aCBwYWNraW5nIHRyaWFscyByYXRoZXIgdGhhbiBlc3RpbWF0ZWQgZnJvbSBhIHNwcmVhZHNoZWV0IGFsb25lLjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2VsZWN0IE1hdGVyaWFsIGFzIFBhcnQgb2YgdGhlIFN0cnVjdHVyZTwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlBhcGVyIHNlbGVjdGlvbiBzaG91bGQgYmUgZXZhbHVhdGVkIHRvZ2V0aGVyIHdpdGggYm94IGRlc2lnbiwgcHJpbnRpbmcsIHN1cmZhY2UgdHJlYXRtZW50IGFuZCBkaXN0cmlidXRpb24gY29uZGl0aW9ucy4gQXNraW5nIG9ubHkgZm9yIGEgY2VydGFpbiBwYXBlciB0aGlja25lc3MgY2FuIGxlYWQgdG8gdGhlIHdyb25nIHJlc3VsdCBiZWNhdXNlIHR3byBtYXRlcmlhbHMgd2l0aCBzaW1pbGFyIHN0YXRlZCB3ZWlnaHRzIG1heSBiZWhhdmUgZGlmZmVyZW50bHkgYWZ0ZXIgcHJpbnRpbmcsIHNjb3JpbmcgYW5kIGZvbGRpbmcuPC9zcGFuPg0KPGgzPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Jdm9yeSBvciBjb2F0ZWQgcGFwZXJib2FyZDwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoaXMgaXMgYSBwcmFjdGljYWwgZGlyZWN0aW9uIGZvciBmb2xkaW5nIGNhcnRvbnMgdGhhdCBuZWVkIGEgc21vb3RoIHN1cmZhY2UsIGNsZWFuIGNvbG9yIHJlcHJvZHVjdGlvbiBhbmQgZGV0YWlsZWQgcmV0YWlsIGFydHdvcmsuIFRoZSBib2FyZCBzcGVjaWZpY2F0aW9uIHNob3VsZCBiZSB0ZXN0ZWQgYWdhaW5zdCB0aGUgcHJvZHVjdCB3ZWlnaHQsIGNhcnRvbiBkaW1lbnNpb25zIGFuZCBjbG9zdXJlIGRlc2lnbi48L3NwYW4+DQo8aDM+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPktyYWZ0IHBhcGVyIGFuZCBrcmFmdCBib2FyZDwvc3Bhbj48L2gzPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPktyYWZ0IHN1cmZhY2VzIGNhbiBzdXBwb3J0IGEgbmF0dXJhbCwgdXRpbGl0YXJpYW4gb3IgY3JhZnQtb3JpZW50ZWQgdmlzdWFsIGRpcmVjdGlvbi4gRGFyayBjb2xvcnMsIGZpbmUgZ3JhZGllbnRzIGFuZCB2ZXJ5IHNtYWxsIHJldmVyc2UgdGV4dCBtYXkgcmVxdWlyZSB0ZXN0aW5nIGJlY2F1c2UgdGhlIGJhc2UgcGFwZXIgY29sb3IgYWZmZWN0cyB0aGUgcHJpbnRlZCBhcHBlYXJhbmNlLjwvc3Bhbj4NCjxoMz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29ycnVnYXRlZCBwYXBlcmJvYXJkPC9zcGFuPjwvaDM+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29ycnVnYXRlZCBzdHJ1Y3R1cmVzIGFyZSB1c2VmdWwgd2hlbiB0aGUgcGFja2FnZSBtdXN0IHJlc2lzdCBoYW5kbGluZywgc3RhY2tpbmcgb3IgZWNvbW1lcmNlIGRlbGl2ZXJ5LiBUaGUgZmx1dGUsIG91dGVyIGxpbmVyIGFuZCBwcmludGluZyBtZXRob2Qgc2hvdWxkIGJlIHNlbGVjdGVkIGFjY29yZGluZyB0byB0aGUgZGVzaXJlZCBiYWxhbmNlIGJldHdlZW4gcHJvdGVjdGlvbiBhbmQgcmV0YWlsIGFwcGVhcmFuY2UuPC9zcGFuPg0KPGgzPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5HcmV5Ym9hcmQgd3JhcHBlZCB3aXRoIHByaW50ZWQgb3Igc3BlY2lhbHR5IHBhcGVyPC9zcGFuPjwvaDM+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhpcyBjb25zdHJ1Y3Rpb24gaXMgY29tbW9ubHkgY29uc2lkZXJlZCBmb3IgcmlnaWQgcHJlc2VudGF0aW9uIGJveGVzLiBUaGUgZ3JleWJvYXJkIHByb3ZpZGVzIHRoZSBmb3JtIHdoaWxlIHRoZSBvdXRlciB3cmFwcGluZyBwYXBlciBjYXJyaWVzIHRoZSBjb2xvciwgdGV4dHVyZSBhbmQgZmluaXNoaW5nLiBTaGFycCBjb3JuZXJzLCB3cmFwcGluZyBzZWFtcyBhbmQgc3VyZmFjZSBjbGVhbmxpbmVzcyBzaG91bGQgYmUgaW5jbHVkZWQgaW4gc2FtcGxlIGFwcHJvdmFsLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNsYWltcyBzdWNoIGFzIHJlY3ljbGVkIGNvbnRlbnQsIHJlY3ljbGFiaWxpdHksIGNlcnRpZmllZCBzb3VyY2luZyBvciBjb21wb3N0YWJpbGl0eSBzaG91bGQgb25seSBiZSBwcmludGVkIGFmdGVyIHRoZSByZWxldmFudCBtYXRlcmlhbCBzcGVjaWZpY2F0aW9uIGFuZCBsb2NhbCBkaXNwb3NhbCBjb25kaXRpb25zIGhhdmUgYmVlbiB2ZXJpZmllZC4gQSBicm93biBwYXBlciBhcHBlYXJhbmNlIGFsb25lIGRvZXMgbm90IHByb3ZlIGEgcGFydGljdWxhciBlbnZpcm9ubWVudGFsIHBlcmZvcm1hbmNlLjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QnVpbGQgYSBMb2dvIGFuZCBJbmZvcm1hdGlvbiBTeXN0ZW0gVGhhdCBXb3JrcyBBY3Jvc3MgRXZlcnkgU0tVPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TG9nbyBwcmludGluZyBpcyBub3Qgc2ltcGx5IGEgZGVjaXNpb24gYmV0d2VlbiBmdWxsIGNvbG9yIGFuZCBmb2lsLiBUaGUgYXJ0d29yayBtdXN0IGZ1bmN0aW9uIGFjcm9zcyBib3ggc2l6ZXMsIHBhcGVyIGJhZ3MsIGxhYmVscyBhbmQgZGlmZmVyZW50IHByb2R1Y3QgY29sbGVjdGlvbnMuPC9zcGFuPg0KPGgzPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5DcmVhdGUgYSBjbGVhciBpbmZvcm1hdGlvbiBoaWVyYXJjaHk8L3NwYW4+PC9oMz4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TZXBhcmF0ZSBwZXJtYW5lbnQgYnJhbmQgZWxlbWVudHMgZnJvbSB2YXJpYWJsZSBwcm9kdWN0IGluZm9ybWF0aW9uOjwvc3Bhbj4NCjx1bD4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPlBlcm1hbmVudDo8L3N0cm9uZz4gYnJhbmQgbWFyaywgY29yZSBjb2xvciwgcGF0dGVybiBhbmQgc3RhbmRhcmQgbWVzc2FnZS48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPkNvbGxlY3Rpb24tc3BlY2lmaWM6PC9zdHJvbmc+IGNhbXBhaWduIG5hbWUsIHNlYXNvbmFsIGdyYXBoaWMgb3IgYXRobGV0ZSBjb2xsYWJvcmF0aW9uLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+U0tVLXNwZWNpZmljOjwvc3Ryb25nPiBzaXplLCBjb2xvciwgc3R5bGUgY29kZSwgYmFyY29kZSwgY2FyZSBpbmZvcm1hdGlvbiBhbmQgbWFya2V0LXNwZWNpZmljIGxhYmVscy48L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhpcyBzZXBhcmF0aW9uIG1ha2VzIHJlcGVhdCBvcmRlcnMgZWFzaWVyLiBUaGUgcHJpbWFyeSBib3ggbWF5IHJlbWFpbiB1bmNoYW5nZWQgd2hpbGUgYSBsYWJlbCwgc2xlZXZlIG9yIHByaW50ZWQgcGFuZWwgY2FycmllcyB0aGUgdmFyaWFibGUgaW5mb3JtYXRpb24uPC9zcGFuPg0KPGgzPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5QcmVwYXJlIGFydHdvcmsgZm9yIHRoZSByZWFsIHByaW50aW5nIHByb2Nlc3M8L3NwYW4+PC9oMz4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5TdXBwbHkgZWRpdGFibGUgdmVjdG9yIGxvZ28gYXJ0d29yayB3aGVuIHBvc3NpYmxlLiBEZWZpbmUgd2hldGhlciBjcml0aWNhbCBjb2xvcnMgc2hvdWxkIGZvbGxvdyBhIFBhbnRvbmUgcmVmZXJlbmNlIG9yIGJlIHByb2R1Y2VkIHRocm91Z2ggQ01ZSy4gU21hbGwgdGV4dCwgdGhpbiBsaW5lcywgZ3JhZGllbnRzIGFuZCByZXZlcnNlZCBsb2dvcyBzaG91bGQgYmUgcmV2aWV3ZWQgYXQgYWN0dWFsIHByaW50ZWQgc2l6ZSwgbm90IG9ubHkgb24gYSBsYXJnZSBjb21wdXRlciBzY3JlZW4uPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmVzZXJ2ZSBhIGNsZWFuIHpvbmUgZm9yIGJhcmNvZGVzIGFuZCByZXRhaWwgbGFiZWxzIGluc3RlYWQgb2YgcGxhY2luZyB0aGVtIG92ZXIgcGF0dGVybnMsIGZvbGRzIG9yIHRleHR1cmVkIGZpbmlzaGVzLiBUaGUgZGllbGluZSBzaG91bGQgYWxzbyBzaG93IGdsdWUgYXJlYXMsIGZvbGQgbGluZXMsIGJsZWVkLCBvcmllbnRhdGlvbiBhbmQgcGFuZWxzIHRoYXQgbWF5IGJlIGhpZGRlbiBhZnRlciBhc3NlbWJseS48L3NwYW4+DQo8aDM+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlVzZSBmaW5pc2hpbmcgd2l0aCBhIHB1cnBvc2U8L3NwYW4+PC9oMz4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb2lsIHN0YW1waW5nIGNhbiBjcmVhdGUgYSBjb250cm9sbGVkIGhpZ2hsaWdodC4gRW1ib3NzaW5nIG9yIGRlYm9zc2luZyBjYW4gYWRkIHRhY3RpbGUgZGVwdGguIFNwb3QgVVYgY2FuIGNvbnRyYXN0IHdpdGggYSBtYXR0ZSBiYWNrZ3JvdW5kLiBJbnNpZGUgcHJpbnRpbmcgY2FuIGltcHJvdmUgdGhlIG9wZW5pbmcgZXhwZXJpZW5jZS4gTm9uZSBvZiB0aGVzZSBlZmZlY3RzIG5lZWRzIHRvIGNvdmVyIHRoZSBlbnRpcmUgcGFja2FnZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb3IgbWFueSBmYXNoaW9uIHByb2plY3RzLCBvbmUgd2VsbC1wb3NpdGlvbmVkIGZpbmlzaCBvbiB0aGUgbG9nbyBpcyBtb3JlIGVmZmVjdGl2ZSB0aGFuIGNvbWJpbmluZyBmb2lsLCBlbWJvc3NpbmcsIHNwb3QgVVYgYW5kIGNvbXBsZXggcGF0dGVybnMgb24gZXZlcnkgcGFuZWwuIEVhY2ggYWRkaXRpb25hbCBwcm9jZXNzIGludHJvZHVjZXMgYW5vdGhlciBhcHByb3ZhbCBwb2ludCBmb3IgcmVnaXN0cmF0aW9uLCBzdXJmYWNlIGNvbnNpc3RlbmN5IGFuZCByZXBlYXQtb3JkZXIgbWF0Y2hpbmcuPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PCEtLSBJTUFHRV9TTE9UXzMgLS0+PC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBjb29yZGluYXRlZCBwYWNrYWdpbmcgZmFtaWx5IGRvZXMgbm90IHJlcXVpcmUgdGhlIHBhcGVyIGJveCBhbmQgcGFwZXIgYmFnIHRvIGxvb2sgaWRlbnRpY2FsLiBUaGV5IHNob3VsZCBzaGFyZSByZWNvZ25pemFibGUgZWxlbWVudHMgc3VjaCBhcyBjb2xvciByZWxhdGlvbnNoaXBzLCBsb2dvIHNjYWxlLCB0eXBvZ3JhcGh5LCBtYXRlcmlhbCBjaGFyYWN0ZXIgb3IgYSByZXBlYXRlZCBncmFwaGljIGRldmljZS4gVGhlIGJhZyBtYXkgdXNlIGEgc2ltcGxpZmllZCBvbmUtY29sb3IgbG9nbyB3aGlsZSB0aGUgYm94IGNhcnJpZXMgdGhlIGZ1bGxlciBjb2xsZWN0aW9uIGFydHdvcmsuPC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EZXNpZ24gdGhlIFJldGFpbCBQcmVzZW50YXRpb24gQXJvdW5kIFJlYWwgU3RvcmUgT3BlcmF0aW9uczwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJldGFpbCBwYWNrYWdpbmcgbXVzdCB3b3JrIGJlZm9yZSwgZHVyaW5nIGFuZCBhZnRlciB0aGUgY3VzdG9tZXIgc2VlcyBpdC4gQSB2aXN1YWxseSBpbXByZXNzaXZlIGJveCBjYW4gY3JlYXRlIHByb2JsZW1zIGlmIHN0YWZmIG5lZWQgdG9vIGxvbmcgdG8gYXNzZW1ibGUgaXQsIGNhbm5vdCBpZGVudGlmeSB0aGUgU0tVIG9yIHN0cnVnZ2xlIHRvIHJlcGFjayBhIGRpc3BsYXllZCBpdGVtLjwvc3Bhbj4NCg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJldmlldyB0aGUgcGFja2FnZSBmcm9tIHRoZSByZXRhaWxlcuKAmXMgcGVyc3BlY3RpdmU6PC9zcGFuPg0KPHVsPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNhbiBzdGFmZiBpZGVudGlmeSB0aGUgc2l6ZSBhbmQgY29sb3Igd2l0aG91dCBvcGVuaW5nIHRoZSBwYWNrYWdlPzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRvZXMgdGhlIGJveCBzdGFjayBpbiB0aGUgaW50ZW5kZWQgb3JpZW50YXRpb24gd2l0aG91dCBzbGlkaW5nIG9yIGNvbGxhcHNpbmc/PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+SXMgdGhlIGZyb250LWZhY2luZyBwYW5lbCBjbGVhciB3aGVuIHNldmVyYWwgcHJvZHVjdHMgYXJlIHBsYWNlZCB0b2dldGhlcj88L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5DYW4gcHJpY2UgbGFiZWxzIG9yIG1hcmtldC1zcGVjaWZpYyBzdGlja2VycyBiZSBhZGRlZCB3aXRob3V0IGNvdmVyaW5nIHRoZSBsb2dvPzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNhbiB0aGUgcHJvZHVjdCBiZSByZW1vdmVkIGFuZCByZXR1cm5lZCB0byB0aGUgcGFja2FnZSBuZWF0bHk/PC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RG9lcyB0aGUgcGFwZXIgYmFnIGZpdCB0aGUgYm94IHdpdGhvdXQgZXhjZXNzaXZlIGVtcHR5IHNwYWNlPzwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFyZSBoYW5kbGVzIGFuZCBib3R0b20gcmVpbmZvcmNlbWVudCBhcHByb3ByaWF0ZSBmb3IgdGhlIGV4cGVjdGVkIHB1cmNoYXNlIHdlaWdodD88L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U3BvcnRzd2VhciBsaW5lcyBvZnRlbiBjb250YWluIG1hbnkgdmFyaWF0aW9ucyBhY3Jvc3Mgc2l6ZSwgY29sb3IgYW5kIHByb2R1Y3QgdHlwZS4gQSBzdHJvbmcgdmlzdWFsIGlkZW50aXR5IHNob3VsZCBub3QgcmVkdWNlIGludmVudG9yeSBjbGFyaXR5LiBVc2UgY29uc2lzdGVudCBsYWJlbCBwb3NpdGlvbnMsIHJlYWRhYmxlIHNpemUgY29kaW5nIGFuZCBjb250cm9sbGVkIGNvbG9yIGluZGljYXRvcnMgc28gd2FyZWhvdXNlIGFuZCByZXRhaWwgdGVhbXMgY2FuIGRpc3Rpbmd1aXNoIFNLVXMgcXVpY2tseS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Gb290d2VhciBwYWNrYWdpbmcgbmVlZHMgYSBkaWZmZXJlbnQgYXBwcm9hY2ggZnJvbSBmb2xkZWQgYXBwYXJlbC4gU2hvZXMgcGxhY2UgbW9yZSBsb2FkIG9uIHRoZSBiYXNlIGFuZCBhcmUgY29tbW9ubHkgc3RhY2tlZC4gQ29tcGFjdCBhY2Nlc3NvcmllcyBtYXkgbmVlZCBpbnNlcnRzIG9yIHRpc3N1ZSB0byBjb250cm9sIG1vdmVtZW50LiBMaWdodHdlaWdodCBnYXJtZW50cyBtYXkgbmVlZCB2ZXJ5IGxpdHRsZSBzdHJ1Y3R1cmFsIHN1cHBvcnQgYnV0IHN0aWxsIHJlcXVpcmUgYSBwcmVjaXNlIGZvbGQgYW5kIGFuIG9yZGVybHkgb3BlbmluZyBleHBlcmllbmNlLjwvc3Bhbj4NCjxoMj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGxhbiBFY29tbWVyY2UgUHJvdGVjdGlvbiBTZXBhcmF0ZWx5IEZyb20gQm91dGlxdWUgUHJlc2VudGF0aW9uPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QSBib3V0aXF1ZSBwYXBlciBiYWcgaXMgaW50ZW5kZWQgZm9yIGNhcnJ5aW5nIGFuZCBicmFuZCBleHBvc3VyZSwgbm90IHVuY29udHJvbGxlZCBwYXJjZWwgaGFuZGxpbmcuIExpa2V3aXNlLCBhIHByZW1pdW0gcmV0YWlsIGJveCBjYW4gc3RpbGwgZGV2ZWxvcCBydWJiZWQgY29ybmVycyB3aGVuIGl0IG1vdmVzIGZyZWVseSBpbnNpZGUgYW4gb3ZlcnNpemVkIGV4cG9ydCBjYXJ0b24uPC9zcGFuPg0KDQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9yIGVjb21tZXJjZSBhbmQgaW50ZXJuYXRpb25hbCBkaXN0cmlidXRpb24sIHJldmlldzo8L3NwYW4+DQo8dWw+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciB0aGUgcmV0YWlsIHBhY2thZ2UgbmVlZHMgYSBzZXBhcmF0ZSBjb3JydWdhdGVkIG1haWxlci48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Ib3cgbXVjaCBlbXB0eSBzcGFjZSByZW1haW5zIGFyb3VuZCB0aGUgcHJvZHVjdC48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaGV0aGVyIGNvcm5lcnMsIGhhbmRsZXMgb3IgY2xvc3VyZXMgY2FuIHJ1YiBhZ2FpbnN0IG5lYXJieSBwYWNrYWdlcy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Ib3cgYm94ZXMgd2lsbCBiZSBhcnJhbmdlZCBpbnNpZGUgdGhlIG1hc3RlciBjYXJ0b24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciB0aGUgcGFja2FnZSBtdXN0IHN1cnZpdmUgc3RhY2tpbmcgaW4gYSB3YXJlaG91c2UuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+V2hldGhlciB0aGUgb3V0ZXIgY2FydG9uIHByb3RlY3RzIHByaW50ZWQgc3VyZmFjZXMgZnJvbSBkaXJ0IGFuZCBtb2lzdHVyZSBleHBvc3VyZSBkdXJpbmcgbm9ybWFsIGxvZ2lzdGljcyBoYW5kbGluZy48L3NwYW4+PC9saT4NCjwvdWw+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VGhlIGdvYWwgaXMgbm90IHRvIGFkZCBtb3JlIHBhY2thZ2luZyBieSBkZWZhdWx0LiBJdCBpcyB0byB1c2UgdGhlIHNtYWxsZXN0IHByYWN0aWNhbCBwYWNrYWdpbmcgc3lzdGVtIHRoYXQgc3RpbGwgcHJvdGVjdHMgdGhlIHByb2R1Y3QgYW5kIG1haW50YWlucyB0aGUgaW50ZW5kZWQgcHJlc2VudGF0aW9uIHRocm91Z2ggdGhlIGV4cGVjdGVkIGRpc3RyaWJ1dGlvbiByb3V0ZS48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFwcHJvdmUgU2FtcGxlcyBUaHJvdWdoIGEgUGFja2FnaW5nIFRlc3QgU2VxdWVuY2U8L3NwYW4+PC9oMj4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHNjcmVlbiByZW5kZXJpbmcgY2FuIGNvbmZpcm0gdGhlIGdlbmVyYWwgYXJ0d29yayBkaXJlY3Rpb24sIGJ1dCBpdCBjYW5ub3QgZnVsbHkgc2hvdyBwYXBlciBzdGlmZm5lc3MsIGNsb3N1cmUgcmVzaXN0YW5jZSwgaGFuZGxlIGNvbWZvcnQsIHN1cmZhY2UgcnViYmluZyBvciBob3cgYSBmb2xkZWQgZ2FybWVudCBiZWhhdmVzIGluc2lkZSB0aGUgcGFja2FnZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5BIHByYWN0aWNhbCBhcHByb3ZhbCBzZXF1ZW5jZSBjYW4gaW5jbHVkZTo8L3NwYW4+DQo8b2w+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5TdHJ1Y3R1cmFsIHNhbXBsZTo8L3N0cm9uZz4gY29uZmlybSBzaXplLCBvcGVuaW5nLCBmb2xkaW5nLCBwcm9kdWN0IGZpdCBhbmQgcGFja2luZyBtZXRob2QuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5BcnR3b3JrIHByb29mOjwvc3Ryb25nPiBjb25maXJtIHBhbmVsIG9yaWVudGF0aW9uLCB0ZXh0LCBiYXJjb2RlIGFyZWEsIGxvZ28gc2NhbGUgYW5kIHZhcmlhYmxlIGRhdGEuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PHN0cm9uZz5QcmludGVkIHNhbXBsZTo8L3N0cm9uZz4gZXZhbHVhdGUgbWF0ZXJpYWwsIGNvbG9yIGRpcmVjdGlvbiwgZmluaXNoaW5nLCBlZGdlIHF1YWxpdHkgYW5kIGludGVyaW9yIHByZXNlbnRhdGlvbi48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij48c3Ryb25nPlBhY2tpbmcgdHJpYWw6PC9zdHJvbmc+IHBhY2sgdGhlIHJlYWwgcHJvZHVjdHMgdXNpbmcgdGhlIGludGVuZGVkIHJldGFpbCBvciBmdWxmaWxsbWVudCBwcm9jZXNzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPjxzdHJvbmc+TWFzdGVyIGFwcHJvdmFsIHNhbXBsZTo8L3N0cm9uZz4ga2VlcCBvbmUgc2lnbmVkIHJlZmVyZW5jZSBmb3IgcHJvZHVjdGlvbiBhbmQgZmluYWwgaW5zcGVjdGlvbi48L3NwYW4+PC9saT4NCjwvb2w+DQo8dGFibGU+DQo8dGhlYWQ+DQo8dHI+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlFDIHBvaW50PC9zcGFuPjwvdGg+DQo8dGg+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoYXQgdG8gaW5zcGVjdDwvc3Bhbj48L3RoPg0KPHRoPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaHkgaXQgbWF0dGVyczwvc3Bhbj48L3RoPg0KPC90cj4NCjwvdGhlYWQ+DQo8dGJvZHk+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb2R1Y3QgZml0PC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkZvbGQgc3RhYmlsaXR5LCBtb3ZlbWVudCwgYnVsZ2luZyBhbmQgZWFzZSBvZiByZW1vdmFsPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNvbmZpcm1zIHRoZSBzdHJ1Y3R1cmUgd29ya3Mgd2l0aCB0aGUgcmVhbCBwcm9kdWN0PC9zcGFuPjwvdGQ+DQo8L3RyPg0KPHRyPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Mb2dvIGFuZCBjb2xvcjwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5QbGFjZW1lbnQsIHNjYWxlLCBsZWdpYmlsaXR5IGFuZCBhcHByb3ZlZCBjb2xvciByZWZlcmVuY2U8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmVkdWNlcyB2aXNpYmxlIGluY29uc2lzdGVuY3kgYWNyb3NzIGEgcGFja2FnaW5nIGZhbWlseTwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+RmluaXNoaW5nPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkZvaWwgcmVnaXN0cmF0aW9uLCBlbWJvc3NpbmcgZGV0YWlsLCBsYW1pbmF0aW9uIGFuZCBzdXJmYWNlIG1hcmtzPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByZXZlbnRzIGEgcHJlbWl1bSBlZmZlY3QgZnJvbSBiZWNvbWluZyBhIGRlZmVjdCBmb2N1czwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QXNzZW1ibHk8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9sZCBkaXJlY3Rpb24sIGdsdWUgc2VhbSwgbGlkIGZpdCwgZHJhd2VyIG1vdmVtZW50IGFuZCBjbG9zdXJlPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkFmZmVjdHMgYm90aCBwYWNraW5nIHNwZWVkIGFuZCBjdXN0b21lciBoYW5kbGluZzwvc3Bhbj48L3RkPg0KPC90cj4NCjx0cj4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UGFwZXIgYmFnIHBlcmZvcm1hbmNlPC9zcGFuPjwvdGQ+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkhhbmRsZSBhdHRhY2htZW50LCBib3R0b20gc3VwcG9ydCBhbmQgZml0IHdpdGggdGhlIGJveGVkIHByb2R1Y3Q8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmVkdWNlcyB0aGUgcmlzayBvZiBmYWlsdXJlIGR1cmluZyByZXRhaWwgY2Fycnlpbmc8L3NwYW4+PC90ZD4NCjwvdHI+DQo8dHI+DQo8dGQ+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkNhcnRvbiBwYWNrLW91dDwvc3Bhbj48L3RkPg0KPHRkPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5RdWFudGl0eSBwZXIgY2FydG9uLCBvcmllbnRhdGlvbiwgZWRnZSBwcm90ZWN0aW9uIGFuZCB1bnVzZWQgc3BhY2U8L3NwYW4+PC90ZD4NCjx0ZD48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U3VwcG9ydHMgc2FmZXIgYW5kIG1vcmUgcHJlZGljdGFibGUgdHJhbnNwb3J0IHBsYW5uaW5nPC9zcGFuPjwvdGQ+DQo8L3RyPg0KPC90Ym9keT4NCjwvdGFibGU+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+PCEtLSBJTUFHRV9TTE9UXzQgLS0+PC9zcGFuPg0KPGgyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Db250cm9sIENvc3QgV2l0aG91dCBNYWtpbmcgdGhlIFBhY2thZ2luZyBMb29rIENoZWFwPC9zcGFuPjwvaDI+DQo8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Q29zdCByZWR1Y3Rpb24gc2hvdWxkIHJlbW92ZSBjb21wbGV4aXR5IHRoYXQgZG9lcyBub3QgaW1wcm92ZSBwcm9kdWN0IHByb3RlY3Rpb24sIHJldGFpbCBvcGVyYXRpb24gb3IgYnJhbmQgcmVjb2duaXRpb24uPC9zcGFuPg0KPHVsPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlN0YW5kYXJkaXplIGEgc21hbGwgbnVtYmVyIG9mIGJveCBmb290cHJpbnRzIGFjcm9zcyByZWxhdGVkIHByb2R1Y3RzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlVzZSBsYWJlbHMgb3Igc2xlZXZlcyB0byBtYW5hZ2Ugc2Vhc29uYWwgYW5kIFNLVSBjaGFuZ2VzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJlc2VydmUgcmlnaWQgYm94ZXMgZm9yIHByb2R1Y3RzIHdoZXJlIHRoZSBwcmVzZW50YXRpb24gbGV2ZWwganVzdGlmaWVzIHRoZSBhZGRlZCB2b2x1bWUuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+VXNlIGZvbGRpbmcgc3RydWN0dXJlcyB3aGVuIGZsYXQgZGVsaXZlcnkgYW5kIHN0b3JhZ2UgZWZmaWNpZW5jeSBhcmUgaW1wb3J0YW50Ljwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkxpbWl0IHByZW1pdW0gZmluaXNoaW5nIHRvIG9uZSBvciB0d28gdmlzaWJsZSBicmFuZCBlbGVtZW50cy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EZXNpZ24gdGhlIHBhcGVyIGJhZyBhcm91bmQgY29tbW9uIGJveGVkIHB1cmNoYXNlcyBpbnN0ZWFkIG9mIHByb2R1Y2luZyB0b28gbWFueSBiYWcgc2l6ZXMuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+UmV2aWV3IG1hc3Rlci1jYXJ0b24gdXRpbGl6YXRpb24gYmVmb3JlIGFwcHJvdmluZyBhIGxhcmdlIHJldGFpbCBib3guPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+QXZvaWQgaW5zZXJ0cyB3aGVuIHRoZSBwcm9kdWN0IGNhbiBiZSBwb3NpdGlvbmVkIHJlbGlhYmx5IHRocm91Z2ggZm9sZGluZywgdGlzc3VlIG9yIGEgc2ltcGxlIHBhcGVyIGJhbmQuPC9zcGFuPjwvbGk+DQo8L3VsPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRoZSBsb3dlc3QgdW5pdCBwcmljZSBpcyBub3QgYWx3YXlzIHRoZSBsb3dlc3QgcHJvamVjdCBjb3N0LiBBIGNoZWFwZXIgYm94IG1heSBjcmVhdGUgYWRkaXRpb25hbCBwYWNraW5nIGxhYm9yLCBkYW1hZ2VkIGNvcm5lcnMsIGluY29uc2lzdGVudCBmb2xkcyBvciBleGNlc3NpdmUgZnJlaWdodCB2b2x1bWUuIENvbXBhcmUgdGhlIGZ1bGwgcGFja2FnaW5nIHdvcmtmbG93IHJhdGhlciB0aGFuIG9uZSBxdW90ZWQgdW5pdCBwcmljZS48L3NwYW4+DQo8aDI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByZXBhcmUgYSBRdW90ZS1SZWFkeSBGYXNoaW9uIFBhY2thZ2luZyBCcmllZjwvc3Bhbj48L2gyPg0KPHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkEgY2xlYXIgYnJpZWYgaGVscHMgYSBzdXBwbGllciByZWNvbW1lbmQgdGhlIHJpZ2h0IHN0cnVjdHVyZSBpbnN0ZWFkIG9mIGd1ZXNzaW5nIGZyb20gYSBsb2dvIGFuZCByZWZlcmVuY2UgaW1hZ2UuIEluY2x1ZGU6PC9zcGFuPg0KPHVsPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByb2R1Y3QgbmFtZSBhbmQgY2xlYXIgcHJvZHVjdCBwaG90b2dyYXBocy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5EaW1lbnNpb25zIGFuZCB3ZWlnaHQgaW4gdGhlIGZpbmFsIHBhY2tlZCBjb25kaXRpb24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+Rm9sZGluZyBvciBwcm9kdWN0LWFycmFuZ2VtZW50IGluc3RydWN0aW9ucy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5SZXF1aXJlZCBib3gsIHNsZWV2ZSwgbWFpbGVyIG9yIHBhcGVyIGJhZyB0eXBlLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkV4cGVjdGVkIHF1YW50aXR5IGZvciBlYWNoIFNLVSBvciBhcnR3b3JrIHZlcnNpb24uPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+U2VsbGluZyBjaGFubmVsOiByZXRhaWwsIGVjb21tZXJjZSwgd2hvbGVzYWxlLCBnaWZ0IG9yIGV2ZW50Ljwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlRhcmdldCBtYXJrZXQgYW5kIHJlcXVpcmVkIHZhcmlhYmxlIGluZm9ybWF0aW9uLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlByZWZlcnJlZCBwYXBlciBhcHBlYXJhbmNlIGFuZCBwcmVzZW50YXRpb24gbGV2ZWwuPC9zcGFuPjwvbGk+DQogCTxsaT48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMTAlOyI+TG9nbyBhcnR3b3JrIGFuZCBicmFuZCBjb2xvciByZWZlcmVuY2VzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPlJlcXVpcmVkIHByaW50aW5nIGFuZCBmaW5pc2hpbmcgZWZmZWN0cy48L3NwYW4+PC9saT4NCiAJPGxpPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5JbnNlcnQsIHRpc3N1ZSwgcmliYm9uLCBoYW5kbGUgb3IgbGFiZWwgcmVxdWlyZW1lbnRzLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPkRlbGl2ZXJ5IGNvdW50cnkgYW5kIGFueSBpbXBvcnRhbnQgbGF1bmNoIGRlYWRsaW5lLjwvc3Bhbj48L2xpPg0KIAk8bGk+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTEwJTsiPldoZXRoZXIgYSBzdHJ1Y3R1cmFsIG9yIHByaW50ZWQgc2FtcGxlIGlzIHJlcXVpcmVkLjwvc3Bhbj48L2xpPg0KPC91bD4NCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5XaGVuIHRoZSBzdHJ1Y3R1cmUgaXMgbm90IHlldCBjb25maXJtZWQsIHNlbmQgdGhlIHBhY2tlZCBwcm9kdWN0IHNpemUsIHdlaWdodCwgc2VsbGluZyBjaGFubmVsIGFuZCB2aXN1YWwgZGlyZWN0aW9uIHRvIGEgPGEgaHJlZj0iaHR0cHM6Ly9ob3BnaWF5dnBuLmNvbS9jdXN0b20tcGFja2FnaW5nLWJveGVzLW1hbnVmYWN0dXJlci8iPnBhY2thZ2luZyBib3hlcyBtYW51ZmFjdHVyZXI8L2E+LiBUaGVzZSBkZXRhaWxzIHByb3ZpZGUgYSBzdHJvbmdlciBzdGFydGluZyBwb2ludCBmb3IgY29tcGFyaW5nIHBhcGVyIGJveGVzLCBiYWdzLCBtYXRlcmlhbHMsIHByaW50aW5nIG1ldGhvZHMgYW5kIHNhbXBsZSBvcHRpb25zIHRoYW4gcmVxdWVzdGluZyBhIHByaWNlIGZyb20gYXJ0d29yayBhbG9uZS48L3NwYW4+DQoNCjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDExMCU7Ij5Zb3UgY2FuIGFsc28gcmV2aWV3IDxhIGhyZWY9Imh0dHBzOi8vaG9wZ2lheXZwbi5jb20vcHJvZHVjdHMvZmFzaGlvbi1zcG9ydHN3ZWFyLXBhY2thZ2luZy8iPmZhc2hpb24gYW5kIHNwb3J0c3dlYXIgcGFja2FnaW5nIG9wdGlvbnM8L2E+IHdoZW4gc2VsZWN0aW5nIHN0cnVjdHVyZXMgZm9yIGFwcGFyZWwsIGZvb3R3ZWFyIGFuZCBhY2Nlc3Nvcmllcywgb3IgY29tcGFyZSA8YSBocmVmPSJodHRwczovL2hvcGdpYXl2cG4uY29tL3Byb2R1Y3RzL3BhcGVyLWJhZ3Mtd2l0aC1sb2dvLyI+cGFwZXIgYmFncyB3aXRoIGxvZ288L2E+IGZvciBtYXRjaGluZyByZXRhaWwgY2FycnkgcGFja2FnaW5nLjwvc3Bhbj4=', true);
    return is_string($content) ? $content : '';
}
