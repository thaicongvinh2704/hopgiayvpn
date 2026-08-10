<?php
/**
 * Deploys the prepared girdle packaging draft and its four local images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_GIRDLE_PACKAGING_SYNC_VERSION = '2026-08-10-v1';
const CUSTOM_BOX_GIRDLE_PACKAGING_VERSION_OPTION = 'custom_box_girdle_packaging_sync_version';
const CUSTOM_BOX_GIRDLE_PACKAGING_NOTICE_OPTION = 'custom_box_girdle_packaging_sync_notice';
const CUSTOM_BOX_GIRDLE_PACKAGING_MISSING_IMAGES_OPTION = 'custom_box_girdle_packaging_missing_images';
const CUSTOM_BOX_GIRDLE_PACKAGING_MISSING_SLOTS_OPTION = 'custom_box_girdle_packaging_missing_slots';
const CUSTOM_BOX_GIRDLE_PACKAGING_VALIDATION_FAILURES_OPTION = 'custom_box_girdle_packaging_validation_failures';

add_action('admin_init', 'custom_box_sync_girdle_packaging_post');
add_action('admin_notices', 'custom_box_girdle_packaging_admin_notice');

function custom_box_girdle_packaging_post_data(): array
{
    return array(
        'title' => 'Girdles in Product Packaging: Definition, Uses and Paper Sleeve Design',
        'slug' => 'girdle-product-packaging-definition-uses',
        'excerpt' => 'Learn what a girdle means in product packaging, how paper girdles and belly bands are used, how they compare with sleeves and boxes, and what to check before production.',
        'category' => array(
            'name' => 'Packaging Guides',
            'slug' => 'packaging-guides',
        ),
        'tags' => array(
            'Girdle Packaging' => 'girdle-packaging',
            'Paper Sleeve Packaging' => 'paper-sleeve-packaging',
            'Belly Band Packaging' => 'belly-band-packaging',
            'Packaging Design' => 'packaging-design',
            'Custom Paper Packaging' => 'custom-paper-packaging',
        ),
        'seo_title' => 'Girdle Packaging: Definition, Uses & Paper Sleeve Design',
        'seo_description' => 'Learn what a girdle means in product packaging, its uses, how it differs from belly bands, sleeves and boxes, and how to design a paper girdle.',
        'focus_keyword' => 'definition and uses of girdle in product packaging',
    );
}

function custom_box_girdle_packaging_images(): array
{
    return array(
        'featured' => array(
            'base' => 'girdle-product-packaging-paper-sleeve-guide',
            'alt' => 'Paper girdle sleeve wrapped around a custom product packaging box',
            'title' => 'Girdle Packaging and Paper Sleeve Design',
            'caption' => 'A paper girdle adds a printed branding layer around an existing product package.',
        ),
        'slot_1' => array(
            'base' => 'girdle-belly-band-sleeve-box-comparison',
            'alt' => 'Comparison of a paper girdle, full packaging sleeve and folding carton',
            'title' => 'Girdle vs Sleeve vs Full Carton',
            'caption' => 'A narrow girdle covers only part of the package, while a full sleeve or carton provides progressively greater coverage.',
        ),
        'slot_2' => array(
            'base' => 'paper-girdle-sleeve-fit-dieline',
            'alt' => 'Paper girdle dieline and finished sleeve fitted around a rigid box',
            'title' => 'Paper Girdle Fit and Dieline',
            'caption' => 'Sleeve dimensions must be validated on the finished package rather than treated as artwork dimensions alone.',
        ),
        'slot_3' => array(
            'base' => 'paper-girdle-packaging-use-cases',
            'alt' => 'Paper girdles used on a rigid box, soap product and folded textile bundle',
            'title' => 'Paper Girdle Packaging Use Cases',
            'caption' => 'Paper girdles can support branding, product identification and light bundling across different packaging applications.',
        ),
    );
}

function custom_box_girdle_packaging_content(): string
{
    $path = __DIR__ . '/post-content/girdle-product-packaging-definition-uses.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    return is_string($content) ? $content : '';
}

function custom_box_find_girdle_packaging_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_girdle_packaging_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_girdle_packaging_post_data();
    $post = custom_box_find_girdle_packaging_post($data['slug'], $data['title']);
    if (
        CUSTOM_BOX_GIRDLE_PACKAGING_SYNC_VERSION === get_option(CUSTOM_BOX_GIRDLE_PACKAGING_VERSION_OPTION)
        && $post
        && custom_box_girdle_packaging_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_girdle_packaging_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_GIRDLE_PACKAGING_VERSION_OPTION);
        update_option(CUSTOM_BOX_GIRDLE_PACKAGING_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_girdle_packaging_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_GIRDLE_PACKAGING_VERSION_OPTION, CUSTOM_BOX_GIRDLE_PACKAGING_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_GIRDLE_PACKAGING_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Girdle packaging draft synced: post ID %d, featured image %d, 3 inline figures, category Packaging Guides, 5 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_GIRDLE_PACKAGING_VERSION_OPTION);
    update_option(CUSTOM_BOX_GIRDLE_PACKAGING_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Girdle packaging sync is incomplete. Missing images: '
            . implode(', ', (array) get_option(CUSTOM_BOX_GIRDLE_PACKAGING_MISSING_IMAGES_OPTION, array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option(CUSTOM_BOX_GIRDLE_PACKAGING_MISSING_SLOTS_OPTION, array()),
                (array) get_option(CUSTOM_BOX_GIRDLE_PACKAGING_VALIDATION_FAILURES_OPTION, array())
            )),
    ), false);
}

function custom_box_upsert_girdle_packaging_post()
{
    $data = custom_box_girdle_packaging_post_data();
    $canonical_content = custom_box_girdle_packaging_content();
    if ('' === trim($canonical_content)) {
        return new WP_Error('girdle_packaging_content_missing', 'The canonical girdle packaging content bundle is missing.');
    }

    $post = custom_box_find_girdle_packaging_post($data['slug'], $data['title']);
    $payload = array(
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_type' => 'post',
        'post_excerpt' => $data['excerpt'],
    );

    if ($post) {
        $payload['ID'] = (int) $post->ID;
        $payload['post_status'] = in_array($post->post_status, array('publish', 'private'), true) ? $post->post_status : 'draft';
        if (
            !in_array($post->post_status, array('publish', 'private'), true)
            || '' === trim((string) $post->post_content)
            || false !== strpos((string) $post->post_content, 'IMAGE_SLOT_')
        ) {
            $payload['post_content'] = $canonical_content;
        }
        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = $canonical_content;
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_girdle_packaging_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_girdle_packaging_images($post_id);

    return $post_id;
}

function custom_box_sync_girdle_packaging_terms(int $post_id, array $data): void
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

function custom_box_sync_girdle_packaging_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_girdle_packaging_images() as $key => $image) {
        $attachment_id = custom_box_find_girdle_packaging_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_girdle_packaging_attachment($image['base'], $post_id, $image);
        }
        $url = $attachment_id ? wp_get_attachment_url($attachment_id) : false;
        if (!$attachment_id || !$url) {
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

        $marker = '<!-- girdle-packaging-image:' . $key . ' -->';
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

    update_option(CUSTOM_BOX_GIRDLE_PACKAGING_MISSING_IMAGES_OPTION, array_values(array_unique($missing_images)), false);
    update_option(CUSTOM_BOX_GIRDLE_PACKAGING_MISSING_SLOTS_OPTION, array_values(array_unique($missing_slots)), false);
}

function custom_box_find_girdle_packaging_attachment(string $base): int
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

function custom_box_create_girdle_packaging_attachment(string $base, int $post_id, array $image): int
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

function custom_box_girdle_packaging_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_girdle_packaging_post_data();
    $images = custom_box_girdle_packaging_images();
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

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_girdle_packaging_attachment($image['base']);
        $attachment = $attachment_id ? get_post($attachment_id) : null;
        if (
            !$attachment
            || 'attachment' !== $attachment->post_type
            || $post_id !== (int) $attachment->post_parent
            || $image['title'] !== $attachment->post_title
            || $image['caption'] !== $attachment->post_excerpt
            || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
        ) {
            $failures[] = $key . ' attachment metadata';
        }
    }

    $content = $post ? (string) $post->post_content : '';
    if (
        3 !== substr_count($content, '<!-- girdle-packaging-image:')
        || 3 !== preg_match_all('/<figure\b/i', $content, $unused)
        || 3 !== preg_match_all('/<img\s/i', $content, $unused)
    ) {
        $failures[] = 'inline image counts';
    }
    foreach (array('slot_1', 'slot_2', 'slot_3') as $key) {
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
    if ((array) get_option(CUSTOM_BOX_GIRDLE_PACKAGING_MISSING_IMAGES_OPTION, array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option(CUSTOM_BOX_GIRDLE_PACKAGING_MISSING_SLOTS_OPTION, array())) {
        $failures[] = 'missing slots';
    }

    update_option(CUSTOM_BOX_GIRDLE_PACKAGING_VALIDATION_FAILURES_OPTION, array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_girdle_packaging_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_GIRDLE_PACKAGING_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}
