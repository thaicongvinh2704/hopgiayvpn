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
				'value' => 'product-samples-batch-3-ten',
			),
		),
	)
);

foreach ( $products as $post ) {
	$content = (string) $post->post_content;
	echo $post->post_title . PHP_EOL;
	echo '  product-inline-figure-small: ' . preg_match_all( '/product-inline-figure-small/i', $content ) . PHP_EOL;
	echo '  vpn-product-inline-image: ' . preg_match_all( '/vpn-product-inline-image/i', $content ) . PHP_EOL;
}
