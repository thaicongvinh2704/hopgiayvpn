<?php
/**
 * Deploys the Food Packaging Seal Integrity Testing draft and its images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_SYNC_VERSION = '2026-08-06-v1';
const CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_VERSION_OPTION = 'custom_box_food_packaging_seal_integrity_testing_sync_version';
const CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_NOTICE_OPTION = 'custom_box_food_packaging_seal_integrity_testing_sync_notice';

add_action('admin_init', 'custom_box_sync_food_packaging_seal_integrity_testing_post');
add_action('admin_notices', 'custom_box_food_packaging_seal_integrity_testing_admin_notice');

function custom_box_food_packaging_seal_integrity_testing_post_data(): array
{
    return array(
        'title' => 'Food Packaging Seal Integrity Testing: Methods and Failure Modes',
        'slug' => 'food-packaging-seal-integrity-testing',
        'excerpt' => 'A technical guide to food packaging seal integrity testing, including method selection, test procedures, failure diagnosis and corrective actions for sealing processes, materials and secondary packaging.',
        'category' => array(
            'name' => 'Packaging Testing & Quality Control',
            'slug' => 'packaging-testing-quality-control',
        ),
        'tags' => array(
            'Food Packaging Testing' => 'food-packaging-testing',
            'Seal Integrity' => 'seal-integrity',
            'Package Leak Testing' => 'package-leak-testing',
            'Packaging Quality Control' => 'packaging-quality-control',
            'Corrugated Mailer Boxes' => 'corrugated-mailer-boxes',
        ),
        'seo_title' => 'Food Packaging Seal Integrity Testing: Methods & Failures',
        'seo_description' => 'Compare food packaging seal integrity tests, diagnose channel leaks, pinholes and weak seals, and choose corrective actions for materials and structures.',
        'focus_keyword' => 'food packaging seal integrity testing',
    );
}

function custom_box_food_packaging_seal_integrity_testing_images(): array
{
    return array(
        'featured' => array(
            'base' => 'food-packaging-seal-integrity-testing-methods',
            'alt' => 'Food packaging seal integrity testing equipment with sealed pouches and a corrugated mailer',
            'title' => 'Food Packaging Seal Integrity Testing Methods',
            'caption' => 'Seal integrity testing should connect the detected defect with its process, material or structural cause.',
        ),
        'slot_1' => array(
            'base' => 'food-package-seal-failure-modes',
            'alt' => 'Channel leak, contaminated seal and pinhole defects in flexible food packages',
            'title' => 'Food Package Seal Failure Modes',
            'caption' => 'Seal location and defect shape provide evidence about the likely failure mechanism.',
        ),
        'slot_2' => array(
            'base' => 'food-packaging-integrity-test-methods',
            'alt' => 'Bubble leak, dye penetration and seal strength tests for food packaging',
            'title' => 'Food Packaging Integrity Test Methods',
            'caption' => 'Different tests measure different aspects of seal performance and should not be treated as interchangeable.',
        ),
        'slot_3' => array(
            'base' => 'seal-leak-location-diagnostic-map',
            'alt' => 'QC inspection showing seal leak locations on food pouches',
            'title' => 'Seal Leak Location Diagnostic Map',
            'caption' => 'Repeated failures at the same seal position often point to a specific machine, tooling or geometry problem.',
        ),
        'slot_4' => array(
            'base' => 'corrugated-mailer-protection-for-food-pouches',
            'alt' => 'Corrugated mailer with dividers protecting sealed food pouches during shipping',
            'title' => 'Corrugated Mailer Protection for Sealed Food Pouches',
            'caption' => 'Secondary packaging can reduce puncture, abrasion and movement, but it cannot repair a leaking primary seal.',
        ),
    );
}

function custom_box_food_packaging_seal_integrity_testing_content(): string
{
    $path = __DIR__ . '/post-content/food-packaging-seal-integrity-testing.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    return is_string($content) ? $content : '';
}

function custom_box_find_food_packaging_seal_integrity_testing_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_food_packaging_seal_integrity_testing_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_food_packaging_seal_integrity_testing_post_data();
    $post = custom_box_find_food_packaging_seal_integrity_testing_post($data['slug'], $data['title']);
    if (
        CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_SYNC_VERSION === get_option(CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_VERSION_OPTION)
        && $post
        && custom_box_food_packaging_seal_integrity_testing_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_food_packaging_seal_integrity_testing_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_VERSION_OPTION);
        custom_box_food_packaging_seal_integrity_testing_set_notice(false, $post_id->get_error_message());
        return;
    }

    if (custom_box_food_packaging_seal_integrity_testing_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_VERSION_OPTION, CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_SYNC_VERSION, false);
        custom_box_food_packaging_seal_integrity_testing_set_notice(true, sprintf(
            'Food packaging seal integrity testing draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Testing & Quality Control, 5 tags, and Rank Math fields verified.',
            (int) $post_id,
            (int) get_post_thumbnail_id((int) $post_id)
        ));
        return;
    }

    delete_option(CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_VERSION_OPTION);
    custom_box_food_packaging_seal_integrity_testing_set_notice(false, 'Food packaging seal integrity testing sync is incomplete. Missing images: '
        . implode(', ', (array) get_option('custom_box_food_packaging_seal_integrity_testing_missing_images', array()))
        . '; missing slots or validation failures: '
        . implode(', ', array_merge(
            (array) get_option('custom_box_food_packaging_seal_integrity_testing_missing_slots', array()),
            (array) get_option('custom_box_food_packaging_seal_integrity_testing_validation_failures', array())
        )));
}

function custom_box_food_packaging_seal_integrity_testing_set_notice(bool $success, string $message): void
{
    update_option(CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_NOTICE_OPTION, array(
        'success' => $success,
        'message' => $message,
    ), false);
}

function custom_box_upsert_food_packaging_seal_integrity_testing_post()
{
    $data = custom_box_food_packaging_seal_integrity_testing_post_data();
    $post = custom_box_find_food_packaging_seal_integrity_testing_post($data['slug'], $data['title']);
    $content = custom_box_food_packaging_seal_integrity_testing_content();
    if ('' === trim($content)) {
        return new WP_Error('food_packaging_seal_integrity_testing_content_missing', 'The canonical food packaging seal integrity testing content bundle is missing.');
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
    custom_box_sync_food_packaging_seal_integrity_testing_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_food_packaging_seal_integrity_testing_images($post_id);

    return $post_id;
}

function custom_box_sync_food_packaging_seal_integrity_testing_terms(int $post_id, array $data): void
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

function custom_box_sync_food_packaging_seal_integrity_testing_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_food_packaging_seal_integrity_testing_images() as $key => $image) {
        $attachment_id = custom_box_find_food_packaging_seal_integrity_testing_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_food_packaging_seal_integrity_testing_attachment($image['base'], $post_id, $image);
        }
        $restored = $attachment_id
            ? custom_box_restore_food_packaging_seal_integrity_testing_attachment($attachment_id, $image['base'])
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

        $marker = '<!-- food-packaging-seal-integrity-testing-image:' . $key . ' -->';
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

    update_option('custom_box_food_packaging_seal_integrity_testing_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_food_packaging_seal_integrity_testing_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_restore_food_packaging_seal_integrity_testing_attachment(int $attachment_id, string $base): bool
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

function custom_box_find_food_packaging_seal_integrity_testing_attachment(string $base): int
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

function custom_box_create_food_packaging_seal_integrity_testing_attachment(string $base, int $post_id, array $image): int
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

function custom_box_food_packaging_seal_integrity_testing_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_food_packaging_seal_integrity_testing_post_data();
    $images = custom_box_food_packaging_seal_integrity_testing_images();
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
        $attachment_id = custom_box_find_food_packaging_seal_integrity_testing_attachment($image['base']);
        $attachment = $attachment_id ? get_post($attachment_id) : null;
        $attached_file = $attachment_id ? (string) get_post_meta($attachment_id, '_wp_attached_file', true) : '';
        if (
            !$attachment
            || 'attachment' !== $attachment->post_type
            || $image['base'] !== pathinfo(wp_basename($attached_file), PATHINFO_FILENAME)
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
        4 !== substr_count($content, '<!-- food-packaging-seal-integrity-testing-image:')
        || 4 !== preg_match_all('/<figure\b/i', $content)
        || 4 !== preg_match_all('/<img\b/i', $content)
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
    $expected_categories = array($data['category']['slug']);
    if (is_wp_error($categories)) {
        $failures[] = 'category';
    } else {
        sort($categories);
        sort($expected_categories);
        if ($categories !== $expected_categories) {
            $failures[] = 'exact category';
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
        $data['seo_title'] !== get_post_meta($post_id, 'rank_math_title', true)
        || $data['seo_description'] !== get_post_meta($post_id, 'rank_math_description', true)
        || $data['focus_keyword'] !== get_post_meta($post_id, 'rank_math_focus_keyword', true)
    ) {
        $failures[] = 'Rank Math metadata';
    }
    if ((array) get_option('custom_box_food_packaging_seal_integrity_testing_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_food_packaging_seal_integrity_testing_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_food_packaging_seal_integrity_testing_validation_failures', array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_food_packaging_seal_integrity_testing_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_FOOD_PACKAGING_SEAL_INTEGRITY_TESTING_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}
