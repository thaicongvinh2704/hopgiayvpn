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

echo custom_box_custom_vial_boxes_sync_report((int) $product->ID);
