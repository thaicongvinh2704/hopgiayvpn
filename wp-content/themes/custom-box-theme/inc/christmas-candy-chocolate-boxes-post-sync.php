<?php
/**
 * Imports the Christmas chocolate and candy boxes guide and its five-image set.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_SYNC_VERSION = '2026-09-26-christmas-candy-chocolate-boxes-v1';
const CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_VERSION_OPTION = 'custom_box_christmas_candy_chocolate_boxes_sync_version';
const CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_NOTICE_OPTION = 'custom_box_christmas_candy_chocolate_boxes_sync_notice';
const CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_MISSING_IMAGES_OPTION = 'custom_box_christmas_candy_chocolate_boxes_missing_images';
const CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_MISSING_SLOTS_OPTION = 'custom_box_christmas_candy_chocolate_boxes_missing_slots';
const CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_VALIDATION_OPTION = 'custom_box_christmas_candy_chocolate_boxes_validation_failures';

add_action('admin_init', 'custom_box_sync_christmas_candy_chocolate_boxes_post');
add_action('admin_notices', 'custom_box_christmas_candy_chocolate_boxes_admin_notice');

function custom_box_christmas_candy_chocolate_boxes_post_data(): array
{
    return array(
        'title' => 'Christmas Chocolate Boxes and Candy Packaging: A Sourcing Guide',
        'slug' => 'christmas-candy-chocolate-boxes',
        'excerpt' => 'A practical guide to Christmas chocolate and candy boxes for brands, covering structures, inserts, food-contact layers, samples and quote-ready specifications.',
        'categories' => array(
            'Packaging Guides' => 'packaging-guides',
        ),
        'tags' => array(
            'Christmas Packaging' => 'christmas-packaging',
            'Chocolate Packaging' => 'chocolate-packaging',
            'Candy Packaging' => 'candy-packaging',
            'Gift Boxes' => 'gift-boxes',
            'Food Packaging' => 'food-packaging',
            'Packaging Inserts' => 'packaging-inserts',
        ),
        'seo_title' => 'Christmas Chocolate Boxes: Structures, Inserts & Sourcing',
        'seo_description' => 'Choose Christmas chocolate boxes by product fit, insert, food-contact layer and shipping route. Use this checklist to prepare a custom packaging quote.',
        'focus_keyword' => 'christmas chocolate boxes',
    );
}
function custom_box_christmas_candy_chocolate_boxes_images(): array
{
    return array(
        'featured' => array(
            'base' => 'christmas-chocolate-candy-boxes-packaging-guide-for-brands',
            'alt' => 'Christmas chocolate and candy box packaging formats with fitted inserts',
            'title' => 'Christmas Chocolate and Candy Packaging Guide',
            'caption' => 'Different confectionery programs call for different box and insert systems.',
        ),
        'slot_1' => array(
            'base' => 'christmas-candy-chocolate-box-format-comparison',
            'alt' => 'Christmas candy and chocolate box formats including cartons, sleeves and fitted gift boxes',
            'title' => 'Christmas Chocolate Box Format Comparison',
            'caption' => 'The confection and sales channel help determine which packaging format to evaluate.',
        ),
        'slot_2' => array(
            'base' => 'chocolate-box-insert-cavity-fit-layout',
            'alt' => 'Chocolate box insert showing cavity spacing, product height clearance and removal access',
            'title' => 'Chocolate Box Insert Fit and Layout',
            'caption' => 'Plan the insert around product geometry, headspace, removal and packing sequence.',
        ),
        'slot_3' => array(
            'base' => 'chocolate-packaging-food-contact-layer-system',
            'alt' => 'Chocolate packaging layers showing a food-contact cup or tray inside a decorative outer box',
            'title' => 'Food-Contact Layers in Chocolate Packaging',
            'caption' => 'Identify which packaging component actually touches the confection before specifying the outer box.',
        ),
        'slot_4' => array(
            'base' => 'filled-chocolate-box-sample-fit-qc-check',
            'alt' => 'Filled chocolate box sample being checked for insert fit, product clearance and closure',
            'title' => 'Filled Chocolate Box Sample Approval',
            'caption' => 'Review the packaging system with the product loaded, not only as an empty printed box.',
        ),
    );
}
function custom_box_christmas_candy_chocolate_boxes_content(): string
{
    $path = __DIR__ . '/post-content/christmas-candy-chocolate-boxes.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    if (!is_string($content) || '' === trim($content)) {
        return '';
    }

    return str_replace('https://hopgiayvpn.com/', trailingslashit(home_url('/')), $content);
}

function custom_box_find_christmas_candy_chocolate_boxes_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_christmas_candy_chocolate_boxes_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_christmas_candy_chocolate_boxes_post_data();
    $post = custom_box_find_christmas_candy_chocolate_boxes_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_SYNC_VERSION === get_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_VERSION_OPTION)
        && $post
        && custom_box_christmas_candy_chocolate_boxes_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_christmas_candy_chocolate_boxes_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_VERSION_OPTION);
        update_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_christmas_candy_chocolate_boxes_is_complete((int) $post_id)) {
        $post = get_post((int) $post_id);
        $content = $post ? (string) $post->post_content : '';
        $categories = wp_get_post_terms((int) $post_id, 'category', array('fields' => 'slugs'));
        $tags = wp_get_post_terms((int) $post_id, 'post_tag', array('fields' => 'slugs'));

        update_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_VERSION_OPTION, CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Christmas chocolate and candy boxes guide imported as %s: post ID %d, featured image %d, 4 inline figures, categories %s, %d exact tags, and Rank Math fields verified.',
                $post ? (string) $post->post_status : 'unknown',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id),
                !is_wp_error($categories) && $categories ? implode(', ', $categories) : 'missing',
                !is_wp_error($tags) ? count($tags) : 0
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_VERSION_OPTION);
    update_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Christmas chocolate and candy boxes guide sync is incomplete and will retry. Missing images: '
            . implode(', ', (array) get_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_MISSING_IMAGES_OPTION, array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_MISSING_SLOTS_OPTION, array()),
                (array) get_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_VALIDATION_OPTION, array())
            )),
    ), false);
}

function custom_box_upsert_christmas_candy_chocolate_boxes_post()
{
    $data = custom_box_christmas_candy_chocolate_boxes_post_data();
    $post = custom_box_find_christmas_candy_chocolate_boxes_post($data['slug'], $data['title']);
    $content = custom_box_christmas_candy_chocolate_boxes_content();

    if ('' === trim($content)) {
        return new WP_Error('christmas_candy_chocolate_boxes_content_missing', 'The canonical Christmas chocolate and candy boxes guide content bundle is missing.');
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
        if (
            !in_array($post->post_status, array('publish', 'private'), true)
            || '' === trim($existing)
            || false !== strpos($existing, 'IMAGE_SLOT_')
        ) {
            $payload['post_content'] = $content;
        }
        $result = wp_update_post($payload, true);
    } else {
        $payload['post_status'] = 'draft';
        $payload['post_content'] = $content;
        $result = wp_insert_post($payload, true);
    }

    if (is_wp_error($result)) {
        return $result;
    }

    $post_id = (int) $result;
    custom_box_sync_christmas_candy_chocolate_boxes_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_christmas_candy_chocolate_boxes_images($post_id);
    update_post_meta($post_id, '_custom_box_christmas_candy_chocolate_boxes_synced', current_time('mysql'));

    return $post_id;
}

function custom_box_sync_christmas_candy_chocolate_boxes_terms(int $post_id, array $data): void
{
    $category_ids = array();
    foreach ($data['categories'] as $name => $slug) {
        $category = get_term_by('slug', $slug, 'category');
        if (!$category || is_wp_error($category)) {
            $created = wp_insert_term($name, 'category', array('slug' => $slug));
            if (is_wp_error($created)) {
                continue;
            }
            $category = get_term((int) $created['term_id'], 'category');
        }
        if ($category && !is_wp_error($category)) {
            $category_ids[] = (int) $category->term_id;
        }
    }
    wp_set_post_categories($post_id, array_values(array_unique($category_ids)), false);

    $tag_ids = array();
    foreach ($data['tags'] as $name => $slug) {
        $tag = get_term_by('slug', $slug, 'post_tag');
        if (!$tag || is_wp_error($tag)) {
            $created = wp_insert_term($name, 'post_tag', array('slug' => $slug));
            if (is_wp_error($created)) {
                continue;
            }
            $tag = get_term((int) $created['term_id'], 'post_tag');
        }
        if ($tag && !is_wp_error($tag)) {
            $tag_ids[] = (int) $tag->term_id;
        }
    }
    wp_set_post_terms($post_id, array_values(array_unique($tag_ids)), 'post_tag', false);
}

function custom_box_christmas_candy_chocolate_boxes_bundle_path(string $base): string
{
    return get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/09/' . $base . '.webp';
}

function custom_box_christmas_candy_chocolate_boxes_upload_path(string $base): array
{
    $uploads = wp_get_upload_dir();
    if (empty($uploads['basedir']) || empty($uploads['baseurl'])) {
        return array();
    }

    $relative = '2026/09/' . $base . '.webp';
    $upload_path = trailingslashit($uploads['basedir']) . $relative;
    $bundle_path = custom_box_christmas_candy_chocolate_boxes_bundle_path($base);

    if (!file_exists($upload_path) && file_exists($bundle_path)) {
        if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
            return array();
        }
    }

    if (!file_exists($upload_path)) {
        return array();
    }

    return array(
        'absolute' => $upload_path,
        'relative' => $relative,
        'url' => trailingslashit($uploads['baseurl']) . $relative,
    );
}

function custom_box_find_christmas_candy_chocolate_boxes_attachment(string $base): int
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

function custom_box_christmas_candy_chocolate_boxes_ensure_attachment_file(int $attachment_id, string $base): bool
{
    $relative = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
    $uploads = wp_get_upload_dir();
    $current_path = $relative && !empty($uploads['basedir'])
        ? trailingslashit($uploads['basedir']) . $relative
        : '';

    if ($current_path && file_exists($current_path)) {
        return true;
    }

    $file = custom_box_christmas_candy_chocolate_boxes_upload_path($base);
    if (empty($file['absolute'])) {
        return false;
    }

    update_post_meta($attachment_id, '_wp_attached_file', $file['relative']);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata($attachment_id, $file['absolute']);
    if (is_array($metadata)) {
        wp_update_attachment_metadata($attachment_id, $metadata);
    }

    return true;
}

function custom_box_christmas_candy_chocolate_boxes_create_attachment(string $base, int $post_id, array $image): int
{
    $file = custom_box_christmas_candy_chocolate_boxes_upload_path($base);
    if (empty($file['absolute'])) {
        return 0;
    }

    $type = wp_check_filetype(wp_basename($file['absolute']), null);
    $attachment_id = wp_insert_attachment(array(
        'guid' => $file['url'],
        'post_mime_type' => !empty($type['type']) ? $type['type'] : 'image/webp',
        'post_title' => $image['title'],
        'post_excerpt' => $image['caption'],
        'post_status' => 'inherit',
        'post_parent' => $post_id,
    ), $file['absolute'], $post_id, true);

    if (is_wp_error($attachment_id)) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_wp_attached_file', $file['relative']);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $file['absolute']);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);

    return (int) $attachment_id;
}

function custom_box_sync_christmas_candy_chocolate_boxes_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $images = custom_box_christmas_candy_chocolate_boxes_images();
    $missing_images = array();
    $missing_slots = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_christmas_candy_chocolate_boxes_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_christmas_candy_chocolate_boxes_create_attachment($image['base'], $post_id, $image);
        } elseif (!custom_box_christmas_candy_chocolate_boxes_ensure_attachment_file($attachment_id, $image['base'])) {
            $missing_images[] = $image['base'];
            continue;
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

        $marker = '<!-- christmas-candy-chocolate-boxes-image:' . $key . ' -->';
        $img_html = wp_get_attachment_image($attachment_id, 'full', false, array(
            'alt' => $image['alt'],
            'loading' => 'lazy',
            'decoding' => 'async',
        ));
        if (!$img_html) {
            $missing_images[] = $image['base'];
            continue;
        }
        $figure = $marker . "\n<figure class=\"wp-block-image size-full\">" . $img_html
            . '<figcaption>' . esc_html($image['caption']) . '</figcaption></figure>';
        $slot_number = substr($key, 5);
        $slot = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
        $wrapped_slot_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

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

    update_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_MISSING_IMAGES_OPTION, array_values(array_unique($missing_images)), false);
    update_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_MISSING_SLOTS_OPTION, array_values(array_unique($missing_slots)), false);
}

function custom_box_christmas_candy_chocolate_boxes_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_christmas_candy_chocolate_boxes_post_data();
    $images = custom_box_christmas_candy_chocolate_boxes_images();
    $failures = array();

    if (
        !$post
        || 'post' !== $post->post_type
        || $data['title'] !== $post->post_title
        || $data['slug'] !== $post->post_name
        || $data['excerpt'] !== $post->post_excerpt
    ) {
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
        $attachment_id = custom_box_find_christmas_candy_chocolate_boxes_attachment($image['base']);
        $attachment = $attachment_id ? get_post($attachment_id) : null;
        $attached_file = $attachment_id ? get_attached_file($attachment_id) : '';
        if (
            !$attachment
            || 'attachment' !== $attachment->post_type
            || $post_id !== (int) $attachment->post_parent
            || $image['title'] !== $attachment->post_title
            || $image['caption'] !== $attachment->post_excerpt
            || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
            || !wp_get_attachment_url($attachment_id)
            || !$attached_file
            || !file_exists($attached_file)
        ) {
            $failures[] = $key . ' attachment metadata';
        }
    }

    $content = $post ? (string) $post->post_content : '';
    $inline_count = count($images) - 1;
    if (
        $inline_count !== substr_count($content, '<!-- christmas-candy-chocolate-boxes-image:slot_')
        || $inline_count !== preg_match_all('/<figure\b/i', $content, $unused)
        || $inline_count !== preg_match_all('/<img\b/i', $content, $unused)
    ) {
        $failures[] = 'inline image counts';
    }
    foreach ($images as $key => $image) {
        if ('featured' !== $key && false === strpos($content, $image['base'])) {
            $failures[] = $key . ' filename';
        }
    }
    if (preg_match('/IMAGE_SLOT_[0-9]+/', $content)) {
        $failures[] = 'image placeholders';
    }

    $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    $expected_categories = array_values($data['categories']);
    if (is_wp_error($categories)) {
        $failures[] = 'categories';
    } else {
        sort($categories);
        sort($expected_categories);
        if ($categories !== $expected_categories) {
            $failures[] = 'exact categories';
        }
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
        $data['seo_title'] !== (string) get_post_meta($post_id, 'rank_math_title', true)
        || $data['seo_description'] !== (string) get_post_meta($post_id, 'rank_math_description', true)
        || $data['focus_keyword'] !== (string) get_post_meta($post_id, 'rank_math_focus_keyword', true)
    ) {
        $failures[] = 'Rank Math metadata';
    }
    if ((array) get_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_MISSING_IMAGES_OPTION, array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_MISSING_SLOTS_OPTION, array())) {
        $failures[] = 'missing slots';
    }

    update_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_VALIDATION_OPTION, array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_christmas_candy_chocolate_boxes_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_CHRISTMAS_CANDY_CHOCOLATE_BOXES_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}
