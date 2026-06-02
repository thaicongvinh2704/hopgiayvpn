<?php
/**
 * Import the first 10 reviewed product samples into local WooCommerce as drafts.
 *
 * Usage:
 *   php tools/import-product-samples-10.php
 */

require_once dirname( __DIR__ ) . '/wp-load.php';

if ( ! post_type_exists( 'product' ) ) {
	fwrite( STDERR, "WooCommerce product post type is not available.\n" );
	exit( 1 );
}

require_once ABSPATH . 'wp-admin/includes/image.php';

$sample_file = dirname( __DIR__ ) . '/product-samples-10.md';

if ( ! file_exists( $sample_file ) ) {
	fwrite( STDERR, "Missing product-samples-10.md.\n" );
	exit( 1 );
}

$markdown = file_get_contents( $sample_file );
$sections = preg_split( '/^##\s+/m', $markdown );
array_shift( $sections );

function vpn_sample_value( string $section, string $label ): string {
	if ( preg_match( '/^-\s+' . preg_quote( $label, '/' ) . ':\s*(.+)$/mi', $section, $matches ) ) {
		return trim( $matches[1] );
	}

	return '';
}

function vpn_sample_block( string $section, string $start, ?string $end = null ): string {
	$pattern = $end
		? '/' . preg_quote( $start, '/' ) . ":\s*\R\R(.+?)\R\R" . preg_quote( $end, '/' ) . ':/s'
		: '/' . preg_quote( $start, '/' ) . ":\s*\R\R(.+)$/s";

	if ( preg_match( $pattern, $section, $matches ) ) {
		return trim( $matches[1] );
	}

	return '';
}

function vpn_sample_attachment_id( string $relative_path ): int {
	$relative_path = str_replace( '\\', '/', trim( $relative_path ) );
	$uploads      = wp_get_upload_dir();
	$base_dir     = str_replace( '\\', '/', $uploads['basedir'] );
	$file_path    = ABSPATH . $relative_path;

	if ( ! file_exists( $file_path ) ) {
		return 0;
	}

	$attached_file = ltrim( str_replace( $base_dir, '', str_replace( '\\', '/', $file_path ) ), '/' );
	$existing      = get_posts(
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

	$filetype      = wp_check_filetype( basename( $file_path ), null );
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => trailingslashit( $uploads['baseurl'] ) . $attached_file,
			'post_mime_type' => $filetype['type'] ?: 'image/webp',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$file_path
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attached_file', $attached_file );
	$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );

	if ( $metadata ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	return (int) $attachment_id;
}

function vpn_sample_term_id( string $category_name ): int {
	$term = term_exists( $category_name, 'product_cat' );

	if ( ! $term ) {
		$term = wp_insert_term( $category_name, 'product_cat' );
	}

	if ( is_wp_error( $term ) ) {
		return 0;
	}

	return (int) ( is_array( $term ) ? $term['term_id'] : $term );
}

$imported = 0;

foreach ( $sections as $section ) {
	$lines      = preg_split( '/\R/', trim( $section ) );
	$heading    = trim( $lines[0] ?? '' );
	$title      = vpn_sample_value( $section, 'Product name' ) ?: preg_replace( '/^\d+\.\s*/', '', $heading );
	$slug       = sanitize_title( vpn_sample_value( $section, 'Slug' ) ?: $title );
	$category   = vpn_sample_value( $section, 'Category' );
	$tags       = array_filter( array_map( 'trim', explode( ',', vpn_sample_value( $section, 'Tags' ) ) ) );
	$featured   = vpn_sample_value( $section, 'Featured image' );
	$gallery    = array_filter( array_map( 'trim', explode( ',', vpn_sample_value( $section, 'Gallery images' ) ) ) );
	$keyword    = vpn_sample_value( $section, 'Focus keyword' );
	$seo_title  = vpn_sample_value( $section, 'SEO title' );
	$seo_desc   = vpn_sample_value( $section, 'Meta description' );
	$alt_text   = vpn_sample_value( $section, 'Image alt text' );
	$custom     = vpn_sample_value( $section, 'Custom fields' );
	$short_desc = vpn_sample_block( $section, 'Short description', 'Long description' );
	$long_desc  = vpn_sample_block( $section, 'Long description' );

	if ( ! $title || ! $slug ) {
		continue;
	}

	$existing = get_page_by_path( $slug, OBJECT, 'product' );
	$postarr  = array(
		'post_type'    => 'product',
		'post_status'  => 'draft',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_excerpt' => $short_desc,
		'post_content' => $long_desc,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$product_id    = wp_update_post( $postarr, true );
	} else {
		$product_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $product_id ) || ! $product_id ) {
		fwrite( STDERR, "Failed: {$title}\n" );
		continue;
	}

	wp_set_object_terms( $product_id, 'simple', 'product_type' );

	if ( $category ) {
		$term_id = vpn_sample_term_id( $category );
		if ( $term_id ) {
			wp_set_object_terms( $product_id, array( $term_id ), 'product_cat' );
		}
	}

	if ( $tags ) {
		wp_set_object_terms( $product_id, $tags, 'product_tag' );
	}

	$thumbnail_id = vpn_sample_attachment_id( $featured );
	if ( $thumbnail_id ) {
		set_post_thumbnail( $product_id, $thumbnail_id );
		update_post_meta( $thumbnail_id, '_wp_attachment_image_alt', $alt_text );
	}

	$gallery_ids = array();
	foreach ( $gallery as $image_path ) {
		$image_id = vpn_sample_attachment_id( $image_path );
		if ( $image_id && $image_id !== $thumbnail_id ) {
			$gallery_ids[] = $image_id;
			update_post_meta( $image_id, '_wp_attachment_image_alt', $alt_text );
		}
	}

	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_unique( $gallery_ids ) ) );
	update_post_meta( $product_id, '_sku', 'sample-' . $slug );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_virtual', 'no' );
	update_post_meta( $product_id, '_downloadable', 'no' );
	update_post_meta( $product_id, '_vpn_sample_import', 'product-samples-10' );
	update_post_meta( $product_id, '_vpn_custom_packaging_fields', $custom );
	update_post_meta( $product_id, 'rank_math_title', $seo_title );
	update_post_meta( $product_id, 'rank_math_description', $seo_desc );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $keyword );

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $product_id );
	}

	$imported++;
	echo "Draft product ready: {$title} (#{$product_id})\n";
}

echo "Imported/updated {$imported} draft products.\n";
