<?php
/**
 * Maintains metadata and images for the paper box logo printing guide.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_logo_printing_post');
add_action('admin_notices', 'custom_box_logo_printing_post_notice');

function custom_box_sync_logo_printing_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_logo_printing_post();
}

function custom_box_upsert_logo_printing_post()
{
    $data = custom_box_logo_printing_post_map();
    $version = 'logo-printing-post-20260617-v1';
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
        return 0;
    }

    if (!$post) {
        $post_id = wp_insert_post(array(
            'post_type'    => 'post',
            'post_status'  => 'draft',
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => '',
        ), true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $post = get_post($post_id);
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

    custom_box_logo_printing_post_terms($post->ID);

    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $missing = custom_box_logo_printing_post_images($post->ID);
    update_option('custom_box_logo_printing_post_missing_images', $missing, false);

    if (empty($missing)) {
        update_post_meta($post->ID, '_custom_box_logo_printing_post_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_logo_printing_post_map()
{
    return array(
        'title'           => 'How to Print a Logo on Paper Boxes: Methods and Artwork Tips',
        'slug'            => 'how-to-print-logo-on-paper-boxes',
        'excerpt'         => 'Learn how to print a logo on paper boxes using offset printing, digital printing, foil stamping, embossing, spot UV and proper vector artwork preparation.',
        'seo_title'       => 'How to Print a Logo on Paper Boxes: Methods and Artwork Tips',
        'seo_description' => 'Learn how to print a logo on paper boxes with offset, digital, foil stamping, embossing, spot UV and print-ready vector artwork tips.',
        'focus_keyword'   => 'how to print logo on paper boxes',
        'category'        => array(
            'name' => 'Paper Packaging Guide',
            'slug' => 'paper-packaging-guide',
        ),
        'tags'            => array(
            'logo printing',
            'paper box printing',
            'custom printed paper boxes',
            'foil stamping',
            'embossing',
            'spot UV',
            'offset printing',
            'packaging artwork',
            'vector logo',
        ),
    );
}

function custom_box_logo_printing_post_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'how-to-print-logo-on-paper-boxes-thumbnail-3',
            'alt'     => 'how to print a logo on paper boxes',
            'title'   => 'How to Print a Logo on Paper Boxes',
            'caption' => 'A practical guide to logo printing methods and artwork preparation for paper boxes.',
        ),
        'slot_1' => array(
            'base'    => 'paper-box-logo-printing-methods-overview-3',
            'alt'     => 'paper box logo printing methods overview',
            'title'   => 'Paper Box Logo Printing Methods',
            'caption' => 'Offset printing, foil stamping, embossing and spot UV create different logo effects on paper boxes.',
        ),
        'slot_2' => array(
            'base'    => 'offset-logo-printing-on-paperboard-boxes-3',
            'alt'     => 'offset logo printing on paperboard boxes',
            'title'   => 'Offset Logo Printing on Paperboard Boxes',
            'caption' => 'Offset printing is suitable for detailed logos and full printed packaging artwork.',
        ),
        'slot_3' => array(
            'base'    => 'foil-stamped-logo-on-rigid-paper-box-3',
            'alt'     => 'foil stamped logo on rigid paper box',
            'title'   => 'Foil Stamped Logo on Rigid Paper Box',
            'caption' => 'Foil stamping creates a metallic logo effect for premium paper box packaging.',
        ),
        'slot_4' => array(
            'base'    => 'vector-logo-artwork-for-paper-box-printing-3',
            'alt'     => 'vector logo artwork for paper box printing',
            'title'   => 'Vector Logo Artwork for Paper Box Printing',
            'caption' => 'Vector artwork helps keep logos sharp and ready for printing, foil, embossing and spot UV.',
        ),
        'slot_5' => array(
            'base'    => 'logo-proofing-for-custom-paper-boxes-3',
            'alt'     => 'logo proofing for custom paper boxes',
            'title'   => 'Logo Proofing for Custom Paper Boxes',
            'caption' => 'Physical samples help confirm logo color, position, finishing and material performance before bulk production.',
        ),
    );
}

function custom_box_logo_printing_post_terms($post_id)
{
    $data = custom_box_logo_printing_post_map();
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

function custom_box_logo_printing_post_images($post_id)
{
    $post = get_post($post_id);
    if (!$post) {
        return array();
    }

    $content = $post->post_content;
    $missing = array();

    foreach (custom_box_logo_printing_post_image_map() as $key => $image) {
        $attachment_id = custom_box_find_logo_printing_attachment($image['base']);

        if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
            $missing[] = $image['base'];
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
        $marker = '<!-- vpn-logo-printing-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_logo_printing_figure($attachment_id, $image);
        $slot = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
        $slot_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

        if (false !== strpos($content, $marker)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
        } elseif (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
        } else {
            $content = preg_replace($slot_pattern, $figure, $content, 1);
        }
    }

    if ($content !== $post->post_content) {
        wp_update_post(array(
            'ID'           => $post_id,
            'post_content' => $content,
        ));
    }

    return $missing;
}

function custom_box_find_logo_printing_attachment($base)
{
    $attachment = get_page_by_path(sanitize_title($base), OBJECT, 'attachment');
    if ($attachment) {
        return (int) $attachment->ID;
    }

    global $wpdb;

    $attachment_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_wp_attached_file'
             AND meta_value LIKE %s
             ORDER BY post_id DESC LIMIT 1",
            '%/' . $wpdb->esc_like($base) . '.%'
        )
    );

    if ($attachment_id) {
        return $attachment_id;
    }

    return custom_box_create_logo_printing_attachment($base);
}

function custom_box_create_logo_printing_attachment($base)
{
    $upload_dir = wp_get_upload_dir();
    $relative_file = '2026/06/' . sanitize_file_name($base) . '.webp';
    $file_path = trailingslashit($upload_dir['basedir']) . $relative_file;

    if (!file_exists($file_path)) {
        return 0;
    }

    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => 'image/webp',
            'post_title'     => sanitize_title($base),
            'post_name'      => sanitize_title($base),
            'post_status'    => 'inherit',
        ),
        $file_path
    );

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';

    update_post_meta($attachment_id, '_wp_attached_file', $relative_file);
    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file_path));

    return (int) $attachment_id;
}

function custom_box_logo_printing_figure($attachment_id, $image)
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

function custom_box_logo_printing_post_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_logo_printing_post_missing_images', array());
    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Logo printing post sync is waiting for these Media Library files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}
