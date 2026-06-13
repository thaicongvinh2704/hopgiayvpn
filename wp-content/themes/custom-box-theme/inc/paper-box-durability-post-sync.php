<?php
/**
 * Maintains metadata and images for the paper box durability guide.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_box_durability_post');

function custom_box_sync_paper_box_durability_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_paper_box_durability_post();
}

function custom_box_upsert_paper_box_durability_post()
{
    $data = custom_box_paper_box_durability_post_map();
    $post = get_page_by_path($data['slug'], OBJECT, 'post');

    if (!$post || 'trash' === $post->post_status) {
        return 0;
    }

    $updated = wp_update_post(array(
        'ID'           => $post->ID,
        'post_title'   => $data['title'],
        'post_name'    => $data['slug'],
        'post_excerpt' => $data['excerpt'],
    ), true);

    if (is_wp_error($updated)) {
        return $updated;
    }

    custom_box_update_paper_box_durability_terms($post->ID);

    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $missing = custom_box_sync_paper_box_durability_images($post->ID);
    update_option('custom_box_paper_box_durability_missing_images', $missing, false);

    return (int) $post->ID;
}

function custom_box_paper_box_durability_post_map()
{
    return array(
        'title'           => 'How to Improve Paper Box Durability for Product Packaging',
        'slug'            => 'how-to-improve-paper-box-durability',
        'excerpt'         => 'Learn how to improve paper box durability for product packaging by choosing better materials, structures, inserts, glue, lamination and export packing methods.',
        'seo_title'       => 'How to Improve Paper Box Durability for Product Packaging',
        'seo_description' => 'Learn how to improve paper box durability with better materials, structure, inserts, glue, lamination and export packing for product packaging.',
        'focus_keyword'   => 'how to improve paper box durability',
        'category'        => array(
            'name' => 'Paper Packaging Guide',
            'slug' => 'paper-packaging-guide',
        ),
        'tags'            => array(
            'paper box durability',
            'paper packaging',
            'product packaging',
            'paperboard',
            'box structure',
            'packaging inserts',
            'lamination',
            'export packing',
        ),
    );
}

function custom_box_paper_box_durability_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'improve-paper-box-durability-thumbnail',
            'alt'     => 'paper box durability guide for product packaging',
            'title'   => 'How to Improve Paper Box Durability',
            'caption' => 'A practical guide to improving paper box durability for product packaging.',
        ),
        'slot_1' => array(
            'base'    => 'durable-paper-box-material-comparison',
            'alt'     => 'durable paperboard material comparison for paper boxes',
            'title'   => 'Durable Paperboard Material Comparison',
            'caption' => 'Different paperboard materials provide different levels of stiffness, surface quality and protection.',
        ),
        'slot_2' => array(
            'base'    => 'paper-box-structure-strength-test',
            'alt'     => 'paper box structure strength test for product packaging',
            'title'   => 'Paper Box Structure Strength Test',
            'caption' => 'Box structure helps distribute product weight and improve packaging durability.',
        ),
        'slot_3' => array(
            'base'    => 'paper-box-inserts-for-product-protection',
            'alt'     => 'paper box inserts for product protection and stability',
            'title'   => 'Paper Box Inserts for Product Protection',
            'caption' => 'Inserts reduce product movement and improve internal protection during handling and shipping.',
        ),
        'slot_4' => array(
            'base'    => 'paper-box-lamination-and-export-packing',
            'alt'     => 'paper box lamination and export packing for durability',
            'title'   => 'Paper Box Lamination and Export Packing',
            'caption' => 'Surface finishing and export packing help protect boxes from scratches, compression and transport damage.',
        ),
    );
}

function custom_box_update_paper_box_durability_terms($post_id)
{
    $data = custom_box_paper_box_durability_post_map();
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

function custom_box_sync_paper_box_durability_images($post_id)
{
    $post = get_post($post_id);

    if (!$post) {
        return array();
    }

    $content = $post->post_content;
    $missing = array();

    foreach (custom_box_paper_box_durability_image_map() as $key => $image) {
        $attachment_id = custom_box_find_paper_box_durability_attachment($image['base']);

        if (!$attachment_id) {
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

        $marker = '<!-- vpn-paper-box-durability-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_paper_box_durability_figure($attachment_id, $image);
        $slot_number = substr($key, -1);
        $slot_pattern = '/<span\b[^>]*>\s*<!--\s*IMAGE_SLOT_' . preg_quote($slot_number, '/') . '\s*-->\s*<\/span>/i';
        $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

        if (false !== strpos($content, $marker)) {
            $content = preg_replace($marker_pattern, $figure, $content, 1);
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

function custom_box_find_paper_box_durability_attachment($base)
{
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

function custom_box_paper_box_durability_figure($attachment_id, $image)
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

add_action('admin_notices', 'custom_box_paper_box_durability_sync_notice');

function custom_box_paper_box_durability_sync_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_paper_box_durability_missing_images', array());

    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Paper box durability post sync is waiting for these media files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}
