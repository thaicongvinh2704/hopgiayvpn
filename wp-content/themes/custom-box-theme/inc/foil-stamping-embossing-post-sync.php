<?php
/**
 * Maintains metadata and images for the foil stamping and embossing paper boxes draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_foil_stamping_embossing_post');
add_action('admin_notices', 'custom_box_foil_stamping_embossing_notice');

function custom_box_sync_foil_stamping_embossing_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_foil_stamping_embossing_post();
}

function custom_box_upsert_foil_stamping_embossing_post()
{
    $data = custom_box_foil_stamping_embossing_map();
    $version = 'foil-stamping-embossing-20260623-v1';
    $post = custom_box_find_foil_stamping_embossing_post($data);

    if ($post && 'trash' === $post->post_status) {
        return 0;
    }

    if (!$post) {
        update_option('custom_box_foil_stamping_embossing_missing_slots', array('draft post'), false);
        return 0;
    }

    if (get_post_meta($post->ID, '_custom_box_foil_stamping_embossing_version', true) !== $version) {
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
    }

    custom_box_foil_stamping_embossing_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_result = custom_box_foil_stamping_embossing_images($post->ID);
    update_option('custom_box_foil_stamping_embossing_missing_images', $image_result['missing_images'], false);
    update_option('custom_box_foil_stamping_embossing_missing_slots', $image_result['missing_slots'], false);

    if (empty($image_result['missing_images']) && empty($image_result['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_foil_stamping_embossing_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_find_foil_stamping_embossing_post($data)
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

function custom_box_foil_stamping_embossing_map()
{
    return array(
        'title'           => 'Foil Stamping and Embossing on Paper Boxes: When to Use Them',
        'slug'            => 'foil-stamping-and-embossing-on-paper-boxes',
        'excerpt'         => 'Learn when to use foil stamping, embossing, debossing, or combined finishes on paper boxes for gift, cosmetic, and jewelry packaging projects.',
        'seo_title'       => 'Foil Stamping & Embossing on Paper Boxes: Use Guide',
        'seo_description' => 'Learn when to use foil stamping and embossing on paper boxes for gift, cosmetic and jewelry packaging, including materials, artwork, cost and QC tips.',
        'focus_keyword'   => 'foil stamping and embossing on paper boxes',
        'category'        => array(
            'name' => 'Paper Box Printing',
            'slug' => 'paper-box-printing',
        ),
        'tags'            => array(
            'Foil Stamping',
            'Embossing',
            'Paper Box Printing',
            'Gift Boxes',
            'Cosmetic Packaging',
            'Jewelry Packaging',
            'Packaging Finishing',
        ),
    );
}

function custom_box_foil_stamping_embossing_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'foil-stamping-embossing-paper-boxes-guide',
            'alt'     => 'Foil stamping and embossing on premium paper boxes',
            'title'   => 'Foil Stamping and Embossing on Paper Boxes',
            'caption' => 'Foil stamping and embossing can add visual contrast and tactile detail when used correctly.',
        ),
        'slot_1' => array(
            'base'    => 'foil-vs-embossing-finish-comparison',
            'alt'     => 'Comparison of foil stamping and embossing effects on paper box logos',
            'title'   => 'Foil vs Embossing Finish Comparison',
            'caption' => 'Foil adds shine, while embossing adds a raised tactile effect.',
        ),
        'slot_2' => array(
            'base'    => 'hot-foil-stamped-gift-box-logo',
            'alt'     => 'Gold foil stamped logo on a rigid gift paper box',
            'title'   => 'Hot Foil Stamped Gift Box Logo',
            'caption' => 'Foil stamping is effective for clean logos on gift boxes and premium retail packaging.',
        ),
        'slot_3' => array(
            'base'    => 'cosmetic-paper-box-embossed-logo',
            'alt'     => 'Embossed logo detail on cosmetic paper box packaging',
            'title'   => 'Cosmetic Paper Box Embossed Logo',
            'caption' => 'Embossing can create a refined tactile detail on cosmetic paper boxes.',
        ),
        'slot_4' => array(
            'base'    => 'jewelry-box-foil-embossing-detail',
            'alt'     => 'Foil stamping and embossing detail on jewelry paper box',
            'title'   => 'Jewelry Box Foil and Embossing Detail',
            'caption' => 'Jewelry boxes often use foil or embossing for small but visible brand marks.',
        ),
        'slot_5' => array(
            'base'    => 'paper-box-finishing-rfq-checklist',
            'alt'     => 'Packaging buyer checklist for foil stamping and embossing paper boxes',
            'title'   => 'Paper Box Finishing RFQ Checklist',
            'caption' => 'A clear RFQ helps the factory recommend the right finishing method.',
        ),
    );
}

function custom_box_foil_stamping_embossing_terms($post_id)
{
    $data = custom_box_foil_stamping_embossing_map();
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

function custom_box_foil_stamping_embossing_images($post_id)
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

    foreach (custom_box_foil_stamping_embossing_image_map() as $key => $image) {
        $attachment_id = custom_box_foil_stamping_embossing_attachment($image['base']);

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
        $marker = '<!-- vpn-foil-stamping-embossing-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_foil_stamping_embossing_figure($attachment_id, $image);
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

function custom_box_foil_stamping_embossing_attachment($base)
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

function custom_box_foil_stamping_embossing_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_foil_stamping_embossing_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_images = get_option('custom_box_foil_stamping_embossing_missing_images', array());
    $missing_slots = get_option('custom_box_foil_stamping_embossing_missing_slots', array());

    if (empty($missing_images) && empty($missing_slots)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Foil stamping and embossing post sync needs attention:', 'custom-box-theme') . ' ';

    if (!empty($missing_images) && is_array($missing_images)) {
        echo esc_html__('missing images', 'custom-box-theme') . ': ' . esc_html(implode(', ', $missing_images)) . '. ';
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        echo esc_html__('missing slots', 'custom-box-theme') . ': ' . esc_html(implode(', ', $missing_slots)) . '.';
    }

    echo '</p></div>';
}
