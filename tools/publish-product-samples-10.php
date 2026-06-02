<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'draft', 'pending', 'private', 'publish' ),
		'posts_per_page' => 20,
		'meta_key'       => '_vpn_sample_import',
		'meta_value'     => 'product-samples-10',
	)
);

foreach ( $products as $product ) {
	wp_update_post(
		array(
			'ID'          => $product->ID,
			'post_status' => 'publish',
		)
	);

	echo 'Published: ' . $product->post_title . ' (' . get_permalink( $product->ID ) . ')' . PHP_EOL;
}

echo 'Total published: ' . count( $products ) . PHP_EOL;
