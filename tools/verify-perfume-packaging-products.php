<?php
/**
 * Verify July 2026 perfume packaging product import.
 *
 * Run:
 *   php tools/verify-perfume-packaging-products.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'product-samples-perfume-packaging-202607';
$expected = array(
    'custom-folding-carton-perfume-box',
    'custom-luxury-perfume-box-with-magnetic-closure',
    'custom-mini-perfume-bottle-packaging-box',
    'custom-perfume-and-lotion-gift-set-box',
    'custom-perfume-box-with-eva-foam-insert',
    'custom-perfume-box-with-lid-and-base',
    'custom-perfume-box-with-paper-insert',
    'custom-perfume-drawer-box-with-insert',
    'custom-perfume-sample-set-box',
    'custom-refillable-perfume-packaging-box',
);
$failures = array();

$products = get_posts(array(
    'post_type'      => 'product',
    'post_status'    => array('publish', 'draft', 'private'),
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'   => '_vpn_sample_import',
            'value' => $marker,
        ),
    ),
));

if (count($products) !== count($expected)) {
    $failures[] = 'Expected ' . count($expected) . ' products, found ' . count($products) . '.';
}

foreach ($products as $product) {
    $slug = $product->post_name;
    $title = get_the_title($product);
    $body = (string) $product->post_content;
    $short = (string) $product->post_excerpt;
    $words = str_word_count(wp_strip_all_tags($body));
    $short_words = str_word_count(wp_strip_all_tags($short));
    $featured = (int) get_post_thumbnail_id($product->ID);
    $gallery = array_filter(array_map('absint', explode(',', (string) get_post_meta($product->ID, '_product_image_gallery', true))));
    $image_ids = array_merge(array($featured), $gallery);
    $unique_images = array_unique(array_filter($image_ids));
    $terms = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'slugs'));
    $specs = get_post_meta($product->ID, '_custom_box_product_specs', true);
    $moq = '';
    $figures = substr_count($body, '<figure class="product-inline-figure');

    if (!in_array($slug, $expected, true)) {
        $failures[] = $title . ': unexpected product slug ' . $slug . '.';
    }
    if ('publish' !== $product->post_status) {
        $failures[] = $title . ': product is not published.';
    }
    if ($words < 1500) {
        $failures[] = $title . ': content has fewer than 1500 words (' . $words . ').';
    }
    if ($short_words < 120 || $short_words > 180) {
        $failures[] = $title . ': short description is outside 120-180 words (' . $short_words . ').';
    }
    if (preg_match('#<h1\b#i', $body)) {
        $failures[] = $title . ': long description contains an H1.';
    }
    if ($figures < 4) {
        $failures[] = $title . ': missing expected inline product figures.';
    }
    if (!$featured) {
        $failures[] = $title . ': missing featured image.';
    }
    if (count($unique_images) < 4) {
        $failures[] = $title . ': has fewer than 4 unique product images.';
    }
    if (is_wp_error($terms) || !in_array('perfume-packaging-boxes', $terms, true)) {
        $failures[] = $title . ': missing perfume-packaging-boxes category.';
    }
    if (is_array($specs)) {
        foreach ($specs as $spec) {
            if (isset($spec['label'], $spec['value']) && 'Minimum Order Quantity (MOQ)' === $spec['label']) {
                $moq = (string) $spec['value'];
                break;
            }
        }
        if (count($specs) < 21) {
            $failures[] = $title . ': product specification table has fewer than 21 rows.';
        }
    } else {
        $failures[] = $title . ': product specification table is missing.';
    }
    if ('1000 boxes' !== $moq) {
        $failures[] = $title . ': MOQ is not 1000 boxes.';
    }
    if (!get_post_meta($product->ID, 'rank_math_focus_keyword', true)) {
        $failures[] = $title . ': missing Rank Math focus keyword.';
    }
    if (!get_post_meta($product->ID, 'rank_math_title', true) || !get_post_meta($product->ID, 'rank_math_description', true)) {
        $failures[] = $title . ': missing Rank Math title or description.';
    }

    echo $title . ': status=' . $product->post_status . ', words=' . $words . ', short_words=' . $short_words . ', images=' . count($unique_images) . ', figures=' . $figures . PHP_EOL;
}

foreach ($expected as $slug) {
    $found = get_page_by_path($slug, OBJECT, 'product');
    if (!$found || $marker !== get_post_meta($found->ID, '_vpn_sample_import', true)) {
        $failures[] = 'Missing expected perfume product: ' . $slug . '.';
    }
}

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'Verified perfume packaging products successfully.' . PHP_EOL;
