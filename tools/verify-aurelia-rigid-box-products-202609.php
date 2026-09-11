<?php
/**
 * Completion verifier for the September 2026 Aurelia rigid-box product batch.
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$marker = 'product-samples-aurelia-rigid-boxes-202609';
$expected = array(
	'custom-burgundy-magnetic-perfume-gift-box-velvet-insert' => array(
		'title' => 'Custom Burgundy Magnetic Perfume Gift Box with Velvet Insert',
		'images' => array(
			'custom-burgundy-magnetic-perfume-gift-box-closed',
			'custom-burgundy-magnetic-perfume-gift-box-open-velvet-insert',
			'custom-burgundy-magnetic-perfume-gift-box-top-view',
			'custom-burgundy-magnetic-perfume-gift-box-front-view',
			'custom-burgundy-magnetic-perfume-gift-box-gold-foil-detail',
			'custom-burgundy-magnetic-perfume-gift-box-boutique-display',
		),
		'categories' => array( 'beauty-skincare-packaging', 'gift-paper-boxes', 'magnetic-closure-boxes', 'perfume-packaging-boxes', 'rigid-boxes' ),
		'primary_category' => 'perfume-packaging-boxes',
		'tags' => array( 'custom-perfume-packaging', 'gold-foil-packaging', 'luxury-fragrance-packaging', 'magnetic-rigid-boxes', 'velvet-insert-boxes' ),
		'focus' => 'custom magnetic perfume gift box',
		'seo_title' => 'Custom Magnetic Perfume Gift Box | Vietnam Manufacturer',
		'seo_description' => 'Custom burgundy magnetic perfume gift boxes made in Vietnam with velvet inserts, rigid board, foil logos and OEM sizing for fragrance sets.',
	),
	'custom-emerald-lid-base-rigid-gift-box' => array(
		'title' => 'Custom Emerald Lid and Base Rigid Gift Box',
		'images' => array(
			'custom-emerald-lid-base-rigid-gift-box-closed',
			'custom-emerald-lid-base-rigid-gift-box-open-velvet-insert',
			'custom-emerald-lid-base-rigid-gift-box-top-view-gold-foil',
			'custom-emerald-lid-base-rigid-gift-box-side-profile',
			'custom-emerald-lid-base-rigid-gift-box-embossed-foil-detail',
			'custom-emerald-lid-base-rigid-gift-box-luxury-display',
		),
		'categories' => array( 'corporate-gift-packaging', 'custom-printed-paper-boxes', 'gift-paper-boxes', 'lid-and-base-boxes', 'rigid-boxes' ),
		'primary_category' => 'lid-and-base-boxes',
		'tags' => array( 'embossed-packaging', 'foil-stamped-boxes', 'luxury-rigid-gift-boxes', 'two-piece-gift-boxes', 'velvet-insert-boxes' ),
		'focus' => 'custom lid and base rigid gift box',
		'seo_title' => 'Custom Lid and Base Rigid Gift Box | Vietnam Factory',
		'seo_description' => 'Custom emerald lid and base rigid gift boxes from Vietnam with fitted inserts, gold foil, embossed details and OEM sizes for luxury products.',
	),
	'custom-navy-shoulder-neck-jewelry-box-velvet-insert' => array(
		'title' => 'Custom Navy Shoulder Neck Jewelry Box with Velvet Insert',
		'images' => array(
			'custom-navy-shoulder-neck-jewelry-box-closed',
			'custom-navy-shoulder-neck-jewelry-box-gold-foil-perspective',
			'custom-navy-shoulder-neck-jewelry-box-open-velvet-insert',
			'custom-navy-shoulder-neck-jewelry-box-top-view',
			'custom-navy-shoulder-neck-jewelry-box-front-gold-neck',
			'custom-navy-shoulder-neck-jewelry-box-gold-foil-detail',
			'custom-navy-shoulder-neck-jewelry-box-stacked-display',
		),
		'categories' => array( 'custom-printed-paper-boxes', 'gift-paper-boxes', 'jewelry-paper-boxes', 'lid-and-base-boxes', 'rigid-boxes' ),
		'primary_category' => 'jewelry-paper-boxes',
		'tags' => array( 'custom-jewelry-boxes', 'gold-foil-boxes', 'rigid-gift-boxes', 'shoulder-neck-boxes', 'velvet-jewelry-inserts' ),
		'focus' => 'custom shoulder neck jewelry box',
		'seo_title' => 'Custom Shoulder Neck Jewelry Box | Vietnam Manufacturer',
		'seo_description' => 'Custom navy shoulder neck jewelry boxes made in Vietnam with velvet inserts, gold neck reveals, foil logos and OEM sizing for luxury brands.',
	),
);

function vpn_aurelia_verify_words( $html ) {
	$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$parts = preg_split( '/\s+/u', trim( $text ) );
	return $parts && '' !== $parts[0] ? count( $parts ) : 0;
}

function vpn_aurelia_verify_attachment_ids( $base ) {
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

foreach ( $expected as $slug => $spec ) {
	$products = get_posts( array( 'name' => $slug, 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1 ) );
	if ( 1 !== count( $products ) ) {
		$failures[] = $slug . ': expected exactly one product, found ' . count( $products );
		continue;
	}
	$product = $products[0];
	$id = (int) $product->ID;
	$content = (string) $product->post_content;
	$excerpt_words = vpn_aurelia_verify_words( $product->post_excerpt );
	$content_words = vpn_aurelia_verify_words( $content );
	$gallery = array_values( array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $id, '_product_image_gallery', true ) ) ) ) );
	$thumbnail = (int) get_post_thumbnail_id( $id );
	$expected_attachment_ids = array();

	if ( 'publish' !== get_post_status( $id ) ) {
		$failures[] = $slug . ': product is not published';
	}
	if ( $spec['title'] !== $product->post_title ) {
		$failures[] = $slug . ': title mismatch';
	}
	if ( $marker !== get_post_meta( $id, '_vpn_sample_import', true ) ) {
		$failures[] = $slug . ': batch marker mismatch';
	}
	if ( $content_words < 1500 || $content_words > 2000 ) {
		$failures[] = $slug . ': content word count is ' . $content_words;
	}
	if ( $excerpt_words < 120 || $excerpt_words > 180 ) {
		$failures[] = $slug . ': excerpt word count is ' . $excerpt_words;
	}
	if ( preg_match( '/<h1\b/i', $content ) ) {
		$failures[] = $slug . ': long content contains H1';
	}
	if ( substr_count( $content, '<h2' ) < 8 ) {
		$failures[] = $slug . ': fewer than eight H2 sections';
	}
	if ( 4 !== substr_count( $content, '<!-- stable-product-image:slot_' ) || 4 !== substr_count( $content, '<figure' ) || 4 !== substr_count( $content, '<img' ) ) {
		$failures[] = $slug . ': inline image markers/figures/images are incomplete';
	}
	if ( substr_count( $content, '<a href=' ) < 4 ) {
		$failures[] = $slug . ': fewer than four internal links';
	}
	if ( false !== strpos( $content, 'href="/' ) ) {
		$failures[] = $slug . ': environment-relative internal link was not normalized';
	}

	foreach ( $spec['images'] as $base ) {
		$ids = vpn_aurelia_verify_attachment_ids( $base );
		if ( 1 !== count( $ids ) ) {
			$failures[] = $slug . ': expected one exact attachment for ' . $base . ', found ' . count( $ids );
			continue;
		}
		$attachment_id = $ids[0];
		$expected_attachment_ids[] = $attachment_id;
		$relative = (string) get_post_meta( $attachment_id, '_wp_attached_file', true );
		$file = trailingslashit( wp_upload_dir()['basedir'] ) . $relative;
		if ( ! file_exists( $file ) || filesize( $file ) >= 100000 ) {
			$failures[] = $slug . ': missing or oversized uploads image ' . $relative;
		}
		if ( '' === trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ) {
			$failures[] = $slug . ': missing alt text for ' . $base;
		}
		$attachment = get_post( $attachment_id );
		if ( ! $attachment || $id !== (int) $attachment->post_parent || '' === trim( $attachment->post_title ) || '' === trim( $attachment->post_excerpt ) ) {
			$failures[] = $slug . ': attachment metadata/parent incomplete for ' . $base;
		}
	}

	if ( ! $thumbnail || $thumbnail !== ( $expected_attachment_ids[0] ?? 0 ) ) {
		$failures[] = $slug . ': featured image mismatch';
	}
	if ( $gallery !== array_slice( $expected_attachment_ids, 1 ) ) {
		$failures[] = $slug . ': gallery IDs/order mismatch';
	}

	$category_slugs = wp_get_object_terms( $id, 'product_cat', array( 'fields' => 'slugs' ) );
	$tag_slugs = wp_get_object_terms( $id, 'product_tag', array( 'fields' => 'slugs' ) );
	sort( $category_slugs );
	sort( $tag_slugs );
	$wanted_categories = $spec['categories'];
	$wanted_tags = $spec['tags'];
	sort( $wanted_categories );
	sort( $wanted_tags );
	if ( $wanted_categories !== $category_slugs ) {
		$failures[] = $slug . ': category set mismatch';
	}
	$primary_category = get_term_by( 'slug', $spec['primary_category'], 'product_cat' );
	if ( ! $primary_category || (int) $primary_category->term_id !== (int) get_post_meta( $id, 'rank_math_primary_product_cat', true ) ) {
		$failures[] = $slug . ': primary category mismatch';
	}
	if ( $wanted_tags !== $tag_slugs ) {
		$failures[] = $slug . ': tag set mismatch';
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
	if ( ! is_array( $spec_rows ) || 21 !== count( $spec_rows ) || '1000 boxes' !== $moq ) {
		$failures[] = $slug . ': specification rows or MOQ mismatch';
	}
	if ( $spec['focus'] !== get_post_meta( $id, 'rank_math_focus_keyword', true ) || $spec['seo_title'] !== get_post_meta( $id, 'rank_math_title', true ) || $spec['seo_description'] !== get_post_meta( $id, 'rank_math_description', true ) ) {
		$failures[] = $slug . ': Rank Math fields mismatch';
	}
	$faq = (string) get_post_meta( $id, '_custom_box_product_faq_html', true );
	if ( false === strpos( $faq, 'https://schema.org/FAQPage' ) || 6 !== substr_count( $faq, 'itemprop="mainEntity"' ) || 6 !== substr_count( $faq, 'itemprop="acceptedAnswer"' ) ) {
		$failures[] = $slug . ': FAQ markup incomplete';
	}

	$summary[] = array( 'id' => $id, 'slug' => $slug, 'status' => get_post_status( $id ), 'words' => $content_words, 'excerpt_words' => $excerpt_words, 'images' => count( $expected_attachment_ids ), 'figures' => substr_count( $content, '<figure' ), 'categories' => count( $category_slugs ), 'tags' => count( $tag_slugs ) );
}

$marked = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => '_vpn_sample_import', 'meta_value' => $marker, 'fields' => 'ids' ) );
if ( 3 !== count( $marked ) ) {
	$failures[] = 'Batch marker expected 3 products, found ' . count( $marked );
}

echo wp_json_encode( array( 'complete' => empty( $failures ), 'products' => $summary, 'failures' => $failures ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
if ( $failures ) {
	exit( 1 );
}
