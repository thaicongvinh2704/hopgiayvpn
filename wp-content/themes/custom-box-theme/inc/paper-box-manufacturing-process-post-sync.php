<?php
/**
 * Syncs the Paper Box Manufacturing Process post images after media upload.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_box_manufacturing_process_post');

function custom_box_sync_paper_box_manufacturing_process_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $post_data = custom_box_paper_box_manufacturing_process_post_map();
    $post = get_page_by_path($post_data['slug'], OBJECT, 'post');

    if (!$post || 'trash' === $post->post_status) {
        update_option('custom_box_paper_box_manufacturing_process_missing_images', array(), false);
        return;
    }

    custom_box_update_paper_box_manufacturing_process_post_terms($post->ID);
    custom_box_update_paper_box_manufacturing_process_post_seo($post->ID);

    $found = array();
    $missing = array();

    foreach (custom_box_paper_box_manufacturing_process_image_map() as $key => $image) {
        $attachment_id = custom_box_paper_box_manufacturing_process_find_attachment_by_base($image['base']);

        if (!$attachment_id) {
            $missing[] = $image['base'];
            continue;
        }

        $url = wp_get_attachment_url($attachment_id);

        if (!$url) {
            $missing[] = $image['base'];
            continue;
        }

        custom_box_paper_box_manufacturing_process_update_attachment_metadata($attachment_id, $image);

        $found[$key] = array(
            'id'   => $attachment_id,
            'url'  => $url,
            'alt'  => $image['alt'],
            'base' => $image['base'],
        );
    }

    update_option('custom_box_paper_box_manufacturing_process_missing_images', $missing, false);

    if (!empty($found['featured']['id']) && (int) get_post_thumbnail_id($post->ID) !== (int) $found['featured']['id']) {
        set_post_thumbnail($post->ID, $found['featured']['id']);
    }

    $updated_content = custom_box_insert_paper_box_manufacturing_process_images($post->post_content, $found);

    if ($updated_content !== $post->post_content) {
        wp_update_post(array(
            'ID'           => $post->ID,
            'post_content' => $updated_content,
        ));
    }
}

function custom_box_paper_box_manufacturing_process_post_map()
{
    return array(
        'title'         => 'Paper Box Manufacturing Process: Printing, Cutting, Folding and Quality Control',
        'slug'          => 'paper-box-manufacturing-process',
        'categories'    => array('Packaging Guides'),
        'tags'          => array('paper box manufacturing process', 'paper box production', 'paper box printing', 'die cutting paper boxes', 'paper box quality control'),
        'seo_title'     => 'Paper Box Manufacturing Process: Printing, Cutting and Folding',
        'seo_description' => 'Learn the paper box manufacturing process from artwork checking and paper material selection to printing, die cutting, folding, gluing and quality control.',
        'seo_focus_keyword' => 'paper box manufacturing process',
    );
}

function custom_box_update_paper_box_manufacturing_process_post_seo($post_id)
{
    $post_data = custom_box_paper_box_manufacturing_process_post_map();

    update_post_meta($post_id, 'rank_math_title', $post_data['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $post_data['seo_description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $post_data['seo_focus_keyword']);
}

function custom_box_update_paper_box_manufacturing_process_post_terms($post_id)
{
    $post_data = custom_box_paper_box_manufacturing_process_post_map();
    $category_ids = array();

    foreach ($post_data['categories'] as $category) {
        $term = term_exists($category, 'category');

        if (!$term) {
            $term = wp_insert_term($category, 'category');
        }

        if (!is_wp_error($term) && !empty($term['term_id'])) {
            $category_ids[] = (int) $term['term_id'];
        }
    }

    if ($category_ids) {
        wp_set_post_terms($post_id, $category_ids, 'category', false);
    }

    wp_set_post_terms($post_id, $post_data['tags'], 'post_tag', false);
}

add_action('admin_notices', 'custom_box_paper_box_manufacturing_process_sync_notice');

function custom_box_paper_box_manufacturing_process_sync_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_paper_box_manufacturing_process_missing_images', array());

    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Paper Box Manufacturing Process image sync:</strong> missing media files: ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}

function custom_box_paper_box_manufacturing_process_image_map()
{
    return array(
        'featured' => array(
            'base'  => 'paper-box-manufacturing-process-thumbnail',
            'alt'   => 'paper box manufacturing process printing cutting folding and quality control',
            'title' => 'Paper Box Manufacturing Process Thumbnail',
        ),
        'artwork' => array(
            'base'  => 'artwork-and-dieline-checking-for-paper-box-production',
            'alt'   => 'artwork and dieline checking for paper box production',
            'title' => 'Artwork and Dieline Checking for Paper Box Production',
        ),
        'materials' => array(
            'base'  => 'paper-materials-for-paper-box-manufacturing',
            'alt'   => 'paper materials for paper box manufacturing',
            'title' => 'Paper Materials for Paper Box Manufacturing',
        ),
        'printing' => array(
            'base'  => 'printing-paper-sheets-for-paper-box-packaging',
            'alt'   => 'printing paper sheets for paper box packaging',
            'title' => 'Printing Paper Sheets for Paper Box Packaging',
        ),
        'forming' => array(
            'base'  => 'die-cutting-folding-and-gluing-paper-boxes',
            'alt'   => 'die cutting folding and gluing paper boxes',
            'title' => 'Die Cutting Folding and Gluing Paper Boxes',
        ),
        'quality' => array(
            'base'  => 'vpn-paper-box-quality-control-and-packaging-production',
            'alt'   => 'VPN Paper Box quality control and packaging production',
            'title' => 'VPN Paper Box Quality Control and Packaging Production',
        ),
    );
}

function custom_box_paper_box_manufacturing_process_find_attachment_by_base($base)
{
    $attachment = get_page_by_path($base, OBJECT, 'attachment');

    if ($attachment) {
        return (int) $attachment->ID;
    }

    global $wpdb;

    $like = '%' . $wpdb->esc_like('/' . $base . '.') . '%';
    $attachment_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
            $like
        )
    );

    if ($attachment_id) {
        return (int) $attachment_id;
    }

    $like = $wpdb->esc_like($base) . '%';

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_name LIKE %s ORDER BY ID DESC LIMIT 1",
            $like
        )
    );
}

function custom_box_paper_box_manufacturing_process_update_attachment_metadata($attachment_id, $image)
{
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);

    wp_update_post(array(
        'ID'         => $attachment_id,
        'post_title' => $image['title'],
    ));
}

function custom_box_insert_paper_box_manufacturing_process_images($content, $found)
{
    $placements = array(
        'artwork' => 'Artwork and dieline checking is one of the most important stages',
        'materials' => 'Paper material affects almost every part of the final packaging',
        'printing' => 'Printing is one of the most visible parts of the paper box manufacturing process',
        'forming' => 'After die-cutting, the paper box begins to take its final shape',
        'quality' => 'Quality control is one of the most important parts of paper box manufacturing',
    );

    foreach ($placements as $key => $paragraph_start) {
        if (empty($found[$key])) {
            continue;
        }

        $content = custom_box_remove_paper_box_manufacturing_process_image($content, $found[$key]['base']);
        $paragraph_pos = strpos($content, $paragraph_start);

        if (false === $paragraph_pos) {
            continue;
        }

        $paragraph_end = strpos($content, '</span>', $paragraph_pos);

        if (false === $paragraph_end) {
            continue;
        }

        $paragraph_end += strlen('</span>');
        $image_html = custom_box_paper_box_manufacturing_process_image_html($found[$key]['url'], $found[$key]['alt']);
        $content = substr_replace($content, $image_html, $paragraph_end, 0);
    }

    return $content;
}

function custom_box_remove_paper_box_manufacturing_process_image($content, $base)
{
    $pattern = '#\s*<img\b[^>]*src=["\'][^"\']*' . preg_quote($base, '#') . '[^"\']*["\'][^>]*>\s*#i';

    return preg_replace($pattern, "\n\n", $content);
}

function custom_box_paper_box_manufacturing_process_image_html($url, $alt)
{
    return "\n\n<img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($alt) . "\" loading=\"lazy\" />";
}
