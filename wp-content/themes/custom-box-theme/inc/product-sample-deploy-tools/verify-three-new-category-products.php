<?php
/**
 * Verify the local 21-product import.
 *
 * Run:
 *   php tools/verify-three-new-category-products.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

$expected_categories = array(
    'toy-and-game-packaging-boxes' => 7,
    'tea-and-coffee-packaging-boxes' => 7,
    'pet-product-packaging-boxes' => 7,
);
$products = get_posts(array(
    'post_type' => 'product',
    'post_status' => array('publish', 'draft', 'pending', 'private'),
    'posts_per_page' => -1,
    'meta_key' => '_vpn_sample_import',
    'meta_value' => 'product-samples-three-new-categories-202607',
));
$failures = array();

if (21 !== count($products)) {
    $failures[] = 'Expected 21 products, found ' . count($products) . '.';
}

foreach ($products as $product) {
    $content = (string) $product->post_content;
    $words = str_word_count(wp_strip_all_tags($content));
    $short_words = str_word_count(wp_strip_all_tags((string) $product->post_excerpt));
    $specs = get_post_meta($product->ID, '_custom_box_product_specs', true);
    $gallery = array_filter(array_map('absint', explode(',', (string) get_post_meta($product->ID, '_product_image_gallery', true))));
    $categories = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'slugs'));
    $thumbnail = (int) get_post_thumbnail_id($product->ID);
    $image_ids = array_merge(array($thumbnail), $gallery);
    $meta_description = (string) get_post_meta($product->ID, 'rank_math_description', true);
    $seo_title = (string) get_post_meta($product->ID, 'rank_math_title', true);
    $expected_seo_title = $product->post_title . ' | VPN PAPER BOX MANUFACTURER';
    preg_match_all('/<a\b[^>]*\bhref=(["\'])(.*?)\1/i', $content, $link_matches);
    $internal_links = array_filter($link_matches[2], static function (string $url): bool {
        return str_starts_with($url, '/') || str_starts_with($url, home_url('/'));
    });

    if ($words < 1500 || $words > 2000) {
        $failures[] = $product->post_name . ': long description words=' . $words . '.';
    }
    if ($short_words < 120 || $short_words > 180) {
        $failures[] = $product->post_name . ': short description words=' . $short_words . '.';
    }
    if (preg_match('/<h1\b/i', $content)) {
        $failures[] = $product->post_name . ': H1 found in content.';
    }
    if (4 !== substr_count($content, '<figure class="product-inline-figure')) {
        $failures[] = $product->post_name . ': expected 4 inline figures.';
    }
    if (3 !== count($gallery)) {
        $failures[] = $product->post_name . ': expected 3 gallery images.';
    }
    if (!$thumbnail) {
        $failures[] = $product->post_name . ': missing featured image.';
    }
    if (4 !== count(array_unique(array_filter($image_ids)))) {
        $failures[] = $product->post_name . ': expected 4 unique product images.';
    }
    foreach ($image_ids as $image_id) {
        $attached_file = get_attached_file($image_id);

        if ('attachment' !== get_post_type($image_id) || !wp_attachment_is_image($image_id)) {
            $failures[] = $product->post_name . ': invalid image attachment #' . $image_id . '.';
        } elseif (!$attached_file || !file_exists($attached_file)) {
            $failures[] = $product->post_name . ': image file missing for attachment #' . $image_id . '.';
        } elseif ((int) wp_get_post_parent_id($image_id) !== (int) $product->ID) {
            $failures[] = $product->post_name . ': attachment #' . $image_id . ' has the wrong parent.';
        }
    }
    if (!is_array($specs) || 21 !== count($specs)) {
        $failures[] = $product->post_name . ': expected 21 specs.';
    }
    if (!str_contains(wp_json_encode($specs), '1000 boxes')) {
        $failures[] = $product->post_name . ': MOQ mismatch.';
    }
    if (!get_post_meta($product->ID, 'rank_math_focus_keyword', true)) {
        $failures[] = $product->post_name . ': missing focus keyword.';
    }
    if (!$meta_description || strlen($meta_description) > 155) {
        $failures[] = $product->post_name . ': invalid meta description.';
    }
    if ($expected_seo_title !== $seo_title) {
        $failures[] = $product->post_name . ': SEO title format mismatch.';
    }
    if (count($internal_links) < 3 || count($internal_links) > 5) {
        $failures[] = $product->post_name . ': expected 3-5 internal links, found ' . count($internal_links) . '.';
    }
    if (is_wp_error($categories) || !array_intersect(array_keys($expected_categories), $categories)) {
        $failures[] = $product->post_name . ': missing one of the new primary categories.';
    }

    $response = wp_remote_get(get_permalink($product), array(
        'redirection' => 3,
        'timeout' => 5,
    ));
    $status_code = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);

    if (200 !== $status_code) {
        $failures[] = $product->post_name . ': product URL returned HTTP ' . $status_code . '.';
    }
}

foreach ($expected_categories as $slug => $expected_count) {
    $term = get_term_by('slug', $slug, 'product_cat');

    if (!$term || is_wp_error($term)) {
        $failures[] = $slug . ': category missing.';
        continue;
    }

    $ids = get_posts(array(
        'post_type' => 'product',
        'post_status' => array('publish', 'draft', 'pending', 'private'),
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_key' => '_vpn_sample_import',
        'meta_value' => 'product-samples-three-new-categories-202607',
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $slug,
        )),
    ));

    if ($expected_count !== count($ids)) {
        $failures[] = $slug . ': expected ' . $expected_count . ' batch products, found ' . count($ids) . '.';
    }
}

if ($failures) {
    echo "FAILED\n";
    foreach ($failures as $failure) {
        echo '- ' . $failure . PHP_EOL;
    }

    if ('cli' === PHP_SAPI) {
        exit(1);
    }

    throw new RuntimeException('Three-category product verification failed with ' . count($failures) . ' issue(s).');
}

echo "PASS\n";
echo 'Products: ' . count($products) . PHP_EOL;
echo "Categories: 7 toy/game, 7 tea/coffee, 7 pet product\n";
echo "Each product: HTTP 200, 1500-2000 words, 120-180 short words, 0 H1, 21 specs, 5 internal links, 4 attached images, 4 inline figures, 3 gallery images, featured image, Rank Math fields.\n";
