<?php
/**
 * Deploys the cardboard box weight estimation guide and images.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_SYNC_VERSION = '2026-07-27-v1';
const CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_VERSION_OPTION = 'custom_box_cardboard_box_weight_sync_version';
const CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_NOTICE_OPTION = 'custom_box_cardboard_box_weight_sync_notice';

add_action('admin_init', 'custom_box_sync_cardboard_box_weight_post');
add_action('admin_notices', 'custom_box_cardboard_box_weight_admin_notice');

function custom_box_cardboard_box_weight_post_data(): array
{
    return array(
        'title' => 'How Much Does a Cardboard Box Weigh? A Practical Estimation Guide',
        'slug' => 'how-much-does-a-cardboard-box-weigh',
        'excerpt' => 'Estimate the weight of an empty cardboard box using practical size ranges, blank area and corrugated board GSM—without confusing actual weight with dimensional weight.',
        'category' => array('name' => 'Packaging Guides', 'slug' => 'packaging-guides'),
        'tags' => array(
            'Cardboard Box Weight' => 'cardboard-box-weight',
            'Corrugated Boxes' => 'corrugated-boxes',
            'Shipping Packaging' => 'shipping-packaging',
            'Packaging Specifications' => 'packaging-specifications',
            'Mailer Boxes' => 'mailer-boxes',
        ),
        'seo_title' => 'How Much Does a Cardboard Box Weigh? Practical Guide',
        'seo_description' => 'See typical cardboard box weights, learn the correct blank-area × GSM formula, and avoid confusing empty box weight with dimensional shipping weight.',
        'focus_keyword' => 'how much does a cardboard box weigh',
    );
}

function custom_box_cardboard_box_weight_images(): array
{
    return array(
        'featured' => array(
            'base' => 'cardboard-box-weight-estimation-guide',
            'alt' => 'Empty corrugated boxes on a scale for cardboard box weight estimation',
            'title' => 'Cardboard Box Weight Estimation Guide',
            'caption' => 'A finished corrugated box on a digital scale provides the most reliable empty unit-weight check.',
        ),
        'slot_1' => array(
            'base' => 'empty-cardboard-box-weight-by-size',
            'alt' => 'Small, medium, and large empty corrugated boxes compared by estimated weight',
            'title' => 'Empty Cardboard Box Weight by Size',
            'caption' => 'Indicative empty-box weight ranges vary with box size, style, flute, and combined board grammage.',
        ),
        'slot_2' => array(
            'base' => 'actual-weight-vs-dimensional-weight-box',
            'alt' => 'Actual empty box weight compared with dimensional shipping weight for a corrugated box',
            'title' => 'Actual Weight vs Dimensional Weight',
            'caption' => 'Dimensional weight prices shipping volume; it does not measure the physical weight of an empty corrugated box.',
        ),
        'slot_3' => array(
            'base' => 'corrugated-box-blank-area-gsm-calculation',
            'alt' => 'Corrugated box dieline showing blank area multiplied by combined board GSM',
            'title' => 'Corrugated Box Blank Area and GSM Calculation',
            'caption' => 'Estimate empty corrugated box weight from the net blank area and the confirmed combined board grammage.',
        ),
        'slot_4' => array(
            'base' => 'finished-mailer-box-weight-qc-check',
            'alt' => 'Finished corrugated mailer box and insert weighed on a digital scale for quality control',
            'title' => 'Finished Mailer Box Weight QC Check',
            'caption' => 'Confirm calculated unit weight by weighing production-representative boxes with every specified component included.',
        ),
    );
}

function custom_box_cardboard_box_weight_content(): string
{
    $path = __DIR__ . '/post-content/how-much-does-a-cardboard-box-weigh.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    return is_string($content) ? $content : '';
}

function custom_box_find_cardboard_box_weight_post(string $slug, string $title): ?WP_Post
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

function custom_box_sync_cardboard_box_weight_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_cardboard_box_weight_post_data();
    $post = custom_box_find_cardboard_box_weight_post($data['slug'], $data['title']);
    if (
        CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_SYNC_VERSION === get_option(CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_VERSION_OPTION)
        && $post
        && custom_box_cardboard_box_weight_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_cardboard_box_weight_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_VERSION_OPTION);
        update_option(CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_cardboard_box_weight_is_complete((int) $post_id)) {
        update_option(CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_VERSION_OPTION, CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_SYNC_VERSION, false);
        update_option(CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Cardboard box weight draft synced: post ID %d, featured image %d, 4 inline figures, category Packaging Guides, 5 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_VERSION_OPTION);
    update_option(CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Cardboard box weight sync is incomplete. Missing images: '
            . implode(', ', (array) get_option('custom_box_cardboard_box_weight_missing_images', array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option('custom_box_cardboard_box_weight_missing_slots', array()),
                (array) get_option('custom_box_cardboard_box_weight_validation_failures', array())
            )),
    ), false);
}

function custom_box_upsert_cardboard_box_weight_post()
{
    $data = custom_box_cardboard_box_weight_post_data();
    $post = custom_box_find_cardboard_box_weight_post($data['slug'], $data['title']);
    $content = custom_box_cardboard_box_weight_content();
    if ('' === trim($content)) {
        return new WP_Error('cardboard_box_weight_content_missing', 'The canonical cardboard box weight content bundle is missing.');
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
    custom_box_sync_cardboard_box_weight_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_cardboard_box_weight_images($post_id);

    return $post_id;
}

function custom_box_sync_cardboard_box_weight_terms(int $post_id, array $data): void
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

function custom_box_sync_cardboard_box_weight_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_cardboard_box_weight_images() as $key => $image) {
        $attachment_id = custom_box_find_cardboard_box_weight_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_cardboard_box_weight_attachment($image['base'], $post_id, $image);
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

        $marker = '<!-- cardboard-box-weight-image:' . $key . ' -->';
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

    update_option('custom_box_cardboard_box_weight_missing_images', array_values(array_unique($missing_images)), false);
    update_option('custom_box_cardboard_box_weight_missing_slots', array_values(array_unique($missing_slots)), false);
}

function custom_box_find_cardboard_box_weight_attachment(string $base): int
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

function custom_box_create_cardboard_box_weight_attachment(string $base, int $post_id, array $image): int
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

function custom_box_cardboard_box_weight_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_cardboard_box_weight_post_data();
    $images = custom_box_cardboard_box_weight_images();
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
        $attachment_id = custom_box_find_cardboard_box_weight_attachment($image['base']);
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
        4 !== substr_count($content, '<!-- cardboard-box-weight-image:')
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
    if ((array) get_option('custom_box_cardboard_box_weight_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_cardboard_box_weight_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option('custom_box_cardboard_box_weight_validation_failures', array_values(array_unique($failures)), false);

    return !$failures;
}

function custom_box_cardboard_box_weight_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_CARDBOARD_BOX_WEIGHT_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success']) ? 'notice notice-success is-dismissible' : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}
