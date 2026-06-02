<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'   => '_vpn_sample_import',
				'value' => 'product-samples-batch-2-five',
			),
		),
	)
);

echo 'Batch 2 product count: ' . count( $products ) . PHP_EOL;

foreach ( $products as $post ) {
	$content = (string) $post->post_content;
	$text    = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $content ) ) );
	$words   = str_word_count( $text );
	$specs   = get_post_meta( $post->ID, '_custom_box_product_specs', true );
	$moq     = '';

	if ( is_array( $specs ) ) {
		foreach ( $specs as $row ) {
			if ( isset( $row['label'], $row['value'] ) && 'Minimum Order Quantity (MOQ)' === $row['label'] ) {
				$moq = $row['value'];
				break;
			}
		}
	}

	$h1_count     = preg_match_all( '/<h1\b/i', $content );
	$figure_count = preg_match_all( '/<figure\b/i', $content );
	$url          = get_permalink( $post );

	echo PHP_EOL;
	echo $post->post_title . ' (#' . $post->ID . ')' . PHP_EOL;
	echo 'URL: ' . $url . PHP_EOL;
	echo 'Status: ' . $post->post_status . PHP_EOL;
	echo 'Words: ' . $words . PHP_EOL;
	echo 'Content H1: ' . $h1_count . PHP_EOL;
	echo 'Figures: ' . $figure_count . PHP_EOL;
	echo 'Specs: ' . ( is_array( $specs ) ? count( $specs ) : 0 ) . PHP_EOL;
	echo 'MOQ: ' . $moq . PHP_EOL;
	echo 'Duplicate risk: ' . get_post_meta( $post->ID, '_vpn_duplicate_risk_score', true ) . '/10' . PHP_EOL;
}
