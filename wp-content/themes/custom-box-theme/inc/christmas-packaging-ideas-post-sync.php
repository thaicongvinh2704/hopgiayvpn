<?php
/** Deploys the Christmas packaging ideas post and its five-image set. */
defined('ABSPATH') || exit;

const CUSTOM_BOX_CHRISTMAS_IDEAS_VERSION = '2026-09-21-christmas-packaging-ideas-v1';
const CUSTOM_BOX_CHRISTMAS_IDEAS_OPTION = 'custom_box_christmas_packaging_ideas_sync_version';

add_action('admin_init', 'custom_box_sync_christmas_packaging_ideas');

function custom_box_christmas_packaging_ideas_data(): array {
    return array(
        'title' => 'Christmas Packaging Ideas: 12 Box & Bag Concepts for Brands',
        'slug' => 'christmas-packaging-ideas',
        'excerpt' => 'Explore 12 Christmas packaging ideas for paper boxes, gift bags, ecommerce mailers and corporate gifts, with practical guidance for choosing a production-ready concept.',
        'category' => array('name' => 'Blog / Packaging Guide', 'slug' => 'blog-packaging-guide'),
        'tags' => array('Christmas Packaging' => 'christmas-packaging', 'Holiday Packaging' => 'holiday-packaging', 'Gift Boxes' => 'gift-boxes', 'Paper Bags' => 'paper-bags', 'Seasonal Packaging' => 'seasonal-packaging'),
        'seo_title' => 'Christmas Packaging Ideas: 12 Box & Bag Concepts',
        'seo_description' => 'Explore 12 Christmas packaging ideas for gift boxes, paper bags, ecommerce mailers and seasonal gift sets, plus tips for choosing the right format.',
        'focus_keyword' => 'christmas packaging ideas',
    );
}

function custom_box_christmas_packaging_ideas_images(): array {
    return array(
        'featured' => array('base' => 'christmas-packaging-ideas-boxes-bags-brand-guide', 'alt' => 'Christmas packaging ideas with paper gift boxes and paper bags for brands', 'title' => 'Christmas Packaging Ideas for Brands', 'caption' => 'Christmas packaging concepts for retail, gifting and ecommerce.'),
        'slot_1' => array('base' => 'christmas-packaging-three-layer-design-system', 'alt' => 'Three-layer Christmas packaging design system showing structure artwork and reveal details', 'title' => 'Three-Layer Christmas Packaging System', 'caption' => 'Build the concept around structure, seasonal artwork and the reveal experience.'),
        'slot_2' => array('base' => 'christmas-gift-box-packaging-design-directions', 'alt' => 'Christmas gift box packaging in kraft red and midnight blue design directions', 'title' => 'Christmas Gift Box Design Directions', 'caption' => 'Different Christmas visual directions can work on different packaging structures.'),
        'slot_3' => array('base' => 'christmas-ecommerce-mailer-box-unboxing-idea', 'alt' => 'Christmas ecommerce mailer box with festive inside lid printing', 'title' => 'Christmas Ecommerce Packaging Idea', 'caption' => 'A shipping-ready mailer can reserve the strongest Christmas moment for the inside reveal.'),
        'slot_4' => array('base' => 'christmas-box-paper-bag-packaging-system', 'alt' => 'Matching Christmas gift box and paper bag packaging system', 'title' => 'Christmas Box and Paper Bag System', 'caption' => 'Coordinate color and artwork across the gift box and carry bag without making them identical.'),
    );
}

function custom_box_christmas_packaging_ideas_content(): string {
    $content = @file_get_contents(__DIR__ . '/post-content/christmas-packaging-ideas.html');
    return is_string($content) ? str_replace('https://hopgiayvpn.com/', trailingslashit(home_url('/')), $content) : '';
}

function custom_box_christmas_packaging_ideas_file(array $image): array {
    $uploads = wp_get_upload_dir();
    $relative = '2026/09/' . $image['base'] . '.webp';
    $target = trailingslashit($uploads['basedir']) . $relative;
    if (!file_exists($target)) {
        $source = __DIR__ . '/product-sample-deploy-assets/uploads/2026/09/' . $image['base'] . '.webp';
        if (!is_readable($source) || !wp_mkdir_p(dirname($target)) || !copy($source, $target)) return array();
    }
    return array('relative' => $relative, 'path' => $target, 'url' => trailingslashit($uploads['baseurl']) . $relative);
}

function custom_box_christmas_packaging_ideas_attachment(int $post_id, array $image): int {
    global $wpdb;
    $ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s", '%' . $wpdb->esc_like($image['base']) . '%'));
    foreach ($ids as $id) if ($image['base'] === pathinfo(wp_basename((string) get_post_meta((int) $id, '_wp_attached_file', true)), PATHINFO_FILENAME)) return (int) $id;
    $file = custom_box_christmas_packaging_ideas_file($image);
    if (!$file) return 0;
    $id = wp_insert_attachment(array('guid' => $file['url'], 'post_mime_type' => 'image/webp', 'post_title' => $image['title'], 'post_excerpt' => $image['caption'], 'post_status' => 'inherit', 'post_parent' => $post_id), $file['path'], $post_id, true);
    if (is_wp_error($id)) return 0;
    update_post_meta($id, '_wp_attached_file', $file['relative']);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata($id, $file['path']);
    if (is_array($metadata)) wp_update_attachment_metadata($id, $metadata);
    return (int) $id;
}

function custom_box_christmas_packaging_ideas_terms(int $post_id, array $data): void {
    $category = get_term_by('slug', $data['category']['slug'], 'category');
    if (!$category) { $created = wp_insert_term($data['category']['name'], 'category', array('slug' => $data['category']['slug'])); $category = !is_wp_error($created) ? get_term($created['term_id'], 'category') : null; }
    if ($category) wp_set_post_categories($post_id, array((int) $category->term_id), false);
    $ids = array(); foreach ($data['tags'] as $name => $slug) { $term = get_term_by('slug', $slug, 'post_tag'); if (!$term) { $created = wp_insert_term($name, 'post_tag', array('slug' => $slug)); if (!is_wp_error($created)) $ids[] = (int) $created['term_id']; } else $ids[] = (int) $term->term_id; }
    wp_set_post_terms($post_id, $ids, 'post_tag', false);
}

function custom_box_sync_christmas_packaging_ideas(): void {
    if (!current_user_can('manage_options')) return;
    $data = custom_box_christmas_packaging_ideas_data();
    $post = get_page_by_path($data['slug'], OBJECT, 'post');
    if (CUSTOM_BOX_CHRISTMAS_IDEAS_VERSION === get_option(CUSTOM_BOX_CHRISTMAS_IDEAS_OPTION) && $post) return;
    $content = custom_box_christmas_packaging_ideas_content();
    if ('' === trim($content)) return;
    $payload = array('post_title' => $data['title'], 'post_name' => $data['slug'], 'post_type' => 'post', 'post_excerpt' => $data['excerpt'], 'post_content' => $content);
    if ($post) { $payload['ID'] = $post->ID; $payload['post_status'] = in_array($post->post_status, array('publish', 'private'), true) ? $post->post_status : 'draft'; $post_id = wp_update_post($payload, true); } else { $payload['post_status'] = 'draft'; $post_id = wp_insert_post($payload, true); }
    if (is_wp_error($post_id)) return;
    $post_id = (int) $post_id; custom_box_christmas_packaging_ideas_terms($post_id, $data);
    update_post_meta($post_id, 'rank_math_title', $data['seo_title']); update_post_meta($post_id, 'rank_math_description', $data['seo_description']); update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']); update_post_meta($post_id, 'rank_math_canonical_url', home_url('/christmas-packaging-ideas/'));
    $body = $content;
    foreach (custom_box_christmas_packaging_ideas_images() as $key => $image) {
        $attachment_id = custom_box_christmas_packaging_ideas_attachment($post_id, $image); $url = $attachment_id ? wp_get_attachment_url($attachment_id) : false; if (!$url) return;
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']); wp_update_post(array('ID' => $attachment_id, 'post_title' => $image['title'], 'post_excerpt' => $image['caption'], 'post_parent' => $post_id));
        if ('featured' === $key) { set_post_thumbnail($post_id, $attachment_id); continue; }
        $marker = '<!-- christmas-packaging-ideas-image:' . $key . ' -->'; $figure = $marker . "\n<figure><img src=\"" . esc_url($url) . "\" alt=\"" . esc_attr($image['alt']) . "\" style=\"width:100%; height:auto;\" loading=\"lazy\" decoding=\"async\"><figcaption>" . esc_html($image['caption']) . '</figcaption></figure>';
        $body = str_replace('<!-- IMAGE_SLOT_' . substr($key, 5) . ' -->', $figure, $body);
    }
    wp_update_post(array('ID' => $post_id, 'post_content' => $body));
    update_option(CUSTOM_BOX_CHRISTMAS_IDEAS_OPTION, CUSTOM_BOX_CHRISTMAS_IDEAS_VERSION, false);
}
