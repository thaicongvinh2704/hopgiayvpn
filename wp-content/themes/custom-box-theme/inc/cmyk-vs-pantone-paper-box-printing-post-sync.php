<?php
/**
 * Maintains metadata and images for the CMYK vs Pantone paper box printing guide.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_cmyk_vs_pantone_paper_box_printing_post');
add_action('admin_notices', 'custom_box_cmyk_vs_pantone_paper_box_printing_notice');

function custom_box_sync_cmyk_vs_pantone_paper_box_printing_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_cmyk_vs_pantone_paper_box_printing_post();
}

function custom_box_upsert_cmyk_vs_pantone_paper_box_printing_post()
{
    $data = custom_box_cmyk_vs_pantone_paper_box_printing_map();
    $version = 'cmyk-vs-pantone-paper-box-printing-20260618-v1';
    $post = get_page_by_path($data['slug'], OBJECT, 'post');

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
            'post_content' => custom_box_cmyk_vs_pantone_paper_box_printing_content(),
        ), true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $post = get_post($post_id);
    }

    if (get_post_meta($post->ID, '_custom_box_cmyk_vs_pantone_paper_box_printing_version', true) !== $version) {
        $update = array(
            'ID'           => $post->ID,
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => custom_box_cmyk_vs_pantone_paper_box_printing_content(),
        );

        if (!in_array($post->post_status, array('publish', 'private'), true)) {
            $update['post_status'] = 'draft';
        }

        $updated = wp_update_post($update, true);
        if (is_wp_error($updated)) {
            return $updated;
        }
    }

    custom_box_cmyk_vs_pantone_paper_box_printing_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $missing = custom_box_cmyk_vs_pantone_paper_box_printing_images($post->ID);
    update_option('custom_box_cmyk_vs_pantone_paper_box_printing_missing_images', $missing, false);

    if (empty($missing)) {
        update_post_meta($post->ID, '_custom_box_cmyk_vs_pantone_paper_box_printing_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_cmyk_vs_pantone_paper_box_printing_map()
{
    return array(
        'title'           => 'CMYK vs Pantone for Paper Box Printing: Which Should You Use?',
        'slug'            => 'cmyk-vs-pantone-paper-box-printing',
        'excerpt'         => 'Learn when to use CMYK, Pantone, or both for paper box printing. This guide explains color consistency, cost, materials, finishing, artwork setup, and proofing for B2B packaging buyers.',
        'seo_title'       => 'CMYK vs Pantone for Paper Box Printing: Color Guide',
        'seo_description' => 'Unsure whether to use CMYK or Pantone for paper boxes? Learn which option protects brand color, controls cost, and improves print consistency.',
        'focus_keyword'   => 'CMYK vs Pantone for paper box printing',
        'category'        => array(
            'name' => 'Printing & Finishing',
            'slug' => 'printing-finishing',
        ),
        'tags'            => array(
            'CMYK printing',
            'Pantone printing',
            'paper box printing',
            'custom printed paper boxes',
            'packaging artwork',
            'color matching',
            'packaging design',
        ),
    );
}

function custom_box_cmyk_vs_pantone_paper_box_printing_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'cmyk-vs-pantone-paper-box-printing-thumbnail',
            'alt'     => 'CMYK vs Pantone color guide for paper box printing',
            'title'   => 'CMYK vs Pantone for Paper Box Printing',
            'caption' => 'A practical color printing guide for custom paper box packaging.',
        ),
        'slot_1' => array(
            'base'    => 'cmyk-pantone-paper-box-color-comparison',
            'alt'     => 'CMYK and Pantone color comparison on printed paper boxes',
            'title'   => 'CMYK and Pantone Paper Box Color Comparison',
            'caption' => 'CMYK and Pantone color systems can produce different results on paper box packaging.',
        ),
        'slot_2' => array(
            'base'    => 'cmyk-printing-for-full-color-paper-boxes',
            'alt'     => 'CMYK printing for full color paper box artwork',
            'title'   => 'CMYK Printing for Full Color Paper Boxes',
            'caption' => 'CMYK is commonly used for photos, gradients, and full-color paper box artwork.',
        ),
        'slot_3' => array(
            'base'    => 'pantone-logo-color-matching-paper-box',
            'alt'     => 'Pantone logo color matching on premium paper box packaging',
            'title'   => 'Pantone Logo Color Matching for Paper Boxes',
            'caption' => 'Pantone is useful when brand logo color consistency is critical.',
        ),
        'slot_4' => array(
            'base'    => 'paper-material-impact-on-box-printing-color',
            'alt'     => 'different paper materials affecting printed box color',
            'title'   => 'Paper Material Impact on Printed Box Color',
            'caption' => 'Paper stock, coating, and surface texture can change how printed colors appear.',
        ),
        'slot_5' => array(
            'base'    => 'packaging-artwork-color-proof-checklist',
            'alt'     => 'packaging artwork and color proof checklist for paper box printing',
            'title'   => 'Packaging Artwork and Color Proof Checklist',
            'caption' => 'A production-ready artwork checklist helps reduce color problems before mass printing.',
        ),
    );
}

function custom_box_cmyk_vs_pantone_paper_box_printing_terms($post_id)
{
    $data = custom_box_cmyk_vs_pantone_paper_box_printing_map();
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

function custom_box_cmyk_vs_pantone_paper_box_printing_images($post_id)
{
    $post = get_post($post_id);
    if (!$post) {
        return array();
    }

    $content = $post->post_content;
    $missing = array();

    foreach (custom_box_cmyk_vs_pantone_paper_box_printing_image_map() as $key => $image) {
        $attachment_id = custom_box_find_cmyk_vs_pantone_paper_box_printing_attachment($image['base']);

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
        $marker = '<!-- vpn-cmyk-vs-pantone-paper-box-printing-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_cmyk_vs_pantone_paper_box_printing_figure($attachment_id, $image);
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

function custom_box_find_cmyk_vs_pantone_paper_box_printing_attachment($base)
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

    return $attachment_id;
}

function custom_box_cmyk_vs_pantone_paper_box_printing_figure($attachment_id, $image)
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

function custom_box_cmyk_vs_pantone_paper_box_printing_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_cmyk_vs_pantone_paper_box_printing_missing_images', array());
    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('CMYK vs Pantone post sync is waiting for these Media Library files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}

function custom_box_cmyk_vs_pantone_paper_box_printing_content()
{
    $content_file = __DIR__ . '/post-content/cmyk-vs-pantone-paper-box-printing.html';

    if (!is_readable($content_file)) {
        return '';
    }

    return (string) file_get_contents($content_file);
}
