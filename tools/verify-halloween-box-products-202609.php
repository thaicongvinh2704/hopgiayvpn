<?php
/**
 * Verify the five Halloween box products after Product Sample Deploy.
 */

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
	return;
}

if ( ! function_exists( 'vpn_halloween_box_202609_product_definitions' ) ) {
	require_once get_template_directory() . '/inc/product-sample-deploy-tools/import-halloween-box-products-202609.php';
}

$definitions = vpn_halloween_box_202609_product_definitions();
$expected    = array();
foreach ( $definitions as $definition ) {
	$expected[ $definition['slug'] ] = $definition;
}

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => '_vpn_sample_import',
		'meta_value'     => VPN_HALLOWEEN_BOX_202609_MARKER,
	)
);
$failures = array();

if ( count( $products ) !== count( $expected ) ) {
	$failures[] = 'Expected ' . count( $expected ) . ' published products, found ' . count( $products ) . '.';
}

foreach ( $products as $product ) {
	$slug       = $product->post_name;
	$definition = $expected[ $slug ] ?? null;
	if ( ! $definition ) {
		$failures[] = $slug . ': unexpected product.';
		continue;
	}

	$product_id = (int) $product->ID;
	$content    = (string) $product->post_content;
	$words      = str_word_count( wp_strip_all_tags( $content ) );
	$short      = str_word_count( wp_strip_all_tags( (string) $product->post_excerpt ) );
	$featured   = (int) get_post_thumbnail_id( $product_id );
	$gallery    = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product_id, '_product_image_gallery', true ) ) ) );
	$image_ids  = array_values( array_unique( array_merge( array( $featured ), $gallery ) ) );
	$categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );
	$specs      = get_post_meta( $product_id, '_custom_box_product_specs', true );
	$faq        = (string) get_post_meta( $product_id, '_custom_box_product_faq_html', true );

	if ( 'publish' !== $product->post_status || $definition['title'] !== $product->post_title || $slug !== $definition['slug'] ) {
		$failures[] = $slug . ': title, slug or status mismatch.';
	}
	if ( $words < 1200 || $words > 2400 ) {
		$failures[] = $slug . ': long content words=' . $words . '.';
	}
	if ( $short < 90 || $short > 220 ) {
		$failures[] = $slug . ': short description words=' . $short . '.';
	}
	if ( preg_match( '/<h1\b/i', $content ) ) {
		$failures[] = $slug . ': product content contains an extra H1.';
	}
	if ( 4 !== substr_count( $content, '<!-- stable-product-image:slot_' ) || 4 !== substr_count( $content, '<figure class="product-inline-figure' ) ) {
		$failures[] = $slug . ': expected four stable inline figures.';
	}
	if ( 6 !== count( $image_ids ) || 5 !== count( $gallery ) ) {
		$failures[] = $slug . ': expected one featured image and five gallery images.';
	}
	foreach ( $definition['categories'] as $category_slug ) {
		if ( is_wp_error( $categories ) || ! in_array( $category_slug, $categories, true ) ) {
			$failures[] = $slug . ': missing category ' . $category_slug . '.';
		}
	}
	if ( ! is_array( $specs ) || 21 !== count( $specs ) || false === strpos( wp_json_encode( $specs ), 'Available on request' ) || false === strpos( wp_json_encode( $specs ), 'Request a quote' ) ) {
		$failures[] = $slug . ': specifications or quote-only pricing mismatch.';
	}
	if ( 6 !== substr_count( $faq, '<details' ) || false === strpos( $faq, 'https://schema.org/FAQPage' ) ) {
		$failures[] = $slug . ': expected six FAQ items and FAQPage markup.';
	}

	foreach ( $image_ids as $index => $image_id ) {
		$file       = get_attached_file( $image_id );
		$dimensions = $file ? @getimagesize( $file ) : false;
		$expected_filename = $definition['images'][ $index ] ?? '';
		$relative   = (string) get_post_meta( $image_id, '_wp_attached_file', true );
		if ( ! $file || ! file_exists( $file ) || ! wp_attachment_is_image( $image_id ) || (int) wp_get_post_parent_id( $image_id ) !== $product_id ) {
			$failures[] = $slug . ': invalid attachment at slot ' . ( $index + 1 ) . '.';
			continue;
		}
		if ( $expected_filename !== wp_basename( $relative ) ) {
			$failures[] = $slug . ': image order/name mismatch at slot ' . ( $index + 1 ) . '.';
		}
		if ( ! $dimensions || 1000 !== (int) $dimensions[0] || 1000 !== (int) $dimensions[1] || 'image/jpeg' !== strtolower( (string) $dimensions['mime'] ) ) {
			$failures[] = $slug . ': image is not a 1000x1000 JPEG at slot ' . ( $index + 1 ) . '.';
		}
		if ( $definition['captions'][ $index ] !== (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) {
			$failures[] = $slug . ': image alt mismatch at slot ' . ( $index + 1 ) . '.';
		}
	}

	$seo_values = array(
		'rank_math_title'         => $definition['seo_title'],
		'rank_math_description'   => $definition['seo_description'],
		'rank_math_focus_keyword' => $definition['keyword'],
		'rank_math_canonical_url' => get_permalink( $product_id ),
	);
	foreach ( $seo_values as $meta_key => $value ) {
		if ( $value !== (string) get_post_meta( $product_id, $meta_key, true ) ) {
			$failures[] = $slug . ': SEO meta mismatch: ' . $meta_key . '.';
		}
	}
	if ( array( 'index', 'follow' ) !== get_post_meta( $product_id, 'rank_math_robots', true ) ) {
		$failures[] = $slug . ': Rank Math robots mismatch.';
	}

	$response = get_permalink( $product_id ) ? wp_remote_get( get_permalink( $product_id ), array( 'timeout' => 8 ) ) : null;
	$body     = is_wp_error( $response ) ? '' : (string) wp_remote_retrieve_body( $response );
	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		$failures[] = $slug . ': product URL did not return HTTP 200.';
	}
	if ( false === strpos( $body, '"@type":"Product"' ) || false === strpos( $body, 'FAQPage' ) ) {
		$failures[] = $slug . ': frontend Product or FAQPage schema missing.';
	}

	echo $product->post_title . ': id=' . $product_id . ' words=' . $words . ' short=' . $short . ' images=' . count( $image_ids ) . PHP_EOL;
}

if ( $failures ) {
	echo "FAILED\n";
	foreach ( $failures as $failure ) {
		echo '- ' . $failure . PHP_EOL;
	}
	exit( 1 );
}

echo "PASS\n";
echo "Five Halloween box products verified: published, SEO/GEO/AIO fields, 30 JPEGs at 1000x1000, galleries, inline figures, specs, FAQ/Product schema, category assignments and HTTP 200.\n";
