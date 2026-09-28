<?php
/** Verify the Pizza Boxes category and its five deploy-managed products. */

if ( ! defined( 'ABSPATH' ) ) {
	$project_root = 'product-sample-deploy-tools' === basename( __DIR__ )
		? dirname( __DIR__, 5 )
		: dirname( __DIR__ );
	$wp_load = $project_root . '/wp-load.php';
	if ( file_exists( $wp_load ) ) {
		require_once $wp_load;
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	throw new RuntimeException( 'WordPress is not available for the Pizza Boxes verification.' );
}

$marker = 'product-samples-pizza-boxes-20260928';
$slugs  = array(
	'kraft-pizza-delivery-box',
	'personal-kraft-pizza-box',
	'rectangular-flatbread-pizza-box',
	'white-kraft-pizza-box',
	'white-printed-pizza-box',
);
$expected_figures = array(
	'kraft-pizza-delivery-box'      => 3,
	'personal-kraft-pizza-box'      => 3,
	'rectangular-flatbread-pizza-box' => 4,
	'white-kraft-pizza-box'         => 4,
	'white-printed-pizza-box'       => 3,
);
$term     = get_term_by( 'slug', 'pizza-boxes', 'product_cat' );
$failures = array();
$results  = array();
$category_data = function_exists( 'custom_box_pizza_boxes_category_data' ) ? custom_box_pizza_boxes_category_data() : array();

if ( ! $term || is_wp_error( $term ) ) {
	throw new RuntimeException( 'Pizza Boxes product category is missing.' );
}

$parent = get_term( (int) $term->parent, 'product_cat' );
if ( ! $parent || is_wp_error( $parent ) || 'food-paper-boxes' !== $parent->slug ) {
	$failures[] = 'Pizza Boxes must be a child of Food Paper Boxes.';
}
if ( function_exists( 'custom_box_pizza_boxes_category_is_complete' ) ) {
	$category_failures = array();
	if ( ! custom_box_pizza_boxes_category_is_complete( $term, $category_failures ) ) {
		$failures = array_merge( $failures, $category_failures );
	}
}
if ( empty( $category_data['product_slugs'] ) || ! is_array( $category_data['product_slugs'] ) ) {
	$failures[] = 'Pizza Boxes product assignment data is unavailable.';
} else {
	foreach ( $category_data['product_slugs'] as $category_slug ) {
		$category_product = get_page_by_path( $category_slug, OBJECT, 'product' );
		// Only the five products bundled in this release are required. Older
		// local catalogue entries may not exist or be public on the hosting DB.
		if ( ! in_array( $category_slug, $slugs, true ) && ( ! $category_product || 'publish' !== $category_product->post_status ) ) {
			continue;
		}
		if ( ! $category_product ) {
			$failures[] = $category_slug . ': expected category product is missing.';
			continue;
		}
		if ( 'publish' !== $category_product->post_status ) {
			$failures[] = $category_slug . ': category product is not published.';
		}
		if ( ! has_term( (int) $term->term_id, 'product_cat', (int) $category_product->ID ) ) {
			$failures[] = $category_slug . ': expected Pizza Boxes assignment is missing.';
		}
	}
}

foreach ( $slugs as $slug ) {
	$product = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $product ) {
		$failures[] = $slug . ': product is missing.';
		continue;
	}

	$product_id = (int) $product->ID;
	$content    = (string) $product->post_content;
	$excerpt    = (string) $product->post_excerpt;
	$words      = preg_match_all( "/[\\p{L}\\p{N}][\\p{L}\\p{N}'-]*/u", wp_strip_all_tags( $content ) );
	$short      = preg_match_all( "/[\\p{L}\\p{N}][\\p{L}\\p{N}'-]*/u", wp_strip_all_tags( $excerpt ) );
	$gallery    = array_values( array_unique( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product_id, '_product_image_gallery', true ) ) ) ) ) );
	$featured   = (int) get_post_thumbnail_id( $product_id );
	$image_ids  = array_values( array_unique( array_merge( $featured ? array( $featured ) : array(), $gallery ) ) );
	$specs      = get_post_meta( $product_id, '_custom_box_product_specs', true );
	$robots     = get_post_meta( $product_id, 'rank_math_robots', true );

	if ( 'publish' !== $product->post_status ) {
		$failures[] = $slug . ': product is not published.';
	}
	if ( $marker !== (string) get_post_meta( $product_id, '_vpn_sample_import', true ) ) {
		$failures[] = $slug . ': import marker mismatch.';
	}
	if ( ! has_term( (int) $term->term_id, 'product_cat', $product_id ) ) {
		$failures[] = $slug . ': Pizza Boxes category assignment is missing.';
	}
	if ( false === $words || $words < 1500 || $words > 2000 || preg_match( '/<h1\\b/i', $content ) ) {
		$failures[] = $slug . ': long description length or heading structure is invalid.';
	}
	if ( false === $short || $short < 120 || $short > 180 ) {
		$failures[] = $slug . ': short description must contain 120 to 180 words.';
	}
	if ( $expected_figures[ $slug ] !== substr_count( $content, '<!-- stable-product-image:slot_' ) ) {
		$failures[] = $slug . ': inline product figure count does not match its approved layout.';
	}
	if ( 6 !== count( $image_ids ) || 5 !== count( $gallery ) ) {
		$failures[] = $slug . ': expected one featured image and five gallery images.';
	}
	foreach ( $image_ids as $image_id ) {
		$file = get_attached_file( $image_id );
		if ( 'attachment' !== get_post_type( $image_id ) || ! $file || ! file_exists( $file ) || ! wp_attachment_is_image( $image_id ) ) {
			$failures[] = $slug . ': missing or invalid product image attachment ' . $image_id . '.';
		}
	}
	if ( ! is_array( $specs ) || 21 !== count( $specs ) || '1000 boxes' !== (string) ( $specs[18]['value'] ?? '' ) ) {
		$failures[] = $slug . ': expected 21 product specifications and the listed 1,000-box MOQ.';
	}
	if ( array( 'index', 'follow' ) !== $robots ) {
		$failures[] = $slug . ': Rank Math robots must be index, follow.';
	}
	if ( (string) get_post_meta( $product_id, 'rank_math_canonical_url', true ) !== get_permalink( $product_id ) ) {
		$failures[] = $slug . ': canonical URL does not match the product permalink.';
	}

	$results[] = array(
		'id'            => $product_id,
		'slug'          => $slug,
		'status'        => get_post_status( $product_id ),
		'word_count'    => $words,
		'image_count'   => count( $image_ids ),
		'pizza_category' => has_term( (int) $term->term_id, 'product_cat', $product_id ),
	);
}

if ( $failures ) {
	throw new RuntimeException( 'Pizza Boxes verification failed: ' . implode( ' ', $failures ) );
}

// Refresh catalogue counts and the two public pages after a successful repair.
wp_update_term_count_now( array( (int) $term->term_id ), 'product_cat' );
clean_term_cache( (int) $term->term_id, 'product_cat' );
$verified_term = get_term( (int) $term->term_id, 'product_cat' );
foreach ( array( custom_box_pizza_boxes_category_url(), home_url( '/custom-pizza-boxes-manufacturer/' ) ) as $pizza_url ) {
	do_action( 'litespeed_purge_url', $pizza_url );
}
echo 'Verified 5 published Pizza Boxes products; refreshed category counts and requested page-cache purge.' . PHP_EOL;

echo wp_json_encode(
	array(
		'category'  => $term->name,
		'url'       => get_term_link( $term ),
		'category_products' => (int) $verified_term->count,
		'deploy_products'  => count( $results ),
		'verified'  => $results,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
