<?php
/** Verify the five September 2026 Christmas gift-box collection products. */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'alibaba-christmas-gift-box-collection-20260917';
$slugs = array(
    'custom-red-snowflake-lid-and-base-christmas-gift-box',
    'custom-kraft-evergreen-tuck-top-christmas-gift-box',
    'custom-candy-cane-pillow-christmas-gift-box',
    'custom-holly-berry-gable-christmas-gift-box',
    'custom-green-tree-magnetic-christmas-gift-box',
);
$failures = array();
$term = get_term_by('slug', 'christmas-packaging', 'product_cat');

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

    if ('publish' !== get_post_status($product->ID)) $failures[] = $slug . ': not published';
    if (str_word_count(wp_strip_all_tags($content)) < 500) $failures[] = $slug . ': content under 500 words';
    if (preg_match('/<h1\b/i', $content)) $failures[] = $slug . ': content H1';
    if (!is_array($specs) || count($specs) < 8) $failures[] = $slug . ': specs';
    if (count($images) !== 6) $failures[] = $slug . ': expected 6 images, found ' . count($images);
    if (!$term || !has_term((int) $term->term_id, 'product_cat', $product->ID)) $failures[] = $slug . ': Christmas category';
    if ('alibaba-christmas-gift-box-collection-20260917' !== (string) get_post_meta($product->ID, '_vpn_sample_import', true)) $failures[] = $slug . ': batch marker';
    if (false === stripos($content, 'AI-generated design visualization')) $failures[] = $slug . ': visualization disclosure';
    if (!$title || strlen($title) > 60) $failures[] = $slug . ': SEO title length';
    if (!$description || strlen($description) > 160) $failures[] = $slug . ': SEO description length';
}

$result = array('marker' => $marker, 'expected' => count($slugs), 'failures' => $failures);
echo wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures) {
    exit(1);
}
