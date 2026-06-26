<?php
/**
 * Verify jewelry paper box product import.
 *
 * Run:
 *   php tools/verify-jewelry-paper-box-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$marker   = 'product-samples-jewelry-paper-boxes';
$expected = array(
	'custom-bracelet-packaging-box-with-pillow-insert' => 'jewelry-paper-boxes',
	'custom-earring-box-with-foam-insert'              => 'jewelry-paper-boxes',
	'custom-necklace-paper-box-with-logo'              => 'jewelry-paper-boxes',
	'custom-ring-box-with-velvet-insert'               => 'jewelry-paper-boxes',
);
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

if ( count( $products ) !== count( $expected ) ) {
	$failures[] = 'Expected ' . count( $expected ) . ' products, found ' . count( $products ) . '.';
}

foreach ( $products as $product ) {
	$slug      = $product->post_name;
	$title     = get_the_title( $product );
	$body      = (string) $product->post_content;
	$words     = str_word_count( wp_strip_all_tags( $body ) );
	$featured  = (int) get_post_thumbnail_id( $product->ID );
	$gallery   = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product->ID, '_product_image_gallery', true ) ) ) );
	$image_ids = array_merge( array( $featured ), $gallery );
	$terms     = wp_get_post_terms( $product->ID, 'product_cat', array( 'fields' => 'slugs' ) );
	$specs     = get_post_meta( $product->ID, '_custom_box_product_specs', true );
	$moq       = '';
	$figures   = substr_count( $body, 'product-inline-figure' );

	if ( ! isset( $expected[ $slug ] ) ) {
		$failures[] = $title . ': unexpected product slug ' . $slug . '.';
	}
	if ( $words < 1500 ) {
		$failures[] = $title . ': content has fewer than 1500 words.';
	}
	if ( preg_match( '#<h1\b#i', $body ) ) {
		$failures[] = $title . ': long description contains an H1.';
	}
	if ( $figures < 3 ) {
		$failures[] = $title . ': missing expected inline product figures.';
	}
	if ( ! $featured ) {
		$failures[] = $title . ': missing featured image.';
	}
	if ( count( array_unique( array_filter( $image_ids ) ) ) < 4 ) {
		$failures[] = $title . ': has fewer than 4 unique product images.';
	}
	if ( isset( $expected[ $slug ] ) && ( is_wp_error( $terms ) || ! in_array( $expected[ $slug ], $terms, true ) ) ) {
		$failures[] = $title . ': missing expected category ' . $expected[ $slug ] . '.';
	}
	if ( is_array( $specs ) ) {
		foreach ( $specs as $spec ) {
			if ( isset( $spec['label'], $spec['value'] ) && 'Minimum Order Quantity (MOQ)' === $spec['label'] ) {
				$moq = (string) $spec['value'];
				break;
			}
		}

		if ( count( $specs ) < 21 ) {
			$failures[] = $title . ': product specification table has fewer than 21 rows.';
		}
	} else {
		$failures[] = $title . ': product specification table is missing.';
	}
	if ( '1000 boxes' !== $moq ) {
		$failures[] = $title . ': MOQ is not 1000 boxes.';
	}
	if ( ! get_post_meta( $product->ID, 'rank_math_focus_keyword', true ) ) {
		$failures[] = $title . ': missing Rank Math focus keyword.';
	}

	echo $title . ': status=' . $product->post_status . ', words=' . $words . ', images=' . count( array_filter( $image_ids ) ) . ', figures=' . $figures . PHP_EOL;
}

foreach ( array_keys( $expected ) as $slug ) {
	$found = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $found || $marker !== get_post_meta( $found->ID, '_vpn_sample_import', true ) ) {
		$failures[] = 'Missing expected jewelry product: ' . $slug . '.';
	}
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
	}
	exit( 1 );
}

echo 'Verified jewelry paper box products successfully.' . PHP_EOL;
