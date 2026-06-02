<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$sections = array(
	'custom-double-wine-bottle-gift-box' => array(
		'Wine Logistics Details Buyers Should Confirm' => array(
			'For a double wine bottle gift box, the most important details are bottle diameter, shoulder height, neck height, filled weight, and the distance between the two bottles. These measurements decide whether the divider should be straight, stepped, padded, or shaped around the bottle shoulders. If the bottles have heavy glass bases, the bottom panel can be reinforced with thicker board or an additional hidden support layer.',
			'Buyers should also confirm whether the box will be used for local gifting, courier delivery, export cartons, or retail shelf display. A holiday wine set for corporate gifting may need a clean magnetic presentation, while a distributor shipment may need a stronger sleeve, tighter divider, and more practical outer carton planning.',
		),
		'How This Wine Box Differs From General Gift Packaging' => array(
			'Unlike a general gift box, this structure is planned around liquid weight and two tall glass items sitting side by side. The divider is not decorative only; it controls bottle movement, protects labels from rubbing, and keeps the pair balanced when the recipient opens the lid. The packaging should feel ceremonial without becoming loose or fragile.',
			'This makes the product suitable for wineries, wine importers, hotel gift programs, festive hampers, premium food brands, and corporate buyers that need repeatable bottle presentation. The same construction can be adapted for red wine, sparkling wine, olive oil, tea bottles, or beverage gift sets with similar dimensions.',
		),
	),
	'custom-drawer-gift-box' => array(
		'Drawer Tolerance and Slide Feel' => array(
			'A drawer gift box depends heavily on sliding tolerance. If the tray is too tight, the customer has to pull hard and the unboxing feels rough. If it is too loose, the tray can move during delivery and damage the product inside. During sampling, the sleeve thickness, mounted paper thickness, tray wall height, ribbon pull, and insert height should be checked together.',
			'This detail is especially important for jewelry, accessories, candle sets, small electronics, stationery gifts, and promotional kits. A smooth drawer opening makes the product feel intentional, while the insert protects the item after the tray has been pulled out.',
		),
		'Best Fit for Modular Gift Programs' => array(
			'This drawer gift box works well when a brand wants one packaging system for several gift combinations. The outside sleeve can keep the same brand color and logo, while the inner tray changes for different products. For B2B buyers, this reduces redesign work and helps procurement manage several SKUs with one recognizable packaging family.',
			'It is different from a supplement drawer box because the message is not dosage, ingredient trust, or health information. Here the focus is on reveal, product arrangement, campaign color, and the tactile slide-out experience used for gifts, retail accessories, and branded event merchandise.',
		),
	),
	'custom-fountain-pen-gift-box' => array(
		'Pen Presentation Details That Affect Perceived Quality' => array(
			'A fountain pen gift box should protect small luxury details that are easy to overlook: the nib, clip, cap band, refill position, warranty card, and instruction booklet. The tray groove should hold the pen straight without pressing too hard on the lacquer, metal finish, or clip. For premium pens, the empty space around the pen is also part of the presentation.',
			'The inner lid can include a printed brand message, certificate holder, ribbon tab, or small paper pocket. These features make the box more suitable for executive gifts, graduation gifts, hotel VIP gifts, stationery boutiques, and private label pen collections.',
		),
		'Materials for a Refined Stationery Box' => array(
			'Rigid greyboard with mounted art paper is common for fountain pen packaging because it gives the box a stable hand feel. Velvet, suede paper, flocked paper, or shaped EVA can be used for the insert depending on the value of the pen. A magnetic lid, sleeve, or lid-and-base structure can be chosen according to the target price level.',
			'Printing should stay controlled because a pen box usually benefits from restraint. Foil stamping, debossing, blind embossing, edge color, and soft-touch lamination can make the brand feel refined without overwhelming the product.',
		),
	),
	'custom-knife-set-packaging-box' => array(
		'Safety and Slot Planning for Knife Sets' => array(
			'A knife set packaging box must consider sharp edges, handle weight, blade length, and the direction each knife faces when removed. The insert should keep every blade separated, reduce contact between metal surfaces, and prevent the product from shifting toward the box wall. For heavier chef knife sets, reinforced corners and a stronger base can be added.',
			'The printed layout can include safety warnings, blade material, handle material, care instructions, warranty information, barcode, and set quantity. These details help retailers and online buyers understand the product quickly while keeping the package practical for handling.',
		),
		'Packaging Use Cases for Kitchenware Brands' => array(
			'This box can be used for chef knife sets, steak knife sets, carving tools, kitchen scissors, sharpening rod bundles, barbecue tool kits, or premium kitchenware gifts. The structure can be made as a lid-and-base box for gift presentation, a corrugated retail box for stronger protection, or a sleeve system for boxed kitchenware lines.',
			'It is different from dinnerware packaging because the internal logic is based on long narrow tools, blade safety, and fixed slot distance. Dinnerware boxes focus on impact protection around fragile ceramic items, while knife packaging must secure sharp products and guide safe removal.',
		),
	),
	'custom-kraft-paper-bag-for-supplement-packaging' => array(
		'Supplement Retail Bag Details' => array(
			'A kraft paper bag for supplement packaging should be planned around bottle weight, jar size, pouch height, and the number of products carried after purchase. Reinforced handles and a bottom board can help when the bag carries glass bottles, protein jars, multiple vitamin packs, or wellness gift bundles. The gusset width should match the real product mix instead of using a generic shopping bag size.',
			'The print layout can include a wellness logo, campaign slogan, QR code, store location, subscription message, or product line color. Kraft paper gives the bag a natural health-market tone, but it still needs clean brand printing and reliable construction for retail use.',
		),
		'How It Supports Health Brand Campaigns' => array(
			'This bag is useful for supplement shops, vitamin brands, nutrition clinics, fitness events, pharmacy promotions, and wellness subscription programs. It can carry bottled supplements, sachet boxes, collagen packs, probiotics, herbal products, or sample kits while keeping the presentation consistent with the brand.',
			'It is different from a cosmetic paper bag because the communication should feel trustworthy, practical, and wellness-focused. Instead of beauty boutique luxury, this product emphasizes kraft texture, carrying strength, clean health branding, and clear campaign information for repeat retail use.',
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

	echo 'Final top-up: ' . $post->post_title . PHP_EOL;
}
