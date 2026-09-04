<?php
/**
 * Verify the eight bread-bag product pages, media relationships and SEO data.
 */

require_once dirname( __DIR__ ) . '/wp-load.php';
require_once __DIR__ . '/import-bread-bag-products-202609.php';

$failures = array();
$products = vpn_bread_bag_202609_products();
$found    = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'meta_key'       => '_vpn_sample_import',
		'meta_value'     => VPN_BREAD_BAG_202609_MARKER,
	)
);

if ( count( $found ) !== count( $products ) ) {
	$failures[] = 'Expected ' . count( $products ) . ' products for the marker, found ' . count( $found ) . '.';
}

foreach ( $products as $product ) {
	$parsed = vpn_bread_bag_202609_parse_source( vpn_bread_bag_202609_source_path( $product ) );
	$post   = get_page_by_path( $product['slug'], OBJECT, 'product' );
	$label  = $product['slug'];

	if ( ! $post ) {
		$failures[] = $label . ': product not found by exact slug.';
		continue;
	}

	$content = (string) $post->post_content;
	$words   = vpn_bread_bag_202609_word_count( $content );
	$short   = trim( wp_strip_all_tags( (string) $post->post_excerpt ) );
	$expected_short = trim( $parsed['copy'] ? wp_strip_all_tags( '<p>' . vpn_bread_bag_202609_render_markdown( $parsed['copy'] )['short'] . '</p>' ) : '' );

	if ( $post->post_title !== $product['title'] || $post->post_name !== $product['slug'] ) {
		$failures[] = $label . ': title or slug mismatch.';
	}
	if ( ! in_array( $post->post_status, array( 'draft', 'publish', 'private' ), true ) ) {
		$failures[] = $label . ': unexpected status ' . $post->post_status . '.';
	}
	if ( $words < 800 || preg_match( '/<h1\b/i', $content ) ) {
		$failures[] = $label . ': long description is below 800 words or contains an H1.';
	}
	if ( $short !== $expected_short ) {
		$failures[] = $label . ': short description does not match the first two source paragraphs.';
	}
	if ( preg_match( '/SEO CONTENT BRIEF|INTERNAL LINK RECOMMENDATIONS|IMAGE SEO|IMAGE_SLOT_/i', $content ) ) {
		$failures[] = $label . ': raw brief/source placeholder text remains in content.';
	}

	$thumbnail_id = (int) get_post_thumbnail_id( $post->ID );
	$gallery_ids  = array_values( array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $post->ID, '_product_image_gallery', true ) ) ) ) );
	$image_ids    = array_merge( $thumbnail_id ? array( $thumbnail_id ) : array(), $gallery_ids );
	if ( 7 !== count( $image_ids ) || count( array_unique( $image_ids ) ) !== 7 ) {
		$failures[] = $label . ': expected seven unique featured/gallery image IDs.';
	}

	foreach ( $parsed['images'] as $index => $image ) {
		$attachment_id = $image_ids[ $index ] ?? 0;
		$attached      = $attachment_id ? (string) get_post_meta( $attachment_id, '_wp_attached_file', true ) : '';
		$base          = pathinfo( $image['filename'], PATHINFO_FILENAME );
		if ( ! $attachment_id || $base !== pathinfo( wp_basename( $attached ), PATHINFO_FILENAME ) ) {
			$failures[] = $label . ': image order/base mismatch at position ' . ( $index + 1 ) . '.';
			continue;
		}
		if ( 1 !== count( vpn_bread_bag_202609_exact_attachment_ids( $base ) ) ) {
			$failures[] = $label . ': duplicate or missing attachment record for ' . $image['filename'] . '.';
		}
		if ( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) !== $image['alt'] ) {
			$failures[] = $label . ': exact alt text mismatch for ' . $image['filename'] . '.';
		}
		if ( (int) wp_get_post_parent_id( $attachment_id ) !== (int) $post->ID ) {
			$failures[] = $label . ': attachment parent mismatch for ' . $image['filename'] . '.';
		}
		if ( ! wp_get_attachment_image_url( $attachment_id, 'full' ) ) {
			$failures[] = $label . ': attachment URL missing for ' . $image['filename'] . '.';
		}
	}

	$markers = substr_count( $content, 'stable-product-image:slot_' );
	$figures = preg_match_all( '/<figure\b/i', $content );
	$imgs    = preg_match_all( '/<img\b/i', $content );
	if ( 4 !== $markers || 4 !== $figures || 4 !== $imgs ) {
		$failures[] = $label . ': expected 4 stable markers, figures and images; got ' . $markers . '/' . $figures . '/' . $imgs . '.';
	}
	foreach ( array_slice( $parsed['images'], 1, 4 ) as $image ) {
		if ( false === strpos( $content, pathinfo( $image['filename'], PATHINFO_FILENAME ) ) || false === strpos( $content, esc_attr( $image['alt'] ) ) ) {
			$failures[] = $label . ': inline image filename or alt missing for ' . $image['filename'] . '.';
		}
	}
	if ( false === stripos( $content, '<h2>Frequently Asked Questions</h2>' ) || preg_match_all( '/<h3\b/i', $content ) < 3 ) {
		$failures[] = $label . ': visible FAQ heading/questions are missing.';
	}
	if ( false === strpos( $content, 'itemtype="https://schema.org/FAQPage"' ) || 0 === substr_count( $content, 'itemprop="acceptedAnswer"' ) ) {
		$failures[] = $label . ': visible FAQ schema microdata is missing.';
	}
	foreach ( $parsed['links'] as $link ) {
		if ( false === strpos( $content, esc_url( vpn_bread_bag_202609_local_url( $link['url'] ) ) ) || false === strpos( $content, esc_html( $link['anchor'] ) ) ) {
			$failures[] = $label . ': recommended internal link missing: ' . $link['anchor'] . '.';
		}
	}

	$actual_categories = wp_get_post_terms( $post->ID, 'product_cat', array( 'fields' => 'slugs' ) );
	$expected_categories = $product['categories'];
	sort( $actual_categories );
	sort( $expected_categories );
	if ( $actual_categories !== $expected_categories ) {
		$failures[] = $label . ': category set mismatch.';
	}
	$specs = get_post_meta( $post->ID, '_custom_box_product_specs', true );
	$moq   = '';
	if ( is_array( $specs ) ) {
		foreach ( $specs as $row ) {
			if ( isset( $row['label'], $row['value'] ) && 'Minimum Order Quantity (MOQ)' === $row['label'] ) {
				$moq = (string) $row['value'];
				break;
			}
		}
	}
	if ( ! is_array( $specs ) || count( $specs ) !== 21 || 'Project-based quotation; confirm quantity by RFQ' !== $moq ) {
		$failures[] = $label . ': specs are not the expected 21-row non-fixed-quantity set.';
	}
	if ( (string) get_post_meta( $post->ID, 'rank_math_title', true ) !== $parsed['seo_title']
		|| (string) get_post_meta( $post->ID, 'rank_math_description', true ) !== $parsed['meta']
		|| (string) get_post_meta( $post->ID, 'rank_math_focus_keyword', true ) !== $parsed['primary'] ) {
		$failures[] = $label . ': Rank Math title/description/focus keyword mismatch.';
	}
	$robots = get_post_meta( $post->ID, 'rank_math_robots', true );
	if ( (string) get_post_meta( $post->ID, 'rank_math_canonical_url', true ) !== vpn_bread_bag_202609_canonical_url( $product['slug'] ) || ! is_array( $robots ) || $robots !== array( 'index', 'follow' ) ) {
		$failures[] = $label . ': Rank Math canonical/index-follow settings are not self-referencing and index/follow.';
	}

	echo $product['title'] . ' (#' . $post->ID . ') status=' . $post->post_status
		. ' words=' . $words . ' images=' . count( $image_ids ) . ' figures=' . $figures
		. ' categories=' . implode( ',', $actual_categories ) . PHP_EOL;
}

if ( $failures ) {
	echo PHP_EOL . 'VERIFICATION FAILED' . PHP_EOL;
	foreach ( $failures as $failure ) {
		echo '- ' . $failure . PHP_EOL;
	}
	exit( 1 );
}

echo PHP_EOL . 'VERIFICATION PASSED: eight bread-bag products are complete.' . PHP_EOL;
