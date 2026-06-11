<?php
/**
 * Maintains metadata and images for the paper thickness packaging guide.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_thickness_post');

function custom_box_sync_paper_thickness_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_paper_thickness_post();
}

function custom_box_upsert_paper_thickness_post()
{
    $data = custom_box_paper_thickness_post_map();
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

    custom_box_update_paper_thickness_terms($post->ID);

    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $missing = custom_box_sync_paper_thickness_images($post->ID);
    update_option('custom_box_paper_thickness_missing_images', $missing, false);

    return (int) $post->ID;
}

function custom_box_paper_thickness_post_map()
{
    return array(
        'title'           => 'What Paper Thickness Is Used for Product Packaging?',
        'slug'            => 'what-paper-thickness-is-used-for-packaging',
        'excerpt'         => 'Learn what paper thickness is used for product packaging, including GSM, caliper, stiffness, product weight, box structure and sample testing for B2B packaging projects.',
        'seo_title'       => 'What Paper Thickness Is Used for Product Packaging?',
        'seo_description' => 'Learn what paper thickness is used for packaging, including GSM, caliper, stiffness, product weight, box structure and sample testing.',
        'focus_keyword'   => 'what paper thickness is used for packaging',
        'category'        => array(
            'name' => 'Paper Packaging Guide',
            'slug' => 'paper-packaging-guide',
        ),
        'tags'            => array(
            'paper thickness',
            'packaging GSM',
            'paperboard thickness',
            'product packaging',
            'paper box material',
            'folding carton',
            'rigid box',
            'packaging guide',
        ),
    );
}

function custom_box_paper_thickness_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'paper-thickness-used-for-product-packaging-thumbnail',
            'alt'     => 'paper thickness guide for product packaging',
            'title'   => 'What Paper Thickness Is Used for Product Packaging',
            'caption' => 'A practical guide to choosing paper thickness, GSM and stiffness for product packaging.',
        ),
        'slot_1' => array(
            'base'    => 'paperboard-gsm-thickness-comparison',
            'alt'     => 'paperboard GSM and thickness comparison for packaging',
            'title'   => 'Paperboard GSM and Thickness Comparison',
            'caption' => 'GSM, caliper and stiffness should be reviewed together when choosing packaging material.',
        ),
        'slot_2' => array(
            'base'    => 'common-paper-thickness-ranges-for-packaging',
            'alt'     => 'common paper thickness ranges for product packaging',
            'title'   => 'Common Paper Thickness Ranges for Packaging',
            'caption' => 'Different packaging formats require different material thickness and board structures.',
        ),
        'slot_3' => array(
            'base'    => 'folding-carton-paperboard-sample-check',
            'alt'     => 'folding carton paperboard sample for thickness and stiffness testing',
            'title'   => 'Folding Carton Paperboard Sample Check',
            'caption' => 'A structural sample helps verify stiffness, folding quality and box shape before production.',
        ),
        'slot_4' => array(
            'base'    => 'product-weight-and-packaging-material-selection',
            'alt'     => 'product weight and paper packaging material selection',
            'title'   => 'Product Weight and Packaging Material Selection',
            'caption' => 'Product weight, box size and shipping method affect the final paperboard choice.',
        ),
    );
}

function custom_box_update_paper_thickness_terms($post_id)
{
    $data = custom_box_paper_thickness_post_map();
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

function custom_box_sync_paper_thickness_images($post_id)
{
    $post = get_post($post_id);

    if (!$post) {
        return array();
    }

    $content = $post->post_content;
    $missing = array();

    foreach (custom_box_paper_thickness_image_map() as $key => $image) {
        $attachment_id = custom_box_find_paper_thickness_attachment($image['base']);

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

        $marker = '<!-- vpn-paper-thickness-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_paper_thickness_figure($attachment_id, $image);
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

function custom_box_find_paper_thickness_attachment($base)
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

function custom_box_paper_thickness_figure($attachment_id, $image)
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

add_action('admin_notices', 'custom_box_paper_thickness_sync_notice');

function custom_box_paper_thickness_sync_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_paper_thickness_missing_images', array());

    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Paper thickness post sync is waiting for these media files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}
