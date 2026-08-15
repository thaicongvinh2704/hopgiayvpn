<?php
/**
 * Deploys the VPN paper box manufacturer supplier-evaluation draft and images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_SYNC_VERSION = '2026-08-15-light-brown-recycled-kraft-paper-roll-v2';
const CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_VERSION_OPTION = 'custom_box_light_brown_recycled_kraft_paper_roll_sync_version';
const CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_NOTICE_OPTION = 'custom_box_light_brown_recycled_kraft_paper_roll_sync_notice';

add_action('admin_init', 'custom_box_sync_light_brown_recycled_kraft_paper_roll_post');
add_action('admin_notices', 'custom_box_light_brown_recycled_kraft_paper_roll_admin_notice');

function custom_box_light_brown_recycled_kraft_paper_roll_post_data(): array
{
    return array(
        'title' => 'Light Brown Recycled Kraft Paper Rolls: Uses, Grades and Buying Guide',
        'slug' => 'light-brown-recycled-kraft-paper-roll',
        'excerpt' => 'Learn how to specify light brown recycled kraft paper rolls by recycled content, GSM, strength, roll quality, recyclability and finished-box performance before buying.',
        'category' => array(
            'name' => 'Packaging Guides',
            'slug' => 'packaging-guides',
        ),
        'categories' => array(
            array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
        ),
        'tags' => array(
            'Recycled Kraft Paper' => 'recycled-kraft-paper',
            'Kraft Paper' => 'kraft-paper',
            'Paper Materials' => 'paper-materials',
            'Sustainable Packaging' => 'sustainable-packaging',
            'Paper Box Materials' => 'paper-box-materials',
        ),
        'seo_title' => 'Light Brown Recycled Kraft Paper Roll Buying Guide',
        'seo_description' => 'Compare light brown recycled kraft paper rolls by recycled content, GSM, strength, recyclability, roll quality and paper-box performance.',
        'focus_keyword' => 'light brown kraft recycled paper roll',
    );
}
function custom_box_light_brown_recycled_kraft_paper_roll_images(): array
{
    return array(
        'featured' => array(
            'base' => 'light-brown-recycled-kraft-paper-roll-buying-guide',
            'alt' => 'Light brown recycled kraft paper rolls for packaging material sourcing',
            'title' => 'Light Brown Recycled Kraft Paper Rolls',
            'caption' => 'Light brown recycled kraft paper should be specified by measurable properties, not appearance alone.',
        ),
        'slot_1' => array(
            'base' => 'recycled-kraft-paper-specification-check',
            'alt' => 'Recycled kraft paper roll specification check for B2B buyers',
            'title' => 'Recycled Kraft Paper Specification',
            'caption' => 'GSM, caliper, fibre content and roll construction should be verified before bulk approval.',
        ),
        'slot_2' => array(
            'base' => 'kraft-paper-roll-end-use-comparison',
            'alt' => 'Kraft paper roll applications for wrapping bags and paper packaging',
            'title' => 'Kraft Paper End-Use Comparison',
            'caption' => 'Paper grade should be selected from the finished application rather than GSM alone.',
        ),
        'slot_3' => array(
            'base' => 'recycled-content-vs-recyclability-paper-packaging',
            'alt' => 'Recycled content and recyclability comparison for paper packaging',
            'title' => 'Recycled Content vs Recyclability',
            'caption' => 'Recycled fibre content and finished-package recyclability are separate questions.',
        ),
        'slot_4' => array(
            'base' => 'recycled-kraft-paper-supplier-scorecard',
            'alt' => 'Recycled kraft paper supplier qualification scorecard',
            'title' => 'Kraft Paper Supplier Scorecard',
            'caption' => 'Compare suppliers using documented material, converting and recyclability evidence.',
        ),
    );
}
function custom_box_light_brown_recycled_kraft_paper_roll_content(): string
{
    $path = __DIR__ . '/post-content/light-brown-recycled-kraft-paper-roll.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    if (!is_string($content)) {
        return '';
    }

    // Protect the import from literal list artifacts in pasted HTML.
    return str_replace('`t<li>', '<li>', $content);
}

function custom_box_find_light_brown_recycled_kraft_paper_roll_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_light_brown_recycled_kraft_paper_roll_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_light_brown_recycled_kraft_paper_roll_post_data();
    $post = custom_box_find_light_brown_recycled_kraft_paper_roll_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_SYNC_VERSION === get_option(CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_VERSION_OPTION)
        && $post
        && custom_box_light_brown_recycled_kraft_paper_roll_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_light_brown_recycled_kraft_paper_roll_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_VERSION_OPTION);
        update_option(CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_light_brown_recycled_kraft_paper_roll_is_complete((int) $post_id)) {
        $post = get_post((int) $post_id);
        $content = $post ? (string) $post->post_content : '';
        $categories = wp_get_post_terms((int) $post_id, 'category', array('fields' => 'slugs'));
        $tags = wp_get_post_terms((int) $post_id, 'post_tag', array('fields' => 'slugs'));

        update_option(CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_VERSION_OPTION, CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Light brown recycled kraft paper roll guide draft synced: post ID %d, status %s, featured image %d, %d inline figures, categories %s, %d exact tags, and Rank Math fields verified.',
                (int) $post_id,
                $post ? (string) $post->post_status : 'unknown',
                (int) get_post_thumbnail_id((int) $post_id),
                substr_count($content, '<!-- light-brown-recycled-kraft-paper-roll-image:slot_'),
                !is_wp_error($categories) && $categories ? implode(', ', $categories) : 'missing',
                !is_wp_error($tags) ? count($tags) : 0
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_VERSION_OPTION);
    update_option(CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Light brown recycled kraft paper roll guide sync is incomplete and will retry. Missing images: '
            . implode(', ', (array) get_option('custom_box_light_brown_recycled_kraft_paper_roll_missing_images', array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option('custom_box_light_brown_recycled_kraft_paper_roll_missing_slots', array()),
                (array) get_option('custom_box_light_brown_recycled_kraft_paper_roll_validation_failures', array())
            )),
    ), false);
}

function custom_box_upsert_light_brown_recycled_kraft_paper_roll_post()
{
    $data = custom_box_light_brown_recycled_kraft_paper_roll_post_data();
    $post = custom_box_find_light_brown_recycled_kraft_paper_roll_post($data['slug'], $data['title']);
    $content = custom_box_light_brown_recycled_kraft_paper_roll_content();

    if ('' === trim($content)) {
        return new WP_Error('light_brown_recycled_kraft_paper_roll_content_missing', 'The canonical light brown recycled kraft paper roll content bundle is missing.');
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
    custom_box_sync_light_brown_recycled_kraft_paper_roll_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_light_brown_recycled_kraft_paper_roll_images($post_id);

    return $post_id;
}

function custom_box_sync_light_brown_recycled_kraft_paper_roll_terms(int $post_id, array $data): void
{
    $category_ids = array();
    foreach ((array) ($data['categories'] ?? array($data['category'])) as $category_data) {
        $category = get_term_by('slug', $category_data['slug'], 'category');
        if (!$category || is_wp_error($category)) {
            $created = wp_insert_term($category_data['name'], 'category', array('slug' => $category_data['slug']));
            if (!is_wp_error($created)) {
                $category = get_term((int) $created['term_id'], 'category');
            }
        }
        if ($category && !is_wp_error($category)) {
            $category_ids[] = (int) $category->term_id;
        }
    }
    if ($category_ids) {
        wp_set_post_categories($post_id, array_values(array_unique($category_ids)), false);
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

function custom_box_sync_light_brown_recycled_kraft_paper_roll_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_light_brown_recycled_kraft_paper_roll_images() as $key => $image) {
        $attachment_id = custom_box_find_light_brown_recycled_kraft_paper_roll_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_light_brown_recycled_kraft_paper_roll_attachment($image['base'], $post_id, $image);
        } elseif (!custom_box_ensure_light_brown_recycled_kraft_paper_roll_attachment_file($attachment_id, $image['base'])) {
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

        $marker = '<!-- light-brown-recycled-kraft-paper-roll-image:' . $key . ' -->';
        $figure = $marker . "\n<figure style=\"margin:1.5rem 0;\"><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"display:block; width:auto; max-width:100%; height:auto; margin:0 auto; border-radius:12px;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
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

    update_option('custom_box_light_brown_recycled_kraft_paper_roll_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_light_brown_recycled_kraft_paper_roll_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_find_light_brown_recycled_kraft_paper_roll_attachment(string $base): int
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

function custom_box_light_brown_recycled_kraft_paper_roll_bundle_path(string $base, string $extension): string
{
    return get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/08/' . $base . '.' . $extension;
}

function custom_box_ensure_light_brown_recycled_kraft_paper_roll_attachment_file(int $attachment_id, string $base): bool
{
    $relative_file = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
    $uploads = wp_get_upload_dir();
    if (empty($uploads['basedir'])) {
        return false;
    }

    $upload_path = $relative_file ? trailingslashit($uploads['basedir']) . $relative_file : '';
    if ($upload_path && file_exists($upload_path)) {
        return true;
    }

    foreach (array('webp', 'png', 'jpg', 'jpeg') as $extension) {
        $candidate_relative = '2026/08/' . $base . '.' . $extension;
        $candidate_path = trailingslashit($uploads['basedir']) . $candidate_relative;
        $bundle_path = custom_box_light_brown_recycled_kraft_paper_roll_bundle_path($base, $extension);

        if (!file_exists($candidate_path) && file_exists($bundle_path)) {
            if (!wp_mkdir_p(dirname($candidate_path)) || !copy($bundle_path, $candidate_path)) {
                continue;
            }
        }
        if (file_exists($candidate_path)) {
            update_post_meta($attachment_id, '_wp_attached_file', $candidate_relative);
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $metadata = wp_generate_attachment_metadata($attachment_id, $candidate_path);
            if (is_array($metadata)) {
                wp_update_attachment_metadata($attachment_id, $metadata);
            }
            return true;
        }
    }

    return false;
}

function custom_box_create_light_brown_recycled_kraft_paper_roll_attachment(string $base, int $post_id, array $image): int
{
    $uploads = wp_get_upload_dir();
    if (empty($uploads['basedir']) || empty($uploads['baseurl'])) {
        return 0;
    }

    $file_path = '';
    $relative_file = '';
    foreach (array('webp', 'png', 'jpg', 'jpeg') as $extension) {
        $candidate_relative = '2026/08/' . $base . '.' . $extension;
        $candidate_path = trailingslashit($uploads['basedir']) . $candidate_relative;
        $bundle_path = custom_box_light_brown_recycled_kraft_paper_roll_bundle_path($base, $extension);

        if (!file_exists($candidate_path) && file_exists($bundle_path)) {
            if (!wp_mkdir_p(dirname($candidate_path)) || !copy($bundle_path, $candidate_path)) {
                continue;
            }
        }
        if (file_exists($candidate_path)) {
            $file_path = $candidate_path;
            $relative_file = $candidate_relative;
            break;
        }
    }

    if (!$file_path || !$relative_file) {
        return 0;
    }

    $type = wp_check_filetype(wp_basename($file_path), null);
    $attachment_id = wp_insert_attachment(array(
        'guid' => trailingslashit($uploads['baseurl']) . $relative_file,
        'post_mime_type' => !empty($type['type']) ? $type['type'] : 'image/webp',
        'post_title' => $image['title'],
        'post_excerpt' => $image['caption'],
        'post_status' => 'inherit',
        'post_parent' => $post_id,
    ), $file_path, $post_id, true);
    if (is_wp_error($attachment_id)) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_wp_attached_file', $relative_file);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $file_path);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);

    return (int) $attachment_id;
}

function custom_box_light_brown_recycled_kraft_paper_roll_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_light_brown_recycled_kraft_paper_roll_post_data();
    $images = custom_box_light_brown_recycled_kraft_paper_roll_images();
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
        $attachment_id = custom_box_find_light_brown_recycled_kraft_paper_roll_attachment($image['base']);
        $attachment = $attachment_id ? get_post($attachment_id) : null;
        if (
            !$attachment
            || 'attachment' !== $attachment->post_type
            || $post_id !== (int) $attachment->post_parent
            || $image['title'] !== $attachment->post_title
            || $image['caption'] !== $attachment->post_excerpt
            || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
            || !wp_get_attachment_url($attachment_id)
        ) {
            $failures[] = $key . ' attachment metadata';
        }
    }

    $content = $post ? (string) $post->post_content : '';
    if (
        4 !== substr_count($content, '<!-- light-brown-recycled-kraft-paper-roll-image:slot_')
        || 4 !== preg_match_all('/<figure\b/i', $content, $figure_matches)
        || 4 !== preg_match_all('/<img\b/i', $content, $image_matches)
    ) {
        $failures[] = 'inline image counts';
    }
    foreach ($images as $key => $image) {
        if ('featured' !== $key && false === strpos($content, '/' . $image['base'] . '.')) {
            $failures[] = $key . ' filename';
        }
    }
    if (preg_match('/IMAGE_SLOT_[0-9]+/', $content)) {
        $failures[] = 'image placeholders';
    }
    if (false !== strpos($content, '`t')) {
        $failures[] = 'literal list formatting artifacts';
    }

    $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    $expected_categories = array_map(static function (array $category_data): string {
        return $category_data['slug'];
    }, (array) ($data['categories'] ?? array($data['category'])));
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
    if ((array) get_option('custom_box_light_brown_recycled_kraft_paper_roll_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_light_brown_recycled_kraft_paper_roll_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_light_brown_recycled_kraft_paper_roll_validation_failures', array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_light_brown_recycled_kraft_paper_roll_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_LIGHT_BROWN_RECYCLED_KRAFT_PAPER_ROLL_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}
