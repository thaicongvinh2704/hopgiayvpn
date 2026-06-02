<?php
/**
 * Verify Batch 4 remaining WooCommerce product samples.
 *
 * Usage:
 *   php tools/verify-product-samples-batch-4-remaining.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$marker   = 'product-samples-batch-4-remaining';
$expected = 17;

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'   => '_vpn_sample_import',
				'value' => $marker,
			),
		),
	)
);

$failures = array();

if ( count( $products ) !== $expected ) {
	$failures[] = sprintf( 'Expected %d products, found %d.', $expected, count( $products ) );
}

foreach ( $products as $product ) {
	$title = get_the_title( $product );
	$body  = (string) $product->post_content;
	$specs = get_post_meta( $product->ID, '_custom_box_product_specs', true );
	$moq   = '';

	if ( preg_match( '#<h1\b#i', $body ) ) {
		$failures[] = "{$title}: long description contains an H1.";
	}

	if ( false === strpos( $body, 'product-inline-figure-small' ) ) {
		$failures[] = "{$title}: missing small inline image class.";
	}

	if ( ! has_post_thumbnail( $product->ID ) ) {
		$failures[] = "{$title}: missing featured image.";
	}

	if ( is_array( $specs ) ) {
		foreach ( $specs as $spec ) {
			if ( isset( $spec['label'], $spec['value'] ) && 'Minimum Order Quantity (MOQ)' === $spec['label'] ) {
				$moq = (string) $spec['value'];
			}
		}

		if ( count( $specs ) < 20 ) {
			$failures[] = "{$title}: has fewer than 20 specification rows.";
		}
	} else {
		$failures[] = "{$title}: specifications are missing or invalid.";
	}

	if ( '1000 boxes' !== $moq ) {
		$failures[] = "{$title}: MOQ is not 1000 boxes.";
	}

	$terms = wp_get_post_terms( $product->ID, 'product_cat', array( 'fields' => 'slugs' ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		$failures[] = "{$title}: missing product category.";
	}
}

if ( $failures ) {
	echo "Batch 4 verification failed:\n";
	foreach ( $failures as $failure ) {
		echo '- ' . $failure . "\n";
	}
	exit( 1 );
}

echo "Batch 4 verification passed: {$expected} products.\n";
