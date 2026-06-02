<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$sections = array(
	'custom-luxury-gift-box-with-paper-bag' => array(
		'Campaign Packing for Gift Box and Bag Sets' => 'For large gift campaigns, the rigid box and paper bag should be packed as a controlled set so warehouse teams do not mix bag colors, handle styles, or insert versions. This is especially important when one campaign uses several gift combinations but needs one consistent brand presentation across all locations.',
	),
	'custom-paper-tube-food-packaging-box' => array(
		'Retail Shelf Behavior of Round Food Tubes' => 'Round food tubes should be reviewed for shelf stability, label direction, lid grip, and carton packing. Unlike rectangular boxes, a tube can rotate if the display tray is not planned correctly. Clear front artwork and consistent label orientation help food retailers present the product neatly.',
	),
	'custom-rigid-gift-box' => array(
		'Premium Surface Review Before Approval' => 'Before approving a rigid gift box, buyers should review the surface paper, corner wrap, lid fit, insert color, foil position, and bottom stability together. A strong rigid structure only feels premium when these visible and tactile details are controlled in the final sample.',
	),
	'custom-single-wine-bottle-gift-box' => array(
		'Bottle Gift Packing for Corporate Orders' => 'For corporate wine gift orders, the box can include a printed message, greeting card slot, product story, or brand panel inside the lid. These details make a single bottle feel more complete as a gift while keeping the outside structure clean and professional.',
	),
);

foreach ( $sections as $slug => $product_sections ) {
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post ) {
		echo "Missing {$slug}\n";
		continue;
	}

	$append = '';
	foreach ( $product_sections as $heading => $paragraph ) {
		$append .= "\n<h2>" . esc_html( $heading ) . "</h2>\n";
		$append .= '<p>' . esc_html( $paragraph ) . "</p>\n";
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

	echo 'Batch 3 text floor top-up: ' . $post->post_title . PHP_EOL;
}
