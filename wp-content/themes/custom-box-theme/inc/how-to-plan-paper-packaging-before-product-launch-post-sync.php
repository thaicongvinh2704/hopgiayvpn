<?php
/**
 * Creates and maintains the pre-launch paper packaging planning blog draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_packaging_before_launch_post');
add_action('admin_notices', 'custom_box_paper_packaging_before_launch_notice');

function custom_box_sync_paper_packaging_before_launch_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_paper_packaging_before_launch_post();
}

function custom_box_upsert_paper_packaging_before_launch_post()
{
    $data = custom_box_paper_packaging_before_launch_map();
    $version = 'paper-packaging-before-product-launch-20260702-v1';
    $post = custom_box_find_paper_packaging_before_launch_post($data);

    if (!$post) {
        $post_id = wp_insert_post(array(
            'post_type'    => 'post',
            'post_status'  => 'draft',
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => custom_box_paper_packaging_before_launch_post_content(),
        ), true);

        if (is_wp_error($post_id)) {
            update_option('custom_box_paper_packaging_before_launch_missing_post', $data['slug'], false);
            return $post_id;
        }

        $post = get_post($post_id);
    }

    if (!$post || 'trash' === $post->post_status) {
        update_option('custom_box_paper_packaging_before_launch_missing_post', $data['slug'], false);
        return 0;
    }

    delete_option('custom_box_paper_packaging_before_launch_missing_post');

    $update = array(
        'ID'           => $post->ID,
        'post_title'   => $data['title'],
        'post_name'    => $data['slug'],
        'post_excerpt' => $data['excerpt'],
    );

    if (get_post_meta($post->ID, '_custom_box_paper_packaging_before_launch_version', true) !== $version) {
        $update['post_content'] = custom_box_paper_packaging_before_launch_post_content();
    }

    if (!in_array($post->post_status, array('publish', 'private'), true)) {
        $update['post_status'] = 'draft';
    }

    $updated = wp_update_post($update, true);

    if (is_wp_error($updated)) {
        return $updated;
    }

    custom_box_paper_packaging_before_launch_terms($post->ID);

    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_result = custom_box_paper_packaging_before_launch_images($post->ID);
    update_option('custom_box_paper_packaging_before_launch_missing_images', $image_result['missing_images'], false);
    update_option('custom_box_paper_packaging_before_launch_missing_slots', $image_result['missing_slots'], false);

    if (empty($image_result['missing_images']) && empty($image_result['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_paper_packaging_before_launch_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_find_paper_packaging_before_launch_post($data)
{
    global $wpdb;

    $post_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'post'
             AND post_name = %s
             AND post_status IN ('draft', 'pending', 'private', 'publish')
             ORDER BY ID DESC LIMIT 1",
            $data['slug']
        )
    );

    if ($post_id) {
        return get_post($post_id);
    }

    $matches = get_posts(array(
        'post_type'      => 'post',
        'post_status'    => array('draft', 'pending', 'private', 'publish'),
        'title'          => $data['title'],
        'posts_per_page' => 1,
    ));

    return !empty($matches) ? $matches[0] : null;
}

function custom_box_paper_packaging_before_launch_map()
{
    return array(
        'title'           => 'How to Plan Paper Packaging Before a Product Launch',
        'slug'            => 'how-to-plan-paper-packaging-before-product-launch',
        'excerpt'         => 'Learn how to plan paper packaging before a product launch, from product specs and box structure to dieline, artwork, samples, QC, shipping, and supplier quotation details.',
        'seo_title'       => 'How to Plan Paper Packaging Before Product Launch',
        'seo_description' => 'Plan paper packaging before launch with a practical checklist for box structure, dieline, artwork, samples, QC, shipping, and supplier quotation details.',
        'focus_keyword'   => 'how to plan packaging before product launch',
        'category'        => array(
            'name' => 'Packaging Guide',
            'slug' => 'packaging-guide',
        ),
        'tags'            => array(
            'Product Launch Packaging',
            'Paper Packaging',
            'Custom Paper Boxes',
            'Packaging Planning',
            'Dieline',
            'Packaging Sample',
            'Packaging Manufacturer',
        ),
    );
}

function custom_box_paper_packaging_before_launch_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'paper-packaging-before-product-launch-thumbnail',
            'alt'     => 'Paper packaging planning materials before a product launch',
            'title'   => 'Paper Packaging Planning Before Product Launch',
            'caption' => 'Packaging planning starts with product specs, structure, dieline, material, sample, and shipping requirements.',
        ),
        'slot_1' => array(
            'base'    => 'product-specs-paper-packaging-planning',
            'alt'     => 'Product specifications used to plan custom paper packaging',
            'title'   => 'Product Specs for Paper Packaging Planning',
            'caption' => 'Product size, weight, fragility, and selling channel should guide the box structure before launch.',
        ),
        'slot_2' => array(
            'base'    => 'paper-box-structure-launch-comparison',
            'alt'     => 'Comparison of paper box structures for product launch planning',
            'title'   => 'Paper Box Structure Comparison for Launch',
            'caption' => 'Folding cartons, rigid boxes, and corrugated mailers serve different launch and shipping needs.',
        ),
        'slot_3' => array(
            'base'    => 'dieline-artwork-sample-check-before-launch',
            'alt'     => 'Dieline artwork and sample check for paper packaging before launch',
            'title'   => 'Dieline and Sample Check Before Launch',
            'caption' => 'Production-ready artwork and physical samples reduce packaging risk before bulk production.',
        ),
        'slot_4' => array(
            'base'    => 'pre-launch-packaging-qc-checklist',
            'alt'     => 'QC checklist for paper packaging before product launch',
            'title'   => 'Pre-Launch Packaging QC Checklist',
            'caption' => 'QC review should cover structure, print, finishing, insert fit, and export carton packing.',
        ),
    );
}

function custom_box_paper_packaging_before_launch_terms($post_id)
{
    $data = custom_box_paper_packaging_before_launch_map();
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

function custom_box_paper_packaging_before_launch_images($post_id)
{
    $post = get_post($post_id);

    if (!$post) {
        return array(
            'missing_images' => array(),
            'missing_slots'  => array('draft post'),
        );
    }

    $content = (string) $post->post_content;
    $missing_images = array();
    $missing_slots = array();

    foreach (custom_box_paper_packaging_before_launch_image_map() as $key => $image) {
        $attachment_id = custom_box_paper_packaging_before_launch_attachment($image['base']);

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

        $slot_number = substr($key, -1);
        $marker = '<!-- vpn-paper-packaging-before-launch-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_paper_packaging_before_launch_figure($attachment_id, $image);
        $inserted = custom_box_paper_packaging_before_launch_insert_figure($content, $figure, $marker, $slot_number);

        $content = $inserted['content'];

        if (!$inserted['found']) {
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

function custom_box_paper_packaging_before_launch_insert_figure($content, $figure, $marker, $slot_number)
{
    $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

    if (false !== strpos($content, $marker)) {
        $updated = preg_replace($marker_pattern, $figure, $content, 1, $count);

        return array(
            'content' => $count ? $updated : $content,
            'found'   => true,
        );
    }

    $slot_pattern = '/<span\b[^>]*>\s*<!--\s*IMAGE_SLOT_' . preg_quote($slot_number, '/') . '\s*-->\s*<\/span>/i';
    $updated = preg_replace($slot_pattern, $figure, $content, 1, $count);

    if ($count) {
        return array(
            'content' => $updated,
            'found'   => true,
        );
    }

    $raw_slot_pattern = '/<!--\s*IMAGE_SLOT_' . preg_quote($slot_number, '/') . '\s*-->/i';
    $updated = preg_replace($raw_slot_pattern, $figure, $content, 1, $count);

    return array(
        'content' => $count ? $updated : $content,
        'found'   => (bool) $count,
    );
}

function custom_box_paper_packaging_before_launch_attachment($base)
{
    $attachment = get_page_by_path(sanitize_title($base), OBJECT, 'attachment');

    if ($attachment) {
        return (int) $attachment->ID;
    }

    global $wpdb;

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_wp_attached_file'
             AND meta_value LIKE %s
             ORDER BY post_id DESC LIMIT 1",
            '%/' . $wpdb->esc_like($base) . '.%'
        )
    );
}

function custom_box_paper_packaging_before_launch_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt'])
    );
}

function custom_box_paper_packaging_before_launch_post_content()
{
    $content_file = __DIR__ . '/post-content/how-to-plan-paper-packaging-before-product-launch.html';

    if (!is_readable($content_file)) {
        return '';
    }

    return (string) file_get_contents($content_file);
}

function custom_box_paper_packaging_before_launch_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = get_option('custom_box_paper_packaging_before_launch_missing_post', '');
    $missing_images = get_option('custom_box_paper_packaging_before_launch_missing_images', array());
    $missing_slots = get_option('custom_box_paper_packaging_before_launch_missing_slots', array());
    $messages = array();

    if (!empty($missing_post)) {
        $messages[] = sprintf(
            /* translators: %s: post slug */
            esc_html__('Paper packaging before product launch sync could not create or find the draft for slug: %s', 'custom-box-theme'),
            esc_html($missing_post)
        );
    }

    if (!empty($missing_images) && is_array($missing_images)) {
        $messages[] = esc_html__('Paper packaging before product launch sync is waiting for these Media Library files:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_images));
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        $messages[] = esc_html__('Paper packaging before product launch sync could not find these content slots:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_slots));
    }

    if (empty($messages)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo implode('<br>', $messages);
    echo '</p></div>';
}
