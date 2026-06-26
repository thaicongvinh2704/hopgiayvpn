<?php
/**
 * Creates and maintains the paper box inserts protection guide draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_how_inserts_protect_products_post');
add_action('admin_notices', 'custom_box_how_inserts_protect_products_notice');

function custom_box_sync_how_inserts_protect_products_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_how_inserts_protect_products_post();
}

function custom_box_upsert_how_inserts_protect_products_post()
{
    $data = custom_box_how_inserts_protect_products_map();
    $version = 'how-inserts-protect-products-paper-boxes-20260626-v1';
    $post = get_page_by_path($data['slug'], OBJECT, 'post');

    if (!$post) {
        $matches = get_posts(array(
            'post_type'      => 'post',
            'post_status'    => array('draft', 'pending', 'private', 'publish'),
            'title'          => $data['title'],
            'posts_per_page' => 1,
        ));
        $post = !empty($matches) ? $matches[0] : null;
    }

    if ($post && 'trash' === $post->post_status) {
        update_option('custom_box_how_inserts_protect_products_missing_post', $data['slug'], false);
        return 0;
    }

    if (!$post) {
        $post_id = wp_insert_post(array(
            'post_type'    => 'post',
            'post_status'  => 'draft',
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => custom_box_how_inserts_protect_products_content(),
        ), true);

        if (is_wp_error($post_id)) {
            update_option('custom_box_how_inserts_protect_products_missing_post', $data['slug'], false);
            return $post_id;
        }

        $post = get_post($post_id);
    }

    delete_option('custom_box_how_inserts_protect_products_missing_post');

    if (get_post_meta($post->ID, '_custom_box_how_inserts_protect_products_version', true) !== $version) {
        $update = array(
            'ID'           => $post->ID,
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => custom_box_how_inserts_protect_products_content(),
        );

        if (!in_array($post->post_status, array('publish', 'private'), true)) {
            $update['post_status'] = 'draft';
        }

        $updated = wp_update_post($update, true);
        if (is_wp_error($updated)) {
            return $updated;
        }
    }

    custom_box_how_inserts_protect_products_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_sync = custom_box_how_inserts_protect_products_images($post->ID);
    update_option('custom_box_how_inserts_protect_products_missing_images', $image_sync['missing_images'], false);
    update_option('custom_box_how_inserts_protect_products_missing_slots', $image_sync['missing_slots'], false);

    if (empty($image_sync['missing_images']) && empty($image_sync['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_how_inserts_protect_products_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_how_inserts_protect_products_map()
{
    return array(
        'title'           => 'How Inserts Help Protect Products Inside Paper Boxes',
        'slug'            => 'how-inserts-protect-products-paper-boxes',
        'excerpt'         => 'Learn how paperboard, corrugated, and molded pulp inserts help protect products inside paper boxes by controlling movement, separation, pressure, and presentation.',
        'seo_title'       => 'How Inserts Protect Products Inside Paper Boxes',
        'seo_description' => 'See how paperboard, corrugated, and molded pulp inserts protect products inside paper boxes by improving fit, reducing movement, and supporting safer shipping.',
        'focus_keyword'   => 'how inserts protect products in paper boxes',
        'category'        => array(
            'name' => 'Paper Packaging Guide',
            'slug' => 'paper-packaging-guide',
        ),
        'tags'            => array(
            'paper box inserts',
            'packaging inserts',
            'product protection',
            'paperboard inserts',
            'corrugated inserts',
            'molded pulp inserts',
            'custom paper boxes',
        ),
    );
}

function custom_box_how_inserts_protect_products_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'paper-box-inserts-product-protection-thumbnail',
            'alt'     => 'Paper box inserts protecting products inside custom packaging',
            'title'   => 'Paper Box Inserts Product Protection',
            'caption' => 'Inserts help control movement and support products inside paper boxes.',
        ),
        'slot_1' => array(
            'base'    => 'paperboard-insert-holds-product-inside-box',
            'alt'     => 'Paperboard insert holding a product inside a paper box',
            'title'   => 'Paperboard Insert Holding Product',
            'caption' => 'A paperboard insert keeps a product positioned inside a retail paper box.',
        ),
        'slot_2' => array(
            'base'    => 'paperboard-corrugated-pulp-inserts-comparison',
            'alt'     => 'Comparison of paperboard corrugated and molded pulp inserts',
            'title'   => 'Paper Based Insert Options Comparison',
            'caption' => 'Paperboard, corrugated, and molded pulp inserts protect products in different ways.',
        ),
        'slot_3' => array(
            'base'    => 'corrugated-insert-protects-glass-jar-paper-box',
            'alt'     => 'Corrugated insert protecting a glass jar inside a paper box',
            'title'   => 'Corrugated Insert Protecting Glass Jar',
            'caption' => 'Corrugated inserts add structure and separation for fragile or heavier products.',
        ),
        'slot_4' => array(
            'base'    => 'paper-box-insert-qc-sample-check',
            'alt'     => 'QC check for a paper box insert sample with real product',
            'title'   => 'Paper Box Insert QC Sample Check',
            'caption' => 'Insert samples should be checked with the real product before mass production.',
        ),
    );
}

function custom_box_how_inserts_protect_products_terms($post_id)
{
    $data = custom_box_how_inserts_protect_products_map();
    $category = get_term_by('slug', $data['category']['slug'], 'category');

    if (!$category) {
        $created = wp_insert_term(
            $data['category']['name'],
            'category',
            array('slug' => $data['category']['slug'])
        );

        if (!is_wp_error($created)) {
            $category = get_term((int) $created['term_id'], 'category');
        }
    }

    if ($category && !is_wp_error($category)) {
        wp_set_post_categories($post_id, array((int) $category->term_id), false);
    }

    wp_set_post_tags($post_id, $data['tags'], false);
}

function custom_box_how_inserts_protect_products_images($post_id)
{
    $post = get_post($post_id);

    if (!$post) {
        return array(
            'missing_images' => array(),
            'missing_slots'  => array(),
        );
    }

    $content = $post->post_content;
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_how_inserts_protect_products_image_map() as $key => $image) {
        $attachment_id = custom_box_find_how_inserts_protect_products_attachment($image['base']);

        if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
            $missing_images[] = $image['base'];
            continue;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
        wp_update_post(array(
            'ID'           => $attachment_id,
            'post_parent'  => $post_id,
            'post_title'   => $image['title'],
            'post_excerpt' => $image['caption'],
        ));

        if ('featured' === $key) {
            set_post_thumbnail($post_id, $attachment_id);
            continue;
        }

        $marker = '<!-- vpn-paper-box-inserts-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_how_inserts_protect_products_figure($attachment_id, $image);
        $slot_number = substr($key, -1);
        $slot = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
        $slot_pattern = '/<span\b[^>]*>\s*<!--\s*IMAGE_SLOT_' . preg_quote($slot_number, '/') . '\s*-->\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

        if (false !== strpos($content, $marker)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
        } elseif (preg_match($slot_pattern, $content)) {
            $content = preg_replace($slot_pattern, $figure, $content, 1);
        } elseif (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
        } else {
            $missing_slots[] = 'IMAGE_SLOT_' . $slot_number;
        }
    }

    if ($content !== $post->post_content) {
        wp_update_post(array(
            'ID'           => $post_id,
            'post_content' => $content,
        ));
    }

    return array(
        'missing_images' => $missing_images,
        'missing_slots'  => $missing_slots,
    );
}

function custom_box_find_how_inserts_protect_products_attachment($base)
{
    $attachment = get_page_by_path(sanitize_title($base), OBJECT, 'attachment');
    if ($attachment) {
        return (int) $attachment->ID;
    }

    $attachments = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'     => '_wp_attached_file',
                'value'   => '/' . $base . '.',
                'compare' => 'LIKE',
            ),
        ),
    ));

    return !empty($attachments) ? (int) $attachments[0]->ID : 0;
}

function custom_box_how_inserts_protect_products_figure($attachment_id, $image)
{
    $url = wp_get_attachment_url($attachment_id);

    if (!$url) {
        return '';
    }

    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url($url),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_how_inserts_protect_products_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = get_option('custom_box_how_inserts_protect_products_missing_post', '');
    $missing_images = get_option('custom_box_how_inserts_protect_products_missing_images', array());
    $missing_slots = get_option('custom_box_how_inserts_protect_products_missing_slots', array());
    $messages = array();

    if (!empty($missing_post)) {
        $messages[] = sprintf(
            /* translators: %s: post slug */
            esc_html__('Paper box inserts post sync could not create or update the draft for slug: %s', 'custom-box-theme'),
            esc_html($missing_post)
        );
    }

    if (!empty($missing_images) && is_array($missing_images)) {
        $messages[] = esc_html__('Paper box inserts post sync is waiting for these Media Library files:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_images));
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        $messages[] = esc_html__('Paper box inserts post sync could not find these content slots:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_slots));
    }

    if (empty($messages)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo implode('<br>', $messages);
    echo '</p></div>';
}

function custom_box_how_inserts_protect_products_content()
{
    $content_file = __DIR__ . '/post-content/how-inserts-protect-products-paper-boxes.html';

    if (!is_readable($content_file)) {
        return '';
    }

    return (string) file_get_contents($content_file);
}
