<?php
/**
 * Imports the evidence-led eco-friendly food packaging guide and its five images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_SYNC_VERSION = '2026-09-14-eco-food-packaging-materials-v1';
const CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VERSION_OPTION = 'custom_box_eco_food_packaging_materials_sync_version';
const CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_MISSING_IMAGES_OPTION = 'custom_box_eco_food_packaging_materials_missing_images';
const CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_MISSING_SLOTS_OPTION = 'custom_box_eco_food_packaging_materials_missing_slots';
const CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VALIDATION_OPTION = 'custom_box_eco_food_packaging_materials_validation_failures';

add_action('admin_init', 'custom_box_sync_eco_food_packaging_materials_post');
add_action('admin_notices', 'custom_box_eco_food_packaging_materials_admin_notice');

function custom_box_eco_food_packaging_materials_post_data(): array
{
    return array(
        'title' => 'Eco-Friendly Food Packaging: Materials, Performance and Trade-Offs',
        'slug' => 'eco-friendly-food-packaging-materials',
        'excerpt' => 'Compare recycled paper, kraft, molded pulp, bagasse and barrier-coated food packaging by performance, recyclability, compostability, cost and the evidence needed to support environmental claims.',
        'categories' => array(
            array('name' => 'Food Packaging', 'slug' => 'food-packaging'),
            array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
        ),
        'tags' => array(
            'Eco-Friendly Food Packaging' => 'eco-friendly-food-packaging',
            'Sustainable Packaging' => 'sustainable-packaging',
            'Food Paper Boxes' => 'food-paper-boxes',
            'Recyclable Packaging' => 'recyclable-packaging',
            'Compostable Packaging' => 'compostable-packaging',
            'Bagasse' => 'bagasse',
            'Molded Pulp' => 'molded-pulp',
        ),
        'seo_title' => 'Eco-Friendly Food Packaging: Materials & Trade-Offs',
        'seo_description' => 'Compare eco-friendly food packaging materials, barriers, recyclability, compostability, costs and evidence needed to support sustainability claims.',
        'focus_keyword' => 'eco friendly food packaging',
        'canonical_path' => '/eco-friendly-food-packaging-materials/',
    );
}

function custom_box_eco_food_packaging_materials_images(): array
{
    return array(
        'featured' => array(
            'base' => 'eco-friendly-food-packaging-materials-performance-tradeoffs',
            'alt' => 'Eco-friendly food packaging materials including kraft paper, molded pulp, bagasse and coated paperboard',
            'title' => 'Eco-Friendly Food Packaging Materials',
            'caption' => 'Sustainable food packaging should be compared by material, performance and verified end-of-life pathways.',
        ),
        'slot_1' => array(
            'base' => 'eco-friendly-food-packaging-material-system-comparison',
            'alt' => 'Comparison of recycled paperboard, kraft paper, molded pulp, bagasse and coated food packaging',
            'title' => 'Food Packaging Material System Comparison',
            'caption' => 'Similar-looking fibre packages can use very different materials, barriers and disposal pathways.',
        ),
        'slot_2' => array(
            'base' => 'paper-food-packaging-barrier-coating-tradeoffs',
            'alt' => 'Cross-section of paper food packaging showing fibre board and grease moisture barrier coating',
            'title' => 'Paper Food Packaging Barrier Trade-Offs',
            'caption' => 'Barrier layers improve food protection but must be evaluated as part of the complete packaging structure.',
        ),
        'slot_3' => array(
            'base' => 'food-packaging-recycling-composting-disposal-path',
            'alt' => 'Decision path for recycling or composting fibre-based food packaging',
            'title' => 'Food Packaging Disposal Path',
            'caption' => 'End-of-life claims depend on the complete package, collection system, facility acceptance and supporting evidence.',
        ),
        'slot_4' => array(
            'base' => 'sustainable-food-packaging-supplier-documentation-checklist',
            'alt' => 'Supplier documentation checklist for sustainable food packaging claims',
            'title' => 'Sustainable Packaging Documentation Checklist',
            'caption' => 'Material specifications, certifications and test reports should be checked before environmental claims are approved.',
        ),
    );
}

function custom_box_eco_food_packaging_materials_content(): string
{
    $path = __DIR__ . '/post-content/eco-friendly-food-packaging-materials.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    if (!is_string($content) || '' === trim($content)) {
        return '';
    }

    return str_replace('https://hopgiayvpn.com/', trailingslashit(home_url('/')), $content);
}

function custom_box_find_eco_food_packaging_materials_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_eco_food_packaging_materials_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_eco_food_packaging_materials_post_data();
    $post = custom_box_find_eco_food_packaging_materials_post($data['slug'], $data['title']);

    if (
        CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_SYNC_VERSION === get_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VERSION_OPTION)
        && $post
        && custom_box_eco_food_packaging_materials_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_eco_food_packaging_materials_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VERSION_OPTION);
        update_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VALIDATION_OPTION, array($post_id->get_error_message()), false);
        return;
    }

    if (custom_box_eco_food_packaging_materials_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VERSION_OPTION, CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VALIDATION_OPTION, array(), false);
    } else {
        delete_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VERSION_OPTION);
    }
}

function custom_box_upsert_eco_food_packaging_materials_post()
{
    $data = custom_box_eco_food_packaging_materials_post_data();
    $post = custom_box_find_eco_food_packaging_materials_post($data['slug'], $data['title']);
    $content = custom_box_eco_food_packaging_materials_content();

    if ('' === trim($content)) {
        return new WP_Error('eco_food_packaging_materials_content_missing', 'The eco-friendly food packaging materials content bundle is missing.');
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
    custom_box_sync_eco_food_packaging_materials_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_post_meta($post_id, 'rank_math_canonical_url', home_url($data['canonical_path']));
    custom_box_sync_eco_food_packaging_materials_images($post_id);

    return $post_id;
}

function custom_box_sync_eco_food_packaging_materials_terms(int $post_id, array $data): void
{
    $category_ids = array();
    foreach ($data['categories'] as $definition) {
        $term = get_term_by('slug', $definition['slug'], 'category');
        if (!$term || is_wp_error($term)) {
            $created = wp_insert_term($definition['name'], 'category', array('slug' => $definition['slug']));
            if (!is_wp_error($created)) {
                $term = get_term((int) $created['term_id'], 'category');
            }
        }
        if ($term && !is_wp_error($term)) {
            $category_ids[] = (int) $term->term_id;
        }
    }
    if ($category_ids) {
        wp_set_post_categories($post_id, $category_ids, false);
    }

    $tag_ids = array();
    foreach ($data['tags'] as $name => $slug) {
        $term = get_term_by('slug', $slug, 'post_tag');
        if (!$term || is_wp_error($term)) {
            $created = wp_insert_term($name, 'post_tag', array('slug' => $slug));
            if (!is_wp_error($created)) {
                $tag_ids[] = (int) $created['term_id'];
            }
        } else {
            $tag_ids[] = (int) $term->term_id;
        }
    }
    wp_set_post_terms($post_id, $tag_ids, 'post_tag', false);
}

function custom_box_eco_food_packaging_materials_bundle_path(string $base): string
{
    return __DIR__ . '/product-sample-deploy-assets/uploads/2026/09/' . $base . '.webp';
}

function custom_box_eco_food_packaging_materials_upload_path(string $base): array
{
    $uploads = wp_get_upload_dir();
    $relative = '2026/09/' . $base . '.webp';
    return array(
        'relative' => $relative,
        'path' => trailingslashit($uploads['basedir']) . $relative,
        'url' => trailingslashit($uploads['baseurl']) . $relative,
    );
}

function custom_box_find_eco_food_packaging_materials_attachment(string $base): int
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

function custom_box_ensure_eco_food_packaging_materials_attachment_file(string $base): array
{
    $target = custom_box_eco_food_packaging_materials_upload_path($base);
    if (file_exists($target['path'])) {
        return $target;
    }

    $bundle = custom_box_eco_food_packaging_materials_bundle_path($base);
    if (file_exists($bundle) && wp_mkdir_p(dirname($target['path'])) && copy($bundle, $target['path'])) {
        return $target;
    }

    return array();
}

function custom_box_create_eco_food_packaging_materials_attachment(int $post_id, array $image): int
{
    $target = custom_box_ensure_eco_food_packaging_materials_attachment_file($image['base']);
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

function custom_box_sync_eco_food_packaging_materials_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_eco_food_packaging_materials_images() as $key => $image) {
        $attachment_id = custom_box_find_eco_food_packaging_materials_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_eco_food_packaging_materials_attachment($post_id, $image);
        } else {
            $attached = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
            $uploads = wp_get_upload_dir();
            $attached_path = $attached ? trailingslashit($uploads['basedir']) . $attached : '';
            if (!$attached_path || !file_exists($attached_path)) {
                $target = custom_box_ensure_eco_food_packaging_materials_attachment_file($image['base']);
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

        $marker = '<!-- eco-friendly-food-packaging-materials-image:' . $key . ' -->';
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

    update_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_MISSING_IMAGES_OPTION, array_values(array_unique($missing_images)), false);
    update_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_MISSING_SLOTS_OPTION, array_values(array_unique($missing_slots)), false);
}

function custom_box_eco_food_packaging_materials_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_eco_food_packaging_materials_post_data();
    $images = custom_box_eco_food_packaging_materials_images();
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
        $attachment_id = custom_box_find_eco_food_packaging_materials_attachment($image['base']);
        $attachment = $attachment_id ? get_post($attachment_id) : null;
        if (!$attachment || 'attachment' !== $attachment->post_type || $post_id !== (int) $attachment->post_parent || $image['title'] !== $attachment->post_title || $image['caption'] !== $attachment->post_excerpt || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true) || !wp_get_attachment_url($attachment_id)) {
            $failures[] = $key . ' attachment metadata';
        }
    }

    $content = $post ? (string) $post->post_content : '';
    if (4 !== substr_count($content, '<!-- eco-friendly-food-packaging-materials-image:slot_')) {
        $failures[] = 'inline image markers';
    }
    if (4 !== preg_match_all('/<figure\b/i', $content)) {
        $failures[] = 'inline figures';
    }
    if (preg_match('/IMAGE_SLOT_[0-9]+/i', $content)) {
        $failures[] = 'image slots remain';
    }
    if (preg_match('/<h1\b/i', $content)) {
        $failures[] = 'content contains H1';
    }

    $category_slugs = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    foreach ($data['categories'] as $category) {
        if (is_wp_error($category_slugs) || !in_array($category['slug'], $category_slugs, true)) {
            $failures[] = 'category ' . $category['slug'];
        }
    }
    $tag_slugs = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'slugs'));
    foreach ($data['tags'] as $slug) {
        if (is_wp_error($tag_slugs) || !in_array($slug, $tag_slugs, true)) {
            $failures[] = 'tag ' . $slug;
        }
    }

    if ((string) get_post_meta($post_id, 'rank_math_title', true) !== $data['seo_title']) {
        $failures[] = 'Rank Math title';
    }
    if ((string) get_post_meta($post_id, 'rank_math_description', true) !== $data['seo_description']) {
        $failures[] = 'Rank Math description';
    }
    if ((string) get_post_meta($post_id, 'rank_math_focus_keyword', true) !== $data['focus_keyword']) {
        $failures[] = 'Rank Math focus keyword';
    }
    if ((string) get_post_meta($post_id, 'rank_math_canonical_url', true) !== home_url($data['canonical_path'])) {
        $failures[] = 'Rank Math canonical';
    }

    update_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VALIDATION_OPTION, $failures, false);
    return empty($failures);
}

function custom_box_eco_food_packaging_materials_report(): array
{
    $data = custom_box_eco_food_packaging_materials_post_data();
    $post = custom_box_find_eco_food_packaging_materials_post($data['slug'], $data['title']);
    if (!$post) {
        return array('complete' => false, 'error' => 'Post not found.');
    }

    $content = (string) $post->post_content;
    return array(
        'complete' => custom_box_eco_food_packaging_materials_is_complete((int) $post->ID),
        'post_id' => (int) $post->ID,
        'status' => $post->post_status,
        'slug' => $post->post_name,
        'permalink' => get_permalink($post->ID),
        'featured_id' => (int) get_post_thumbnail_id($post->ID),
        'inline_markers' => substr_count($content, '<!-- eco-friendly-food-packaging-materials-image:slot_'),
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
        'missing_images' => (array) get_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_MISSING_IMAGES_OPTION, array()),
        'missing_slots' => (array) get_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_MISSING_SLOTS_OPTION, array()),
        'validation_failures' => (array) get_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VALIDATION_OPTION, array()),
        'sync_version' => (string) get_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VERSION_OPTION, ''),
    );
}

function custom_box_eco_food_packaging_materials_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $failures = (array) get_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_VALIDATION_OPTION, array());
    $missing_images = (array) get_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_MISSING_IMAGES_OPTION, array());
    $missing_slots = (array) get_option(CUSTOM_BOX_ECO_FOOD_PACKAGING_MATERIALS_MISSING_SLOTS_OPTION, array());
    if (!$failures && !$missing_images && !$missing_slots) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Eco-friendly food packaging materials post sync:</strong> ';
    $messages = array();
    if ($missing_images) {
        $messages[] = 'missing images: ' . implode(', ', $missing_images);
    }
    if ($missing_slots) {
        $messages[] = 'missing slots: ' . implode(', ', $missing_slots);
    }
    if ($failures) {
        $messages[] = 'validation: ' . implode(', ', $failures);
    }
    echo esc_html(implode(' | ', $messages)) . '</p></div>';
}
