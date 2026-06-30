<?php
/**
 * Syncs metadata and images for the printed packaging sample check draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_printed_packaging_sample_check_post');
add_action('admin_notices', 'custom_box_printed_packaging_sample_check_notice');

function custom_box_sync_printed_packaging_sample_check_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_printed_packaging_sample_check_post();
}

function custom_box_upsert_printed_packaging_sample_check_post()
{
    $data = custom_box_printed_packaging_sample_check_map();
    $version = 'printed-packaging-sample-check-20260630-v1';
    $post = custom_box_find_printed_packaging_sample_check_post($data);

    if (!$post || 'trash' === $post->post_status) {
        update_option('custom_box_printed_packaging_sample_check_missing_post', $data['slug'], false);
        return 0;
    }

    delete_option('custom_box_printed_packaging_sample_check_missing_post');

    $update = array(
        'ID'           => $post->ID,
        'post_title'   => $data['title'],
        'post_name'    => $data['slug'],
        'post_excerpt' => $data['excerpt'],
    );

    if (!in_array($post->post_status, array('publish', 'private'), true)) {
        $update['post_status'] = 'draft';
    }

    $updated = wp_update_post($update, true);
    if (is_wp_error($updated)) {
        return $updated;
    }

    custom_box_printed_packaging_sample_check_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_result = custom_box_printed_packaging_sample_check_images($post->ID);
    update_option('custom_box_printed_packaging_sample_check_missing_images', $image_result['missing_images'], false);
    update_option('custom_box_printed_packaging_sample_check_missing_slots', $image_result['missing_slots'], false);

    if (empty($image_result['missing_images']) && empty($image_result['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_printed_packaging_sample_check_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_find_printed_packaging_sample_check_post($data)
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

function custom_box_printed_packaging_sample_check_map()
{
    return array(
        'title'           => 'How to Check Printed Packaging Samples Before Mass Production',
        'slug'            => 'how-to-check-printed-packaging-samples-before-mass-production',
        'excerpt'         => 'Learn how to check printed packaging samples before mass production, including artwork, color, dieline, structure, finishing, surface defects, product fit, and sample approval notes.',
        'seo_title'       => 'How to Check Printed Packaging Samples Before Production',
        'seo_description' => 'Learn how to check printed packaging samples before mass production, including color, dieline, structure, finishing, defects, and approval notes.',
        'focus_keyword'   => 'how to check printed packaging samples',
        'category'        => array(
            'name' => 'Packaging Quality Control',
            'slug' => 'packaging-quality-control',
        ),
        'tags'            => array(
            'Printed Packaging',
            'Packaging Samples',
            'Sample Approval',
            'Packaging QC',
            'Custom Printed Boxes',
            'Mass Production',
        ),
    );
}

function custom_box_printed_packaging_sample_check_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'printed-packaging-sample-check-thumbnail',
            'alt'     => 'Printed packaging sample inspection before mass production',
            'title'   => 'Printed Packaging Sample Check Before Production',
            'caption' => 'A printed packaging sample review helps buyers approve artwork, color, structure, and finishing before mass production.',
        ),
        'slot_1' => array(
            'base'    => 'packaging-sample-dieline-artwork-check',
            'alt'     => 'Packaging sample checked against dieline and artwork',
            'title'   => 'Packaging Sample Dieline and Artwork Check',
            'caption' => 'Buyers should compare the physical sample with the approved artwork and dieline before production approval.',
            'heading' => 'What a Printed Packaging Sample Should Prove',
        ),
        'slot_2' => array(
            'base'    => 'printed-packaging-color-registration-check',
            'alt'     => 'Printed packaging color and registration inspection',
            'title'   => 'Printed Packaging Color and Registration Check',
            'caption' => 'Color, registration, and print sharpness should be checked against the approved reference, not only by screen view.',
            'heading' => 'Check Print Color Under Realistic Conditions',
        ),
        'slot_3' => array(
            'base'    => 'packaging-finishing-surface-defects-qc',
            'alt'     => 'Packaging finishing and surface defect inspection',
            'title'   => 'Packaging Finishing Surface Defects QC',
            'caption' => 'Finishing details such as lamination, foil, embossing, and spot UV need close inspection before approval.',
            'heading' => 'Review Finishing Details Before Approval',
        ),
        'slot_4' => array(
            'base'    => 'printed-packaging-sample-approval-checklist',
            'alt'     => 'Printed packaging sample approval checklist for buyers',
            'title'   => 'Printed Packaging Sample Approval Checklist',
            'caption' => 'A clear approval checklist helps sourcing teams communicate sample corrections before mass production.',
            'heading' => 'Use a Practical Sample Approval Checklist',
        ),
    );
}

function custom_box_printed_packaging_sample_check_terms($post_id)
{
    $data = custom_box_printed_packaging_sample_check_map();
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

function custom_box_printed_packaging_sample_check_images($post_id)
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

    foreach (custom_box_printed_packaging_sample_check_image_map() as $key => $image) {
        $attachment_id = custom_box_printed_packaging_sample_check_attachment($image['base']);

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
        $marker = '<!-- vpn-printed-packaging-sample-check-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_printed_packaging_sample_check_figure($attachment_id, $image);
        $inserted = custom_box_printed_packaging_sample_check_insert_figure($content, $figure, $marker, $slot_number, $image['heading']);

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

function custom_box_printed_packaging_sample_check_insert_figure($content, $figure, $marker, $slot_number, $heading)
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

    if ($count) {
        return array(
            'content' => $updated,
            'found'   => true,
        );
    }

    $heading_pattern = '/(<h2\b[^>]*>.*?' . preg_quote($heading, '/') . '.*?<\/h2>)/is';
    $updated = preg_replace($heading_pattern, $figure . "\n" . '$1', $content, 1, $count);

    return array(
        'content' => $count ? $updated : $content,
        'found'   => (bool) $count,
    );
}

function custom_box_printed_packaging_sample_check_attachment($base)
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

function custom_box_printed_packaging_sample_check_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt'])
    );
}

function custom_box_printed_packaging_sample_check_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = get_option('custom_box_printed_packaging_sample_check_missing_post', '');
    $missing_images = get_option('custom_box_printed_packaging_sample_check_missing_images', array());
    $missing_slots = get_option('custom_box_printed_packaging_sample_check_missing_slots', array());
    $messages = array();

    if (!empty($missing_post)) {
        $messages[] = sprintf(
            /* translators: %s: post slug */
            esc_html__('Printed packaging sample check sync could not find the draft for slug: %s', 'custom-box-theme'),
            esc_html($missing_post)
        );
    }

    if (!empty($missing_images) && is_array($missing_images)) {
        $messages[] = esc_html__('Printed packaging sample check sync is waiting for these Media Library files:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_images));
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        $messages[] = esc_html__('Printed packaging sample check sync could not find these content slots:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_slots));
    }

    if (empty($messages)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo implode('<br>', $messages);
    echo '</p></div>';
}
