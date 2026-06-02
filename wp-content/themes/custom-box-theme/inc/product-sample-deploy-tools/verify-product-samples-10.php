<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => 'draft',
		'posts_per_page' => 20,
		'meta_key'       => '_vpn_sample_import',
		'meta_value'     => 'product-samples-10',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

foreach ( $products as $product ) {
	echo $product->ID . "\t";
	echo $product->post_title . "\t";
	echo 'thumb=' . get_post_thumbnail_id( $product->ID ) . "\t";
	echo 'gallery=' . get_post_meta( $product->ID, '_product_image_gallery', true ) . "\t";
	echo admin_url( 'post.php?post=' . $product->ID . '&action=edit' ) . PHP_EOL;
}

echo 'Total: ' . count( $products ) . PHP_EOL;
