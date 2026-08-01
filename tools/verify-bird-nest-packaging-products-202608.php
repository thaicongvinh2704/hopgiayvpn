<?php
/** Verify the local August 2026 bird nest packaging product batch. */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'product-samples-bird-nest-packaging-202608';
$expected = array(
    'custom-2-bottle-bird-nest-beverage-box',
    'custom-6-jar-bird-nest-magnetic-gift-box',
    'custom-8-jar-bird-nest-lid-and-base-gift-box',
    'custom-12-jar-double-layer-bird-nest-gift-box',
    'custom-bird-nest-bowl-and-spoon-gift-box',
    'custom-bird-nest-paper-tube-packaging',
    'custom-bird-nest-rock-sugar-gift-box',
    'custom-bird-nest-sachet-packaging-box',
    'custom-dried-bird-nest-window-display-box',
    'custom-single-jar-bird-nest-window-box',
);
$failures = array();
$products = get_posts(array(
    'post_type' => 'product', 'post_status' => array('publish', 'draft', 'private'), 'posts_per_page' => -1,
    'meta_query' => array(array('key' => '_vpn_sample_import', 'value' => $marker)),
));

if (count($products) !== 10) {
    $failures[] = 'Expected 10 products, found ' . count($products) . '.';
}

foreach ($products as $product) {
    $title = get_the_title($product);
    $body = (string) $product->post_content;
    $words = str_word_count(wp_strip_all_tags($body));
    $short_words = str_word_count(wp_strip_all_tags((string) $product->post_excerpt));
    $featured = (int) get_post_thumbnail_id($product->ID);
    $gallery = array_filter(array_map('absint', explode(',', (string) get_post_meta($product->ID, '_product_image_gallery', true))));
    $images = array_unique(array_filter(array_merge(array($featured), $gallery)));
    $figures = substr_count($body, '<figure class="product-inline-figure');
    $links = substr_count($body, '<a ');
    $categories = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'slugs'));
    $specs = get_post_meta($product->ID, '_custom_box_product_specs', true);
    $moq = '';
    $missing_alt = 0;
    foreach ($images as $image_id) {
        if ('' === trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true))) {
            $missing_alt++;
        }
    }
    if (is_array($specs)) {
        foreach ($specs as $spec) {
            if (isset($spec['label'], $spec['value']) && 'Minimum Order Quantity (MOQ)' === $spec['label']) {
                $moq = (string) $spec['value'];
            }
        }
    }

    if (!in_array($product->post_name, $expected, true)) $failures[] = $title . ': unexpected slug.';
    if ('publish' !== $product->post_status) $failures[] = $title . ': not published.';
    if ($words < 1500 || $words > 2000) $failures[] = $title . ': long description outside 1500-2000 words (' . $words . ').';
    if ($short_words < 120 || $short_words > 180) $failures[] = $title . ': short description outside 120-180 words (' . $short_words . ').';
    if (preg_match('/<h1\b/i', $body)) $failures[] = $title . ': contains H1 in long description.';
    if (4 !== count($images) || 4 !== $figures) $failures[] = $title . ': expected 4 images and figures; images=' . count($images) . ', figures=' . $figures . '.';
    if ($links < 3 || $links > 5) $failures[] = $title . ': expected 3-5 internal links; found ' . $links . '.';
    if ($missing_alt) $failures[] = $title . ': image alt text missing.';
    if (is_wp_error($categories) || !in_array('bird-nest-packaging-boxes', $categories, true)) $failures[] = $title . ': missing category.';
    if (!is_array($specs) || 21 !== count($specs)) $failures[] = $title . ': expected 21 specification rows.';
    if ('1000 boxes' !== $moq) $failures[] = $title . ': MOQ is not 1000 boxes.';
    if (!get_post_meta($product->ID, 'rank_math_focus_keyword', true)
        || !get_post_meta($product->ID, 'rank_math_title', true)
        || !get_post_meta($product->ID, 'rank_math_description', true)) $failures[] = $title . ': missing Rank Math metadata.';
    if (!is_array(get_post_meta($product->ID, '_vpn_product_specific_details', true))) $failures[] = $title . ': missing Product DNA details.';

    $response = wp_remote_get(get_permalink($product), array('timeout' => 15, 'sslverify' => false));
    $http = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);
    if (200 !== $http) $failures[] = $title . ': frontend HTTP status is ' . $http . '.';

    echo $title . ': status=' . $product->post_status . ', words=' . $words . ', short=' . $short_words . ', images=' . count($images) . ', figures=' . $figures . ', links=' . $links . ', specs=' . (is_array($specs) ? count($specs) : 0) . ', http=' . $http . PHP_EOL;
}

foreach ($expected as $slug) {
    $product = get_page_by_path($slug, OBJECT, 'product');
    if (!$product || $marker !== get_post_meta($product->ID, '_vpn_sample_import', true)) {
        $failures[] = 'Missing expected product: ' . $slug . '.';
    }
}

if ($failures) {
    foreach ($failures as $failure) fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
    exit(1);
}

echo 'Verified bird nest packaging August 2026 products successfully.' . PHP_EOL;
