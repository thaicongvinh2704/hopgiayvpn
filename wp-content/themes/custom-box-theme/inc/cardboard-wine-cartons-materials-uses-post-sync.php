<?php
/**
 * Creates and maintains the cardboard wine cartons blog draft.
 *
 * @package Custom_Box_Theme
 */

add_action('admin_init', 'custom_box_sync_cardboard_wine_cartons_post');
add_action('admin_notices', 'custom_box_cardboard_wine_cartons_post_notice');

function custom_box_sync_cardboard_wine_cartons_post()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    custom_box_upsert_cardboard_wine_cartons_post();
}

function custom_box_upsert_cardboard_wine_cartons_post()
{
    $data = custom_box_cardboard_wine_cartons_post_map();
    $version = 'cardboard-wine-cartons-materials-uses-20260618-v1';
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
            'post_content' => custom_box_cardboard_wine_cartons_post_content(),
        ), true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $post = get_post($post_id);
    }

    if (get_post_meta($post->ID, '_custom_box_cardboard_wine_cartons_sync_version', true) !== $version) {
        $update = array(
            'ID'           => $post->ID,
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_excerpt' => $data['excerpt'],
            'post_content' => custom_box_cardboard_wine_cartons_post_content(),
        );

        if (!in_array($post->post_status, array('publish', 'private'), true)) {
            $update['post_status'] = 'draft';
        }

        $updated = wp_update_post($update, true);

        if (is_wp_error($updated)) {
            return $updated;
        }
    }

    custom_box_cardboard_wine_cartons_post_terms($post->ID);

    update_post_meta($post->ID, 'rank_math_title', $data['seo_title']);
    update_post_meta($post->ID, 'rank_math_description', $data['seo_description']);
    update_post_meta($post->ID, 'rank_math_focus_keyword', $data['focus_keyword']);

    $missing = custom_box_sync_cardboard_wine_cartons_images($post->ID);
    update_option('custom_box_cardboard_wine_cartons_missing_images', $missing, false);

    if (empty($missing)) {
        update_post_meta($post->ID, '_custom_box_cardboard_wine_cartons_sync_version', $version);
    }

    return (int) $post->ID;
}

function custom_box_cardboard_wine_cartons_post_map()
{
    return array(
        'title'           => 'What Are Cardboard Wine Cartons? Materials and Uses Explained',
        'slug'            => 'cardboard-wine-cartons-materials-uses',
        'excerpt'         => 'Learn what cardboard wine cartons are, which materials are used, and how to choose the right wine carton for U.S. shipping, retail, gifting, wine clubs, and wholesale packaging.',
        'seo_title'       => 'Cardboard Wine Cartons: Materials, Uses & Buyer Tips',
        'seo_description' => 'Choosing wine cartons for U.S. retail, wine clubs or shipping? Compare corrugated, paperboard, rigid boxes, inserts and best-use cases before you order.',
        'focus_keyword'   => 'what are cardboard wine cartons materials and uses',
        'category'        => array(
            'name' => 'Packaging Guides',
            'slug' => 'packaging-guides',
        ),
        'tags'            => array(
            'wine packaging',
            'cardboard wine cartons',
            'corrugated wine boxes',
            'wine shipping cartons',
            'rigid wine boxes',
            'beverage packaging',
            'custom paper boxes',
        ),
    );
}

function custom_box_cardboard_wine_cartons_image_map()
{
    return array(
        'featured' => array(
            'base'    => 'cardboard-wine-cartons-materials-uses-thumbnail',
            'alt'     => 'cardboard wine cartons materials and uses guide',
            'title'   => 'Cardboard Wine Cartons Materials and Uses',
            'caption' => 'A practical guide to wine carton materials, structures, and applications.',
        ),
        'slot_1' => array(
            'base'    => 'cardboard-wine-carton-types-comparison',
            'alt'     => 'comparison of cardboard wine carton types for shipping retail and gifting',
            'title'   => 'Cardboard Wine Carton Types Comparison',
            'caption' => 'Cardboard wine cartons can be designed for shipping, retail display, or premium gifting.',
        ),
        'slot_2' => array(
            'base'    => 'wine-carton-materials-corrugated-paperboard-rigid',
            'alt'     => 'corrugated paperboard and rigid board materials for wine cartons',
            'title'   => 'Wine Carton Materials: Corrugated, Paperboard and Rigid Board',
            'caption' => 'Different paper-based materials serve different wine packaging functions.',
        ),
        'slot_3' => array(
            'base'    => 'multi-bottle-wine-carton-with-dividers',
            'alt'     => 'multi bottle cardboard wine carton with corrugated dividers',
            'title'   => 'Multi-Bottle Wine Carton with Dividers',
            'caption' => 'Dividers help separate wine bottles and reduce glass-to-glass impact.',
        ),
        'slot_4' => array(
            'base'    => 'custom-printed-wine-carton-packaging',
            'alt'     => 'custom printed cardboard wine carton packaging for retail branding',
            'title'   => 'Custom Printed Wine Carton Packaging',
            'caption' => 'Printing and finishing can turn a wine carton into a stronger brand presentation tool.',
        ),
        'slot_5' => array(
            'base'    => 'wine-carton-buyer-checklist-bottle-insert',
            'alt'     => 'wine carton buyer checklist with bottle insert and packaging sample',
            'title'   => 'Wine Carton Buyer Checklist',
            'caption' => 'Buyers should confirm bottle size, insert type, material, printing, and shipping use before ordering.',
        ),
    );
}

function custom_box_cardboard_wine_cartons_post_terms($post_id)
{
    $data = custom_box_cardboard_wine_cartons_post_map();
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

function custom_box_sync_cardboard_wine_cartons_images($post_id)
{
    $post = get_post($post_id);

    if (!$post) {
        return array();
    }

    $content = $post->post_content;
    $missing = array();

    foreach (custom_box_cardboard_wine_cartons_image_map() as $key => $image) {
        $attachment_id = custom_box_find_cardboard_wine_cartons_attachment($image['base']);

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
        $marker = '<!-- vpn-cardboard-wine-cartons-image:' . $key . ' -->';
        $figure = $marker . "\n" . custom_box_cardboard_wine_cartons_figure($attachment_id, $image);
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

function custom_box_find_cardboard_wine_cartons_attachment($base)
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

function custom_box_cardboard_wine_cartons_figure($attachment_id, $image)
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

function custom_box_cardboard_wine_cartons_post_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $missing = get_option('custom_box_cardboard_wine_cartons_missing_images', array());

    if (empty($missing) || !is_array($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Cardboard wine cartons post sync is waiting for these Media Library files:', 'custom-box-theme') . ' ';
    echo esc_html(implode(', ', $missing));
    echo '</p></div>';
}

function custom_box_cardboard_wine_cartons_post_content()
{
    $content_file = __DIR__ . '/post-content/cardboard-wine-cartons-materials-uses.html';

    if (!is_readable($content_file)) {
        return '';
    }

    return (string) file_get_contents($content_file);
}
