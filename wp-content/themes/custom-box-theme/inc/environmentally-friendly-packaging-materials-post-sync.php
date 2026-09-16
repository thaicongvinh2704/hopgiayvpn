<?php
/**
 * Imports the environmentally friendly packaging materials comparison and five images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_ENV_PACKAGING_MATERIALS_SYNC_VERSION = '2026-09-16-environmentally-friendly-packaging-materials-v1';
const CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VERSION_OPTION = 'custom_box_env_packaging_materials_sync_version';
const CUSTOM_BOX_ENV_PACKAGING_MATERIALS_MISSING_IMAGES_OPTION = 'custom_box_env_packaging_materials_missing_images';
const CUSTOM_BOX_ENV_PACKAGING_MATERIALS_MISSING_SLOTS_OPTION = 'custom_box_env_packaging_materials_missing_slots';
const CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VALIDATION_OPTION = 'custom_box_env_packaging_materials_validation_failures';

add_action('admin_init', 'custom_box_sync_env_packaging_materials_post');
add_action('admin_notices', 'custom_box_env_packaging_materials_admin_notice');

function custom_box_env_packaging_materials_post_data(): array
{
    return array(
        'title' => 'Environmentally Friendly Packaging Materials: Performance, Cost and End-of-Life Compared',
        'slug' => 'environmentally-friendly-packaging-materials',
        'excerpt' => 'Compare paperboard, kraft, molded fiber and coatings using measurable criteria for sourcing, strength, printability, barrier performance, cost and end-of-life.',
        'category' => array('name' => 'Sustainable Packaging', 'slug' => 'sustainable-packaging'),
        'tags' => array(
            'Sustainable Packaging' => 'sustainable-packaging',
            'Packaging Materials' => 'packaging-materials',
            'Kraft Paper' => 'kraft-paper',
            'Paperboard' => 'paperboard',
            'Molded Fiber' => 'molded-fiber',
            'Recycled Packaging' => 'recycled-packaging',
            'Packaging Sustainability' => 'packaging-sustainability',
        ),
        'seo_title' => 'Environmentally Friendly Packaging Materials Compared',
        'seo_description' => 'Compare paperboard, kraft, molded fiber and coatings by strength, printability, cost, sourcing evidence and end-of-life to choose better packaging.',
        'focus_keyword' => 'environmentally friendly packaging materials',
        'canonical_path' => '/environmentally-friendly-packaging-materials/',
    );
}

function custom_box_env_packaging_materials_images(): array
{
    return array(
        'featured' => array(
            'base' => 'environmentally-friendly-packaging-materials-performance-cost-end-of-life',
            'alt' => 'Environmentally friendly packaging materials compared by performance, cost and end-of-life',
            'title' => 'Environmentally Friendly Packaging Materials Comparison',
            'caption' => 'Paperboard, kraft, molded fiber and coated board should be compared as packaging systems, not by green labels alone.',
        ),
        'slot_1' => array(
            'base' => 'packaging-material-procurement-weighted-scorecard',
            'alt' => 'Procurement scorecard for comparing sustainable packaging materials',
            'title' => 'Sustainable Packaging Procurement Scorecard',
            'caption' => 'A material scorecard connects sustainability criteria with the performance requirements of the actual package.',
        ),
        'slot_2' => array(
            'base' => 'paperboard-kraft-molded-fiber-sample-comparison',
            'alt' => 'Paperboard, kraft and molded fiber packaging material samples compared side by side',
            'title' => 'Paperboard Kraft and Molded Fiber Samples',
            'caption' => 'Physical samples reveal differences in surface, structure, printing potential and protective function.',
        ),
        'slot_3' => array(
            'base' => 'paper-packaging-barrier-coating-performance-test',
            'alt' => 'Barrier coating performance test on paper packaging samples',
            'title' => 'Paper Packaging Barrier Coating Test',
            'caption' => 'Barrier performance should be tested together with the intended recycling or recovery route.',
        ),
        'slot_4' => array(
            'base' => 'sustainable-packaging-certification-evidence-review',
            'alt' => 'Packaging buyer reviewing material specifications and sustainability evidence',
            'title' => 'Sustainable Packaging Evidence Review',
            'caption' => 'Procurement teams should connect every sustainability claim to material specifications and supporting evidence.',
        ),
    );
}

function custom_box_env_packaging_materials_content(): string
{
    $path = __DIR__ . '/post-content/environmentally-friendly-packaging-materials.html';
    $content = is_readable($path) ? file_get_contents($path) : false;
    if (!is_string($content) || '' === trim($content)) {
        return '';
    }
    return str_replace('https://hopgiayvpn.com/', trailingslashit(home_url('/')), $content);
}

function custom_box_find_env_packaging_materials_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_env_packaging_materials_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $data = custom_box_env_packaging_materials_post_data();
    $post = custom_box_find_env_packaging_materials_post($data['slug'], $data['title']);
    if (
        CUSTOM_BOX_ENV_PACKAGING_MATERIALS_SYNC_VERSION === get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VERSION_OPTION)
        && $post
        && custom_box_env_packaging_materials_is_complete((int) $post->ID)
    ) {
        return;
    }
    $post_id = custom_box_upsert_env_packaging_materials_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VERSION_OPTION);
        update_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VALIDATION_OPTION, array($post_id->get_error_message()), false);
        return;
    }
    if (custom_box_env_packaging_materials_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VERSION_OPTION, CUSTOM_BOX_ENV_PACKAGING_MATERIALS_SYNC_VERSION, false);
    } else {
        delete_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VERSION_OPTION);
    }
}

function custom_box_upsert_env_packaging_materials_post()
{
    $data = custom_box_env_packaging_materials_post_data();
    $post = custom_box_find_env_packaging_materials_post($data['slug'], $data['title']);
    $content = custom_box_env_packaging_materials_content();
    if ('' === trim($content)) {
        return new WP_Error('env_packaging_materials_content_missing', 'The environmentally friendly packaging materials content bundle is missing.');
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
        $result = wp_insert_post($payload, true);
    }
    if (is_wp_error($result)) {
        return $result;
    }
    $post_id = (int) $result;
    custom_box_sync_env_packaging_materials_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_post_meta($post_id, 'rank_math_canonical_url', home_url($data['canonical_path']));
    custom_box_sync_env_packaging_materials_images($post_id);
    return $post_id;
}

function custom_box_sync_env_packaging_materials_terms(int $post_id, array $data): void
{
    $definition = $data['category'];
    $term = get_term_by('slug', $definition['slug'], 'category');
    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term($definition['name'], 'category', array('slug' => $definition['slug']));
        if (!is_wp_error($created)) {
            $term = get_term((int) $created['term_id'], 'category');
        }
    }
    if ($term && !is_wp_error($term)) {
        wp_set_post_categories($post_id, array((int) $term->term_id), false);
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

function custom_box_env_packaging_materials_bundle_path(string $base): string
{
    return __DIR__ . '/product-sample-deploy-assets/uploads/2026/09/' . $base . '.webp';
}

function custom_box_env_packaging_materials_upload_path(string $base): array
{
    $uploads = wp_get_upload_dir();
    $relative = '2026/09/' . $base . '.webp';
    return array(
        'relative' => $relative,
        'path' => trailingslashit($uploads['basedir']) . $relative,
        'url' => trailingslashit($uploads['baseurl']) . $relative,
    );
}

function custom_box_find_env_packaging_materials_attachment(string $base): int
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

function custom_box_ensure_env_packaging_materials_attachment_file(string $base): array
{
    $target = custom_box_env_packaging_materials_upload_path($base);
    if (file_exists($target['path'])) {
        return $target;
    }
    $bundle = custom_box_env_packaging_materials_bundle_path($base);
    if (file_exists($bundle) && filesize($bundle) < 100000 && wp_mkdir_p(dirname($target['path'])) && copy($bundle, $target['path'])) {
        return $target;
    }
    return array();
}

function custom_box_create_env_packaging_materials_attachment(int $post_id, array $image): int
{
    $target = custom_box_ensure_env_packaging_materials_attachment_file($image['base']);
    if (!$target) {
        return 0;
    }
    $type = wp_check_filetype(wp_basename($target['path']), null);
    $attachment_id = wp_insert_attachment(array(
        'guid' => $target['url'],
        'post_mime_type' => !empty($type['type']) ? $type['type'] : 'image/webp',
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

function custom_box_sync_env_packaging_materials_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();
    foreach (custom_box_env_packaging_materials_images() as $key => $image) {
        $attachment_id = custom_box_find_env_packaging_materials_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_env_packaging_materials_attachment($post_id, $image);
        } else {
            $target = custom_box_ensure_env_packaging_materials_attachment_file($image['base']);
            if ($target) {
                update_post_meta($attachment_id, '_wp_attached_file', $target['relative']);
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
        $marker = '<!-- environmentally-friendly-packaging-materials-image:' . $key . ' -->';
        $figure = $marker . "\n<figure><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $slot = '<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';
        $wrapped_slot_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
        if (preg_match($marker_pattern, $content)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
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
    update_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_MISSING_IMAGES_OPTION, array_values(array_unique($missing_images)), false);
    update_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_MISSING_SLOTS_OPTION, array_values(array_unique($missing_slots)), false);
}

function custom_box_env_packaging_materials_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_env_packaging_materials_post_data();
    $images = custom_box_env_packaging_materials_images();
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
        $attachment_id = custom_box_find_env_packaging_materials_attachment($image['base']);
        $attachment = $attachment_id ? get_post($attachment_id) : null;
        $relative = $attachment_id ? (string) get_post_meta($attachment_id, '_wp_attached_file', true) : '';
        $target = $relative ? trailingslashit(wp_get_upload_dir()['basedir']) . $relative : '';
        $metadata = $attachment_id ? wp_get_attachment_metadata($attachment_id) : array();
        if (!$attachment || 'attachment' !== $attachment->post_type || $post_id !== (int) $attachment->post_parent || $image['title'] !== $attachment->post_title || $image['caption'] !== $attachment->post_excerpt || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true) || !$target || !file_exists($target) || filesize($target) >= 100000 || 1672 !== (int) ($metadata['width'] ?? 0) || 941 !== (int) ($metadata['height'] ?? 0)) {
            $failures[] = $key . ' attachment';
        }
    }
    $content = $post ? (string) $post->post_content : '';
    if (4 !== substr_count($content, '<!-- environmentally-friendly-packaging-materials-image:slot_')) {
        $failures[] = 'inline image markers';
    }
    if (4 !== preg_match_all('/<figure\b/i', $content) || 4 !== preg_match_all('/<img\b/i', $content)) {
        $failures[] = 'inline figures or images';
    }
    if (preg_match('/IMAGE_SLOT_[0-9]+|<h1\b/i', $content)) {
        $failures[] = 'invalid canonical content';
    }
    $category_slugs = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    if (array($data['category']['slug']) !== $category_slugs) {
        $failures[] = 'category set';
    }
    $tag_slugs = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs'));
    $expected_tags = array_values($data['tags']);
    sort($tag_slugs);
    sort($expected_tags);
    if ($expected_tags !== $tag_slugs) {
        $failures[] = 'tag set';
    }
    if ((string) get_post_meta($post_id, 'rank_math_title', true) !== $data['seo_title'] || (string) get_post_meta($post_id, 'rank_math_description', true) !== $data['seo_description'] || (string) get_post_meta($post_id, 'rank_math_focus_keyword', true) !== $data['focus_keyword'] || (string) get_post_meta($post_id, 'rank_math_canonical_url', true) !== home_url($data['canonical_path'])) {
        $failures[] = 'Rank Math metadata';
    }
    update_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VALIDATION_OPTION, $failures, false);
    return empty($failures);
}

function custom_box_env_packaging_materials_report(): array
{
    $data = custom_box_env_packaging_materials_post_data();
    $post = custom_box_find_env_packaging_materials_post($data['slug'], $data['title']);
    if (!$post) {
        return array('complete' => false, 'error' => 'Post not found.');
    }
    $content = (string) $post->post_content;
    return array(
        'complete' => custom_box_env_packaging_materials_is_complete((int) $post->ID),
        'post_id' => (int) $post->ID,
        'status' => $post->post_status,
        'slug' => $post->post_name,
        'permalink' => get_permalink($post->ID),
        'featured_id' => (int) get_post_thumbnail_id($post->ID),
        'inline_markers' => substr_count($content, '<!-- environmentally-friendly-packaging-materials-image:slot_'),
        'figures' => (int) preg_match_all('/<figure\b/i', $content),
        'remaining_slots' => (int) preg_match_all('/IMAGE_SLOT_[0-9]+/', $content),
        'word_count' => str_word_count(wp_strip_all_tags($content)),
        'categories' => wp_get_post_terms($post->ID, 'category', array('fields' => 'slugs')),
        'tags' => wp_get_post_terms($post->ID, 'post_tag', array('fields' => 'slugs')),
        'rank_math' => array(
            'title' => (string) get_post_meta($post->ID, 'rank_math_title', true),
            'description' => (string) get_post_meta($post->ID, 'rank_math_description', true),
            'focus_keyword' => (string) get_post_meta($post->ID, 'rank_math_focus_keyword', true),
            'canonical' => (string) get_post_meta($post->ID, 'rank_math_canonical_url', true),
        ),
        'missing_images' => (array) get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_MISSING_IMAGES_OPTION, array()),
        'missing_slots' => (array) get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_MISSING_SLOTS_OPTION, array()),
        'validation_failures' => (array) get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VALIDATION_OPTION, array()),
        'sync_version' => (string) get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VERSION_OPTION, ''),
    );
}

function custom_box_env_packaging_materials_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $data = custom_box_env_packaging_materials_post_data();
    $post = custom_box_find_env_packaging_materials_post($data['slug'], $data['title']);
    if ($post && custom_box_env_packaging_materials_is_complete((int) $post->ID)) {
        $report = custom_box_env_packaging_materials_report();
        printf(
            '<div class="notice notice-success"><p><strong>Environmentally friendly packaging materials synced:</strong> post ID %d, featured image %d, %d inline figures, Sustainable Packaging category, 7 tags and Rank Math metadata verified.</p></div>',
            (int) $report['post_id'],
            (int) $report['featured_id'],
            (int) $report['figures']
        );
        return;
    }
    $failures = (array) get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_VALIDATION_OPTION, array());
    $missing_images = (array) get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_MISSING_IMAGES_OPTION, array());
    $missing_slots = (array) get_option(CUSTOM_BOX_ENV_PACKAGING_MATERIALS_MISSING_SLOTS_OPTION, array());
    if ($failures || $missing_images || $missing_slots) {
        echo '<div class="notice notice-warning"><p><strong>Environmentally friendly packaging materials sync incomplete:</strong> ' . esc_html(implode(' | ', array_merge($failures, $missing_images, $missing_slots))) . '</p></div>';
    }
}
