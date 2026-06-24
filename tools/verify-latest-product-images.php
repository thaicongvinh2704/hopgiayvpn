<?php
/**
 * Verify latest June 2026 product image import.
 *
 * Run:
 *   php tools/verify-latest-product-images.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$marker   = 'product-samples-latest-20260624';
$expected = 7;
$failures = array();
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

if ( count( $products ) !== $expected ) {
	$failures[] = 'Expected ' . $expected . ' products, found ' . count( $products ) . '.';
}

foreach ( $products as $product ) {
	$title     = get_the_title( $product );
	$body      = (string) $product->post_content;
	$words     = str_word_count( wp_strip_all_tags( $body ) );
	$featured  = (int) get_post_thumbnail_id( $product->ID );
	$gallery   = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product->ID, '_product_image_gallery', true ) ) ) );
	$image_ids = array_merge( array( $featured ), $gallery );
	$specs     = get_post_meta( $product->ID, '_custom_box_product_specs', true );

	if ( 'publish' !== $product->post_status ) {
		$failures[] = $title . ': product is not published.';
	}

	if ( $words < 1500 ) {
		$failures[] = $title . ': content has fewer than 1500 words.';
	}

	if ( preg_match( '#<h1\b#i', $body ) ) {
		$failures[] = $title . ': long description contains an H1.';
	}

	if ( substr_count( $body, 'product-inline-figure-small' ) < 4 ) {
		$failures[] = $title . ': has fewer than 4 inline product images.';
	}

	if ( ! $featured ) {
		$failures[] = $title . ': missing featured image.';
	}

	if ( count( array_unique( array_filter( $image_ids ) ) ) < 4 ) {
		$failures[] = $title . ': has fewer than 4 unique product images.';
	}

	if ( ! is_array( $specs ) || count( $specs ) < 21 ) {
		$failures[] = $title . ': product specification table is incomplete.';
	}

	if ( '1000 boxes' !== wp_list_pluck( $specs, 'value', 'label' )['Minimum Order Quantity (MOQ)'] ?? '' ) {
		$failures[] = $title . ': MOQ is not 1000 boxes.';
	}

	if ( ! get_post_meta( $product->ID, 'rank_math_focus_keyword', true ) ) {
		$failures[] = $title . ': missing Rank Math focus keyword.';
	}

	echo $title . ': words=' . $words . ', images=' . count( array_filter( $image_ids ) ) . ', url=' . get_permalink( $product->ID ) . PHP_EOL;
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
	}
	exit( 1 );
}

echo 'Verified ' . count( $products ) . ' latest product image products successfully.' . PHP_EOL;
