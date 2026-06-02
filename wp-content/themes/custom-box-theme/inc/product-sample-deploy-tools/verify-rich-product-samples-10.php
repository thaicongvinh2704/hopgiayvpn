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
	$specs = get_post_meta( $product->ID, '_custom_box_product_specs', true );
	$moq   = '';

	foreach ( (array) $specs as $row ) {
		if ( isset( $row['label'] ) && 'Minimum Order Quantity (MOQ)' === $row['label'] ) {
			$moq = $row['value'] ?? '';
		}
	}

	echo $product->ID . "\t";
	echo $product->post_status . "\t";
	echo str_word_count( wp_strip_all_tags( $product->post_content ) ) . " words\t";
	echo 'specs=' . count( (array) $specs ) . "\t";
	echo 'moq=' . $moq . "\t";
	echo get_permalink( $product->ID ) . PHP_EOL;
}

echo 'Total: ' . count( $products ) . PHP_EOL;
