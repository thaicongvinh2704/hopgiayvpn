<?php
/**
 * Verify the six-product paper shopping bag batch.
 *
 * Run:
 *   php tools/verify-paper-shopping-bag-products-202607.php
 */

require_once dirname(__DIR__) . '/wp-load.php';
require_once __DIR__ . '/import-paper-shopping-bag-products-202607.php';

$expected = array();
foreach (vpn_bag_202607_products() as $product) {
    $expected[$product['slug']] = count($product['images']);
}

$products = get_posts(array(
    'post_type' => 'product',
    'post_status' => array('publish', 'draft', 'private'),
    'posts_per_page' => -1,
    'meta_key' => '_vpn_sample_import',
    'meta_value' => VPN_PAPER_BAG_202607_MARKER,
));
$failures = array();

if (count($products) !== count($expected)) {
    $failures[] = 'Expected 6 products, found ' . count($products) . '.';
}

foreach ($products as $product) {
    $slug = $product->post_name;
    $content = (string) $product->post_content;
    $words = str_word_count(wp_strip_all_tags($content));
    $short_words = str_word_count(wp_strip_all_tags((string) $product->post_excerpt));
    $featured = (int) get_post_thumbnail_id($product->ID);
    $gallery = array_filter(array_map('absint', explode(',', (string) get_post_meta($product->ID, '_product_image_gallery', true))));
    $images = array_values(array_unique(array_filter(array_merge(array($featured), $gallery))));
    $specs = get_post_meta($product->ID, '_custom_box_product_specs', true);
    $categories = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'slugs'));
    $meta = (string) get_post_meta($product->ID, 'rank_math_description', true);
    $seo_title = (string) get_post_meta($product->ID, 'rank_math_title', true);
    preg_match_all('/<a\b[^>]*\bhref=(["\'])(.*?)\1/i', $content, $link_matches);
    $internal_links = array_filter($link_matches[2], static function (string $url): bool {
        return str_starts_with($url, '/') || str_starts_with($url, home_url('/'));
    });

    if (!isset($expected[$slug])) {
        $failures[] = $slug . ': unexpected product.';
        continue;
    }
    if ('publish' !== $product->post_status) {
        $failures[] = $slug . ': status=' . $product->post_status . '.';
    }
    if ($words < 1500 || $words > 2000) {
        $failures[] = $slug . ': long words=' . $words . '.';
    }
    if ($short_words < 120 || $short_words > 180) {
        $failures[] = $slug . ': short words=' . $short_words . '.';
    }
    if (preg_match('/<h1\b/i', $content)) {
        $failures[] = $slug . ': H1 found.';
    }
    if (4 !== substr_count($content, '<figure class="product-inline-figure')) {
        $failures[] = $slug . ': expected 4 inline figures.';
    }
    if ($expected[$slug] !== count($images)) {
        $failures[] = $slug . ': expected ' . $expected[$slug] . ' unique images, found ' . count($images) . '.';
    }
    if (($expected[$slug] - 1) !== count($gallery)) {
        $failures[] = $slug . ': gallery count mismatch.';
    }
    foreach ($images as $image_id) {
        $file = get_attached_file($image_id);
        if ('attachment' !== get_post_type($image_id) || !wp_attachment_is_image($image_id) || !$file || !file_exists($file)) {
            $failures[] = $slug . ': invalid image #' . $image_id . '.';
        }
        if ((int) wp_get_post_parent_id($image_id) !== (int) $product->ID) {
            $failures[] = $slug . ': wrong parent for image #' . $image_id . '.';
        }
    }
    if (!is_array($specs) || 21 !== count($specs) || !str_contains(wp_json_encode($specs), '1000 boxes')) {
        $failures[] = $slug . ': specs or MOQ mismatch.';
    }
    if (is_wp_error($categories) || !in_array('paper-bags-with-logo', $categories, true)) {
        $failures[] = $slug . ': category mismatch.';
    }
    if (!get_post_meta($product->ID, 'rank_math_focus_keyword', true)) {
        $failures[] = $slug . ': focus keyword missing.';
    }
    if ($seo_title !== $product->post_title . ' | VPN PAPER BOX MANUFACTURER') {
        $failures[] = $slug . ': SEO title mismatch.';
    }
    if (!$meta || strlen($meta) > 155) {
        $failures[] = $slug . ': meta description invalid.';
    }
    if (4 !== count($internal_links)) {
        $failures[] = $slug . ': expected 4 internal links, found ' . count($internal_links) . '.';
    }

    $response = wp_remote_get(get_permalink($product), array('redirection' => 3, 'timeout' => 5));
    $status = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);
    if (200 !== $status) {
        $failures[] = $slug . ': HTTP ' . $status . '.';
    }

    echo $product->post_title . ': id=' . $product->ID . ' words=' . $words . ' short=' . $short_words
        . ' images=' . count($images) . ' figures=4 HTTP=' . $status . PHP_EOL;
}

if ($failures) {
    echo "FAILED\n";
    foreach ($failures as $failure) {
        echo '- ' . $failure . PHP_EOL;
    }
    exit(1);
}

echo "PASS\n";
echo "Six paper shopping bag products verified: published, HTTP 200, 1500-2000 long words, 120-180 short words, 0 H1, 21 specs, MOQ 1000 boxes, 4 internal links, attached media, galleries, and four inline figures.\n";
