<?php
/**
 * Repair featured images for Batch 4 remaining WooCommerce product samples.
 *
 * Usage:
 *   php tools/repair-product-samples-batch-4-featured-images.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_b4_repair_attachment_id( string $relative_path ): int {
	$file_path = ABSPATH . ltrim( $relative_path, '/\\' );

	if ( ! file_exists( $file_path ) ) {
		echo 'Missing image file: ' . $relative_path . PHP_EOL;
		return 0;
	}

	$uploads       = wp_get_upload_dir();
	$base_dir      = str_replace( '\\', '/', $uploads['basedir'] );
	$base_url      = str_replace( '\\', '/', $uploads['baseurl'] );
	$normalized    = str_replace( '\\', '/', $file_path );
	$attached_file = ltrim( str_replace( $base_dir, '', $normalized ), '/' );
	$file_url      = trailingslashit( $base_url ) . $attached_file;
	$basename      = basename( $relative_path );

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_attached_file',
			'meta_value'     => $attached_file,
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			's'              => preg_replace( '/\.[^.]+$/', '', $basename ),
		)
	);

	if ( $existing ) {
		update_post_meta( (int) $existing[0], '_wp_attached_file', $attached_file );
		return (int) $existing[0];
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filetype      = wp_check_filetype( $file_path );
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => $file_url,
			'post_mime_type' => $filetype['type'] ?? 'image/webp',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $basename ),
			'post_status'    => 'inherit',
		),
		$file_path
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		echo 'Could not create attachment: ' . $relative_path . PHP_EOL;
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attached_file', $attached_file );
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file_path ) );

	return (int) $attachment_id;
}

$featured_images = array(
	'custom-cosmetic-skincare-packaging-boxes'       => 'wp-content/uploads/2026/05/custom-cosmetic-skincare-packaging-boxes.webp',
	'custom-home-lifestyle-product-packaging-boxes' => 'wp-content/uploads/2026/05/custom-home-lifestyle-product-packaging-boxes.webp',
	'custom-pharmaceutical-medicine-packaging-boxes' => 'wp-content/uploads/2026/05/custom-pharmaceutical-medicine-packaging-boxes.webp',
	'custom-phone-accessories-packaging-boxes'       => 'wp-content/uploads/2026/05/custom-phone-accessories-packaging-boxes.webp',
	'custom-phone-packaging-box-with-paper-bag'      => 'wp-content/uploads/2026/05/custom-phone-packaging-box-with-paper-bag-1.webp',
	'custom-red-paper-shopping-bag'                  => 'wp-content/uploads/2026/05/custom-red-paper-shopping-bag.jpeg',
	'custom-skincare-gift-box-with-insert'           => 'wp-content/uploads/2026/05/custom-skincare-gift-box-with-insert-1.webp',
	'custom-stationery-packaging-box'                => 'wp-content/uploads/2026/05/custom-stationery-packaging-box-1.webp',
	'custom-stationery-school-supplies-packaging-boxes' => 'wp-content/uploads/2026/05/custom-stationery-school-supplies-packaging-boxes.webp',
	'custom-supplement-vitamin-packaging-boxes'      => 'wp-content/uploads/2026/05/custom-supplement-vitamin-packaging-boxes.webp',
	'custom-tablet-packaging-box'                    => 'wp-content/uploads/2026/05/custom-tablet-packaging-box-1.webp',
	'custom-teal-rigid-gift-box'                     => 'wp-content/uploads/2026/05/custom-teal-rigid-gift-box.png',
	'custom-thermos-bottle-packaging-box'            => 'wp-content/uploads/2026/05/custom-thermos-bottle-packaging-box-1.webp',
	'custom-vial-packaging-box'                      => 'wp-content/uploads/2026/05/custom-vial-packaging-box-1.webp',
	'custom-wine-bottle-gift-box-with-paper-bag'     => 'wp-content/uploads/2026/05/custom-wine-bottle-gift-box-with-paper-bag-1.webp',
	'custom-wine-bottle-packaging-box'               => 'wp-content/uploads/2026/05/custom-wine-bottle-packaging-box-1.webp',
	'custom-wine-premium-beverage-packaging-boxes'   => 'wp-content/uploads/2026/05/custom-wine-premium-beverage-packaging-boxes.webp',
);

$fixed = 0;

foreach ( $featured_images as $slug => $image ) {
	$product = get_page_by_path( $slug, OBJECT, 'product' );

	if ( ! $product ) {
		echo 'Missing product: ' . $slug . PHP_EOL;
		continue;
	}

	$attachment_id = vpn_b4_repair_attachment_id( $image );
	if ( ! $attachment_id ) {
		echo 'Missing attachment for product: ' . $slug . PHP_EOL;
		continue;
	}

	set_post_thumbnail( $product->ID, $attachment_id );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', get_the_title( $product ) );
	++$fixed;

	echo 'Featured image repaired: ' . get_the_title( $product ) . PHP_EOL;
}

echo 'Batch 4 featured image repair complete. Products checked: ' . count( $featured_images ) . ', repaired: ' . $fixed . PHP_EOL;
