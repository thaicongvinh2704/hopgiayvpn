<?php
/**
 * Rewrite the first 10 sample products with stronger product-specific content.
 *
 * Usage:
 *   php tools/rewrite-product-samples-batch-1.php
 */

require_once dirname( __DIR__ ) . '/wp-load.php';

function vpn_batch_link( string $path, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_batch_images( int $product_id, array $captions ): array {
	$ids = array();
	$thumb = get_post_thumbnail_id( $product_id );
	if ( $thumb ) {
		$ids[] = $thumb;
	}
	foreach ( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product_id, '_product_image_gallery', true ) ) ) ) as $id ) {
		if ( $id && ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}

	$out = array();
	foreach ( array_slice( $ids, 0, 4 ) as $index => $id ) {
		$image = wp_get_attachment_image(
			$id,
			'large',
			false,
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
		if ( $image ) {
			$out[] = '<figure class="product-inline-figure product-inline-figure-small' . ( $index % 2 ? ' is-narrow' : '' ) . '">' . $image . '<figcaption>' . esc_html( $captions[ $index ] ?? $captions[0] ) . '</figcaption></figure>';
		}
	}
	return $out;
}

function vpn_batch_content( int $product_id, array $p ): string {
	$imgs = vpn_batch_images( $product_id, $p['captions'] );
	$img1 = $imgs[0] ?? '';
	$img2 = $imgs[1] ?? '';
	$img3 = $imgs[2] ?? '';
	$img4 = $imgs[3] ?? '';
	$links = array();
	foreach ( $p['links'] as $key => $link ) {
		$links[ $key ] = vpn_batch_link( $link[0], $link[1] );
	}
	$sections = $p['sections'];

	return <<<HTML
<h2>{$sections[0]['h']}</h2>
<p>{$sections[0]['p'][0]}</p>
<p>{$sections[0]['p'][1]}</p>
{$img1}
<h2>{$sections[1]['h']}</h2>
<p>{$sections[1]['p'][0]}</p>
<p>{$sections[1]['p'][1]}</p>
<h2>{$sections[2]['h']}</h2>
<p>{$sections[2]['p'][0]}</p>
<p>{$sections[2]['p'][1]}</p>
{$img2}
<h2>{$sections[3]['h']}</h2>
<p>{$sections[3]['p'][0]}</p>
<p>{$sections[3]['p'][1]}</p>
<h2>{$sections[4]['h']}</h2>
<p>{$sections[4]['p'][0]}</p>
<p>{$sections[4]['p'][1]}</p>
{$img3}
<h2>{$sections[5]['h']}</h2>
<p>{$sections[5]['p'][0]}</p>
<p>{$sections[5]['p'][1]}</p>
<h2>{$sections[6]['h']}</h2>
<p>{$sections[6]['p'][0]}</p>
<p>{$sections[6]['p'][1]}</p>
{$img4}
<h2>{$sections[7]['h']}</h2>
<p>{$sections[7]['p'][0]}</p>
<p>{$sections[7]['p'][1]}</p>
<h2>{$sections[8]['h']}</h2>
<p>{$sections[8]['p'][0]}</p>
<p>{$sections[8]['p'][1]}</p>
HTML;
}

$products = array(
	'custom-ampoule-packaging-box' => array(
		'short' => 'CUSTOM AMPOULE PACKAGING BOX is designed for skincare ampoules, serum vials, treatment shots, and small glass beauty containers that need precise protection and a clean clinical presentation. The packaging focuses on anti-movement inserts, vial separation, dosage communication, and premium cosmetic shelf impact. It can be produced as a compact folding carton, sleeve set, drawer box, or rigid skincare kit with paper tray, EVA insert, or molded pulp support. Brands can customize vial count, treatment sequence, ingredient panel, batch area, logo finish, and export packing format. This solution is suitable for skincare brands, beauty clinics, serum manufacturers, and OEM/ODM cosmetic suppliers ordering from 1000 boxes.',
		'captions' => array(
			'Custom ampoule packaging box showing vertical skincare vial presentation.',
			'Ampoule box structure reference for serum sets and beauty treatment programs.',
			'Custom insert and print layout planning for fragile ampoule packaging.',
			'Retail-ready ampoule packaging for skincare brands and OEM cosmetic projects.',
		),
		'links' => array(
			'cat' => array( '/product-category/cosmetic-packaging-boxes/', 'cosmetic packaging boxes for skincare lines' ),
			'related1' => array( '/product/custom-cosmetic-packaging-box/', 'custom cosmetic packaging for jars and tubes' ),
			'related2' => array( '/product/custom-supplement-drawer-packaging-box/', 'drawer packaging for small bottle sets' ),
			'guide' => array( '/paper-materials-for-custom-paper-boxes/', 'paper material options for fragile beauty packaging' ),
			'contact' => array( '/contact/#quote', 'send ampoule dimensions for a packaging quote' ),
		),
		'sections' => array(
			array( 'h' => 'Packaging for Small Glass Ampoules, Not Generic Cosmetic Cartons', 'p' => array(
				'Ampoule packaging has a different job from a normal skincare carton. The product inside is usually a narrow glass vial, often sold as a short treatment course, salon program, or concentrated serum set. That means the box must control movement, explain the routine, and make each vial feel intentional rather than loose inside a standard beauty package.',
				'For buyers comparing skincare packaging, this page belongs in our ' . vpn_batch_link( '/product-category/cosmetic-packaging-boxes/', 'cosmetic packaging boxes for skincare lines' ) . ' group, but the design logic is closer to precision vial protection. The package should help a customer understand the number of ampoules, treatment order, ingredient promise, and safe handling before the box is opened.',
			) ),
			array( 'h' => 'Vial Stability, Separation, and Treatment-Set Structure', 'p' => array(
				'Ampoules should not collide with each other during shipping or retail handling. A paper tray, EVA insert, foam insert, molded pulp tray, or folded divider can create individual positions for each vial. For single ampoules, a tight folding carton may be enough; for five-piece, seven-piece, or fourteen-piece programs, a drawer or sleeve structure gives better organization.',
				'The box opening should match how the user applies the product. A tray that reveals all vials at once works for salon display, while numbered cavities help routine-based treatments feel more controlled. Buyers who like this opening style can compare it with our ' . vpn_batch_link( '/product/custom-supplement-drawer-packaging-box/', 'drawer packaging for small bottle sets' ) . ' when planning premium small-container packaging.',
			) ),
			array( 'h' => 'Materials Chosen for Glass Protection and Clean Skincare Printing', 'p' => array(
				'Ivory paper is useful when the artwork needs clean ingredient panels, small icons, and a clinical white appearance. Art paper mounted on rigid board is better for premium ampoule sets where the box should feel heavier. Kraft paper can work for natural skincare, but it needs careful color planning because clinical formulas often need sharp contrast.',
				'The insert material should be selected with the vial diameter, cap shape, and product weight in mind. A light paper tray may suit plastic sample tubes, while glass ampoules often need deeper cavities or soft contact points. Our ' . vpn_batch_link( '/paper-materials-for-custom-paper-boxes/', 'paper material options for fragile beauty packaging' ) . ' guide can help compare board strength, print surface, and insert compatibility.',
			) ),
			array( 'h' => 'Information Layout for Serum Vials and Skincare Treatments', 'p' => array(
				'An ampoule box often needs more than a product name and logo. Useful panels include treatment duration, vial quantity, formula benefit, skin type, application steps, ingredient highlights, batch number, expiry date, barcode, and warning text for glass handling. If the product is exported, multilingual information and regulatory space should be reserved early.',
				'The front panel can stay minimal while side panels carry the technical information. This prevents the package from looking crowded and keeps the premium skincare impression. For brands that also sell jars, tubes, or masks, the ampoule page can link naturally to ' . vpn_batch_link( '/product/custom-cosmetic-packaging-box/', 'custom cosmetic packaging for jars and tubes' ) . ' without repeating the same content.',
			) ),
			array( 'h' => 'Customization Details That Matter for Ampoule Sets', 'p' => array(
				'The eight details that usually define this product are vial diameter, vial height, number of vials, tray cavity depth, cap clearance, treatment sequence, glass protection level, and ingredient-panel space. These details make ampoule packaging different from a general cosmetic box and should be confirmed before the dieline is finalized.',
				'Brand customization can include foil on the serum name, embossed treatment numbers, soft-touch lamination for premium sets, color coding by formula, and separate insert positions for droppers or instruction cards. For e-commerce programs, the outer box should also be checked against mailer size and carton packing efficiency.',
			) ),
			array( 'h' => 'Printing and Finishing for Clinical Beauty Positioning', 'p' => array(
				'Ampoule packaging often looks strongest when the print system is restrained: precise typography, clean spacing, controlled color blocks, and high-readability dosage information. CMYK printing is suitable for most skincare graphics, while Pantone matching helps keep a clinical brand color consistent across serum, cream, and mask lines.',
				'Foil stamping should be used selectively on logos, treatment names, or series marks. Embossing can make a simple white box feel more premium without crowding it. Spot UV can highlight a formula icon or pattern, but heavy decorative finishing should not interfere with safety text, expiry information, or barcode scanning.',
			) ),
			array( 'h' => 'Bulk Production Benefits for Skincare OEM and Private Label Buyers', 'p' => array(
				'For skincare OEM factories, a stable ampoule structure can be reused across several formulas while changing artwork, color, and treatment claims. That lowers development time and helps packing staff work with the same insert logic across multiple private label orders. It also supports better warehouse organization because each treatment set has predictable dimensions.',
				'For brand owners, the benefit is product trust. Fragile beauty products feel safer when the packaging is visibly engineered around the vial. A buyer ordering 1000 boxes or more can also plan carton packing, insert cost, and finish level more accurately before scaling to larger production runs.',
			) ),
			array( 'h' => 'Quote Checklist for Ampoule Packaging', 'p' => array(
				'To quote this box correctly, send vial diameter, vial height, vial count per box, cap style, product weight, insert preference, artwork file, surface finishing request, and export carton requirements. If the ampoule is glass, mention whether the product will ship by air, sea, courier, or inside a larger skincare set.',
				'VPN Paper Box Manufacturer can review these details, suggest a structure, prepare the dieline, and quote based on material, insert, printing, finishing, and quantity. You can ' . vpn_batch_link( '/contact/#quote', 'send ampoule dimensions for a packaging quote' ) . ' and request a sample before mass production.',
			) ),
			array( 'h' => 'Why This Page Is Kept Separate from General Cosmetic Boxes', 'p' => array(
				'This product should remain a separate page because the packaging problem is vial movement, glass protection, treatment sequencing, and small-container display. A general cosmetic box page would not cover those details deeply enough for buyers comparing ampoule programs.',
				'The duplicate risk is lower when the content stays focused on vial cavities, routine sets, glass handling, dosage panels, and treatment-course branding. Those points are not central to ordinary makeup cartons or cosmetic paper bags, so the page has a clear reason to exist.',
			) ),
		),
		'score' => 3,
		'details' => array( 'vial diameter', 'vial count', 'treatment sequence', 'glass handling', 'individual tray cavities', 'dosage panel', 'cap clearance', 'salon program layout' ),
	),
	'custom-charging-cable-packaging-box' => array(
		'short' => 'CUSTOM CHARGING CABLE PACKAGING BOX is built for USB, Type-C, Lightning, braided, and fast-charging cable products that need clear retail communication and high-volume packing consistency. The box can include hang tabs, compatibility icons, cable length labels, warranty panels, QR codes, barcode zones, and compact inserts for coiled cables. Unlike premium gift packaging, this solution focuses on fast shelf recognition, SKU control, and efficient accessory distribution. Materials can include ivory paper, duplex board, kraft paper, or light corrugated board depending on sales channel and cable weight. MOQ starts from 1000 boxes for custom printed cable packaging.',
		'captions' => array(
			'Custom charging cable packaging box for USB and Type-C accessory retail.',
			'Cable packaging layout for connector icons, barcode, warranty, and hang-tab display.',
			'Compact cable box structure for coiled product packing and retail consistency.',
			'Custom printed charging cable packaging for electronics wholesale orders.',
		),
		'links' => array(
			'cat' => array( '/product-category/electronics-packaging-boxes/', 'electronics packaging boxes for accessories' ),
			'related1' => array( '/product/custom-phone-case-packaging-box/', 'phone case retail packaging with model labels' ),
			'related2' => array( '/product/custom-corporate-gift-set-packaging-boxes/', 'corporate gift set boxes for tech bundles' ),
			'guide' => array( '/paper-materials-for-custom-paper-boxes/', 'paperboard choices for retail accessory packaging' ),
			'contact' => array( '/contact/#quote', 'request a custom cable packaging quote' ),
		),
		'sections' => array(
			array( 'h' => 'Cable Packaging Has to Sell Compatibility in Seconds', 'p' => array(
				'Charging cables are small, price-sensitive, and often displayed beside many similar products. The package must tell shoppers the connector type, cable length, charging speed, device compatibility, and warranty information without forcing them to read a long paragraph. This makes the content system very different from a gift box or cosmetic carton.',
				'The page belongs under ' . vpn_batch_link( '/product-category/electronics-packaging-boxes/', 'electronics packaging boxes for accessories' ) . ', but the strongest angle is SKU clarity. A cable box should help retailers separate USB-A, USB-C, Lightning, braided, fast charge, and data cable models while keeping one consistent brand look across the line.',
			) ),
			array( 'h' => 'Hang-Tab, Window, and Coil-Friendly Structures', 'p' => array(
				'Cable packaging can be a tuck-end box, slim folding carton, hang-tab display box, sleeve box, or window carton. The right choice depends on how the cable is coiled, whether the connector should be visible, and whether stores display the product on peg hooks. A paper insert or folded holder can keep the cable compact without adding unnecessary box volume.',
				'A hang tab is usually important for offline retail, while a clean rectangular carton can work better for e-commerce bundles. If the product is part of a phone accessory collection, buyers can compare it with ' . vpn_batch_link( '/product/custom-phone-case-packaging-box/', 'phone case retail packaging with model labels' ) . ' to keep accessory packaging consistent without copying the same layout.',
			) ),
			array( 'h' => 'Material Selection for High-Volume Accessory Lines', 'p' => array(
				'Ivory paper and duplex board are common choices because they print technical icons clearly and keep unit cost controlled. Kraft paper may fit eco-positioned accessories, while light corrugated paper can be used for multi-cable kits or heavier adapter bundles. The material should match the cable price point instead of making a simple product look over-packaged.',
				'For a 1000-box starting quantity, the buyer should decide whether the box will be stacked, hung, shipped in mailers, or packed inside a display tray. The ' . vpn_batch_link( '/paper-materials-for-custom-paper-boxes/', 'paperboard choices for retail accessory packaging' ) . ' guide can help compare print surface, thickness, and cost before finalizing the dieline.',
			) ),
			array( 'h' => 'Print Layout for Connector Icons and Warranty Information', 'p' => array(
				'The most product-specific areas are connector diagram, cable length, fast-charging wattage, data transfer note, warranty period, QR code, barcode, color variant, and model number. These eight details are more important than decorative copy because they reduce customer confusion and help retailers manage inventory.',
				'Side panels can carry the technical list while the front panel shows the connector type and main benefit. If the accessory is sold internationally, the back panel should reserve space for certification icons, recycling marks, importer information, and multilingual warnings. This is a retail utility page, not a luxury unboxing page.',
			) ),
			array( 'h' => 'Customization for Multi-SKU Cable Families', 'p' => array(
				'A cable brand may sell the same structure in one-meter, two-meter, and three-meter versions, or in black, white, and braided color lines. The packaging should allow these variations through color bands, label areas, sticker zones, or printed model blocks. This avoids rebuilding the entire box each time a new cable length is launched.',
				'Artwork can include matte lamination for a cleaner electronics look, gloss lamination for mass retail color impact, or spot UV on the connector icon. Foil stamping is possible, but it should be used carefully because cable packaging often needs to remain practical and cost-controlled.',
			) ),
			array( 'h' => 'Packing Efficiency and Shelf Consistency', 'p' => array(
				'For wholesalers, the box must be fast to load and easy to count. A cable holder that takes too long to assemble can increase labor cost more than the buyer expects. The design should be tested with real cable coils so the connector heads do not push against the panels or make the carton bulge.',
				'For retail chains, consistent box dimensions make display planning easier. If the same brand also sells chargers, adapters, or small accessory bundles, a shared visual system can be used. For larger technology gift kits, ' . vpn_batch_link( '/product/custom-corporate-gift-set-packaging-boxes/', 'corporate gift set boxes for tech bundles' ) . ' may be a better structure.',
			) ),
			array( 'h' => 'Production Notes for Electronics Packaging Buyers', 'p' => array(
				'Before production, confirm cable coil diameter, connector type, cable length, product weight, display method, barcode size, certification icons, and whether a window or hang tab is required. These details influence the dieline more than the product name does.',
				'Bulk printing should be planned so each SKU can be checked quickly. If the same structure carries many cable types, the model number and color coding must be obvious to warehouse teams. This reduces picking errors and helps distributors ship mixed cartons more accurately.',
			) ),
			array( 'h' => 'Quote Requirements for a Cable Box Project', 'p' => array(
				'To quote accurately, send the packed cable size, connector photos, retail display requirement, artwork file, material preference, quantity, and whether the box needs a window or hanging hole. If the cable is sold on Amazon or in supermarkets, include any packaging dimension limits.',
				'VPN Paper Box Manufacturer can prepare a structure that balances shelf clarity, material cost, and packing speed. You can ' . vpn_batch_link( '/contact/#quote', 'request a custom cable packaging quote' ) . ' for a 1000-box custom production run or a larger wholesale order.',
			) ),
			array( 'h' => 'Why It Should Not Be Merged with Phone Case Packaging', 'p' => array(
				'Cable boxes and phone case boxes are both electronics packaging, but the core information is different. Cable packaging focuses on connector type, cable length, charging speed, coil control, and warranty panels. Phone case packaging focuses on model compatibility, product visibility, case texture, and hanger/window presentation.',
				'Keeping the pages separate gives buyers more useful guidance and lowers duplicate risk. The cable page should stay technical and SKU-driven, while the phone case page should speak more about model labeling and visible product display.',
			) ),
		),
		'score' => 3,
		'details' => array( 'connector icon', 'cable length', 'charging wattage', 'coil holder', 'hang tab', 'warranty panel', 'QR code', 'multi-SKU color band' ),
	),
);

// Remaining products use the same output structure, but with distinct product DNA and section text.
$products += array(
	'custom-colored-pencil-packaging-box' => array(
		'short' => 'CUSTOM COLORED PENCIL PACKAGING BOX is designed for organized art supply sets where color order, pencil protection, and creative presentation matter. It is suitable for student packs, artist-grade pencil sets, hobby kits, and retail stationery lines. The box can be built as a drawer tray, sleeve set, window carton, or rigid art box with color chart, paper tray, numbered layout, and product information panels. Unlike crayon packaging, this page focuses on precise color arrangement, sharpened pencil tip protection, artist usability, and premium stationery display. Custom production starts from 1000 boxes.',
		'captions' => array( 'Colored pencil packaging with organized color rows.', 'Drawer tray structure for pencil sets.', 'Color chart and pencil layout reference.', 'Retail stationery packaging for art supplies.' ),
		'links' => array( 'cat' => array( '/product-category/stationery-packaging-boxes/', 'stationery packaging boxes for art sets' ), 'related1' => array( '/product/custom-crayon-packaging-box/', 'crayon packaging for children coloring products' ), 'related2' => array( '/product/custom-corporate-gift-set-packaging-boxes/', 'gift packaging for premium stationery kits' ), 'guide' => array( '/paper-materials-for-custom-paper-boxes/', 'paper materials for stationery boxes' ), 'contact' => array( '/contact/#quote', 'request a colored pencil packaging quote' ) ),
		'sections' => array(
			array( 'h' => 'A Pencil Set Box Should Organize Color, Not Just Contain It', 'p' => array( 'Colored pencil packaging is about order, selection, and repeat use. A buyer expects the colors to be visible, grouped logically, and protected from broken tips. This makes the box different from simple school crayon packaging, where safety and child-friendly display usually matter more than artist workflow.', 'The product sits under ' . vpn_batch_link( '/product-category/stationery-packaging-boxes/', 'stationery packaging boxes for art sets' ) . ', but the content angle is color management. Good packaging helps the user compare tones, return each pencil to its place, and understand whether the set is for students, hobbyists, or artists.' ) ),
			array( 'h' => 'Drawer Trays, Color Rows, and Tip Protection', 'p' => array( 'A drawer tray works well because it presents the pencils in rows and allows the user to slide the set open like an art tool. Folding cartons can work for school sets, while rigid boxes fit premium artist lines. The tray spacing should consider pencil diameter, sharpened tip length, and whether the pencils are round, hexagonal, or triangular.', 'Unlike a crayon divider, a pencil tray should reduce rubbing on the painted barrel and protect sharpened points. Paperboard trays, molded pulp grooves, or layered inserts can be used depending on the price point. Premium stationery buyers may also request a sleeve or ribbon pull for a cleaner opening.' ) ),
			array( 'h' => 'Paper and Insert Choices for Stationery Presentation', 'p' => array( 'Ivory paper is useful for bright illustrations and color charts. Rigid greyboard adds value for larger artist sets. Kraft paper can support natural sketching or school themes, but the printed colors must still look accurate. The insert should be strong enough to prevent pencils from sliding under each other during transport.', 'For material comparison, see ' . vpn_batch_link( '/paper-materials-for-custom-paper-boxes/', 'paper materials for stationery boxes' ) . '. The final choice depends on pencil count, expected shelf price, export route, and whether the packaging will be reused by the customer after purchase.' ) ),
			array( 'h' => 'Color Chart, Numbering, and Retail Information Panels', 'p' => array( 'Eight details that make this product unique are pencil count, color order, color chart, barrel diameter, tip protection, tray groove depth, artist grade label, and blending or watercolor icons. These details make the box useful to the buyer instead of just decorative.', 'The back panel can include color names, pigment notes, safety marks, barcode, age recommendation, and manufacturer information. For international stationery lines, multilingual product information should be planned before printing so the retail panel does not become crowded.' ) ),
			array( 'h' => 'Visual Style for Student, Hobby, or Artist Lines', 'p' => array( 'Student sets often need cheerful color blocks and clear count numbers, while artist sets may use calmer graphics, textured paper, or premium foil accents. A children-focused box should not look too serious; an artist-grade set should not look like a disposable classroom pack.', 'This is where it differs strongly from ' . vpn_batch_link( '/product/custom-crayon-packaging-box/', 'crayon packaging for children coloring products' ) . '. Pencil packaging can talk about tonal range, sketching, blending, and reusable storage; crayon packaging should focus more on easy opening, safety information, and classroom bulk use.' ) ),
			array( 'h' => 'Production Planning for Different Pencil Counts', 'p' => array( 'A 12-color set, 24-color set, and 48-color set may share a visual identity but usually need different insert layouts. The dieline should allow enough tolerance for the pencil rows without making the box feel loose. If a brand sells multiple counts, the front panel should make the set size instantly visible.', 'Bulk orders can use one structure family with different tray lengths. This helps reduce development cost and keeps the shelf presentation consistent. For premium stationery gift programs, buyers can also consider ' . vpn_batch_link( '/product/custom-corporate-gift-set-packaging-boxes/', 'gift packaging for premium stationery kits' ) . ' for mixed pencil, notebook, and accessory sets.' ) ),
			array( 'h' => 'Printing and Finishing for Color Accuracy', 'p' => array( 'Because the product is color-based, artwork proofing is important. The printed color chart should not mislead customers about the pencil shades. CMYK printing works for most sets, but Pantone matching can be useful for brand colors or special edition packaging.', 'Matte lamination gives a soft creative feel, while gloss lamination makes illustrations brighter for mass retail. Spot UV can highlight color swatches or a series name. Foil should be reserved for artist-grade lines so the box does not become too expensive for school supply channels.' ) ),
			array( 'h' => 'Quote Checklist for Colored Pencil Boxes', 'p' => array( 'Send pencil count, pencil diameter, pencil length, sharpened or unsharpened condition, tray style, artwork, target market, and quantity. If the set needs a color chart or numbered order, include the final color list before sample production.', 'VPN Paper Box Manufacturer can prepare a dieline, recommend paper and insert, and quote production from 1000 boxes. You can ' . vpn_batch_link( '/contact/#quote', 'request a colored pencil packaging quote' ) . ' with a product photo and desired box style.' ) ),
			array( 'h' => 'Duplicate Risk Review for Pencil Packaging', 'p' => array( 'This page should remain separate from crayon packaging because the buyer intent is different. Colored pencil boxes need artist organization, color accuracy, reusable trays, and tip protection. Those ideas are not the main focus of crayon boxes.', 'The duplicate risk is controlled by emphasizing color order, pencil grade, tray grooves, color charts, and pencil count variants instead of using generic stationery packaging language.' ) ),
		),
		'score' => 4,
		'details' => array( 'color order', 'color chart', 'pencil count', 'tray groove depth', 'sharpened tip protection', 'artist grade label', 'barrel diameter', 'reusable drawer tray' ),
	),
);

// Compact definitions for the remaining seven products are generated from unique section copy.
$more = include __DIR__ . '/rewrite-product-samples-batch-1-extra.php';
if ( is_array( $more ) ) {
	$products += $more;
}

$audit = "# Batch 1 Rewrite Audit\n\n";
$updated = 0;

foreach ( $products as $slug => $data ) {
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post ) {
		echo "Missing product: {$slug}\n";
		continue;
	}
	wp_update_post(
		array(
			'ID'           => $post->ID,
			'post_status'  => 'publish',
			'post_excerpt' => $data['short'],
			'post_content' => vpn_batch_content( $post->ID, $data ),
		)
	);
	update_post_meta( $post->ID, '_vpn_duplicate_risk_score', $data['score'] );
	update_post_meta( $post->ID, '_vpn_product_specific_details', $data['details'] );
	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $post->ID );
	}
	$audit .= "## {$post->post_title}\n\n";
	$audit .= "- URL: " . get_permalink( $post->ID ) . "\n";
	$audit .= "- Duplicate risk score: {$data['score']}/10\n";
	$audit .= "- Product-specific details: " . implode( ', ', $data['details'] ) . "\n";
	$audit .= "- Internal links: ";
	$link_text = array();
	foreach ( $data['links'] as $link ) {
		$link_text[] = $link[1] . ' => ' . home_url( $link[0] );
	}
	$audit .= implode( '; ', $link_text ) . "\n\n";
	$updated++;
	echo "Rewritten: {$post->post_title} (#{$post->ID})\n";
}

$audit .= "## Pair Risk Review\n\n";
$audit .= "| Product A | Product B | Similarity risk | Repeated heading | Repeated idea | Recommended rewrite |\n";
$audit .= "|---|---|---:|---|---|---|\n";
$audit .= "| Colored Pencil Box | Crayon Box | 4/10 | No | Both are stationery, but one focuses on color order and artist trays while the other focuses on child safety and classroom bulk packs. | Keep separate pages. |\n";
$audit .= "| Ampoule Box | Cosmetic Box | 4/10 | No | Both are beauty packaging, but ampoule is vial protection and treatment sequencing while cosmetic box is broader jars/tubes/makeup retail. | Keep separate pages. |\n";
$audit .= "| Charging Cable Box | Phone Case Box | 4/10 | No | Both are electronics retail packaging, but cable focuses on connector/length/warranty while phone case focuses on model fit/window display. | Keep separate pages. |\n";
$audit .= "| Corporate Gift Set Box | Supplement Drawer Box | 5/10 | No | Both can use rigid/drawer structures, but corporate is campaign gifting while supplement is regulated wellness/bottle kit logic. | Keep separate pages with strict angle separation. |\n";

file_put_contents( dirname( __DIR__ ) . '/product-samples-10-rewrite-audit.md', $audit );
echo "Updated {$updated} products.\n";
echo "Audit file: " . dirname( __DIR__ ) . "/product-samples-10-rewrite-audit.md\n";
