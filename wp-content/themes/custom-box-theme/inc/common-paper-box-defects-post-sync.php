<?php
/**
 * Syncs metadata and images for the common paper box defects draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_common_paper_box_defects_post');
add_action('admin_notices', 'custom_box_common_paper_box_defects_notice');

function custom_box_sync_common_paper_box_defects_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_common_paper_box_defects_post();
}

function custom_box_upsert_common_paper_box_defects_post()
{
    $data = custom_box_common_paper_box_defects_map();
    $version = 'common-paper-box-defects-20260629-v1';
    $post = custom_box_find_common_paper_box_defects_post($data);

    if (!$post || 'trash' === $post->post_status) {
        update_option('custom_box_common_paper_box_defects_missing_post', $data['slug'], false);
        return 0;
    }

    delete_option('custom_box_common_paper_box_defects_missing_post');

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

    custom_box_common_paper_box_defects_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_result = custom_box_common_paper_box_defects_images($post->ID);
    update_option('custom_box_common_paper_box_defects_missing_images', $image_result['missing_images'], false);
    update_option('custom_box_common_paper_box_defects_missing_slots', $image_result['missing_slots'], false);

    if (empty($image_result['missing_images']) && empty($image_result['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_common_paper_box_defects_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_find_common_paper_box_defects_post($data)
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

function custom_box_common_paper_box_defects_map()
{
    return array(
        'title'           => 'Common Paper Box Defects and How Packaging Factories Prevent Them',
        'slug'            => 'common-paper-box-defects',
        'excerpt'         => 'Learn the most common paper box defects in packaging production, including color variation, print misalignment, weak glue, surface scratches, deformation, wrong size, and how factories prevent them.',
        'seo_title'       => 'Common Paper Box Defects and How Packaging Factories Prevent Them',
        'seo_description' => 'Learn common paper box defects such as color variation, print misalignment, weak glue, scratches, deformation, wrong size, and how packaging factories prevent them.',
        'focus_keyword'   => 'common paper box defects',
        'category'        => array(
            'name' => 'Blog / Packaging Production Guide',
            'slug' => 'blog-packaging-production-guide',
        ),
        'tags'            => array(
            'paper box defects',
            'packaging defects',
            'paper box production',
            'packaging quality control',
            'custom paper boxes',
            'printing defects',
            'gluing defects',
        ),
    );
}

function custom_box_common_paper_box_defects_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'common-paper-box-defects-thumbnail',
            'alt'     => 'Common paper box defects and prevention in packaging production',
            'title'   => 'Common Paper Box Defects',
            'caption' => 'Common paper box defects include color variation, print misalignment, weak glue, scratches, dents, and wrong dimensions.',
        ),
        'slot_1' => array(
            'base'    => 'paper-box-defects-comparison-overview',
            'alt'     => 'Overview of common paper box defects on custom packaging',
            'title'   => 'Paper Box Defects Overview',
            'caption' => 'A visual overview helps buyers identify the most common defects before approving bulk packaging production.',
        ),
        'slot_2' => array(
            'base'    => 'paper-box-color-print-misalignment-defects',
            'alt'     => 'Paper box color variation and print misalignment defects',
            'title'   => 'Color and Print Misalignment Defects',
            'caption' => 'Color variation and print misalignment should be controlled during proofing, press setup, and first-sheet inspection.',
        ),
        'slot_3' => array(
            'base'    => 'paper-box-weak-glue-surface-scratch-defects',
            'alt'     => 'Weak glue and surface scratch defects on paper boxes',
            'title'   => 'Weak Glue and Surface Defect QC',
            'caption' => 'Weak glue, scratches, scuffs, and handling marks can damage both function and appearance.',
        ),
        'slot_4' => array(
            'base'    => 'paper-box-dent-wrong-size-packing-defects',
            'alt'     => 'Paper box dents wrong size and packing damage defects',
            'title'   => 'Dents, Wrong Size and Packing Defects',
            'caption' => 'Dents, poor product fit, and packing damage should be reviewed through structure, dieline, carton loading, and shipping checks.',
        ),
    );
}

function custom_box_common_paper_box_defects_terms($post_id)
{
    $data = custom_box_common_paper_box_defects_map();
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

function custom_box_common_paper_box_defects_images($post_id)
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

    foreach (custom_box_common_paper_box_defects_image_map() as $key => $image) {
        $attachment_id = custom_box_common_paper_box_defects_attachment($image['base']);

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
        $marker = '<!-- vpn-common-paper-box-defects-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_common_paper_box_defects_figure($attachment_id, $image);
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

function custom_box_common_paper_box_defects_attachment($base)
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

function custom_box_common_paper_box_defects_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_common_paper_box_defects_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = get_option('custom_box_common_paper_box_defects_missing_post', '');
    $missing_images = get_option('custom_box_common_paper_box_defects_missing_images', array());
    $missing_slots = get_option('custom_box_common_paper_box_defects_missing_slots', array());
    $messages = array();

    if (!empty($missing_post)) {
        $messages[] = sprintf(
            /* translators: %s: post slug */
            esc_html__('Common paper box defects sync could not find the draft for slug: %s', 'custom-box-theme'),
            esc_html($missing_post)
        );
    }

    if (!empty($missing_images) && is_array($missing_images)) {
        $messages[] = esc_html__('Common paper box defects sync is waiting for these Media Library files:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_images));
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        $messages[] = esc_html__('Common paper box defects sync could not find these content slots:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_slots));
    }

    if (empty($messages)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo implode('<br>', $messages);
    echo '</p></div>';
}
