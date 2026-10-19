<?php
/**
 * Plugin Name: VPN Four Product Enquiry Schema
 * Description: Align structured data with the four October 19 custom-order products.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;

add_filter('rank_math/json_ld', static function (array $data): array {
    if (!function_exists('is_product') || !is_product()
        || get_post_meta(get_queried_object_id(), '_vpn_sample_import', true) !== 'four-products-20261019-v1') {
        return $data;
    }
    $product = wc_get_product(get_queried_object_id());
    if (!$product) { return $data; }
    foreach ($data as &$entity) {
        if (!is_array($entity) || !in_array('Product', (array) ($entity['@type'] ?? []), true)) { continue; }
        // The search title includes an enquiry CTA; Product.name identifies the actual item.
        $entity['name'] = $product->get_name();
        $entity['description'] = wp_strip_all_tags($product->get_short_description());
        $entity['url'] = get_permalink($product->get_id());
        $entity['sku'] = $product->get_sku();
        // A made-to-order enquiry is not a verified retail price or customer rating.
        if ($product->get_price() === '') { unset($entity['offers']); }
        if (!$product->get_review_count()) { unset($entity['aggregateRating'], $entity['review']); }
    }
    unset($entity);
    return $data;
}, 110);
