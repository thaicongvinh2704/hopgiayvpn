<?php
/**
 * Verify the June 2026 sports packaging product import.
 *
 * Run:
 *   php tools/verify-sports-packaging-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$marker   = 'product-samples-sports-packaging';
$expected = 4;
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
	$terms     = wp_get_post_terms( $product->ID, 'product_cat', array( 'fields' => 'slugs' ) );
	$specs     = get_post_meta( $product->ID, '_custom_box_product_specs', true );

	if ( 'publish' !== $product->post_status ) {
		$failures[] = $title . ': product is not published.';
	}
	if ( $words < 900 ) {
		$failures[] = $title . ': content has fewer than 900 words.';
	}
	if ( preg_match( '#<h1\b#i', $body ) ) {
		$failures[] = $title . ': long description contains an H1.';
	}
	if ( false === strpos( $body, 'product-inline-figure-small' ) ) {
		$failures[] = $title . ': missing inline product images.';
	}
	if ( ! $featured ) {
		$failures[] = $title . ': missing featured image.';
	}
	if ( count( array_unique( array_filter( $image_ids ) ) ) < 4 ) {
		$failures[] = $title . ': has fewer than 4 unique product images.';
	}
	if ( is_wp_error( $terms ) || ! in_array( 'sports-packaging-boxes', $terms, true ) ) {
		$failures[] = $title . ': missing Sports Packaging Boxes category.';
	}
	if ( ! is_array( $specs ) || count( $specs ) < 20 ) {
		$failures[] = $title . ': product specification table is incomplete.';
	}
	if ( ! get_post_meta( $product->ID, 'rank_math_focus_keyword', true ) ) {
		$failures[] = $title . ': missing Rank Math focus keyword.';
	}

	echo $title . ': words=' . $words . ', images=' . count( array_filter( $image_ids ) ) . PHP_EOL;
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
	}
	exit( 1 );
}

echo 'Verified ' . count( $products ) . ' sports packaging products successfully.' . PHP_EOL;
