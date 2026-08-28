<?php
/**
 * Verify the local Custom Lunar New Year Gift Boxes product.
 *
 * Usage:
 *   php tools/verify-custom-lunar-new-year-gift-box-product.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'product-samples-custom-lunar-new-year-gift-box-202608';
$expected_title = 'Custom Lunar New Year Gift Boxes';
$expected_slug = 'custom-lunar-new-year-gift-boxes';
$expected_categories = array(
    'gift-paper-boxes',
    'rigid-boxes',
    'corporate-gift-packaging',
);
$expected_images = array(
    'custom-tet-gift-box-double-door-open' => array(
        'alt' => 'Custom red Tet gift box with two hinged doors open around a wine and food gift set',
        'title' => 'Custom Tet Gift Box Double-Door Open View',
        'caption' => 'A custom double-door Tet gift box designed for premium wine and gourmet gift sets.',
    ),
    'red-double-door-rigid-gift-box-front-view' => array(
        'alt' => 'Front view of red double-door rigid Lunar New Year gift box with gold festive artwork',
        'title' => 'Red Double-Door Rigid Gift Box Front View',
        'caption' => 'Front-facing view showing the symmetrical opening and structured multi-product interior.',
    ),
    'red-double-door-tet-gift-box-closed' => array(
        'alt' => 'Closed red double-door Tet gift box with large gold calligraphy, blossoms and lantern artwork',
        'title' => 'Closed Red Double-Door Tet Gift Box',
        'caption' => 'Closed presentation view showing the centered two-door design and gold Tet calligraphy.',
    ),
    'tet-gift-box-top-view-wine-snacks' => array(
        'alt' => 'Top view of open Tet gift box with wine bottle and divided snack compartments',
        'title' => 'Tet Gift Box Top View with Wine and Snacks',
        'caption' => 'Top view showing how the wine bottle and smaller gifts are separated into fitted compartments.',
    ),
    'gold-foil-tet-calligraphy-close-up' => array(
        'alt' => 'Macro close-up of metallic gold Tet calligraphy on textured red Lunar New Year gift box',
        'title' => 'Gold Foil Tet Calligraphy Close-Up',
        'caption' => 'Close-up emphasizing the metallic gold finish and textured red paper surface.',
    ),
);
$failures = array();

$products = get_posts(array(
    'post_type' => 'product',
    'post_status' => array('publish', 'draft', 'pending', 'private'),
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => '_vpn_sample_import',
            'value' => $marker,
        ),
    ),
));

if (1 !== count($products)) {
    $failures[] = 'Expected one product with the batch marker, found ' . count($products) . '.';
}

if ($products) {
    $product = $products[0];
    $product_id = (int) $product->ID;
    $body = (string) $product->post_content;
    $short_description = (string) $product->post_excerpt;
    preg_match_all('/[\p{L}\p{N}]+/u', wp_strip_all_tags($body), $word_matches);
    preg_match_all('/[\p{L}\p{N}]+/u', wp_strip_all_tags($short_description), $short_word_matches);
    $long_words = count($word_matches[0]);
    $short_words = count($short_word_matches[0]);
    $featured_id = (int) get_post_thumbnail_id($product_id);
    $gallery_ids = array_values(array_filter(array_map('absint', explode(',', (string) get_post_meta($product_id, '_product_image_gallery', true)))));
    $all_image_ids = array_values(array_unique(array_merge(array($featured_id), $gallery_ids)));
    sort($all_image_ids);

    $expected_image_ids = array();
    $attachment_counts = array();
    global $wpdb;

    foreach ($expected_images as $base => $image) {
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id ASC",
                '%' . $wpdb->esc_like($base) . '%'
            )
        );
        $matches = array();
        foreach ($ids as $id) {
            $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
            if ($base === pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
                $matches[] = (int) $id;
            }
        }

        $attachment_counts[$base] = count($matches);
        if (1 !== count($matches)) {
            $failures[] = 'Expected one exact attachment for ' . $base . ', found ' . count($matches) . '.';
            continue;
        }

        $attachment_id = $matches[0];
        $expected_image_ids[] = $attachment_id;
        $attachment = get_post($attachment_id);

        if (
            !$attachment
            || 'attachment' !== $attachment->post_type
            || 'inherit' !== $attachment->post_status
            || $product_id !== (int) $attachment->post_parent
            || $image['title'] !== $attachment->post_title
            || $image['caption'] !== $attachment->post_excerpt
            || $image['alt'] !== (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
            || !wp_attachment_is_image($attachment_id)
            || !wp_get_attachment_url($attachment_id)
        ) {
            $failures[] = 'Attachment metadata or parent is incorrect for ' . $base . '.';
        }
    }

    sort($expected_image_ids);

    if ($expected_title !== $product->post_title) {
        $failures[] = 'Unexpected product title: ' . $product->post_title . '.';
    }
    if ($expected_slug !== $product->post_name) {
        $failures[] = 'Unexpected product slug: ' . $product->post_name . '.';
    }
    if ('publish' !== $product->post_status) {
        $failures[] = 'Product is not published.';
    }
    if ($long_words < 1500) {
        $failures[] = 'Long description is too short: ' . $long_words . ' words.';
    }
    if ($short_words < 120 || $short_words > 180) {
        $failures[] = 'Short description should be 120-180 words: ' . $short_words . '.';
    }
    if (preg_match('/<h1\b/i', $body)) {
        $failures[] = 'Long description contains an H1.';
    }
    if (preg_match('/\{\{IMAGE_/i', $body)) {
        $failures[] = 'Unresolved canonical image placeholder remains.';
    }

    $inline_figures = preg_match_all('/<figure\b[^>]*class="[^"]*\bproduct-inline-figure-small\b[^"]*"/i', $body);
    if (4 !== $inline_figures) {
        $failures[] = 'Expected four distinct inline figures, found ' . $inline_figures . '.';
    }

    $internal_link_paths = array(
        '/products/gift-paper-boxes/',
        '/products/rigid-boxes/',
        '/products/corporate-gift-packaging/',
        '/contact/',
    );
    foreach ($internal_link_paths as $path) {
        if (false === strpos($body, 'href="' . $path . '"')) {
            $failures[] = 'Missing internal link: ' . $path;
        }
    }

    $terms = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));
    sort($terms);
    $sorted_expected_categories = $expected_categories;
    sort($sorted_expected_categories);
    if (is_wp_error($terms) || $terms !== $sorted_expected_categories) {
        $failures[] = 'Product category set is incorrect.';
    }

    if ($all_image_ids !== $expected_image_ids) {
        $failures[] = 'Featured/gallery image IDs do not match the five ZIP images.';
    }
    if ($featured_id !== ($expected_image_ids ? $expected_image_ids[0] : 0) && $featured_id) {
        $featured_file = (string) get_post_meta($featured_id, '_wp_attached_file', true);
        if ('custom-tet-gift-box-double-door-open' !== pathinfo(wp_basename($featured_file), PATHINFO_FILENAME)) {
            $failures[] = 'Featured image is not the approved open-box hero image.';
        }
    }

    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);
    $moq = '';
    if (!is_array($specs) || 21 !== count($specs)) {
        $failures[] = 'Expected exactly 21 product specification rows.';
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

    $model_number = '';
    if (is_array($specs)) {
        foreach ($specs as $spec) {
            if (isset($spec['label'], $spec['value']) && 'Model Number' === $spec['label']) {
                $model_number = (string) $spec['value'];
                break;
            }
        }
    }
    if ('CUSTOM LUNAR NEW YEAR GIFT BOXES' !== $model_number) {
        $failures[] = 'Model Number must use the product name rather than an invented identifier.';
    }

    $expected_seo = array(
        'rank_math_focus_keyword' => 'custom lunar new year gift boxes',
        'rank_math_title' => 'Custom Lunar New Year Gift Boxes | Tet Packaging Manufacturer',
        'rank_math_description' => 'Custom Lunar New Year gift boxes for Tet, corporate gifts, wine and premium foods. Custom sizes, inserts, printing and finishing from Vietnam.',
    );
    foreach ($expected_seo as $key => $value) {
        if ($value !== (string) get_post_meta($product_id, $key, true)) {
            $failures[] = 'SEO meta mismatch: ' . $key . '.';
        }
    }
    if (strlen($expected_seo['rank_math_description']) > 155) {
        $failures[] = 'Meta description exceeds 155 characters.';
    }
    if ((int) get_post_meta($product_id, 'rank_math_primary_product_cat', true) !== (int) get_term_by('slug', 'gift-paper-boxes', 'product_cat')->term_id) {
        $failures[] = 'Primary product category meta is incorrect.';
    }

    echo $product->post_title . ': status=' . $product->post_status
        . ', slug=' . $product->post_name
        . ', short_words=' . $short_words
        . ', long_words=' . $long_words
        . ', inline_figures=' . $inline_figures
        . ', total_images=' . count($all_image_ids)
        . ', specs=' . (is_array($specs) ? count($specs) : 0)
        . ', URL=' . get_permalink($product_id)
        . PHP_EOL;
    echo 'Exact attachment counts: ' . json_encode($attachment_counts, JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'Verified Custom Lunar New Year Gift Boxes successfully.' . PHP_EOL;
