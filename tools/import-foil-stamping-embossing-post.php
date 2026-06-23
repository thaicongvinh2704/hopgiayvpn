<?php
/**
 * Imports metadata and images for the foil stamping and embossing paper boxes draft.
 *
 * Usage:
 *   php tools/import-foil-stamping-embossing-post.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

$post_data = array(
    'id'              => 1161,
    'title'           => 'Foil Stamping and Embossing on Paper Boxes: When to Use Them',
    'slug'            => 'foil-stamping-and-embossing-on-paper-boxes',
    'excerpt'         => 'Learn when to use foil stamping, embossing, debossing, or combined finishes on paper boxes for gift, cosmetic, and jewelry packaging projects.',
    'seo_title'       => 'Foil Stamping & Embossing on Paper Boxes: Use Guide',
    'seo_description' => 'Learn when to use foil stamping and embossing on paper boxes for gift, cosmetic and jewelry packaging, including materials, artwork, cost and QC tips.',
    'focus_keyword'   => 'foil stamping and embossing on paper boxes',
    'category'        => array(
        'name' => 'Paper Box Printing',
        'slug' => 'paper-box-printing',
    ),
    'tags'            => array(
        'Foil Stamping',
        'Embossing',
        'Paper Box Printing',
        'Gift Boxes',
        'Cosmetic Packaging',
        'Jewelry Packaging',
        'Packaging Finishing',
    ),
);

$images = array(
    'featured' => array(
        'base'    => 'foil-stamping-embossing-paper-boxes-guide',
        'alt'     => 'Foil stamping and embossing on premium paper boxes',
        'title'   => 'Foil Stamping and Embossing on Paper Boxes',
        'caption' => 'Foil stamping and embossing can add visual contrast and tactile detail when used correctly.',
    ),
    'slot_1' => array(
        'base'    => 'foil-vs-embossing-finish-comparison',
        'alt'     => 'Comparison of foil stamping and embossing effects on paper box logos',
        'title'   => 'Foil vs Embossing Finish Comparison',
        'caption' => 'Foil adds shine, while embossing adds a raised tactile effect.',
    ),
    'slot_2' => array(
        'base'    => 'hot-foil-stamped-gift-box-logo',
        'alt'     => 'Gold foil stamped logo on a rigid gift paper box',
        'title'   => 'Hot Foil Stamped Gift Box Logo',
        'caption' => 'Foil stamping is effective for clean logos on gift boxes and premium retail packaging.',
    ),
    'slot_3' => array(
        'base'    => 'cosmetic-paper-box-embossed-logo',
        'alt'     => 'Embossed logo detail on cosmetic paper box packaging',
        'title'   => 'Cosmetic Paper Box Embossed Logo',
        'caption' => 'Embossing can create a refined tactile detail on cosmetic paper boxes.',
    ),
    'slot_4' => array(
        'base'    => 'jewelry-box-foil-embossing-detail',
        'alt'     => 'Foil stamping and embossing detail on jewelry paper box',
        'title'   => 'Jewelry Box Foil and Embossing Detail',
        'caption' => 'Jewelry boxes often use foil or embossing for small but visible brand marks.',
    ),
    'slot_5' => array(
        'base'    => 'paper-box-finishing-rfq-checklist',
        'alt'     => 'Packaging buyer checklist for foil stamping and embossing paper boxes',
        'title'   => 'Paper Box Finishing RFQ Checklist',
        'caption' => 'A clear RFQ helps the factory recommend the right finishing method.',
    ),
);

$post = get_post((int) $post_data['id']);

if (!$post || 'post' !== $post->post_type) {
    $post = get_page_by_path($post_data['slug'], OBJECT, 'post');
}

if (!$post || 'trash' === $post->post_status) {
    fwrite(STDERR, "Draft post was not found or is in trash.\n");
    exit(1);
}

$update = wp_update_post(array(
    'ID'           => $post->ID,
    'post_title'   => $post_data['title'],
    'post_name'    => $post_data['slug'],
    'post_excerpt' => $post_data['excerpt'],
    'post_status'  => in_array($post->post_status, array('publish', 'private'), true) ? $post->post_status : 'draft',
), true);

if (is_wp_error($update)) {
    fwrite(STDERR, $update->get_error_message() . PHP_EOL);
    exit(1);
}

$category = get_term_by('slug', $post_data['category']['slug'], 'category');
if (!$category) {
    $created = wp_insert_term(
        $post_data['category']['name'],
        'category',
        array('slug' => $post_data['category']['slug'])
    );

    if (!is_wp_error($created)) {
        $category = get_term((int) $created['term_id'], 'category');
    }
}

if ($category && !is_wp_error($category)) {
    wp_set_post_categories($post->ID, array((int) $category->term_id), false);
}

wp_set_post_tags($post->ID, $post_data['tags'], false);
update_post_meta($post->ID, 'rank_math_title', $post_data['seo_title']);
update_post_meta($post->ID, 'rank_math_description', $post_data['seo_description']);
update_post_meta($post->ID, 'rank_math_focus_keyword', $post_data['focus_keyword']);

$post = get_post($post->ID);
$content = (string) $post->post_content;
$missing_images = array();
$missing_slots = array();
$inserted = 0;

foreach ($images as $key => $image) {
    $attachment_id = vpn_find_attachment_by_base($image['base']);

    if (!$attachment_id || !wp_get_attachment_url($attachment_id)) {
        $missing_images[] = $image['base'];
        continue;
    }

    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
    wp_update_post(array(
        'ID'           => $attachment_id,
        'post_parent'  => $post->ID,
        'post_title'   => $image['title'],
        'post_excerpt' => $image['caption'],
    ));

    if ('featured' === $key) {
        set_post_thumbnail($post->ID, $attachment_id);
        continue;
    }

    $slot_number = substr($key, -1);
    $marker = '<!-- vpn-foil-stamping-embossing-image:' . $key . ' -->';
    $figure = $marker . "\n" . vpn_clean_figure($attachment_id, $image);
    $slot = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
    $slot_pattern = '/<span\b[^>]*>\s*' . preg_quote($slot, '/') . '\s*<\/span>/i';
    $marker_pattern = '/' . preg_quote($marker, '/') . '\s*<figure\b.*?<\/figure>/is';

    if (false !== strpos($content, $marker)) {
        $content = preg_replace($marker_pattern, $figure, $content, 1);
        $inserted++;
    } elseif (false !== strpos($content, $slot)) {
        $content = str_replace($slot, $figure, $content);
        $inserted++;
    } elseif (preg_match($slot_pattern, $content)) {
        $content = preg_replace($slot_pattern, $figure, $content, 1);
        $inserted++;
    } else {
        $missing_slots[] = 'IMAGE_SLOT_' . $slot_number;
    }
}

if ($content !== $post->post_content) {
    $updated_content = wp_update_post(array(
        'ID'           => $post->ID,
        'post_content' => $content,
    ), true);

    if (is_wp_error($updated_content)) {
        fwrite(STDERR, $updated_content->get_error_message() . PHP_EOL);
        exit(1);
    }
}

update_option('custom_box_foil_stamping_embossing_missing_images', $missing_images, false);
update_post_meta($post->ID, '_custom_box_foil_stamping_embossing_imported', current_time('mysql'));

echo 'Foil stamping and embossing draft imported: ' . get_permalink($post->ID) . PHP_EOL;
echo 'Post ID: ' . (int) $post->ID . PHP_EOL;
echo 'Status: ' . get_post_status($post->ID) . PHP_EOL;
echo 'Featured image ID: ' . (int) get_post_thumbnail_id($post->ID) . PHP_EOL;
echo 'Inline figures inserted or refreshed: ' . (int) $inserted . PHP_EOL;
echo 'Missing images: ' . (empty($missing_images) ? 'none' : implode(', ', $missing_images)) . PHP_EOL;
echo 'Missing slots: ' . (empty($missing_slots) ? 'none' : implode(', ', $missing_slots)) . PHP_EOL;

function vpn_find_attachment_by_base($base)
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

function vpn_clean_figure($attachment_id, $image)
{
    return sprintf(
        '<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
        esc_url(wp_get_attachment_url($attachment_id)),
        esc_attr($image['alt']),
        esc_html($image['caption'])
    );
}
