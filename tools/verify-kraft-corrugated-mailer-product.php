<?php
/**
 * Verify kraft corrugated mailer product import.
 *
 * Run:
 *   php tools/verify-kraft-corrugated-mailer-product.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$marker   = 'product-samples-kraft-corrugated-mailer';
$failures = array();
$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_vpn_sample_import',
				'value' => $marker,
			),
		),
	)
);

if ( 1 !== count( $products ) ) {
	$failures[] = 'Expected 1 product, found ' . count( $products ) . '.';
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
	$moq       = '';

	if ( $words < 900 ) {
		$failures[] = $title . ': content has fewer than 900 words.';
	}
	if ( preg_match( '#<h1\b#i', $body ) ) {
		$failures[] = $title . ': long description contains an H1.';
	}
	if ( false === strpos( $body, '<figure>' ) || ! preg_match( '#style="[^"]*width\s*:\s*100%;\s*height\s*:\s*auto;?[^"]*"#i', $body ) ) {
		$failures[] = $title . ': missing clean figure image HTML.';
	}
	if ( ! $featured ) {
		$failures[] = $title . ': missing featured image.';
	}
	if ( count( array_unique( array_filter( $image_ids ) ) ) < 4 ) {
		$failures[] = $title . ': has fewer than 4 unique product images.';
	}
	if ( is_wp_error( $terms ) || ! in_array( 'corrugated-mailer-boxes', $terms, true ) ) {
		$failures[] = $title . ': missing Corrugated Mailer Boxes category.';
	}
	if ( is_array( $specs ) ) {
		foreach ( $specs as $spec ) {
			if ( isset( $spec['label'], $spec['value'] ) && 'Minimum Order Quantity (MOQ)' === $spec['label'] ) {
				$moq = (string) $spec['value'];
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

	echo $title . ': status=' . $product->post_status . ', words=' . $words . ', images=' . count( array_filter( $image_ids ) ) . PHP_EOL;
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
	}
	exit( 1 );
}

echo 'Verified kraft corrugated mailer product successfully.' . PHP_EOL;
