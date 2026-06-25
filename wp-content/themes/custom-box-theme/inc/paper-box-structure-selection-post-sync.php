<?php
/**
 * Maintains metadata and images for the paper box structure selection guide.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_paper_box_structure_selection_post');
add_action('admin_notices', 'custom_box_paper_box_structure_selection_notice');

function custom_box_sync_paper_box_structure_selection_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_paper_box_structure_selection_post();
}

function custom_box_upsert_paper_box_structure_selection_post()
{
    $data = custom_box_paper_box_structure_selection_map();
    $version = 'paper-box-structure-selection-20260625-v1';
    $post = custom_box_find_paper_box_structure_selection_post($data);

    if ($post && 'trash' === $post->post_status) {
        return 0;
    }

    if (!$post) {
        update_option('custom_box_paper_box_structure_selection_missing_slots', array('draft post'), false);
        return 0;
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

    custom_box_paper_box_structure_selection_terms($post->ID);

    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $image_result = custom_box_paper_box_structure_selection_images($post->ID);
    update_option('custom_box_paper_box_structure_selection_missing_images', $image_result['missing_images'], false);
    update_option('custom_box_paper_box_structure_selection_missing_slots', $image_result['missing_slots'], false);

    if (empty($image_result['missing_images']) && empty($image_result['missing_slots'])) {
        update_post_meta($post->ID, '_custom_box_paper_box_structure_selection_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_find_paper_box_structure_selection_post($data)
{
    global $wpdb;

    $post_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'post'
             AND post_name = %s
             AND post_status IN ('draft', 'pending', 'private', 'publish')
             ORDER BY ID DESC LIMIT 1",
            $data['slug']
        )
    );

    if ($post_id) {
        return get_post($post_id);
    }

    $matches = get_posts(array(
        'post_type'      => 'post',
        'post_status'    => array('draft', 'pending', 'private', 'publish'),
        'title'          => $data['title'],
        'posts_per_page' => 1,
    ));

    return !empty($matches) ? $matches[0] : null;
}

function custom_box_paper_box_structure_selection_map()
{
    return array(
        'title'           => 'How to Choose the Right Paper Box Structure for a Product',
        'slug'            => 'how-to-choose-paper-box-structure-product',
        'excerpt'         => 'Learn how to choose the right paper box structure for a product based on weight, fragility, sales channel, inserts, printing, sample testing, and buyer RFQ preparation.',
        'seo_title'       => 'How to Choose the Right Paper Box Structure for a Product',
        'seo_description' => 'A practical B2B guide to choosing the right paper box structure based on product weight, fragility, sales channel, inserts, printing, sampling, and shipping needs.',
        'focus_keyword'   => 'how to choose box structure for a product',
        'category'        => array(
            'name' => 'Blog / Packaging Guide',
            'slug' => 'blog-packaging-guide',
        ),
        'tags'            => array(
            'paper box structure',
            'custom packaging',
            'folding carton',
            'rigid box',
            'corrugated mailer',
            'packaging design',
            'packaging sourcing',
        ),
    );
}

function custom_box_paper_box_structure_selection_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'choose-paper-box-structure-product-thumbnail',
            'alt'     => 'paper box structure selection guide for product packaging',
            'title'   => 'Choosing the Right Paper Box Structure',
            'caption' => 'A practical visual guide to selecting paper box structures based on product needs.',
        ),
        'slot_1' => array(
            'base'    => 'paper-box-structure-options-comparison',
            'alt'     => 'folding carton rigid box corrugated mailer and paper tube comparison',
            'title'   => 'Paper Box Structure Options Comparison',
            'caption' => 'Different paper box structures fit different product weights, sales channels, and presentation needs.',
        ),
        'slot_2' => array(
            'base'    => 'product-fit-insert-paper-box-structure',
            'alt'     => 'product fit and paper insert inside custom box structure',
            'title'   => 'Product Fit and Insert Planning',
            'caption' => 'Inserts and internal fit often decide whether a box structure protects the product properly.',
        ),
        'slot_3' => array(
            'base'    => 'paper-box-dieline-structure-qc-check',
            'alt'     => 'paper box dieline and structure sample quality check',
            'title'   => 'Dieline and Structure QC Check',
            'caption' => 'Dieline review, fold lines, glue areas, and sample testing help prevent structure problems.',
        ),
        'slot_4' => array(
            'base'    => 'paper-box-structure-rfq-brief',
            'alt'     => 'buyer RFQ brief for custom paper box structure',
            'title'   => 'Paper Box Structure RFQ Brief',
            'caption' => 'A clear product and structure brief helps the supplier recommend a more suitable packaging solution.',
        ),
    );
}

function custom_box_paper_box_structure_selection_terms($post_id)
{
    $data = custom_box_paper_box_structure_selection_map();
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

function custom_box_paper_box_structure_selection_images($post_id)
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

    foreach (custom_box_paper_box_structure_selection_image_map() as $key => $image) {
        $attachment_id = custom_box_paper_box_structure_selection_attachment($image['base']);

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
        $marker = '<!-- vpn-paper-box-structure-selection-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_paper_box_structure_selection_figure($attachment_id, $image);
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

function custom_box_paper_box_structure_selection_attachment($base)
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

function custom_box_paper_box_structure_selection_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}

function custom_box_paper_box_structure_selection_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing_images = get_option('custom_box_paper_box_structure_selection_missing_images', array());
    $missing_slots = get_option('custom_box_paper_box_structure_selection_missing_slots', array());

    if (empty($missing_images) && empty($missing_slots)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Paper box structure selection post sync needs attention:', 'custom-box-theme') . ' ';

    if (!empty($missing_images) && is_array($missing_images)) {
        echo esc_html__('missing images', 'custom-box-theme') . ': ' . esc_html(implode(', ', $missing_images)) . '. ';
    }

    if (!empty($missing_slots) && is_array($missing_slots)) {
        echo esc_html__('missing slots', 'custom-box-theme') . ': ' . esc_html(implode(', ', $missing_slots)) . '.';
    }

    echo '</p></div>';
}
