<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$sections = array(
	'custom-magnetic-gift-box' => array(
		'Retail Handling Notes for Magnetic Boxes' => 'Magnetic gift boxes should also be checked for how retail staff and fulfillment teams handle them in bulk. The front flap should not catch on neighboring boxes, the lid should stay closed inside the master carton, and the surface should resist scuffing during packing. These details protect the premium look after production, not only during sample review.',
	),
	'custom-medical-kit-packaging-box' => array(
		'Packing Accuracy for Healthcare Kits' => 'Medical kit packaging should support accurate packing because missing or misplaced components can create serious customer service issues. Component cavities, printed checklists, leaflet pockets, and QR code positions can help packing teams confirm the kit before sealing. This practical detail matters more for healthcare kits than for ordinary retail gift packaging.',
	),
	'custom-mug-packaging-box-with-window' => array(
		'Ceramic Mug Shipping Notes' => 'For ceramic mug boxes, buyers should confirm whether the window box is only for shelf display or also for direct shipping. If the box will travel through courier systems, the material may need stronger board, extra corner clearance, or an outer carton. A display-focused window carton and a shipping-ready mug package are not always the same structure.',
	),
	'custom-phone-packaging-box' => array(
		'Security Labels and Device Traceability' => 'Phone packaging often needs traceability details such as model label, memory version, color label, IMEI area, warranty sticker, or tamper seal. These fields should be planned before artwork approval because they affect panel layout and warehouse scanning. A clean electronics box still needs practical information for inventory control.',
	),
	'custom-pill-packaging-box' => array(
		'Pharmacy Shelf Organization' => 'Pill boxes should be easy to organize on pharmacy shelves and in distributor cartons. Consistent side-panel labels, readable product names, dosage strength, and batch information help staff identify products quickly. This is one reason pharmaceutical carton design should stay clear and functional even when the brand wants a premium healthcare look.',
	),
	'custom-printed-corrugated-pet-food-box' => array(
		'Subscription Packing for Pet Food Boxes' => 'For pet food subscription programs, the box may need space for multiple pouches, sample cards, feeding guides, coupons, or small accessories. The insert and locking structure should support repeated monthly fulfillment. A strong corrugated design can reduce packing time while keeping the customer unboxing experience branded.',
	),
	'custom-rigid-gift-box' => array(
		'Gift Box Family Planning' => 'A rigid gift box can become the base structure for a full packaging family. Brands can keep the same board thickness, lid proportion, and surface paper while changing insert cavities for different products. This approach helps B2B buyers control sampling cost and keep packaging consistent across product launches.',
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

	echo 'Batch 3 final top-up: ' . $post->post_title . PHP_EOL;
}
