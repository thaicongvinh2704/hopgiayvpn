<?php
/**
 * Completion verifier for the September 2026 rigid paperboard magazine file holder product.
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$slug = 'custom-rigid-paperboard-magazine-file-holder-set';
$marker = 'product-samples-magazine-file-holder-202609';
$expected_images = array(
	'01-rigid-paperboard-magazine-file-holder-set-hero',
	'02-rigid-magazine-file-holders-in-use',
	'03-teal-rigid-magazine-file-holder-interior',
	'04-rigid-magazine-file-holder-label-finger-pull-closeup',
	'05-rigid-magazine-file-holder-set-rear-side-view',
	'06-rigid-magazine-file-holder-feature-callouts',
);
$expected_categories = array( 'back-to-school-stationery-packaging', 'home-lifestyle-packaging', 'rigid-boxes' );
$expected_tags = array( 'custom-office-accessories', 'magazine-file-holder', 'paperboard-desk-organizer', 'rigid-paperboard-products', 'stationery-storage' );
$focus = 'rigid paperboard magazine file holder';
$seo_title = 'Rigid Paperboard Magazine File Holder | Vietnam Factory';
$seo_description = 'Custom rigid paperboard magazine file holders made in Vietnam with wrapped board, label panels, finger pulls, custom sizes, colors and printing.';

function vpn_magazine_file_holder_verify_words( $html ) {
	$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$parts = preg_split( '/\s+/u', trim( $text ) );
	return $parts && '' !== $parts[0] ? count( $parts ) : 0;
}

function vpn_magazine_file_holder_verify_attachment_ids( $base ) {
	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s", '%' . $wpdb->esc_like( $base ) . '%' ) );
	$exact = array();
	foreach ( $ids as $id ) {
		$file = (string) get_post_meta( (int) $id, '_wp_attached_file', true );
		if ( $base === pathinfo( wp_basename( $file ), PATHINFO_FILENAME ) ) {
			$exact[] = (int) $id;
		}
	}
	return array_values( array_unique( $exact ) );
}

$failures = array();
$summary = array();
$products = get_posts( array( 'name' => $slug, 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1 ) );

if ( 1 !== count( $products ) ) {
	$failures[] = 'Expected exactly one product, found ' . count( $products );
} else {
	$product = $products[0];
	$id = (int) $product->ID;
	$content = (string) $product->post_content;
	$content_words = vpn_magazine_file_holder_verify_words( $content );
	$excerpt_words = vpn_magazine_file_holder_verify_words( $product->post_excerpt );
	$gallery = array_values( array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $id, '_product_image_gallery', true ) ) ) ) );
	$expected_attachment_ids = array();

	if ( 'publish' !== get_post_status( $id ) ) {
		$failures[] = 'Product is not published';
	}
	if ( 'Custom Rigid Paperboard Magazine File Holder Set' !== $product->post_title ) {
		$failures[] = 'Product title mismatch';
	}
	if ( $marker !== get_post_meta( $id, '_vpn_sample_import', true ) ) {
		$failures[] = 'Batch marker mismatch';
	}
	if ( $content_words < 1500 || $content_words > 2000 ) {
		$failures[] = 'Content word count is ' . $content_words . '; expected 1500-2000';
	}
	if ( $excerpt_words < 120 || $excerpt_words > 180 ) {
		$failures[] = 'Excerpt word count is ' . $excerpt_words . '; expected 120-180';
	}
	if ( preg_match( '/<h1\b/i', $content ) ) {
		$failures[] = 'Long content contains H1';
	}
	if ( substr_count( $content, '<h2' ) < 9 ) {
		$failures[] = 'Long content has fewer than nine H2 sections';
	}
	if ( 4 !== substr_count( $content, '<!-- stable-product-image:slot_' ) || 4 !== substr_count( $content, '<figure' ) || 4 !== substr_count( $content, '<img' ) ) {
		$failures[] = 'Inline image markers, figures or images are incomplete';
	}
	if ( substr_count( $content, '<a href=' ) < 5 || false !== strpos( $content, 'href="/' ) ) {
		$failures[] = 'Internal links are incomplete or were not normalized';
	}
	if ( false === stripos( $content, '<strong>A custom rigid paperboard magazine file holder' ) || false === stripos( $content, 'Ho Chi Minh City, Vietnam' ) ) {
		$failures[] = 'Answer-first or Vietnam manufacturing content is missing';
	}

	foreach ( $expected_images as $base ) {
		$ids = vpn_magazine_file_holder_verify_attachment_ids( $base );
		if ( 1 !== count( $ids ) ) {
			$failures[] = 'Expected one exact attachment for ' . $base . ', found ' . count( $ids );
			continue;
		}
		$attachment_id = $ids[0];
		$expected_attachment_ids[] = $attachment_id;
		$relative = (string) get_post_meta( $attachment_id, '_wp_attached_file', true );
		$file = trailingslashit( wp_upload_dir()['basedir'] ) . $relative;
		$metadata = wp_get_attachment_metadata( $attachment_id );
		if ( ! file_exists( $file ) || filesize( $file ) >= 100000 ) {
			$failures[] = 'Missing or oversized uploads image ' . $relative;
		}
		if ( 450 !== (int) ( $metadata['width'] ?? 0 ) || 570 !== (int) ( $metadata['height'] ?? 0 ) ) {
			$failures[] = 'Unexpected image dimensions for ' . $base;
		}
		if ( '' === trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ) {
			$failures[] = 'Missing alt text for ' . $base;
		}
		$attachment = get_post( $attachment_id );
		if ( ! $attachment || $id !== (int) $attachment->post_parent || '' === trim( $attachment->post_title ) || '' === trim( $attachment->post_excerpt ) ) {
			$failures[] = 'Attachment metadata or parent incomplete for ' . $base;
		}
	}

	if ( (int) get_post_thumbnail_id( $id ) !== ( $expected_attachment_ids[0] ?? 0 ) ) {
		$failures[] = 'Featured image mismatch';
	}
	if ( $gallery !== array_slice( $expected_attachment_ids, 1 ) ) {
		$failures[] = 'Gallery IDs or order mismatch';
	}

	$category_slugs = wp_get_object_terms( $id, 'product_cat', array( 'fields' => 'slugs' ) );
	$tag_slugs = wp_get_object_terms( $id, 'product_tag', array( 'fields' => 'slugs' ) );
	sort( $category_slugs );
	sort( $tag_slugs );
	sort( $expected_categories );
	sort( $expected_tags );
	if ( $expected_categories !== $category_slugs ) {
		$failures[] = 'Product category set mismatch';
	}
	if ( $expected_tags !== $tag_slugs ) {
		$failures[] = 'Product tag set mismatch';
	}
	$primary_category = get_term_by( 'slug', 'back-to-school-stationery-packaging', 'product_cat' );
	if ( ! $primary_category || (int) $primary_category->term_id !== (int) get_post_meta( $id, 'rank_math_primary_product_cat', true ) ) {
		$failures[] = 'Primary category mismatch';
	}

	$spec_rows = get_post_meta( $id, '_custom_box_product_specs', true );
	$moq = '';
	if ( is_array( $spec_rows ) ) {
		foreach ( $spec_rows as $row ) {
			if ( isset( $row['label'], $row['value'] ) && 'Minimum Order Quantity (MOQ)' === $row['label'] ) {
				$moq = $row['value'];
			}
		}
	}
	if ( ! is_array( $spec_rows ) || 21 !== count( $spec_rows ) || '1000 sets' !== $moq ) {
		$failures[] = 'Specification rows or MOQ mismatch';
	}

	if ( $focus !== get_post_meta( $id, 'rank_math_focus_keyword', true ) || $seo_title !== get_post_meta( $id, 'rank_math_title', true ) || $seo_description !== get_post_meta( $id, 'rank_math_description', true ) ) {
		$failures[] = 'Rank Math fields mismatch';
	}
	if ( get_permalink( $id ) !== get_post_meta( $id, 'rank_math_canonical_url', true ) || array( 'index', 'follow' ) !== get_post_meta( $id, 'rank_math_robots', true ) ) {
		$failures[] = 'Canonical URL or robots metadata mismatch';
	}
	foreach ( array( 'facebook', 'twitter' ) as $network ) {
		if ( ! get_post_meta( $id, 'rank_math_' . $network . '_image_id', true ) || ! get_post_meta( $id, 'rank_math_' . $network . '_image', true ) ) {
			$failures[] = ucfirst( $network ) . ' social image metadata is missing';
		}
	}
	$faq = (string) get_post_meta( $id, '_custom_box_product_faq_html', true );
	if ( false === strpos( $faq, 'https://schema.org/FAQPage' ) || 6 !== substr_count( $faq, 'itemprop="mainEntity"' ) || 6 !== substr_count( $faq, 'itemprop="acceptedAnswer"' ) ) {
		$failures[] = 'FAQ markup is incomplete';
	}

	$summary = array(
		'id' => $id,
		'slug' => $slug,
		'status' => get_post_status( $id ),
		'url' => get_permalink( $id ),
		'words' => $content_words,
		'excerpt_words' => $excerpt_words,
		'images' => count( $expected_attachment_ids ),
		'figures' => substr_count( $content, '<figure' ),
		'categories' => $category_slugs,
		'tags' => $tag_slugs,
	);
}

$marked = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => '_vpn_sample_import', 'meta_value' => $marker, 'fields' => 'ids' ) );
if ( 1 !== count( $marked ) ) {
	$failures[] = 'Batch marker expected one product, found ' . count( $marked );
}

echo wp_json_encode( array( 'complete' => empty( $failures ), 'product' => $summary, 'failures' => $failures ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
if ( $failures ) {
	exit( 1 );
}
