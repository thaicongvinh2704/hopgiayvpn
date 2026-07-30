<?php
/**
 * Verify the local custom Christmas gift box with ribbon product.
 *
 * Usage:
 *   php tools/verify-christmas-gift-box-with-ribbon-product.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'product-samples-christmas-gift-box-202607';
$expected_slug = 'custom-christmas-gift-box-with-ribbon';
$expected_categories = array(
    'gift-paper-boxes',
    'rigid-boxes',
    'lid-and-base-boxes',
    'corporate-gift-packaging',
);
$expected_image_bases = array(
    'green-christmas-gift-box-with-ribbon',
    'ivory-christmas-gift-box-with-ribbon',
    'navy-blue-christmas-gift-box-with-ribbon',
    'red-christmas-gift-box-with-ribbon',
);
$failures = array();

function vpn_christmas_ribbon_box_shingles(string $text, int $size = 5): array
{
    $normalized = strtolower(wp_strip_all_tags($text));
    $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized);
    $words = array_values(array_filter(explode(' ', trim((string) $normalized))));
    $shingles = array();
    $word_count = count($words);

    for ($index = 0; $index <= $word_count - $size; $index++) {
        $shingles[implode(' ', array_slice($words, $index, $size))] = true;
    }

    return $shingles;
}

function vpn_christmas_ribbon_box_similarity(array $left, array $right): float
{
    if (!$left || !$right) {
        return 0.0;
    }

    $intersection = count(array_intersect_key($left, $right));
    $union = count($left + $right);

    return $union ? $intersection / $union : 0.0;
}

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

if (1 !== count($products)) {
    $failures[] = 'Expected 1 product, found ' . count($products) . '.';
}

foreach ($products as $product) {
    $body = (string) $product->post_content;
    $short_description = (string) $product->post_excerpt;
    $words = str_word_count(wp_strip_all_tags($body));
    $short_words = str_word_count(wp_strip_all_tags($short_description));
    $featured = (int) get_post_thumbnail_id($product->ID);
    $gallery = array_values(array_filter(array_map('absint', explode(',', (string) get_post_meta($product->ID, '_product_image_gallery', true)))));
    $image_ids = array_values(array_unique(array_merge(array($featured), $gallery)));
    sort($image_ids);
    $expected_image_ids = array();

    foreach ($expected_image_bases as $expected_image_base) {
        $matching_attachments = get_posts(array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_wp_attached_file',
                    'value'   => '/' . $expected_image_base . '.',
                    'compare' => 'LIKE',
                ),
            ),
        ));

        if ($matching_attachments) {
            $expected_image_ids[] = (int) $matching_attachments[0];
        }
    }

    sort($expected_image_ids);
    $terms = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'slugs'));
    $specs = get_post_meta($product->ID, '_custom_box_product_specs', true);
    $moq = '';
    $inline_figures = preg_match_all('#<figure\b[^>]*class="[^"]*\bproduct-inline-figure-small\b[^"]*"#i', $body);
    $internal_links = preg_match_all('#<a\s+[^>]*href="[^"]+"#i', $body);
    $h2_count = preg_match_all('#<h2\b#i', $body);
    $target_shingles = vpn_christmas_ribbon_box_shingles($body);
    $highest_similarity = 0.0;
    $most_similar_title = '';
    $comparison_products = get_posts(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'post__not_in'   => array($product->ID),
    ));

    foreach ($comparison_products as $comparison_product) {
        if (!$comparison_product->post_content) {
            continue;
        }

        $similarity = vpn_christmas_ribbon_box_similarity(
            $target_shingles,
            vpn_christmas_ribbon_box_shingles((string) $comparison_product->post_content)
        );

        if ($similarity > $highest_similarity) {
            $highest_similarity = $similarity;
            $most_similar_title = get_the_title($comparison_product);
        }
    }

    if ('publish' !== $product->post_status) {
        $failures[] = 'Product is not published locally.';
    }
    if ($expected_slug !== $product->post_name) {
        $failures[] = 'Unexpected product slug: ' . $product->post_name . '.';
    }
    if ($words < 1500 || $words > 2000) {
        $failures[] = 'Long description word count must be 1500-2000; found ' . $words . '.';
    }
    if ($short_words < 120 || $short_words > 180) {
        $failures[] = 'Short description word count must be 120-180; found ' . $short_words . '.';
    }
    if (preg_match('#<h1\b#i', $body)) {
        $failures[] = 'Long description contains an H1.';
    }
    if ($h2_count < 7 || $h2_count > 10) {
        $failures[] = 'Expected 7-10 H2 sections; found ' . $h2_count . '.';
    }
    if (2 !== $inline_figures) {
        $failures[] = 'Expected exactly 2 non-repetitive inline figures; found ' . $inline_figures . '.';
    }
    if ($internal_links < 3 || $internal_links > 5) {
        $failures[] = 'Expected 3-5 internal links; found ' . $internal_links . '.';
    }
    if ($image_ids !== $expected_image_ids) {
        $failures[] = 'Featured/gallery image IDs do not match the four uploaded originals.';
    }
    if (is_wp_error($terms)) {
        $failures[] = 'Could not read product categories.';
    } else {
        foreach ($expected_categories as $expected_category) {
            if (!in_array($expected_category, $terms, true)) {
                $failures[] = 'Missing category: ' . $expected_category . '.';
            }
        }
    }
    if (!is_array($specs) || 21 !== count($specs)) {
        $failures[] = 'Product specification table must contain exactly 21 rows.';
    } else {
        foreach ($specs as $spec) {
            if (isset($spec['label'], $spec['value']) && 'Minimum Order Quantity (MOQ)' === $spec['label']) {
                $moq = (string) $spec['value'];
            }
        }
    }
    if ('1000 boxes' !== $moq) {
        $failures[] = 'MOQ is not 1000 boxes.';
    }
    if ('custom Christmas gift box with ribbon' !== get_post_meta($product->ID, 'rank_math_focus_keyword', true)) {
        $failures[] = 'Rank Math focus keyword is missing or incorrect.';
    }
    if (strlen((string) get_post_meta($product->ID, 'rank_math_description', true)) > 155) {
        $failures[] = 'Meta description exceeds 155 characters.';
    }
    if ($highest_similarity >= 0.20) {
        $failures[] = 'Five-word shingle similarity is too high: '
            . number_format($highest_similarity * 100, 2)
            . '% against ' . $most_similar_title . '.';
    }

    foreach ($expected_image_ids as $image_id) {
        if (!$image_id || !wp_attachment_is_image($image_id)) {
            $failures[] = 'Attachment is missing or invalid: ' . $image_id . '.';
        }
        if (!(string) get_post_meta($image_id, '_wp_attachment_image_alt', true)) {
            $failures[] = 'Attachment alt text is missing: ' . $image_id . '.';
        }
    }

    echo get_the_title($product) . ': status=' . $product->post_status
        . ', slug=' . $product->post_name
        . ', short_words=' . $short_words
        . ', long_words=' . $words
        . ', h2=' . $h2_count
        . ', figures=' . $inline_figures
        . ', links=' . $internal_links
        . ', images=' . count($image_ids)
        . ', specs=' . (is_array($specs) ? count($specs) : 0)
        . PHP_EOL;
    echo 'Highest five-word shingle similarity: '
        . number_format($highest_similarity * 100, 2)
        . '% against ' . ($most_similar_title ?: 'none')
        . PHP_EOL;
    echo 'Preview: ' . get_permalink($product->ID) . PHP_EOL;
}

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'Verified custom Christmas gift box with ribbon product successfully.' . PHP_EOL;
