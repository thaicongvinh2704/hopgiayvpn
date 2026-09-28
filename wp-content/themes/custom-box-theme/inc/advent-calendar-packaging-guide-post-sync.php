<?php
/**
 * Imports the Advent calendar packaging guide and its supporting images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_SYNC_VERSION = '2026-09-28-advent-calendar-packaging-guide-v1';
const CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_VERSION_OPTION = 'custom_box_advent_calendar_packaging_guide_sync_version';
const CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_NOTICE_OPTION = 'custom_box_advent_calendar_packaging_guide_sync_notice';
const CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_MISSING_IMAGES_OPTION = 'custom_box_advent_calendar_packaging_guide_missing_images';
const CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_MISSING_SLOTS_OPTION = 'custom_box_advent_calendar_packaging_guide_missing_slots';
const CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_VALIDATION_OPTION = 'custom_box_advent_calendar_packaging_guide_validation_failures';

add_action('admin_init', 'custom_box_sync_advent_calendar_packaging_guide');
add_action('admin_notices', 'custom_box_advent_calendar_packaging_guide_admin_notice');

function custom_box_advent_calendar_packaging_guide_data(): array
{
    return array(
        'title' => 'Advent Calendar Packaging: Box Structures, Inserts and Production Guide',
        'slug' => 'advent-calendar-packaging-guide',
        'excerpt' => 'Compare advent calendar packaging structures, inserts, sampling, assembly and production planning before requesting a custom quotation.',
        'category' => array(
            'name' => 'Packaging Guides',
            'slug' => 'packaging-guides',
        ),
        'tags' => array(
            'Advent Calendar Packaging' => 'advent-calendar-packaging',
            'Christmas Packaging' => 'christmas-packaging',
            'Custom Packaging' => 'custom-packaging',
            'Rigid Boxes' => 'rigid-boxes',
            'Packaging Inserts' => 'packaging-inserts',
        ),
        'seo_title' => 'Advent Calendar Packaging: Structures, Inserts & Production',
        'seo_description' => 'Compare advent calendar box structures, inserts, sampling, assembly and production planning. Build a supplier-ready brief before requesting a quote.',
        'focus_keyword' => 'advent calendar packaging',
        'canonical_path' => '/advent-calendar-packaging-guide/',
    );
}

function custom_box_advent_calendar_packaging_guide_images(): array
{
    return array(
        'featured' => array(
            'base' => 'advent-calendar-packaging-box-structures-inserts-production-guide',
            'extension' => 'webp',
            'alt' => 'Advent calendar packaging structures with doors, drawers and compartment inserts',
            'title' => 'Advent Calendar Packaging Structures and Inserts',
            'caption' => 'Compare the structural systems behind custom advent calendar packaging.',
        ),
        'slot_1' => array(
            'base' => 'advent-calendar-box-structure-options',
            'extension' => 'webp',
            'alt' => 'Door, drawer, book-style and individual-box advent calendar structures',
            'title' => 'Advent Calendar Box Structure Options',
            'caption' => 'Door, drawer, book-style and individual-box systems create different production and filling requirements.',
        ),
        'slot_2' => array(
            'base' => 'advent-calendar-insert-positioning-system',
            'extension' => 'webp',
            'alt' => 'Advent calendar outer box, numbered panel, insert compartments and products',
            'title' => 'Advent Calendar Insert Positioning System',
            'caption' => 'The insert connects each product with the correct opening and physical position.',
        ),
        'slot_3' => array(
            'base' => 'advent-calendar-reveal-map-packout-map',
            'extension' => 'webp',
            'alt' => 'Advent calendar reveal map and product pack-out map',
            'title' => 'Advent Calendar Reveal Map and Pack-Out Map',
            'caption' => 'Use separate reveal and pack-out maps to keep day numbers, compartments and products synchronized.',
        ),
        'slot_4' => array(
            'base' => 'advent-calendar-loaded-sample-assembly-process',
            'extension' => 'webp',
            'alt' => 'Advent calendar structural sample, components and fully loaded prototype',
            'title' => 'Advent Calendar Loaded Sample and Assembly',
            'caption' => 'Validate the real products, compartment positions and assembly sequence before bulk production.',
        ),
        'slot_5' => array(
            'base' => 'advent-calendar-production-approval-flow',
            'extension' => 'png',
            'alt' => 'Advent calendar structural approval and production workflow',
            'title' => 'Advent Calendar Production Approval Flow',
            'caption' => 'Build the production plan backward from the required pack-out or arrival date.',
        ),
    );
}

function custom_box_advent_calendar_packaging_guide_content(): string
{
    $path = __DIR__ . '/post-content/advent-calendar-packaging-guide.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    if (!is_string($content) || '' === trim($content)) {
        return '';
    }

    return str_replace('https://hopgiayvpn.com/', trailingslashit(home_url('/')), $content);
}

function custom_box_find_advent_calendar_packaging_guide_post(string $slug): ?WP_Post
{
    $post = get_page_by_path($slug, OBJECT, 'post');
    if ($post && 'trash' !== $post->post_status) {
        return $post;
    }

    $matches = get_posts(array(
        'post_type' => 'post',
        'post_status' => array('draft', 'publish', 'private', 'pending', 'future'),
        'title' => 'Advent Calendar Packaging: Box Structures, Inserts and Production Guide',
        'numberposts' => 1,
    ));
    return $matches ? $matches[0] : null;
}

function custom_box_sync_advent_calendar_packaging_guide(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_advent_calendar_packaging_guide_data();
    $post = custom_box_find_advent_calendar_packaging_guide_post($data['slug']);

    if (
        CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_SYNC_VERSION === get_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_VERSION_OPTION)
        && $post
        && custom_box_advent_calendar_packaging_guide_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_advent_calendar_packaging_guide_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_VERSION_OPTION);
        update_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_advent_calendar_packaging_guide_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_VERSION_OPTION, CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Advent calendar packaging guide imported as %s: post ID %d, featured image and 5 inline figures verified.',
                (string) get_post_status($post_id),
                (int) $post_id
            ),
        ), false);
    } else {
        delete_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_VERSION_OPTION);
        $failures = (array) get_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_VALIDATION_OPTION, array());
        update_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_NOTICE_OPTION, array(
            'success' => false,
            'message' => 'Advent calendar guide import is incomplete: ' . implode(', ', $failures),
        ), false);
    }
}

function custom_box_upsert_advent_calendar_packaging_guide_post()
{
    $data = custom_box_advent_calendar_packaging_guide_data();
    $post = custom_box_find_advent_calendar_packaging_guide_post($data['slug']);
    $content = custom_box_advent_calendar_packaging_guide_content();

    if ('' === trim($content)) {
        return new WP_Error('advent_calendar_guide_content_missing', 'The Advent calendar article content bundle is missing.');
    }

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
            $payload['post_content'] = $content;
        }
        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = $content;
        $payload['post_author'] = get_current_user_id();
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_advent_calendar_packaging_guide_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_post_meta($post_id, 'rank_math_canonical_url', home_url($data['canonical_path']));
    custom_box_sync_advent_calendar_packaging_guide_images($post_id);

    return $post_id;
}

function custom_box_sync_advent_calendar_packaging_guide_terms(int $post_id, array $data): void
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

function custom_box_advent_calendar_packaging_guide_upload_target(array $image): array
{
    $uploads = wp_get_upload_dir();
    $relative = '2026/09/' . $image['base'] . '.' . $image['extension'];
    return array(
        'relative' => $relative,
        'path' => trailingslashit($uploads['basedir']) . $relative,
        'url' => trailingslashit($uploads['baseurl']) . $relative,
    );
}

function custom_box_ensure_advent_calendar_packaging_guide_file(array $image): array
{
    $target = custom_box_advent_calendar_packaging_guide_upload_target($image);
    if (file_exists($target['path'])) {
        return $target;
    }

    $bundle_path = __DIR__ . '/product-sample-deploy-assets/uploads/' . $target['relative'];
    if (file_exists($bundle_path) && wp_mkdir_p(dirname($target['path'])) && copy($bundle_path, $target['path'])) {
        return $target;
    }

    return array();
}

function custom_box_find_advent_calendar_packaging_guide_attachment(string $base): int
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

function custom_box_create_advent_calendar_packaging_guide_attachment(int $post_id, array $image): int
{
    $target = custom_box_ensure_advent_calendar_packaging_guide_file($image);
    if (!$target) {
        return 0;
    }

    $type = wp_check_filetype(wp_basename($target['path']), null);
    $attachment_id = wp_insert_attachment(array(
        'guid' => $target['url'],
        'post_mime_type' => !empty($type['type']) ? $type['type'] : 'image/' . $image['extension'],
        'post_title' => $image['title'],
        'post_excerpt' => $image['caption'],
        'post_status' => 'inherit',
        'post_parent' => $post_id,
    ), $target['path'], $post_id, true);
    if (is_wp_error($attachment_id)) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_wp_attached_file', $target['relative']);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $target['path']);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);

    return (int) $attachment_id;
}

function custom_box_sync_advent_calendar_packaging_guide_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_advent_calendar_packaging_guide_images() as $key => $image) {
        $attachment_id = custom_box_find_advent_calendar_packaging_guide_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_advent_calendar_packaging_guide_attachment($post_id, $image);
        } else {
            $attached = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
            $uploads = wp_get_upload_dir();
            $attached_path = $attached ? trailingslashit($uploads['basedir']) . $attached : '';
            if (!$attached_path || !file_exists($attached_path)) {
                $target = custom_box_ensure_advent_calendar_packaging_guide_file($image);
                if ($target) {
                    update_post_meta($attachment_id, '_wp_attached_file', $target['relative']);
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    $metadata = wp_generate_attachment_metadata($attachment_id, $target['path']);
                    if (is_array($metadata)) {
                        wp_update_attachment_metadata($attachment_id, $metadata);
                    }
                }
            }
        }

        $url = $attachment_id ? wp_get_attachment_url($attachment_id) : false;
        if (!$attachment_id || !$url) {
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

        $marker = '<!-- advent-calendar-packaging-guide-image:' . $key . ' -->';
        $figure = $marker . "\n<figure><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';
        $wrapped_slot_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';

        if (preg_match($marker_pattern, $content)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
        } elseif (false !== strpos($content, $marker)) {
            $content = str_replace($marker, $figure, $content);
        } elseif (preg_match($wrapped_slot_pattern, $content)) {
            $content = preg_replace($wrapped_slot_pattern, $figure, $content, 1);
        } elseif (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
        } else {
            $missing_slots[] = $key;
        }
    }

    if ($post && $content !== (string) $post->post_content) {
        wp_update_post(array('ID' => $post_id, 'post_content' => $content));
    }

    update_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_MISSING_IMAGES_OPTION, array_values(array_unique($missing_images)), false);
    update_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_MISSING_SLOTS_OPTION, array_values(array_unique($missing_slots)), false);
}

function custom_box_advent_calendar_packaging_guide_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_advent_calendar_packaging_guide_data();
    $images = custom_box_advent_calendar_packaging_guide_images();
    $failures = array();

    if (!$post || 'post' !== $post->post_type || $data['title'] !== $post->post_title || $data['slug'] !== $post->post_name || $data['excerpt'] !== $post->post_excerpt) {
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

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_advent_calendar_packaging_guide_attachment($image['base']);
        $attachment = $attachment_id ? get_post($attachment_id) : null;
        if (!$attachment || 'attachment' !== $attachment->post_type || $post_id !== (int) $attachment->post_parent || $image['title'] !== $attachment->post_title || $image['caption'] !== $attachment->post_excerpt || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true) || !wp_get_attachment_url($attachment_id)) {
            $failures[] = $key . ' attachment metadata';
        }
    }

    $content = $post ? (string) $post->post_content : '';
    if (5 !== substr_count($content, '<!-- advent-calendar-packaging-guide-image:slot_') || 5 !== preg_match_all('/<figure\b/i', $content) || 5 !== preg_match_all('/<img\s/i', $content)) {
        $failures[] = 'inline image counts';
    }
    foreach ($images as $key => $image) {
        if ('featured' !== $key && false === strpos($content, $image['base'])) {
            $failures[] = $key . ' image filename';
        }
    }
    if (preg_match('/IMAGE_SLOT_[0-9]+/i', $content)) {
        $failures[] = 'image placeholders';
    }
    if (preg_match('/<h1\b/i', $content)) {
        $failures[] = 'content contains H1';
    }

    $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    if (is_wp_error($categories) || array($data['category']['slug']) !== array_values($categories)) {
        $failures[] = 'exact category';
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

    if ($data['seo_title'] !== get_post_meta($post_id, 'rank_math_title', true) || $data['seo_description'] !== get_post_meta($post_id, 'rank_math_description', true) || $data['focus_keyword'] !== get_post_meta($post_id, 'rank_math_focus_keyword', true) || home_url($data['canonical_path']) !== get_post_meta($post_id, 'rank_math_canonical_url', true)) {
        $failures[] = 'Rank Math metadata';
    }
    if ((array) get_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_MISSING_IMAGES_OPTION, array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_MISSING_SLOTS_OPTION, array())) {
        $failures[] = 'missing slots';
    }

    update_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_VALIDATION_OPTION, array_values(array_unique($failures)), false);
    return empty($failures);
}

function custom_box_advent_calendar_packaging_guide_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success' : 'notice notice-error';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
    delete_option(CUSTOM_BOX_ADVENT_CALENDAR_GUIDE_NOTICE_OPTION);
}
