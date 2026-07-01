<?php
/**
 * Creates and maintains the paper packaging lead time blog draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_packaging_lead_time_post');
add_action('admin_notices', 'custom_box_paper_packaging_lead_time_notice');

function custom_box_sync_paper_packaging_lead_time_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_paper_packaging_lead_time_post();
}

function custom_box_upsert_paper_packaging_lead_time_post()
{
    $data = custom_box_paper_packaging_lead_time_map();
    $version = 'paper-packaging-lead-time-20260701-v1';
    $post = custom_box_find_paper_packaging_lead_time_post($data);

    if (!$post) {
        $post_id = wp_insert_post(array(
            'post_type'    => 'post',
            'post_status'  => 'draft',
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => custom_box_paper_packaging_lead_time_post_content(),
        ), true);

        if (is_wp_error($post_id)) {
            update_option('custom_box_paper_packaging_lead_time_missing_post', $data['slug'], false);
            return $post_id;
        }

        $post = get_post($post_id);
    }

    if (!$post || 'trash' === $post->post_status) {
        update_option('custom_box_paper_packaging_lead_time_missing_post', $data['slug'], false);
        return 0;
    }

    delete_option('custom_box_paper_packaging_lead_time_missing_post');

    $update = array(
        'ID'           => $post->ID,
        'post_title'   => $data['title'],
        'post_name'    => $data['slug'],
        'post_excerpt' => $data['excerpt'],
    );

    if (get_post_meta($post->ID, '_custom_box_paper_packaging_lead_time_version', true) !== $version) {
        $update['post_content'] = custom_box_paper_packaging_lead_time_post_content();
    }

    if (!in_array($post->post_status, array('publish', 'private'), true)) {
        $update['post_status'] = 'draft';
    }

    $updated = wp_update_post($update, true);

    if (is_wp_error($updated)) {
        return $updated;
    }

    custom_box_paper_packaging_lead_time_terms($post->ID);

    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_result = custom_box_paper_packaging_lead_time_images($post->ID);
    update_option('custom_box_paper_packaging_lead_time_missing_images', $image_result['missing_images'], false);
    update_option('custom_box_paper_packaging_lead_time_missing_slots', $image_result['missing_slots'], false);

    if (empty($image_result['missing_images']) && empty($image_result['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_paper_packaging_lead_time_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_find_paper_packaging_lead_time_post($data)
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

function custom_box_paper_packaging_lead_time_map()
{
    return array(
        'title'           => 'What Affects Paper Packaging Lead Time?',
        'slug'            => 'what-affects-paper-packaging-lead-time',
        'excerpt'         => 'Learn what affects paper packaging lead time, from artwork and sample approval to material choice, printing, finishing, QC, packing, and shipping planning.',
        'seo_title'       => 'What Affects Paper Packaging Lead Time? Key Factors for Buyers',
        'seo_description' => 'Understand what affects paper packaging lead time, including artwork, sample approval, material, printing, finishing, QC, packing, and shipping.',
        'focus_keyword'   => 'what affects paper packaging lead time',
        'category'        => array(
            'name' => 'Packaging Guide',
            'slug' => 'packaging-guide',
        ),
        'tags'            => array(
            'paper packaging lead time',
            'custom paper boxes',
            'packaging production',
            'sample approval',
            'artwork checking',
            'printing and finishing',
            'packaging QC',
        ),
    );
}

function custom_box_paper_packaging_lead_time_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'what-affects-paper-packaging-lead-time-thumbnail',
            'alt'     => 'Paper packaging lead time planning with artwork, sample, materials, printing, QC and shipping notes',
            'title'   => 'What affects paper packaging lead time',
            'caption' => 'Lead time depends on artwork, sample approval, material, printing, finishing, QC and shipping coordination.',
        ),
        'slot_1' => array(
            'base'    => 'packaging-artwork-dieline-readiness-check',
            'alt'     => 'Packaging artwork and dieline readiness check before paper box production',
            'title'   => 'Packaging artwork and dieline readiness check',
            'caption' => 'Clear artwork and dieline files help reduce avoidable delays before production.',
        ),
        'slot_2' => array(
            'base'    => 'packaging-sample-material-approval-stage',
            'alt'     => 'Paper box sample approval with material swatches and structure checking',
            'title'   => 'Paper box sample and material approval',
            'caption' => 'Sample approval helps confirm structure, size, material and finishing direction before mass production.',
        ),
        'slot_3' => array(
            'base'    => 'paper-box-printing-finishing-qc-delay-factors',
            'alt'     => 'Custom paper box printing finishing and QC factors that affect lead time',
            'title'   => 'Printing finishing and QC lead time factors',
            'caption' => 'Printing, finishing and QC steps can add time but protect production quality.',
        ),
        'slot_4' => array(
            'base'    => 'packaging-lead-time-rfq-preparation-checklist',
            'alt'     => 'RFQ preparation checklist for custom paper packaging lead time planning',
            'title'   => 'Custom packaging RFQ preparation checklist',
            'caption' => 'A complete packaging brief helps the supplier estimate schedule more accurately.',
        ),
    );
}

function custom_box_paper_packaging_lead_time_terms($post_id)
{
    $data = custom_box_paper_packaging_lead_time_map();
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

function custom_box_paper_packaging_lead_time_images($post_id)
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

    foreach (custom_box_paper_packaging_lead_time_image_map() as $key => $image) {
        $attachment_id = custom_box_paper_packaging_lead_time_attachment($image['base']);

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
        $marker = '<!-- vpn-paper-packaging-lead-time-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_paper_packaging_lead_time_figure($attachment_id, $image);
        $inserted = custom_box_paper_packaging_lead_time_insert_figure($content, $figure, $marker, $slot_number);

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

function custom_box_paper_packaging_lead_time_insert_figure($content, $figure, $marker, $slot_number)
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

function custom_box_paper_packaging_lead_time_attachment($base)
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

function custom_box_paper_packaging_lead_time_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt'])
    );
}

function custom_box_paper_packaging_lead_time_post_content()
{
    $content_file = __DIR__ . '/post-content/what-affects-paper-packaging-lead-time.html';

    if (!is_readable($content_file)) {
        return '';
    }

    return (string) file_get_contents($content_file);
}

function custom_box_paper_packaging_lead_time_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = get_option('custom_box_paper_packaging_lead_time_missing_post', '');
    $missing_images = get_option('custom_box_paper_packaging_lead_time_missing_images', array());
    $missing_slots = get_option('custom_box_paper_packaging_lead_time_missing_slots', array());
    $messages = array();

    if (!empty($missing_post)) {
        $messages[] = sprintf(
            /* translators: %s: post slug */
            esc_html__('Paper packaging lead time sync could not create or find the draft for slug: %s', 'custom-box-theme'),
            esc_html($missing_post)
        );
    }

    if (!empty($missing_images) && is_array($missing_images)) {
        $messages[] = esc_html__('Paper packaging lead time sync is waiting for these Media Library files:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_images));
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        $messages[] = esc_html__('Paper packaging lead time sync could not find these content slots:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_slots));
    }

    if (empty($messages)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo implode('<br>', $messages);
    echo '</p></div>';
}
