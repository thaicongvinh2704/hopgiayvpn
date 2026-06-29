<?php
/**
 * Syncs metadata and images for the paper box quality control checklist draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_box_quality_control_checklist_post');
add_action('admin_notices', 'custom_box_paper_box_quality_control_checklist_notice');

function custom_box_sync_paper_box_quality_control_checklist_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_paper_box_quality_control_checklist_post();
}

function custom_box_upsert_paper_box_quality_control_checklist_post()
{
    $data = custom_box_paper_box_quality_control_checklist_map();
    $version = 'paper-box-quality-control-checklist-20260629-v1';
    $post = custom_box_find_paper_box_quality_control_checklist_post($data);

    if ($post && 'trash' === $post->post_status) {
        update_option('custom_box_paper_box_quality_control_checklist_missing_post', $data['slug'], false);
        return 0;
    }

    if (!$post) {
        update_option('custom_box_paper_box_quality_control_checklist_missing_post', $data['slug'], false);
        return 0;
    }

    delete_option('custom_box_paper_box_quality_control_checklist_missing_post');

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

    custom_box_paper_box_quality_control_checklist_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_result = custom_box_paper_box_quality_control_checklist_images($post->ID);
    update_option('custom_box_paper_box_quality_control_checklist_missing_images', $image_result['missing_images'], false);
    update_option('custom_box_paper_box_quality_control_checklist_missing_slots', $image_result['missing_slots'], false);

    if (empty($image_result['missing_images']) && empty($image_result['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_paper_box_quality_control_checklist_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_find_paper_box_quality_control_checklist_post($data)
{
    $post = get_page_by_path($data['slug'], OBJECT, 'post');
    if ($post) {
        return $post;
    }

    $matches = get_posts(array(
        'post_type'      => 'post',
        'post_status'    => array('draft', 'pending', 'private', 'publish'),
        'title'          => $data['title'],
        'posts_per_page' => 1,
    ));

    return !empty($matches) ? $matches[0] : null;
}

function custom_box_paper_box_quality_control_checklist_map()
{
    return array(
        'title'           => 'Paper Box Quality Control Checklist for Packaging Production',
        'slug'            => 'paper-box-quality-control-checklist',
        'excerpt'         => 'Use this paper box quality control checklist to inspect material, size, printing, finishing, gluing, inserts, final packing, and export readiness before bulk packaging production.',
        'seo_title'       => 'Paper Box Quality Control Checklist for Packaging Production',
        'seo_description' => 'A practical paper box quality control checklist for B2B buyers covering material, size, color, printing, finishing, gluing, inserts, final inspection, and export packing.',
        'focus_keyword'   => 'paper box quality control checklist',
        'category'        => array(
            'name' => 'Blog / Packaging Production Guide',
            'slug' => 'blog-packaging-production-guide',
        ),
        'tags'            => array(
            'paper box quality control',
            'packaging QC',
            'paper box checklist',
            'custom packaging production',
            'box inspection',
            'packaging manufacturing',
        ),
    );
}

function custom_box_paper_box_quality_control_checklist_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'paper-box-quality-control-checklist-thumbnail',
            'alt'     => 'Paper box quality control checklist for packaging production',
            'title'   => 'Paper Box Quality Control Checklist',
            'caption' => 'A practical QC checklist scene for inspecting custom paper boxes before packaging production.',
        ),
        'slot_1' => array(
            'base'    => 'paper-box-qc-checklist-inspection-table',
            'alt'     => 'QC checklist table for custom paper box inspection',
            'title'   => 'Paper Box QC Inspection Table',
            'caption' => 'A structured paper box inspection checklist helps buyers and factories control defects by production stage.',
        ),
        'slot_2' => array(
            'base'    => 'paper-box-material-thickness-qc-check',
            'alt'     => 'Paper box material thickness and surface quality check',
            'title'   => 'Paper Box Material QC Check',
            'caption' => 'Material inspection helps confirm board thickness, surface condition, and box strength before production continues.',
        ),
        'slot_3' => array(
            'base'    => 'paper-box-printing-finishing-defects-qc',
            'alt'     => 'Paper box printing and finishing defect quality check',
            'title'   => 'Printing and Finishing QC for Paper Boxes',
            'caption' => 'Printing and finishing inspection should check color, lamination, foil, embossing, and visible surface defects.',
        ),
        'slot_4' => array(
            'base'    => 'paper-box-export-packing-final-qc',
            'alt'     => 'Export packing quality control for paper boxes',
            'title'   => 'Paper Box Export Packing QC',
            'caption' => 'Final packing QC reduces scratches, crushing, wrong labels, and transit damage risks.',
        ),
    );
}

function custom_box_paper_box_quality_control_checklist_terms($post_id)
{
    $data = custom_box_paper_box_quality_control_checklist_map();
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

function custom_box_paper_box_quality_control_checklist_images($post_id)
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

    foreach (custom_box_paper_box_quality_control_checklist_image_map() as $key => $image) {
        $attachment_id = custom_box_paper_box_quality_control_checklist_attachment($image['base']);

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
        $marker = '<!-- vpn-paper-box-quality-control-checklist-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_paper_box_quality_control_checklist_figure($attachment_id, $image);
        $slot = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
        $slot_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
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

function custom_box_paper_box_quality_control_checklist_attachment($base)
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

function custom_box_paper_box_quality_control_checklist_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_paper_box_quality_control_checklist_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = get_option('custom_box_paper_box_quality_control_checklist_missing_post', '');
    $missing_images = get_option('custom_box_paper_box_quality_control_checklist_missing_images', array());
    $missing_slots = get_option('custom_box_paper_box_quality_control_checklist_missing_slots', array());
    $messages = array();

    if (!empty($missing_post)) {
        $messages[] = sprintf(
            /* translators: %s: post slug */
            esc_html__('Paper box quality control checklist sync could not find the draft for slug: %s', 'custom-box-theme'),
            esc_html($missing_post)
        );
    }

    if (!empty($missing_images) && is_array($missing_images)) {
        $messages[] = esc_html__('Paper box quality control checklist sync is waiting for these Media Library files:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_images));
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        $messages[] = esc_html__('Paper box quality control checklist sync could not find these content slots:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_slots));
    }

    if (empty($messages)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo implode('<br>', $messages);
    echo '</p></div>';
}
