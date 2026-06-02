<?php
require_once dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function vpn_b3_attachment_id( string $relative_path ): int {
	$uploads = wp_get_upload_dir();
	$url     = trailingslashit( $uploads['baseurl'] ) . preg_replace( '#^wp-content/uploads/#', '', $relative_path );
	$id      = attachment_url_to_postid( $url );
	if ( $id ) {
		return $id;
	}

	$path = dirname( __DIR__ ) . '/' . $relative_path;
	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( basename( $path ), null );
	$id       = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $path ) ),
			'post_status'    => 'inherit',
			'guid'           => $url,
		),
		$path
	);

	if ( is_wp_error( $id ) ) {
		return 0;
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	return (int) $id;
}

function vpn_b3_link( string $path, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_b3_specs( array $p ): array {
	return array(
		array( 'label' => 'Feature', 'value' => $p['feature'] ),
		array( 'label' => 'Industrial Use', 'value' => $p['industrial'] ),
		array( 'label' => 'Paper Type', 'value' => $p['paper'] ),
		array( 'label' => 'Box Type', 'value' => $p['box_type'] ),
		array( 'label' => 'Shape', 'value' => $p['shape'] ),
		array( 'label' => 'Place of Origin', 'value' => 'Vietnam' ),
		array( 'label' => 'Model Number', 'value' => $p['title'] ),
		array( 'label' => 'Brand Name', 'value' => 'VPN' ),
		array( 'label' => 'Province', 'value' => 'Ho Chi Minh City' ),
		array( 'label' => 'Accessories', 'value' => $p['accessories'] ),
		array( 'label' => 'Custom Order', 'value' => 'Accept' ),
		array( 'label' => 'Liner Type', 'value' => $p['liner'] ),
		array( 'label' => 'Logo Printing', 'value' => 'Custom logo' ),
		array( 'label' => 'Printing Handling', 'value' => $p['printing'] ),
		array( 'label' => 'Color', 'value' => $p['color'] ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $p['title'] ),
		array( 'label' => 'Design', 'value' => 'Customer Specific Requirement' ),
	);
}

function vpn_b3_images_html( array $ids, array $captions ): string {
	$out = '';
	foreach ( array_slice( $ids, 0, 4 ) as $i => $id ) {
		if ( ! $id ) {
			continue;
		}
		$caption = $captions[ $i ] ?? $captions[0] ?? 'Custom paper packaging detail for this product.';
		$out .= '<figure class="product-inline-figure product-inline-figure-small' . ( $i % 2 ? ' is-narrow' : '' ) . '">';
		$out .= wp_get_attachment_image( $id, 'large', false, array( 'loading' => 'lazy' ) );
		$out .= '<figcaption>' . esc_html( $caption ) . '</figcaption>';
		$out .= '</figure>' . "\n";
	}
	return $out;
}

function vpn_b3_content( array $p, array $ids ): string {
	$links = array(
		'guide'    => vpn_b3_link( '/paper-materials-for-custom-paper-boxes/', $p['anchors']['guide'] ),
		'related1' => vpn_b3_link( $p['anchors']['related1'][0], $p['anchors']['related1'][1] ),
		'related2' => vpn_b3_link( $p['anchors']['related2'][0], $p['anchors']['related2'][1] ),
	);

	$sections = array(
		$p['heading'] => array(
			$p['title'] . ' is developed for ' . $p['audience'] . ' that need packaging planned around ' . $p['core_need'] . '. The product page should not read like a generic box description, because buyers in this niche usually compare structure, material, insert layout, printed information, shelf display, packing speed, and export reliability before they ask for a quote. This batch 3 page is written from the product DNA of ' . strtolower( $p['keyword'] ) . ', so the content stays separate from earlier gift, cosmetic, electronics, stationery, wine, supplement, and homeware products.',
			'For B2B buyers, this packaging must help teams make practical decisions before sampling. The box needs to answer how the product sits inside, how it opens, what information appears on each panel, which surface treatment fits the brand, and how the finished packaging will behave during retail display, warehouse packing, carton loading, and delivery. VPN Paper Box Manufacturer can adjust the structure, paperboard, insert, print method, and finishing plan for custom projects from 1000 boxes.',
		),
		$p['structure_heading'] => array(
			'The recommended structure can include ' . $p['structures'] . ', depending on product weight, display method, unboxing expectation, and shipping route. The structure should be confirmed from the real product dimensions rather than only from a reference image, because small differences in height, edge pressure, opening direction, or accessory placement can change the dieline and insert design.',
			'Important structural details for this product include ' . implode( ', ', array_slice( $p['details'], 0, 8 ) ) . '. These points help the packaging hold the product in a fixed position, reduce movement during delivery, keep the front display clean, and make the unboxing feel intentional. The structure should also leave enough room for barcode, warnings, ingredient or technical information, and market-specific labeling when needed.',
		),
		$p['insert_heading'] => array(
			'Insert planning is one of the fastest ways to separate this product from similar-looking paper boxes. The insert can be made from paperboard, corrugated board, molded pulp, EVA, foam, or a shaped tray according to the product type. For this product, the insert should focus on ' . $p['insert_focus'] . ', not only on making the inside look neat for photos.',
			'If the product is fragile, heavy, cylindrical, sharp, premium, or sold as a set, the insert needs to support the pressure points. Loose cavities may look acceptable during design review but can fail when products are packed in bulk. A good sample should be checked for loading speed, product removal, shaking resistance, and carton packing before mass production.',
		),
		$p['material_heading'] => array(
			'Material choice should match product weight, brand level, sustainability target, printing detail, and logistics risk. Common options include ' . $p['paper'] . '. Buyers can compare material strength and surface behavior in the ' . $links['guide'] . ', then select the best construction for the target price and market channel.',
			'For this product, material is not only a cost item. It affects color accuracy, crease quality, edge sharpness, stacking performance, hand feel, and the way the package communicates trust. A premium retail project may use mounted art paper and rigid board, while a volume order may use ivory paper, duplex board, kraft paper, or corrugated paper to balance cost and protection.',
		),
		$p['application_heading'] => array(
			'This packaging can be used for ' . $p['uses'] . '. The exact application should be stated early in the brief because the same outside box can require a very different insert and printed panel once the product changes. Retail products need front-facing clarity, e-commerce products need compression control, and export orders need carton planning.',
			'The product also fits ' . $p['channels'] . '. In these channels, packaging is part of procurement, sales, shelf presentation, and fulfillment. A practical package helps the buyer keep SKUs organized, reduce repacking work, improve brand consistency, and present the product more confidently to distributors or end customers.',
		),
		$p['custom_heading'] => array(
			'Customization can include box size, opening direction, logo position, brand color, insert layout, handle, ribbon, window, sleeve, tray, or label panel. The artwork should be planned around real information areas instead of placing decoration everywhere. Useful panels may include product name, feature icons, ingredient or technical text, barcode, QR code, warning marks, batch information, and multilingual copy.',
			'For OEM and ODM buyers, the same structure can be adapted across several SKUs by changing the insert, label area, color system, and printed model information. This helps brands keep a consistent packaging family while still giving each product a specific use case. Related buyers may also review ' . $links['related1'] . ' when comparing similar structures.',
		),
		$p['printing_heading'] => array(
			'Printing and finishing options include ' . $p['printing'] . '. Offset printing is suitable for clean graphics, product information, gradients, and accurate brand colors. Foil stamping, embossing, debossing, spot UV, matte lamination, gloss lamination, soft-touch lamination, and specialty paper can be selected according to the product positioning.',
			'The finishing should support the product message rather than hide important information. If the package needs technical data, warnings, dosage, food information, or compatibility labels, those areas must remain readable. Premium processes can be applied to the logo, pattern, lid, sleeve, or brand mark while keeping functional panels clear.',
		),
		$p['b2b_heading'] => array(
			'For B2B customers, this product is valuable because it can be produced consistently in large quantities while still being customized for product size, brand identity, and market rules. It supports sample development, private label projects, distributor programs, retail launch kits, e-commerce packaging, and export-ready production.',
			'Bulk production can reduce unit cost, but the design still needs to be specific. A quote should include product measurements, target quantity, material preference, insert requirement, artwork file, finishing request, and shipping expectation. The minimum order quantity for this batch 3 product is 1000 boxes.',
		),
		$p['difference_heading'] => array(
			'This page avoids duplicate content by focusing on ' . implode( ', ', $p['details'] ) . '. Those details are different from the other products already imported, so the description does more than replace one product name with another. The headings, examples, insert logic, and buyer notes are chosen for this product only.',
			'When the next batch is produced, nearby products should start again from their own Product DNA. For example, a gift box with a paper bag should not be written like a magnetic gift box, a pill carton should not read like a medical kit package, and a phone box should not repeat a phone case page. This keeps the website useful for international buyers and lowers duplicate risk.',
		),
		$p['process_heading'] => array(
			'The ordering process normally starts with product photos, dimensions, expected quantity, shipping channel, and brand artwork. VPN Paper Box Manufacturer can then recommend structure, paper material, insert type, printing method, finishing plan, and master carton direction. After the dieline is confirmed, a sample can be produced for fit and surface review.',
			'During sample approval, buyers should test product loading, product removal, panel readability, insert stability, color match, and carton packing. After approval, mass production can move through printing, lamination, mounting, die cutting, folding, gluing, insert assembly, quality checking, and final packing. Related structure inspiration is available from ' . $links['related2'] . '.',
		),
		$p['cta_heading'] => array(
			'Send your product size, target quantity, preferred material, artwork file, and any insert or finishing requirement to request a custom quote. We can suggest a practical packaging structure for ' . strtolower( $p['keyword'] ) . ', prepare a sample plan, and quote based on material, size, printing, finishing, insert, and order volume.',
		),
	);

	$html  = '';
	$count = 0;
	foreach ( $sections as $heading => $paragraphs ) {
		$html .= '<h2>' . esc_html( $heading ) . "</h2>\n";
		foreach ( $paragraphs as $paragraph ) {
			$html .= '<p>' . $paragraph . "</p>\n";
		}
		if ( 1 === $count || 3 === $count || 5 === $count || 8 === $count ) {
			$html .= vpn_b3_images_html( array_slice( $ids, (int) ( $count / 2 ), 1 ), array_slice( $p['captions'], (int) ( $count / 2 ), 1 ) );
		}
		$count++;
	}

	return $html;
}

$products = array(
	array(
		'title' => 'CUSTOM LUXURY GIFT BOX WITH PAPER BAG',
		'slug' => 'custom-luxury-gift-box-with-paper-bag',
		'category' => 'Gift Packaging Boxes',
		'tags' => array( 'luxury gift box with paper bag', 'gift box and bag set', 'premium paper packaging', 'rigid gift box', 'custom gift packaging' ),
		'keyword' => 'luxury gift box with paper bag',
		'seo_title' => 'CUSTOM LUXURY GIFT BOX WITH PAPER BAG | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom luxury gift box with paper bag for retail gifts, VIP sets, and branded campaigns with matching rigid box, shopping bag, inserts, logo printing, and premium finishing.',
		'images' => array( 'wp-content/uploads/2026/05/custom-luxury-gift-box-with-paper-bag-1.webp', 'wp-content/uploads/2026/05/custom-luxury-gift-box-with-paper-bag-2.webp', 'wp-content/uploads/2026/05/custom-luxury-gift-box-with-paper-bag-3.webp', 'wp-content/uploads/2026/05/custom-luxury-gift-box-with-paper-bag-4.webp' ),
		'captions' => array( 'Matching rigid gift box and paper bag for premium retail presentation.', 'Custom gift box set with coordinated color, logo, and handle detail.', 'Luxury paper bag and box system for branded VIP gifting.', 'Premium gift packaging set designed for retail and event campaigns.' ),
		'alt' => 'Luxury gift box with paper bag for premium retail gifting and branded campaigns',
		'short' => 'CUSTOM LUXURY GIFT BOX WITH PAPER BAG is a coordinated paper packaging solution for retail gifts, VIP client sets, holiday campaigns, and premium product launches. The set can include a rigid gift box, matching shopping bag, insert, ribbon, magnetic closure, custom logo, and brand color system. It is suitable for brands, agencies, distributors, and OEM/ODM gift suppliers that need consistent packaging from 1000 sets.',
		'heading' => 'Coordinated Gift Packaging for Premium Brand Campaigns',
		'structure_heading' => 'Rigid Box and Paper Bag Structure',
		'insert_heading' => 'Insert and Carry-Out Experience',
		'material_heading' => 'Paper Materials for a Matching Set',
		'application_heading' => 'Retail, Event, and VIP Gift Uses',
		'custom_heading' => 'Coordinating Box, Bag, Logo, and Color',
		'printing_heading' => 'Luxury Printing and Finishing Options',
		'b2b_heading' => 'B2B Value for Gift Set Procurement',
		'difference_heading' => 'Why This Set Is Not a Generic Gift Box',
		'process_heading' => 'Sampling and Production Workflow',
		'cta_heading' => 'Request a Gift Box and Paper Bag Quote',
		'audience' => 'gift brands, corporate agencies, retail teams, and distributors',
		'core_need' => 'a matching rigid box and shopping bag presentation',
		'structures' => 'magnetic rigid boxes, lid and base boxes, drawer boxes, matching paper bags, ribbon bags, and insert-based gift sets',
		'insert_focus' => 'protecting the gift item while making the box and bag feel like one coordinated brand system',
		'uses' => 'cosmetic gifts, jewelry sets, candles, apparel accessories, VIP kits, holiday gifts, event merchandise, and premium retail bundles',
		'channels' => 'corporate gifting, boutique retail, distributor programs, influencer gifting, hotel gift programs, and launch events',
		'details' => array( 'matching bag handle', 'rigid box lid fit', 'insert depth', 'ribbon color', 'gift message panel', 'bag gusset', 'logo alignment', 'box and bag color match', 'magnetic closure', 'master carton pairing' ),
		'feature' => 'Matching gift box and paper bag set, custom logo, insert, premium retail presentation',
		'industrial' => 'Gift, Retail, Corporate Gift, Luxury Packaging, Promotional Packaging',
		'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Kraft Paper / Coated Paper',
		'box_type' => 'Luxury Gift Box with Matching Paper Bag',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'Paper bag, ribbon, insert, magnetic closure, handle, custom tray',
		'liner' => 'Paperboard insert / EVA insert / Foam insert / Custom tray',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Soft-touch Lamination',
		'color' => 'White / Black / Brand Color / Pantone / Customized Color',
		'anchors' => array( 'guide' => 'paper materials for premium gift packaging', 'related1' => array( '/product/custom-corporate-gift-set-packaging-boxes/', 'corporate gift set packaging with inserts' ), 'related2' => array( '/product/custom-magnetic-gift-box/', 'magnetic gift boxes for premium presentation' ) ),
		'duplicate' => 4,
	),
	array(
		'title' => 'CUSTOM MAGNETIC GIFT BOX',
		'slug' => 'custom-magnetic-gift-box',
		'category' => 'Gift Packaging Boxes',
		'tags' => array( 'magnetic gift box', 'rigid magnetic box', 'luxury gift packaging', 'custom magnetic closure box', 'premium paper box' ),
		'keyword' => 'magnetic gift box',
		'seo_title' => 'CUSTOM MAGNETIC GIFT BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom magnetic gift box for premium products, retail gifts, and branded sets with rigid board, magnetic closure, inserts, logo printing, and luxury finishes.',
		'images' => array( 'wp-content/uploads/2026/05/custom-magnetic-gift-box-1.webp', 'wp-content/uploads/2026/05/custom-magnetic-gift-box-2.webp', 'wp-content/uploads/2026/05/custom-magnetic-gift-box-3.webp', 'wp-content/uploads/2026/05/custom-magnetic-gift-box-4.webp' ),
		'captions' => array( 'Magnetic rigid gift box with clean lid closure and premium surface.', 'Custom magnetic closure box for retail and VIP gift products.', 'Rigid magnetic box structure with branded presentation surface.', 'Luxury magnetic gift packaging designed for repeatable B2B production.' ),
		'alt' => 'Magnetic gift box with rigid paperboard closure for premium retail products',
		'short' => 'CUSTOM MAGNETIC GIFT BOX is a rigid paper packaging solution for premium gifts, retail products, promotional kits, and branded merchandise. The magnetic closure creates a controlled opening experience while inserts help secure products inside. The box can be customized by size, color, logo, paper material, magnet strength, ribbon, tray, and finishing. MOQ starts from 1000 boxes.',
		'heading' => 'Magnetic Closure Packaging for Controlled Unboxing',
		'structure_heading' => 'Magnet Position, Lid Angle, and Board Strength',
		'insert_heading' => 'Interior Tray Planning for Magnetic Boxes',
		'material_heading' => 'Rigid Board and Mounted Paper Choices',
		'application_heading' => 'Gift, Retail, and Promotional Uses',
		'custom_heading' => 'Custom Size, Logo, and Closure Details',
		'printing_heading' => 'Finishing That Highlights the Magnetic Lid',
		'b2b_heading' => 'Scalable Magnetic Box Production',
		'difference_heading' => 'How This Differs From Box and Bag Sets',
		'process_heading' => 'Magnetic Box Sampling Checklist',
		'cta_heading' => 'Request a Magnetic Gift Box Quote',
		'audience' => 'premium product brands, retailers, agencies, and private label gift suppliers',
		'core_need' => 'a stable rigid box with a controlled magnetic closing experience',
		'structures' => 'book-style magnetic boxes, clamshell rigid boxes, foldable magnetic boxes, sleeve magnetic boxes, and insert-based presentation boxes',
		'insert_focus' => 'holding products securely without interrupting the clean lid opening and magnetic snap',
		'uses' => 'jewelry, watches, cosmetics, candles, electronics accessories, stationery gifts, apparel accessories, and promotional kits',
		'channels' => 'boutique retail, online gift sales, corporate campaigns, subscription boxes, distributor showrooms, and product launches',
		'details' => array( 'magnet strength', 'lid alignment', 'hinge crease', 'board thickness', 'insert height', 'ribbon pull', 'opening angle', 'closure sound', 'front flap space', 'edge wrapping' ),
		'feature' => 'Magnetic closure, rigid board structure, custom insert, premium unboxing',
		'industrial' => 'Gift, Retail, Promotional Packaging, Luxury Packaging',
		'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Coated Paper / Kraft Paper Optional',
		'box_type' => 'Magnetic Rigid Gift Box',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'Magnet, ribbon, paperboard insert, EVA insert, foam insert, custom tray',
		'liner' => 'Paperboard liner / Velvet liner / EVA insert / Foam insert',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Glossy Lamination',
		'color' => 'Black / White / Grey / Pantone / Customized Color',
		'anchors' => array( 'guide' => 'rigid paperboard materials for magnetic boxes', 'related1' => array( '/product/custom-rigid-gift-box/', 'rigid gift boxes with lid and base structure' ), 'related2' => array( '/product/custom-luxury-gift-box-with-paper-bag/', 'gift box and paper bag sets for brand campaigns' ) ),
		'duplicate' => 4,
	),
	array(
		'title' => 'CUSTOM MEDICAL KIT PACKAGING BOX',
		'slug' => 'custom-medical-kit-packaging-box',
		'category' => 'Healthcare Packaging Boxes',
		'tags' => array( 'medical kit packaging box', 'healthcare packaging', 'diagnostic kit box', 'medical paper box', 'custom kit packaging' ),
		'keyword' => 'medical kit packaging box',
		'seo_title' => 'CUSTOM MEDICAL KIT PACKAGING BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom medical kit packaging box for healthcare kits, diagnostic products, sample sets, and clinical supplies with organized inserts, clear labeling, and durable paperboard.',
		'images' => array( 'wp-content/uploads/2026/05/custom-medical-kit-packaging-box-1.webp', 'wp-content/uploads/2026/05/custom-medical-kit-packaging-box-2.webp', 'wp-content/uploads/2026/05/custom-medical-kit-packaging-box-3.webp', 'wp-content/uploads/2026/05/custom-medical-kit-packaging-box-4.webp', 'wp-content/uploads/2026/05/custom-medical-kit-packaging-box-open.jpeg', 'wp-content/uploads/2026/05/custom-medical-kit-packaging-box-inside.jpeg', 'wp-content/uploads/2026/05/custom-medical-kit-packaging-box-detail.jpeg' ),
		'captions' => array( 'Medical kit packaging box with organized clinical product presentation.', 'Custom healthcare kit box with printed information panels.', 'Medical kit packaging structure for sample and diagnostic components.', 'Healthcare paper box designed for kit organization and labeling.' ),
		'alt' => 'Medical kit packaging box for healthcare products with organized paperboard structure',
		'short' => 'CUSTOM MEDICAL KIT PACKAGING BOX is designed for diagnostic kits, healthcare sample sets, first-aid components, clinical products, and medical supply programs. The box can be customized with compartments, instruction space, QR codes, batch labels, warning panels, and strong paperboard. It is suitable for healthcare brands, medical distributors, laboratories, OEM/ODM suppliers, and bulk orders from 1000 boxes.',
		'heading' => 'Healthcare Kit Packaging Built Around Clear Organization',
		'structure_heading' => 'Compartments for Clinical Components',
		'insert_heading' => 'Insert Logic for Sample Tubes, Cards, and Tools',
		'material_heading' => 'Paperboard Choices for Healthcare Handling',
		'application_heading' => 'Medical, Diagnostic, and Sample Kit Uses',
		'custom_heading' => 'Labeling, Instructions, QR Codes, and Batch Areas',
		'printing_heading' => 'Clean Printing for Medical Information',
		'b2b_heading' => 'B2B Healthcare Packaging Reliability',
		'difference_heading' => 'Why Medical Kit Packaging Needs Its Own Page',
		'process_heading' => 'Medical Kit Box Development Process',
		'cta_heading' => 'Request a Medical Kit Packaging Quote',
		'audience' => 'healthcare brands, medical distributors, laboratories, and diagnostic kit suppliers',
		'core_need' => 'organized compartments, readable information, and stable kit handling',
		'structures' => 'folding cartons, lid and base boxes, sleeve kits, corrugated mailer boxes, and tray-based medical kit structures',
		'insert_focus' => 'separating sample components, printed instructions, small tools, cards, bottles, and test accessories',
		'uses' => 'diagnostic test kits, first-aid kits, sample collection kits, medical device accessories, wellness screening kits, and clinical supply sets',
		'channels' => 'healthcare distribution, laboratory programs, clinic supply, pharmacy sales, public health projects, and export medical packaging',
		'details' => array( 'component count', 'instruction booklet space', 'QR code panel', 'batch label', 'warning icons', 'tube cavity', 'card slot', 'sterile product note', 'tamper label area', 'kit sequence' ),
		'feature' => 'Organized kit compartments, clear medical labeling, custom insert, healthcare presentation',
		'industrial' => 'Healthcare, Medical, Diagnostic Kit, Clinical Product Packaging',
		'paper' => 'Ivory Paper / Duplex Board / Corrugated Paper / Rigid Board / Kraft Paper',
		'box_type' => 'Medical Kit Packaging Box with Insert',
		'shape' => 'Rectangle / Customized Kit Shape',
		'accessories' => 'Paperboard divider, molded pulp insert, EVA insert, instruction pocket, QR label',
		'liner' => 'Paperboard insert / Corrugated insert / Molded pulp tray / EVA insert',
		'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Spot UV, Barcode Printing, QR Code Printing',
		'color' => 'White / Blue / Green / Medical Brand Color / Customized Color',
		'anchors' => array( 'guide' => 'paperboard options for healthcare packaging', 'related1' => array( '/product/custom-pill-packaging-box/', 'pill packaging boxes for medicine products' ), 'related2' => array( '/product/custom-vial-packaging-box/', 'vial packaging boxes with protective inserts' ) ),
		'duplicate' => 3,
	),
	array(
		'title' => 'CUSTOM MUG PACKAGING BOX WITH WINDOW',
		'slug' => 'custom-mug-packaging-box-with-window',
		'category' => 'Retail Packaging Boxes',
		'tags' => array( 'mug packaging box with window', 'ceramic mug box', 'window packaging box', 'cup packaging box', 'custom retail box' ),
		'keyword' => 'mug packaging box with window',
		'seo_title' => 'CUSTOM MUG PACKAGING BOX WITH WINDOW | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom mug packaging box with window for ceramic mugs, cups, and drinkware retail products with protective paperboard, visible display window, and branded printing.',
		'images' => array( 'wp-content/uploads/2026/05/custom-mug-packaging-box-with-window-1.webp', 'wp-content/uploads/2026/05/custom-mug-packaging-box-with-window-2.webp', 'wp-content/uploads/2026/05/custom-mug-packaging-box-with-window-3.webp', 'wp-content/uploads/2026/05/custom-mug-packaging-box-with-window-4.webp' ),
		'captions' => array( 'Mug packaging box with window for visible ceramic cup display.', 'Custom window box structure for retail mug presentation.', 'Paperboard mug box with front display window and branded panels.', 'Retail cup packaging box designed for product visibility and protection.' ),
		'alt' => 'Mug packaging box with window for ceramic cup retail display',
		'short' => 'CUSTOM MUG PACKAGING BOX WITH WINDOW is a retail paperboard packaging solution for ceramic mugs, cups, drinkware gifts, and branded beverage merchandise. The display window helps customers see the mug design while the box protects the product during handling. It can be customized by mug size, window shape, paperboard strength, insert, logo, color, and finishing. MOQ starts from 1000 boxes.',
		'heading' => 'Window Mug Packaging for Visible Drinkware Display',
		'structure_heading' => 'Window Shape, Handle Clearance, and Base Support',
		'insert_heading' => 'Holding Ceramic Mugs Without Hiding the Design',
		'material_heading' => 'Paperboard Strength for Ceramic Products',
		'application_heading' => 'Mug, Cup, and Drinkware Retail Uses',
		'custom_heading' => 'Custom Window, Handle Position, and Artwork Panels',
		'printing_heading' => 'Printing Around a Visible Product Window',
		'b2b_heading' => 'Retail Packaging Value for Drinkware Suppliers',
		'difference_heading' => 'How Mug Packaging Differs From Dinnerware Boxes',
		'process_heading' => 'Mug Box Fit Testing and Production',
		'cta_heading' => 'Request a Mug Window Box Quote',
		'audience' => 'drinkware brands, ceramic mug suppliers, gift shops, and promotional product distributors',
		'core_need' => 'product visibility, ceramic protection, handle clearance, and retail stacking',
		'structures' => 'window folding cartons, tuck-end boxes, lock-bottom boxes, sleeve boxes, and reinforced paperboard mug boxes',
		'insert_focus' => 'supporting the mug base and handle while keeping the printed mug design visible through the window',
		'uses' => 'ceramic mugs, coffee cups, tea cups, promotional mugs, souvenir drinkware, holiday mug gifts, and branded cup sets',
		'channels' => 'gift shops, supermarket retail, online drinkware stores, coffee brand merchandise, tourism products, and promotional campaigns',
		'details' => array( 'window shape', 'handle clearance', 'mug diameter', 'bottom lock', 'PET window', 'anti-scratch space', 'stacking strength', 'barcode panel', 'souvenir artwork', 'ceramic protection' ),
		'feature' => 'Display window, mug protection, custom logo, retail-ready paperboard',
		'industrial' => 'Drinkware, Gift, Retail, Ceramic Product Packaging',
		'paper' => 'Ivory Paper / Duplex Board / Kraft Paper / Corrugated Paper / PET Window Optional',
		'box_type' => 'Mug Packaging Box with Window',
		'shape' => 'Rectangle / Customized Window Shape',
		'accessories' => 'PET window, paperboard insert, bottom lock, divider, handle clearance',
		'liner' => 'Paperboard support / Corrugated insert / Custom mug tray',
		'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Gloss Lamination, Spot UV, Window Patching',
		'color' => 'White / Kraft / Brand Color / Customized Color',
		'anchors' => array( 'guide' => 'paper materials for ceramic retail packaging', 'related1' => array( '/product/custom-dinnerware-packaging-box/', 'dinnerware packaging for fragile tableware' ), 'related2' => array( '/product/custom-rigid-gift-box/', 'rigid gift boxes for premium drinkware gifts' ) ),
		'duplicate' => 3,
	),
	array(
		'title' => 'CUSTOM PAPER TUBE FOOD PACKAGING BOX',
		'slug' => 'custom-paper-tube-food-packaging-box',
		'category' => 'Food Packaging Boxes',
		'tags' => array( 'paper tube food packaging box', 'food tube packaging', 'round paper box', 'custom food packaging', 'kraft paper tube' ),
		'keyword' => 'paper tube food packaging box',
		'seo_title' => 'CUSTOM PAPER TUBE FOOD PACKAGING BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom paper tube food packaging box for snacks, tea, coffee, candy, and dry food products with round paper structure, food-safe liner options, and branded printing.',
		'images' => array( 'wp-content/uploads/2026/05/custom-paper-tube-food-packaging-box.jpeg', 'wp-content/uploads/2026/05/custom-paper-tube-food-packaging-box-open.jpeg', 'wp-content/uploads/2026/05/custom-paper-tube-food-packaging-box-inside.jpeg', 'wp-content/uploads/2026/05/custom-paper-tube-food-packaging-box-detail.jpeg' ),
		'captions' => array( 'Paper tube food packaging box with round branded structure.', 'Open paper tube food box showing lid and container format.', 'Inside view of food paper tube packaging for dry product use.', 'Detail view of custom paper tube food packaging surface and lid.' ),
		'alt' => 'Paper tube food packaging box for dry food products with round paper structure',
		'short' => 'CUSTOM PAPER TUBE FOOD PACKAGING BOX is a round paper packaging solution for tea, coffee, snacks, candy, cookies, powders, and dry food products. The tube can be customized by diameter, height, lid type, paper material, inner liner, label design, and brand color. It supports retail display, gift packaging, and private label food projects from 1000 boxes.',
		'heading' => 'Round Paper Tube Packaging for Dry Food Products',
		'structure_heading' => 'Tube Diameter, Lid Fit, and Vertical Display',
		'insert_heading' => 'Liner and Inner Protection for Food Use',
		'material_heading' => 'Kraft, Coated Paper, and Food Liner Choices',
		'application_heading' => 'Tea, Coffee, Snack, and Candy Uses',
		'custom_heading' => 'Custom Diameter, Label Space, and Lid Style',
		'printing_heading' => 'Printing on Curved Paper Tube Surfaces',
		'b2b_heading' => 'B2B Value for Food Brands',
		'difference_heading' => 'Why Food Tubes Are Different From Rectangular Boxes',
		'process_heading' => 'Food Tube Sampling and Production',
		'cta_heading' => 'Request a Paper Tube Food Packaging Quote',
		'audience' => 'food brands, tea suppliers, coffee roasters, snack distributors, and private label food manufacturers',
		'core_need' => 'round shelf display, lid fit, food liner planning, and dry product presentation',
		'structures' => 'round paper tubes, kraft tubes, telescopic lid tubes, paper cans, composite paper tubes, and sleeve-label tube packaging',
		'insert_focus' => 'protecting dry food with the right inner liner, lid tightness, and moisture barrier expectation',
		'uses' => 'tea leaves, coffee beans, candy, cookies, nuts, snacks, protein powder samples, dried fruit, and gourmet food gifts',
		'channels' => 'food retail, supermarket shelves, gourmet gift shops, coffee shops, tea brands, subscription food boxes, and export dry food projects',
		'details' => array( 'tube diameter', 'lid tightness', 'inner liner', 'food label area', 'moisture concern', 'round shelf display', 'bottom seal', 'powder filling', 'tamper seal option', 'curved artwork' ),
		'feature' => 'Round paper tube, food liner option, custom lid, branded retail display',
		'industrial' => 'Food, Tea, Coffee, Snack, Candy, Dry Food Packaging',
		'paper' => 'Kraft Paper / Coated Paper / Specialty Paper / Food-grade Inner Liner / Rigid Paper Tube',
		'box_type' => 'Round Paper Tube Food Packaging Box',
		'shape' => 'Round / Cylinder / Customized Tube Size',
		'accessories' => 'Paper lid, plastic lid optional, inner liner, tamper label, sealing sticker',
		'liner' => 'Food liner / Aluminum foil liner optional / Paper inner wall',
		'printing' => 'CMYK Printing, Pantone Printing, Label Printing, Foil Stamping, Matte Lamination, Gloss Lamination',
		'color' => 'Kraft / White / Food Brand Color / Customized Color',
		'anchors' => array( 'guide' => 'paper material options for food packaging', 'related1' => array( '/product/custom-printed-corrugated-pet-food-box/', 'printed corrugated boxes for pet food products' ), 'related2' => array( '/product/custom-pill-packaging-box/', 'pill cartons with regulated product information panels' ) ),
		'duplicate' => 3,
	),
	array(
		'title' => 'CUSTOM PHONE PACKAGING BOX',
		'slug' => 'custom-phone-packaging-box',
		'category' => 'Electronics Packaging Boxes',
		'tags' => array( 'phone packaging box', 'smartphone box', 'electronics packaging', 'mobile phone paper box', 'custom phone box' ),
		'keyword' => 'phone packaging box',
		'seo_title' => 'CUSTOM PHONE PACKAGING BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom phone packaging box for smartphones, mobile devices, refurbished phones, and electronics retail kits with rigid structure, inserts, manuals, and branded printing.',
		'images' => array( 'wp-content/uploads/2026/05/custom-phone-packaging-box-webp-1.webp', 'wp-content/uploads/2026/05/custom-phone-packaging-box-webp-2.webp', 'wp-content/uploads/2026/05/custom-phone-packaging-box-webp-3.webp', 'wp-content/uploads/2026/05/custom-phone-packaging-box-webp-4.webp' ),
		'captions' => array( 'Custom phone packaging box for smartphone retail presentation.', 'Phone box structure with clean electronics branding and insert space.', 'Smartphone packaging box designed for device, cable, and manual layout.', 'Mobile phone paper box for retail and refurbished device programs.' ),
		'alt' => 'Phone packaging box for smartphone retail kits with rigid paper structure',
		'short' => 'CUSTOM PHONE PACKAGING BOX is a paper packaging solution for smartphones, refurbished phones, demo devices, and mobile electronics kits. It can be produced with rigid board, art paper, ivory paper, or specialty paper and customized with device tray, accessory compartment, manual pocket, logo, model label, barcode, and premium finishing. MOQ starts from 1000 boxes.',
		'heading' => 'Smartphone Packaging Built for Device and Accessory Layout',
		'structure_heading' => 'Device Tray, Manual Pocket, and Accessory Layer',
		'insert_heading' => 'Protecting the Screen, Edge, and Cable Space',
		'material_heading' => 'Rigid Materials for Electronics Presentation',
		'application_heading' => 'Phone, Refurbished Device, and Demo Kit Uses',
		'custom_heading' => 'Model Labels, IMEI Space, and Brand Panels',
		'printing_heading' => 'Clean Electronics Printing and Finishing',
		'b2b_heading' => 'B2B Value for Mobile Device Programs',
		'difference_heading' => 'How Phone Boxes Differ From Phone Case Boxes',
		'process_heading' => 'Phone Box Sampling and Fit Testing',
		'cta_heading' => 'Request a Phone Packaging Box Quote',
		'audience' => 'mobile phone brands, refurbished device sellers, electronics distributors, and device kit suppliers',
		'core_need' => 'secure device placement, accessory organization, and clean model information',
		'structures' => 'rigid lid and base phone boxes, drawer phone boxes, sleeve boxes, tray systems, and accessory-layer electronics boxes',
		'insert_focus' => 'holding the phone screen and edges safely while organizing cable, manual, charger, SIM tool, and warranty card',
		'uses' => 'smartphones, refurbished phones, demo devices, mobile phone kits, repair replacement devices, and electronics retail bundles',
		'channels' => 'electronics retail, refurbished phone sales, distributor programs, warranty replacement kits, online device stores, and export electronics packaging',
		'details' => array( 'device tray depth', 'screen protection', 'manual pocket', 'charger cavity', 'model label', 'IMEI label space', 'accessory layer', 'anti-scratch liner', 'barcode panel', 'tamper seal area' ),
		'feature' => 'Device tray, accessory compartment, rigid structure, electronics branding',
		'industrial' => 'Electronics, Mobile Phone, Smartphone, Retail Device Packaging',
		'paper' => 'Rigid Greyboard / Art Paper / Ivory Paper / Specialty Paper / Coated Paper',
		'box_type' => 'Phone Packaging Box with Device Tray',
		'shape' => 'Rectangle / Customized Device Box Shape',
		'accessories' => 'Device tray, paperboard insert, manual pocket, accessory compartment, label area',
		'liner' => 'Paper tray / EVA insert / Foam insert / Molded pulp tray',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Matte Lamination, Soft-touch Lamination',
		'color' => 'White / Black / Technology Brand Color / Customized Color',
		'anchors' => array( 'guide' => 'paperboard materials for electronics boxes', 'related1' => array( '/product/custom-phone-case-packaging-box/', 'phone case packaging for mobile accessories' ), 'related2' => array( '/product/custom-charging-cable-packaging-box/', 'charging cable packaging for electronics accessories' ) ),
		'duplicate' => 4,
	),
	array(
		'title' => 'CUSTOM PILL PACKAGING BOX',
		'slug' => 'custom-pill-packaging-box',
		'category' => 'Healthcare Packaging Boxes',
		'tags' => array( 'pill packaging box', 'medicine packaging box', 'pharmaceutical carton', 'tablet packaging', 'custom healthcare box' ),
		'keyword' => 'pill packaging box',
		'seo_title' => 'CUSTOM PILL PACKAGING BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom pill packaging box for tablets, capsules, blister packs, and medicine products with readable panels, tamper label space, inserts, and healthcare printing.',
		'images' => array( 'wp-content/uploads/2026/05/custom-pill-packaging-box-1.webp', 'wp-content/uploads/2026/05/custom-pill-packaging-box-2.webp', 'wp-content/uploads/2026/05/custom-pill-packaging-box-3.webp', 'wp-content/uploads/2026/05/custom-pill-packaging-box-4.webp', 'wp-content/uploads/2026/05/custom-pill-packaging-box-5.webp', 'wp-content/uploads/2026/05/custom-pill-packaging-box-6.webp', 'wp-content/uploads/2026/05/custom-pill-packaging-box-7.webp' ),
		'captions' => array( 'Pill packaging box for medicine and tablet retail products.', 'Custom pill carton with healthcare branding and information panels.', 'Medicine packaging box designed for blister packs and dosage notes.', 'Healthcare paper box for pill, capsule, and supplement tablet products.' ),
		'alt' => 'Pill packaging box for medicine tablets with healthcare information panels',
		'short' => 'CUSTOM PILL PACKAGING BOX is designed for pills, tablets, capsules, blister packs, medicine samples, and healthcare products that need clear information panels and organized retail presentation. The box can include dosage layout, batch label, barcode, QR code, tamper label area, insert, and custom printing. It is suitable for pharmaceutical brands, supplement companies, distributors, and OEM/ODM projects from 1000 boxes.',
		'heading' => 'Pill Carton Packaging for Readable Healthcare Information',
		'structure_heading' => 'Blister Pack, Bottle, and Tablet Carton Structure',
		'insert_heading' => 'Holding Blisters, Leaflets, and Small Medicine Items',
		'material_heading' => 'Paperboard for Pharmaceutical Cartons',
		'application_heading' => 'Pill, Tablet, Capsule, and Medicine Uses',
		'custom_heading' => 'Dosage Panels, Batch Labels, and QR Codes',
		'printing_heading' => 'Healthcare Printing with Readability First',
		'b2b_heading' => 'B2B Value for Pharmaceutical Packaging',
		'difference_heading' => 'How Pill Boxes Differ From Medical Kit Boxes',
		'process_heading' => 'Pill Box Sampling and Artwork Review',
		'cta_heading' => 'Request a Pill Packaging Box Quote',
		'audience' => 'pharmaceutical brands, supplement manufacturers, healthcare distributors, and private label medicine suppliers',
		'core_need' => 'small product protection, readable dosage information, and regulated-looking retail panels',
		'structures' => 'tuck-end cartons, straight tuck boxes, reverse tuck boxes, sleeve cartons, blister card boxes, and small rigid healthcare boxes',
		'insert_focus' => 'keeping blister packs, leaflets, bottles, and small medicine items aligned while preserving clear dosage and warning information',
		'uses' => 'tablets, capsules, blister packs, pill bottles, medicine samples, healthcare kits, herbal tablets, and supplement pills',
		'channels' => 'pharmacy retail, supplement distribution, clinic products, private label medicine projects, healthcare e-commerce, and export pharmaceutical packaging',
		'details' => array( 'dosage panel', 'batch label', 'expiry date space', 'leaflet room', 'blister size', 'tamper label', 'barcode area', 'QR code panel', 'warning text', 'medicine claim space' ),
		'feature' => 'Readable healthcare panels, custom carton, blister support, tamper label space',
		'industrial' => 'Pharmaceutical, Healthcare, Medicine, Supplement Packaging',
		'paper' => 'Ivory Paper / Duplex Board / Coated Paper / Kraft Paper Optional / Rigid Board Optional',
		'box_type' => 'Pill Packaging Box / Pharmaceutical Carton',
		'shape' => 'Rectangle / Customized Medicine Box Shape',
		'accessories' => 'Leaflet space, paperboard insert, blister holder, tamper label, QR code panel',
		'liner' => 'Paperboard insert / Leaflet pocket / Custom blister support',
		'printing' => 'CMYK Printing, Pantone Printing, Matte Lamination, Barcode Printing, QR Code Printing, Spot UV Optional',
		'color' => 'White / Blue / Green / Healthcare Brand Color / Customized Color',
		'anchors' => array( 'guide' => 'paperboard choices for healthcare cartons', 'related1' => array( '/product/custom-medical-kit-packaging-box/', 'medical kit packaging with organized components' ), 'related2' => array( '/product/custom-supplement-drawer-packaging-box/', 'supplement drawer packaging for wellness products' ) ),
		'duplicate' => 3,
	),
	array(
		'title' => 'CUSTOM PRINTED CORRUGATED PET FOOD BOX',
		'slug' => 'custom-printed-corrugated-pet-food-box',
		'category' => 'Food Packaging Boxes',
		'tags' => array( 'printed corrugated pet food box', 'pet food packaging', 'corrugated food box', 'custom pet product box', 'printed shipping box' ),
		'keyword' => 'printed corrugated pet food box',
		'seo_title' => 'CUSTOM PRINTED CORRUGATED PET FOOD BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom printed corrugated pet food box for pet treats, dry food, subscription kits, and retail pet products with durable corrugated board and branded printing.',
		'images' => array( 'wp-content/uploads/2026/05/custom-printed-corrugated-pet-food-box.webp', 'wp-content/uploads/2026/05/custom-printed-corrugated-pet-food-box-2.webp', 'wp-content/uploads/2026/05/custom-printed-corrugated-pet-food-box-3.webp', 'wp-content/uploads/2026/05/custom-printed-corrugated-pet-food-box-4.webp' ),
		'captions' => array( 'Printed corrugated pet food box for branded dry food packaging.', 'Custom pet food corrugated box for retail and shipping use.', 'Pet product paper box with durable corrugated structure and print.', 'Corrugated packaging for pet treats, food pouches, and subscription kits.' ),
		'alt' => 'Printed corrugated pet food box for dry pet food and treat packaging',
		'short' => 'CUSTOM PRINTED CORRUGATED PET FOOD BOX is a durable paper packaging solution for pet food, treats, pouches, subscription kits, and pet product bundles. The corrugated structure supports protection during shipping while printed branding improves retail presentation. It can be customized by flute type, size, insert, logo, color, nutrition panel, and carton packing. MOQ starts from 1000 boxes.',
		'heading' => 'Corrugated Pet Food Packaging for Retail and Shipping',
		'structure_heading' => 'Flute Type, Product Weight, and Shipping Strength',
		'insert_heading' => 'Holding Pouches, Treat Packs, and Pet Product Bundles',
		'material_heading' => 'Corrugated Board Choices for Pet Food Boxes',
		'application_heading' => 'Pet Treat, Dry Food, and Subscription Uses',
		'custom_heading' => 'Nutrition Panels, Brand Artwork, and SKU Labels',
		'printing_heading' => 'Printed Corrugated Branding for Pet Products',
		'b2b_heading' => 'B2B Value for Pet Food Distribution',
		'difference_heading' => 'Why Pet Food Corrugated Boxes Need Separate Content',
		'process_heading' => 'Pet Food Box Sampling and Carton Planning',
		'cta_heading' => 'Request a Printed Pet Food Box Quote',
		'audience' => 'pet food brands, treat suppliers, subscription box operators, and pet product distributors',
		'core_need' => 'corrugated strength, food pouch organization, readable nutrition panels, and delivery protection',
		'structures' => 'corrugated mailer boxes, folding corrugated cartons, display boxes, tuck-end corrugated boxes, and subscription kit boxes',
		'insert_focus' => 'holding pouches, sachets, treat packs, scoop accessories, and mixed pet product bundles during shipping',
		'uses' => 'dry pet food, pet treats, sample pouches, subscription pet kits, grooming product bundles, pet supplements, and retail display packs',
		'channels' => 'pet retail stores, e-commerce delivery, subscription programs, distributor cartons, veterinary promotions, and export pet product packaging',
		'details' => array( 'flute type', 'pouch count', 'nutrition panel', 'pet treat weight', 'subscription insert', 'shipping compression', 'shelf display front', 'barcode label', 'food safety note', 'carton stacking' ),
		'feature' => 'Printed corrugated board, shipping protection, pet food branding, custom size',
		'industrial' => 'Pet Food, Food, E-commerce, Retail, Subscription Packaging',
		'paper' => 'Corrugated Paper / Kraft Paper / White Kraft / Duplex Board / Coated Paper',
		'box_type' => 'Printed Corrugated Pet Food Box',
		'shape' => 'Rectangle / Mailer / Customized Corrugated Shape',
		'accessories' => 'Paperboard divider, corrugated insert, locking tab, display opening, label area',
		'liner' => 'Corrugated insert / Kraft liner / Paperboard divider',
		'printing' => 'Flexo Printing, Offset Printing, CMYK Printing, Pantone Printing, Matte Lamination, Spot UV Optional',
		'color' => 'Kraft / White / Pet Brand Color / Customized Color',
		'anchors' => array( 'guide' => 'corrugated paper options for food packaging', 'related1' => array( '/product/custom-paper-tube-food-packaging-box/', 'paper tube food packaging for dry products' ), 'related2' => array( '/product/custom-dinnerware-packaging-box/', 'protective packaging structures for fragile products' ) ),
		'duplicate' => 3,
	),
	array(
		'title' => 'CUSTOM RIGID GIFT BOX',
		'slug' => 'custom-rigid-gift-box',
		'category' => 'Gift Packaging Boxes',
		'tags' => array( 'rigid gift box', 'custom rigid box', 'premium gift packaging', 'lid and base box', 'luxury paper box' ),
		'keyword' => 'rigid gift box',
		'seo_title' => 'CUSTOM RIGID GIFT BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom rigid gift box for premium retail products, branded gifts, and presentation sets with strong greyboard, inserts, logo printing, and luxury finishes.',
		'images' => array( 'wp-content/uploads/2026/05/custom-rigid-gift-box-1.webp', 'wp-content/uploads/2026/05/custom-rigid-gift-box-2.webp', 'wp-content/uploads/2026/05/custom-rigid-gift-box-3.webp', 'wp-content/uploads/2026/05/custom-rigid-gift-box-4.webp' ),
		'captions' => array( 'Rigid gift box with premium paperboard structure and branded surface.', 'Custom rigid box for retail gifts and product presentation.', 'Lid and base gift box structure for premium packaging projects.', 'Rigid paper gift box designed for inserts, logo, and luxury finishing.' ),
		'alt' => 'Rigid gift box for premium product packaging with strong paperboard structure',
		'short' => 'CUSTOM RIGID GIFT BOX is a premium paperboard packaging solution for gifts, retail products, luxury sets, and branded presentation boxes. It can be developed as a lid and base box, sleeve box, drawer box, or custom rigid structure with inserts, logo printing, foil stamping, embossing, and brand color matching. It is suitable for B2B gift packaging orders from 1000 boxes.',
		'heading' => 'Rigid Paperboard Packaging for Premium Product Presentation',
		'structure_heading' => 'Lid and Base Structure, Wall Height, and Edge Wrap',
		'insert_heading' => 'Interior Support for Premium Gift Products',
		'material_heading' => 'Greyboard Thickness and Mounted Paper Choices',
		'application_heading' => 'Retail Gift, Boutique, and Presentation Uses',
		'custom_heading' => 'Custom Box Size, Insert Layout, and Brand Surface',
		'printing_heading' => 'Luxury Finishing on Rigid Gift Boxes',
		'b2b_heading' => 'B2B Value for Premium Box Programs',
		'difference_heading' => 'How Rigid Gift Boxes Differ From Magnetic Boxes',
		'process_heading' => 'Rigid Box Sampling and Mass Production',
		'cta_heading' => 'Request a Rigid Gift Box Quote',
		'audience' => 'premium product brands, gift suppliers, retailers, and packaging distributors',
		'core_need' => 'strong board structure, clean edge wrapping, and premium product presentation',
		'structures' => 'lid and base rigid boxes, shoulder neck boxes, drawer rigid boxes, sleeve boxes, and custom presentation boxes',
		'insert_focus' => 'supporting the product inside a strong board structure while keeping the presentation clean and reusable',
		'uses' => 'jewelry, candles, apparel accessories, cosmetics, electronics gifts, stationery sets, homeware gifts, and promotional products',
		'channels' => 'boutique retail, corporate gift programs, online premium stores, distributor showrooms, holiday campaigns, and product launches',
		'details' => array( 'greyboard thickness', 'lid fit', 'edge wrapping', 'wall height', 'insert depth', 'shoulder structure', 'surface paper', 'foil logo', 'bottom board', 'reusable feel' ),
		'feature' => 'Rigid greyboard, lid and base structure, custom insert, premium finishing',
		'industrial' => 'Gift, Retail, Luxury Packaging, Promotional Product Packaging',
		'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Coated Paper / Kraft Paper Optional',
		'box_type' => 'Rigid Gift Box / Lid and Base Box',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'Paperboard insert, EVA insert, foam insert, ribbon, shoulder neck structure',
		'liner' => 'Paperboard liner / EVA insert / Foam insert / Velvet liner optional',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Soft-touch Lamination',
		'color' => 'White / Black / Grey / Brand Color / Customized Color',
		'anchors' => array( 'guide' => 'rigid greyboard materials for gift boxes', 'related1' => array( '/product/custom-magnetic-gift-box/', 'magnetic gift boxes with controlled closure' ), 'related2' => array( '/product/custom-luxury-gift-box-with-paper-bag/', 'luxury gift box and paper bag sets' ) ),
		'duplicate' => 4,
	),
	array(
		'title' => 'CUSTOM SINGLE WINE BOTTLE GIFT BOX',
		'slug' => 'custom-single-wine-bottle-gift-box',
		'category' => 'Wine Packaging Boxes',
		'tags' => array( 'single wine bottle gift box', 'wine bottle packaging', 'custom wine gift box', 'rigid wine box', 'beverage packaging' ),
		'keyword' => 'single wine bottle gift box',
		'seo_title' => 'CUSTOM SINGLE WINE BOTTLE GIFT BOX | VPN PAPER BOX MANUFACTURER',
		'meta' => 'Custom single wine bottle gift box for wine, spirits, and premium beverage gifting with bottle support, rigid paperboard, logo printing, and luxury finishing.',
		'images' => array( 'wp-content/uploads/2026/05/custom-single-wine-bottle-gift-box-1.webp', 'wp-content/uploads/2026/05/custom-single-wine-bottle-gift-box-2.webp', 'wp-content/uploads/2026/05/custom-single-wine-bottle-gift-box-3.webp', 'wp-content/uploads/2026/05/custom-single-wine-bottle-gift-box-4.webp' ),
		'captions' => array( 'Single wine bottle gift box for premium beverage presentation.', 'Custom wine bottle box with rigid structure and branded finish.', 'Paper wine gift box designed around bottle height and neck support.', 'Luxury single bottle packaging for wine, spirits, and corporate gifts.' ),
		'alt' => 'Single wine bottle gift box for premium beverage packaging with rigid paper structure',
		'short' => 'CUSTOM SINGLE WINE BOTTLE GIFT BOX is designed for one wine bottle, spirits bottle, champagne bottle, or premium beverage gift. The structure can be customized around bottle diameter, shoulder height, neck support, bottom reinforcement, logo, ribbon, magnetic closure, and finishing. It is suitable for wineries, beverage distributors, corporate gifting, and export orders from 1000 boxes.',
		'heading' => 'Single Bottle Gift Packaging for Wine and Premium Beverages',
		'structure_heading' => 'Bottle Height, Neck Support, and Bottom Reinforcement',
		'insert_heading' => 'Keeping One Bottle Stable During Gifting and Shipping',
		'material_heading' => 'Rigid Board and Beverage Packaging Materials',
		'application_heading' => 'Wine, Spirits, Champagne, and Beverage Uses',
		'custom_heading' => 'Custom Bottle Diameter, Logo, Ribbon, and Label Space',
		'printing_heading' => 'Premium Finishing for Wine Gift Boxes',
		'b2b_heading' => 'B2B Value for Beverage Gift Programs',
		'difference_heading' => 'How Single Bottle Boxes Differ From Double Wine Boxes',
		'process_heading' => 'Wine Bottle Box Sampling and Fit Check',
		'cta_heading' => 'Request a Single Wine Bottle Gift Box Quote',
		'audience' => 'wineries, beverage brands, spirit distributors, gift suppliers, and corporate buyers',
		'core_need' => 'single bottle stability, label protection, neck clearance, and premium gift presentation',
		'structures' => 'rigid wine boxes, lid and base bottle boxes, magnetic bottle boxes, drawer bottle boxes, and sleeve-based beverage gift boxes',
		'insert_focus' => 'holding one glass bottle securely at the base, shoulder, and neck without covering the bottle label unnecessarily',
		'uses' => 'red wine, white wine, champagne, spirits, olive oil bottles, beverage gifts, and corporate bottle gift programs',
		'channels' => 'wine retail, hotel gifting, distributor programs, holiday corporate gifts, beverage export, and premium food gift channels',
		'details' => array( 'bottle diameter', 'neck clearance', 'shoulder height', 'bottom reinforcement', 'label abrasion', 'ribbon pull', 'magnetic lid', 'glass weight', 'carton direction', 'gift message area' ),
		'feature' => 'Single bottle support, rigid gift structure, custom logo, premium beverage presentation',
		'industrial' => 'Wine, Beverage, Gift, Luxury Packaging',
		'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / Kraft Paper / Corrugated Paper Optional',
		'box_type' => 'Single Wine Bottle Gift Box',
		'shape' => 'Rectangle / Tall Bottle Box / Customized Shape',
		'accessories' => 'Bottle insert, ribbon, magnetic closure, paperboard divider, handle optional',
		'liner' => 'Paperboard bottle support / EVA insert / Foam insert / Corrugated support',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Matte Lamination, Spot UV',
		'color' => 'Black / Burgundy / Gold / Brand Color / Customized Color',
		'anchors' => array( 'guide' => 'paperboard materials for wine bottle boxes', 'related1' => array( '/product/custom-double-wine-bottle-gift-box/', 'double wine bottle gift boxes with center divider' ), 'related2' => array( '/product/custom-wine-bottle-gift-box-with-paper-bag/', 'wine bottle gift boxes with matching paper bags' ) ),
		'duplicate' => 4,
	),
);

$audit = array( '# Batch 3 Ten Product Audit', '' );

foreach ( $products as $p ) {
	$image_ids = array();
	foreach ( $p['images'] as $i => $image ) {
		$id = vpn_b3_attachment_id( $image );
		if ( $id ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $p['alt'] );
		}
		$image_ids[] = $id;
	}

	$existing = get_page_by_path( $p['slug'], OBJECT, 'product' );
	$postarr  = array(
		'post_type'    => 'product',
		'post_status'  => 'publish',
		'post_title'   => $p['title'],
		'post_name'    => $p['slug'],
		'post_excerpt' => $p['short'],
		'post_content' => vpn_b3_content( $p, $image_ids ),
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$product_id    = wp_update_post( $postarr );
	} else {
		$product_id = wp_insert_post( $postarr );
	}

	if ( is_wp_error( $product_id ) || ! $product_id ) {
		echo 'Failed: ' . $p['title'] . PHP_EOL;
		continue;
	}

	wp_set_object_terms( $product_id, $p['category'], 'product_cat' );
	wp_set_object_terms( $product_id, $p['tags'], 'product_tag' );
	wp_set_object_terms( $product_id, 'simple', 'product_type' );

	update_post_meta( $product_id, '_sku', 'sample-b3-' . $p['slug'] );
	update_post_meta( $product_id, '_regular_price', '' );
	update_post_meta( $product_id, '_price', '' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_thumbnail_id', $image_ids[0] ?? 0 );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_filter( array_slice( $image_ids, 1 ) ) ) );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_b3_specs( $p ) );
	update_post_meta( $product_id, '_vpn_sample_import', 'product-samples-batch-3-ten' );
	update_post_meta( $product_id, '_vpn_duplicate_risk_score', $p['duplicate'] );
	update_post_meta( $product_id, '_vpn_product_specific_details', $p['details'] );
	update_post_meta( $product_id, 'rank_math_title', $p['seo_title'] );
	update_post_meta( $product_id, 'rank_math_description', $p['meta'] );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $p['keyword'] );

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $product_id );
	}

	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ) );
	$audit[] = '## ' . $p['title'];
	$audit[] = '- URL: ' . get_permalink( $product_id );
	$audit[] = '- Words: ' . $words;
	$audit[] = '- Duplicate risk: ' . $p['duplicate'] . '/10';
	$audit[] = '- Product-specific details: ' . implode( ', ', $p['details'] );
	$audit[] = '';

	echo 'Imported: ' . $p['title'] . ' (#' . $product_id . ') words=' . $words . PHP_EOL;
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-batch-3-ten-audit.md', implode( PHP_EOL, $audit ) );
echo 'Updated 10 batch 3 products.' . PHP_EOL;
