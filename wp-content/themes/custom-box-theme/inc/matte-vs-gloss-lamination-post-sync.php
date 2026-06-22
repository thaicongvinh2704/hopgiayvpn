<?php
/**
 * Creates and maintains the matte vs gloss lamination packaging guide draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_matte_vs_gloss_lamination_post');
add_action('admin_notices', 'custom_box_matte_vs_gloss_lamination_notice');

function custom_box_sync_matte_vs_gloss_lamination_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_matte_vs_gloss_lamination_post();
}

function custom_box_upsert_matte_vs_gloss_lamination_post()
{
    $data = custom_box_matte_vs_gloss_lamination_map();
    $version = 'matte-vs-gloss-lamination-20260622-v1';
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
            'post_content' => custom_box_matte_vs_gloss_lamination_content(),
        ), true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $post = get_post($post_id);
    }

    if (get_post_meta($post->ID, '_custom_box_matte_vs_gloss_lamination_version', true) !== $version) {
        $update = array(
            'ID'           => $post->ID,
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => custom_box_matte_vs_gloss_lamination_content(),
        );

        if (!in_array($post->post_status, array('publish', 'private'), true)) {
            $update['post_status'] = 'draft';
        }

        $updated = wp_update_post($update, true);
        if (is_wp_error($updated)) {
            return $updated;
        }
    }

    custom_box_matte_vs_gloss_lamination_terms($post->ID);
    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $missing = custom_box_matte_vs_gloss_lamination_images($post->ID);
    update_option('custom_box_matte_vs_gloss_lamination_missing_images', $missing, false);

    if (empty($missing)) {
        update_post_meta($post->ID, '_custom_box_matte_vs_gloss_lamination_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_matte_vs_gloss_lamination_map()
{
    return array(
        'title'           => 'Matte vs Gloss Lamination for Paper Packaging: Practical Differences',
        'slug'            => 'matte-vs-gloss-lamination-for-packaging',
        'excerpt'         => 'Compare matte vs gloss lamination for paper packaging, including visual effect, color impact, industry use cases, artwork notes, QC checks and practical buyer recommendations.',
        'seo_title'       => 'Matte vs Gloss Lamination for Packaging: Which Finish Fits Your Box?',
        'seo_description' => 'Matte or gloss lamination for paper packaging? Compare color, texture, readability, durability, cost and best uses by industry before ordering custom boxes.',
        'focus_keyword'   => 'matte vs gloss lamination for packaging',
        'category'        => array(
            'name' => 'Packaging Guides',
            'slug' => 'packaging-guides',
        ),
        'tags'            => array(
            'Paper Box Printing',
            'Packaging Finishing',
            'Matte Lamination',
            'Gloss Lamination',
            'Custom Paper Boxes',
            'Printed Packaging',
        ),
    );
}

function custom_box_matte_vs_gloss_lamination_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'matte-vs-gloss-lamination-packaging',
            'alt'     => 'Matte vs gloss lamination comparison for paper packaging boxes',
            'title'   => 'Matte vs Gloss Lamination for Packaging',
            'caption' => 'A practical surface finish comparison for printed paper packaging.',
        ),
        'slot_1' => array(
            'base'    => 'matte-gloss-paper-box-surface-comparison',
            'alt'     => 'Side by side matte and gloss laminated paper boxes',
            'title'   => 'Matte and Gloss Surface Comparison',
            'caption' => 'Matte and gloss lamination create different visual effects on printed paper boxes.',
        ),
        'slot_2' => array(
            'base'    => 'matte-laminated-cosmetic-packaging-box',
            'alt'     => 'Matte laminated cosmetic paper box with premium finish',
            'title'   => 'Matte Lamination for Cosmetic Packaging',
            'caption' => 'Matte lamination is often used for clean, premium cosmetic packaging.',
        ),
        'slot_3' => array(
            'base'    => 'gloss-laminated-food-retail-packaging',
            'alt'     => 'Gloss laminated food packaging boxes with vivid colors',
            'title'   => 'Gloss Lamination for Retail Food Packaging',
            'caption' => 'Gloss lamination helps colorful retail packaging appear brighter on shelf.',
        ),
        'slot_4' => array(
            'base'    => 'lamination-artwork-qc-check-packaging',
            'alt'     => 'Packaging team checking laminated artwork and color proof',
            'title'   => 'Lamination Artwork and QC Check',
            'caption' => 'Printed samples should be checked after lamination before bulk production.',
        ),
        'slot_5' => array(
            'base'    => 'matte-gloss-finish-decision-samples',
            'alt'     => 'Paper packaging finish samples for matte and gloss decision',
            'title'   => 'Matte vs Gloss Finish Decision Samples',
            'caption' => 'Physical samples help buyers choose the right lamination for real packaging use.',
        ),
    );
}

function custom_box_matte_vs_gloss_lamination_terms($post_id)
{
    $data = custom_box_matte_vs_gloss_lamination_map();
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

function custom_box_matte_vs_gloss_lamination_images($post_id)
{
    $post = get_post($post_id);
    if (!$post) {
        return array();
    }

    $content = $post->post_content;
    $missing = array();

    foreach (custom_box_matte_vs_gloss_lamination_image_map() as $key => $image) {
        $attachment_id = custom_box_matte_vs_gloss_lamination_attachment($image['base']);

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

        $marker = '<!-- vpn-matte-vs-gloss-lamination-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_matte_vs_gloss_lamination_figure($attachment_id, $image);
        $slot = '<!-- IMAGE_SLOT_' . substr($key, -1) . ' -->';

        if (false !== strpos($content, $marker)) {
            $content = preg_replace(
                '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is',
                $figure,
                $content,
                1
            );
        } elseif (false !== strpos($content, $slot)) {
            $content = str_replace($slot, $figure, $content);
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

function custom_box_matte_vs_gloss_lamination_attachment($base)
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

function custom_box_matte_vs_gloss_lamination_figure($attachment_id, $image)
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

function custom_box_matte_vs_gloss_lamination_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_matte_vs_gloss_lamination_missing_images', array());
    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Matte vs Gloss lamination post sync is waiting for these Media Library files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}

function custom_box_matte_vs_gloss_lamination_content()
{
    $content_file = __DIR__ . '/post-content/matte-vs-gloss-lamination-for-packaging.html';

    if (!is_readable($content_file)) {
        return '';
    }

    return (string) file_get_contents($content_file);
}
