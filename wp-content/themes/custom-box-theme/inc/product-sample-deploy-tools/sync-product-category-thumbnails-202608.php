<?php
/**
 * Sync the three requested WooCommerce product category thumbnails.
 *
 * Usage:
 *   php tools/sync-product-category-thumbnails-202608.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

if ( ! taxonomy_exists( 'product_cat' ) ) {
	throw new RuntimeException( 'WooCommerce product_cat taxonomy is not available.' );
}

function custom_box_category_thumbnail_attachment_from_upload_202608( string $relative_file, string $title ): int {
	$relative_file = ltrim( wp_normalize_path( $relative_file ), '/' );
	if ( ! preg_match( '#^[0-9]{4}/[0-9]{2}/[^/]+\.webp$#i', $relative_file ) ) {
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_attached_file',
			'meta_value'     => $relative_file,
		)
	);

	if ( ! empty( $existing[0] ) ) {
		return (int) $existing[0];
	}

	$uploads  = wp_upload_dir();
	$absolute = trailingslashit( $uploads['basedir'] ) . $relative_file;
	if ( ! empty( $uploads['error'] ) || ! is_readable( $absolute ) ) {
		return 0;
	}

	$filetype      = wp_check_filetype( wp_basename( $absolute ) );
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => trailingslashit( $uploads['baseurl'] ) . $relative_file,
			'post_mime_type' => $filetype['type'] ?: 'image/webp',
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$absolute
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	$attachment_id = (int) $attachment_id;
	update_attached_file( $attachment_id, $absolute );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $absolute ) );

	return $attachment_id;
}

$assignments = array(
	array(
		'category_slug' => 'pharmaceutical-packaging-boxes',
		'category_name' => 'Pharmaceutical Packaging Boxes',
		'upload_file'   => '2026/08/custom-blister-pack-medicine-box-1.webp',
	),
	array(
		'category_slug' => 'beauty-skincare-packaging',
		'category_name' => 'Beauty and Skincare Packaging',
		'upload_file'   => '2026/08/beauty-skincare-packaging-category.webp',
	),
	array(
		'category_slug' => 'bird-nest-packaging-boxes',
		'category_name' => 'Bird Nest Packaging Boxes',
		'upload_file'   => '2026/06/blue-bird-nest-gift-packaging-box-with-gold-pattern-front-view.webp',
	),
	array(
		'category_slug' => 'wine-premium-drink-packaging',
		'category_name' => 'Wine and Premium Drink Packaging',
		'product_slug'  => 'custom-wine-bottle-packaging-box',
	),
	array(
		'category_slug' => 'fashion-sportswear-packaging',
		'category_name' => 'Fashion and Sportswear Packaging',
		'product_slug'  => 'custom-men-underwear-packaging-box',
	),
);

foreach ( $assignments as $assignment ) {
	$term = get_term_by( 'slug', $assignment['category_slug'], 'product_cat' );
	if ( ! $term || is_wp_error( $term ) ) {
		throw new RuntimeException( 'Missing product category: ' . $assignment['category_slug'] );
	}

	if ( ! empty( $assignment['upload_file'] ) ) {
		$attachment_id = custom_box_category_thumbnail_attachment_from_upload_202608(
			$assignment['upload_file'],
			$assignment['category_name']
		);
	} else {
		$product       = get_page_by_path( $assignment['product_slug'], OBJECT, 'product' );
		$attachment_id = $product ? (int) get_post_thumbnail_id( $product->ID ) : 0;
	}

	if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
		throw new RuntimeException( 'Missing requested thumbnail attachment for: ' . $assignment['category_name'] );
	}

	update_term_meta( (int) $term->term_id, 'thumbnail_id', $attachment_id );
	update_term_meta( (int) $term->term_id, 'custom_box_category_image_id', $attachment_id );
	clean_term_cache( (int) $term->term_id, 'product_cat' );

	$thumbnail_id = (int) get_term_meta( (int) $term->term_id, 'thumbnail_id', true );
	$custom_id    = (int) get_term_meta( (int) $term->term_id, 'custom_box_category_image_id', true );
	if ( $thumbnail_id !== $attachment_id || $custom_id !== $attachment_id ) {
		throw new RuntimeException( 'Category thumbnail verification failed: ' . $assignment['category_name'] );
	}

	echo $assignment['category_name'] . ': thumbnail attachment_id=' . $attachment_id . PHP_EOL;
}

echo 'Verified 5 product category thumbnails successfully.' . PHP_EOL;
