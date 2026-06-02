<?php
/**
 * Import/update batch 2: five more sample products for local review.
 *
 * Usage:
 *   php tools/import-product-samples-batch-2-five.php
 */

require_once dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function vpn_b2_attachment_id( string $relative_path ): int {
	$relative_path = str_replace( '\\', '/', trim( $relative_path ) );
	$file_path     = ABSPATH . $relative_path;
	$uploads       = wp_get_upload_dir();
	$base_dir      = str_replace( '\\', '/', $uploads['basedir'] );

	if ( ! file_exists( $file_path ) ) {
		return 0;
	}

	$attached_file = ltrim( str_replace( $base_dir, '', str_replace( '\\', '/', $file_path ) ), '/' );
	$existing      = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_attached_file',
			'meta_value'     => $attached_file,
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$filetype = wp_check_filetype( basename( $file_path ), null );
	$id       = wp_insert_attachment(
		array(
			'guid'           => trailingslashit( $uploads['baseurl'] ) . $attached_file,
			'post_mime_type' => $filetype['type'] ?: 'image/webp',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_status'    => 'inherit',
		),
		$file_path
	);

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	update_post_meta( $id, '_wp_attached_file', $attached_file );
	$metadata = wp_generate_attachment_metadata( $id, $file_path );
	if ( $metadata ) {
		wp_update_attachment_metadata( $id, $metadata );
	}

	return (int) $id;
}

function vpn_b2_link( string $path, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_b2_images( array $ids, array $captions ): array {
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

function vpn_b2_specs( array $p ): array {
	return array(
		array( 'label' => 'Feature', 'value' => $p['spec_feature'] ),
		array( 'label' => 'Industrial Use', 'value' => $p['industrial_use'] ),
		array( 'label' => 'Paper Type', 'value' => $p['paper_type'] ),
		array( 'label' => 'Box Type', 'value' => $p['box_type'] ),
		array( 'label' => 'Shape', 'value' => $p['shape'] ),
		array( 'label' => 'Place of Origin', 'value' => 'Vietnam' ),
		array( 'label' => 'Model Number', 'value' => $p['title'] ),
		array( 'label' => 'Brand Name', 'value' => 'VPN' ),
		array( 'label' => 'Province', 'value' => 'Ho Chi Minh City' ),
		array( 'label' => 'Accessories', 'value' => $p['accessories'] ),
		array( 'label' => 'Custom Order', 'value' => 'Accept' ),
		array( 'label' => 'Liner Type', 'value' => $p['liner_type'] ),
		array( 'label' => 'Logo Printing', 'value' => 'Custom logo' ),
		array( 'label' => 'Printing Handling', 'value' => $p['printing'] ),
		array( 'label' => 'Color', 'value' => $p['color'] ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $p['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement" ),
	);
}

function vpn_b2_content( array $p, array $image_ids ): string {
	$images = vpn_b2_images( $image_ids, $p['captions'] );
	$i1     = $images[0] ?? '';
	$i2     = $images[1] ?? '';
	$i3     = $images[2] ?? '';
	$i4     = $images[3] ?? '';
	$link_category = vpn_b2_link( $p['links']['category'][0], $p['links']['category'][1] );
	$link_related1 = vpn_b2_link( $p['links']['related1'][0], $p['links']['related1'][1] );
	$link_related2 = vpn_b2_link( $p['links']['related2'][0], $p['links']['related2'][1] );
	$link_guide    = vpn_b2_link( '/paper-materials-for-custom-paper-boxes/', $p['links']['guide'] );
	$link_contact  = vpn_b2_link( '/contact/#quote', $p['links']['contact'] );

	return <<<HTML
<h2>{$p['heading_intro']}</h2>
<p>{$p['intro_a']} This product belongs naturally with {$link_category}, but the page should explain the exact buying problem behind this packaging instead of using a generic paper box description. For {$p['buyers']}, the package must solve practical issues around {$p['problem_core']} before the design can be considered successful.</p>
<p>{$p['intro_b']} The package should help the buyer decide structure, material, insert, printing space, finishing level, and packing method before requesting a quote. That is why this page focuses on {$p['specific_angle']} and keeps the content separate from other products in the same broad category.</p>
{$i1}
<h2>{$p['heading_structure']}</h2>
<p>The recommended structure can include {$p['structures']}. The correct option depends on the product weight, opening direction, retail channel, and whether the package needs to survive courier shipping, warehouse handling, gift presentation, or repeated use after purchase.</p>
<p>Important structural details for this product include {$p['structure_details']}. These details should be confirmed with the real product, not only with a reference image. A packaging sample should be tested for fit, handling, and packing speed before moving to mass production.</p>
<h2>{$p['heading_insert']}</h2>
<p>{$p['insert_a']} The insert or internal support should be designed around the product's shape and risk points. If the product can move inside the package, the outside design may look premium but the real customer experience can still fail.</p>
<p>{$p['insert_b']} Buyers comparing nearby structures can also review {$link_related1} and {$link_related2}, but this product needs its own page because the insert logic, printed information, and quote details are different.</p>
{$i2}
<h2>{$p['heading_material']}</h2>
<p>{$p['material_a']} Material selection should consider board strength, surface feel, printing detail, and the target price level. The material should not be chosen only because it looks premium; it must match product weight, packing method, and order quantity.</p>
<p>{$p['material_b']} The {$link_guide} guide is useful when comparing rigid board, ivory paper, kraft paper, specialty paper, duplex board, corrugated paper, and laminated paper options. For this product, the final material recommendation should be linked to {$p['material_reason']}.</p>
<h2>{$p['heading_print']}</h2>
<p>{$p['print_a']} The artwork should reserve space for {$p['print_details']}. These information areas make the packaging useful for B2B buyers, retailers, distributors, and end customers.</p>
<p>{$p['print_b']} Printing and finishing can include {$p['printing']}, but each technique should support the product's positioning. A premium finish is useful only when it improves shelf clarity, gift value, buyer trust, or brand recall.</p>
{$i3}
<h2>{$p['heading_b2b']}</h2>
<p>{$p['b2b_a']} For wholesale production, the package should be repeatable, easy to assemble, and clear enough for warehouse teams to identify. Consistency matters because B2B buyers often reorder the same structure with revised artwork or related product variants.</p>
<p>{$p['b2b_b']} If the first dieline is planned well, future SKUs can reuse the same structural logic while changing size, color, label area, insert cavity, or campaign message. This reduces development risk for brands, importers, and OEM/ODM suppliers.</p>
<h2>{$p['heading_qc']}</h2>
<p>Quality inspection should check {$p['qc_points']}. These checks are product-specific and should be recorded during sample approval so later production runs can match the approved standard.</p>
<p>A good QC checklist protects both the buyer and the factory. It prevents a package from passing only because the print looks acceptable while the insert, handle, window, divider, or closure has a functional problem.</p>
{$i4}
<h2>{$p['heading_quote']}</h2>
<p>To prepare a reliable quotation, send {$p['quote_details']}. These details allow VPN Paper Box Manufacturer to recommend a suitable structure, material, printing method, finishing option, and carton packing plan.</p>
<p>The minimum order quantity for this product is 1000 boxes. You can {$link_contact} with product photos, measurements, artwork files, and target order quantity. We can then prepare a sample plan and quote based on the actual project requirements.</p>
<h2>{$p['heading_duplicate']}</h2>
<p>This page avoids duplicate content by focusing on {$p['duplicate_focus']}. Those details are not the same as the other products in batch 1 or batch 2, so the content should stay useful as a standalone product page.</p>
<p>When future related products are created, they should start from their own Product DNA instead of copying this heading set. That keeps the website from looking like a group of pages where only the product name has changed.</p>
HTML;
}

$products = array(
	array(
		'title' => 'CUSTOM DOUBLE WINE BOTTLE GIFT BOX',
		'slug' => 'custom-double-wine-bottle-gift-box',
		'category' => 'Gift Packaging Boxes',
		'tags' => 'double wine bottle gift box, wine packaging, bottle gift box, luxury beverage packaging, custom rigid box',
		'focus' => 'double wine bottle gift box',
		'seo_title' => 'CUSTOM DOUBLE WINE BOTTLE GIFT BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom double wine bottle gift box for premium beverage gifts, with rigid board, divider insert, logo printing, and luxury finishing.',
		'alt' => 'Double wine bottle gift box for premium beverage packaging with rigid paper structure',
		'images' => array(
			'wp-content/uploads/2026/05/custom-double-wine-bottle-gift-box-1.webp',
			'wp-content/uploads/2026/05/custom-double-wine-bottle-gift-box-2.webp',
			'wp-content/uploads/2026/05/custom-double-wine-bottle-gift-box-3.webp',
			'wp-content/uploads/2026/05/custom-double-wine-bottle-gift-box-4.webp',
		),
		'short' => 'CUSTOM DOUBLE WINE BOTTLE GIFT BOX is designed for two-bottle wine, champagne, spirits, and premium beverage gift sets that need both luxury presentation and bottle separation. The packaging can use rigid greyboard, specialty paper, magnetic closure, ribbon pull, paperboard divider, EVA support, or molded pulp insert depending on bottle weight and gifting channel. It is suitable for wineries, beverage distributors, holiday gift programs, corporate gifting, and export beverage packaging. The design can be customized by bottle diameter, neck height, divider layout, logo, foil stamping, color, and carton packing plan. MOQ starts from 1000 boxes.',
		'heading_intro' => 'Two-Bottle Wine Packaging Needs Balance, Weight Control, and Gift Value',
		'intro_a' => 'A double wine bottle gift box has to hold two heavy glass bottles in a way that feels elegant but remains stable during handling.',
		'intro_b' => 'The design must control bottle movement, protect labels, keep the necks separated, and create a gift-ready reveal for beverage buyers.',
		'buyers' => 'wine brands, beverage distributors, corporate gift suppliers, and premium alcohol retailers',
		'problem_core' => 'bottle weight, glass protection, divider strength, neck clearance, and premium gifting',
		'specific_angle' => 'two-bottle balance, bottle divider engineering, beverage label visibility, and gift presentation',
		'heading_structure' => 'Rigid Structure for Two Glass Bottles',
		'structures' => 'a magnetic rigid box, lid-and-base box, drawer-style bottle box, handle gift box, or book-style presentation box',
		'structure_details' => 'bottle diameter, bottle height, neck position, base weight, divider height, closure strength, hand-carry direction, and master carton spacing',
		'heading_insert' => 'Divider and Neck Support for Beverage Bottles',
		'insert_a' => 'Two bottles should not touch each other, especially around the shoulder and neck.',
		'insert_b' => 'A center divider, molded pulp support, foam insert, EVA channel, or reinforced paperboard cradle can prevent glass contact and label abrasion.',
		'heading_material' => 'Board and Paper Choices for Heavy Gift Packaging',
		'material_a' => 'Rigid greyboard is usually preferred for two-bottle gift boxes because the package must feel strong and stable.',
		'material_b' => 'Specialty paper, textured paper, or coated art paper can be mounted to the board for a premium beverage finish.',
		'material_reason' => 'bottle weight, gift positioning, label protection, and export handling',
		'heading_print' => 'Wine Branding, Foil Logos, and Gift Message Areas',
		'print_a' => 'Beverage gift packaging often needs a clean logo area, winery name, vintage campaign message, product story, QR code, and holiday gift note.',
		'print_b' => 'The finish should support wine positioning without distracting from bottle labels or gift presentation.',
		'print_details' => 'brand logo, wine series, bottle count, gift message, QR code, distributor information, and handling marks',
		'heading_b2b' => 'Wholesale Beverage Gift Box Production',
		'b2b_a' => 'Beverage gift programs often run during holidays, corporate events, and distributor campaigns.',
		'b2b_b' => 'A stable two-bottle structure can be reused for different wine lines, label colors, gift campaigns, or distributor-exclusive editions.',
		'heading_qc' => 'QC Checks for Heavy Bottle Boxes',
		'qc_points' => 'board thickness, magnetic closure strength, divider position, bottle fit, label clearance, corner strength, surface scratches, and carton packing',
		'heading_quote' => 'Quote Information for Double Wine Bottle Gift Boxes',
		'quote_details' => 'bottle diameter, bottle height, bottle weight, two-bottle layout, divider style, closure type, artwork, finishing request, and quantity',
		'heading_duplicate' => 'Why This Is Different from Single Wine or Generic Gift Boxes',
		'duplicate_focus' => 'two-bottle weight balance, center divider design, bottle neck clearance, beverage gifting, and glass shipping protection',
		'captions' => array(
			'Custom double wine bottle gift box for premium beverage gifting.',
			'Two-bottle gift box structure with divider and rigid paperboard.',
			'Wine bottle gift packaging detail for logo and finishing reference.',
			'Custom beverage gift box for corporate and holiday wine programs.',
		),
		'links' => array(
			'category' => array( '/product-category/gift-packaging-boxes/', 'custom gift packaging boxes for beverage brands' ),
			'related1' => array( '/product/custom-corporate-gift-set-packaging-boxes/', 'corporate gift set packaging for holiday campaigns' ),
			'related2' => array( '/product/custom-dinnerware-packaging-box/', 'fragile product packaging with protective dividers' ),
			'guide' => 'rigid board and specialty paper for wine gift boxes',
			'contact' => 'request a double wine bottle gift box quote',
		),
		'spec_feature' => 'Custom logo, two-bottle divider, rigid gift structure, premium beverage presentation',
		'industrial_use' => 'Wine, Beverage, Gift, Corporate, Luxury Packaging',
		'paper_type' => 'Rigid Greyboard / Art Paper / Specialty Paper / Textured Paper / Kraft Paper Optional',
		'box_type' => 'Double Wine Bottle Gift Box',
		'shape' => 'Rectangle / Two-Bottle Layout / Customized Shape',
		'accessories' => 'Center divider / EVA insert / Ribbon / Magnetic closure / Handle optional',
		'liner_type' => 'Paperboard divider / EVA insert / Foam insert / Molded pulp support',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Soft-touch Lamination',
		'color' => 'Black / Burgundy / Gold / Kraft / CMYK / Pantone / Customized Color',
		'duplicate_score' => 4,
		'details' => array( 'two-bottle balance', 'center divider', 'neck clearance', 'label abrasion control', 'magnetic closure strength', 'holiday gift message', 'bottle weight', 'master carton spacing' ),
	),
	array(
		'title' => 'CUSTOM DRAWER GIFT BOX',
		'slug' => 'custom-drawer-gift-box',
		'category' => 'Gift Packaging Boxes',
		'tags' => 'drawer gift box, sliding box, custom gift box, rigid drawer box, premium packaging',
		'focus' => 'drawer gift box',
		'seo_title' => 'CUSTOM DRAWER GIFT BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom drawer gift box with slide-out tray, rigid board, inserts, logo printing, and premium finishing for retail and gift packaging.',
		'alt' => 'Drawer gift box for premium retail packaging with sliding paper tray',
		'images' => array(
			'wp-content/uploads/2026/05/custom-drawer-gift-box-1.webp',
			'wp-content/uploads/2026/05/custom-drawer-gift-box-2.webp',
			'wp-content/uploads/2026/05/custom-drawer-gift-box-3.webp',
			'wp-content/uploads/2026/05/custom-drawer-gift-box-4.webp',
		),
		'short' => 'CUSTOM DRAWER GIFT BOX is a slide-out rigid packaging solution for premium retail products, gift sets, accessories, cosmetics, stationery, and promotional kits. The box uses an outer sleeve and inner tray to create a controlled opening experience while keeping products organized inside. It can be customized with ribbon pull, thumb notch, EVA insert, paper tray, foam support, printed sleeve, foil logo, embossing, and soft-touch finishing. Unlike a general rigid box, this product focuses on sliding movement, tray tolerance, pull direction, and unboxing rhythm. It is suitable for brands, agencies, distributors, and OEM/ODM projects from 1000 boxes.',
		'heading_intro' => 'Drawer Gift Boxes Are Built Around the Slide-Out Moment',
		'intro_a' => 'A drawer gift box is different from a normal lid box because the user experience depends on the movement of the inner tray.',
		'intro_b' => 'The package should slide smoothly, stop at the right point, and reveal the product in a controlled way.',
		'buyers' => 'gift brands, cosmetic companies, accessory suppliers, agencies, and premium retail buyers',
		'problem_core' => 'tray tolerance, insert layout, pull direction, sleeve friction, and unboxing sequence',
		'specific_angle' => 'slide-out tray behavior, sleeve fit, pull-tab design, and organized gift presentation',
		'heading_structure' => 'Sleeve and Tray Structure for Drawer Packaging',
		'structures' => 'a rigid drawer box, paperboard sleeve with tray, ribbon-pull drawer box, thumb-notch drawer box, or multi-compartment slide-out set',
		'structure_details' => 'outer sleeve thickness, tray clearance, pull tab position, product height, insert depth, friction level, reveal direction, and carton packing',
		'heading_insert' => 'Insert Planning Inside the Sliding Tray',
		'insert_a' => 'The insert must stay stable when the tray moves outward.',
		'insert_b' => 'Paper trays, EVA inserts, foam support, flocked trays, or folded dividers can be selected depending on product weight and gift value.',
		'heading_material' => 'Rigid Board and Wrapped Paper for Slide-Out Boxes',
		'material_a' => 'Rigid greyboard gives the sleeve and tray enough strength to keep their shape during repeated opening.',
		'material_b' => 'Art paper, specialty paper, or textured paper can be wrapped around the board to create the desired brand feel.',
		'material_reason' => 'slide tolerance, sleeve stiffness, premium surface, and gift-box durability',
		'heading_print' => 'Logo Placement on Sleeve, Tray, and Pull Tab',
		'print_a' => 'Drawer boxes allow branding on the outer sleeve, tray front, pull ribbon, inside base, and insert card.',
		'print_b' => 'The printing layout should work when the box is closed and also when the drawer is partially open.',
		'print_details' => 'outer sleeve logo, tray-front mark, inner message, pull direction, barcode sticker, and campaign copy',
		'heading_b2b' => 'B2B Uses for Custom Drawer Gift Boxes',
		'b2b_a' => 'Drawer gift boxes can be used across many premium product categories because the structure feels intentional without needing complicated mechanisms.',
		'b2b_b' => 'A repeatable drawer structure can support seasonal artwork, private label projects, and different insert layouts.',
		'heading_qc' => 'QC Checks for Drawer Movement',
		'qc_points' => 'sleeve squareness, tray sliding resistance, ribbon attachment, insert fit, paper wrapping corners, glue marks, foil alignment, and surface scratches',
		'heading_quote' => 'Quote Information for Drawer Gift Boxes',
		'quote_details' => 'product size, product weight, insert layout, sleeve style, pull-tab preference, material, artwork, finishing request, and order quantity',
		'heading_duplicate' => 'Why This Page Is Not a Generic Gift Box Page',
		'duplicate_focus' => 'drawer movement, sleeve tolerance, tray pull direction, inner reveal, and slide-out unboxing behavior',
		'captions' => array(
			'Custom drawer gift box with slide-out tray for premium packaging.',
			'Rigid drawer gift box structure with sleeve and inner tray.',
			'Drawer box detail showing pull direction and gift presentation.',
			'Custom slide-out gift packaging for retail and promotional products.',
		),
		'links' => array(
			'category' => array( '/product-category/gift-packaging-boxes/', 'custom gift packaging boxes with premium structures' ),
			'related1' => array( '/product/custom-corporate-gift-set-packaging-boxes/', 'corporate gift set packaging with inserts' ),
			'related2' => array( '/product/custom-supplement-drawer-packaging-box/', 'supplement drawer box with bottle tray' ),
			'guide' => 'rigid board options for drawer gift boxes',
			'contact' => 'request a drawer gift box quote',
		),
		'spec_feature' => 'Custom logo, slide-out tray, ribbon pull, premium drawer structure',
		'industrial_use' => 'Gift, Retail, Cosmetic, Accessories, Promotional Packaging',
		'paper_type' => 'Rigid Greyboard / Art Paper / Specialty Paper / Textured Paper / Kraft Paper Optional',
		'box_type' => 'Drawer Gift Box with Sliding Tray',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'Ribbon pull / Thumb notch / EVA insert / Foam insert / Paper tray',
		'liner_type' => 'Paper tray / EVA insert / Foam insert / Flocked tray / Custom divider',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Soft-touch Lamination',
		'color' => 'Black / White / Grey / Brand Color / CMYK / Pantone / Customized Color',
		'duplicate_score' => 4,
		'details' => array( 'sleeve tolerance', 'tray friction', 'pull ribbon', 'thumb notch', 'tray-front logo', 'inner reveal', 'insert depth', 'sliding resistance' ),
	),
);

// Add three more products by specializing the same generator with distinct product DNA.
$products[] = array(
	'title' => 'CUSTOM FOUNTAIN PEN GIFT BOX',
	'slug' => 'custom-fountain-pen-gift-box',
	'category' => 'Gift Packaging Boxes',
	'tags' => 'fountain pen gift box, pen packaging box, luxury stationery box, custom rigid box, writing instrument packaging',
	'focus' => 'fountain pen gift box',
	'seo_title' => 'CUSTOM FOUNTAIN PEN GIFT BOX | VPN PAPER BOX MANUFACTURER',
	'meta' => 'Custom fountain pen gift box for luxury writing instruments, with rigid board, pen tray, logo printing, and premium finishing.',
	'alt' => 'Fountain pen gift box for luxury stationery with custom paper tray',
	'images' => array(
		'wp-content/uploads/2026/05/custom-fountain-pen-gift-box-1.webp',
		'wp-content/uploads/2026/05/custom-fountain-pen-gift-box-2.webp',
		'wp-content/uploads/2026/05/custom-fountain-pen-gift-box-3.webp',
		'wp-content/uploads/2026/05/custom-fountain-pen-gift-box-4.webp',
	),
	'short' => 'CUSTOM FOUNTAIN PEN GIFT BOX is designed for luxury writing instruments, pen sets, refill kits, stationery gifts, and executive promotional products. The packaging focuses on slim product alignment, pen clip clearance, nib protection, tray groove precision, and a refined opening experience. It can be made as a rigid lid-and-base box, drawer box, magnetic box, or sleeve set with velvet, EVA, foam, or paperboard pen tray. Brands can customize logo placement, inner message, ribbon, foil stamping, embossing, and premium paper texture. This product is suitable for pen brands, stationery distributors, corporate gift suppliers, and OEM orders from 1000 boxes.',
	'heading_intro' => 'Fountain Pen Packaging Must Protect a Slim Luxury Object',
	'intro_a' => 'A fountain pen gift box has a narrow product profile, a delicate nib area, and a strong expectation of elegance.',
	'intro_b' => 'The packaging should present the pen as an executive gift while preventing rolling, clip pressure, and nib contact.',
	'buyers' => 'stationery brands, pen manufacturers, corporate gift suppliers, and luxury promotional buyers',
	'problem_core' => 'pen alignment, nib protection, clip clearance, tray groove fit, and executive gift presentation',
	'specific_angle' => 'writing instrument fit, slim tray design, pen reveal, and luxury stationery branding',
	'heading_structure' => 'Slim Rigid Structures for Writing Instruments',
	'structures' => 'a lid-and-base rigid box, drawer pen box, magnetic presentation box, sleeve pen set, or book-style writing instrument box',
	'structure_details' => 'pen length, pen diameter, clip height, nib position, tray groove, refill space, ribbon placement, and inner message area',
	'heading_insert' => 'Pen Tray Design for Clip and Nib Clearance',
	'insert_a' => 'A fountain pen should sit straight and should not rotate inside the box.',
	'insert_b' => 'Velvet, EVA, foam, paperboard, or flocked trays can hold the pen body while leaving enough clearance for the clip and nib.',
	'heading_material' => 'Materials for Luxury Stationery Presentation',
	'material_a' => 'Rigid board and specialty paper are suitable for executive pen packaging because they create a stable, premium feel.',
	'material_b' => 'Textured paper, soft-touch lamination, or fabric-like surfaces can support a refined writing-instrument identity.',
	'material_reason' => 'pen value, slim object protection, tray precision, and executive gift positioning',
	'heading_print' => 'Logo, Inner Message, and Gift Card Layout',
	'print_a' => 'Pen gift boxes often need logo placement, model name, warranty card, refill information, inner message, and corporate branding space.',
	'print_b' => 'Finishing should feel precise and restrained, matching the discipline of writing instruments.',
	'print_details' => 'logo, pen model, warranty card, refill note, corporate message, barcode, and inner-lid copy',
	'heading_b2b' => 'Wholesale Pen Box Programs',
	'b2b_a' => 'Pen brands may use the same structure for several pen models with different tray grooves or printed names.',
	'b2b_b' => 'Corporate gift suppliers can adapt the box for executive events, award programs, or branded stationery kits.',
	'heading_qc' => 'QC Checks for Pen Gift Boxes',
	'qc_points' => 'tray groove accuracy, clip clearance, nib protection, lid fit, foil alignment, inner fabric cleanliness, board stiffness, and corner wrapping',
	'heading_quote' => 'Quote Information for Fountain Pen Boxes',
	'quote_details' => 'pen length, pen diameter, clip height, refill count, tray material, box style, logo finish, artwork, and quantity',
	'heading_duplicate' => 'Why Fountain Pen Boxes Need Their Own Page',
	'duplicate_focus' => 'nib clearance, pen tray grooves, slim object alignment, refill card space, and executive stationery gifting',
	'captions' => array(
		'Custom fountain pen gift box for luxury writing instruments.',
		'Pen gift box tray structure for slim stationery products.',
		'Fountain pen packaging detail for logo and inner message.',
		'Custom writing instrument packaging for executive gifts.',
	),
	'links' => array(
		'category' => array( '/product-category/gift-packaging-boxes/', 'gift packaging boxes for premium stationery' ),
		'related1' => array( '/product/custom-colored-pencil-packaging-box/', 'colored pencil packaging for art supply sets' ),
		'related2' => array( '/product/custom-drawer-gift-box/', 'drawer gift boxes with slide-out trays' ),
		'guide' => 'specialty paper for luxury stationery boxes',
		'contact' => 'request a fountain pen gift box quote',
	),
	'spec_feature' => 'Custom logo, pen tray, executive presentation, premium writing instrument packaging',
	'industrial_use' => 'Stationery, Gift, Corporate, Luxury Retail, Writing Instruments',
	'paper_type' => 'Rigid Greyboard / Art Paper / Specialty Paper / Textured Paper / Velvet Paper Optional',
	'box_type' => 'Fountain Pen Gift Box with Custom Tray',
	'shape' => 'Long Rectangle / Slim Box / Customized Shape',
	'accessories' => 'Velvet tray / EVA insert / Ribbon / Warranty card / Refill compartment',
	'liner_type' => 'Velvet insert / EVA insert / Foam insert / Flocked tray / Paper tray',
	'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Soft-touch Lamination',
	'color' => 'Black / Navy / Grey / Burgundy / CMYK / Pantone / Customized Color',
	'duplicate_score' => 3,
	'details' => array( 'pen length', 'nib clearance', 'clip height', 'tray groove', 'refill card', 'inner-lid message', 'velvet insert', 'executive gift use' ),
);

$products[] = array(
	'title' => 'CUSTOM KNIFE SET PACKAGING BOX',
	'slug' => 'custom-knife-set-packaging-box',
	'category' => 'Retail Packaging Boxes',
	'tags' => 'knife set packaging box, kitchen knife box, cutlery packaging, rigid knife box, custom retail packaging',
	'focus' => 'knife set packaging box',
	'seo_title' => 'CUSTOM KNIFE SET PACKAGING BOX | VPN PAPER BOX MANUFACTURER',
	'meta' => 'Custom knife set packaging box for kitchen knives and cutlery sets, with protective inserts, rigid board, printing, and retail display.',
	'alt' => 'Knife set packaging box for kitchen cutlery with protective paper insert',
	'images' => array(
		'wp-content/uploads/2026/05/custom-knife-set-packaging-box-1.webp',
		'wp-content/uploads/2026/05/custom-knife-set-packaging-box-2.webp',
		'wp-content/uploads/2026/05/custom-knife-set-packaging-box-3.webp',
		'wp-content/uploads/2026/05/custom-knife-set-packaging-box-4.webp',
	),
	'short' => 'CUSTOM KNIFE SET PACKAGING BOX is made for kitchen knives, cutlery gift sets, chef knife kits, utility knives, and premium homeware retail products. The packaging must protect sharp edges, separate different blade sizes, prevent handle movement, and present the set clearly for retail or gifting. It can use rigid board, corrugated paper, duplex board, kraft paper, EVA insert, paperboard tray, foam support, or molded pulp depending on weight and safety requirement. Custom options include blade slot layout, warning panel, logo printing, handle display, sleeve, and premium finishing. MOQ starts from 1000 boxes.',
	'heading_intro' => 'Knife Set Packaging Starts with Safety and Blade Separation',
	'intro_a' => 'A knife set box has a sharper risk profile than ordinary homeware packaging.',
	'intro_b' => 'The box must hold blades safely, keep handles aligned, and help the buyer understand the set composition.',
	'buyers' => 'kitchenware brands, cutlery manufacturers, homeware distributors, and chef gift suppliers',
	'problem_core' => 'blade safety, handle movement, set organization, insert strength, and retail warning layout',
	'specific_angle' => 'sharp-edge protection, blade slot planning, cutlery set display, and safety information',
	'heading_structure' => 'Protective Structures for Kitchen Knife Sets',
	'structures' => 'a rigid lid-and-base box, corrugated protective carton, sleeve box, window retail box, or tray-based cutlery set package',
	'structure_details' => 'blade length, blade width, handle thickness, knife count, slot distance, edge direction, warning panel, and carton packing',
	'heading_insert' => 'Blade Slots, Handle Cavities, and Safety Support',
	'insert_a' => 'Each knife should have a dedicated position so blades do not touch and handles do not shift.',
	'insert_b' => 'EVA, foam, molded pulp, corrugated partitions, or paperboard trays can create safe separation for different blade sizes.',
	'heading_material' => 'Material Choices for Heavy and Sharp Products',
	'material_a' => 'Knife sets need stronger board than lightweight retail products because the edges and handles create pressure points.',
	'material_b' => 'Rigid board or corrugated support can be combined with printed sleeves for a balance of safety and shelf appearance.',
	'material_reason' => 'blade protection, product weight, retail safety, and homeware gift value',
	'heading_print' => 'Safety Warnings, Set Contents, and Brand Story',
	'print_a' => 'Knife packaging should reserve space for blade type, set contents, care instructions, safety warnings, barcode, material claims, and brand story.',
	'print_b' => 'Premium finishing can be added, but it should not reduce warning readability or make the package slippery to handle.',
	'print_details' => 'knife count, blade types, care instructions, safety warning, barcode, steel claim, handle material, and warranty information',
	'heading_b2b' => 'Wholesale Kitchenware Packaging Programs',
	'b2b_a' => 'Kitchenware brands often sell several knife set sizes using the same visual system.',
	'b2b_b' => 'A strong dieline can support three-piece, five-piece, or seven-piece sets with adjusted insert slots.',
	'heading_qc' => 'QC Checks for Knife Set Boxes',
	'qc_points' => 'insert slot accuracy, blade clearance, handle fit, warning print readability, board strength, corner durability, glue quality, and carton packing',
	'heading_quote' => 'Quote Information for Knife Set Boxes',
	'quote_details' => 'knife count, blade sizes, handle sizes, total weight, insert preference, safety text, artwork, finishing request, and quantity',
	'heading_duplicate' => 'Why Knife Set Packaging Is Not Generic Homeware Packaging',
	'duplicate_focus' => 'blade safety, slot layout, handle cavities, warning panels, cutlery set organization, and sharp product shipping',
	'captions' => array(
			'Custom knife set packaging box for kitchen cutlery retail.',
			'Knife set box insert layout for blade and handle protection.',
			'Cutlery packaging detail for safety information and branding.',
			'Custom kitchen knife packaging for homeware and chef gift sets.',
	),
	'links' => array(
		'category' => array( '/product-category/retail-packaging-boxes/', 'retail packaging boxes for homeware products' ),
		'related1' => array( '/product/custom-dinnerware-packaging-box/', 'dinnerware packaging for fragile homeware sets' ),
		'related2' => array( '/product/custom-corporate-gift-set-packaging-boxes/', 'gift set boxes for premium homeware campaigns' ),
		'guide' => 'corrugated and rigid paperboard for protective packaging',
		'contact' => 'request a knife set packaging quote',
	),
	'spec_feature' => 'Custom logo, knife slot insert, sharp-edge protection, retail cutlery presentation',
	'industrial_use' => 'Kitchenware, Cutlery, Homeware, Retail, Gift Packaging',
	'paper_type' => 'Rigid Greyboard / Corrugated Paper / Duplex Board / Kraft Paper / Art Paper',
	'box_type' => 'Knife Set Packaging Box with Protective Insert',
	'shape' => 'Long Rectangle / Set Box / Customized Shape',
	'accessories' => 'EVA insert / Foam insert / Paper tray / Corrugated divider / Safety card',
	'liner_type' => 'EVA insert / Foam insert / Molded pulp insert / Paperboard tray',
	'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Matte Lamination, Glossy Lamination',
	'color' => 'Black / Grey / Kraft / Metallic Accent / CMYK / Pantone / Customized Color',
	'duplicate_score' => 3,
	'details' => array( 'blade length', 'blade clearance', 'handle cavity', 'knife count', 'edge direction', 'safety warning', 'steel claim', 'slot distance' ),
);

$products[] = array(
	'title' => 'CUSTOM KRAFT PAPER BAG FOR SUPPLEMENT PACKAGING',
	'slug' => 'custom-kraft-paper-bag-for-supplement-packaging',
	'category' => 'Paper Bags',
	'tags' => 'kraft supplement paper bag, supplement packaging bag, kraft paper bag, wellness packaging, custom paper bag',
	'focus' => 'kraft paper bag for supplement packaging',
	'seo_title' => 'CUSTOM KRAFT PAPER BAG FOR SUPPLEMENT PACKAGING | VPN PAPER BOX MANUFACTURER',
	'meta' => 'Custom kraft paper bag for supplement packaging, wellness retail, and promotional kits, with logo printing, handles, and reinforced paper.',
	'alt' => 'Kraft paper bag for supplement packaging with natural wellness retail style',
	'images' => array(
		'wp-content/uploads/2026/05/custom-kraft-paper-bag-for-supplement-packaging-1.webp',
		'wp-content/uploads/2026/05/custom-kraft-paper-bag-for-supplement-packaging-2.webp',
		'wp-content/uploads/2026/05/custom-kraft-paper-bag-for-supplement-packaging-3.webp',
		'wp-content/uploads/2026/05/custom-kraft-paper-bag-for-supplement-packaging-4.webp',
	),
	'short' => 'CUSTOM KRAFT PAPER BAG FOR SUPPLEMENT PACKAGING is designed for vitamins, wellness products, herbal supplements, nutrition kits, health stores, and promotional supplement bundles. Unlike a supplement drawer box, this product works as a carry-out, retail, event, or gift bag that supports a natural wellness identity. It can be customized by kraft paper weight, gusset, reinforced base, handle type, logo printing, QR code, product message, and surface finish. The bag is suitable for supplement brands, clinics, health shops, distributors, and wellness campaigns that need branded packaging from 1000 bags.',
	'heading_intro' => 'Kraft Supplement Bags Communicate Natural Wellness at Carry-Out Level',
	'intro_a' => 'A kraft supplement paper bag is not the primary bottle box; it is the branded outer layer used in stores, clinics, events, and wellness kits.',
	'intro_b' => 'The bag should feel natural, strong, and clean while carrying supplement bottles, sachets, brochures, and sample packs.',
	'buyers' => 'supplement brands, wellness retailers, clinics, nutrition distributors, and health campaign teams',
	'problem_core' => 'carry weight, natural material appearance, handle strength, gusset width, and wellness brand messaging',
	'specific_angle' => 'kraft paper texture, supplement retail carry-out, reinforced bag construction, and health campaign packaging',
	'heading_structure' => 'Gusset, Handle, and Reinforced Base for Supplement Products',
	'structures' => 'a rope-handle kraft bag, flat-handle shopping bag, reinforced gift bag, die-cut handle bag, or wide-gusset wellness retail bag',
	'structure_details' => 'bag width, bag height, gusset depth, bottle count, bottom board strength, handle material, carry weight, and flat storage',
	'heading_insert' => 'Carry Support Instead of Bottle Insert Design',
	'insert_a' => 'This product does not need internal bottle cavities like a drawer box.',
	'insert_b' => 'The important support is the base board, side gusset, handle attachment, and paper thickness that carry the supplement products safely.',
	'heading_material' => 'Kraft Paper Choices for Wellness Branding',
	'material_a' => 'Natural kraft paper gives a health-focused and eco-conscious impression that suits herbal supplements and wellness products.',
	'material_b' => 'White kraft, brown kraft, recycled kraft, or laminated kraft can be selected depending on print clarity and carry strength.',
	'material_reason' => 'supplement brand tone, carry weight, handle durability, and natural retail presentation',
	'heading_print' => 'Logo, QR Code, and Wellness Message Layout',
	'print_a' => 'Supplement paper bags often need logo placement, QR code, clinic message, product campaign, social handle, and natural wellness claims.',
	'print_b' => 'Printing should stay clean and readable on kraft paper because rougher paper texture can soften small details.',
	'print_details' => 'logo, QR code, wellness campaign text, store information, social handle, recycling mark, and supplement line message',
	'heading_b2b' => 'Wholesale Kraft Bags for Health Stores and Campaigns',
	'b2b_a' => 'Health retailers may use the same kraft bag for many supplement products and seasonal promotions.',
	'b2b_b' => 'A stable bag size can carry bottles, sample sachets, flyers, and gift packs while keeping the brand identity consistent.',
	'heading_qc' => 'QC Checks for Kraft Supplement Bags',
	'qc_points' => 'paper GSM, handle pull strength, base board placement, gusset fold, logo print clarity, glue strength, bag opening, and carton packing',
	'heading_quote' => 'Quote Information for Kraft Supplement Bags',
	'quote_details' => 'bag size, gusset depth, expected carry weight, handle type, kraft paper color, logo artwork, print color, finish, and quantity',
	'heading_duplicate' => 'Why This Bag Is Different from Supplement Drawer Boxes',
	'duplicate_focus' => 'kraft carry-out use, handle strength, bag gusset, wellness campaign message, and retail shopping presentation',
	'captions' => array(
		'Custom kraft paper bag for supplement packaging and wellness retail.',
		'Supplement kraft bag structure with handle and reinforced base.',
		'Natural kraft paper bag detail for health product branding.',
		'Custom wellness retail bag for supplement stores and campaigns.',
	),
	'links' => array(
		'category' => array( '/product-category/paper-bags/', 'custom paper bags for wellness retail' ),
		'related1' => array( '/product/custom-supplement-drawer-packaging-box/', 'supplement drawer packaging for bottle kits' ),
		'related2' => array( '/product/custom-cosmetic-paper-bag/', 'cosmetic paper bags with boutique handles' ),
		'guide' => 'kraft paper choices for branded retail bags',
		'contact' => 'request a kraft supplement paper bag quote',
	),
	'spec_feature' => 'Custom logo, kraft texture, reinforced base, wellness retail carry bag',
	'industrial_use' => 'Supplement, Wellness, Health Retail, Gift, Promotional Packaging',
	'paper_type' => 'Brown Kraft Paper / White Kraft Paper / Recycled Kraft Paper / Laminated Kraft Paper',
	'box_type' => 'Kraft Paper Bag for Supplement Packaging',
	'shape' => 'Shopping Bag / Gift Bag / Wide Gusset / Customized Shape',
	'accessories' => 'Rope handle / Flat handle / Reinforced base / Hang tag / Ribbon optional',
	'liner_type' => 'Reinforced paper bottom / Folded gusset / Custom bag support',
	'printing' => 'CMYK Printing, Pantone Printing, Screen Printing, Foil Stamping Optional, Matte Lamination Optional',
	'color' => 'Brown Kraft / White Kraft / Natural Color / CMYK / Pantone / Customized Color',
	'duplicate_score' => 3,
	'details' => array( 'kraft paper GSM', 'gusset depth', 'handle pull strength', 'base board', 'QR code', 'wellness campaign text', 'carry weight', 'natural kraft texture' ),
);

$audit = "# Batch 2 Five Product Rewrite Audit\n\n";
$updated = 0;

foreach ( $products as $p ) {
	$ids = array();
	foreach ( $p['images'] as $index => $path ) {
		$id = vpn_b2_attachment_id( $path );
		if ( $id ) {
			$ids[] = $id;
			update_post_meta( $id, '_wp_attachment_image_alt', $p['alt'] );
		}
	}
	$post = get_page_by_path( $p['slug'], OBJECT, 'product' );
	$postarr = array(
		'post_type'    => 'product',
		'post_status'  => 'publish',
		'post_title'   => $p['title'],
		'post_name'    => $p['slug'],
		'post_excerpt' => $p['short'],
		'post_content' => vpn_b2_content( $p, $ids ),
	);
	if ( $post ) {
		$postarr['ID'] = $post->ID;
		$product_id = wp_update_post( $postarr, true );
	} else {
		$product_id = wp_insert_post( $postarr, true );
	}
	if ( is_wp_error( $product_id ) || ! $product_id ) {
		echo "Failed: {$p['title']}\n";
		continue;
	}
	$term = term_exists( $p['category'], 'product_cat' );
	if ( ! $term ) {
		$term = wp_insert_term( $p['category'], 'product_cat' );
	}
	if ( ! is_wp_error( $term ) ) {
		wp_set_object_terms( $product_id, array( (int) ( is_array( $term ) ? $term['term_id'] : $term ) ), 'product_cat' );
	}
	wp_set_object_terms( $product_id, 'simple', 'product_type' );
	wp_set_object_terms( $product_id, array_map( 'trim', explode( ',', $p['tags'] ) ), 'product_tag' );
	if ( $ids ) {
		set_post_thumbnail( $product_id, $ids[0] );
		update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $ids, 1 ) ) );
	}
	update_post_meta( $product_id, '_sku', 'sample-b2-' . $p['slug'] );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_b2_specs( $p ) );
	update_post_meta( $product_id, '_vpn_sample_import', 'product-samples-batch-2-five' );
	update_post_meta( $product_id, '_vpn_duplicate_risk_score', $p['duplicate_score'] );
	update_post_meta( $product_id, '_vpn_product_specific_details', $p['details'] );
	update_post_meta( $product_id, 'rank_math_title', $p['seo_title'] );
	update_post_meta( $product_id, 'rank_math_description', $p['meta'] );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $p['focus'] );
	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $product_id );
	}
	$word_count = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ) );
	$audit .= "## {$p['title']}\n\n";
	$audit .= "- URL: " . get_permalink( $product_id ) . "\n";
	$audit .= "- Word count: {$word_count}\n";
	$audit .= "- Duplicate risk score: {$p['duplicate_score']}/10\n";
	$audit .= "- Product-specific details: " . implode( ', ', $p['details'] ) . "\n\n";
	echo "Batch 2 product ready: {$p['title']} (#{$product_id}) {$word_count} words\n";
	$updated++;
}

$audit .= "## Pair Risk Review\n\n";
$audit .= "| Product A | Product B | Similarity risk | Recommended handling |\n";
$audit .= "|---|---|---:|---|\n";
$audit .= "| Double Wine Bottle Gift Box | Corporate Gift Set Box | 4/10 | Keep separate; wine box focuses on bottle weight, glass divider, neck clearance. |\n";
$audit .= "| Drawer Gift Box | Supplement Drawer Box | 5/10 | Keep separate; drawer gift box focuses on slide-out experience, supplement drawer focuses on health bottle trust and dosage. |\n";
$audit .= "| Fountain Pen Gift Box | Colored Pencil Packaging Box | 3/10 | Keep separate; pen box focuses on single luxury instrument, pencil box focuses on color organization. |\n";
$audit .= "| Knife Set Packaging Box | Dinnerware Packaging Box | 4/10 | Keep separate; both homeware but knife page focuses on sharp-edge safety and blade slots. |\n";
$audit .= "| Kraft Supplement Paper Bag | Cosmetic Paper Bag | 4/10 | Keep separate; supplement bag focuses on kraft wellness carry-out and health campaign messaging. |\n";

file_put_contents( dirname( __DIR__ ) . '/product-samples-batch-2-five-audit.md', $audit );
echo "Updated {$updated} batch 2 products.\n";
