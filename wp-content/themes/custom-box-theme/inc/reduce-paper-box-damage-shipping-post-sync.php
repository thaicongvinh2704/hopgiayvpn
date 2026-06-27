<?php
/**
 * Syncs images and metadata for the paper box shipping damage guide draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_reduce_paper_box_damage_shipping_post');
add_action('admin_notices', 'custom_box_reduce_paper_box_damage_shipping_notice');

function custom_box_sync_reduce_paper_box_damage_shipping_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_reduce_paper_box_damage_shipping_post();
}

function custom_box_upsert_reduce_paper_box_damage_shipping_post()
{
    $data = custom_box_reduce_paper_box_damage_shipping_map();
    $version = 'reduce-paper-box-damage-shipping-20260627-v1';
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

    if (!$post || 'trash' === $post->post_status) {
        update_option('custom_box_reduce_paper_box_damage_shipping_missing_post', $data['slug'], false);
        return 0;
    }

    delete_option('custom_box_reduce_paper_box_damage_shipping_missing_post');

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

    custom_box_reduce_paper_box_damage_shipping_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_sync = custom_box_reduce_paper_box_damage_shipping_images($post->ID);
    update_option('custom_box_reduce_paper_box_damage_shipping_missing_images', $image_sync['missing_images'], false);
    update_option('custom_box_reduce_paper_box_damage_shipping_missing_slots', $image_sync['missing_slots'], false);

    if (empty($image_sync['missing_images']) && empty($image_sync['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_reduce_paper_box_damage_shipping_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_reduce_paper_box_damage_shipping_map()
{
    return array(
        'title'           => 'How to Reduce Paper Box Damage During Shipping and Handling',
        'slug'            => 'how-to-reduce-paper-box-damage-during-shipping',
        'excerpt'         => 'Learn how B2B buyers can reduce paper box damage during shipping and handling by improving inserts, outer cartons, palletizing, moisture control, and pre-shipment QC.',
        'seo_title'       => 'How to Reduce Paper Box Damage During Shipping and Handling',
        'seo_description' => 'Learn practical ways to reduce paper box damage during shipping with better inserts, outer cartons, palletizing, moisture control, and QC checks.',
        'focus_keyword'   => 'how to reduce paper box damage during shipping',
        'category'        => array(
            'name' => 'Packaging Guide',
            'slug' => 'packaging-guide',
        ),
        'tags'            => array(
            'paper box damage',
            'shipping packaging',
            'export carton',
            'paper box inserts',
            'palletizing',
            'packaging QC',
            'moisture control',
        ),
    );
}

function custom_box_reduce_paper_box_damage_shipping_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'reduce-paper-box-damage-shipping-thumbnail',
            'alt'     => 'Paper boxes protected with inserts, outer carton and pallet packing to reduce shipping damage',
            'title'   => 'Reducing Paper Box Damage During Shipping',
            'caption' => 'A complete packaging system should protect the product, retail box, carton and pallet load.',
        ),
        'slot_1' => array(
            'base'    => 'paper-box-shipping-damage-causes',
            'alt'     => 'Common paper box shipping damage including crushed corners, scratches and moisture exposure',
            'title'   => 'Common Causes of Paper Box Shipping Damage',
            'caption' => 'Identifying the damage type helps buyers fix the right part of the packaging system.',
        ),
        'slot_2' => array(
            'base'    => 'outer-carton-paper-box-protection',
            'alt'     => 'Strong corrugated outer carton protecting retail paper boxes during export shipping',
            'title'   => 'Outer Carton Protection for Paper Boxes',
            'caption' => 'The outer carton should match product weight, carton quantity and shipping route.',
        ),
        'slot_3' => array(
            'base'    => 'paper-box-inserts-shipping-protection',
            'alt'     => 'Paperboard insert and corrugated divider holding products inside paper boxes for shipping protection',
            'title'   => 'Inserts and Dividers for Shipping Protection',
            'caption' => 'Inserts reduce movement inside the box and help protect products during vibration and handling.',
        ),
        'slot_4' => array(
            'base'    => 'palletized-paper-box-export-packing-qc',
            'alt'     => 'Palletized export cartons with corner boards, stretch wrap and QC checklist for paper box shipment',
            'title'   => 'Palletized Export Packing QC',
            'caption' => 'Pallet stability, carton condition and moisture control should be checked before shipment.',
        ),
    );
}

function custom_box_reduce_paper_box_damage_shipping_terms($post_id)
{
    $data = custom_box_reduce_paper_box_damage_shipping_map();
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

function custom_box_reduce_paper_box_damage_shipping_images($post_id)
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

    foreach (custom_box_reduce_paper_box_damage_shipping_image_map() as $key => $image) {
        $attachment_id = custom_box_find_reduce_paper_box_damage_shipping_attachment($image['base']);

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

        $marker = '<!-- vpn-reduce-paper-box-damage-shipping-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_reduce_paper_box_damage_shipping_figure($attachment_id, $image);
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

function custom_box_find_reduce_paper_box_damage_shipping_attachment($base)
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

function custom_box_reduce_paper_box_damage_shipping_figure($attachment_id, $image)
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

function custom_box_reduce_paper_box_damage_shipping_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_post = get_option('custom_box_reduce_paper_box_damage_shipping_missing_post', '');
    $missing_images = get_option('custom_box_reduce_paper_box_damage_shipping_missing_images', array());
    $missing_slots = get_option('custom_box_reduce_paper_box_damage_shipping_missing_slots', array());
    $messages = array();

    if (!empty($missing_post)) {
        $messages[] = sprintf(
            /* translators: %s: post slug */
            esc_html__('Paper box damage shipping post sync could not find the draft for slug: %s', 'custom-box-theme'),
            esc_html($missing_post)
        );
    }

    if (!empty($missing_images) && is_array($missing_images)) {
        $messages[] = esc_html__('Paper box damage shipping post sync is waiting for these Media Library files:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_images));
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        $messages[] = esc_html__('Paper box damage shipping post sync could not find these content slots:', 'custom-box-theme') . ' ' . esc_html(implode(', ', $missing_slots));
    }

    if (empty($messages)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo implode('<br>', $messages);
    echo '</p></div>';
}
