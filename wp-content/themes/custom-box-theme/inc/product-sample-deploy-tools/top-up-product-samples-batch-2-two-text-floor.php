<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$sections = array(
	'custom-fountain-pen-gift-box' => array(
		'Retail Labeling for Fountain Pen Sets' => array(
			'For fountain pen packaging, the side panel can reserve space for pen model, ink color, nib size, refill compatibility, barcode, warranty note, and batch label. This small information area helps stationery retailers manage SKUs without interrupting the premium front design.',
		),
	),
	'custom-knife-set-packaging-box' => array(
		'Master Carton Planning for Knife Packaging' => array(
			'For B2B knife set orders, the product box should also be checked against the master carton layout. Carton direction, unit weight, insert pressure, and stacking height all affect whether the finished packaging stays stable during warehouse storage and export shipping.',
		),
	),
);

foreach ( $sections as $slug => $product_sections ) {
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post ) {
		echo "Missing {$slug}\n";
		continue;
	}

	$append = '';
	foreach ( $product_sections as $heading => $paragraphs ) {
		$append .= "\n<h2>" . esc_html( $heading ) . "</h2>\n";
		foreach ( $paragraphs as $paragraph ) {
			$append .= '<p>' . esc_html( $paragraph ) . "</p>\n";
		}
	}

	wp_update_post(
		array(
			'ID'           => $post->ID,
			'post_content' => rtrim( $post->post_content ) . "\n" . $append,
		)
	);

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $post->ID );
	}

	echo 'Text floor top-up: ' . $post->post_title . PHP_EOL;
}
