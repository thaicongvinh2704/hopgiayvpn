<?php
/**
 * Imports the food packaging and design article and its five WebP images.
 */

defined('ABSPATH') || exit;

const HGVN_FOOD_PACKAGING_DESIGN_SYNC_VERSION = '2026-10-03-v1';
const HGVN_FOOD_PACKAGING_DESIGN_SYNC_OPTION = 'hgvn_food_packaging_design_sync_version';
const HGVN_FOOD_PACKAGING_DESIGN_NOTICE_OPTION = 'hgvn_food_packaging_design_sync_notice';

add_action('admin_init', 'hgvn_food_packaging_design_maybe_sync', 20);
add_action('admin_notices', 'hgvn_food_packaging_design_admin_notice');

function hgvn_food_packaging_design_data(): array
{
    return array(
        'title' => 'Food Packaging and Design: From Brief to Production',
        'slug' => 'food-packaging-and-design-from-brief-to-production',
        'excerpt' => 'Plan food packaging as one connected system—from the product and box structure to materials, artwork, prototypes and production approval.',
        'canonical_path' => '/food-packaging-and-design-from-brief-to-production/',
        'categories' => array(
            'food-packaging' => 'Food Packaging',
            'packaging-design' => 'Packaging Design',
        ),
    );
}

function hgvn_food_packaging_design_images(): array
{
    return array(
        'featured' => array(
            'filename' => 'food-packaging-and-design-structure-branding-safety-guide.webp',
            'title' => 'Food Packaging and Design: Structure, Branding and Safety',
            'alt' => 'Food packaging design concept showing a paper box, food product, brand artwork and safety information',
            'caption' => 'Food packaging design connects product protection, structure, brand communication and production requirements.',
        ),
        'slot_1' => array(
            'filename' => 'food-packaging-structure-decision-tree.webp',
            'title' => 'Food Packaging Structure Decision Tree',
            'alt' => 'Food paper box structures selected to match the product and its journey',
            'caption' => 'Choose the package structure around the product, filling method, storage and distribution journey.',
        ),
        'slot_2' => array(
            'filename' => 'food-packaging-material-system-board-coating-ink.webp',
            'title' => 'Food Packaging Material System',
            'alt' => 'Paperboard, coating and ink working together as one food packaging material system',
            'caption' => 'Paperboard, coating and ink should be specified together as one material system.',
        ),
        'slot_3' => array(
            'filename' => 'food-packaging-artwork-dieline-design-zones.webp',
            'title' => 'Food Packaging Artwork and Dieline Design Zones',
            'alt' => 'Food packaging artwork zones mapped to folds, glue areas and a production dieline',
            'caption' => 'Build artwork on the approved production dieline and keep critical information clear of folds and glue areas.',
        ),
        'slot_4' => array(
            'filename' => 'food-packaging-prototype-testing-approval-flow.webp',
            'title' => 'Food Packaging Prototype Testing and Approval Flow',
            'alt' => 'Prototype testing and approval steps for production-ready food packaging',
            'caption' => 'Use prototypes and a documented approval flow to resolve design and production risks before release.',
        ),
    );
}

function hgvn_food_packaging_design_upload_target(string $filename): array
{
    $uploads = wp_get_upload_dir();
    $relative = '2026/10/food-packaging-and-design/' . $filename;

    return array(
        'relative' => $relative,
        'path' => trailingslashit($uploads['basedir']) . $relative,
        'url' => trailingslashit($uploads['baseurl']) . $relative,
    );
}

function hgvn_food_packaging_design_ensure_file(string $filename)
{
    $target = hgvn_food_packaging_design_upload_target($filename);
    $bundle_path = __DIR__ . '/product-sample-deploy-assets/uploads/' . $target['relative'];

    if (!is_readable($bundle_path)) {
        return new WP_Error('food_packaging_design_image_missing', 'Missing bundled image: ' . $filename);
    }

    $bundle_size = filesize($bundle_path);
    if (false === $bundle_size || $bundle_size <= 0 || $bundle_size >= 102400) {
        return new WP_Error('food_packaging_design_image_size', 'Bundled image is empty or exceeds 100 KB: ' . $filename);
    }

    $target_exists = is_readable($target['path']);
    $same_file = $target_exists
        && filesize($target['path']) === $bundle_size
        && hash_file('sha256', $target['path']) === hash_file('sha256', $bundle_path);

    if (!$same_file) {
        if (!wp_mkdir_p(dirname($target['path'])) || !copy($bundle_path, $target['path'])) {
            return new WP_Error('food_packaging_design_image_copy', 'Could not install image: ' . $filename);
        }
    }

    return $target;
}

function hgvn_food_packaging_design_find_attachment(string $filename): int
{
    global $wpdb;

    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s
         ORDER BY post_id DESC",
        '%' . $wpdb->esc_like($filename) . '%'
    ));

    foreach ((array) $ids as $id) {
        $attached_file = (string) get_post_meta((int) $id, '_wp_attached_file', true);
        if ($filename === wp_basename($attached_file) && 'attachment' === get_post_type((int) $id)) {
            return (int) $id;
        }
    }

    return 0;
}

function hgvn_food_packaging_design_sync_images(int $post_id)
{
    $attachments = array();
    $images = hgvn_food_packaging_design_images();
    require_once ABSPATH . 'wp-admin/includes/image.php';

    foreach ($images as $key => $image) {
        $target = hgvn_food_packaging_design_ensure_file($image['filename']);
        if (is_wp_error($target)) {
            return $target;
        }

        $attachment_id = hgvn_food_packaging_design_find_attachment($image['filename']);
        if (!$attachment_id) {
            $type = wp_check_filetype($image['filename']);
            $attachment_id = wp_insert_attachment(array(
                'guid' => $target['url'],
                'post_mime_type' => !empty($type['type']) ? $type['type'] : 'image/webp',
                'post_title' => $image['title'],
                'post_excerpt' => $image['caption'],
                'post_status' => 'inherit',
                'post_parent' => $post_id,
            ), $target['path'], $post_id, true);

            if (is_wp_error($attachment_id)) {
                return $attachment_id;
            }
        }

        $attachment_id = (int) $attachment_id;
        update_post_meta($attachment_id, '_wp_attached_file', $target['relative']);
        $metadata = wp_generate_attachment_metadata($attachment_id, $target['path']);
        if (is_array($metadata)) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        }

        $updated = wp_update_post(array(
            'ID' => $attachment_id,
            'post_title' => $image['title'],
            'post_excerpt' => $image['caption'],
            'post_parent' => $post_id,
        ), true);
        if (is_wp_error($updated)) {
            return $updated;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
        $url = wp_get_attachment_url($attachment_id);
        if (!$url || !is_readable($target['path'])) {
            return new WP_Error('food_packaging_design_attachment_url', 'Could not verify media URL for ' . $image['filename']);
        }

        $attachments[$key] = array(
            'id' => $attachment_id,
            'url' => $url,
        );
    }

    if (!isset($attachments['featured'])) {
        return new WP_Error('food_packaging_design_featured_image', 'Could not set the article featured image.');
    }
    set_post_thumbnail($post_id, $attachments['featured']['id']);
    if ((int) get_post_thumbnail_id($post_id) !== (int) $attachments['featured']['id']) {
        return new WP_Error('food_packaging_design_featured_image', 'Could not set the article featured image.');
    }

    return $attachments;
}

function hgvn_food_packaging_design_content(array $attachments): string
{
    $path = __DIR__ . '/post-content/food-packaging-and-design-from-brief-to-production.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    if (!is_string($content) || '' === trim($content)) {
        return '';
    }

    $site_root = trailingslashit(home_url('/'));
    $content = preg_replace_callback(
        '~\b(href|src)=(["\'])/([^"\']*)\2~i',
        static function ($match) use ($site_root) {
            return $match[1] . '=' . $match[2] . esc_url($site_root . $match[3]) . $match[2];
        },
        $content
    );

    foreach (hgvn_food_packaging_design_images() as $key => $image) {
        if (empty($attachments[$key]['url'])) {
            return '';
        }

        $pattern = '~\bsrc=(["\'])[^"\']*' . preg_quote($image['filename'], '~') . '\1~i';
        $content = preg_replace_callback(
            $pattern,
            static function ($match) use ($attachments, $key) {
                return 'src=' . $match[1] . esc_url($attachments[$key]['url']) . $match[1];
            },
            $content,
            1
        );
    }

    return is_string($content) ? $content : '';
}

function hgvn_food_packaging_design_find_post(): ?WP_Post
{
    $post = get_page_by_path('food-packaging-and-design-from-brief-to-production', OBJECT, 'post');
    return ($post && 'trash' !== $post->post_status) ? $post : null;
}

function hgvn_food_packaging_design_sync_categories(int $post_id): bool
{
    $category_ids = array();
    foreach (hgvn_food_packaging_design_data()['categories'] as $slug => $name) {
        $term = get_term_by('slug', $slug, 'category');
        if (!$term || is_wp_error($term)) {
            $created = wp_insert_term($name, 'category', array('slug' => $slug));
            if (is_wp_error($created)) {
                return false;
            }
            $term = get_term((int) $created['term_id'], 'category');
        }

        if (!$term || is_wp_error($term)) {
            return false;
        }
        $category_ids[] = (int) $term->term_id;
    }

    $set_categories = wp_set_post_terms($post_id, $category_ids, 'category', false);
    $set_tags = wp_set_post_terms($post_id, array(), 'post_tag', false);
    return !is_wp_error($set_categories) && !is_wp_error($set_tags);
}

function hgvn_food_packaging_design_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = hgvn_food_packaging_design_data();
    $images = hgvn_food_packaging_design_images();

    if (
        !$post
        || 'post' !== $post->post_type
        || 'publish' !== $post->post_status
        || $data['title'] !== $post->post_title
        || $data['slug'] !== $post->post_name
        || $data['excerpt'] !== $post->post_excerpt
    ) {
        return false;
    }

    $content = (string) $post->post_content;
    if (4 !== preg_match_all('/<figure\b/i', $content) || 4 !== preg_match_all('/<img\b/i', $content)) {
        return false;
    }
    if (preg_match('/IMAGE_SLOT_[0-9]+/i', $content)) {
        return false;
    }

    $thumbnail_id = get_post_thumbnail_id($post_id);
    $thumbnail_file = $thumbnail_id ? (string) get_post_meta($thumbnail_id, '_wp_attached_file', true) : '';
    if ($images['featured']['filename'] !== wp_basename($thumbnail_file)) {
        return false;
    }

    foreach ($images as $image) {
        $attachment_id = hgvn_food_packaging_design_find_attachment($image['filename']);
        if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
            return false;
        }
    }

    $actual_categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    $expected_categories = array_keys($data['categories']);
    if (is_wp_error($actual_categories)) {
        return false;
    }
    sort($actual_categories);
    sort($expected_categories);
    return $actual_categories === $expected_categories;
}

function hgvn_food_packaging_design_run_sync(): array
{
    $data = hgvn_food_packaging_design_data();
    $post = hgvn_food_packaging_design_find_post();
    $is_new = !$post;

    if ($is_new) {
        $post_id = wp_insert_post(array(
            'post_title' => $data['title'],
            'post_name' => $data['slug'],
            'post_type' => 'post',
            'post_status' => 'draft',
            'post_excerpt' => $data['excerpt'],
            'post_author' => get_current_user_id(),
        ), true);
        if (is_wp_error($post_id)) {
            return array('success' => false, 'message' => $post_id->get_error_message());
        }
        $post_id = (int) $post_id;
    } else {
        $post_id = (int) $post->ID;
    }

    $attachments = hgvn_food_packaging_design_sync_images($post_id);
    if (is_wp_error($attachments)) {
        return array('success' => false, 'message' => $attachments->get_error_message());
    }

    $content = hgvn_food_packaging_design_content($attachments);
    if ('' === trim($content)) {
        return array('success' => false, 'message' => 'The article HTML bundle is missing or could not be prepared.');
    }

    $updated = wp_update_post(array(
        'ID' => $post_id,
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_excerpt' => $data['excerpt'],
        'post_content' => wp_slash($content),
    ), true);
    if (is_wp_error($updated)) {
        return array('success' => false, 'message' => $updated->get_error_message());
    }

    if (!hgvn_food_packaging_design_sync_categories($post_id)) {
        return array('success' => false, 'message' => 'Could not assign the Food Packaging and Packaging Design categories.');
    }
    update_post_meta($post_id, 'rank_math_canonical_url', home_url($data['canonical_path']));

    if (!hgvn_food_packaging_design_is_complete($post_id)) {
        return array('success' => false, 'message' => 'The post or its media did not pass the completion check.');
    }

    return array(
        'success' => true,
        'message' => sprintf(
            'Food Packaging and Design is published (post ID %d); the featured image and four inline images were verified.',
            $post_id
        ),
    );
}

function hgvn_food_packaging_design_maybe_sync(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $force_sync = isset($_GET['custom_box_run_post_syncs'])
        && '1' === sanitize_text_field(wp_unslash($_GET['custom_box_run_post_syncs']));
    if (!$force_sync && HGVN_FOOD_PACKAGING_DESIGN_SYNC_VERSION === get_option(HGVN_FOOD_PACKAGING_DESIGN_SYNC_OPTION)) {
        return;
    }

    $result = hgvn_food_packaging_design_run_sync();
    if (!empty($result['success'])) {
        update_option(HGVN_FOOD_PACKAGING_DESIGN_SYNC_OPTION, HGVN_FOOD_PACKAGING_DESIGN_SYNC_VERSION, false);
    } else {
        delete_option(HGVN_FOOD_PACKAGING_DESIGN_SYNC_OPTION);
    }
    update_option(HGVN_FOOD_PACKAGING_DESIGN_NOTICE_OPTION, $result, false);
}

function hgvn_food_packaging_design_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(HGVN_FOOD_PACKAGING_DESIGN_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-error';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
    delete_option(HGVN_FOOD_PACKAGING_DESIGN_NOTICE_OPTION);
}
