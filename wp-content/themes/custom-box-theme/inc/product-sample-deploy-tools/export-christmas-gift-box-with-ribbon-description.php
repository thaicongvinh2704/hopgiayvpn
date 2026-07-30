<?php
/**
 * Export a text-only review copy for the Christmas gift box product.
 *
 * Usage:
 *   php tools/export-christmas-gift-box-with-ribbon-description.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

$marker = 'product-samples-christmas-gift-box-202607';
$products = get_posts(array(
    'post_type'      => 'product',
    'post_status'    => array('publish', 'draft', 'private'),
    'posts_per_page' => 1,
    'meta_query'     => array(
        array(
            'key'   => '_vpn_sample_import',
            'value' => $marker,
        ),
    ),
));

if (!$products) {
    fwrite(STDERR, "Christmas gift box product was not found.\n");
    exit(1);
}

$product = $products[0];
$short_description = trim(
    preg_replace(
        '/\s+/',
        ' ',
        html_entity_decode(wp_strip_all_tags((string) $product->post_excerpt), ENT_QUOTES | ENT_HTML5, 'UTF-8')
    )
);
$long_description = html_entity_decode(
    wp_strip_all_tags(
        preg_replace(
            array('#</p>#i', '#</h[2-6]>#i', '#</figcaption>#i', '#<br\s*/?>#i'),
            "\n\n",
            (string) $product->post_content
        )
    ),
    ENT_QUOTES | ENT_HTML5,
    'UTF-8'
);
$long_description = trim(preg_replace("/[ \t]+\n/", "\n", preg_replace("/\n{3,}/", "\n\n", $long_description)));
$output = '# ' . get_the_title($product) . "\n\n"
    . '- Product ID: ' . $product->ID . "\n"
    . '- Slug: ' . $product->post_name . "\n"
    . '- Preview: ' . get_permalink($product->ID) . "\n"
    . '- Focus keyword: ' . get_post_meta($product->ID, 'rank_math_focus_keyword', true) . "\n"
    . '- Content duplicate risk: ' . get_post_meta($product->ID, '_vpn_content_duplicate_risk', true) . "\n"
    . '- Image duplicate risk: ' . get_post_meta($product->ID, '_vpn_image_duplicate_risk', true) . "\n\n"
    . "## Short Description\n\n"
    . $short_description . "\n\n"
    . "## Long Description\n\n"
    . $long_description . "\n";
$output_path = dirname(__DIR__) . '/product-samples-christmas-gift-box-202607-descriptions-text-only.md';

if (false === file_put_contents($output_path, $output)) {
    fwrite(STDERR, "Could not write text-only review file.\n");
    exit(1);
}

echo 'Exported: ' . $output_path . PHP_EOL;
