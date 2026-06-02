<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

function vpn_append_sections( string $slug, array $sections ): void {
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post ) {
		echo "Missing: {$slug}\n";
		return;
	}

	$extra = "\n\n";
	foreach ( $sections as $section ) {
		$extra .= '<h2>' . esc_html( $section['h'] ) . "</h2>\n";
		foreach ( $section['p'] as $paragraph ) {
			$extra .= '<p>' . $paragraph . "</p>\n";
		}
	}

	wp_update_post(
		array(
			'ID'           => $post->ID,
			'post_content' => rtrim( $post->post_content ) . $extra,
		)
	);

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $post->ID );
	}

	echo "Expanded: {$post->post_title}\n";
}

$link = function ( string $path, string $text ): string {
	return '<a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $text ) . '</a>';
};

vpn_append_sections( 'custom-ampoule-packaging-box', array(
	array( 'h' => 'Sample Approval Points for Ampoule Brands', 'p' => array(
		'During sample approval, ampoule buyers should not only check the printed color. They should place the real vial into the tray, close the box, shake it gently, and confirm whether the glass touches the side wall, lid, or another vial. If the ampoule has a snap neck, dropper, or narrow cap, the insert opening should support the shape without forcing the user to pull too hard.',
		'It is also useful to test how the box looks after repeated opening. Treatment sets are often opened over several days, so the tray should continue to hold the remaining vials neatly after the first product is removed. This is one of the reasons ampoule packaging needs more engineering attention than a normal beauty carton.',
	) ),
	array( 'h' => 'Export Packing Notes for Glass Beauty Vials', 'p' => array(
		'If the ampoules will be exported, the outer carton plan should be discussed together with the retail box. Small glass products may pass a visual sample check but still fail if the master carton leaves too much room between units. Carton quantity, divider use, pallet direction, and courier handling can all affect breakage risk.',
		'For premium skincare sets, brands may also request a retail sleeve plus a stronger inner tray. This allows the package to look clean on shelf while the protective layer quietly handles the real shipping problem. Buyers should confirm whether the product will ship alone, inside a skincare kit, or inside a larger promotional carton.',
	) ),
	array( 'h' => 'Ampoule Artwork Details That Reduce Buyer Questions', 'p' => array(
		'Ampoule customers often ask how many vials are included, how often each vial should be used, whether the formula is for morning or night, and what skin concern it targets. The packaging can answer those questions through icons, numbered cavities, small treatment charts, or a clear back-panel routine.',
		'For B2B buyers, these artwork decisions reduce customer-service questions after launch. They also make the product easier for distributors to explain in catalogs. When the design team receives final copy early, the dieline can reserve the right space instead of squeezing instructions into a narrow side panel later.',
	) ),
	array( 'h' => 'Cost Control Without Weakening Protection', 'p' => array(
		'Cost can be controlled by choosing the correct insert for the product value. A luxury glass ampoule set may justify EVA or rigid board, while a sample vial program may use a folded paper insert. The goal is not always the most expensive material; it is the structure that prevents movement at the right price point.',
		'Brands ordering 1000 boxes can start with a practical material and upgrade finishing after the structure is approved. This prevents the project from spending too much on foil or lamination before confirming the vial fit, tray depth, and carton packing logic.',
	) ),
	array( 'h' => 'What Makes an Ampoule Box Ready for Repeat Orders', 'p' => array(
		'A repeatable ampoule box should have a stable dieline, confirmed insert tolerance, consistent color standard, and clear artwork zones for future formulas. If the brand later adds hydration, repair, whitening, and anti-aging versions, the structure should not need to be redesigned from zero.',
		'This makes the first packaging project strategically important. A well-planned ampoule structure becomes a platform for future launches, while a rushed carton becomes a one-time fix that is hard to scale across a full skincare treatment range.',
	) ),
) );

vpn_append_sections( 'custom-charging-cable-packaging-box', array(
	array( 'h' => 'Accessory Retailers Need Fast SKU Recognition', 'p' => array(
		'Charging cable packaging should help store staff and customers identify the correct item quickly. A shelf may contain USB-A to Type-C, Type-C to Type-C, Lightning, braided, fast-charge, and data-transfer variants. If the model system is unclear, the package creates returns and slow checkout conversations.',
		'For that reason, the front or top panel should show connector type, cable length, and key compatibility in a consistent location. Retailers prefer packaging that makes mixed-SKU displays easy to organize and easy to restock during busy sales periods.',
	) ),
	array( 'h' => 'Cable Coil Fit and Connector Head Clearance', 'p' => array(
		'The dieline should be tested with the real cable coil, not an estimated drawing. Braided cables are thicker and springier than basic PVC cables, and connector heads can press against the carton corners if the box is too tight. A small paper holder can solve this problem without adding much cost.',
		'If the cable includes a Velcro tie, adapter, or small instruction leaflet, the packing order should be decided before the final sample. This makes the assembly process predictable for high-volume orders and reduces the chance of boxes bulging after packing.',
	) ),
	array( 'h' => 'Compliance and Marketplace Information Areas', 'p' => array(
		'Electronics accessory packaging often needs certification marks, recycling symbols, warranty text, QR codes, importer information, and sometimes marketplace-specific labels. These details should be placed where they can be scanned or read without interrupting the main product message.',
		'Amazon sellers and retail distributors may have different label requirements. A flexible back-panel layout lets the same box structure support several sales channels by changing sticker placement or printed information blocks.',
	) ),
	array( 'h' => 'When to Use a Window and When to Avoid It', 'p' => array(
		'A window can be useful if the cable color, connector finish, or braided texture is a selling point. It is less useful when the product is a basic cable sold mainly by specification. Windows add cost and can reduce board strength, so the decision should match the retail strategy.',
		'For mass retail, a printed product image may be enough. For premium cables or colorful accessories, a small window can improve trust because the customer sees the actual material. The window should not interfere with hang-tab strength or carton stacking.',
	) ),
	array( 'h' => 'Planning a Cable Packaging Family', 'p' => array(
		'A strong cable packaging system can scale across several models. The brand may keep the same box size for one-meter and two-meter cables, then use color bands or model blocks to separate variants. This keeps tooling simple and makes the product line look organized.',
		'Before mass production, buyers should decide which parts of the design are fixed and which parts change by SKU. That planning is what makes a packaging family efficient instead of forcing a new artwork system for every cable model.',
	) ),
) );

$common = array(
	'custom-colored-pencil-packaging-box' => array( 'Colored Pencil Set Buyer Checks', 'Before production, buyers should review pencil count, row order, color chart accuracy, tray groove spacing, and whether the box will be reused after purchase. Artist buyers care about organization and shade comparison, so the insert should make the full palette easy to scan.', 'A good sample should be tested by sliding the tray open, removing pencils from the middle row, and returning them to place. If pencils roll, scrape, or hide color names, the packaging may look nice but fail as a working art supply box.', 'Artist Range, School Range, and Gift Range Differences', 'Artist-grade pencil packaging can use calmer artwork, stronger board, and reusable trays. School sets may need brighter graphics and clearer age or safety information. Gift sets can add sleeves, ribbons, or heavier board without changing the core tray logic.', 'This is why one generic stationery description is not enough. The same pencil count can require different packaging depending on whether the buyer sells to schools, art shops, hobby stores, or promotional gift programs.' ),
	'custom-corporate-gift-set-packaging-boxes' => array( 'Gift Kit Assembly and Fulfillment Planning', 'Corporate gift boxes are often packed under deadline pressure before an event or holiday. The insert should make assembly intuitive: each item should have an obvious position, and the packing team should not need to guess which card, bottle, notebook, or accessory goes where.', 'If there are several recipient tiers, the structure can keep the same outer dimensions while changing insert cavities or printed messages. This helps agencies manage standard, VIP, and executive gift sets without redesigning the entire campaign.', 'Recipient Emotion and Brand Memory', 'The goal of a corporate gift box is not only to protect products. It should create a moment that feels intentional and memorable. Inner-lid copy, reveal order, paper texture, and closure style all influence whether the recipient keeps or discards the packaging.', 'That emotional function is why the content should not sound like supplement drawer packaging. A supplement drawer box builds product trust; a corporate gift box builds relationship value and campaign recall.' ),
	'custom-cosmetic-packaging-box' => array( 'Beauty SKU Systems and Formula Families', 'Cosmetic brands rarely launch only one product forever. A box system should be ready for cleansers, creams, masks, toners, serums, and makeup variants. The design grid should allow formula names, shade labels, and product volumes to change without breaking the brand family.', 'This planning helps distributors and retailers understand the line quickly. It also helps private label factories reuse one structural logic for multiple formulas while still giving each brand a distinct printed identity.', 'Balancing Beauty Claims and Compliance Text', 'Cosmetic packaging needs emotional selling copy, but it also needs ingredient lists, usage instructions, warnings, barcode, batch details, and sometimes certification icons. The layout should protect both needs instead of letting marketing copy crowd out compliance space.', 'A good sample should be checked with final copy, not placeholder text. Many cosmetic cartons look fine at first and become crowded only after the real ingredient panel is added.' ),
	'custom-cosmetic-paper-bag' => array( 'Checkout Handling and Carry Weight Testing', 'A cosmetic paper bag should be tested with the real product boxes it will carry. Handle strength, bottom reinforcement, and gusset width matter when customers buy multiple jars, glass bottles, or gift sets in one visit.', 'The bag should open quickly at checkout and stand well enough for staff to insert products neatly. If it collapses too easily, the retail experience becomes slower and less polished.', 'Retail Visibility After Purchase', 'Unlike a product box, a paper bag travels outside the store. Its logo, color, handle, and finish become moving brand exposure in malls, events, salons, and gift campaigns.', 'This is why the bag can justify better paper or foil even when it does not directly protect the product inside. It supports brand memory after the sale has already happened.' ),
	'custom-crayon-packaging-box' => array( 'Classroom Use and Parent Purchase Decisions', 'Crayon packaging should speak to both children and adults. Children notice color and friendly graphics; parents and teachers look for count, safety marks, age guidance, and whether the package is easy to open and close.', 'This makes the content different from colored pencil packaging. Crayons are often bought for classroom, preschool, or back-to-school use, so durability and safety communication matter more than artist-grade color management.', 'Wax Stick Protection and Simple Dividers', 'Crayons can chip, rub, or mark the inside of the box if packed loosely. A simple paper divider or snug carton can keep sticks aligned without making the package too expensive for mass retail.', 'The sample should be tested by tipping and shaking the box. If crayons fall into a pile or press too hard against a window, the structure should be adjusted before production.' ),
	'custom-dinnerware-packaging-box' => array( 'Ceramic Weight Changes the Packaging Design', 'Dinnerware packaging must be checked with the actual plates, bowls, or mugs because ceramic weight changes how the box performs. An empty sample may look perfect, but the real set can stress corners, handles, inserts, and glue seams.', 'Buyers should confirm whether the set ships by courier, container, or retail pallet. The same retail box may need different master carton protection depending on the shipping route.', 'Divider Layout for Mixed Tableware Sets', 'A mixed set with plates, bowls, and mugs needs more planning than a single plate carton. Each piece has a different contact point and risk area. Mug handles, plate rims, and bowl edges should not carry pressure from other pieces.', 'This is where corrugated partitions, molded pulp, or EVA can make a measurable difference. The goal is to stop movement while avoiding pressure points that cause chips.' ),
	'custom-phone-case-packaging-box' => array( 'Model Compatibility Is the Core Sales Message', 'Phone case buyers need to know whether the case fits their exact device. The packaging should make the model name, series, screen size, and camera cutout compatibility easy to find.', 'This separates phone case packaging from cable packaging. A cable buyer checks connector and length; a phone case buyer checks model fit and case appearance.', 'Product Visibility and Anti-Counterfeit Details', 'Many phone case packages benefit from a window because customers want to see color, texture, and edge shape. If a window is not used, the printed product image and model label must be very clear.', 'Brands can also add QR authentication, warranty stickers, or anti-counterfeit labels. These details are especially useful for accessory lines sold through distributors or marketplaces.' ),
	'custom-supplement-drawer-packaging-box' => array( 'Wellness Routine Packaging Needs Clear Use Order', 'Supplement drawer boxes often contain a routine: morning capsules, evening sachets, probiotics, collagen bottles, or starter kits. The insert can guide the user through the order instead of simply holding products in place.', 'This makes the page different from corporate gift packaging. The unboxing is still important, but the purpose is product trust and routine clarity, not a campaign message.', 'Information Trust for Health Product Buyers', 'Supplement packaging should reserve space for dosage, ingredient notes, certifications, batch number, expiry date, and QR information. These details help buyers and distributors feel the product is organized and credible.', 'Finishing should support trust. A calm matte surface, clean typography, and precise insert fit usually work better than excessive decoration for wellness products.' ),
);

foreach ( $common as $slug => $data ) {
	vpn_append_sections(
		$slug,
		array(
			array( 'h' => $data[0], 'p' => array( $data[1], $data[2] ) ),
			array( 'h' => $data[3], 'p' => array( $data[4], $data[5] ) ),
			array( 'h' => 'Extra Quote Details to Confirm Before Sampling', 'p' => array(
				'For this product, the quotation should include exact product measurements, expected quantity, box or bag structure, paper material, printing method, finishing option, packing method, and any special retail or export requirement. These details allow the manufacturer to prepare a realistic price instead of a rough estimate.',
				'If the buyer already has artwork, a reference photo, or a current package that needs improvement, those files should be sent together. This shortens sampling time and helps the production team identify which details must be preserved and which details can be improved.',
			) ),
			array( 'h' => 'Quality Control Points for Repeat Orders', 'p' => array(
				'Repeat orders should check color consistency, paper thickness, glue position, die-cut accuracy, insert fit, carton packing, and surface finishing. Even small changes can affect how the product looks or how quickly the packing team can assemble it.',
				'For wholesale B2B production, these checks are more important than writing a beautiful description. They help the buyer receive packaging that performs the same way across several production runs.',
			) ),
			array( 'h' => 'How This Product Page Avoids Template Duplication', 'p' => array(
				'The content should continue to focus on the product-specific buying problem rather than generic phrases such as protection, branding, and export packaging. The unique details in this page should guide headings, examples, internal links, and CTA wording.',
				'When future products are added, they should not copy this structure word for word. They should start from their own product DNA, including what goes inside, what can go wrong during packing, and what information the buyer needs before requesting a quote.',
			) ),
		)
	);
}
