<?php
/**
 * Import the latest June 2026 product image groups as WooCommerce products.
 *
 * Run:
 *   php tools/import-latest-product-images.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_latest_product_link( string $path, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_latest_product_attachment_id( string $filename, string $alt, string $title ): int {
	$attached_file = '2026/06/' . $filename;
	$ids           = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_attached_file',
			'meta_value'     => $attached_file,
		)
	);

	if ( $ids ) {
		$attachment_id = (int) $ids[0];
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		wp_update_post(
			array(
				'ID'           => $attachment_id,
				'post_title'   => $title,
				'post_excerpt' => $alt,
			)
		);

		return $attachment_id;
	}

	$uploads     = wp_get_upload_dir();
	$target_path = trailingslashit( $uploads['basedir'] ) . $attached_file;

	if ( ! file_exists( $target_path ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filetype      = wp_check_filetype( $target_path );
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => trailingslashit( $uploads['baseurl'] ) . $attached_file,
			'post_mime_type' => $filetype['type'] ?? 'image/webp',
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$target_path
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attached_file', $attached_file );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $target_path ) );

	return (int) $attachment_id;
}

function vpn_latest_product_specs( array $product ): array {
	return array(
		array( 'label' => 'Feature', 'value' => $product['feature'] ),
		array( 'label' => 'Industrial Use', 'value' => $product['industrial'] ),
		array( 'label' => 'Paper Type', 'value' => $product['paper'] ),
		array( 'label' => 'Box Type', 'value' => $product['box_type'] ),
		array( 'label' => 'Shape', 'value' => $product['shape'] ),
		array( 'label' => 'Place of Origin', 'value' => 'Vietnam' ),
		array( 'label' => 'Model Number', 'value' => $product['title'] ),
		array( 'label' => 'Brand Name', 'value' => 'VPN' ),
		array( 'label' => 'Province', 'value' => 'Ho Chi Minh City' ),
		array( 'label' => 'Accessories', 'value' => $product['accessories'] ),
		array( 'label' => 'Custom Order', 'value' => 'Accept' ),
		array( 'label' => 'Liner Type', 'value' => $product['liner'] ),
		array( 'label' => 'Logo Printing', 'value' => 'Custom logo' ),
		array( 'label' => 'Printing Handling', 'value' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Glossy Lamination' ),
		array( 'label' => 'Color', 'value' => $product['colors'] ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $product['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement" ),
	);
}

function vpn_latest_product_section( string $heading, array $paragraphs ): string {
	$html = '<h2>' . esc_html( $heading ) . '</h2>';

	foreach ( $paragraphs as $paragraph ) {
		$html .= '<p>' . $paragraph . '</p>';
	}

	return $html;
}

function vpn_latest_product_inline_image( int $image_id, string $caption, bool $narrow = false ): string {
	$image = wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'lazy' ) );
	if ( ! $image ) {
		return '';
	}

	return '<figure class="product-inline-figure product-inline-figure-small' . ( $narrow ? ' is-narrow' : '' ) . '">' . $image . '<figcaption>' . esc_html( $caption ) . '</figcaption></figure>';
}

function vpn_latest_product_content( array $product, array $image_ids ): string {
	$category_link = vpn_latest_product_link( $product['category_url'], $product['category_anchor'] );
	$materials     = vpn_latest_product_link( '/paper-materials-for-custom-paper-boxes/', 'paper material options for custom boxes' );
	$dieline       = vpn_latest_product_link( '/what-is-a-paper-box-dieline/', 'packaging dieline preparation' );
	$artwork       = vpn_latest_product_link( '/how-to-prepare-artwork-for-printed-paper-boxes/', 'print-ready packaging artwork' );
	$quote         = vpn_latest_product_link( '/contact/#quote', 'request a custom packaging quote' );
	$related       = vpn_latest_product_link( $product['related_url'], $product['related_anchor'] );

	$html  = vpn_latest_product_section(
		$product['opening_heading'],
		array(
			$product['title'] . ' is designed for ' . $product['buyers'] . ' that need packaging for ' . $product['applications'] . '. The packaging has to protect the product, explain the offer quickly, and give buyers a consistent brand impression across retail shelves, e-commerce photos, wholesale presentations, and export cartons. A custom structure is especially useful when the item has a non-standard size, a set of accessories, or a surface finish that can be damaged by loose movement during transport.',
			'This product belongs to our ' . $category_link . ' range and can be produced with custom size, board thickness, printed graphics, insert layout, surface finishing, and bulk order planning. Instead of treating the box as a blank carton, the structure should be planned around the real product dimensions, packing method, sales channel, barcode position, legal copy, opening direction, and the way customers will remove the item after purchase.',
		)
	);

	$html .= vpn_latest_product_section(
		$product['problem_heading'],
		array(
			'The main packaging problem is ' . $product['problem'] . '. If the box is too loose, the item can shift and rub against printed panels. If the insert is too tight, packing becomes slow and customers may struggle to remove the product. If the front panel is unclear, the buyer may not understand the product size, flavor, model, quantity, or gift value quickly enough.',
			'For this reason, the dieline should be checked with actual product samples before mass production. Panel size, flap depth, opening force, insert clearance, window position, glue area, and barcode placement should all be confirmed before final artwork. Useful preparation steps are explained in our guide to ' . $dieline . ', especially for projects that include folds, sleeves, tubes, drawer trays, or shaped inserts.',
		)
	);

	if ( ! empty( $image_ids[0] ) ) {
		$html .= vpn_latest_product_inline_image( $image_ids[0], $product['captions'][0], true );
	}

	$html .= vpn_latest_product_section(
		$product['structure_heading'],
		array(
			'Recommended structures include ' . $product['structures'] . '. The best option depends on packed weight, display position, unboxing expectation, shipping distance, and whether the product will be packed manually or on a production line. A premium set may need a rigid body and fitted tray, while high-volume retail packaging may need a faster folding carton or tube format with reliable closure.',
			'Structural details such as thumb notches, hang tabs, tray height, divider spacing, lid depth, tube diameter, shoulder fit, and reinforced corners can be adjusted during sampling. These details are small, but they determine whether the package feels easy to open, stable in the hand, and practical for repeated packing. For export orders, carton quantity and stacking direction should also be reviewed early.',
		)
	);

	if ( ! empty( $image_ids[1] ) ) {
		$html .= vpn_latest_product_inline_image( $image_ids[1], $product['captions'][1] );
	}

	$html .= vpn_latest_product_section(
		$product['insert_heading'],
		array(
			'Insert planning should focus on ' . $product['insert_need'] . '. Options may include folded paperboard, corrugated dividers, molded pulp, EVA foam, EPE foam, paper sleeves, or a custom inner tray. The insert should stop product movement without hiding key details or making the package difficult to assemble.',
			'When the product includes multiple pieces, each cavity needs a clear purpose. Accessories, instruction cards, refill packs, tubes, jars, balls, bands, or printed inserts should remain organized after handling. A physical sample is recommended because a digital mockup cannot fully show compression, friction, lid fit, opening resistance, or the real feeling of the unboxing sequence.',
		)
	);

	$html .= vpn_latest_product_section(
		$product['material_heading'],
		array(
			'Material choices include ' . $product['materials'] . '. Buyers can compare common board options in our ' . $materials . ' guide. Folding paperboard is efficient for lightweight retail packaging, kraft paper gives a natural look, corrugated board improves protection for shipping, and rigid greyboard supports premium gift presentation. For tube packaging, wall thickness, paper wrap, lid fit, and inner lining should be selected based on the product weight and shelf-life requirement.',
			'Surface finishing can include matte lamination, gloss lamination, anti-scratch film, foil stamping, embossing, debossing, spot UV, Pantone color matching, textured paper, or kraft texture. These finishes should support the product position rather than distract from it. A premium candle, tea, or coffee package may use tactile paper and foil, while sports retail boxes often need durable color blocks, sharp icons, and clear model information.',
		)
	);

	if ( ! empty( $image_ids[2] ) ) {
		$html .= vpn_latest_product_inline_image( $image_ids[2], $product['captions'][2], true );
	}

	$html .= vpn_latest_product_section(
		$product['artwork_heading'],
		array(
			'Artwork should organize ' . $product['artwork_needs'] . '. The front panel should identify the product quickly, while side and back panels can carry specifications, ingredients, usage instructions, warnings, care information, QR codes, barcodes, batch information, and importer details. For multi-SKU programs, a controlled artwork system helps keep different sizes, flavors, colors, and models consistent.',
			'Before printing, the final file should follow ' . $artwork . ' rules: CMYK or named Pantone colors, outlined or embedded fonts, high-resolution images, correct bleed, safe zones, separate dieline layer, and separate finishing masks when foil or spot UV is used. This reduces prepress questions and helps the buyer approve samples faster.',
		)
	);

	$html .= vpn_latest_product_section(
		$product['use_heading'],
		array(
			'Typical applications include ' . $product['applications'] . '. Compared with ' . $related . ', this product needs special attention to ' . $product['difference'] . '. That difference should influence the box style, insert material, print layout, finishing choice, and the amount of protection added around the product.',
			'Retail packaging should communicate benefits at a glance, while online packaging also needs to photograph well and survive parcel handling. Gift packaging should slow down the reveal and make the product feel complete as a set. Wholesale packaging may prioritize consistent dimensions, packing speed, carton efficiency, and easy quality inspection before shipment.',
		)
	);

	if ( ! empty( $image_ids[3] ) ) {
		$html .= vpn_latest_product_inline_image( $image_ids[3], $product['captions'][3] );
	}

	$html .= vpn_latest_product_section(
		'Procurement Checklist Before Ordering',
		array(
			'Before placing a bulk order, the buyer should prepare a clear packaging brief with product measurements, packed weight, preferred sales channel, target quantity, artwork status, insert requirement, and any mandatory labeling information. For ' . strtolower( $product['title'] ) . ', the brief should also explain ' . $product['difference'] . ' so the supplier can recommend a structure that matches the product instead of using a generic box.',
			'Sample approval should check the product fit, opening experience, print readability, material feel, finishing alignment, carton packing method, and how the package looks after normal handling. If the order includes multiple sizes, flavors, colors, resistance levels, scents, or product variants, send a complete SKU table before artwork finalization so the dieline, labels, barcode panels, and version control can be managed cleanly.',
		)
	);

	$html .= vpn_latest_product_section(
		'Bulk Production and Packing Notes',
		array(
			'For repeat production, the approved sample should become the control reference for material, structure, printing, finishing, insert fit, and packing method. The buyer can request a pre-production sample when the project uses a new paper material, new finish, new insert, or a new product size. This is especially important when the package must fit retail displays, gift sets, subscription cartons, or export cartons with fixed dimensions.',
			'During mass production, inspection can be organized around incoming material checks, printing color checks, die-cutting accuracy, gluing strength, insert assembly, final packed appearance, and export carton labeling. Clear packing instructions help workers place each item the same way, reduce pressure marks, protect printed surfaces, and keep the final presentation consistent when customers open the package.',
			'It is also useful to keep one approved production sample in the purchasing office and one sample with the factory QC team. When a reorder is placed months later, both sides can compare color, board stiffness, finish position, insert depth, and packing details against the same physical reference instead of relying only on photos or old messages.',
		)
	);

	$html .= vpn_latest_product_section(
		$product['qc_heading'],
		array(
			'Quality checks should cover dimensions, paper thickness, print color, lamination adhesion, foil or spot UV position, glue strength, closure action, insert fit, barcode readability, carton marks, and packed-product appearance. For premium products, sample review should include the full opening sequence, not only the outside graphics.',
			'For international orders, buyers should also confirm export carton strength, carton quantity, pallet plan, humidity protection, inspection criteria, and acceptable tolerance before production. Clear specifications make it easier for both sides to evaluate bulk goods consistently and reduce disputes caused by subjective expectations.',
		)
	);

	$html .= vpn_latest_product_section(
		$product['quote_heading'],
		array(
			'To quote this product accurately, useful information includes product dimensions, product weight, quantity, target market, preferred box style, required insert, artwork files, print colors, finishing requirements, shipping method, and expected delivery schedule. Photos or reference samples can help the packaging team understand the desired look and practical constraints.',
			'VPN supports custom size, structure, printing, finishing, inserts, and bulk production from 1000 boxes. Share the product details and order plan to ' . $quote . '. We can review the product, recommend a suitable paper packaging structure, and prepare a sample plan before mass production.',
		)
	);

	return $html;
}

$marker   = 'product-samples-latest-20260624';
$products = array(
	array(
		'title' => 'CUSTOM CANDLE PAPER TUBE BOX',
		'slug' => 'custom-candle-paper-tube-box',
		'keyword' => 'candle paper tube box',
		'category_slug' => 'paper-tube-packaging',
		'category_url' => '/product-category/paper-tube-packaging/',
		'category_anchor' => 'paper tube packaging',
		'related_url' => '/product/custom-candle-shipping-mailer-box-with-corrugated-insert/',
		'related_anchor' => 'candle shipping mailer boxes',
		'buyers' => 'candle brands, fragrance studios, gift shops, wellness retailers, and private label candle suppliers',
		'applications' => 'glass jar candles, travel candles, aromatherapy candles, seasonal gift candles, and premium fragrance sets',
		'problem' => 'protecting a fragile candle jar while keeping the fragrance product premium and easy to present as a gift',
		'structures' => 'round paper tubes, shoulder-neck tubes, rigid paper canisters, tube-and-lid sets, and tube sleeves with inner jar support',
		'insert_need' => 'jar stability, lid protection, fragrance card placement, surface scuff control, and a clean vertical reveal',
		'materials' => 'rigid paper tube board, coated paper wrap, kraft paper, specialty textured paper, and optional inner paperboard support',
		'artwork_needs' => 'fragrance name, candle weight, scent notes, burn instructions, warning labels, batch code, barcode, and premium brand graphics',
		'difference' => 'fragile jar protection, scent storytelling, warning label placement, and premium round tube presentation',
		'feature' => 'Rigid paper tube, candle jar protection, premium fragrance branding, custom lid and insert',
		'industrial' => 'Candle, Fragrance, Wellness Gift, Home Decor',
		'paper' => 'Rigid Paper Tube Board / Kraft Paper / Coated Art Paper / Specialty Paper',
		'box_type' => 'Candle Paper Tube Box',
		'shape' => 'Round Tube / Customized Diameter',
		'accessories' => 'Paper insert / Lid / Shoulder neck / Fragrance card / Warning label area',
		'liner' => 'Paperboard support / Inner tube liner / Tissue paper',
		'colors' => 'White / Kraft / Black / CMYK / Pantone / Customized Color',
		'opening_heading' => 'Paper Tube Packaging for Premium Candle Products',
		'problem_heading' => 'Candle Packaging Problems to Solve Before Sampling',
		'structure_heading' => 'Round Tube Structure and Candle Jar Fit',
		'insert_heading' => 'Insert and Lid Planning for Candle Tubes',
		'material_heading' => 'Materials and Finishing for Candle Paper Tubes',
		'artwork_heading' => 'Candle Label Artwork and Safety Information',
		'use_heading' => 'Candle Tube Applications and Sales Channels',
		'qc_heading' => 'Quality Control for Candle Tube Packaging',
		'quote_heading' => 'Quote Information for Custom Candle Tubes',
		'images' => array(
			'custom-candle-paper-tube-box-hero-shot.webp',
			'custom-candle-paper-tube-box-open-tube.webp',
			'custom-candle-paper-tube-box-luxury-finishing-detail.webp',
			'custom-candle-paper-tube-box-full-packaging-set.webp',
		),
		'captions' => array(
			'Custom candle paper tube box with premium fragrance packaging presentation.',
			'Open candle paper tube showing lid fit and inner product access.',
			'Close-up detail of luxury finishing for custom candle tube packaging.',
			'Full candle paper tube packaging set for retail and gift programs.',
		),
	),
	array(
		'title' => 'CUSTOM COFFEE PAPER TUBE PACKAGING',
		'slug' => 'custom-coffee-paper-tube-packaging',
		'keyword' => 'coffee paper tube packaging',
		'category_slug' => 'paper-tube-packaging',
		'category_url' => '/product-category/paper-tube-packaging/',
		'category_anchor' => 'paper tube packaging for food products',
		'related_url' => '/product/custom-tea-paper-tube-packaging/',
		'related_anchor' => 'tea paper tube packaging',
		'buyers' => 'coffee roasters, specialty beverage brands, gift set companies, subscription suppliers, and private label food brands',
		'applications' => 'ground coffee, coffee beans, sample tubes, premium coffee gifts, seasonal blends, and beverage subscription packs',
		'problem' => 'presenting coffee as a premium product while organizing flavor information, roast details, and inner bag protection',
		'structures' => 'paper tube canisters, telescopic tubes, food gift tubes, tube sleeves around inner bags, and multi-tube display sets',
		'insert_need' => 'inner bag retention, aroma label placement, scoop or card space, lid consistency, and protection from crushing during display',
		'materials' => 'kraft tube board, coated paper tube board, food-contact inner liner when required, specialty wrap paper, and rigid paper lids',
		'artwork_needs' => 'origin, roast level, flavor notes, net weight, brewing method, QR code, barcode, date area, and batch information',
		'difference' => 'food information hierarchy, aroma positioning, kraft texture, lid fit, and multi-flavor display consistency',
		'feature' => 'Coffee tube packaging, kraft texture, premium beverage branding, custom lid and label layout',
		'industrial' => 'Coffee, Beverage, Food Gift, Specialty Retail',
		'paper' => 'Kraft Paper Tube Board / Coated Art Paper / Food Grade Inner Liner If Required',
		'box_type' => 'Coffee Paper Tube Packaging',
		'shape' => 'Round Tube / Customized Diameter',
		'accessories' => 'Paper lid / Inner liner / Label area / Scoop card / Flavor sticker',
		'liner' => 'Food grade liner if required / Inner bag / Paperboard support',
		'colors' => 'Kraft / Brown / Black / CMYK / Pantone / Customized Color',
		'opening_heading' => 'Coffee Paper Tube Packaging for Specialty Beverage Brands',
		'problem_heading' => 'Coffee Tube Packaging Problems to Solve',
		'structure_heading' => 'Tube Structure for Beans, Grounds, and Gift Sets',
		'insert_heading' => 'Inner Bag and Accessory Planning',
		'material_heading' => 'Kraft Texture, Liners, and Food Packaging Materials',
		'artwork_heading' => 'Coffee Origin, Roast, and Flavor Artwork',
		'use_heading' => 'Coffee Tube Applications and Related Beverage Packaging',
		'qc_heading' => 'Quality Control for Coffee Tube Packaging',
		'quote_heading' => 'Quote Information for Coffee Tube Orders',
		'images' => array(
			'custom-coffee-paper-tube-packaging-premium-front-shot.webp',
			'custom-coffee-paper-tube-packaging-open-lid.webp',
			'custom-coffee-paper-tube-packaging-kraft-texture-detail.webp',
			'custom-coffee-paper-tube-packaging-multi-tube-display.webp',
		),
		'captions' => array(
			'Custom coffee paper tube packaging with premium front branding.',
			'Open lid view for coffee tube packaging and inner product access.',
			'Kraft texture detail for specialty coffee paper tube packaging.',
			'Multi-tube coffee packaging display for different roasts or flavors.',
		),
	),
	array(
		'title' => 'CUSTOM TEA PAPER TUBE PACKAGING',
		'slug' => 'custom-tea-paper-tube-packaging',
		'keyword' => 'tea paper tube packaging',
		'category_slug' => 'paper-tube-packaging',
		'category_url' => '/product-category/paper-tube-packaging/',
		'category_anchor' => 'custom paper tube packaging',
		'related_url' => '/product/custom-coffee-paper-tube-packaging/',
		'related_anchor' => 'coffee paper tube packaging',
		'buyers' => 'tea brands, herbal product suppliers, gift set companies, wellness retailers, and private label beverage brands',
		'applications' => 'loose leaf tea, tea sachets, herbal blends, premium tea gifts, seasonal tea collections, and wellness beverage sets',
		'problem' => 'protecting tea freshness and premium shelf appeal while making blend type, flavor, origin, and brewing information easy to understand',
		'structures' => 'round tea tubes, rigid paper canisters, lid-and-base tubes, multi-tube gift sets, and tube sleeves for inner pouches',
		'insert_need' => 'inner pouch stability, sachet count organization, flavor card space, lid alignment, and gift set arrangement',
		'materials' => 'rigid tube board, kraft paper, coated art paper, specialty textured wrap, and inner liner options when required',
		'artwork_needs' => 'tea type, origin, flavor profile, brewing time, temperature, net weight, ingredient list, date area, and barcode',
		'difference' => 'loose leaf freshness, brewing instruction clarity, natural texture, and multi-blend gift presentation',
		'feature' => 'Tea paper tube, herbal beverage packaging, premium gift set display, custom lid and label',
		'industrial' => 'Tea, Herbal Beverage, Food Gift, Wellness Retail',
		'paper' => 'Rigid Paper Tube Board / Kraft Paper / Coated Paper / Specialty Paper',
		'box_type' => 'Tea Paper Tube Packaging',
		'shape' => 'Round Tube / Customized Diameter',
		'accessories' => 'Paper lid / Inner pouch / Flavor card / Label sticker / Gift sleeve',
		'liner' => 'Food grade liner if required / Inner pouch / Paperboard support',
		'colors' => 'Green / Kraft / White / CMYK / Pantone / Customized Color',
		'opening_heading' => 'Tea Paper Tube Packaging for Herbal and Premium Blends',
		'problem_heading' => 'Tea Packaging Problems Before Production',
		'structure_heading' => 'Tube Structure for Tea Pouches and Gift Sets',
		'insert_heading' => 'Inner Pouch, Sachet, and Blend Organization',
		'material_heading' => 'Natural Paper Materials and Premium Finishing',
		'artwork_heading' => 'Tea Blend Artwork and Brewing Information',
		'use_heading' => 'Tea Tube Applications and Beverage Packaging Options',
		'qc_heading' => 'Quality Control for Tea Tube Packaging',
		'quote_heading' => 'Quote Information for Tea Tube Packaging',
		'images' => array(
			'custom-tea-paper-tube-packaging-hero-shot.webp',
			'custom-tea-paper-tube-packaging-open-tube.webp',
			'custom-tea-paper-tube-packaging-print-detail.webp',
			'custom-tea-paper-tube-packaging-set-composition.webp',
		),
		'captions' => array(
			'Custom tea paper tube packaging for herbal and premium tea brands.',
			'Open tea tube packaging showing lid and inner product access.',
			'Printing detail for custom tea paper tube packaging.',
			'Tea paper tube packaging set for multiple blends or gift collections.',
		),
	),
	array(
		'title' => 'CUSTOM FITNESS ACCESSORY GIFT BOX',
		'slug' => 'custom-fitness-accessory-gift-box',
		'keyword' => 'fitness accessory gift box',
		'category_slug' => 'sports-packaging-boxes',
		'category_url' => '/product-category/sports-packaging-boxes/',
		'category_anchor' => 'sports packaging boxes',
		'related_url' => '/product/premium-pickleball-set-rigid-paper-box/',
		'related_anchor' => 'premium sports gift set packaging',
		'buyers' => 'fitness accessory brands, gym merchandise suppliers, wellness gift companies, sports retailers, and promotional kit buyers',
		'applications' => 'resistance bands, grips, towels, jump ropes, training accessories, recovery tools, and branded fitness gift kits',
		'problem' => 'organizing multiple small fitness items in a premium gift format without letting accessories move or feel incomplete',
		'structures' => 'rigid drawer boxes, magnetic gift boxes, lid-and-base boxes, sleeve-and-tray boxes, and paperboard gift sets with fitted inserts',
		'insert_need' => 'multi-accessory cavity layout, elastic band control, card placement, finger access, and balanced gift presentation',
		'materials' => 'rigid greyboard, coated art paper, specialty paper, EVA foam, EPE foam, molded pulp, and paperboard insert materials',
		'artwork_needs' => 'brand campaign message, accessory list, training benefit icons, size or resistance level, QR code, and gifting information',
		'difference' => 'multi-item kit organization, premium unboxing, promotional campaign value, and accessory visibility',
		'feature' => 'Rigid fitness gift box, drawer structure, fitted insert, premium sports branding',
		'industrial' => 'Fitness Accessories, Sports Gifts, Wellness Promotion, Retail',
		'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / EVA Foam / Molded Pulp',
		'box_type' => 'Fitness Accessory Gift Box',
		'shape' => 'Rectangle / Drawer / Customized Set Layout',
		'accessories' => 'Drawer tray / EVA insert / Ribbon pull / Sleeve / Accessory wells',
		'liner' => 'EVA foam / Paperboard tray / Molded pulp / EPE foam',
		'colors' => 'Black / White / Green / CMYK / Pantone / Customized Color',
		'opening_heading' => 'Fitness Accessory Gift Box for Branded Training Kits',
		'problem_heading' => 'Fitness Kit Packaging Problems to Solve',
		'structure_heading' => 'Rigid Drawer and Gift Box Structure',
		'insert_heading' => 'Insert Layout for Multi-Accessory Sets',
		'material_heading' => 'Premium Materials and Finishing for Fitness Gifts',
		'artwork_heading' => 'Fitness Campaign Branding and Product Information',
		'use_heading' => 'Fitness Gift Box Applications and Related Sports Packaging',
		'qc_heading' => 'Quality Control for Fitness Gift Packaging',
		'quote_heading' => 'Quote Information for Fitness Accessory Gift Boxes',
		'images' => array(
			'custom-fitness-accessory-gift-box-premium-hero.webp',
			'custom-fitness-accessory-gift-box-drawer-box.webp',
			'custom-fitness-accessory-gift-box-open-insert.webp',
			'custom-fitness-accessory-gift-box-luxury-detail.webp',
		),
		'captions' => array(
			'Custom fitness accessory gift box for premium sports kits.',
			'Drawer box structure for branded fitness accessory packaging.',
			'Open insert layout for organizing multiple fitness accessories.',
			'Luxury finishing detail for custom fitness accessory gift boxes.',
		),
	),
	array(
		'title' => 'CUSTOM GOLF BALL PACKAGING BOX',
		'slug' => 'custom-golf-ball-packaging-box',
		'keyword' => 'golf ball packaging box',
		'category_slug' => 'sports-packaging-boxes',
		'category_url' => '/product-category/sports-packaging-boxes/',
		'category_anchor' => 'sports packaging boxes',
		'related_url' => '/product/premium-pickleball-set-rigid-paper-box/',
		'related_anchor' => 'pickleball set rigid paper boxes',
		'buyers' => 'golf brands, sporting goods retailers, tournament gift suppliers, club merchandise teams, and promotional product companies',
		'applications' => 'golf ball sleeves, three-ball packs, dozen-ball sets, tournament gifts, retail packs, and premium golf accessory kits',
		'problem' => 'holding round golf balls securely while making quantity, model, performance claim, and gift value clear to the buyer',
		'structures' => 'folding cartons, ball sleeves, rigid gift boxes, drawer boxes, lid-and-base boxes, and divider trays for multi-ball sets',
		'insert_need' => 'round ball separation, divider strength, sleeve fit, anti-rattle control, and neat reveal for gift or tournament sets',
		'materials' => 'ivory board, duplex board, rigid greyboard, coated art paper, corrugated micro-flute, and molded paper inserts',
		'artwork_needs' => 'ball count, model name, compression or performance notes, tournament logo, barcode, QR code, and premium brand marks',
		'difference' => 'round product retention, divider accuracy, small-pack retail clarity, and premium golf gifting',
		'feature' => 'Golf ball carton, divider insert, retail sleeve, premium tournament gift packaging',
		'industrial' => 'Golf, Sports Equipment, Tournament Gifts, Retail',
		'paper' => 'Ivory Board / Duplex Board / Rigid Greyboard / Corrugated Board / Molded Paper',
		'box_type' => 'Golf Ball Packaging Box',
		'shape' => 'Rectangle / Sleeve / Customized Layout',
		'accessories' => 'Divider insert / Drawer tray / Sleeve / Window / Gift card slot',
		'liner' => 'Paperboard divider / Molded pulp / EVA foam / No liner',
		'colors' => 'White / Green / Black / CMYK / Pantone / Customized Color',
		'opening_heading' => 'Golf Ball Packaging Box for Retail and Tournament Gifts',
		'problem_heading' => 'Golf Ball Packaging Problems Before Sampling',
		'structure_heading' => 'Sleeve, Divider, and Gift Box Structure',
		'insert_heading' => 'Divider Planning for Round Golf Balls',
		'material_heading' => 'Paperboard Materials and Premium Golf Finishing',
		'artwork_heading' => 'Golf Ball Model, Quantity, and Tournament Artwork',
		'use_heading' => 'Golf Ball Packaging Applications and Related Sports Boxes',
		'qc_heading' => 'Quality Control for Golf Ball Packaging',
		'quote_heading' => 'Quote Information for Golf Ball Box Orders',
		'images' => array(
			'custom-golf-ball-packaging-box-retail-hero.webp',
			'custom-golf-ball-packaging-box-open-dividers.webp',
			'custom-golf-ball-packaging-box-print-detail.webp',
			'custom-golf-ball-packaging-box-luxury-gift-set.webp',
		),
		'captions' => array(
			'Custom golf ball packaging box for retail and tournament gifts.',
			'Open divider layout for secure golf ball packaging.',
			'Printing detail for custom golf ball packaging boxes.',
			'Luxury golf ball gift set packaging for premium sports programs.',
		),
	),
	array(
		'title' => 'CUSTOM RESISTANCE BAND PACKAGING BOX',
		'slug' => 'custom-resistance-band-packaging-box',
		'keyword' => 'resistance band packaging box',
		'category_slug' => 'sports-packaging-boxes',
		'category_url' => '/product-category/sports-packaging-boxes/',
		'category_anchor' => 'sports packaging boxes',
		'related_url' => '/product/custom-fitness-accessory-gift-box/',
		'related_anchor' => 'fitness accessory gift boxes',
		'buyers' => 'resistance band brands, fitness accessory suppliers, gym retailers, therapy product distributors, and private label sports sellers',
		'applications' => 'loop bands, tube bands, resistance sets, therapy bands, training kits, retail hanging packs, and e-commerce fitness accessories',
		'problem' => 'keeping flexible bands organized while showing resistance level, color, usage, and product benefits clearly on the pack',
		'structures' => 'window boxes, tuck-end cartons, hanging cartons, sleeve boxes, drawer boxes, and compact mailer cartons with inserts',
		'insert_need' => 'band folding control, resistance-level separation, instruction card placement, window visibility, and compact retail display',
		'materials' => 'ivory board, duplex board, kraft paper, coated paperboard, PET window film, and corrugated micro-flute board',
		'artwork_needs' => 'resistance level, band size, color coding, exercise icons, material information, QR code, barcode, and warranty details',
		'difference' => 'flexible product control, color-coded resistance levels, window display, and compact hanging retail format',
		'feature' => 'Resistance band retail box, window display, insert support, fitness information layout',
		'industrial' => 'Fitness Accessories, Resistance Bands, Therapy Products, Sports Retail',
		'paper' => 'Ivory Board / Duplex Board / Kraft Paper / Coated Paper / PET Window',
		'box_type' => 'Resistance Band Packaging Box',
		'shape' => 'Vertical Rectangle / Window Box / Customized Shape',
		'accessories' => 'PET window / Paper insert / Hang tab / Instruction card / Resistance label',
		'liner' => 'Paperboard support / No liner / Folded insert',
		'colors' => 'Black / Green / White / CMYK / Pantone / Customized Color',
		'opening_heading' => 'Resistance Band Packaging Box for Fitness Retail',
		'problem_heading' => 'Resistance Band Packaging Problems to Solve',
		'structure_heading' => 'Window Box and Hanging Carton Structure',
		'insert_heading' => 'Band Folding, Insert, and Instruction Card Planning',
		'material_heading' => 'Retail Paperboard and Window Materials',
		'artwork_heading' => 'Resistance Level, Exercise, and Model Information',
		'use_heading' => 'Resistance Band Packaging Applications',
		'qc_heading' => 'Quality Control for Resistance Band Boxes',
		'quote_heading' => 'Quote Information for Resistance Band Packaging',
		'images' => array(
			'custom-resistance-band-packaging-box-closed-retail-box-hero.webp',
			'custom-resistance-band-packaging-box-window-box.webp',
			'custom-resistance-band-packaging-box-open-box-with-insert.webp',
			'custom-resistance-band-packaging-box-detail-printing-texture.webp',
		),
		'captions' => array(
			'Custom resistance band packaging box for compact fitness retail.',
			'Window box design for displaying resistance band products.',
			'Open resistance band box with insert and product organization.',
			'Printing texture detail for custom resistance band packaging.',
		),
	),
	array(
		'title' => 'CUSTOM YOGA MAT PACKAGING BOX',
		'slug' => 'custom-yoga-mat-packaging-box',
		'keyword' => 'yoga mat packaging box',
		'category_slug' => 'sports-packaging-boxes',
		'category_url' => '/product-category/sports-packaging-boxes/',
		'category_anchor' => 'sports packaging boxes',
		'related_url' => '/product/custom-sportswear-packaging-box/',
		'related_anchor' => 'sportswear packaging boxes',
		'buyers' => 'yoga mat brands, wellness retailers, fitness equipment suppliers, studio merchandise teams, and private label sports companies',
		'applications' => 'rolled yoga mats, pilates mats, fitness mats, travel mats, wellness gift sets, and e-commerce exercise equipment',
		'problem' => 'packaging a long rolled product without crushing corners, hiding texture details, or making the box oversized for shipping',
		'structures' => 'long folding cartons, tuck-end boxes, sleeve cartons, corrugated mailer boxes, window boxes, and rigid gift boxes for premium mats',
		'insert_need' => 'rolled mat stability, strap or instruction card space, texture visibility, end protection, and easy product removal',
		'materials' => 'corrugated board, kraft paperboard, ivory board, coated paperboard, rigid greyboard, and optional PET window film',
		'artwork_needs' => 'mat size, thickness, material, texture, care information, colorway, barcode, QR code, and wellness brand message',
		'difference' => 'long product dimensions, roll stability, texture communication, and e-commerce carton efficiency',
		'feature' => 'Long yoga mat box, rolled product support, wellness retail branding, custom size structure',
		'industrial' => 'Yoga, Fitness Equipment, Wellness Retail, E-commerce',
		'paper' => 'Corrugated Board / Kraft Paper / Ivory Board / Coated Paper / Rigid Board',
		'box_type' => 'Yoga Mat Packaging Box',
		'shape' => 'Long Rectangle / Customized Shape',
		'accessories' => 'Paper insert / Strap space / Window / Handle / Instruction card slot',
		'liner' => 'Corrugated support / Paperboard insert / No liner',
		'colors' => 'White / Green / Kraft / CMYK / Pantone / Customized Color',
		'opening_heading' => 'Yoga Mat Packaging Box for Wellness and Fitness Brands',
		'problem_heading' => 'Yoga Mat Packaging Problems Before Sampling',
		'structure_heading' => 'Long Box Structure for Rolled Mats',
		'insert_heading' => 'Rolled Mat Support and Accessory Space',
		'material_heading' => 'Corrugated, Kraft, and Retail Board Options',
		'artwork_heading' => 'Yoga Mat Size, Texture, and Wellness Branding',
		'use_heading' => 'Yoga Mat Packaging Applications and Related Apparel Boxes',
		'qc_heading' => 'Quality Control for Long Fitness Packaging',
		'quote_heading' => 'Quote Information for Yoga Mat Box Orders',
		'images' => array(
			'custom-yoga-mat-packaging-box-hero-shot.webp',
			'custom-yoga-mat-packaging-box-open-box-shot.webp',
			'custom-yoga-mat-packaging-box-detail-close-up.webp',
			'custom-yoga-mat-packaging-box-full-packaging-set.webp',
		),
		'captions' => array(
			'Custom yoga mat packaging box for wellness and fitness brands.',
			'Open yoga mat box showing rolled product access and structure.',
			'Close-up detail for yoga mat packaging material and print finish.',
			'Full yoga mat packaging set for retail and e-commerce programs.',
		),
	),
);

$audit = array( '# Latest Product Image Import Audit', '' );

foreach ( $products as $product ) {
	$category = get_term_by( 'slug', $product['category_slug'], 'product_cat' );
	if ( ! $category || is_wp_error( $category ) ) {
		echo 'Missing product category: ' . $product['category_slug'] . PHP_EOL;
		continue;
	}

	$image_ids = array();
	foreach ( $product['images'] as $index => $filename ) {
		$image_ids[] = vpn_latest_product_attachment_id(
			$filename,
			ucwords( str_replace( '-', ' ', $product['keyword'] ) ) . ' for ' . strtolower( $product['industrial'] ),
			$product['captions'][ $index ] ?? $product['title']
		);
	}

	if ( count( array_filter( $image_ids ) ) !== count( $product['images'] ) ) {
		echo 'Failed images: ' . $product['title'] . PHP_EOL;
		continue;
	}

	$existing = get_page_by_path( $product['slug'], OBJECT, 'product' );
	$postarr  = array(
		'post_type'    => 'product',
		'post_status'  => 'publish',
		'post_title'   => $product['title'],
		'post_name'    => $product['slug'],
		'post_excerpt' => $product['title'] . ' for ' . $product['applications'] . '. Customize size, structure, paper material, insert, logo printing, finishing, and bulk production from 1000 boxes.',
		'post_content' => vpn_latest_product_content( $product, $image_ids ),
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$product_id    = wp_update_post( $postarr );
	} else {
		$product_id = wp_insert_post( $postarr );
	}

	if ( is_wp_error( $product_id ) || ! $product_id ) {
		echo 'Failed product: ' . $product['title'] . PHP_EOL;
		continue;
	}

	foreach ( $image_ids as $image_id ) {
		wp_update_post(
			array(
				'ID'          => $image_id,
				'post_parent' => $product_id,
			)
		);
	}

	wp_set_object_terms( $product_id, array( (int) $category->term_id ), 'product_cat', false );
	wp_set_object_terms(
		$product_id,
		array( $product['keyword'], 'custom paper box', 'custom packaging', $product['category_anchor'] ),
		'product_tag'
	);

	set_post_thumbnail( $product_id, $image_ids[0] );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $image_ids, 1 ) ) );
	update_post_meta( $product_id, '_sku', 'sample-latest-' . $product['slug'] );
	update_post_meta( $product_id, '_regular_price', '' );
	update_post_meta( $product_id, '_price', '' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_latest_product_specs( $product ) );
	update_post_meta( $product_id, '_vpn_sample_import', $marker );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $product['keyword'] );
	update_post_meta( $product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER' );
	update_post_meta( $product_id, 'rank_math_description', substr( $product['title'] . ' for ' . $product['applications'] . ', customized with paper structure, insert, logo printing and finishing.', 0, 154 ) );

	$words   = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ) );
	$audit[] = '## ' . $product['title'];
	$audit[] = '- ID: ' . $product_id;
	$audit[] = '- URL: ' . get_permalink( $product_id );
	$audit[] = '- Category: ' . $product['category_slug'];
	$audit[] = '- Focus keyword: ' . $product['keyword'];
	$audit[] = '- Words: ' . $words;
	$audit[] = '- Images: ' . count( $image_ids );
	$audit[] = '- Source files: ' . implode( ', ', $product['images'] );
	$audit[] = '';

	echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . PHP_EOL;
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-latest-20260624-audit.md', implode( PHP_EOL, $audit ) );
echo 'Latest product image import complete.' . PHP_EOL;
