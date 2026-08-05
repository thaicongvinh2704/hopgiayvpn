<?php
/**
 * Deploys the Packaging Material Testing Methods paper checklist and images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_SYNC_VERSION = '2026-08-05-v1';
const CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_VERSION_OPTION = 'custom_box_packaging_materials_testing_sync_version';
const CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_NOTICE_OPTION = 'custom_box_packaging_materials_testing_sync_notice';

add_action('admin_init', 'custom_box_sync_packaging_materials_testing_post');
add_action('admin_notices', 'custom_box_packaging_materials_testing_admin_notice');

function custom_box_packaging_materials_testing_post_data(): array
{
    return array(
        'title' => 'Testing Methods for Packaging Materials: A Paper-Packaging Checklist',
        'slug' => 'testing-methods-packaging-materials',
        'excerpt' => 'A technical paper-packaging checklist connecting each material and box test with its objective, procedure, result interpretation and corrective action.',
        'category' => array(
            'name' => 'Packaging Knowledge',
            'slug' => 'packaging-knowledge',
        ),
        'tags' => array(
            'Packaging Testing' => 'packaging-testing',
            'Paper Packaging' => 'paper-packaging',
            'Corrugated Board' => 'corrugated-board',
            'Quality Control' => 'quality-control',
            'Box Testing' => 'box-testing',
        ),
        'seo_title' => 'Packaging Material Testing Methods: Paper Checklist',
        'seo_description' => 'Learn which paper packaging tests to use, how to read the results and what to change when board, box structure, printing or transit performance fails.',
        'focus_keyword' => 'testing method for packaging material',
    );
}

function custom_box_packaging_materials_testing_images(): array
{
    return array(
        'featured' => array(
            'base' => 'packaging-material-testing-methods-checklist',
            'alt' => 'Packaging material testing methods checklist for paperboard and corrugated boxes',
            'title' => 'Packaging Material Testing Methods Checklist',
            'caption' => 'A paper-packaging test checklist linking failure risks, test controls, results, corrective action, and retesting.',
        ),
        'slot_1' => array(
            'base' => 'paper-packaging-test-risk-map',
            'alt' => 'Paper packaging failure risk map connecting damage types with material, box, and distribution tests',
            'title' => 'Paper Packaging Test Risk Map',
            'caption' => 'Select the test from the failure risk: material control, converted-box quality, or packed-system distribution performance.',
        ),
        'slot_2' => array(
            'base' => 'paperboard-sample-conditioning-testing',
            'alt' => 'Paperboard samples conditioned for comparable packaging material test results',
            'title' => 'Paperboard Sample Conditioning and Testing',
            'caption' => 'Comparable results require controlled sample identity, conditioning, orientation, method, and acceptance limits.',
        ),
        'slot_3' => array(
            'base' => 'corrugated-board-strength-water-tests',
            'alt' => 'Corrugated board samples undergoing edge crush, compression, and water absorptiveness tests',
            'title' => 'Corrugated Board Strength and Water Tests',
            'caption' => 'Board tests measure different properties and should lead to targeted material, structure, or process corrections.',
        ),
        'slot_4' => array(
            'base' => 'filled-mailer-box-transit-testing',
            'alt' => 'Filled corrugated mailer box tested for compression, drop, vibration, and transit damage',
            'title' => 'Filled Mailer Box Transit Testing',
            'caption' => 'The packed mailer must be tested as a system with the intended product, insert, closure, and distribution route.',
        ),
    );
}

function custom_box_packaging_materials_testing_content(): string
{
    $path = __DIR__ . '/post-content/testing-methods-packaging-materials.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    return is_string($content) ? $content : '';
}

function custom_box_find_packaging_materials_testing_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_packaging_materials_testing_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_packaging_materials_testing_post_data();
    $post = custom_box_find_packaging_materials_testing_post($data['slug'], $data['title']);
    if (
        CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_SYNC_VERSION === get_option(CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_VERSION_OPTION)
        && $post
        && custom_box_packaging_materials_testing_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_packaging_materials_testing_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_VERSION_OPTION);
        update_option(CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_packaging_materials_testing_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_VERSION_OPTION, CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Packaging materials testing draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Knowledge, 5 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_VERSION_OPTION);
    update_option(CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Packaging materials testing sync is incomplete. Missing images: '
            . implode(', ', (array) get_option('custom_box_packaging_materials_testing_missing_images', array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option('custom_box_packaging_materials_testing_missing_slots', array()),
                (array) get_option('custom_box_packaging_materials_testing_validation_failures', array())
            )),
    ), false);
}

function custom_box_upsert_packaging_materials_testing_post()
{
    $data = custom_box_packaging_materials_testing_post_data();
    $post = custom_box_find_packaging_materials_testing_post($data['slug'], $data['title']);
    $content = custom_box_packaging_materials_testing_content();
    if ('' === trim($content)) {
        return new WP_Error('packaging_materials_testing_content_missing', 'The canonical packaging materials testing content bundle is missing.');
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
    custom_box_sync_packaging_materials_testing_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_packaging_materials_testing_images($post_id);

    return $post_id;
}

function custom_box_sync_packaging_materials_testing_terms(int $post_id, array $data): void
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

function custom_box_sync_packaging_materials_testing_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_packaging_materials_testing_images() as $key => $image) {
        $attachment_id = custom_box_find_packaging_materials_testing_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_packaging_materials_testing_attachment($image['base'], $post_id, $image);
        }
        $restored = $attachment_id
            ? custom_box_restore_packaging_materials_testing_attachment($attachment_id, $image['base'])
            : false;
        $url = $attachment_id ? wp_get_attachment_url($attachment_id) : false;
        if (!$attachment_id || !$restored || !$url) {
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

        $marker = '<!-- packaging-materials-testing-image:' . $key . ' -->';
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

    update_option('custom_box_packaging_materials_testing_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_packaging_materials_testing_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_restore_packaging_materials_testing_attachment(int $attachment_id, string $base): bool
{
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return false;
    }

    $attached = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
    $extension = strtolower((string) pathinfo($attached, PATHINFO_EXTENSION));
    $extensions = in_array($extension, array('webp', 'png', 'jpg', 'jpeg'), true)
        ? array($extension)
        : array('webp', 'png', 'jpg', 'jpeg');

    foreach ($extensions as $candidate_extension) {
        $candidate_relative = '2026/08/' . $base . '.' . $candidate_extension;
        $upload_path = trailingslashit($uploads['basedir']) . $candidate_relative;
        $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $candidate_relative;
        if (!file_exists($bundle_path)) {
            continue;
        }

        $bundle_hash = hash_file('sha256', $bundle_path);
        $upload_hash = file_exists($upload_path) ? hash_file('sha256', $upload_path) : false;
        if (is_string($bundle_hash) && is_string($upload_hash) && hash_equals($bundle_hash, $upload_hash)) {
            return true;
        }
        if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
            continue;
        }

        update_post_meta($attachment_id, '_wp_attached_file', $candidate_relative);
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata($attachment_id, $upload_path);
        if (is_array($metadata)) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        }

        return true;
    }

    return false;
}

function custom_box_find_packaging_materials_testing_attachment(string $base): int
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

function custom_box_create_packaging_materials_testing_attachment(string $base, int $post_id, array $image): int
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
            'guid' => trailingslashit($uploads['baseurl']) . $candidate_relative,
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

function custom_box_packaging_materials_testing_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_packaging_materials_testing_post_data();
    $images = custom_box_packaging_materials_testing_images();
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

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_packaging_materials_testing_attachment($image['base']);
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
        4 !== substr_count($content, '<!-- packaging-materials-testing-image:')
        || 4 !== substr_count($content, '<figure>')
        || 4 !== substr_count($content, '<img ')
    ) {
        $failures[] = 'inline image counts';
    }
    foreach ($images as $key => $image) {
        if ('featured' !== $key && false === strpos($content, $image['base'])) {
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
    if ((array) get_option('custom_box_packaging_materials_testing_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_packaging_materials_testing_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_packaging_materials_testing_validation_failures', array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_packaging_materials_testing_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_PACKAGING_MATERIALS_TESTING_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}
