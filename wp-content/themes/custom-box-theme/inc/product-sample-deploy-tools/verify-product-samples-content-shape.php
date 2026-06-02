<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page' => 20,
		'meta_key'       => '_vpn_sample_import',
		'meta_value'     => 'product-samples-10',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

foreach ( $products as $product ) {
	preg_match_all( '/<h1\b/i', $product->post_content, $h1 );
	preg_match_all( '/<figure\b/i', $product->post_content, $figures );
	preg_match_all( '/product-inline-figure-small/i', $product->post_content, $inline_figures );

	echo $product->ID . "\t";
	echo $product->post_title . "\t";
	echo 'content_h1=' . count( $h1[0] ) . "\t";
	echo 'figures=' . count( $figures[0] ) . "\t";
	echo 'inline_small=' . count( $inline_figures[0] ) . "\t";
	echo get_permalink( $product->ID ) . PHP_EOL;
}
