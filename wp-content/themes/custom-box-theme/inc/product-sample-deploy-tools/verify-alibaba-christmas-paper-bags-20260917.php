<?php
/** Verify the five September 2026 Christmas paper-bag products. */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'alibaba-christmas-paper-bags-20260917';
$slugs = array(
    'custom-red-snowflake-twisted-handle-christmas-paper-bag',
    'custom-kraft-evergreen-flat-handle-christmas-paper-bag',
    'custom-ivory-holly-rope-handle-christmas-paper-bag',
    'custom-navy-ornament-rope-handle-christmas-paper-bag',
    'custom-gingerbread-flat-handle-christmas-paper-bag',
);
$failures = array();
$christmas_term = get_term_by('slug', 'christmas-packaging', 'product_cat');
$paper_bag_term = get_term_by('slug', 'paper-bags-with-logo', 'product_cat');

foreach ($slugs as $slug) {
    $product = get_page_by_path($slug, OBJECT, 'product');
    if (!$product) {
        $failures[] = $slug . ': missing product';
        continue;
    }
    $content = (string) $product->post_content;
    $specs = get_post_meta($product->ID, '_custom_box_product_specs', true);
    $gallery = array_filter(array_map('intval', explode(',', (string) get_post_meta($product->ID, '_product_image_gallery', true))));
    $images = array_merge(array((int) get_post_thumbnail_id($product->ID)), $gallery);
    $title = (string) get_post_meta($product->ID, 'rank_math_title', true);
    $description = (string) get_post_meta($product->ID, 'rank_math_description', true);
    $robots = get_post_meta($product->ID, 'rank_math_robots', true);

    if ('publish' !== get_post_status($product->ID)) $failures[] = $slug . ': not published';
    if (str_word_count(wp_strip_all_tags($content)) < 500) $failures[] = $slug . ': content under 500 words';
    if (preg_match('/<h1\b/i', $content)) $failures[] = $slug . ': content H1';
    if (!is_array($specs) || count($specs) < 8) $failures[] = $slug . ': specs';
    if (count($images) !== 6) $failures[] = $slug . ': expected 6 images, found ' . count($images);
    if (!$christmas_term || !has_term((int) $christmas_term->term_id, 'product_cat', $product->ID)) $failures[] = $slug . ': Christmas category';
    if (!$paper_bag_term || !has_term((int) $paper_bag_term->term_id, 'product_cat', $product->ID)) $failures[] = $slug . ': paper bags category';
    if ($marker !== (string) get_post_meta($product->ID, '_vpn_sample_import', true)) $failures[] = $slug . ': batch marker';
    if (false === stripos($content, 'AI-generated packaging concept mockup')) $failures[] = $slug . ': visualization disclosure';
    if (!$title || strlen($title) > 60) $failures[] = $slug . ': SEO title length';
    if (!$description || strlen($description) > 160) $failures[] = $slug . ': SEO description length';
    if (array('index', 'follow') !== $robots) $failures[] = $slug . ': robots metadata';
}

$result = array('marker' => $marker, 'expected' => count($slugs), 'failures' => $failures);
echo wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures) {
    exit(1);
}
