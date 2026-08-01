<?php
/**
 * Verify the local August 2026 pharmaceutical packaging product batch.
 *
 * Run:
 *   php tools/verify-pharmaceutical-packaging-products-202608.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'product-samples-pharmaceutical-packaging-202608';
$expected = array(
    'custom-autoinjector-pen-box',
    'custom-blister-pack-medicine-box',
    'custom-eye-drop-packaging-box',
    'custom-inhaler-packaging-box',
    'custom-liquid-medicine-bottle-box',
    'custom-nasal-spray-packaging-box',
    'custom-pharmaceutical-tube-box',
    'custom-prefilled-syringe-box',
    'custom-sachet-stick-pack-carton',
    'custom-transdermal-patch-box',
);
$failures = array();

$products = get_posts(array(
    'post_type'      => 'product',
    'post_status'    => array('publish', 'draft', 'private'),
    'posts_per_page' => -1,
    'meta_query'     => array(array('key' => '_vpn_sample_import', 'value' => $marker)),
));

if (count($products) !== count($expected)) {
    $failures[] = 'Expected 10 products, found ' . count($products) . '.';
}

foreach ($products as $product) {
    $title = get_the_title($product);
    $body = (string) $product->post_content;
    $short = (string) $product->post_excerpt;
    $words = str_word_count(wp_strip_all_tags($body));
    $short_words = str_word_count(wp_strip_all_tags($short));
    $featured = (int) get_post_thumbnail_id($product->ID);
    $gallery = array_filter(array_map('absint', explode(',', (string) get_post_meta($product->ID, '_product_image_gallery', true))));
    $images = array_unique(array_filter(array_merge(array($featured), $gallery)));
    $figures = substr_count($body, '<figure class="product-inline-figure');
    $internal_links = substr_count($body, '<a ');
    $categories = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'slugs'));
    $specs = get_post_meta($product->ID, '_custom_box_product_specs', true);
    $moq = '';
    $alt_failures = 0;
    foreach ($images as $image_id) {
        if ('' === trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true))) {
            $alt_failures++;
        }
    }
    if (is_array($specs)) {
        foreach ($specs as $spec) {
            if (isset($spec['label'], $spec['value']) && 'Minimum Order Quantity (MOQ)' === $spec['label']) {
                $moq = (string) $spec['value'];
            }
        }
    }

    if (!in_array($product->post_name, $expected, true)) {
        $failures[] = $title . ': unexpected slug ' . $product->post_name . '.';
    }
    if ('publish' !== $product->post_status) {
        $failures[] = $title . ': not published.';
    }
    if ($words < 1500 || $words > 2000) {
        $failures[] = $title . ': long description outside 1500-2000 words (' . $words . ').';
    }
    if ($short_words < 120 || $short_words > 180) {
        $failures[] = $title . ': short description outside 120-180 words (' . $short_words . ').';
    }
    if (preg_match('/<h1\b/i', $body)) {
        $failures[] = $title . ': contains an H1 in long description.';
    }
    if (4 !== count($images) || 4 !== $figures) {
        $failures[] = $title . ': expected 4 unique images and 4 figures; images=' . count($images) . ', figures=' . $figures . '.';
    }
    if ($internal_links < 3 || $internal_links > 5) {
        $failures[] = $title . ': expected 3-5 internal links; found ' . $internal_links . '.';
    }
    if ($alt_failures) {
        $failures[] = $title . ': ' . $alt_failures . ' image(s) missing alt text.';
    }
    if (is_wp_error($categories) || !in_array('pharmaceutical-packaging-boxes', $categories, true)) {
        $failures[] = $title . ': missing pharmaceutical-packaging-boxes category.';
    }
    if (!is_array($specs) || 21 !== count($specs)) {
        $failures[] = $title . ': expected 21 specification rows.';
    }
    if ('1000 boxes' !== $moq) {
        $failures[] = $title . ': MOQ is not 1000 boxes.';
    }
    if (!get_post_meta($product->ID, 'rank_math_focus_keyword', true)
        || !get_post_meta($product->ID, 'rank_math_title', true)
        || !get_post_meta($product->ID, 'rank_math_description', true)) {
        $failures[] = $title . ': missing Rank Math metadata.';
    }
    if (!is_array(get_post_meta($product->ID, '_vpn_product_specific_details', true))) {
        $failures[] = $title . ': missing Product DNA details.';
    }

    $url = get_permalink($product);
    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
    $http_code = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);
    if (200 !== $http_code) {
        $failures[] = $title . ': frontend HTTP status is ' . $http_code . '.';
    }

    echo $title . ': status=' . $product->post_status . ', words=' . $words . ', short=' . $short_words . ', images=' . count($images) . ', figures=' . $figures . ', links=' . $internal_links . ', specs=' . (is_array($specs) ? count($specs) : 0) . ', http=' . $http_code . PHP_EOL;
}

foreach ($expected as $slug) {
    $product = get_page_by_path($slug, OBJECT, 'product');
    if (!$product || $marker !== get_post_meta($product->ID, '_vpn_sample_import', true)) {
        $failures[] = 'Missing expected product: ' . $slug . '.';
    }
}

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'Verified pharmaceutical packaging August 2026 products successfully.' . PHP_EOL;
