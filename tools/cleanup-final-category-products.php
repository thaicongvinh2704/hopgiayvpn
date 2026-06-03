<?php
/**
 * Trash broad overview products that are only import helpers, not final
 * products for the curated category grids.
 *
 * Usage:
 *   php tools/cleanup-final-category-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$overview_slugs = array(
	'custom-corporate-gift-set-packaging-boxes',
	'custom-cosmetic-skincare-packaging-boxes',
	'custom-home-lifestyle-product-packaging-boxes',
	'custom-pharmaceutical-medicine-packaging-boxes',
	'custom-phone-accessories-packaging-boxes',
	'custom-stationery-school-supplies-packaging-boxes',
	'custom-supplement-vitamin-packaging-boxes',
	'custom-teal-rigid-gift-box',
	'custom-wine-premium-beverage-packaging-boxes',
);

$trashed = 0;

foreach ( $overview_slugs as $slug ) {
	$products = get_posts(
		array(
			'name'           => $slug,
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( empty( $products[0] ) ) {
		continue;
	}

	$result = wp_trash_post( (int) $products[0] );

	if ( $result ) {
		++$trashed;
	}
}

echo 'Trashed overview/category helper products: ' . $trashed . PHP_EOL;
