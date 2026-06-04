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

function vpn_fashion_verify_image_ids( int $product_id, string $body ): array {
	$ids = array();

	$featured_id = (int) get_post_thumbnail_id( $product_id );
	if ( $featured_id ) {
		$ids[] = $featured_id;
	}

	$gallery = (string) get_post_meta( $product_id, '_product_image_gallery', true );
	foreach ( array_filter( array_map( 'absint', explode( ',', $gallery ) ) ) as $gallery_id ) {
		$ids[] = $gallery_id;
	}

	if ( preg_match_all( '/wp-image-([0-9]+)/', $body, $matches ) ) {
		foreach ( $matches[1] as $inline_id ) {
			$ids[] = absint( $inline_id );
		}
	}

	return array_values( array_filter( $ids ) );
}

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

	$image_ids           = vpn_fashion_verify_image_ids( (int) $product->ID, $body );
	$unique_image_ids    = array_values( array_unique( $image_ids ) );
	$duplicate_image_ids = array_diff_assoc( $image_ids, $unique_image_ids );
	$image_files         = array();

	foreach ( $unique_image_ids as $image_id ) {
		$file = get_attached_file( $image_id );
		if ( $file ) {
			$image_files[] = wp_normalize_path( $file );
		}
	}

	if ( count( $unique_image_ids ) < 2 ) {
		$failures[] = "{$title}: has fewer than 2 unique product images.";
	}

	if ( $duplicate_image_ids ) {
		$failures[] = "{$title}: reuses the same image attachment in featured, gallery, or inline content.";
	}

	if ( count( $image_files ) !== count( array_unique( $image_files ) ) ) {
		$failures[] = "{$title}: has multiple image attachments pointing to the same source file.";
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
