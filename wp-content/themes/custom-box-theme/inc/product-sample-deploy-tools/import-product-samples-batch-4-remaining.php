<?php
/**
 * Import the remaining product samples from unused upload image groups.
 *
 * Usage:
 *   php tools/import-product-samples-batch-4-remaining.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_b4_link( string $url, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_b4_attachment_id( string $relative_path ): int {
	$uploads       = wp_get_upload_dir();
	$base_dir      = str_replace( '\\', '/', $uploads['basedir'] );
	$base_url      = str_replace( '\\', '/', $uploads['baseurl'] );
	$file_path     = ABSPATH . $relative_path;

	if ( ! file_exists( $file_path ) ) {
		return 0;
	}

	$attached_file = ltrim( str_replace( $base_dir, '', str_replace( '\\', '/', $file_path ) ), '/' );
	$file_url      = trailingslashit( $base_url ) . $attached_file;
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

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			's'              => preg_replace( '/\.[^.]+$/', '', basename( $relative_path ) ),
		)
	);

	if ( $existing ) {
		update_post_meta( (int) $existing[0], '_wp_attached_file', $attached_file );
		return (int) $existing[0];
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => $file_url,
			'post_mime_type' => wp_check_filetype( $file_path )['type'] ?? 'image/webp',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_status'    => 'inherit',
		),
		$file_path
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attached_file', $attached_file );
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file_path ) );

	return (int) $attachment_id;
}

function vpn_b4_inline_images( array $p, array $image_ids ): string {
	$out   = '';
	$limit = min( 4, count( $image_ids ) );

	for ( $i = 0; $i < $limit; $i++ ) {
		if ( empty( $image_ids[ $i ] ) ) {
			continue;
		}

		$image = wp_get_attachment_image( $image_ids[ $i ], 'large', false, array( 'loading' => 'lazy' ) );
		if ( ! $image ) {
			continue;
		}

		$out .= '<figure class="product-inline-figure product-inline-figure-small' . ( $i % 2 ? ' is-narrow' : '' ) . '">';
		$out .= $image;
		$out .= '<figcaption>' . esc_html( $p['captions'][ $i ] ?? $p['captions'][0] ) . '</figcaption>';
		$out .= '</figure>';
	}

	return $out;
}

function vpn_b4_specs( array $p ): array {
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
		array( 'label' => 'Printing Handling', 'value' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Spot UV, Matte Lamination, Glossy Lamination' ),
		array( 'label' => 'Color', 'value' => $p['colors'] ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $p['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement" ),
	);
}

function vpn_b4_section( string $heading, array $paragraphs ): string {
	$html = '<h2>' . esc_html( $heading ) . '</h2>';
	foreach ( $paragraphs as $paragraph ) {
		$html .= '<p>' . $paragraph . '</p>';
	}
	return $html;
}

function vpn_b4_content( array $p, array $image_ids ): string {
	$category_link = vpn_b4_link( '/packaging/' . $p['category_slug'] . '/', strtolower( $p['category_name'] ) );
	$quote_link    = vpn_b4_link( '/contact/#quote', 'request a custom packaging quotation' );
	$material_link = vpn_b4_link( '/paper-materials-for-custom-paper-boxes/', 'paper material options for custom paper boxes' );

	$html  = vpn_b4_section(
		$p['heading'],
		array(
			$p['title'] . ' is developed for ' . $p['audience'] . ' that need packaging planned around ' . $p['core_need'] . '. Buyers compare more than the outside artwork: they look at product fit, insert depth, print accuracy, packing speed, shelf display, and export protection before approving a custom paper packaging order. This page is written specifically for ' . strtolower( $p['keyword'] ) . ', so the content stays separate from earlier gift, food, electronics, stationery, medicine, wine, wellness, and lifestyle box pages.',
			'The product belongs in our ' . $category_link . ' category, but the value is defined by the details of this exact structure. Brands can use the box for launch kits, wholesale retail programs, distributor catalogs, trade show samples, e-commerce fulfillment, or OEM/ODM projects where one packaging family must support several SKUs without losing brand consistency.',
		)
	);

	$html .= vpn_b4_inline_images( $p, $image_ids );

	$html .= vpn_b4_section(
		$p['structure_heading'],
		array(
			'The structure can be produced as ' . $p['structures'] . '. The right choice depends on how the buyer wants the customer to open the package, whether the product needs a reveal moment, how much compression resistance is required, and whether the box will be reused after purchase. For fragile, heavy, or multi-piece products, the outer board and inner insert should be planned together rather than treated as separate decisions.',
			'Opening tolerance is especially important for this product. A drawer should slide smoothly without feeling loose, a lid-and-base box should lift cleanly without scraping, and a folding carton should close securely after packing. During sampling, we recommend checking product movement, corner strength, side-wall stiffness, barcode position, and whether the package can be packed efficiently by a fulfillment team.',
		)
	);

	$html .= vpn_b4_section(
		$p['material_heading'],
		array(
			'Common material choices include ' . $p['materials'] . '. The paper material should support the product weight, the artwork style, and the sales channel. A smooth coated sheet is better for clean color and small text, kraft paper can support natural branding, corrugated board improves crush resistance, and rigid board gives a stronger premium hand feel.',
			'For buyers comparing board strength, coating, print surface, and insert compatibility, the ' . $material_link . ' guide can help narrow down the specification before sampling. Material thickness can be adjusted for 1000 boxes or larger bulk orders, but the sample should always be checked with the real product, not only with a drawing or estimated size.',
		)
	);

	$html .= vpn_b4_inline_images( array_merge( $p, array( 'captions' => array_slice( $p['captions'], 1 ) ) ), array_slice( $image_ids, 1 ) );

	$html .= vpn_b4_section(
		$p['application_heading'],
		array(
			'This packaging is suitable for ' . $p['applications'] . '. The application list matters because the same visual style can require different structures when the product weight, product surface, or packing method changes. A glass item may need a stronger insert, a retail product may need clearer front-panel communication, and a gift set may need a cleaner reveal sequence.',
			'For distributors and private label buyers, application planning also helps organize SKU ranges. A brand can keep one packaging language while changing tray cavities, label areas, printed product names, size marks, or accessory compartments. This reduces redesign work while keeping each product page and each package relevant to the actual item being sold.',
		)
	);

	$html .= vpn_b4_section(
		$p['custom_heading'],
		array(
			'Customization can include ' . $p['customization'] . '. The dieline can reserve space for barcode, QR code, batch number, model label, warning information, certification marks, or retail claims when needed. If the product is part of a line, color coding and icon systems can help customers compare variants without confusing the brand identity.',
			'At the sampling stage, buyers should confirm at least eight product-specific details: exact product dimensions, product weight, packing direction, insert depth, logo area, front-panel information, carton quantity, and export carton layout. These details reduce mistakes before mass production and make the quotation more accurate for international B2B orders.',
		)
	);

	$html .= vpn_b4_section(
		$p['printing_heading'],
		array(
			'Printing and finishing options include ' . $p['printing'] . '. Offset printing is suitable for accurate brand colors and clean packaging information. Foil stamping can highlight a premium logo, embossing adds tactile identity, spot UV can emphasize selected graphics, and matte or soft-touch lamination can make the surface feel more refined.',
			'The finish should serve the product rather than overload it. A technical product may need clear icons and restrained color, while a gift or beauty product can use more tactile finishing. For export packaging, the finish also needs to survive packing, stacking, and shipping so the box still looks professional when it reaches a retailer or distributor.',
		)
	);

	$html .= vpn_b4_inline_images( array_merge( $p, array( 'captions' => array_slice( $p['captions'], 2 ) ) ), array_slice( $image_ids, 2 ) );

	$html .= vpn_b4_section(
		$p['b2b_heading'],
		array(
			'For B2B customers, this product supports ' . $p['channels'] . '. The advantage of custom paper packaging is that structure, print, insert, and packing method can be aligned before production. This helps brands reduce product damage, improve shelf presentation, organize inventory, and create a consistent look across retail and online channels.',
			'Bulk production from 1000 boxes allows the project to balance unit cost and brand impact. OEM/ODM buyers can reuse the same construction across several markets, while distributors can request alternate artwork for different languages or sales channels. A consistent specification also makes repeat orders faster after the first sample is approved.',
		)
	);

	$html .= vpn_b4_section(
		$p['difference_heading'],
		array(
			'This page is intentionally different from the other product sample pages. The writing focuses on ' . $p['difference'] . ', not a general paper box description. That separation helps search engines and buyers understand why this product deserves its own packaging plan and its own quotation discussion.',
			'The visible image details, product use, inner fit, and buyer decision points all guide the copy. Instead of repeating the same list of paper materials and finishes, the page explains how those choices apply to ' . strtolower( $p['keyword'] ) . ' in a practical production setting.',
		)
	);

	$html .= vpn_b4_section(
		$p['process_heading'],
		array(
			'The usual order process starts with product dimensions, target quantity, reference image, and brand requirements. Our team can then suggest box style, insert type, paper material, and finishing options before making a sample. After sample approval, mass production follows printing, lamination, die cutting, folding, gluing, assembly, and export packing.',
			'For the most accurate quotation, send product photos, product size, expected quantity, shipping market, and any brand artwork already available. Buyers can also ask for a recommendation if they are unsure whether a rigid box, folding carton, drawer box, paper bag, tube, or corrugated structure is more suitable.',
		)
	);

	$html .= '<h3>' . esc_html( $p['cta_heading'] ) . '</h3>';
	$html .= '<p>Send your product size, target quantity, material preference, and branding requirements to ' . $quote_link . '. VPN Paper Box Manufacturer can recommend structure, insert, paper material, printing, finishing, and export packing options for your next custom packaging order.</p>';

	return $html;
}

$products = array(
	array(
		'title' => 'CUSTOM COSMETIC SKINCARE PACKAGING BOXES',
		'slug' => 'custom-cosmetic-skincare-packaging-boxes',
		'category_slug' => 'beauty-skincare-packaging',
		'category_name' => 'Beauty and Skincare Packaging',
		'keyword' => 'cosmetic skincare packaging boxes',
		'heading' => 'Cosmetic Skincare Packaging Boxes for Beauty Product Lines',
		'structure_heading' => 'Structures for Skincare Sets, Creams, and Retail Kits',
		'material_heading' => 'Beauty Paper Materials and Surface Feel',
		'application_heading' => 'Skincare Product Applications',
		'custom_heading' => 'Custom Sizing, Inserts, and Beauty Branding',
		'printing_heading' => 'Printing and Finishing for Cosmetic Packaging',
		'b2b_heading' => 'B2B Value for Beauty Brands',
		'difference_heading' => 'Why Skincare Packaging Needs Separate Content',
		'process_heading' => 'Cosmetic Box Sampling and Production Flow',
		'cta_heading' => 'Request a Cosmetic Skincare Packaging Quote',
		'audience' => 'skincare brands, cosmetic distributors, beauty salons, and private label manufacturers',
		'core_need' => 'clean shelf presentation, formula trust, and organized skincare line display',
		'structures' => 'folding cartons, sleeve boxes, rigid skincare boxes, drawer kits, and paperboard sets with inserts',
		'materials' => 'ivory paper, art paper, specialty paper, duplex board, kraft paper, and mounted rigid board',
		'applications' => 'face creams, serums, toners, cleansers, masks, ampoule sets, lotion bottles, and cosmetic trial kits',
		'customization' => 'formula labels, ingredient panels, shade colors, bottle inserts, logo placement, barcode area, and multilingual retail copy',
		'printing' => 'CMYK offset printing, Pantone color, gold foil, silver foil, embossing, debossing, spot UV, matte lamination, and soft-touch coating',
		'channels' => 'beauty retail, skincare launches, OEM cosmetic projects, influencer kits, salon distribution, and export packaging',
		'difference' => 'beauty line consistency, skincare information panels, formula variant planning, and retail shelf trust',
		'feature' => 'Skincare line display, cosmetic inserts, formula label areas, premium beauty finish',
		'industrial' => 'Beauty, Skincare, Cosmetics, Retail Packaging',
		'paper' => 'Ivory Paper / Art Paper / Specialty Paper / Duplex Board / Rigid Board',
		'box_type' => 'Cosmetic Skincare Packaging Box',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'Paper tray / EVA insert / Ribbon / Sleeve / Custom divider',
		'liner' => 'Paperboard insert / EVA insert / Foam insert / Custom tray',
		'colors' => 'Pink / White / Green / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-cosmetic-skincare-packaging-boxes.webp' ),
		'captions' => array( 'Cosmetic skincare packaging boxes for branded beauty product lines.' ),
		'alt' => 'Cosmetic skincare packaging boxes for beauty product retail display',
	),
	array(
		'title' => 'CUSTOM HOME LIFESTYLE PRODUCT PACKAGING BOXES',
		'slug' => 'custom-home-lifestyle-product-packaging-boxes',
		'category_slug' => 'home-lifestyle-packaging',
		'category_name' => 'Home and Lifestyle Packaging',
		'keyword' => 'home lifestyle product packaging boxes',
		'heading' => 'Home Lifestyle Product Packaging Boxes for Retail and Gift Sets',
		'structure_heading' => 'Box Structures for Homeware Presentation',
		'material_heading' => 'Paperboard Choices for Lifestyle Products',
		'application_heading' => 'Home and Lifestyle Applications',
		'custom_heading' => 'Custom Layouts for Mixed Homeware Products',
		'printing_heading' => 'Lifestyle Branding and Surface Finishing',
		'b2b_heading' => 'B2B Value for Homeware Suppliers',
		'difference_heading' => 'Why Lifestyle Packaging Needs Practical Detail',
		'process_heading' => 'Homeware Packaging Sampling Process',
		'cta_heading' => 'Request a Home Lifestyle Packaging Quote',
		'audience' => 'homeware brands, lifestyle retailers, gift suppliers, and distributor programs',
		'core_need' => 'warm product presentation, mixed item organization, and safe retail handling',
		'structures' => 'rigid boxes, lid-and-base boxes, drawer boxes, sleeve cartons, and folding cartons with dividers',
		'materials' => 'rigid board, kraft paper, art paper, coated paper, corrugated paper, and specialty textured paper',
		'applications' => 'home decor, candles, table accessories, small kitchenware, lifestyle gift sets, wellness goods, and boutique retail products',
		'customization' => 'compartment layout, product sleeves, hang tags, logo, brand color, instruction cards, and display-ready panels',
		'printing' => 'offset printing, natural kraft printing, foil logo, debossed brand mark, spot UV pattern, matte lamination, and textured paper wrapping',
		'channels' => 'homeware retail, lifestyle boutiques, promotional gifting, distributor catalogs, e-commerce kits, and export cartons',
		'difference' => 'mixed product fit, lifestyle shelf appeal, homeware protection, and calmer brand presentation',
		'feature' => 'Mixed product compartments, lifestyle retail branding, protective board structure',
		'industrial' => 'Homeware, Lifestyle, Gift, Retail Packaging',
		'paper' => 'Rigid Board / Kraft Paper / Art Paper / Corrugated Paper / Specialty Paper',
		'box_type' => 'Home Lifestyle Product Packaging Box',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'Paper divider / Insert tray / Sleeve / Handle / Custom card',
		'liner' => 'Paper insert / Corrugated divider / Foam insert / Custom tray',
		'colors' => 'Neutral / Green / Brown / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-home-lifestyle-product-packaging-boxes.webp' ),
		'captions' => array( 'Home lifestyle product packaging boxes for retail and gift set presentation.' ),
		'alt' => 'Home lifestyle product packaging boxes for retail gift sets',
	),
	array(
		'title' => 'CUSTOM PHARMACEUTICAL MEDICINE PACKAGING BOXES',
		'slug' => 'custom-pharmaceutical-medicine-packaging-boxes',
		'category_slug' => 'pharmaceutical-packaging-boxes',
		'category_name' => 'Pharmaceutical Packaging Boxes',
		'keyword' => 'pharmaceutical medicine packaging boxes',
		'heading' => 'Pharmaceutical Medicine Packaging Boxes for Healthcare Products',
		'structure_heading' => 'Medicine Carton Structures and Information Panels',
		'material_heading' => 'Paper Materials for Healthcare Packaging',
		'application_heading' => 'Pharmaceutical Product Applications',
		'custom_heading' => 'Custom Dosage Panels, Labels, and Inserts',
		'printing_heading' => 'Clean Printing for Medical Packaging',
		'b2b_heading' => 'B2B Value for Medicine Suppliers',
		'difference_heading' => 'Why Medicine Packaging Needs Regulated Detail',
		'process_heading' => 'Medicine Box Sampling and Print Review',
		'cta_heading' => 'Request a Pharmaceutical Packaging Quote',
		'audience' => 'medicine brands, healthcare distributors, clinics, and pharmaceutical OEM suppliers',
		'core_need' => 'clear product information, dosage communication, and organized retail pharmacy presentation',
		'structures' => 'tuck-end cartons, folding medicine boxes, sleeve cartons, small rigid kits, and cartons with insert leaflets',
		'materials' => 'ivory paper, SBS paperboard, duplex board, coated paper, kraft paper, and corrugated microflute for shipping support',
		'applications' => 'medicine bottles, blister packs, tablets, capsules, healthcare kits, medical samples, ointments, and clinic products',
		'customization' => 'dosage information, batch area, expiry panel, barcode, QR code, leaflet pocket, tamper label space, and multilingual copy',
		'printing' => 'high-resolution offset printing, Pantone medical colors, matte lamination, spot UV icons, anti-counterfeit labels, and clear small text handling',
		'channels' => 'pharmacy retail, clinic distribution, medical e-commerce, private label medicine projects, and export healthcare packaging',
		'difference' => 'dosage information, product safety communication, pharmacy shelf clarity, and small text readability',
		'feature' => 'Dosage panel, batch label area, medical information layout, clean healthcare print',
		'industrial' => 'Pharmaceutical, Medicine, Healthcare, Clinic Packaging',
		'paper' => 'Ivory Paper / SBS Paperboard / Duplex Board / Coated Paper / Microflute',
		'box_type' => 'Pharmaceutical Medicine Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Leaflet pocket / Paper insert / Tamper label area / Divider',
		'liner' => 'Paperboard insert / Carton divider / Leaflet support',
		'colors' => 'White / Blue / Green / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-pharmaceutical-medicine-packaging-boxes.webp' ),
		'captions' => array( 'Pharmaceutical medicine packaging boxes with healthcare retail information layout.' ),
		'alt' => 'Pharmaceutical medicine packaging boxes for healthcare products',
	),
	array(
		'title' => 'CUSTOM PHONE ACCESSORIES PACKAGING BOXES',
		'slug' => 'custom-phone-accessories-packaging-boxes',
		'category_slug' => 'electronics-accessories-packaging',
		'category_name' => 'Electronics Accessories Packaging',
		'keyword' => 'phone accessories packaging boxes',
		'heading' => 'Phone Accessories Packaging Boxes for Mobile Retail Lines',
		'structure_heading' => 'Retail Structures for Phone Accessories',
		'material_heading' => 'Paperboard for Electronics Accessories',
		'application_heading' => 'Phone Accessory Applications',
		'custom_heading' => 'Custom Model Labels and Accessory Inserts',
		'printing_heading' => 'Electronics Printing and Icon Systems',
		'b2b_heading' => 'B2B Value for Mobile Accessory Brands',
		'difference_heading' => 'Why Phone Accessory Packaging Needs SKU Clarity',
		'process_heading' => 'Phone Accessory Box Sampling Process',
		'cta_heading' => 'Request a Phone Accessories Packaging Quote',
		'audience' => 'mobile accessory brands, electronics wholesalers, phone shops, and OEM accessory suppliers',
		'core_need' => 'model compatibility, product visibility, SKU organization, and retail hanger display',
		'structures' => 'hang-tab boxes, sleeve cartons, drawer boxes, window cartons, and compact folding cartons',
		'materials' => 'ivory paper, duplex board, kraft paper, PET window material, art paper, and light corrugated board',
		'applications' => 'phone cases, screen protectors, earbuds, cables, chargers, adapters, stylus products, camera protectors, and accessory kits',
		'customization' => 'model label area, connector icons, product window, hang hole, barcode, warranty card, QR code, and color coding',
		'printing' => 'offset printing, Pantone brand colors, spot UV icons, matte lamination, gloss lamination, foil logo, and anti-counterfeit label area',
		'channels' => 'electronics retail, phone shop display, e-commerce accessory stores, wholesale distribution, and export mobile accessory packaging',
		'difference' => 'model fit clarity, SKU labels, accessory visibility, and compact retail display performance',
		'feature' => 'Model label, hang-tab display, window option, electronics icon layout',
		'industrial' => 'Electronics, Mobile Accessories, Retail Packaging',
		'paper' => 'Ivory Paper / Duplex Board / Kraft Paper / PET Window / Corrugated Paper',
		'box_type' => 'Phone Accessories Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Hang tab / PET window / Paper insert / Warranty card holder',
		'liner' => 'Paperboard insert / Blister support / Foam insert',
		'colors' => 'White / Black / Blue / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-phone-accessories-packaging-boxes.webp' ),
		'captions' => array( 'Phone accessories packaging boxes for mobile retail and electronics display.' ),
		'alt' => 'Phone accessories packaging boxes for mobile electronics retail display',
	),
	array(
		'title' => 'CUSTOM PHONE PACKAGING BOX WITH PAPER BAG',
		'slug' => 'custom-phone-packaging-box-with-paper-bag',
		'category_slug' => 'electronics-accessories-packaging',
		'category_name' => 'Electronics Accessories Packaging',
		'keyword' => 'phone packaging box with paper bag',
		'heading' => 'Phone Packaging Box with Paper Bag for Device Retail Sets',
		'structure_heading' => 'Rigid Phone Box and Matching Bag System',
		'material_heading' => 'Premium Paper Materials for Phone Kits',
		'application_heading' => 'Device and Accessory Kit Applications',
		'custom_heading' => 'Custom Trays, Bag Handles, and Model Labels',
		'printing_heading' => 'Printing a Matched Electronics Packaging Set',
		'b2b_heading' => 'B2B Value for Device Programs',
		'difference_heading' => 'Why a Phone Box and Bag Set Needs Its Own Page',
		'process_heading' => 'Phone Box Set Sampling Process',
		'cta_heading' => 'Request a Phone Box with Bag Quote',
		'audience' => 'smartphone brands, refurbished device sellers, electronics distributors, and retail device kit suppliers',
		'core_need' => 'device protection, premium unboxing, accessory organization, and matching retail carry-out presentation',
		'structures' => 'rigid lid-and-base phone boxes, drawer phone boxes, tray systems, accessory compartments, and matching rope-handle paper bags',
		'materials' => 'rigid greyboard, art paper, coated paper, specialty paper, ivory paper, and reinforced paper bag board',
		'applications' => 'smartphones, refurbished phones, demo devices, warranty kits, phone accessory bundles, retail device launches, and premium electronics gifts',
		'customization' => 'device tray, cable compartment, manual pocket, paper bag handle, logo, model label, barcode, ribbon pull, and inner card',
		'printing' => 'CMYK printing, Pantone matching between box and bag, foil stamping, embossing, spot UV, matte lamination, and soft-touch coating',
		'channels' => 'device retail stores, refurbished phone programs, electronics launch kits, distributor packages, and export-ready phone packaging',
		'difference' => 'matched box-and-bag branding, device tray planning, accessory layers, and premium electronics gift presentation',
		'feature' => 'Rigid phone box, matching paper bag, device tray, accessory compartment',
		'industrial' => 'Electronics, Mobile Phone, Retail, Gift Packaging',
		'paper' => 'Rigid Board / Art Paper / Coated Paper / Specialty Paper / Ivory Paper',
		'box_type' => 'Phone Packaging Box with Paper Bag',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Device tray / Manual pocket / Rope handle bag / Ribbon / Insert card',
		'liner' => 'EVA insert / Paperboard tray / Foam insert / Accessory compartment',
		'colors' => 'Black / White / Green / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-phone-packaging-box-with-paper-bag-1.webp', 'wp-content/uploads/2026/05/custom-phone-packaging-box-with-paper-bag-2.webp', 'wp-content/uploads/2026/05/custom-phone-packaging-box-with-paper-bag-3.webp', 'wp-content/uploads/2026/05/custom-phone-packaging-box-with-paper-bag-4.webp' ),
		'captions' => array( 'Phone packaging box with matching paper bag for premium device retail.', 'Rigid phone box set with coordinated electronics branding.', 'Phone packaging box and bag designed for device launch kits.', 'Custom phone box with paper bag for retail carry-out presentation.' ),
		'alt' => 'Phone packaging box with paper bag for electronics retail set',
	),
	array(
		'title' => 'CUSTOM RED PAPER SHOPPING BAG',
		'slug' => 'custom-red-paper-shopping-bag',
		'category_slug' => 'fashion-sportswear-packaging',
		'category_name' => 'Fashion and Sportswear Packaging',
		'keyword' => 'red paper shopping bag',
		'heading' => 'Red Paper Shopping Bag for Fashion and Retail Packaging',
		'structure_heading' => 'Shopping Bag Structure and Carry Strength',
		'material_heading' => 'Paper Choices for Retail Bags',
		'application_heading' => 'Fashion and Retail Bag Applications',
		'custom_heading' => 'Custom Handles, Gussets, and Brand Colors',
		'printing_heading' => 'Printing and Finishing for Red Paper Bags',
		'b2b_heading' => 'B2B Value for Retail Stores',
		'difference_heading' => 'Why Retail Shopping Bags Need Carry Details',
		'process_heading' => 'Shopping Bag Sampling and Production',
		'cta_heading' => 'Request a Red Paper Shopping Bag Quote',
		'audience' => 'fashion stores, sportswear brands, boutique retailers, event shops, and promotional suppliers',
		'core_need' => 'strong carry performance, high-visibility brand color, and gift-ready retail presentation',
		'structures' => 'rope-handle bags, ribbon-handle bags, folded paper bags, reinforced bottom bags, and boutique gift bags',
		'materials' => 'art paper, kraft paper, ivory paper, coated paper, specialty paper, and reinforced bottom board',
		'applications' => 'fashion apparel, shoes, accessories, cosmetics, sportswear, boutique gifts, event merchandise, and promotional retail packs',
		'customization' => 'handle type, handle color, bag size, side gusset, bottom card, logo position, inner printing, and ribbon detail',
		'printing' => 'solid Pantone red printing, CMYK artwork, foil logo, embossed logo, matte lamination, gloss lamination, and spot UV pattern',
		'channels' => 'fashion retail, sportswear stores, boutique gift programs, event campaigns, exhibition giveaways, and branded carry-out packaging',
		'difference' => 'handle strength, gusset width, red brand color consistency, bottom reinforcement, and retail carry experience',
		'feature' => 'Red brand color, rope handle, reinforced bottom, boutique retail carry-out',
		'industrial' => 'Fashion, Sportswear, Retail, Shopping Bag Packaging',
		'paper' => 'Art Paper / Kraft Paper / Ivory Paper / Coated Paper / Specialty Paper',
		'box_type' => 'Red Paper Shopping Bag',
		'shape' => 'Rectangle / Customized Bag Shape',
		'accessories' => 'Rope handle / Ribbon handle / Bottom board / Hang tag / Custom card',
		'liner' => 'Reinforced bottom board / Inner paper support',
		'colors' => 'Red / White / Gold / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-red-paper-shopping-bag.jpeg', 'wp-content/uploads/2026/05/custom-red-paper-shopping-bag-open.jpeg', 'wp-content/uploads/2026/05/custom-red-paper-shopping-bag-inside.jpeg' ),
		'captions' => array( 'Custom red paper shopping bag for fashion and boutique retail.', 'Open red shopping bag showing gusset and retail carry structure.', 'Inside view of red paper shopping bag with reinforced construction.' ),
		'alt' => 'Red paper shopping bag for fashion retail packaging',
	),
	array(
		'title' => 'CUSTOM SKINCARE GIFT BOX WITH INSERT',
		'slug' => 'custom-skincare-gift-box-with-insert',
		'category_slug' => 'beauty-skincare-packaging',
		'category_name' => 'Beauty and Skincare Packaging',
		'keyword' => 'skincare gift box with insert',
		'heading' => 'Skincare Gift Box with Insert for Beauty Sets',
		'structure_heading' => 'Gift Box Insert Structure for Skincare Bottles',
		'material_heading' => 'Rigid Board and Beauty Paper Options',
		'application_heading' => 'Skincare Gift Set Applications',
		'custom_heading' => 'Custom Insert Cavities and Beauty Line Branding',
		'printing_heading' => 'Luxury Finishing for Skincare Gift Boxes',
		'b2b_heading' => 'B2B Value for Skincare Gift Programs',
		'difference_heading' => 'Why Insert Planning Defines This Product',
		'process_heading' => 'Skincare Gift Box Sampling Process',
		'cta_heading' => 'Request a Skincare Gift Box Quote',
		'audience' => 'skincare brands, beauty gift suppliers, cosmetic distributors, and private label factories',
		'core_need' => 'stable bottle positioning, premium set presentation, and organized unboxing for skincare routines',
		'structures' => 'rigid lid-and-base boxes, magnetic boxes, drawer gift boxes, sleeve kits, and custom insert gift sets',
		'materials' => 'rigid greyboard, art paper, specialty paper, ivory paper, coated paper, EVA, foam, and paperboard insert materials',
		'applications' => 'serum sets, toner and cream kits, facial care routines, spa gift sets, holiday skincare bundles, and influencer beauty packages',
		'customization' => 'bottle cavities, jar cutouts, ribbon pull, inner lid message, logo, formula colors, insert tray, and product instruction card',
		'printing' => 'offset printing, foil stamping, embossing, debossing, spot UV, matte lamination, soft-touch coating, and specialty paper wrapping',
		'channels' => 'beauty retail, holiday gifting, private label skincare sets, salon distribution, influencer mailers, and export gift packaging',
		'difference' => 'insert cavity design, bottle reveal order, skincare routine layout, and premium beauty gifting',
		'feature' => 'Custom insert cavities, skincare bottle protection, premium gift presentation',
		'industrial' => 'Beauty, Skincare, Gift, Retail Packaging',
		'paper' => 'Rigid Board / Art Paper / Specialty Paper / Ivory Paper / Coated Paper',
		'box_type' => 'Skincare Gift Box with Insert',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'EVA insert / Paper tray / Ribbon / Magnetic closure / Instruction card',
		'liner' => 'EVA insert / Foam insert / Paperboard tray / Velvet lining',
		'colors' => 'Pink / White / Gold / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-skincare-gift-box-with-insert-1.webp', 'wp-content/uploads/2026/05/custom-skincare-gift-box-with-insert-2.webp', 'wp-content/uploads/2026/05/custom-skincare-gift-box-with-insert-3.webp', 'wp-content/uploads/2026/05/custom-skincare-gift-box-with-insert-4.webp', 'wp-content/uploads/2026/05/custom-skincare-gift-box-with-insert-5.webp', 'wp-content/uploads/2026/05/custom-skincare-gift-box-with-insert-6.webp', 'wp-content/uploads/2026/05/custom-skincare-gift-box-with-insert-7.webp' ),
		'captions' => array( 'Skincare gift box with insert for premium beauty set presentation.', 'Custom insert layout for skincare bottles and jars.', 'Rigid skincare gift box designed for organized beauty routines.', 'Beauty gift packaging with branded insert and product compartments.' ),
		'alt' => 'Skincare gift box with insert for beauty product set',
	),
	array(
		'title' => 'CUSTOM STATIONERY PACKAGING BOX',
		'slug' => 'custom-stationery-packaging-box',
		'category_slug' => 'back-to-school-stationery-packaging',
		'category_name' => 'Back-to-School and Stationery Packaging',
		'keyword' => 'stationery packaging box',
		'heading' => 'Stationery Packaging Box for School and Office Products',
		'structure_heading' => 'Stationery Box Structures and Product Organization',
		'material_heading' => 'Paper Materials for Stationery Packaging',
		'application_heading' => 'Stationery Product Applications',
		'custom_heading' => 'Custom Trays, Windows, and School Branding',
		'printing_heading' => 'Printing for Educational and Retail Stationery',
		'b2b_heading' => 'B2B Value for Stationery Suppliers',
		'difference_heading' => 'Why Stationery Packaging Needs Organization Detail',
		'process_heading' => 'Stationery Box Sampling Process',
		'cta_heading' => 'Request a Stationery Packaging Quote',
		'audience' => 'stationery brands, school suppliers, office product distributors, and educational kit manufacturers',
		'core_need' => 'product organization, colorful retail display, and protection for slim school supplies',
		'structures' => 'folding cartons, drawer boxes, sleeve boxes, window boxes, rigid stationery kits, and paper trays',
		'materials' => 'ivory paper, kraft paper, duplex board, art paper, rigid board, and molded paper insert materials',
		'applications' => 'pencils, pens, rulers, erasers, art tools, school kits, notebooks, office accessories, and promotional stationery sets',
		'customization' => 'product count, tray rows, window shape, color chart, logo, age mark, barcode, safety information, and school campaign message',
		'printing' => 'bright CMYK printing, Pantone school colors, gloss lamination, matte lamination, spot UV, embossing, and clear small text printing',
		'channels' => 'school supply retail, bookstore distribution, wholesale stationery programs, classroom kits, e-commerce sales, and export packaging',
		'difference' => 'row layout, product count, color visibility, classroom durability, and school supply retail information',
		'feature' => 'Product rows, window option, school retail graphics, stationery tray layout',
		'industrial' => 'Stationery, School Supplies, Office, Retail Packaging',
		'paper' => 'Ivory Paper / Kraft Paper / Duplex Board / Art Paper / Rigid Board',
		'box_type' => 'Stationery Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Paper tray / Window / Sleeve / Divider / Hang tab',
		'liner' => 'Paper insert / Molded pulp tray / Cardboard divider',
		'colors' => 'Green / Cream / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-stationery-packaging-box-1.webp', 'wp-content/uploads/2026/05/custom-stationery-packaging-box-2.webp', 'wp-content/uploads/2026/05/custom-stationery-packaging-box-3.webp', 'wp-content/uploads/2026/05/custom-stationery-packaging-box-4.webp' ),
		'captions' => array( 'Stationery packaging box for school and office product sets.', 'Custom stationery box with organized product tray layout.', 'Retail stationery packaging for school supplies and creative kits.', 'Stationery packaging box designed for branded educational products.' ),
		'alt' => 'Stationery packaging box for school supplies and office products',
	),
	array(
		'title' => 'CUSTOM STATIONERY SCHOOL SUPPLIES PACKAGING BOXES',
		'slug' => 'custom-stationery-school-supplies-packaging-boxes',
		'category_slug' => 'back-to-school-stationery-packaging',
		'category_name' => 'Back-to-School and Stationery Packaging',
		'keyword' => 'stationery school supplies packaging boxes',
		'heading' => 'Stationery School Supplies Packaging Boxes for Back-to-School Retail',
		'structure_heading' => 'School Supply Set Structures',
		'material_heading' => 'Durable Paperboard for School Products',
		'application_heading' => 'Back-to-School Product Applications',
		'custom_heading' => 'Custom Count Labels and Retail Panels',
		'printing_heading' => 'Colorful School Packaging Printing',
		'b2b_heading' => 'B2B Value for School Supply Programs',
		'difference_heading' => 'Why School Supplies Need Clear Count Communication',
		'process_heading' => 'School Supplies Packaging Production Process',
		'cta_heading' => 'Request a School Supplies Packaging Quote',
		'audience' => 'school supply brands, supermarket buyers, education distributors, and promotional stationery suppliers',
		'core_need' => 'clear product count, child-friendly presentation, and durable retail handling during school season',
		'structures' => 'folding school cartons, window boxes, display cartons, hang-tab boxes, and compact paperboard kits',
		'materials' => 'ivory paper, duplex board, kraft paper, coated paper, paper tray board, and light corrugated paper',
		'applications' => 'back-to-school sets, pencils, crayons, markers, rulers, erasers, classroom kits, school promotion packs, and student stationery bundles',
		'customization' => 'age guidance, non-toxic marks, product count, color system, school campaign graphics, barcode, window, hang tab, and display carton layout',
		'printing' => 'bright offset printing, gloss lamination, matte lamination, spot UV on characters, Pantone school colors, and safety icon printing',
		'channels' => 'supermarkets, school distributors, bookstore retail, education events, seasonal promotions, and export school supply packaging',
		'difference' => 'school season merchandising, product count visibility, child-friendly information, and durable retail stacking',
		'feature' => 'Back-to-school graphics, product count label, window display, school retail structure',
		'industrial' => 'School Supplies, Stationery, Education, Retail Packaging',
		'paper' => 'Ivory Paper / Duplex Board / Kraft Paper / Coated Paper / Light Corrugated Paper',
		'box_type' => 'Stationery School Supplies Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Window / Hang tab / Paper tray / Display carton / Divider',
		'liner' => 'Paperboard insert / Divider / Molded pulp support',
		'colors' => 'Green / Yellow / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-stationery-school-supplies-packaging-boxes.webp' ),
		'captions' => array( 'Stationery school supplies packaging boxes for back-to-school retail display.' ),
		'alt' => 'Stationery school supplies packaging boxes for back to school retail',
	),
	array(
		'title' => 'CUSTOM SUPPLEMENT VITAMIN PACKAGING BOXES',
		'slug' => 'custom-supplement-vitamin-packaging-boxes',
		'category_slug' => 'supplement-packaging-boxes',
		'category_name' => 'Supplement Packaging Boxes',
		'keyword' => 'supplement vitamin packaging boxes',
		'heading' => 'Supplement Vitamin Packaging Boxes for Wellness Brands',
		'structure_heading' => 'Vitamin Carton and Bottle Box Structures',
		'material_heading' => 'Paperboard for Supplement Packaging',
		'application_heading' => 'Vitamin and Wellness Applications',
		'custom_heading' => 'Custom Health Information Panels',
		'printing_heading' => 'Clean Supplement Packaging Printing',
		'b2b_heading' => 'B2B Value for Wellness Suppliers',
		'difference_heading' => 'Why Supplement Packaging Needs Trust Details',
		'process_heading' => 'Supplement Box Sampling and Production',
		'cta_heading' => 'Request a Supplement Vitamin Packaging Quote',
		'audience' => 'supplement brands, nutraceutical companies, wellness distributors, and private label health manufacturers',
		'core_need' => 'product trust, dosage clarity, bottle protection, and calm wellness shelf presentation',
		'structures' => 'folding cartons, bottle boxes, sleeve cartons, drawer wellness kits, and rigid supplement gift sets',
		'materials' => 'ivory paper, art paper, duplex board, kraft paper, rigid board, and coated paperboard',
		'applications' => 'vitamin bottles, probiotics, collagen, herbal supplements, capsules, wellness kits, sample packs, and subscription health boxes',
		'customization' => 'dosage table, ingredient panel, certification icons, batch area, expiry panel, QR code, bottle insert, and routine instruction card',
		'printing' => 'offset printing, Pantone wellness colors, matte lamination, foil logo, spot UV icons, embossing, and clear small text printing',
		'channels' => 'health stores, pharmacies, nutrition clinics, wellness e-commerce, private label production, and export supplement packaging',
		'difference' => 'health trust, dosage communication, certification marks, calm branding, and supplement bottle stability',
		'feature' => 'Dosage information panel, bottle insert, wellness branding, clean supplement print',
		'industrial' => 'Supplement, Vitamin, Wellness, Healthcare Packaging',
		'paper' => 'Ivory Paper / Art Paper / Duplex Board / Kraft Paper / Rigid Board',
		'box_type' => 'Supplement Vitamin Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Bottle insert / Paper tray / QR code area / Leaflet pocket',
		'liner' => 'Paperboard insert / EVA insert / Foam insert / Custom tray',
		'colors' => 'Green / White / Blue / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-supplement-vitamin-packaging-boxes.webp' ),
		'captions' => array( 'Supplement vitamin packaging boxes for wellness and health product brands.' ),
		'alt' => 'Supplement vitamin packaging boxes for wellness products',
	),
	array(
		'title' => 'CUSTOM TABLET PACKAGING BOX',
		'slug' => 'custom-tablet-packaging-box',
		'category_slug' => 'pharmaceutical-packaging-boxes',
		'category_name' => 'Pharmaceutical Packaging Boxes',
		'keyword' => 'tablet packaging box',
		'heading' => 'Tablet Packaging Box for Medicine and Healthcare Products',
		'structure_heading' => 'Tablet Carton Structures for Blister Packs',
		'material_heading' => 'Healthcare Paperboard for Tablet Boxes',
		'application_heading' => 'Tablet Product Applications',
		'custom_heading' => 'Custom Dosage Layouts and Batch Labels',
		'printing_heading' => 'Pharmaceutical Printing for Tablet Packaging',
		'b2b_heading' => 'B2B Value for Tablet Packaging Buyers',
		'difference_heading' => 'Why Tablet Boxes Need Specific Information Planning',
		'process_heading' => 'Tablet Box Sampling and Production Flow',
		'cta_heading' => 'Request a Tablet Packaging Box Quote',
		'audience' => 'pharmaceutical brands, tablet manufacturers, healthcare distributors, and medicine private label suppliers',
		'core_need' => 'blister pack fit, dosage information, pharmacy shelf clarity, and export-ready medicine carton production',
		'structures' => 'tuck-end cartons, folding tablet boxes, sleeve cartons, blister pack cartons, and medicine sample boxes',
		'materials' => 'SBS paperboard, ivory paper, duplex board, coated paper, kraft paper, and microflute board for shipping support',
		'applications' => 'tablets, capsules, blister cards, pill bottles, herbal tablets, clinical samples, pharmacy medicine packs, and healthcare kits',
		'customization' => 'dosage table, warning area, active ingredient panel, batch number, expiry date, QR code, barcode, and leaflet pocket',
		'printing' => 'small text offset printing, Pantone healthcare colors, matte lamination, spot UV icons, anti-counterfeit label area, and clean medical typography',
		'channels' => 'pharmacy retail, healthcare distributors, clinic supplies, medicine e-commerce, OEM medicine projects, and export pharmaceutical packaging',
		'difference' => 'blister card dimensions, dosage accuracy, batch label placement, and readable healthcare information',
		'feature' => 'Blister pack fit, dosage panel, medicine label area, pharmaceutical carton structure',
		'industrial' => 'Pharmaceutical, Medicine, Tablet, Healthcare Packaging',
		'paper' => 'SBS Paperboard / Ivory Paper / Duplex Board / Coated Paper / Microflute',
		'box_type' => 'Tablet Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Leaflet pocket / Paper insert / Blister support / Tamper label area',
		'liner' => 'Paperboard insert / Carton divider / Leaflet support',
		'colors' => 'White / Green / Blue / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-tablet-packaging-box-1.webp', 'wp-content/uploads/2026/05/custom-tablet-packaging-box-2.webp', 'wp-content/uploads/2026/05/custom-tablet-packaging-box-3.webp', 'wp-content/uploads/2026/05/custom-tablet-packaging-box-4.webp', 'wp-content/uploads/2026/05/custom-tablet-packaging-box-5.webp' ),
		'captions' => array( 'Tablet packaging box for medicine and healthcare retail products.', 'Custom tablet carton designed for blister pack information panels.', 'Healthcare tablet packaging with clean medical branding.', 'Tablet box structure for pharmacy and export medicine packaging.' ),
		'alt' => 'Tablet packaging box for medicine and healthcare products',
	),
	array(
		'title' => 'CUSTOM TEAL RIGID GIFT BOX',
		'slug' => 'custom-teal-rigid-gift-box',
		'category_slug' => 'corporate-gift-packaging',
		'category_name' => 'Corporate Gift Packaging',
		'keyword' => 'teal rigid gift box',
		'heading' => 'Teal Rigid Gift Box for Premium Brand Presentation',
		'structure_heading' => 'Rigid Gift Box Structure and Reveal',
		'material_heading' => 'Premium Board and Specialty Paper',
		'application_heading' => 'Gift Product Applications',
		'custom_heading' => 'Custom Inserts, Closure, and Brand Color',
		'printing_heading' => 'Teal Gift Box Printing and Finishing',
		'b2b_heading' => 'B2B Value for Gift Campaigns',
		'difference_heading' => 'Why Teal Rigid Gift Boxes Need Color Control',
		'process_heading' => 'Rigid Gift Box Sampling Process',
		'cta_heading' => 'Request a Teal Rigid Gift Box Quote',
		'audience' => 'gift brands, corporate buyers, luxury retailers, agencies, and promotional packaging suppliers',
		'core_need' => 'premium color control, strong board structure, and clean unboxing for brand campaigns',
		'structures' => 'rigid lid-and-base boxes, magnetic rigid boxes, drawer rigid boxes, book-style boxes, and gift boxes with inserts',
		'materials' => 'rigid greyboard, art paper, specialty paper, textured paper, coated paper, and soft-touch wrapped paper',
		'applications' => 'corporate gifts, candles, accessories, cosmetics, stationery gifts, jewelry sets, promotional kits, and VIP client packages',
		'customization' => 'teal brand color, foil logo, insert layout, ribbon pull, magnetic closure, inner message, and campaign card',
		'printing' => 'Pantone teal matching, foil stamping, embossing, debossing, spot UV, matte lamination, and soft-touch lamination',
		'channels' => 'corporate gifting, luxury retail, agency campaigns, holiday gift programs, event kits, and export premium packaging',
		'difference' => 'Pantone color consistency, rigid board feel, unboxing sequence, and campaign-grade gift presentation',
		'feature' => 'Teal brand color, rigid board, premium insert, luxury gift presentation',
		'industrial' => 'Corporate Gift, Luxury Gift, Promotional Packaging',
		'paper' => 'Rigid Board / Art Paper / Specialty Paper / Textured Paper / Coated Paper',
		'box_type' => 'Teal Rigid Gift Box',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'Ribbon / Magnetic closure / EVA insert / Paper tray / Inner card',
		'liner' => 'EVA insert / Foam insert / Paperboard tray / Velvet lining',
		'colors' => 'Teal / Gold / White / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-teal-rigid-gift-box.png', 'wp-content/uploads/2026/05/custom-teal-rigid-gift-box-open.png', 'wp-content/uploads/2026/05/custom-teal-rigid-gift-box-inside.png', 'wp-content/uploads/2026/05/custom-teal-rigid-gift-box-detail.png' ),
		'captions' => array( 'Teal rigid gift box for premium corporate gift presentation.', 'Open teal rigid gift box showing premium reveal structure.', 'Inside view of teal rigid gift box with custom insert area.', 'Detail of teal rigid gift box finish and branded presentation.' ),
		'alt' => 'Teal rigid gift box for corporate gift packaging',
	),
	array(
		'title' => 'CUSTOM THERMOS BOTTLE PACKAGING BOX',
		'slug' => 'custom-thermos-bottle-packaging-box',
		'category_slug' => 'home-lifestyle-packaging',
		'category_name' => 'Home and Lifestyle Packaging',
		'keyword' => 'thermos bottle packaging box',
		'heading' => 'Thermos Bottle Packaging Box for Drinkware and Lifestyle Products',
		'structure_heading' => 'Bottle Box Structures and Insert Protection',
		'material_heading' => 'Paperboard for Thermos Bottle Packaging',
		'application_heading' => 'Drinkware Product Applications',
		'custom_heading' => 'Custom Bottle Inserts and Retail Branding',
		'printing_heading' => 'Printing and Finishing for Drinkware Boxes',
		'b2b_heading' => 'B2B Value for Drinkware Brands',
		'difference_heading' => 'Why Thermos Packaging Needs Vertical Stability',
		'process_heading' => 'Thermos Box Sampling and Production',
		'cta_heading' => 'Request a Thermos Bottle Packaging Quote',
		'audience' => 'drinkware brands, homeware distributors, sports bottle suppliers, and lifestyle retailers',
		'core_need' => 'vertical bottle stability, surface protection, and retail gift presentation for insulated drinkware',
		'structures' => 'tall folding cartons, rigid bottle boxes, sleeve boxes, window boxes, and corrugated bottle cartons with inserts',
		'materials' => 'ivory paper, kraft paper, corrugated paper, duplex board, rigid board, and coated art paper',
		'applications' => 'thermos bottles, insulated tumblers, sports bottles, travel mugs, flask gift sets, drinkware accessories, and lifestyle retail kits',
		'customization' => 'bottle diameter insert, handle option, window shape, product feature icons, logo, care instructions, barcode, and color variants',
		'printing' => 'offset printing, Pantone color, foil logo, spot UV icons, matte lamination, gloss lamination, and textured paper finish',
		'channels' => 'homeware retail, outdoor product sales, corporate drinkware gifts, e-commerce fulfillment, and export lifestyle packaging',
		'difference' => 'tall bottle fit, anti-movement inserts, drinkware feature icons, and vertical carton compression control',
		'feature' => 'Tall bottle structure, insert protection, drinkware retail graphics, gift-ready presentation',
		'industrial' => 'Drinkware, Home Lifestyle, Sports Bottle, Retail Packaging',
		'paper' => 'Ivory Paper / Kraft Paper / Corrugated Paper / Duplex Board / Rigid Board',
		'box_type' => 'Thermos Bottle Packaging Box',
		'shape' => 'Tall Rectangle / Customized Shape',
		'accessories' => 'Bottle insert / Window / Handle / Paper divider / Care card',
		'liner' => 'Paperboard insert / Corrugated support / EVA insert / Foam ring',
		'colors' => 'Green / White / Brown / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-thermos-bottle-packaging-box-1.webp', 'wp-content/uploads/2026/05/custom-thermos-bottle-packaging-box-2.webp', 'wp-content/uploads/2026/05/custom-thermos-bottle-packaging-box-3.webp', 'wp-content/uploads/2026/05/custom-thermos-bottle-packaging-box-4.webp' ),
		'captions' => array( 'Thermos bottle packaging box for drinkware and lifestyle retail.', 'Custom thermos box with tall bottle protection structure.', 'Drinkware packaging box designed for insulated bottle display.', 'Thermos bottle paper box for retail and gift programs.' ),
		'alt' => 'Thermos bottle packaging box for drinkware products',
	),
	array(
		'title' => 'CUSTOM VIAL PACKAGING BOX',
		'slug' => 'custom-vial-packaging-box',
		'category_slug' => 'pharmaceutical-packaging-boxes',
		'category_name' => 'Pharmaceutical Packaging Boxes',
		'keyword' => 'vial packaging box',
		'heading' => 'Vial Packaging Box for Small Glass Healthcare Containers',
		'structure_heading' => 'Vial Box Structure and Anti-Movement Inserts',
		'material_heading' => 'Paperboard Materials for Vial Protection',
		'application_heading' => 'Vial Product Applications',
		'custom_heading' => 'Custom Cavities, Labels, and Product Information',
		'printing_heading' => 'Healthcare Printing for Vial Packaging',
		'b2b_heading' => 'B2B Value for Vial Packaging Buyers',
		'difference_heading' => 'Why Vial Packaging Needs Precision Fit',
		'process_heading' => 'Vial Box Sampling and Insert Testing',
		'cta_heading' => 'Request a Vial Packaging Box Quote',
		'audience' => 'healthcare brands, laboratory suppliers, cosmetic vial brands, pharmaceutical distributors, and OEM factories',
		'core_need' => 'small container protection, cavity precision, product information clarity, and export-ready handling',
		'structures' => 'vial folding cartons, rigid vial kits, sleeve boxes, drawer boxes, and multi-cavity boxes with paper or EVA inserts',
		'materials' => 'ivory paper, SBS board, duplex board, rigid board, EVA, foam, molded pulp, and coated paper',
		'applications' => 'glass vials, sample bottles, ampoules, lab samples, cosmetic vials, medicine samples, essential oil vials, and healthcare kits',
		'customization' => 'vial count, cavity diameter, label panel, batch area, warning text, QR code, barcode, insert depth, and numbered routine layout',
		'printing' => 'clean offset printing, Pantone healthcare colors, foil logo, spot UV icons, matte lamination, anti-counterfeit labels, and readable small text',
		'channels' => 'pharmaceutical distribution, lab supply, cosmetic sampling, clinical kits, OEM vial packaging, and export healthcare logistics',
		'difference' => 'vial diameter control, glass protection, cavity depth, batch information, and small container handling',
		'feature' => 'Vial cavities, glass container protection, healthcare label panels, insert precision',
		'industrial' => 'Pharmaceutical, Healthcare, Laboratory, Cosmetic Vial Packaging',
		'paper' => 'Ivory Paper / SBS Board / Duplex Board / Rigid Board / Coated Paper',
		'box_type' => 'Vial Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'EVA insert / Paper tray / Foam insert / Divider / Leaflet pocket',
		'liner' => 'EVA insert / Foam insert / Paperboard tray / Molded pulp support',
		'colors' => 'White / Green / Blue / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-vial-packaging-box-1.webp', 'wp-content/uploads/2026/05/custom-vial-packaging-box-2.webp', 'wp-content/uploads/2026/05/custom-vial-packaging-box-3.webp', 'wp-content/uploads/2026/05/custom-vial-packaging-box-4.webp', 'wp-content/uploads/2026/05/custom-vial-packaging-box-5.webp', 'wp-content/uploads/2026/05/custom-vial-packaging-box-6.webp', 'wp-content/uploads/2026/05/custom-vial-packaging-box-7.webp' ),
		'captions' => array( 'Vial packaging box for small glass healthcare containers.', 'Custom vial box with insert cavities for product protection.', 'Healthcare vial packaging designed for clear information panels.', 'Vial paper box with organized multi-container layout.' ),
		'alt' => 'Vial packaging box for small glass healthcare containers',
	),
	array(
		'title' => 'CUSTOM WINE BOTTLE GIFT BOX WITH PAPER BAG',
		'slug' => 'custom-wine-bottle-gift-box-with-paper-bag',
		'category_slug' => 'wine-premium-drink-packaging',
		'category_name' => 'Wine and Premium Drink Packaging',
		'keyword' => 'wine bottle gift box with paper bag',
		'heading' => 'Wine Bottle Gift Box with Paper Bag for Premium Beverage Gifting',
		'structure_heading' => 'Wine Gift Box and Matching Bag Structure',
		'material_heading' => 'Rigid Board and Paper Bag Materials',
		'application_heading' => 'Wine and Beverage Gift Applications',
		'custom_heading' => 'Custom Bottle Inserts, Handles, and Brand Details',
		'printing_heading' => 'Luxury Printing for Wine Gift Packaging Sets',
		'b2b_heading' => 'B2B Value for Wine Gift Programs',
		'difference_heading' => 'Why Wine Box and Bag Sets Need Matching Control',
		'process_heading' => 'Wine Gift Set Sampling Process',
		'cta_heading' => 'Request a Wine Bottle Gift Box with Bag Quote',
		'audience' => 'wine brands, beverage distributors, gift suppliers, hotels, and premium retail buyers',
		'core_need' => 'bottle protection, premium gifting, matching carry-out bag presentation, and branded beverage retail impact',
		'structures' => 'single bottle rigid boxes, lid-and-base wine boxes, magnetic wine boxes, insert gift boxes, and matching paper bags with rope handles',
		'materials' => 'rigid board, art paper, specialty paper, kraft paper, coated paper, EVA, foam, and reinforced bag paper',
		'applications' => 'wine bottles, champagne, spirits, premium beverages, hotel gifts, holiday wine sets, corporate beverage gifts, and tasting event kits',
		'customization' => 'bottle insert, neck support, rope handle, bag gusset, logo, foil finish, inner card, ribbon, and brand color matching',
		'printing' => 'Pantone color matching, foil stamping, embossing, debossing, spot UV, matte lamination, soft-touch coating, and specialty paper wrapping',
		'channels' => 'wine retail, hotel gifting, corporate gift programs, beverage distributors, holiday campaigns, and export premium drink packaging',
		'difference' => 'matching box and bag branding, bottle neck support, beverage gifting, and premium carry-out presentation',
		'feature' => 'Wine gift box, matching paper bag, bottle insert, premium beverage branding',
		'industrial' => 'Wine, Beverage, Gift, Retail Packaging',
		'paper' => 'Rigid Board / Art Paper / Specialty Paper / Kraft Paper / Coated Paper',
		'box_type' => 'Wine Bottle Gift Box with Paper Bag',
		'shape' => 'Tall Rectangle / Customized Shape',
		'accessories' => 'Bottle insert / Paper bag / Rope handle / Ribbon / Neck support',
		'liner' => 'EVA insert / Foam insert / Paperboard tray / Velvet lining',
		'colors' => 'Black / Cream / Gold / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-wine-bottle-gift-box-with-paper-bag-1.webp', 'wp-content/uploads/2026/05/custom-wine-bottle-gift-box-with-paper-bag-2.webp', 'wp-content/uploads/2026/05/custom-wine-bottle-gift-box-with-paper-bag-3.webp', 'wp-content/uploads/2026/05/custom-wine-bottle-gift-box-with-paper-bag-4.webp', 'wp-content/uploads/2026/05/custom-wine-bottle-gift-box-with-paper-bag-5.webp' ),
		'captions' => array( 'Wine bottle gift box with paper bag for premium beverage gifting.', 'Custom wine gift box and matching paper bag for retail sets.', 'Wine bottle packaging set with insert and carry bag.', 'Premium wine bottle gift packaging for corporate beverage programs.' ),
		'alt' => 'Wine bottle gift box with paper bag for premium beverage gifting',
	),
	array(
		'title' => 'CUSTOM WINE BOTTLE PACKAGING BOX',
		'slug' => 'custom-wine-bottle-packaging-box',
		'category_slug' => 'wine-premium-drink-packaging',
		'category_name' => 'Wine and Premium Drink Packaging',
		'keyword' => 'wine bottle packaging box',
		'heading' => 'Wine Bottle Packaging Box for Premium Beverage Retail',
		'structure_heading' => 'Wine Bottle Box Structure and Protection',
		'material_heading' => 'Paperboard Choices for Wine Packaging',
		'application_heading' => 'Wine Bottle Product Applications',
		'custom_heading' => 'Custom Inserts, Neck Support, and Branding',
		'printing_heading' => 'Printing and Finishing for Wine Boxes',
		'b2b_heading' => 'B2B Value for Wine Packaging Buyers',
		'difference_heading' => 'Why Wine Bottle Packaging Needs Vertical Support',
		'process_heading' => 'Wine Bottle Box Sampling Process',
		'cta_heading' => 'Request a Wine Bottle Packaging Quote',
		'audience' => 'wine brands, beverage distributors, wineries, gift suppliers, and premium retail buyers',
		'core_need' => 'bottle stability, label visibility, premium presentation, and safe handling during retail and export',
		'structures' => 'single bottle boxes, rigid wine boxes, folding cartons, corrugated bottle cartons, lid-and-base boxes, and sleeve wine cartons',
		'materials' => 'rigid board, corrugated paper, kraft paper, art paper, coated paper, specialty paper, and EVA insert material',
		'applications' => 'wine bottles, champagne, spirits, olive oil bottles, premium beverage bottles, tasting sets, and holiday drink gifts',
		'customization' => 'bottle diameter, neck support, insert tray, handle option, label window, logo, barcode, batch information, and brand color',
		'printing' => 'offset printing, Pantone wine brand colors, foil stamping, embossing, debossing, matte lamination, spot UV, and textured paper finish',
		'channels' => 'wine shops, beverage distributors, winery gift stores, corporate gifting, export beverage packaging, and e-commerce bottle sales',
		'difference' => 'bottle weight, neck support, anti-movement insert, premium wine branding, and vertical compression resistance',
		'feature' => 'Bottle insert, neck support, premium wine branding, export-ready structure',
		'industrial' => 'Wine, Beverage, Gift, Retail Packaging',
		'paper' => 'Rigid Board / Corrugated Paper / Kraft Paper / Art Paper / Specialty Paper',
		'box_type' => 'Wine Bottle Packaging Box',
		'shape' => 'Tall Rectangle / Customized Shape',
		'accessories' => 'Bottle insert / Neck support / Handle / Window / Ribbon',
		'liner' => 'EVA insert / Foam insert / Corrugated support / Paperboard tray',
		'colors' => 'Black / Red / Gold / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-wine-bottle-packaging-box-1.webp', 'wp-content/uploads/2026/05/custom-wine-bottle-packaging-box-2.webp', 'wp-content/uploads/2026/05/custom-wine-bottle-packaging-box-3.webp', 'wp-content/uploads/2026/05/custom-wine-bottle-packaging-box-4.webp' ),
		'captions' => array( 'Wine bottle packaging box for premium beverage retail.', 'Custom wine bottle box with protective insert structure.', 'Wine packaging box designed for bottle display and gifting.', 'Premium beverage bottle packaging for retail and export.' ),
		'alt' => 'Wine bottle packaging box for premium beverage retail',
	),
	array(
		'title' => 'CUSTOM WINE PREMIUM BEVERAGE PACKAGING BOXES',
		'slug' => 'custom-wine-premium-beverage-packaging-boxes',
		'category_slug' => 'wine-premium-drink-packaging',
		'category_name' => 'Wine and Premium Drink Packaging',
		'keyword' => 'wine premium beverage packaging boxes',
		'heading' => 'Wine Premium Beverage Packaging Boxes for Luxury Drink Brands',
		'structure_heading' => 'Premium Beverage Box Structures',
		'material_heading' => 'Luxury Paper Materials for Beverage Packaging',
		'application_heading' => 'Premium Drink Product Applications',
		'custom_heading' => 'Custom Bottle Layouts and Beverage Branding',
		'printing_heading' => 'Luxury Finishing for Premium Beverage Boxes',
		'b2b_heading' => 'B2B Value for Premium Beverage Suppliers',
		'difference_heading' => 'Why Premium Beverage Packaging Needs Brand Depth',
		'process_heading' => 'Premium Beverage Box Sampling Process',
		'cta_heading' => 'Request a Premium Beverage Packaging Quote',
		'audience' => 'premium beverage brands, wine distributors, spirit brands, hotel gift buyers, and luxury retail suppliers',
		'core_need' => 'premium drink presentation, bottle protection, brand storytelling, and high-value gifting impact',
		'structures' => 'rigid beverage boxes, magnetic boxes, lid-and-base bottle boxes, drawer boxes, and multi-bottle gift sets',
		'materials' => 'rigid board, specialty paper, art paper, kraft paper, coated paper, textured paper, and insert materials',
		'applications' => 'wine, spirits, champagne, premium tea beverages, olive oil bottles, beverage gift sets, tasting kits, and luxury drink launches',
		'customization' => 'bottle cavities, brand story panel, foil logo, ribbon, handle, neck support, insert card, and multi-bottle layout',
		'printing' => 'Pantone color, foil stamping, embossing, debossing, spot UV, matte lamination, soft-touch lamination, and specialty paper wrapping',
		'channels' => 'premium beverage retail, hotel gifting, corporate drink programs, duty-free retail, tasting events, and export luxury packaging',
		'difference' => 'luxury drink storytelling, bottle reveal, premium material selection, and high-value beverage gift positioning',
		'feature' => 'Premium beverage branding, rigid structure, bottle insert, luxury finish',
		'industrial' => 'Wine, Premium Beverage, Luxury Gift, Retail Packaging',
		'paper' => 'Rigid Board / Specialty Paper / Art Paper / Kraft Paper / Textured Paper',
		'box_type' => 'Wine Premium Beverage Packaging Box',
		'shape' => 'Rectangle / Tall Rectangle / Customized Shape',
		'accessories' => 'Bottle insert / Ribbon / Handle / Inner card / Neck support',
		'liner' => 'EVA insert / Foam insert / Paperboard tray / Velvet lining',
		'colors' => 'Black / Gold / Cream / CMYK / Pantone / Customized Color',
		'images' => array( 'wp-content/uploads/2026/05/custom-wine-premium-beverage-packaging-boxes.webp' ),
		'captions' => array( 'Wine premium beverage packaging boxes for luxury drink brand presentation.' ),
		'alt' => 'Wine premium beverage packaging boxes for luxury drink brands',
	),
);

$audit = array( '# Product Samples Batch 4 Remaining Audit', '' );

foreach ( $products as $p ) {
	$image_ids = array();
	foreach ( $p['images'] as $image ) {
		$id = vpn_b4_attachment_id( $image );
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
		'post_excerpt' => $p['title'] . ' is a custom paper packaging solution for ' . $p['applications'] . '. It supports custom size, logo, material, insert, color, printing, finishing, and bulk production from 1000 boxes.',
		'post_content' => vpn_b4_content( $p, $image_ids ),
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

	$term = get_term_by( 'slug', $p['category_slug'], 'product_cat' );
	if ( ! $term || is_wp_error( $term ) ) {
		$created = wp_insert_term( $p['category_name'], 'product_cat', array( 'slug' => $p['category_slug'] ) );
		$term_id = is_wp_error( $created ) ? 0 : (int) $created['term_id'];
	} else {
		$term_id = (int) $term->term_id;
	}

	if ( $term_id ) {
		wp_set_object_terms( $product_id, array( $term_id ), 'product_cat', false );
	}

	wp_set_object_terms( $product_id, 'simple', 'product_type' );
	wp_set_object_terms( $product_id, array( $p['keyword'], 'custom paper box', strtolower( $p['category_name'] ), 'custom packaging' ), 'product_tag' );

	if ( ! empty( $image_ids[0] ) ) {
		set_post_thumbnail( $product_id, $image_ids[0] );
	}

	$gallery = array_filter( array_slice( $image_ids, 1 ) );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery ) );
	update_post_meta( $product_id, '_sku', 'sample-b4-' . $p['slug'] );
	update_post_meta( $product_id, '_regular_price', '' );
	update_post_meta( $product_id, '_price', '' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_b4_specs( $p ) );
	update_post_meta( $product_id, '_vpn_sample_import', 'product-samples-batch-4-remaining' );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $p['keyword'] );
	update_post_meta( $product_id, 'rank_math_title', $p['title'] . ' | VPN PAPER BOX MANUFACTURER' );
	update_post_meta( $product_id, 'rank_math_description', $p['title'] . ' for ' . $p['applications'] . ', customized with logo, size, material, insert, color, printing, finishing, and OEM/ODM bulk production.' );

	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ) );
	$audit[] = '## ' . $p['title'];
	$audit[] = '- URL: ' . get_permalink( $product_id );
	$audit[] = '- Category: ' . $p['category_name'];
	$audit[] = '- Focus keyword: ' . $p['keyword'];
	$audit[] = '- Words: ' . $words;
	$audit[] = '- Images: ' . count( array_filter( $image_ids ) );
	$audit[] = '';
	echo 'Imported: ' . $p['title'] . ' (#' . $product_id . ') words=' . $words . PHP_EOL;
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-batch-4-remaining-audit.md', implode( PHP_EOL, $audit ) );
flush_rewrite_rules( false );

echo 'Batch 4 remaining import complete.' . PHP_EOL;
