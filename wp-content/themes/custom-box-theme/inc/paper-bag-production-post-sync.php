<?php
/**
 * Deploys the paper bag production draft and its image set.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_PAPER_BAG_PRODUCTION_SYNC_VERSION = '2026-07-30-v1';
const CUSTOM_BOX_PAPER_BAG_PRODUCTION_VERSION_OPTION = 'custom_box_paper_bag_production_sync_version';
const CUSTOM_BOX_PAPER_BAG_PRODUCTION_NOTICE_OPTION = 'custom_box_paper_bag_production_sync_notice';

add_action('admin_init', 'custom_box_sync_paper_bag_production_post');
add_action('admin_notices', 'custom_box_paper_bag_production_admin_notice');

function custom_box_paper_bag_production_post_data(): array
{
    return array(
        'title' => 'How to Produce Paper Bags: From Kraft Roll to Finished Bag',
        'slug' => 'how-to-produce-paper-bags',
        'excerpt' => 'Learn how paper bags are produced from kraft rolls or printed sheets, including printing, tube forming, bottom gluing, handles, material selection and quality control.',
        'category' => array(
            'name' => 'Packaging Guides',
            'slug' => 'packaging-guides',
        ),
        'tags' => array(
            'Paper Bags' => 'paper-bags',
            'Manufacturing Process' => 'manufacturing-process',
            'Kraft Paper' => 'kraft-paper',
            'Custom Packaging' => 'custom-packaging',
            'Printing' => 'printing',
            'Quality Control' => 'quality-control',
        ),
        'seo_title' => 'How to Produce Paper Bags: Factory Process Guide',
        'seo_description' => 'Learn how paper bags are produced from kraft rolls or printed sheets, including material, printing, forming, handles, quality control and buyer specs.',
        'focus_keyword' => 'how to produce paper bags',
    );
}

function custom_box_paper_bag_production_images(): array
{
    return array(
        'featured' => array(
            'base' => 'how-to-produce-paper-bags-process',
            'alt' => 'Kraft paper roll and finished shopping bag showing how paper bags are produced',
            'title' => 'How to Produce Paper Bags',
            'caption' => 'Commercial paper bag production begins with an approved kraft roll or printed sheet and ends with forming, handles, quality control and packing.',
        ),
        'slot_1' => array(
            'base' => 'roll-fed-vs-sheet-fed-paper-bag-production',
            'alt' => 'Roll-fed kraft paper bag production compared with sheet-fed premium paper bag production',
            'title' => 'Roll-Fed vs Sheet-Fed Paper Bag Production',
            'caption' => 'Roll-fed lines prioritize repeatable volume, while sheet-fed production supports detailed printing, finishing and reinforcement.',
        ),
        'slot_2' => array(
            'base' => 'roll-fed-kraft-paper-bag-forming-process',
            'alt' => 'Roll-fed kraft paper web forming a tube gussets bottom and finished paper bag',
            'title' => 'Roll-Fed Kraft Paper Bag Forming Process',
            'caption' => 'A roll-fed line controls web tension, printing, tube forming, cutting, bottom gluing and collection.',
        ),
        'slot_3' => array(
            'base' => 'sheet-fed-premium-paper-bag-assembly',
            'alt' => 'Printed paper bag sheets being die-cut folded glued and assembled with rope handles',
            'title' => 'Sheet-Fed Premium Paper Bag Assembly',
            'caption' => 'Premium paper bags are printed and finished as sheets before die-cutting, folding, reinforcement and handle installation.',
        ),
        'slot_4' => array(
            'base' => 'paper-types-gsm-for-paper-bag-production',
            'alt' => 'Kraft recycled coated and grease-resistant papers compared by GSM for paper bag production',
            'title' => 'Paper Types and GSM for Paper Bag Production',
            'caption' => 'Paper type and GSM are starting points that must be verified against bag dimensions, handles, bottom structure and product load.',
        ),
        'slot_5' => array(
            'base' => 'paper-bag-handle-bottom-quality-control',
            'alt' => 'Paper bag quality inspection checking handles reinforcement seams and bottom gluing',
            'title' => 'Paper Bag Handle and Bottom Quality Control',
            'caption' => 'Quality controls should inspect the handle, reinforcement, side seam and bottom where carrying failures begin.',
        ),
    );
}

function custom_box_paper_bag_production_content(): string
{
    $path = __DIR__ . '/post-content/how-to-produce-paper-bags.html';
    $content = is_readable($path) ? file_get_contents($path) : false;

    return is_string($content) ? $content : '';
}

function custom_box_find_paper_bag_production_post(string $slug, string $title): ?WP_Post
{
    $post = get_page_by_path($slug, OBJECT, 'post');
    if ($post && 'trash' !== $post->post_status) {
        return $post;
    }

    global $wpdb;
    $post_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type = 'post' AND post_status <> 'trash' AND post_title = %s
         ORDER BY ID DESC LIMIT 1",
        $title
    ));

    return $post_id ? get_post($post_id) : null;
}

function custom_box_sync_paper_bag_production_post(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $data = custom_box_paper_bag_production_post_data();
    $post = custom_box_find_paper_bag_production_post($data['slug'], $data['title']);
    if (
        CUSTOM_BOX_PAPER_BAG_PRODUCTION_SYNC_VERSION
            === get_option(CUSTOM_BOX_PAPER_BAG_PRODUCTION_VERSION_OPTION)
        && $post
        && custom_box_paper_bag_production_is_complete((int) $post->ID)
    ) {
        return;
    }

    $post_id = custom_box_upsert_paper_bag_production_post();
    if (is_wp_error($post_id)) {
        delete_option(CUSTOM_BOX_PAPER_BAG_PRODUCTION_VERSION_OPTION);
        update_option(CUSTOM_BOX_PAPER_BAG_PRODUCTION_NOTICE_OPTION, array(
            'success' => false,
            'message' => $post_id->get_error_message(),
        ), false);
        return;
    }

    if (custom_box_paper_bag_production_is_complete((int) $post_id)) {
        update_option(
            CUSTOM_BOX_PAPER_BAG_PRODUCTION_VERSION_OPTION,
            CUSTOM_BOX_PAPER_BAG_PRODUCTION_SYNC_VERSION,
            false
        );
        update_option(CUSTOM_BOX_PAPER_BAG_PRODUCTION_NOTICE_OPTION, array(
            'success' => true,
            'message' => sprintf(
                'Paper bag production draft synced: post ID %d, featured image %d, 5 inline figures, category Packaging Guides, 6 tags, and Rank Math fields verified.',
                (int) $post_id,
                (int) get_post_thumbnail_id((int) $post_id)
            ),
        ), false);
        return;
    }

    delete_option(CUSTOM_BOX_PAPER_BAG_PRODUCTION_VERSION_OPTION);
    update_option(CUSTOM_BOX_PAPER_BAG_PRODUCTION_NOTICE_OPTION, array(
        'success' => false,
        'message' => 'Paper bag production sync is incomplete. Missing images: '
            . implode(', ', (array) get_option('custom_box_paper_bag_production_missing_images', array()))
            . '; missing slots or validation failures: '
            . implode(', ', array_merge(
                (array) get_option('custom_box_paper_bag_production_missing_slots', array()),
                (array) get_option('custom_box_paper_bag_production_validation_failures', array())
            )),
    ), false);
}

function custom_box_upsert_paper_bag_production_post()
{
    $data = custom_box_paper_bag_production_post_data();
    $post = custom_box_find_paper_bag_production_post($data['slug'], $data['title']);
    $content = custom_box_paper_bag_production_content();
    if ('' === trim($content)) {
        return new WP_Error(
            'paper_bag_production_content_missing',
            'The canonical paper bag production content bundle is missing.'
        );
    }

    $payload = array(
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_type' => 'post',
        'post_excerpt' => $data['excerpt'],
    );

    if ($post) {
        $payload['ID'] = (int) $post->ID;
        $payload['post_status'] = in_array($post->post_status, array('publish', 'private'), true)
            ? $post->post_status
            : 'draft';
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
    custom_box_sync_paper_bag_production_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    custom_box_sync_paper_bag_production_images($post_id);

    return $post_id;
}

function custom_box_sync_paper_bag_production_terms(int $post_id, array $data): void
{
    $category = get_term_by('slug', $data['category']['slug'], 'category');
    if (!$category || is_wp_error($category)) {
        $created = wp_insert_term($data['category']['name'], 'category', array(
            'slug' => $data['category']['slug'],
        ));
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

function custom_box_sync_paper_bag_production_images(int $post_id): void
{
    $post = get_post($post_id);
    $content = $post ? (string) $post->post_content : '';
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_paper_bag_production_images() as $key => $image) {
        $attachment_id = custom_box_find_paper_bag_production_attachment($image['base']);
        if (!$attachment_id) {
            $attachment_id = custom_box_create_paper_bag_production_attachment(
                $image['base'],
                $post_id,
                $image
            );
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

        $marker = '<!-- paper-bag-production-image:' . $key . ' -->';
        $figure = $marker
            . "\n<figure><img src=\"" . esc_url($url)
            . '" alt="' . esc_attr($image['alt'])
            . '" style="width:100%; height:auto;" loading="lazy" decoding="async"><figcaption>'
            . esc_html($image['caption'])
            . '</figcaption></figure>';
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
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $content,
        ));
    }

    update_option(
        'custom_box_paper_bag_production_missing_images',
        array_values(array_unique($missing_images)),
        false
    );
    update_option(
        'custom_box_paper_bag_production_missing_slots',
        array_values(array_unique($missing_slots)),
        false
    );
}

function custom_box_find_paper_bag_production_attachment(string $base): int
{
    global $wpdb;
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s
         ORDER BY post_id DESC",
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

function custom_box_create_paper_bag_production_attachment(
    string $base,
    int $post_id,
    array $image
): int {
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }

    foreach (array('webp', 'png', 'jpg', 'jpeg') as $extension) {
        $candidate_relative = '2026/07/' . $base . '.' . $extension;
        $upload_path = trailingslashit($uploads['basedir']) . $candidate_relative;
        $bundle_path = get_template_directory()
            . '/inc/product-sample-deploy-assets/uploads/'
            . $candidate_relative;

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

function custom_box_paper_bag_production_is_complete(int $post_id): bool
{
    $post = get_post($post_id);
    $data = custom_box_paper_bag_production_post_data();
    $images = custom_box_paper_bag_production_images();
    $failures = array();

    if (
        !$post
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
    if (
        !$featured_id
        || $images['featured']['base'] !== pathinfo(wp_basename($featured_file), PATHINFO_FILENAME)
    ) {
        $failures[] = 'featured image';
    }

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_find_paper_bag_production_attachment($image['base']);
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
        5 !== substr_count($content, '<!-- paper-bag-production-image:')
        || 5 !== substr_count($content, '<figure>')
        || 5 !== substr_count($content, '<img ')
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
    foreach (array(
        'https://hopgiayvpn.com/custom-packaging-boxes-manufacturer/',
        'https://hopgiayvpn.com/products/paper-bags-with-logo/',
        'https://hopgiayvpn.com/how-to-make-paper-bags-stronger/',
    ) as $required_link) {
        if (false === strpos($content, $required_link)) {
            $failures[] = 'internal link: ' . $required_link;
        }
    }

    $categories = wp_get_post_terms($post_id, 'category', array('fields' => 'slugs'));
    if (
        is_wp_error($categories)
        || array($data['category']['slug']) !== array_values($categories)
    ) {
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

    if (
        $data['seo_title'] !== get_post_meta($post_id, 'rank_math_title', true)
        || $data['seo_description'] !== get_post_meta($post_id, 'rank_math_description', true)
        || $data['focus_keyword'] !== get_post_meta($post_id, 'rank_math_focus_keyword', true)
    ) {
        $failures[] = 'Rank Math metadata';
    }
    if ((array) get_option('custom_box_paper_bag_production_missing_images', array())) {
        $failures[] = 'missing images';
    }
    if ((array) get_option('custom_box_paper_bag_production_missing_slots', array())) {
        $failures[] = 'missing slots';
    }

    update_option(
        'custom_box_paper_bag_production_validation_failures',
        array_values(array_unique($failures)),
        false
    );

    return !$failures;
}

function custom_box_paper_bag_production_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_option(CUSTOM_BOX_PAPER_BAG_PRODUCTION_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['message'])) {
        return;
    }

    $class = !empty($notice['success'])
        ? 'notice notice-success is-dismissible'
        : 'notice notice-warning';
    echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($notice['message']) . '</p></div>';
}
