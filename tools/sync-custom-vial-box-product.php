<?php
/**
 * Sync the Custom Vial Boxes product SEO/content update.
 *
 * Usage:
 *   php tools/sync-custom-vial-box-product.php
 */

require_once dirname(__DIR__) . '/wp-load.php';

if (!function_exists('custom_box_sync_custom_vial_boxes_product')) {
    fwrite(STDERR, "Custom vial boxes sync helper is not available.\n");
    exit(1);
}

$product_id = custom_box_sync_custom_vial_boxes_product(true);

if (is_wp_error($product_id)) {
    fwrite(STDERR, $product_id->get_error_message() . PHP_EOL);
    exit(1);
}

$product = get_post((int) $product_id);

if (!$product || 'product' !== $product->post_type) {
    fwrite(STDERR, "Synced product could not be loaded.\n");
    exit(1);
}

$content = (string) $product->post_content;
$faq = (string) get_post_meta($product->ID, '_custom_box_product_faq_html', true);

echo 'Product ID: ' . (int) $product->ID . PHP_EOL;
echo 'Status: ' . get_post_status($product->ID) . PHP_EOL;
echo 'Title: ' . $product->post_title . PHP_EOL;
echo 'Slug: ' . $product->post_name . PHP_EOL;
echo 'URL: ' . get_permalink($product->ID) . PHP_EOL;
echo 'Rank Math title: ' . get_post_meta($product->ID, 'rank_math_title', true) . PHP_EOL;
echo 'Rank Math description: ' . get_post_meta($product->ID, 'rank_math_description', true) . PHP_EOL;
echo 'Focus keyword: ' . get_post_meta($product->ID, 'rank_math_focus_keyword', true) . PHP_EOL;
echo 'Long description words: ' . str_word_count(wp_strip_all_tags($content)) . PHP_EOL;
echo 'Content H1 count: ' . preg_match_all('/<h1\b/i', $content) . PHP_EOL;
echo 'Image grids/cards: ' . substr_count($content, 'product-content-image-grid') . '/' . substr_count($content, 'product-content-image-card') . PHP_EOL;
echo 'FAQ items: ' . substr_count($faq, 'faq-item') . PHP_EOL;
echo 'Featured image ID: ' . (int) get_post_thumbnail_id($product->ID) . PHP_EOL;
echo 'Old all-caps phrase in content: ' . substr_count($content . ' ' . $product->post_excerpt . ' ' . $faq, 'CUSTOM VIAL PACKAGING BOX') . PHP_EOL;
