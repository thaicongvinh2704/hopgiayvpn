<?php
/** Verify the five locally imported Christmas box concepts and their SEO fields. */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'alibaba-christmas-boxes-20260925';
$slugs = array(
    'custom-santa-portrait-rigid-christmas-gift-box',
    'custom-santa-sleigh-christmas-mailer-box',
    'custom-merry-reindeer-christmas-paper-tube',
    'custom-christmas-tree-drawer-gift-box',
    'custom-nutcracker-folding-carton-christmas-gift-box',
);
$expected_labels = array(
    'Feature', 'Industrial Use', 'Paper Type', 'Box Type', 'Shape', 'Place of Origin',
    'Model Number', 'Brand Name', 'Province', 'Accessories', 'Custom Order', 'Liner Type',
    'Logo Printing', 'Printing Handling', 'Color', 'Size', 'Thickness', 'Single Piece Price',
    'Minimum Order Quantity (MOQ)', 'Product Name', 'Design',
);
$term = get_term_by('slug', 'christmas-packaging', 'product_cat');
$failures = array();
$results = array();

foreach ($slugs as $slug) {
    $product = get_page_by_path($slug, OBJECT, 'product');
    if (!$product) {
        $failures[] = $slug . ': missing product';
        continue;
    }
    $short = (string) $product->post_excerpt;
    $content = (string) $product->post_content;
    $specs = get_post_meta($product->ID, '_custom_box_product_specs', true);
    $gallery = array_values(array_filter(array_map('intval', explode(',', (string) get_post_meta($product->ID, '_product_image_gallery', true)))));
    $featured = (int) get_post_thumbnail_id($product->ID);
    $images = array_values(array_unique(array_merge($featured ? array($featured) : array(), $gallery)));
    $title = (string) get_post_meta($product->ID, 'rank_math_title', true);
    $description = (string) get_post_meta($product->ID, 'rank_math_description', true);
    $keyword = (string) get_post_meta($product->ID, 'rank_math_focus_keyword', true);
    $robots = get_post_meta($product->ID, 'rank_math_robots', true);

    if ('publish' !== get_post_status($product->ID)) $failures[] = $slug . ': not published locally';
    if ($marker !== (string) get_post_meta($product->ID, '_vpn_sample_import', true)) $failures[] = $slug . ': batch marker';
    if (!$term || !has_term((int) $term->term_id, 'product_cat', $product->ID)) $failures[] = $slug . ': missing Christmas category';
    if (!is_array($specs) || 21 !== count($specs)) {
        $failures[] = $slug . ': expected 21 product specification rows';
    } else {
        $labels = array_column($specs, 'label');
        foreach ($expected_labels as $label) {
            if (!in_array($label, $labels, true)) $failures[] = $slug . ': missing specification ' . $label;
        }
        if ('Not stated in the supplied visual package; confirm in the written quotation.' !== (string) ($specs[18]['value'] ?? '')) {
            $failures[] = $slug . ': MOQ must remain project-confirmed';
        }
    }
    if (str_word_count(wp_strip_all_tags($short)) < 120 || str_word_count(wp_strip_all_tags($short)) > 180) $failures[] = $slug . ': short description outside 120-180 words';
    $word_count = str_word_count(wp_strip_all_tags($content));
    if ($word_count < 1500 || $word_count > 2000) $failures[] = $slug . ': long description outside 1500-2000 words (' . $word_count . ')';
    if (preg_match('/<h1\b/i', $content)) $failures[] = $slug . ': H1 found inside long description';
    if (3 !== preg_match_all('/<figure\b[^>]*class="[^"]*product-inline-figure/', $content)) $failures[] = $slug . ': expected three responsive inline figures';
    if (preg_match('/\{\{[A-Z0-9_]+\}\}/', $content)) $failures[] = $slug . ': unresolved content placeholder';
    if (false === stripos($content, 'AI-generated design visualization') || false === stripos($content, 'not a manufactured sample')) $failures[] = $slug . ': disclosure missing';
    if (preg_match_all('/<a\b/i', $content) < 5) $failures[] = $slug . ': expected category, related product, guide, and quote links';
    if (!$title || strlen($title) > 60) $failures[] = $slug . ': SEO title missing or over 60 characters';
    if (!$description || strlen($description) > 155) $failures[] = $slug . ': meta description missing or over 155 characters';
    if (!$keyword) $failures[] = $slug . ': focus keyword missing';
    if (array('index', 'follow') !== $robots) $failures[] = $slug . ': robots must be index, follow';
    if (5 !== count($images)) $failures[] = $slug . ': expected five clean product images, found ' . count($images);
    foreach ($images as $attachment_id) {
        if ('attachment' !== get_post_type($attachment_id)) $failures[] = $slug . ': invalid image attachment ' . $attachment_id;
        if ('image/webp' !== get_post_mime_type($attachment_id)) $failures[] = $slug . ': image must be optimized WebP';
        $filename = (string) get_post_meta($attachment_id, '_vpn_xmas_boxes_20260925_file', true);
        if (!$filename || !preg_match('/-0[1-5]-(?:hero|open-interior|paper-detail|rear-side-view|top-artwork-view)\.webp$/i', $filename)) {
            $failures[] = $slug . ': unapproved gallery image ' . $filename;
        }
        if (!$attachment_id || '' === (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) $failures[] = $slug . ': missing descriptive image alt';
        $path = get_attached_file($attachment_id);
        $image_info = $path && file_exists($path) ? @getimagesize($path) : false;
        if (!$image_info || 1000 !== $image_info[0] || 1000 !== $image_info[1]) $failures[] = $slug . ': original WebP image missing or wrong size';
    }

    $results[] = array(
        'id' => (int) $product->ID,
        'slug' => $slug,
        'url' => get_permalink($product->ID),
        'short_words' => str_word_count(wp_strip_all_tags($short)),
        'long_words' => $word_count,
        'images' => count($images),
        'specifications' => is_array($specs) ? count($specs) : 0,
        'seo_title' => $title,
        'seo_description_characters' => strlen($description),
    );
}

$result = array('scope' => 'local WordPress only', 'marker' => $marker, 'expected' => count($slugs), 'products' => $results, 'failures' => $failures);
echo wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures || count($results) !== count($slugs)) {
    exit(1);
}
