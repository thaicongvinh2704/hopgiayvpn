<?php
/**
 * Verify Fashion and Sportswear Packaging products.
 *
 * Usage:
 *   php tools/verify-fashion-sportswear-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$marker   = 'product-samples-fashion-sportswear';
$expected = 6;
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
	$words = str_word_count( wp_strip_all_tags( $body ) );
	$specs = get_post_meta( $product->ID, '_custom_box_product_specs', true );
	$moq   = '';

	if ( $words < 900 ) {
		$failures[] = "{$title}: content has fewer than 900 words.";
	}

	if ( preg_match( '#<h1\b#i', $body ) ) {
		$failures[] = "{$title}: long description contains an H1.";
	}

	if ( false === strpos( $body, 'product-inline-figure-small' ) ) {
		$failures[] = "{$title}: missing small inline image class.";
	}

	if ( ! has_post_thumbnail( $product->ID ) ) {
		$failures[] = "{$title}: missing featured image.";
	}

	$terms = wp_get_post_terms( $product->ID, 'product_cat', array( 'fields' => 'slugs' ) );
	if ( is_wp_error( $terms ) || ! in_array( 'fashion-sportswear-packaging', $terms, true ) ) {
		$failures[] = "{$title}: missing Fashion and Sportswear Packaging category.";
	}

	if ( is_array( $specs ) ) {
		foreach ( $specs as $spec ) {
			if ( isset( $spec['label'], $spec['value'] ) && 'Minimum Order Quantity (MOQ)' === $spec['label'] ) {
				$moq = (string) $spec['value'];
			}
		}

		if ( count( $specs ) < 21 ) {
			$failures[] = "{$title}: has fewer than 21 specification rows.";
		}
	} else {
		$failures[] = "{$title}: specifications are missing or invalid.";
	}

	if ( '1000 boxes' !== $moq ) {
		$failures[] = "{$title}: MOQ is not 1000 boxes.";
	}

	if ( '' === get_post_meta( $product->ID, 'rank_math_title', true ) ) {
		$failures[] = "{$title}: missing Rank Math SEO title.";
	}

	if ( '' === get_post_meta( $product->ID, 'rank_math_description', true ) ) {
		$failures[] = "{$title}: missing Rank Math SEO description.";
	}
}

if ( $failures ) {
	echo "Fashion and Sportswear verification failed:\n";
	foreach ( $failures as $failure ) {
		echo '- ' . $failure . "\n";
	}
	exit( 1 );
}

echo "Fashion and Sportswear verification passed: {$expected} products.\n";
