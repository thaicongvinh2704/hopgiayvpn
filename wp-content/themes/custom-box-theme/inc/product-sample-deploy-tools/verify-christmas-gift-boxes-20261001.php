<?php
/** Verify the five Christmas gift-box products after Product Sample Deploy. */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

require_once get_template_directory() . '/inc/christmas-gift-boxes-20261001-support.php';
$marker = 'christmas-gift-boxes-20261001';
$products = array();
foreach (json_decode((string) file_get_contents(get_template_directory() . '/inc/product-content/christmas-gift-boxes-20261001/products.json'), true, 512, JSON_THROW_ON_ERROR) as $row) { $products[$row['slug']] = $row; }
$required_specs = array(
    'Feature', 'Industrial Use', 'Paper Type', 'Box Type', 'Shape', 'Place of Origin',
    'Model Number', 'Brand Name', 'Province', 'Accessories', 'Custom Order', 'Liner Type',
    'Logo Printing', 'Printing Handling', 'Color', 'Size', 'Thickness', 'Single Piece Price',
    'Minimum Order Quantity (MOQ)', 'Product Name', 'Design',
);
$failures = array();
$results = array();
$bundle_dir = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/10/';

foreach ($products as $slug => $expected) {
    $product = get_page_by_path($slug, OBJECT, 'product');
    if (!$product || 'product' !== $product->post_type) {
        $failures[] = $slug . ': missing WooCommerce product';
        continue;
    }

    $product_id = (int) $product->ID;
    $content = (string) $product->post_content;
    $short = (string) $product->post_excerpt;
    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);
    $featured_id = (int) get_post_thumbnail_id($product_id);
    $gallery_ids = array_values(array_filter(array_map('intval', explode(',', (string) get_post_meta($product_id, '_product_image_gallery', true)))));
    $attachment_ids = array_values(array_unique(array_merge($featured_id ? array($featured_id) : array(), $gallery_ids)));
    $rank_title = (string) get_post_meta($product_id, 'rank_math_title', true);
    $rank_description = (string) get_post_meta($product_id, 'rank_math_description', true);
    $rank_keyword = (string) get_post_meta($product_id, 'rank_math_focus_keyword', true);
    $robots = get_post_meta($product_id, 'rank_math_robots', true);
    $category_slugs = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));

    if ($marker !== (string) get_post_meta($product_id, '_vpn_sample_import', true)) {
        $failures[] = $slug . ': incorrect batch marker';
    }
    if (!in_array(get_post_status($product_id), array('publish', 'private'), true)) {
        $failures[] = $slug . ': product is not published';
    }
    if ($expected['title'] !== $product->post_title) {
        $failures[] = $slug . ': product title mismatch';
    }
    if (!in_array('christmas-packaging', $category_slugs, true)) {
        $failures[] = $slug . ': missing Christmas category';
    }
    if (str_word_count(wp_strip_all_tags($short)) < 120 || str_word_count(wp_strip_all_tags($short)) > 180) {
        $failures[] = $slug . ': short description outside the 120-180 word target';
    }
    $word_count = str_word_count(wp_strip_all_tags($content));
    if ($word_count < 1500 || $word_count > 2000) {
        $failures[] = $slug . ': long description outside 1500-2000 words (' . $word_count . ')';
    }
    if (preg_match('/<h1\b/i', $content)) {
        $failures[] = $slug . ': unexpected H1 inside product description';
    }
    if (3 !== preg_match_all('/<figure\b[^>]*class="[^"]*product-inline-figure/i', $content)) {
        $failures[] = $slug . ': expected three responsive inline figures';
    }
    if (preg_match('/\{\{[A-Z0-9_]+\}\}/', $content) || false === stripos($content, 'AI-generated design visualization')) {
        $failures[] = $slug . ': unresolved placeholder or missing visualization disclosure';
    }
    if (preg_match_all('/<a\b/i', $content) < 5) {
        $failures[] = $slug . ': category, product, guide and quote links are incomplete';
    }
    if (!$rank_title || strlen($rank_title) > 60 || !$rank_description || strlen($rank_description) > 155 || $expected['keyword'] !== $rank_keyword) {
        $failures[] = $slug . ': Rank Math title, description or focus keyword is incomplete';
    }
    if (array('index', 'follow') !== $robots) {
        $failures[] = $slug . ': product-level robots metadata must be index, follow';
    }
    if (!is_array($specs) || 21 !== count($specs)) {
        $failures[] = $slug . ': expected 21 product specification rows';
    } else {
        $labels = array_column($specs, 'label');
        foreach ($required_specs as $label) {
            if (!in_array($label, $labels, true)) {
                $failures[] = $slug . ': missing specification ' . $label;
            }
        }
        $moq = '';
        foreach ($specs as $spec) {
            if ('Minimum Order Quantity (MOQ)' === ($spec['label'] ?? '')) {
                $moq = (string) ($spec['value'] ?? '');
                break;
            }
        }
        if ('1000 boxes; confirm eligible specifications and quantity breaks in the written quotation.' !== $moq) {
            $failures[] = $slug . ': MOQ specification mismatch';
        }
    }
    if (!$featured_id || 5 !== count($gallery_ids) || 6 !== count($attachment_ids)) {
        $failures[] = $slug . ': expected one featured image and five gallery images';
    }
    foreach ($attachment_ids as $attachment_id) {
        $attachment = get_post($attachment_id);
        $file = get_attached_file($attachment_id);
        $relative_file = (string) get_post_meta($attachment_id, '_vpn_christmas_gift_boxes_20261001_file', true);
        $image_info = $file && file_exists($file) ? @getimagesize($file) : false;
        if (
            !$attachment || 'attachment' !== $attachment->post_type
            || $product_id !== (int) $attachment->post_parent
            || 'image/webp' !== get_post_mime_type($attachment_id)
            || '' === (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
            || !$image_info || 450 !== $image_info[0] || 570 !== $image_info[1]
            || !$relative_file || !file_exists($bundle_dir . $relative_file)
        ) {
            $failures[] = $slug . ': invalid or incomplete WebP attachment ' . $attachment_id;
        }
    }
    if ($featured_id) {
        $featured_file = (string) get_post_meta($featured_id, '_vpn_christmas_gift_boxes_20261001_file', true);
        if ($expected['prefix'] . '-01-hero.webp' !== $featured_file) {
            $failures[] = $slug . ': featured image is not the hero view';
        }
    }

    $failures = array_merge($failures, vpn_xmas_20261001_verify_release($product_id, $expected));

    $results[] = array(
        'id' => $product_id,
        'slug' => $slug,
        'url' => get_permalink($product_id),
        'short_words' => str_word_count(wp_strip_all_tags($short)),
        'long_words' => $word_count,
        'images' => count($attachment_ids),
        'specifications' => is_array($specs) ? count($specs) : 0,
        'seo_title' => $rank_title,
    );
}

$result = array(
    'scope' => 'Product Sample Deploy',
    'marker' => $marker,
    'expected_products' => count($products),
    'products' => $results,
    'failures' => array_values(array_unique($failures)),
);
echo wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures || count($results) !== count($products)) {
    throw new RuntimeException('Christmas product verification failed: ' . implode('; ', $failures));
}
