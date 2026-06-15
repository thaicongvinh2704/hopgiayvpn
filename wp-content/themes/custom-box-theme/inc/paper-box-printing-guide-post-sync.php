<?php
/**
 * Creates and maintains the paper box printing guide draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_box_printing_guide');
add_action('admin_notices', 'custom_box_paper_box_printing_guide_notice');

function custom_box_sync_paper_box_printing_guide()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_paper_box_printing_guide();
}

function custom_box_upsert_paper_box_printing_guide()
{
    $data = custom_box_paper_box_printing_guide_map();
    $version = 'paper-box-printing-guide-20260615-v1';
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
            'post_content' => custom_box_paper_box_printing_guide_content(),
        ), true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $post = get_post($post_id);
    }

    if (get_post_meta($post->ID, '_custom_box_paper_box_printing_guide_version', true) !== $version) {
        $update = array(
            'ID'           => $post->ID,
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => custom_box_paper_box_printing_guide_content(),
        );

        if (!in_array($post->post_status, array('publish', 'private'), true)) {
            $update['post_status'] = 'draft';
        }

        $updated = wp_update_post($update, true);
        if (is_wp_error($updated)) {
            return $updated;
        }
    }

    custom_box_paper_box_printing_guide_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $missing = custom_box_paper_box_printing_guide_images($post->ID);
    update_option('custom_box_paper_box_printing_guide_missing_images', $missing, false);

    if (empty($missing)) {
        update_post_meta($post->ID, '_custom_box_paper_box_printing_guide_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_paper_box_printing_guide_map()
{
    return array(
        'title'           => 'Paper Box Printing Guide: Offset, Digital, CMYK and Pantone',
        'slug'            => 'paper-box-printing-guide',
        'excerpt'         => 'Learn the basics of paper box printing, including offset printing, digital printing, CMYK, Pantone, artwork preparation, proofing and common printing mistakes for packaging buyers.',
        'seo_title'       => 'Paper Box Printing Guide: Offset, Digital, CMYK and Pantone',
        'seo_description' => 'Learn paper box printing basics: offset vs digital printing, CMYK vs Pantone, artwork setup, proofing and common mistakes for packaging buyers.',
        'focus_keyword'   => 'paper box printing guide',
        'category'        => array(
            'name' => 'Paper Packaging Guide',
            'slug' => 'paper-packaging-guide',
        ),
        'tags'            => array(
            'paper box printing',
            'offset printing',
            'digital printing',
            'CMYK',
            'Pantone',
            'packaging artwork',
            'printed paper boxes',
            'packaging guide',
        ),
    );
}

function custom_box_paper_box_printing_guide_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'paper-box-printing-guide-thumbnail',
            'alt'     => 'paper box printing guide for packaging buyers',
            'title'   => 'Paper Box Printing Guide',
            'caption' => 'A practical guide to offset, digital, CMYK and Pantone printing for paper boxes.',
        ),
        'slot_1' => array(
            'base'    => 'offset-vs-digital-paper-box-samples',
            'alt'     => 'offset and digital printing methods for paper boxes',
            'title'   => 'Offset and Digital Paper Box Printing',
            'caption' => 'Offset and digital printing are selected based on quantity, artwork complexity and color requirements.',
        ),
        'slot_2' => array(
            'base'    => 'offset-printing-inspection-paperboard',
            'alt'     => 'offset printing for paperboard packaging boxes',
            'title'   => 'Offset Printing for Paperboard Boxes',
            'caption' => 'Offset printing is commonly used for detailed artwork and high-quality retail packaging.',
        ),
        'slot_3' => array(
            'base'    => 'cmyk-pantone-color-matching-proof',
            'alt'     => 'CMYK and Pantone color guide for packaging printing',
            'title'   => 'CMYK and Pantone Packaging Color Guide',
            'caption' => 'CMYK is useful for full-color artwork, while Pantone helps control brand-critical colors.',
        ),
        'slot_4' => array(
            'base'    => 'paper-box-dieline-printing-checklist',
            'alt'     => 'print ready artwork checklist for paper box printing',
            'title'   => 'Print-Ready Paper Box Artwork Checklist',
            'caption' => 'Correct dielines, bleed, safe zones and color setup help reduce printing mistakes.',
        ),
    );
}

function custom_box_paper_box_printing_guide_terms($post_id)
{
    $data = custom_box_paper_box_printing_guide_map();
    $category = get_term_by('slug', $data['category']['slug'], 'category');

    if (!$category) {
        $created = wp_insert_term(
            $data['category']['name'],
            'category',
            array('slug' => $data['category']['slug'])
        );

        if (!is_wp_error($created)) {
            wp_set_post_categories($post_id, array((int) $created['term_id']), false);
        }
    } else {
        wp_set_post_categories($post_id, array((int) $category->term_id), false);
    }

    wp_set_post_tags($post_id, $data['tags'], false);
}

function custom_box_paper_box_printing_guide_images($post_id)
{
    $post = get_post($post_id);
    if (!$post) {
        return array();
    }

    $content = $post->post_content;
    $missing = array();

    foreach (custom_box_paper_box_printing_guide_image_map() as $key => $image) {
        $attachment_id = custom_box_paper_box_printing_guide_attachment($image['base']);

        if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
            $missing[] = $image['base'];
            continue;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
        wp_update_post(array(
            'ID'           => $attachment_id,
            'post_title'   => $image['title'],
            'post_excerpt' => $image['caption'],
        ));

        if ('featured' === $key) {
            set_post_thumbnail($post_id, $attachment_id);
            continue;
        }

        $marker = '<!-- vpn-paper-box-printing-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_paper_box_printing_guide_figure($attachment_id, $image);
        $slot = '<!-- IMAGE_SLOT_' . substr($key, -1) . ' -->';

        if (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
        } elseif (false !== strpos($content, $marker)) {
            $content = preg_replace(
                '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is',
                $figure,
                $content,
                1
            );
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

function custom_box_paper_box_printing_guide_attachment($base)
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

function custom_box_paper_box_printing_guide_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_paper_box_printing_guide_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_paper_box_printing_guide_missing_images', array());
    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Paper Box Printing Guide sync is waiting for these Media Library files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}

function custom_box_paper_box_printing_guide_content()
{
    $content_file = __DIR__ . '/post-content/paper-box-printing-guide.html';

    if (!is_readable($content_file)) {
        return '';
    }

    return (string) file_get_contents($content_file);
}
