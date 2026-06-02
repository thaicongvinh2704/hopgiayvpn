<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$sections = array(
	'custom-luxury-gift-box-with-paper-bag' => array(
		'Box and Bag Color Consistency' => array(
			'For a luxury gift box with paper bag, the box and bag should be reviewed together under the same lighting because paper material, lamination, and printing method can change the final color. A rigid box mounted with specialty paper may not look identical to a shopping bag printed on coated paper unless Pantone matching, proofing, and surface finish are planned early.',
			'The quote brief should include handle type, bag gusset, box dimensions, insert layout, logo scale, ribbon color, and carton packing. These details keep the set from feeling like two separate products and help procurement control presentation quality across a large campaign.',
		),
	),
	'custom-magnetic-gift-box' => array(
		'Magnetic Closure Testing Before Mass Production' => array(
			'A magnetic gift box should be tested for closure strength after the insert and product weight are confirmed. A magnet that feels strong on an empty sample may feel weaker when the tray is loaded, while an overly strong magnet can make the opening experience uncomfortable. The hinge crease, front flap, board thickness, and paper wrap all affect the final closing feel.',
			'Buyers should also check whether the box will be shipped assembled or flat-packed. Assembled rigid magnetic boxes create a more premium structure, while foldable magnetic boxes can reduce shipping volume. The correct choice depends on budget, presentation level, and fulfillment workflow.',
		),
	),
	'custom-medical-kit-packaging-box' => array(
		'Medical Kit Information Hierarchy' => array(
			'Medical kit packaging needs a clear information hierarchy because users may look for instructions, warning marks, component lists, QR codes, and batch labels before opening the box. The design should reserve clean areas for these details instead of treating every panel as decoration. This makes the packaging more practical for clinics, laboratories, distributors, and healthcare buyers.',
			'The insert should also follow the kit sequence when possible. If the user needs to remove a swab, tube, card, or instruction leaflet in a specific order, the box can support that workflow through cavity position, printed numbering, or a small instruction panel inside the lid.',
		),
	),
	'custom-mug-packaging-box-with-window' => array(
		'Window Placement for Mug Artwork' => array(
			'The window position should be planned around the actual mug artwork, not only around the front panel of the box. If the mug has a printed logo, handle detail, illustration, or souvenir graphic, the window should reveal the most valuable part while still protecting the ceramic surface. The box may also need anti-scratch clearance between the PET window and the mug.',
			'For retail display, buyers should test how the box stands on the shelf and how the handle sits inside. A mug can rotate during packing if the internal support is too loose, causing the visible artwork to face the wrong direction. A simple paperboard support can solve that issue.',
		),
	),
	'custom-paper-tube-food-packaging-box' => array(
		'Food Tube Filling and Sealing Details' => array(
			'Paper tube food packaging should be planned around how the product is filled and sealed. Tea leaves, coffee beans, candy, powder, and cookies behave differently inside a round container. Some projects need an inner bag, some need a foil liner, and some only need a paper tube used as outer gift packaging around a sealed food pouch.',
			'The lid tolerance should also be tested after printing and lamination. A tube that closes well before surface treatment may feel tighter or looser after production. Buyers should confirm whether the final package needs a tamper seal, bottom label, batch sticker, or food information panel.',
		),
	),
	'custom-phone-packaging-box' => array(
		'Accessory Layer Planning for Phone Boxes' => array(
			'A phone packaging box is often judged by how cleanly the device and accessories are separated. The top tray can hold the phone, while a lower layer can organize the cable, charger, SIM tool, warranty card, and quick-start guide. If the product is a refurbished phone, label space for model, memory, color, and condition grade becomes especially important.',
			'The sample should be checked for screen contact, corner pressure, tray removal, manual pocket size, and accessory cavity fit. These details make this product different from phone case packaging, where the main concern is retail visibility and model compatibility rather than device protection.',
		),
	),
	'custom-pill-packaging-box' => array(
		'Readable Panels for Medicine Buyers' => array(
			'Pill packaging needs readable panels for dosage, ingredients, warning notes, expiry date, batch number, barcode, and QR code. Even when the design uses a premium healthcare style, the functional text must stay clear. Small type should not be placed over reflective foil, dark gradients, or busy patterns that make pharmacy review difficult.',
			'If the pill product uses blister packs, the carton should be measured around the blister stack and leaflet. If it uses a small bottle, the box may need an insert or neck support. These two use cases look similar from the outside but require different internal planning.',
		),
	),
	'custom-printed-corrugated-pet-food-box' => array(
		'Corrugated Strength for Pet Food Weight' => array(
			'Pet food packaging often carries heavier pouches, sample packs, or multiple treat bags, so corrugated strength should be selected from the expected product weight and shipping route. The flute type, paper liner, locking tab, and carton direction all affect whether the box stays square during delivery and shelf display.',
			'The printed layout can include flavor name, pet type, nutrition panel, feeding note, barcode, subscription message, and brand story. These details make the package useful for both retail and e-commerce, especially when several pet food SKUs share the same box structure.',
		),
	),
	'custom-rigid-gift-box' => array(
		'Rigid Box Edge Quality and Reuse Value' => array(
			'Rigid gift boxes are often kept by customers after purchase, so edge quality, lid fit, bottom stability, and surface feel matter more than they do on disposable folding cartons. The paper wrap should be smooth around corners, and the lid should lift without scraping the base. These small details make the package feel more valuable.',
			'For B2B programs, the same rigid structure can serve several gift products if the insert changes. This helps brands keep one premium packaging family while adapting the inside for candles, accessories, skincare, stationery, or small electronics.',
		),
	),
	'custom-single-wine-bottle-gift-box' => array(
		'Single Bottle Balance and Label Protection' => array(
			'A single wine bottle gift box should keep one bottle centered, stable, and easy to remove. The base support must handle glass weight, while the neck area should prevent side movement without covering the label. If the bottle has a raised seal, foil capsule, or unusual shoulder shape, those details should be measured before the dieline is approved.',
			'This product differs from a double wine bottle box because there is no center divider to balance weight. The structure must create stability from the base, side wall, insert, and lid fit. For corporate gifting, the box can also include a greeting card pocket or printed message inside the lid.',
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

	echo 'Batch 3 top-up: ' . $post->post_title . PHP_EOL;
}
