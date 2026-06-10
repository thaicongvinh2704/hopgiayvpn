<?php
/**
 * Import the June 2026 sports packaging product set.
 *
 * Run:
 *   php tools/import-sports-packaging-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_sports_link( string $url, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_sports_attachment_id( string $filename, string $alt, string $title ): int {
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
				'ID'         => $attachment_id,
				'post_title' => $title,
			)
		);

		return $attachment_id;
	}

	$uploads     = wp_get_upload_dir();
	$target_path = trailingslashit( $uploads['basedir'] ) . $attached_file;
	$source_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $attached_file;

	if ( ! file_exists( $target_path ) ) {
		if ( ! file_exists( $source_path ) || ! wp_mkdir_p( dirname( $target_path ) ) || ! copy( $source_path, $target_path ) ) {
			return 0;
		}
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

function vpn_sports_specs( array $product ): array {
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

function vpn_sports_section( string $heading, array $paragraphs ): string {
	$html = '<h2>' . esc_html( $heading ) . '</h2>';
	foreach ( $paragraphs as $paragraph ) {
		$html .= '<p>' . $paragraph . '</p>';
	}
	return $html;
}

function vpn_sports_inline_image( int $image_id, string $caption, bool $narrow = false ): string {
	$image = wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'lazy' ) );
	if ( ! $image ) {
		return '';
	}

	return '<figure class="product-inline-figure product-inline-figure-small' . ( $narrow ? ' is-narrow' : '' ) . '">' .
		$image . '<figcaption>' . esc_html( $caption ) . '</figcaption></figure>';
}

function vpn_sports_content( array $product, array $image_ids ): string {
	$category_link = vpn_sports_link( '/product-category/sports-packaging-boxes/', 'sports packaging boxes' );
	$materials_link = vpn_sports_link( '/paper-materials-for-custom-paper-boxes/', 'paper material options' );
	$quote_link = vpn_sports_link( '/contact/#quote', 'request a custom packaging quotation' );
	$related_link = vpn_sports_link( $product['related_url'], $product['related_anchor'] );

	$html  = vpn_sports_section(
		$product['heading'],
		array(
			$product['title'] . ' is developed for ' . $product['audience'] . ' that need reliable packaging for ' . $product['applications'] . '. The box must protect the product, present the brand clearly, and remain practical for packing, warehousing, retail display, and e-commerce delivery. Buyers normally review product dimensions, total packed weight, opening style, board strength, insert fit, artwork position, barcode area, and shipping method before approving the structure.',
			'This product belongs to our ' . $category_link . ' collection. It can be customized for private label launches, wholesale programs, retail chains, online stores, promotional campaigns, tournament merchandise, and OEM or ODM orders. The packaging is planned around the real item instead of using a generic carton, helping the final pack look intentional while reducing movement, crushed corners, surface rubbing, and inconsistent presentation.',
		)
	);
	$html .= vpn_sports_section(
		$product['structure_heading'],
		array(
			'Recommended structures include ' . $product['structures'] . '. The correct choice depends on whether the pack needs a quick tuck closure, a premium unboxing experience, a hanging retail tab, a removable lid, or stronger protection for a heavier set. During sampling, the product should be placed inside the box so the team can check clearance, opening force, insert depth, finger access, and the way the item returns to position after transport.',
			'Structural details can be adjusted for manual packing or production-line packing. Dust flaps, thumb notches, locking tabs, reinforced corners, sleeves, trays, and internal supports should be tested before mass production. A well-fitted structure uses material efficiently and gives distributors a repeatable pack that is easier to count, inspect, label, and place into export cartons.',
		)
	);
	if ( ! empty( $image_ids[1] ) ) {
		$html .= vpn_sports_inline_image( $image_ids[1], $product['captions'][1] ?? $product['title'], true );
	}
	$html .= vpn_sports_section(
		$product['material_heading'],
		array(
			'Material options include ' . $product['materials'] . '. Folding paperboard is suitable for lightweight retail packs, corrugated board adds shipping strength, and rigid greyboard supports premium sets that need a controlled reveal. Buyers can compare board characteristics in our ' . $materials_link . ' guide. The final caliper should be selected after considering packed weight, box dimensions, stacking conditions, humidity, transport distance, and the amount of printed coverage.',
			'Surface treatment can combine matte or gloss lamination, anti-scratch film, foil stamping, embossing, debossing, spot UV, and Pantone color matching. Sports packaging often uses high-contrast graphics and performance claims, so registration, color consistency, small text, icons, and finish alignment need careful proofing. Export packaging should also resist scuffing while boxes are packed, stacked, and moved through distribution.',
		)
	);
	$html .= vpn_sports_section(
		$product['insert_heading'],
		array(
			'Insert planning is based on ' . $product['insert_need'] . '. Options may include folded paperboard, corrugated partitions, molded pulp, EVA foam, EPE foam, tissue paper, or a combination of supports. The insert should hold the product without applying harmful pressure, and it should leave enough finger space for customers to remove the item naturally.',
			'For sets with several components, every cavity should have a clear purpose and stable position. Accessories can be separated to prevent scratches and to make inventory checks faster. A physical prototype is recommended because a digital dieline cannot fully show compression, friction, balance, or the customer experience when the package is opened.',
		)
	);
	if ( ! empty( $image_ids[2] ) ) {
		$html .= vpn_sports_inline_image( $image_ids[2], $product['captions'][2] ?? $product['title'] );
	}
	$html .= vpn_sports_section(
		$product['branding_heading'],
		array(
			'Customization can cover ' . $product['customization'] . '. Artwork should maintain a clear hierarchy between the brand, product name, model, size, quantity, performance features, care information, compliance marks, and barcode. Important claims should remain readable at normal retail distance, while technical details can be organized on side or back panels.',
			'Colorways and SKU variations can share one structural dieline while using controlled artwork changes. This helps brands manage several sizes or models without rebuilding every box. A version table, approved color references, barcode list, and final print-ready files reduce errors when the order includes multiple variants.',
		)
	);
	$html .= vpn_sports_section(
		$product['application_heading'],
		array(
			'Typical uses include ' . $product['applications'] . '. This page focuses on ' . $product['difference'] . ', so its structure and content are different from general activewear packaging. Buyers comparing nearby options can also review ' . $related_link . ' to choose the most suitable format for another product line.',
			'Retail packs prioritize shelf communication and clean front-facing graphics, while e-commerce packs may need more corner strength and abrasion resistance. Premium gift or equipment sets usually benefit from a rigid structure and fitted insert. Defining the sales channel early allows the supplier to balance presentation, protection, material use, packing speed, and shipping volume.',
		)
	);
	if ( ! empty( $image_ids[3] ) ) {
		$html .= vpn_sports_inline_image( $image_ids[3], $product['captions'][3] ?? $product['title'], true );
	}
	$html .= vpn_sports_section(
		$product['quality_heading'],
		array(
			'Quality control should cover material thickness, dimensions, print color, coating, finishing position, gluing strength, folding accuracy, insert fit, opening action, and packed-product appearance. Samples can be reviewed with actual products before production, followed by inline inspections and a final check against the approved specification.',
			'For international orders, the buyer should also confirm export carton quantity, carton marks, pallet requirements, humidity protection, and acceptable inspection criteria. Clear tolerances help both sides evaluate production consistently and reduce disputes caused by subjective expectations.',
		)
	);
	$html .= vpn_sports_section(
		$product['b2b_heading'],
		array(
			'For B2B sourcing, useful quotation information includes product dimensions, packed weight, required quantity, target market, preferred box style, material preference, print colors, finishes, insert requirements, and desired delivery date. A complete brief makes it easier to compare structural options and estimate tooling, sampling, production, and shipping costs.',
			'VPN supports customized size, structure, printing, finishing, inserts, and bulk production from 1000 boxes. Share the product measurements, reference images, artwork, and order plan to ' . $quote_link . '. We can prepare a packaging recommendation and sample plan for the specific sports product rather than applying a one-size-fits-all carton.',
		)
	);

	return $html;
}

$category_slug = 'sports-packaging-boxes';
$category_name = 'Sports Packaging Boxes';
$marker        = 'product-samples-sports-packaging';

$products = array(
	array(
		'title' => 'CUSTOM SPORTS SHOE PACKAGING BOX',
		'slug' => 'custom-sports-shoe-packaging-box',
		'keyword' => 'sports shoe packaging box',
		'heading' => 'Sports Shoe Packaging Box for Footwear Brands',
		'structure_heading' => 'Shoe Box Structure and Footwear Protection',
		'material_heading' => 'Paperboard Materials for Sports Shoe Boxes',
		'insert_heading' => 'Tissue and Internal Support for Footwear',
		'branding_heading' => 'Custom Shoe Size Labels and Athletic Branding',
		'application_heading' => 'Sports Footwear Packaging Applications',
		'quality_heading' => 'Quality Control for Shoe Packaging',
		'b2b_heading' => 'B2B Sports Shoe Box Sourcing',
		'audience' => 'sports shoe brands, sneaker factories, footwear distributors, athletic retailers, and private label suppliers',
		'applications' => 'running shoes, training shoes, court shoes, sneakers, team footwear, limited editions, and athletic footwear collections',
		'structures' => 'lid-and-base shoe boxes, hinged-lid cartons, corrugated mailers, drawer boxes, and rigid footwear gift boxes',
		'materials' => 'duplex board, ivory paper, kraft paper, corrugated board, rigid greyboard, and specialty wrapping paper',
		'insert_need' => 'pair separation, toe and heel protection, tissue wrapping, shape retention, and efficient shoe removal',
		'customization' => 'shoe size labels, model codes, colorway stickers, barcode panels, ventilation holes, tissue paper, collection graphics, and custom logo finishes',
		'difference' => 'footwear weight, pair organization, shoe-size identification, and strong stacking performance',
		'related_url' => '/product/custom-shoe-packaging-box/',
		'related_anchor' => 'general custom shoe packaging boxes',
		'feature' => 'Strong footwear box, shoe size label area, stackable structure, athletic retail branding',
		'industrial' => 'Sports Footwear, Sneakers, Athletic Retail, E-commerce',
		'paper' => 'Duplex Board / Ivory Paper / Kraft Paper / Corrugated Board / Rigid Board',
		'box_type' => 'Sports Shoe Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Tissue paper / Paper insert / Finger notch / Label area / Sleeve',
		'liner' => 'Tissue paper / Paperboard support / Corrugated insert',
		'colors' => 'Black / White / Navy / CMYK / Pantone / Customized Color',
		'images' => array(
			'custom-sports-shoe-packaging-box-01-hero.webp',
			'custom-sports-shoe-packaging-box-02-angle-view.webp',
			'custom-sports-shoe-packaging-box-03-open-box.webp',
			'custom-sports-shoe-packaging-box-04-detail-closeup.webp',
		),
		'captions' => array(
			'Custom sports shoe packaging box for athletic footwear brands.',
			'Angled view showing the stackable sports footwear box structure.',
			'Open sports shoe box with branded interior presentation.',
			'Close-up of printing and structural details on the footwear box.',
		),
		'alt' => 'Custom sports shoe packaging box for athletic footwear',
	),
	array(
		'title' => 'PREMIUM PICKLEBALL SET RIGID PAPER BOX',
		'slug' => 'premium-pickleball-set-rigid-paper-box',
		'keyword' => 'pickleball set packaging box',
		'heading' => 'Premium Pickleball Set Packaging Box with Fitted Insert',
		'structure_heading' => 'Rigid Box Structure for Pickleball Equipment Sets',
		'material_heading' => 'Premium Materials for Pickleball Gift Boxes',
		'insert_heading' => 'Custom Insert Layout for Paddles and Accessories',
		'branding_heading' => 'Premium Pickleball Branding and Finishing',
		'application_heading' => 'Pickleball Set and Sports Gift Applications',
		'quality_heading' => 'Quality Control for Multi-Component Sports Sets',
		'b2b_heading' => 'B2B Pickleball Packaging Sourcing',
		'audience' => 'pickleball brands, sports equipment suppliers, tournament organizers, premium retailers, and corporate gift buyers',
		'applications' => 'pickleball paddle pairs, balls, grips, protective covers, accessory kits, tournament gifts, and premium starter sets',
		'structures' => 'rigid hinged boxes, lid-and-base gift boxes, magnetic closure boxes, book-style boxes, and presentation cases with fitted trays',
		'materials' => 'rigid greyboard, coated art paper, specialty paper, EVA foam, EPE foam, molded pulp, and fabric-covered inserts',
		'insert_need' => 'separate paddle cavities, ball retention, accessory organization, handle clearance, and a balanced premium reveal',
		'customization' => 'paddle-shaped cavities, ball slots, accessory wells, magnetic closure, foil logo, inner-lid printing, ribbon lift, and tournament branding',
		'difference' => 'a heavy multi-component equipment set, precise insert engineering, and premium gift presentation',
		'related_url' => '/product/custom-sportswear-packaging-box/',
		'related_anchor' => 'sportswear packaging for athletic apparel',
		'feature' => 'Rigid sports gift box, fitted pickleball insert, multi-component layout, premium unboxing',
		'industrial' => 'Pickleball, Sports Equipment, Tournament Gifts, Premium Retail',
		'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / EVA Foam / Molded Pulp',
		'box_type' => 'Pickleball Set Rigid Paper Box',
		'shape' => 'Rectangle / Customized Set Layout',
		'accessories' => 'EVA insert / Foam tray / Ribbon lift / Magnetic closure / Accessory wells',
		'liner' => 'EVA foam / EPE foam / Molded pulp / Paperboard tray',
		'colors' => 'Navy / Black / White / CMYK / Pantone / Customized Color',
		'images' => array(
			'premium-pickleball-set-rigid-paper-box-01-hero.webp',
			'premium-pickleball-set-rigid-paper-box-02-angle-view.webp',
			'premium-pickleball-set-rigid-paper-box-03-open-box-with-foam-insert.webp',
			'premium-pickleball-set-rigid-paper-box-04-detail-closeup.webp',
		),
		'captions' => array(
			'Premium pickleball set rigid paper box with paddles and accessories.',
			'Angled view of the closed premium pickleball presentation box.',
			'Open pickleball gift box with fitted foam insert and organized components.',
			'Close-up of the rigid box finish, insert, and branded sports accessories.',
		),
		'alt' => 'Premium pickleball set rigid paper packaging box',
	),
	array(
		'title' => 'CUSTOM KNEE SUPPORT PACKAGING BOX',
		'slug' => 'custom-knee-support-packaging-box',
		'keyword' => 'knee support packaging box',
		'heading' => 'Knee Support Packaging Box for Sports Retail',
		'structure_heading' => 'Retail Carton Structure for Knee Supports',
		'material_heading' => 'Paperboard and Window Options for Support Packaging',
		'insert_heading' => 'Folded Product Support and Retail Hanging Details',
		'branding_heading' => 'Performance Claims, Sizing, and Product Information',
		'application_heading' => 'Athletic Support Packaging Applications',
		'quality_heading' => 'Quality Control for Hanging Retail Cartons',
		'b2b_heading' => 'B2B Knee Support Packaging Sourcing',
		'audience' => 'sports support brands, orthopedic product suppliers, fitness retailers, pharmacies, and private label manufacturers',
		'applications' => 'compression knee sleeves, hinged knee braces, patella supports, recovery supports, training accessories, and sports protection products',
		'structures' => 'tuck-end folding cartons, hanging retail boxes, sleeve cartons, window boxes, and compact mailer cartons',
		'materials' => 'ivory board, duplex board, kraft paper, coated paperboard, PET window film, and corrugated micro-flute board',
		'insert_need' => 'neat product folding, controlled compression, size visibility, instruction leaflet space, and stable hanging display',
		'customization' => 'hang tabs, size charts, compression icons, support-level indicators, usage illustrations, QR codes, barcodes, windows, and tamper seals',
		'difference' => 'technical sizing, wearable support claims, compact folding, and clear front-panel retail communication',
		'related_url' => '/product/custom-sportswear-packaging-box/',
		'related_anchor' => 'sportswear packaging boxes for activewear',
		'feature' => 'Hanging retail carton, knee support sizing, performance icons, compact product fit',
		'industrial' => 'Sports Supports, Fitness Accessories, Orthopedic Retail, Pharmacy',
		'paper' => 'Ivory Board / Duplex Board / Kraft Paper / Coated Paper / PET Window',
		'box_type' => 'Knee Support Packaging Box',
		'shape' => 'Vertical Rectangle / Customized Shape',
		'accessories' => 'Hang tab / Instruction leaflet / PET window / Size label / Tamper seal',
		'liner' => 'Paperboard support / No liner / Folded product wrap',
		'colors' => 'Black / White / Green / CMYK / Pantone / Customized Color',
		'images' => array(
			'custom-knee-support-packaging-box-front.webp',
			'custom-knee-support-packaging-box-front-1.webp',
			'custom-knee-support-packaging-box-front-3.webp',
			'custom-knee-support-packaging-box-front-4.webp',
		),
		'captions' => array(
			'Custom knee support packaging box with hanging retail structure.',
			'Knee sleeve carton with performance benefits and product imagery.',
			'Sports knee support box showing sizing and technical information.',
			'Alternate knee support packaging design for athletic retail display.',
		),
		'alt' => 'Custom knee support packaging box for sports retail',
	),
	array(
		'title' => 'CUSTOM SPORTS UNDERWEAR PACKAGING BOX',
		'slug' => 'custom-sports-underwear-packaging-box',
		'keyword' => 'sports underwear packaging box',
		'heading' => 'Sports Underwear Packaging Box for Performance Apparel',
		'structure_heading' => 'Retail Carton Structure for Performance Underwear',
		'material_heading' => 'Paper Materials for Sports Apparel Boxes',
		'insert_heading' => 'Folded Garment Support and Multi-Pack Organization',
		'branding_heading' => 'Size, Fabric, and Performance Feature Communication',
		'application_heading' => 'Sports Underwear and Base Layer Applications',
		'quality_heading' => 'Quality Control for Apparel Retail Boxes',
		'b2b_heading' => 'B2B Sports Underwear Packaging Sourcing',
		'audience' => 'performance underwear brands, activewear factories, gym retailers, sporting goods chains, and private label apparel suppliers',
		'applications' => 'performance boxer briefs, compression shorts, sports bras, base layers, training underwear, running apparel, and multi-pack garments',
		'structures' => 'hanging folding cartons, tuck-end apparel boxes, sleeve boxes, window cartons, multi-pack boxes, and compact e-commerce cartons',
		'materials' => 'ivory board, duplex board, kraft paper, coated paperboard, PET window film, and recycled folding carton stock',
		'insert_need' => 'consistent garment folding, size separation, multi-pack organization, fabric protection, and easy product removal',
		'customization' => 'size panels, fabric composition, moisture-wicking icons, support level, stretch details, care symbols, windows, barcodes, and colorway labels',
		'difference' => 'performance fabric communication, intimate apparel presentation, size clarity, and efficient multi-SKU retail display',
		'related_url' => '/product/custom-men-underwear-packaging-box/',
		'related_anchor' => 'men underwear packaging boxes for general apparel',
		'feature' => 'Performance apparel carton, size communication, fabric benefit icons, hanging retail display',
		'industrial' => 'Sports Underwear, Activewear, Performance Apparel, Retail',
		'paper' => 'Ivory Board / Duplex Board / Kraft Paper / Coated Paper / Recycled Paperboard',
		'box_type' => 'Sports Underwear Packaging Box',
		'shape' => 'Vertical Rectangle / Customized Shape',
		'accessories' => 'Hang tab / PET window / Size label / Insert card / Tamper seal',
		'liner' => 'Paperboard divider / No liner / Tissue paper',
		'colors' => 'Black / White / Blue / CMYK / Pantone / Customized Color',
		'images' => array(
			'custom-sports-underwear-packaging-box-front.webp',
			'custom-sports-underwear-packaging-box-front-2.webp',
			'custom-sports-underwear-packaging-box-front-3.webp',
			'custom-sports-underwear-packaging-box-front-4.webp',
			'custom-sports-underwear-packaging-box-front-5.webp',
			'custom-sports-underwear-packaging-box-front-6.webp',
		),
		'captions' => array(
			'Custom sports underwear packaging box for performance boxer briefs.',
			'Performance underwear carton with sizing and fabric feature information.',
			'Sports base layer packaging with bold athletic retail graphics.',
			'Custom active underwear box for hanging store display.',
			'Performance apparel carton with alternate color and product layout.',
			'Sports underwear retail box with brand, size, and benefit communication.',
		),
		'alt' => 'Custom sports underwear packaging box for performance apparel',
	),
);

$category = get_term_by( 'slug', $category_slug, 'product_cat' );
if ( ! $category || is_wp_error( $category ) ) {
	fwrite( STDERR, 'Missing product category: ' . $category_slug . PHP_EOL );
	exit( 1 );
}

$audit = array( '# Sports Packaging Product Import Audit', '' );

foreach ( $products as $product ) {
	$image_ids = array();
	foreach ( $product['images'] as $index => $filename ) {
		$image_ids[] = vpn_sports_attachment_id(
			$filename,
			$product['alt'],
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
		'post_excerpt' => $product['title'] . ' is a custom paper packaging solution for ' . $product['applications'] . '. Available with custom size, structure, insert, printing, finishing, and bulk production from 1000 boxes.',
		'post_content' => vpn_sports_content( $product, $image_ids ),
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

	wp_set_object_terms( $product_id, array( (int) $category->term_id ), 'product_cat', false );
	wp_set_object_terms(
		$product_id,
		array( $product['keyword'], 'sports packaging', 'custom paper box', 'custom packaging' ),
		'product_tag'
	);

	set_post_thumbnail( $product_id, $image_ids[0] );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $image_ids, 1 ) ) );
	update_post_meta( $product_id, '_sku', 'sample-sports-' . $product['slug'] );
	update_post_meta( $product_id, '_regular_price', '' );
	update_post_meta( $product_id, '_price', '' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_sports_specs( $product ) );
	update_post_meta( $product_id, '_vpn_sample_import', $marker );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $product['keyword'] );
	update_post_meta( $product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER' );
	update_post_meta( $product_id, 'rank_math_description', $product['title'] . ' for ' . $product['applications'] . ', customized with structure, insert, logo printing, finishing, and bulk production.' );

	$words   = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ) );
	$audit[] = '## ' . $product['title'];
	$audit[] = '- ID: ' . $product_id;
	$audit[] = '- URL: ' . get_permalink( $product_id );
	$audit[] = '- Category: ' . $category_name;
	$audit[] = '- Focus keyword: ' . $product['keyword'];
	$audit[] = '- Words: ' . $words;
	$audit[] = '- Images: ' . count( $image_ids );
	$audit[] = '- Source files: ' . implode( ', ', $product['images'] );
	$audit[] = '';

	echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . PHP_EOL;
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-sports-packaging-audit.md', implode( PHP_EOL, $audit ) );
echo 'Sports packaging product import complete.' . PHP_EOL;
