<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_vpn_sample_import',
				'value' => 'product-samples-batch-3-ten',
			),
		),
	)
);

foreach ( $products as $post ) {
	$content = str_replace(
		'<figure class="vpn-product-inline-image">',
		'<figure class="product-inline-figure product-inline-figure-small">',
		(string) $post->post_content
	);

	wp_update_post(
		array(
			'ID'           => $post->ID,
			'post_content' => $content,
		)
	);

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $post->ID );
	}

	echo 'Fixed inline image class: ' . $post->post_title . PHP_EOL;
}
