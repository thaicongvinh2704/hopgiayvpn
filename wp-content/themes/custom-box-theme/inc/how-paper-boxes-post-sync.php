<?php
/**
 * One-off metadata and image sync for the paper box production guide draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_how_paper_boxes_post');

function custom_box_sync_how_paper_boxes_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $sync_version = 'how-paper-boxes-are-made-20260606-v1';
    $post_data = custom_box_how_paper_boxes_post_map();
    $post = get_page_by_path($post_data['slug'], OBJECT, 'post');

    if (!$post) {
        $drafts = get_posts(array(
            'post_type'      => 'post',
            'post_status'    => array('draft', 'pending', 'private'),
            'title'          => $post_data['title'],
            'posts_per_page' => 1,
        ));

        $post = !empty($drafts) ? $drafts[0] : null;
    }

    if ($post && 'trash' === $post->post_status) {
        return;
    }

    if (!$post) {
        $post_id = wp_insert_post(array(
            'post_title'   => $post_data['title'],
            'post_name'    => $post_data['slug'],
            'post_type'    => 'post',
            'post_status'  => 'draft',
            'post_excerpt' => $post_data['excerpt'],
            'post_content' => '',
        ));

        if (!$post_id || is_wp_error($post_id)) {
            return;
        }

        $post = get_post($post_id);
    }

    $missing_option = get_option('custom_box_how_paper_boxes_missing_images', array());
    if (
        get_post_meta($post->ID, '_custom_box_how_paper_boxes_sync_version', true) === $sync_version
        && empty($missing_option)
    ) {
        return;
    }

    custom_box_update_how_paper_boxes_post_details($post->ID, $sync_version);
    custom_box_update_how_paper_boxes_post_seo($post->ID);
    custom_box_update_how_paper_boxes_post_terms($post->ID);

    $images = custom_box_how_paper_boxes_image_map();
    $found = array();
    $missing = array();

    foreach ($images as $key => $image) {
        $attachment_id = custom_box_how_paper_boxes_find_attachment_by_base($image['base']);

        if (!$attachment_id) {
            $missing[] = $image['base'];
            continue;
        }

        $url = wp_get_attachment_url($attachment_id);

        if (!$url) {
            $missing[] = $image['base'];
            continue;
        }

        custom_box_how_paper_boxes_update_attachment_metadata($attachment_id, $image);

        $found[$key] = array(
            'id'      => $attachment_id,
            'url'     => $url,
            'alt'     => $image['alt'],
            'caption' => isset($image['caption']) ? $image['caption'] : '',
        );
    }

    update_option('custom_box_how_paper_boxes_missing_images', $missing, false);

    if (!empty($found['featured']['id']) && (int) get_post_thumbnail_id($post->ID) !== (int) $found['featured']['id']) {
        set_post_thumbnail($post->ID, $found['featured']['id']);
    }

    $post = get_post($post->ID);
    $updated_content = custom_box_insert_how_paper_boxes_figures($post->post_content, $found);

    if ($updated_content !== $post->post_content) {
        wp_update_post(array(
            'ID'           => $post->ID,
            'post_content' => $updated_content,
        ));
    }

    update_post_meta($post->ID, '_custom_box_how_paper_boxes_sync_version', $sync_version);
}

function custom_box_how_paper_boxes_post_map()
{
    return array(
        'title'         => 'How Paper Boxes Are Made: From Paper Material to Finished Packaging',
        'slug'          => 'how-paper-boxes-are-made',
        'excerpt'       => 'Learn how paper boxes are made, from material selection, dieline and artwork preparation to printing, finishing, quality control and final packaging.',
        'seo_title'     => 'How Paper Boxes Are Made: Paper Material to Finished Packaging',
        'seo_description' => 'Learn how paper boxes are made from paper material, dieline and artwork to printing, finishing, quality control and finished packaging.',
        'focus_keyword' => 'how paper boxes are made',
        'categories'    => array(
            array(
                'name' => 'Packaging Guides',
                'slug' => 'packaging-guides',
            ),
        ),
        'tags'          => array(
            'How Paper Boxes Are Made',
            'Paper Box Manufacturing',
            'Paper Packaging Production',
            'Custom Paper Boxes',
            'Packaging Guides',
            'Paper Box Manufacturer',
        ),
    );
}

function custom_box_update_how_paper_boxes_post_details($post_id, $sync_version)
{
    $post = get_post($post_id);
    $post_data = custom_box_how_paper_boxes_post_map();

    if (!$post) {
        return;
    }

    $post_update = array(
        'ID'           => $post_id,
        'post_title'   => $post_data['title'],
        'post_name'    => $post_data['slug'],
        'post_excerpt' => $post_data['excerpt'],
    );

    if (!in_array($post->post_status, array('publish', 'private'), true)) {
        $post_update['post_status'] = 'draft';
    }

    wp_update_post($post_update);
}

function custom_box_update_how_paper_boxes_post_seo($post_id)
{
    $post_data = custom_box_how_paper_boxes_post_map();

    update_post_meta($post_id, 'rank_math_title', $post_data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $post_data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $post_data['focus_keyword']);
}

function custom_box_update_how_paper_boxes_post_terms($post_id)
{
    $post_data = custom_box_how_paper_boxes_post_map();
    $category_ids = array();

    foreach ($post_data['categories'] as $category) {
        $term = get_term_by('slug', $category['slug'], 'category');

        if (!$term) {
            $created = wp_insert_term($category['name'], 'category', array('slug' => $category['slug']));

            if (is_wp_error($created)) {
                continue;
            }

            $category_ids[] = (int) $created['term_id'];
            continue;
        }

        $category_ids[] = (int) $term->term_id;
    }

    if (!empty($category_ids)) {
        wp_set_post_categories($post_id, $category_ids, false);
    }

    wp_set_post_terms($post_id, $post_data['tags'], 'post_tag', false);
}

add_action('admin_notices', 'custom_box_how_paper_boxes_sync_notice');

function custom_box_how_paper_boxes_sync_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_how_paper_boxes_missing_images', array());

    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('How Paper Boxes post sync is waiting for these uploaded media files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}

function custom_box_how_paper_boxes_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'how-paper-boxes-are-made-thumbnail',
            'alt'     => 'How paper boxes are made from paper material to finished packaging',
            'title'   => 'How Paper Boxes Are Made Thumbnail',
            'caption' => 'A practical overview of the paper box production process from material to finished packaging.',
        ),
        'materials' => array(
            'base'    => 'paper-materials-used-for-making-paper-boxes',
            'alt'     => 'Paper materials used for making paper boxes',
            'title'   => 'Paper Materials Used for Making Paper Boxes',
            'caption' => 'Paperboard, kraft paper, greyboard and corrugated board are selected based on structure, printing and product protection needs.',
        ),
        'dieline' => array(
            'base'    => 'paper-box-dieline-for-packaging-production',
            'alt'     => 'Paper box dieline for packaging production',
            'title'   => 'Paper Box Dieline for Packaging Production',
            'caption' => 'The dieline defines panel size, fold lines, glue areas, bleed and cut positions before printing starts.',
        ),
        'artwork' => array(
            'base'    => 'packaging-artwork-preparation-for-printed-paper-boxes',
            'alt'     => 'Packaging artwork preparation for printed paper boxes',
            'title'   => 'Packaging Artwork Preparation for Printed Paper Boxes',
            'caption' => 'Artwork preparation should match the confirmed dieline, print colors, logo placement and finishing requirements.',
        ),
        'quality' => array(
            'base'    => 'paper-box-quality-control-during-packaging-production',
            'alt'     => 'Paper box quality control during packaging production',
            'title'   => 'Paper Box Quality Control During Packaging Production',
            'caption' => 'Quality control checks printing, finishing, folding, gluing, inserts and packing before the order is completed.',
        ),
        'finished' => array(
            'base'    => 'finished-paper-boxes-for-product-packaging',
            'alt'     => 'Finished paper boxes for product packaging',
            'title'   => 'Finished Paper Boxes for Product Packaging',
            'caption' => 'Finished paper boxes are packed after printing, finishing, forming and final inspection.',
        ),
    );
}

function custom_box_how_paper_boxes_find_attachment_by_base($base)
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

function custom_box_how_paper_boxes_update_attachment_metadata($attachment_id, $image)
{
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);

    wp_update_post(array(
        'ID'           => $attachment_id,
        'post_title'   => $image['title'],
        'post_excerpt' => isset($image['caption']) ? $image['caption'] : '',
    ));
}

function custom_box_insert_how_paper_boxes_figures($content, $found)
{
    $placements = array(
        'materials' => 'Step 2: Choosing the Right Paper Material',
        'dieline'   => 'Step 3: Creating the Box Structure and Dieline',
        'artwork'   => 'Step 4: Preparing Artwork for Printing',
        'quality'   => 'Step 11: Quality Control During Production',
        'finished'  => 'Step 12: Packing Finished Paper Boxes',
    );

    foreach ($placements as $key => $heading) {
        if (empty($found[$key])) {
            continue;
        }

        $marker = '<!-- vpn-how-paper-boxes-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_how_paper_boxes_figure_html($found[$key]);

        if (false !== strpos($content, $marker)) {
            $content = preg_replace(
                '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is',
                $figure,
                $content,
                1
            );
            continue;
        }

        $pattern = '/(<h2[^>]*>\s*' . preg_quote($heading, '/') . '\s*<\/h2>)/i';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '$1' . "\n" . $figure, $content, 1);
        }
    }

    return $content;
}

function custom_box_how_paper_boxes_figure_html($image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url($image['url']),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}
