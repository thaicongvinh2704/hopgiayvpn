<?php
/**
 * Verify the five Halloween paper bag products after Product Sample Deploy.
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

if ( ! function_exists( 'vpn_halloween_bag_202609_product_definitions' ) ) {
	require_once get_template_directory() . '/inc/product-sample-deploy-tools/import-halloween-paper-bag-products-202609.php';
}

$definitions = vpn_halloween_bag_202609_product_definitions();
$expected = array();
foreach ( $definitions as $definition ) {
	$expected[ $definition['slug'] ] = $definition;
}

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => '_vpn_sample_import',
		'meta_value'     => VPN_HALLOWEEN_BAG_202609_MARKER,
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
	$featured   = (int) get_post_thumbnail_id( $product_id );
	$gallery    = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product_id, '_product_image_gallery', true ) ) ) );
	$image_ids  = array_values( array_unique( array_merge( array( $featured ), $gallery ) ) );
	$categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );
	$specs      = get_post_meta( $product_id, '_custom_box_product_specs', true );
	$faq        = (string) get_post_meta( $product_id, '_custom_box_product_faq_html', true );

	if ( $definition['title'] !== $product->post_title || $slug !== $definition['slug'] ) {
		$failures[] = $slug . ': title or slug mismatch.';
	}
	if ( str_word_count( wp_strip_all_tags( $product->post_content ) ) < 1200 ) {
		$failures[] = $slug . ': product content is shorter than 1200 words.';
	}
	if ( preg_match( '/<h1\b/i', $product->post_content ) ) {
		$failures[] = $slug . ': saved product content contains an extra H1.';
	}
	if ( 4 !== substr_count( $product->post_content, '<!-- stable-product-image:slot_' ) || 4 !== substr_count( $product->post_content, '<figure class="product-inline-figure' ) ) {
		$failures[] = $slug . ': expected four stable inline figures.';
	}
	if ( 6 !== count( $image_ids ) || 5 !== count( $gallery ) ) {
		$failures[] = $slug . ': expected one featured image and five gallery images.';
	}
	if ( is_wp_error( $categories ) || ! in_array( 'halloween-packaging', $categories, true ) || ! in_array( 'paper-bags-with-logo', $categories, true ) ) {
		$failures[] = $slug . ': required product categories are missing.';
	}
	if ( ! is_array( $specs ) || 21 !== count( $specs ) || false === strpos( wp_json_encode( $specs ), 'Available on request' ) ) {
		$failures[] = $slug . ': specifications or quote-only pricing mismatch.';
	}
	if ( 6 !== substr_count( $faq, '<details' ) || false === strpos( $faq, 'https://schema.org/FAQPage' ) ) {
		$failures[] = $slug . ': expected six FAQ items and FAQPage markup.';
	}
	if ( $definition['seo_title'] !== (string) get_post_meta( $product_id, 'rank_math_title', true ) || $definition['keyword'] !== (string) get_post_meta( $product_id, 'rank_math_focus_keyword', true ) ) {
		$failures[] = $slug . ': Rank Math title or focus keyword mismatch.';
	}

	foreach ( $image_ids as $index => $image_id ) {
		$file       = get_attached_file( $image_id );
		$dimensions = $file ? @getimagesize( $file ) : false;
		if ( ! $file || ! file_exists( $file ) || ! wp_attachment_is_image( $image_id ) || (int) wp_get_post_parent_id( $image_id ) !== $product_id ) {
			$failures[] = $slug . ': invalid image attachment at slot ' . ( $index + 1 ) . '.';
			continue;
		}
		if ( ! $dimensions || 1000 !== (int) $dimensions[0] || 1000 !== (int) $dimensions[1] || 'image/jpeg' !== strtolower( (string) $dimensions['mime'] ) ) {
			$failures[] = $slug . ': image must be a 1000x1000 JPEG at slot ' . ( $index + 1 ) . '.';
		}
	}

	$response = get_permalink( $product_id ) ? wp_remote_get( get_permalink( $product_id ), array( 'timeout' => 8 ) ) : null;
	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		$failures[] = $slug . ': product URL did not return HTTP 200.';
	}

	echo $product->post_title . ': id=' . $product_id . ' images=' . count( $image_ids ) . PHP_EOL;
}

if ( $failures ) {
	echo "FAILED\n";
	foreach ( $failures as $failure ) {
		echo '- ' . $failure . PHP_EOL;
	}
	exit( 1 );
}

echo "PASS\n";
echo "Five Halloween paper bag products verified: published, category-assigned, six-image galleries, SEO fields, specs, FAQ markup and HTTP 200.\n";
