<?php
/**
 * Maintains metadata and images for the paper box dieline guide.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_box_dieline_post');
add_action('admin_notices', 'custom_box_paper_box_dieline_notice');

function custom_box_sync_paper_box_dieline_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_paper_box_dieline_post();
}

function custom_box_upsert_paper_box_dieline_post()
{
    $data = custom_box_paper_box_dieline_map();
    $version = 'paper-box-dieline-20260624-v1';
    $post = custom_box_find_paper_box_dieline_post($data);

    if ($post && 'trash' === $post->post_status) {
        return 0;
    }

    if (!$post) {
        update_option('custom_box_paper_box_dieline_missing_slots', array('draft post'), false);
        return 0;
    }

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

    custom_box_paper_box_dieline_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_result = custom_box_paper_box_dieline_images($post->ID);
    update_option('custom_box_paper_box_dieline_missing_images', $image_result['missing_images'], false);
    update_option('custom_box_paper_box_dieline_missing_slots', $image_result['missing_slots'], false);

    if (empty($image_result['missing_images']) && empty($image_result['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_paper_box_dieline_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_find_paper_box_dieline_post($data)
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

function custom_box_paper_box_dieline_map()
{
    return array(
        'title'           => 'What Is a Paper Box Dieline and Why Does It Matter?',
        'slug'            => 'what-is-a-paper-box-dieline',
        'excerpt'         => 'Learn what a paper box dieline is, how cut lines, fold lines, bleed, safe areas and glue flaps affect custom paper box production, and what buyers should check before approving artwork.',
        'seo_title'       => 'What Is a Paper Box Dieline? Why It Matters Before Production',
        'seo_description' => 'Learn what a paper box dieline is, how cut lines, fold lines, bleed and glue areas affect artwork, samples and custom paper box production.',
        'focus_keyword'   => 'what is a paper box dieline',
        'category'        => array(
            'name' => 'Paper Box Printing',
            'slug' => 'paper-box-printing',
        ),
        'tags'            => array(
            'paper box dieline',
            'packaging dieline',
            'custom paper boxes',
            'artwork preparation',
            'paper box production',
            'packaging design',
        ),
    );
}

function custom_box_paper_box_dieline_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'paper-box-dieline-guide-thumbnail',
            'alt'     => 'Paper box dieline layout and assembled custom paper box sample',
            'title'   => 'Paper Box Dieline Guide',
            'caption' => 'A dieline connects the flat technical layout with the finished paper box.',
        ),
        'slot_1' => array(
            'base'    => 'paper-box-dieline-flat-layout',
            'alt'     => 'Flat paper box dieline with cut lines fold lines and glue flap',
            'title'   => 'Flat Paper Box Dieline Layout',
            'caption' => 'A flat dieline shows where the box will be cut, folded and glued.',
        ),
        'slot_2' => array(
            'base'    => 'dieline-components-cut-fold-bleed',
            'alt'     => 'Close up of cut lines fold lines bleed area and safe area on a box dieline',
            'title'   => 'Dieline Components: Cut, Fold, Bleed and Safe Area',
            'caption' => 'Key dieline markings help designers place artwork safely before printing.',
        ),
        'slot_3' => array(
            'base'    => 'artwork-on-paper-box-dieline',
            'alt'     => 'Packaging artwork placed on a paper box dieline before production',
            'title'   => 'Artwork Placement on a Box Dieline',
            'caption' => 'Artwork should follow the approved dieline before sample making and production.',
        ),
        'slot_4' => array(
            'base'    => 'paper-box-dieline-sample-qc-check',
            'alt'     => 'QC review of paper box dieline printed proof and assembled sample',
            'title'   => 'Paper Box Dieline Sample Review',
            'caption' => 'A sample review helps confirm size, folding, glue areas and artwork alignment.',
        ),
    );
}

function custom_box_paper_box_dieline_terms($post_id)
{
    $data = custom_box_paper_box_dieline_map();
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

function custom_box_paper_box_dieline_images($post_id)
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

    foreach (custom_box_paper_box_dieline_image_map() as $key => $image) {
        $attachment_id = custom_box_paper_box_dieline_attachment($image['base']);

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
        $marker = '<!-- vpn-paper-box-dieline-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_paper_box_dieline_figure($attachment_id, $image);
        $slot = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
        $slot_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

        if (false !== strpos($content, $marker)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
        } elseif (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
        } elseif (preg_match($slot_pattern, $content)) {
            $content = preg_replace($slot_pattern, $figure, $content, 1);
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

function custom_box_paper_box_dieline_attachment($base)
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

function custom_box_paper_box_dieline_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_paper_box_dieline_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_images = get_option('custom_box_paper_box_dieline_missing_images', array());
    $missing_slots = get_option('custom_box_paper_box_dieline_missing_slots', array());

    if (empty($missing_images) && empty($missing_slots)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Paper Box Dieline Guide sync needs attention:', 'custom-box-theme') . ' ';

    if (!empty($missing_images) && is_array($missing_images)) {
        echo esc_html__('missing images', 'custom-box-theme') . ': ' . esc_html(implode(', ', $missing_images)) . '. ';
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        echo esc_html__('missing slots', 'custom-box-theme') . ': ' . esc_html(implode(', ', $missing_slots)) . '.';
    }

    echo '</p></div>';
}
