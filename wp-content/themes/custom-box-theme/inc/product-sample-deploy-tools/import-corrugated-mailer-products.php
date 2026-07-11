<?php
/**
 * Import July 2026 corrugated mailer and shipping box products from 40 uploaded source images.
 *
 * Run:
 *   php tools/import-corrugated-mailer-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_corrugated_link( string $url, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_corrugated_file_base( string $filename ): string {
	return preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
}

function vpn_corrugated_find_attachment_by_base( string $filename_base ): int {
	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_wp_attached_file',
					'value'   => $filename_base,
					'compare' => 'LIKE',
				),
			),
		)
	);

	foreach ( $attachments as $attachment_id ) {
		$attached_file = (string) get_post_meta( (int) $attachment_id, '_wp_attached_file', true );
		if ( 0 === strcasecmp( vpn_corrugated_file_base( $attached_file ), $filename_base ) ) {
			return (int) $attachment_id;
		}
	}

	return 0;
}

function vpn_corrugated_import_attachment_from_uploads( string $filename, string $alt, string $title, string $caption ): int {
	$relative = '2026/07/' . basename( $filename );
	$file     = WP_CONTENT_DIR . '/uploads/' . $relative;

	if ( ! file_exists( $file ) && function_exists( 'get_template_directory' ) ) {
		$bundled = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
		if ( file_exists( $bundled ) ) {
			$target_dir = dirname( $file );
			if ( ! is_dir( $target_dir ) ) {
				wp_mkdir_p( $target_dir );
			}
			copy( $bundled, $file );
		}
	}

	if ( ! file_exists( $file ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( basename( $file ), null );
	if ( empty( $filetype['type'] ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $title,
			'post_content'   => '',
			'post_excerpt'   => $caption,
			'post_status'    => 'inherit',
			'guid'           => content_url( 'uploads/' . $relative ),
		),
		$file
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attached_file', $relative );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$metadata = wp_generate_attachment_metadata( $attachment_id, $file );
	if ( ! is_wp_error( $metadata ) && ! empty( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	return (int) $attachment_id;
}

function vpn_corrugated_attachment_id( string $filename, string $alt, string $title, string $caption ): int {
	$filename_base = vpn_corrugated_file_base( $filename );
	$attachment_id = vpn_corrugated_find_attachment_by_base( $filename_base );

	if ( ! $attachment_id ) {
		$attachment_id = vpn_corrugated_import_attachment_from_uploads( $filename, $alt, $title, $caption );
	}

	if ( ! $attachment_id ) {
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	wp_update_post(
		array(
			'ID'           => $attachment_id,
			'post_title'   => $title,
			'post_excerpt' => $caption,
		)
	);

	return $attachment_id;
}

function vpn_corrugated_sentence_list( array $items ): string {
	$items = array_values( array_filter( $items ) );
	if ( empty( $items ) ) {
		return '';
	}
	if ( 1 === count( $items ) ) {
		return $items[0];
	}

	$last = array_pop( $items );
	return implode( ', ', $items ) . ', and ' . $last;
}

function vpn_corrugated_section( string $heading, array $paragraphs ): string {
	$html = '<h2>' . esc_html( $heading ) . '</h2>';
	foreach ( $paragraphs as $paragraph ) {
		$html .= '<p>' . $paragraph . '</p>';
	}

	return $html;
}

function vpn_corrugated_inline_image( int $attachment_id, string $caption, bool $narrow = false ): string {
	$image = wp_get_attachment_image( $attachment_id, 'large', false, array( 'loading' => 'lazy' ) );
	if ( ! $image ) {
		return '';
	}

	return '<figure class="product-inline-figure product-inline-figure-small' . ( $narrow ? ' is-narrow' : '' ) . '">' .
		$image . '<figcaption>' . esc_html( $caption ) . '</figcaption></figure>';
}

function vpn_corrugated_specs( array $product ): array {
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
		array( 'label' => 'Printing Handling', 'value' => $product['printing'] ),
		array( 'label' => 'Color', 'value' => $product['colors'] ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, board grade, flute type, inserts, printing, finishing, packing method, and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $product['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement" ),
	);
}

function vpn_corrugated_short_description( array $product ): string {
	return $product['title'] . ' is a custom corrugated packaging solution for ' . $product['inside'] . '. It is developed for ' . $product['buyer'] . ' that need stronger transit protection, efficient packing speed, branded unboxing, custom printed panels, and a box structure planned around real product weight. The package helps solve ' . $product['problem'] . '. Size, flute, kraft or white liner, divider style, locking tabs, tear strip, inside printing, barcode area, shipping label zone, and export carton plan can be customized for ecommerce, subscription, retail, and distributor programs. MOQ starts from 1000 boxes.';
}

function vpn_corrugated_content( array $product, array $image_ids ): string {
	$corrugated_link = vpn_corrugated_link( '/product-category/corrugated-mailer-boxes/', 'corrugated mailer boxes' );
	$custom_link     = vpn_corrugated_link( '/product-category/custom-paper-boxes/', 'custom paper boxes' );
	$material_link   = vpn_corrugated_link( '/how-to-choose-paper-material-for-product-packaging/', 'paper material selection for product packaging' );
	$artwork_link    = vpn_corrugated_link( '/how-to-prepare-artwork-for-printed-paper-boxes/', 'print-ready artwork for paper boxes' );
	$finish_link     = vpn_corrugated_link( '/matte-vs-gloss-lamination-for-packaging/', 'matte and gloss lamination options' );
	$quote_link      = vpn_corrugated_link( '/contact/#quote', 'request a corrugated packaging quote' );
	$related_one     = vpn_corrugated_link( $product['related'][0][0], $product['related'][0][1] );
	$related_two     = vpn_corrugated_link( $product['related'][1][0], $product['related'][1][1] );
	$details         = vpn_corrugated_sentence_list( $product['details'] );
	$panel_details   = vpn_corrugated_sentence_list( $product['panel_details'] );
	$qc_points       = vpn_corrugated_sentence_list( $product['qc_points'] );

	$html = vpn_corrugated_section(
		$product['headings'][0],
		array(
			$product['title'] . ' is built for ' . $product['buyer'] . ' shipping ' . $product['inside'] . '. Corrugated packaging has to protect the product, hold its shape during packing, reserve space for labels and handling marks, and still create a brand moment when the customer opens the parcel.',
			'This product belongs to the ' . $corrugated_link . ' range and can also support broader ' . $custom_link . ' projects when a buyer needs the same print system across mailers, shipping boxes, sleeves, or protective inserts. The main packaging challenge is ' . $product['problem'] . ', so the dieline should be planned around the packed product instead of a generic carton size.',
			'Useful RFQ details include ' . $details . '. These details affect board grade, flute choice, inside clearance, divider height, label placement, print coverage, carton packing count, and sample approval. A clear product brief helps the factory quote a structure that works in daily fulfillment, not only in a clean product photo.',
		)
	);

	if ( ! empty( $image_ids[0] ) ) {
		$html .= vpn_corrugated_inline_image( $image_ids[0], $product['captions'][0], true );
	}

	$html .= vpn_corrugated_section(
		$product['headings'][1],
		array(
			$product['problem_copy'] . ' A corrugated package may look simple from the outside, but small tolerance choices decide whether the box closes cleanly, whether the product moves, and whether corners arrive crushed after courier handling.',
			'For ' . strtolower( $product['title'] ) . ', the package should answer protection and operations questions together. How much empty space is safe? Where will the shipping label sit? Can warehouse workers fold and close the box quickly? Will the product rub against the printed surface, insert, divider, or top lid during delivery?',
			'The sales channel changes the risk. A marketplace shipment may need clear label zones and compression strength. A branded subscription order may need inner printing and a slower reveal. A distributor carton may prioritize packing density, barcode accuracy, and master carton planning over decorative finishing.',
		)
	);

	$html .= vpn_corrugated_section(
		$product['headings'][2],
		array(
			$product['structure_copy'] . ' Recommended structures include ' . $product['structure_options'] . '. The best choice depends on product weight, fragility, packing speed, postal size targets, return needs, and whether the package ships alone or inside a master carton.',
			'Board selection should compare E-flute, B-flute, micro-flute, kraft liner, white liner, recycled liner, and coated paper surface based on print expectation and transit stress. The ' . $material_link . ' guide is useful before approving a sample because corrugated board behaves differently from folding carton paperboard.',
			'Sampling should check closure force, tab strength, lid alignment, side-wall stiffness, stacking behavior, and product movement after shake testing. A sample that feels strong when empty can still fail after the real product weight and packing speed are introduced.',
		)
	);

	if ( ! empty( $image_ids[1] ) ) {
		$html .= vpn_corrugated_inline_image( $image_ids[1], $product['captions'][1] );
	}

	$html .= vpn_corrugated_section(
		$product['headings'][3],
		array(
			$product['protection_copy'] . ' Protection can come from the outer flute, folded side walls, dust flaps, locking tabs, corrugated partitions, paperboard dividers, molded pulp, void-fill reduction, or a fitted internal tray. The correct mix depends on how the item moves inside the box and which surface must remain clean.',
			'Divider and insert planning should be practical for workers. A complex insert that looks impressive in a sample room can slow a 1000-box order if every cavity needs hand adjustment. The best protection system is one that locks consistently, uses board direction correctly, and lets the packed product be inspected before closure.',
			'If the product has a fragile cap, sharp corner, glossy label, printed sleeve, soft garment fold, or food-safe primary pack, the insert should isolate that risk. Corrugated packaging is not only about outside strength; it is about controlling contact points during vibration, stacking, and opening.',
		)
	);

	$html .= vpn_corrugated_section(
		$product['headings'][4],
		array(
			'Artwork should organize ' . $panel_details . '. The outside panel needs brand recognition but also enough clean space for courier labels, carton marks, barcode, orientation marks, and handling instructions. The inside lid can carry a stronger unboxing message, care note, reorder QR code, or campaign story.',
			$product['artwork_copy'] . ' Artwork should be prepared on the final dieline with bleed, safe zones, fold direction, glue areas, label zones, and print limitations clearly marked. The ' . $artwork_link . ' guide is useful because corrugated surfaces can change small text, fine lines, and dense color areas compared with smooth coated paper.',
			'If several sizes or product variants use the same mailer family, keep a controlled version system. A shared logo position, label zone, barcode area, and carton mark logic helps warehouse staff pick the right box quickly while still allowing product-specific colors, inside messages, or seasonal artwork.',
		)
	);

	if ( ! empty( $image_ids[2] ) ) {
		$html .= vpn_corrugated_inline_image( $image_ids[2], $product['captions'][2], true );
	}

	$html .= vpn_corrugated_section(
		$product['headings'][5],
		array(
			'Material options include ' . $product['materials'] . '. Kraft liner supports a natural ecommerce look, white liner improves print contrast, and coated liner can help when the brand needs brighter graphics. Board grade should be chosen from packed weight, shipping route, stacking height, and return damage tolerance.',
			$product['finish_strategy'] . ' Finishing can include one-color flexo printing, CMYK litho-lamination, Pantone logo color, inside printing, matte coating, gloss coating, spot UV on selected panels, or anti-scuff treatment. Review ' . $finish_link . ' before applying film to large fold areas because finishing choices can affect folding, glue, and recyclability claims.',
			'For sustainability claims, the team should confirm actual liner material, recycled content, coating type, and whether the box can be recycled in the target market. A natural kraft surface can support a lower-ink packaging story, but the structural performance still has to match the product risk.',
		)
	);

	$html .= vpn_corrugated_section(
		$product['headings'][6],
		array(
			$product['workflow_copy'] . ' Fulfillment teams need a box that opens quickly, receives the product or divider without forcing, closes securely, and can be labeled without covering important artwork. Packing speed affects labor cost as much as board price affects unit cost.',
			'A practical sample review should include product insertion, closure, shake testing, stacking, label application, master carton packing, and unpacking by a first-time customer. If the package will be used for returns, the return strip, reseal method, or second adhesive area should be tested before the dieline is finalized.',
			'This product can be compared with ' . $related_one . ' or ' . $related_two . ' when the buyer is building a complete corrugated packaging range. A strong packaging family can share board, color, and print logic while still adjusting each structure to the product weight and delivery risk.',
		)
	);

	if ( ! empty( $image_ids[3] ) ) {
		$html .= vpn_corrugated_inline_image( $image_ids[3], $product['captions'][3] );
	}

	$html .= vpn_corrugated_section(
		$product['headings'][7],
		array(
			$product['qc_copy'] . ' Quality control should check ' . $qc_points . '. Corrugated shipping packaging is judged after movement, so the approved sample should be packed with the real product and reviewed after shaking, stacking, opening, and simulated delivery handling.',
			'The factory should compare bulk goods with the approved production sample for board thickness, flute direction, print color, tab lock, die-cut accuracy, glue cleanliness, panel alignment, and final packed appearance. If several SKUs are produced together, QC should also confirm size code, barcode, artwork version, and carton label accuracy.',
			'For reorder programs, keep one approved packed sample with the buyer and one with the factory. Corrugated board, liner color, and flute stiffness can drift between paper lots, so a physical reference protects the standard when the same box is reordered or extended to new sizes.',
		)
	);

	$html .= '<h2>' . esc_html( $product['headings'][8] ) . '</h2><ul>';
	foreach ( $product['mistakes'] as $mistake ) {
		$html .= '<li>' . esc_html( $mistake ) . '</li>';
	}
	$html .= '</ul>';

	$html .= vpn_corrugated_section(
		$product['quote_heading'],
		array(
			'For an accurate quotation, send ' . $product['quote_details'] . '. Photos, reference mailers, product drawings, existing artwork, or a packed sample can help the packaging team check structure, board strength, divider logic, and printing before tooling or mass production.',
			'VPN Paper Box Manufacturer can customize size, corrugated flute, kraft or white liner, divider, insert, logo printing, inside message, tear strip, return strip, surface finishing, and export packing for ecommerce and retail buyers.',
			'MOQ starts from 1000 boxes. Send your project details to ' . $quote_link . ' and include the product size, packed weight, target quantity, shipping route, preferred structure, and artwork status so the sample can be reviewed around real fulfillment conditions.',
		)
	);

	return $html;
}

function vpn_corrugated_products(): array {
	return array(
		array(
			'title' => 'CUSTOM CORRUGATED APPAREL MAILER BOX',
			'slug' => 'custom-corrugated-apparel-mailer-box',
			'keyword' => 'corrugated apparel mailer box',
			'buyer' => 'fashion brands, garment factories, streetwear stores, uniform suppliers, marketplace apparel sellers, and subscription clothing teams',
			'inside' => 'folded shirts, hoodies, socks, accessories, textile samples, influencer kits, and lightweight apparel orders',
			'problem' => 'keeping soft folded garments neat during delivery while controlling freight size, label placement, and branded unboxing',
			'problem_copy' => 'Apparel mailers have to protect presentation more than fragile glass, because the biggest risk is a crushed, messy, oversized, or under-branded clothing delivery.',
			'structure_copy' => 'An apparel mailer box usually uses a foldable corrugated mailer with front tuck tabs, side locking wings, dust flaps, and enough internal height for folded garments.',
			'protection_copy' => 'For apparel, the insert system may be minimal, but the inside clearance, tissue wrap, return card, size sticker, and fold direction still need planning.',
			'artwork_copy' => 'Apparel mailer artwork should reserve a shipping label zone while using the inner lid for brand tone, return instructions, garment care reminders, or QR-based reorder prompts.',
			'workflow_copy' => 'A clothing warehouse needs predictable folding depth, fast box forming, easy size identification, and a return-friendly structure when customers exchange sizes.',
			'qc_copy' => 'Apparel mailer QC should focus on carton squareness, fold memory, tab lock, surface scuffing, and whether the garment still looks organized after normal courier movement.',
			'structure_options' => 'front-tuck mailers, self-locking corrugated mailers, returnable mailer boxes, shallow apparel subscription boxes, and printed inner-lid ecommerce mailers',
			'materials' => 'kraft E-flute board, white corrugated board, recycled kraft liner, coated white liner, micro-flute board, tissue wrap, and optional paper size cards',
			'finish_strategy' => 'Apparel brands often get better value from clean kraft printing, bold inside-lid copy, and a practical return strip than from heavy laminated coverage.',
			'feature' => 'Foldable apparel mailer, branded ecommerce unboxing, return-friendly structure, custom logo printing',
			'industrial' => 'Apparel, Fashion, Textile, Ecommerce Shipping',
			'paper' => 'Kraft Corrugated Board / White Corrugated Board / E-Flute / Recycled Liner',
			'box_type' => 'Corrugated Apparel Mailer Box',
			'shape' => 'Shallow Rectangle / Foldable Mailer / Customized Garment Fit',
			'accessories' => 'Locking tabs / Tear strip / Return strip / Tissue wrap / Size card optional',
			'liner' => 'No liner / Tissue paper / Paperboard card / Corrugated insert optional',
			'printing' => 'Flexo Printing, CMYK Printing, Pantone Printing, Inside Printing, Matte Coating',
			'colors' => 'Kraft / White / Black / Brand color system / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'fashion-sportswear-packaging', 'custom-paper-boxes' ),
			'tags' => array( 'apparel mailer', 'clothing shipping box', 'ecommerce apparel packaging', 'returnable mailer box' ),
			'images' => array( 'Custom-Corrugated-Apparel-Mailer-Box.webp', 'Custom-Corrugated-Apparel-Mailer-Box-2.webp', 'Custom-Corrugated-Apparel-Mailer-Box-3.webp', 'Custom-Corrugated-Apparel-Mailer-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated apparel mailer box for folded clothing ecommerce orders.',
				'Apparel mailer box view showing foldable corrugated shipping structure.',
				'Detail view of corrugated apparel mailer panels and printed surface.',
				'Corrugated apparel mailer packaging set for fashion and subscription programs.',
			),
			'details' => array( 'garment fold size', 'packed thickness', 'size range', 'return strip need', 'shipping label zone', 'tissue wrap plan', 'inner message', 'monthly order volume' ),
			'panel_details' => array( 'brand logo', 'shipping label zone', 'size code', 'return instructions', 'care message', 'QR code', 'social handle', 'carton mark' ),
			'qc_points' => array( 'tab lock strength', 'garment movement', 'box depth', 'folding speed', 'label placement', 'print rub resistance', 'return strip function', 'master carton packing' ),
			'related' => array( array( '/product/custom-corrugated-shoe-mailer-box/', 'corrugated shoe mailer box' ), array( '/product/kraft-corrugated-mailer-box/', 'kraft corrugated mailer box' ) ),
			'headings' => array( 'Corrugated Apparel Mailer Box for Fashion Ecommerce', 'Soft Goods Shipping Problems to Solve', 'Mailer Structure for Folded Garments', 'Inside Protection and Return-Friendly Packing', 'Artwork for Labels, Care Notes, and Inner Lid Branding', 'Board and Finish Choices for Apparel Mailers', 'Warehouse Packing Workflow for Clothing Orders', 'Quality Checks for Apparel Corrugated Mailers', 'Common Mistakes With Apparel Mailer Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Apparel Mailer Boxes',
			'quote_details' => 'folded garment size, packed height, order quantity, return strip requirement, print colors, tissue or card plan, label zone, and shipping market',
			'mistakes' => array(
				'Choosing a box depth that crushes folded garments or leaves too much empty space.',
				'Placing the main logo where the courier label must cover it.',
				'Ignoring return workflow when apparel sizes are commonly exchanged.',
				'Using dark ink coverage on kraft board without checking rub resistance.',
			),
			'duplicate_risk' => '4/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED BOOK MAILER BOX',
			'slug' => 'custom-corrugated-book-mailer-box',
			'keyword' => 'corrugated book mailer box',
			'buyer' => 'publishers, bookstores, school suppliers, stationery brands, print shops, education kit sellers, and direct-to-reader ecommerce teams',
			'inside' => 'books, notebooks, journals, catalogs, planners, printed manuals, educational kits, and flat paper goods',
			'problem' => 'protecting corners, covers, spines, and flat printed surfaces without making book shipments bulky or hard to pack',
			'problem_copy' => 'Book mailers fail when corners bend, covers scuff, or a flat printed item slides inside a carton that was not sized for paper goods.',
			'structure_copy' => 'A book mailer box can use a wrap-around corrugated style, shallow front-tuck mailer, adjustable-score book wrap, or flat literature shipping box.',
			'protection_copy' => 'For books, protection should control corner impact, spine pressure, and surface rubbing while keeping the package flat enough for efficient shipping.',
			'artwork_copy' => 'Book mailer artwork should include title or program identification, barcode space, school or publisher marks, orientation notes, and optional inside reading message.',
			'workflow_copy' => 'Book fulfillment often handles several trim sizes, so warehouse teams need size codes, easy wrapping direction, and a structure that avoids over-taping.',
			'qc_copy' => 'Book mailer QC should test corner crush, cover rub, spine pressure, closure security, and whether the packed book can move after repeated handling.',
			'structure_options' => 'adjustable book wraps, flat corrugated mailers, front-tuck literature boxes, self-locking document mailers, and shallow ecommerce book cartons',
			'materials' => 'kraft corrugated board, white E-flute, micro-flute board, recycled liner, coated white liner, corner pads, and paper belly bands',
			'finish_strategy' => 'Book mailers usually benefit from precise scoring, clean one-color printing, and clear size identification more than decorative finishing.',
			'feature' => 'Flat book protection, corner support, adjustable mailer structure, custom publisher printing',
			'industrial' => 'Books, Publishing, Stationery, Education, Ecommerce Shipping',
			'paper' => 'Kraft Corrugated Board / White E-Flute / Micro-Flute / Recycled Liner',
			'box_type' => 'Corrugated Book Mailer Box',
			'shape' => 'Flat Rectangle / Book Wrap / Customized Trim Size',
			'accessories' => 'Locking tabs / Tear strip / Corner support / Paper belly band / Insert card optional',
			'liner' => 'No liner / Paper wrap / Corner pad / Flat corrugated support',
			'printing' => 'Flexo Printing, CMYK Printing, Pantone Printing, Black Ink Printing, Inside Message Printing',
			'colors' => 'Kraft / White / Black / Publisher color system / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'back-to-school-stationery-packaging', 'custom-paper-boxes' ),
			'tags' => array( 'book mailer', 'journal shipping box', 'stationery mailer', 'publisher packaging' ),
			'images' => array( 'Custom-Corrugated-Book-Mailer-Box.webp', 'Custom-Corrugated-Book-Mailer-Box-2.webp', 'Custom-Corrugated-Book-Mailer-Box-3.webp', 'Custom-Corrugated-Book-Mailer-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated book mailer box for books, journals, and printed paper goods.',
				'Book mailer box view showing flat corrugated protection and closure.',
				'Detail view of book mailer panels for spine and corner protection.',
				'Corrugated book mailer packaging set for publishers and education kits.',
			),
			'details' => array( 'book trim size', 'page count', 'spine thickness', 'cover finish', 'corner protection need', 'barcode label zone', 'title version list', 'shipment quantity' ),
			'panel_details' => array( 'publisher logo', 'title code', 'barcode', 'shipping label zone', 'orientation mark', 'school program note', 'QR code', 'carton count' ),
			'qc_points' => array( 'corner protection', 'spine clearance', 'cover scuffing', 'closure lock', 'flatness', 'print readability', 'size code accuracy', 'packing speed' ),
			'related' => array( array( '/product/custom-corrugated-subscription-box/', 'corrugated subscription box' ), array( '/product/kraft-corrugated-mailer-box/', 'kraft corrugated mailer box' ) ),
			'headings' => array( 'Corrugated Book Mailer Box for Publishers and Stationery Sellers', 'Book Corner and Cover Protection Risks', 'Flat Mailer Structures for Printed Goods', 'Book Fit, Wrap Depth, and Surface Protection', 'Artwork for Publisher, Barcode, and Program Details', 'Board and Finish Choices for Book Mailers', 'Packing Workflow for Multiple Book Sizes', 'Quality Checks for Corrugated Book Mailers', 'Common Mistakes With Book Mailer Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Book Mailer Boxes',
			'quote_details' => 'book trim size, page count, spine thickness, packed weight, title versions, print colors, label zone, order quantity, and shipping route',
			'mistakes' => array(
				'Using a generic carton that lets books slide and damage cover corners.',
				'Ignoring spine thickness when one title has multiple page-count editions.',
				'Printing fine publisher details without testing readability on corrugated liner.',
				'Approving a mailer before checking how a return label or barcode will be applied.',
			),
			'duplicate_risk' => '4/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED BOTTLE SHIPPING BOX WITH DIVIDERS',
			'slug' => 'custom-corrugated-bottle-shipping-box-with-dividers',
			'keyword' => 'corrugated bottle shipping box with dividers',
			'buyer' => 'beverage brands, wine sample suppliers, sauce makers, cosmetic bottle sellers, wellness brands, and export distributors shipping multiple bottles',
			'inside' => 'glass bottles, beverage samples, sauce bottles, essential oil bottles, cosmetic bottles, and multi-bottle retail or export sets',
			'problem' => 'separating bottles so they do not hit each other while protecting caps, labels, shoulders, and printed surfaces during shipping',
			'problem_copy' => 'Bottle shipping boxes fail when the outer carton is strong but the bottles can still collide, rotate, or push against caps and labels inside the pack.',
			'structure_copy' => 'A bottle shipping box with dividers can use a regular slotted carton, mailer-style shipper, crash-lock bottom box, or reinforced corrugated carton with cell partitions.',
			'protection_copy' => 'For bottles, divider height, cell size, shoulder clearance, and top-pad planning are the main engineering decisions.',
			'artwork_copy' => 'Bottle shipper artwork should balance brand identity with practical orientation marks, fragile handling notes, barcode, liquid warning space, and carton count.',
			'workflow_copy' => 'Packing teams need dividers that open quickly, stay upright, and let each bottle drop into position without labels scraping against rough board edges.',
			'qc_copy' => 'Bottle shipper QC should include divider fit, bottle movement, cap clearance, compression strength, leak-risk inspection, and master carton orientation.',
			'structure_options' => 'corrugated bottle cartons, divider cell boxes, mailer shippers with partitions, reinforced export cartons, and bottle boxes with top and bottom pads',
			'materials' => 'B-flute board, E-flute board, double-wall corrugated board, kraft liner, white liner, corrugated dividers, paperboard pads, and molded pulp supports',
			'finish_strategy' => 'Bottle shippers should prioritize structural board strength and clear handling marks, with decorative printing used only where it does not interfere with logistics.',
			'feature' => 'Bottle divider protection, multi-cell corrugated structure, cap clearance, export-ready shipping',
			'industrial' => 'Beverage, Wine, Sauce, Cosmetics, Bottle Shipping',
			'paper' => 'Kraft Corrugated Board / B-Flute / E-Flute / Double-Wall Board',
			'box_type' => 'Corrugated Bottle Shipping Box with Dividers',
			'shape' => 'Rectangle / Multi-Cell Divider / Customized Bottle Layout',
			'accessories' => 'Corrugated dividers / Top pad / Bottom pad / Fragile label zone / Handle optional',
			'liner' => 'Corrugated divider / Paperboard pad / Molded pulp insert optional',
			'printing' => 'Flexo Printing, CMYK Printing, Pantone Printing, Handling Marks, Barcode Printing',
			'colors' => 'Kraft / White / Black / Beverage color system / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'wine-premium-drink-packaging', 'premium-food-beverage-packaging', 'packaging-accessories' ),
			'tags' => array( 'bottle shipping box', 'corrugated dividers', 'wine sample shipper', 'glass bottle packaging' ),
			'images' => array( 'Custom-Corrugated-Bottle-Shipping-Box-with-Dividers.webp', 'Custom-Corrugated-Bottle-Shipping-Box-with-Dividers-2.webp', 'Custom-Corrugated-Bottle-Shipping-Box-with-Dividers-3.webp', 'Custom-Corrugated-Bottle-Shipping-Box-with-Dividers-4.webp' ),
			'captions' => array(
				'Custom corrugated bottle shipping box with dividers for multi-bottle protection.',
				'Bottle shipper view showing divider cells and corrugated outer structure.',
				'Detail view of bottle divider packaging for caps, shoulders, and labels.',
				'Corrugated bottle shipping box set for beverage, wine, and cosmetic bottles.',
			),
			'details' => array( 'bottle diameter', 'bottle height', 'filled weight', 'bottle count', 'cap shape', 'divider height', 'shipping route', 'fragile label requirement' ),
			'panel_details' => array( 'brand logo', 'fragile mark', 'orientation arrow', 'bottle count', 'barcode', 'batch code', 'liquid warning', 'shipping label zone' ),
			'qc_points' => array( 'divider cell fit', 'bottle movement', 'cap clearance', 'compression strength', 'label rubbing', 'drop handling', 'carton orientation', 'packing count accuracy' ),
			'related' => array( array( '/product/custom-corrugated-food-delivery-box/', 'corrugated food delivery box' ), array( '/product/kraft-corrugated-mailer-box/', 'kraft corrugated mailer box' ) ),
			'headings' => array( 'Corrugated Bottle Shipping Box With Dividers for Fragile Products', 'Bottle Collision and Cap Pressure Risks', 'Divider Carton Structures for Multi-Bottle Shipping', 'Cell Divider Planning Around Bottle Diameter and Height', 'Artwork for Fragile Marks, Barcodes, and Bottle Counts', 'Board and Finish Choices for Bottle Shippers', 'Packing Workflow for Multi-Bottle Orders', 'Quality Checks for Bottle Shipping Boxes With Dividers', 'Common Mistakes With Bottle Divider Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Bottle Shipping Boxes With Dividers',
			'quote_details' => 'bottle diameter, bottle height, filled weight, bottle count, divider layout, shipping route, print marks, order quantity, and export carton requirement',
			'mistakes' => array(
				'Designing the divider from bottle diameter only and forgetting cap or shoulder shape.',
				'Leaving the divider too low so bottles can hit each other above the cell wall.',
				'Adding decorative print where fragile marks and orientation arrows must remain visible.',
				'Approving an empty carton sample instead of testing with filled bottles.',
			),
			'duplicate_risk' => '5/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED CANDLE SHIPPING BOX',
			'slug' => 'custom-corrugated-candle-shipping-box',
			'keyword' => 'corrugated candle shipping box',
			'buyer' => 'candle brands, home fragrance studios, aromatherapy sellers, subscription candle companies, gift shops, and private label candle suppliers',
			'inside' => 'glass jar candles, tin candles, ceramic candle cups, wax melts, candle care cards, lids, and small home fragrance accessories',
			'problem' => 'protecting dense candle jars, lids, labels, and wax surfaces from impact, rubbing, and heat-related movement during delivery',
			'problem_copy' => 'Candle shipping boxes are risky because candles are compact but heavy, and a jar that moves inside the mailer can crack glass or dent a metal lid.',
			'structure_copy' => 'A candle shipping box can use a corrugated mailer with folded insert, a shipper carton with inner support, a sleeve-and-tray mailer, or a compact protective candle carton.',
			'protection_copy' => 'For candles, the insert should hold the jar body, protect the lid edge, keep the label from rubbing, and leave space for care cards or dust covers.',
			'artwork_copy' => 'Candle mailer artwork should reserve a label area while using inside panels for burn instructions, scent story, safety reminders, and reorder QR code.',
			'workflow_copy' => 'Candle fulfillment needs a pack that workers can assemble quickly without forcing the jar, bending the insert, or trapping cards under the lid.',
			'qc_copy' => 'Candle shipper QC should test jar movement, lid denting, label rubbing, insert collapse, scent-card placement, and whether the box survives stacked storage.',
			'structure_options' => 'corrugated candle mailers, jar shippers with folded inserts, compact candle cartons, subscription candle boxes, and candle boxes with side-wall supports',
			'materials' => 'kraft corrugated board, white E-flute, B-flute board, recycled liner, paperboard inserts, molded pulp support, and anti-scuff inner paper',
			'finish_strategy' => 'Candle packaging can use warm kraft printing, inside scent story, and clear safety information while keeping the outer structure strong for parcel handling.',
			'feature' => 'Glass candle jar protection, folded corrugated insert, ecommerce shipping, inner scent story printing',
			'industrial' => 'Candles, Home Fragrance, Aromatherapy, Gift Shipping',
			'paper' => 'Kraft Corrugated Board / White E-Flute / B-Flute / Paperboard Insert',
			'box_type' => 'Corrugated Candle Shipping Box',
			'shape' => 'Rectangle / Jar Fit / Customized Candle Mailer',
			'accessories' => 'Folded insert / Care card slot / Dust cover space / Locking tabs / Tear strip optional',
			'liner' => 'Corrugated insert / Paperboard tray / Molded pulp / Tissue wrap optional',
			'printing' => 'Flexo Printing, CMYK Printing, Pantone Printing, Inside Printing, Matte Coating',
			'colors' => 'Kraft / White / Black / Scent color variants / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'candle-packaging-boxes', 'home-lifestyle-packaging' ),
			'tags' => array( 'candle shipping box', 'jar candle mailer', 'home fragrance packaging', 'corrugated candle packaging' ),
			'images' => array( 'Custom-Corrugated-Candle-Shipping-Box.webp', 'Custom-Corrugated-Candle-Shipping-Box-2.webp', 'Custom-Corrugated-Candle-Shipping-Box-3.webp', 'Custom-Corrugated-Candle-Shipping-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated candle shipping box for glass jar candle ecommerce.',
				'Candle shipping box view showing corrugated structure and product protection.',
				'Detail view of candle mailer panels and protective packing space.',
				'Corrugated candle shipping box set for fragrance and gift programs.',
			),
			'details' => array( 'jar diameter', 'jar height', 'filled candle weight', 'lid shape', 'label finish', 'care card size', 'scent variants', 'courier route' ),
			'panel_details' => array( 'brand logo', 'scent name', 'burn instructions', 'safety note', 'QR code', 'shipping label zone', 'barcode', 'carton mark' ),
			'qc_points' => array( 'jar movement', 'lid clearance', 'insert strength', 'label scuffing', 'closure lock', 'inside print odor', 'stacking resistance', 'care card fit' ),
			'related' => array( array( '/product/custom-corrugated-gift-set-mailer-box/', 'corrugated gift set mailer box' ), array( '/product/custom-corrugated-bottle-shipping-box-with-dividers/', 'corrugated bottle shipping box with dividers' ) ),
			'headings' => array( 'Corrugated Candle Shipping Box for Home Fragrance Orders', 'Glass Jar and Lid Protection Risks', 'Mailer Structures for Candle Fulfillment', 'Insert Planning for Candle Jars and Care Cards', 'Artwork for Scent Story, Safety Notes, and Labels', 'Board and Finish Choices for Candle Shippers', 'Packing Workflow for Candle Ecommerce', 'Quality Checks for Corrugated Candle Shipping Boxes', 'Common Mistakes With Candle Shipping Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Candle Shipping Boxes',
			'quote_details' => 'jar diameter, jar height, filled weight, lid shape, insert requirement, care card size, print colors, quantity, and shipping market',
			'mistakes' => array(
				'Choosing a shallow mailer that lets the candle lid press against the top panel.',
				'Testing the package with an empty jar instead of a filled candle.',
				'Forgetting that inner printing ink or paper odor can affect a fragrance product.',
				'Leaving care cards loose so they scratch labels or block closure.',
			),
			'duplicate_risk' => '4/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED COSMETIC MAILER BOX',
			'slug' => 'custom-corrugated-cosmetic-mailer-box',
			'keyword' => 'corrugated cosmetic mailer box',
			'buyer' => 'beauty brands, skincare ecommerce teams, cosmetic subscription sellers, influencer kit buyers, and private label cosmetic suppliers',
			'inside' => 'serum bottles, cream jars, tubes, cosmetic cartons, sample sachets, beauty cards, and small skincare kits',
			'problem' => 'shipping cosmetic products cleanly while protecting primary cartons, pump caps, jars, labels, and beauty-brand presentation',
			'problem_copy' => 'Cosmetic mailer boxes must protect fragile or glossy beauty products without making the shipment feel like plain industrial packing.',
			'structure_copy' => 'A cosmetic mailer box can use a printed corrugated mailer, subscription-style shipper, mailer with paper insert, or beauty kit box with compartments.',
			'protection_copy' => 'For cosmetics, inserts should separate glass, caps, tubes, and cards while keeping the unboxing layout tidy and photo-ready.',
			'artwork_copy' => 'Cosmetic mailer artwork should balance beauty-brand color, ingredient or routine information, QR code, reorder prompt, and a practical shipping label zone.',
			'workflow_copy' => 'Beauty fulfillment often handles many SKUs, so the box should support variant stickers, insert cards, routine order, and easy final inspection before closure.',
			'qc_copy' => 'Cosmetic mailer QC should test cap clearance, label rubbing, product order, insert fit, small text readability, and color consistency on corrugated liner.',
			'structure_options' => 'printed cosmetic mailers, beauty subscription boxes, corrugated mailers with paper inserts, influencer kit shippers, and small set boxes with compartments',
			'materials' => 'white corrugated board, kraft E-flute, micro-flute board, coated liner, paperboard inserts, molded pulp, and anti-scuff tissue',
			'finish_strategy' => 'Cosmetic mailers can use cleaner white liner, soft color printing, inside-lid messaging, or spot finish details when the structure remains courier-ready.',
			'feature' => 'Beauty ecommerce mailer, cosmetic product separation, branded inside printing, custom logo corrugated packaging',
			'industrial' => 'Cosmetics, Skincare, Beauty Ecommerce, Subscription Box',
			'paper' => 'White Corrugated Board / Kraft E-Flute / Micro-Flute / Coated Liner',
			'box_type' => 'Corrugated Cosmetic Mailer Box',
			'shape' => 'Rectangle / Beauty Kit Layout / Customized Product Fit',
			'accessories' => 'Paper insert / Product card / QR card / Tissue wrap / Sticker seal optional',
			'liner' => 'Paperboard insert / Molded pulp / Tissue paper / No liner optional',
			'printing' => 'CMYK Printing, Pantone Printing, Inside Printing, Spot UV, Matte Coating',
			'colors' => 'White / Kraft / Pastel / Beauty color system / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'beauty-skincare-packaging', 'cosmetic-paper-boxes' ),
			'tags' => array( 'cosmetic mailer box', 'beauty subscription box', 'skincare shipping box', 'cosmetic ecommerce packaging' ),
			'images' => array( 'Custom-Corrugated-Cosmetic-Mailer-Box.webp', 'Custom-Corrugated-Cosmetic-Mailer-Box-2.webp', 'Custom-Corrugated-Cosmetic-Mailer-Box-3.webp', 'Custom-Corrugated-Cosmetic-Mailer-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated cosmetic mailer box for beauty ecommerce products.',
				'Cosmetic mailer box view showing branded corrugated shipping structure.',
				'Detail view of cosmetic mailer panels for beauty product presentation.',
				'Corrugated cosmetic mailer packaging set for skincare and subscription kits.',
			),
			'details' => array( 'product count', 'bottle height', 'jar diameter', 'cap clearance', 'routine order', 'insert card size', 'scent or shade variants', 'label zone' ),
			'panel_details' => array( 'brand logo', 'routine steps', 'ingredient QR code', 'reorder message', 'shade code', 'barcode', 'shipping label zone', 'batch mark' ),
			'qc_points' => array( 'cap clearance', 'product movement', 'insert alignment', 'color consistency', 'small text readability', 'label rubbing', 'QR scan', 'packing order accuracy' ),
			'related' => array( array( '/product/custom-corrugated-subscription-box/', 'corrugated subscription box' ), array( '/product/custom-corrugated-apparel-mailer-box/', 'corrugated apparel mailer box' ) ),
			'headings' => array( 'Corrugated Cosmetic Mailer Box for Beauty Ecommerce', 'Beauty Product Shipping and Presentation Risks', 'Mailer Structures for Cosmetic Kits', 'Insert Planning for Jars, Tubes, Bottles, and Cards', 'Artwork for Routine Steps, QR Codes, and Beauty Branding', 'Board and Finish Choices for Cosmetic Mailers', 'Packing Workflow for Cosmetic SKU Sets', 'Quality Checks for Corrugated Cosmetic Mailer Boxes', 'Common Mistakes With Cosmetic Mailer Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Cosmetic Mailer Boxes',
			'quote_details' => 'product dimensions, item count, cap height, insert layout, shade or scent variants, artwork, print colors, quantity, and shipping channel',
			'mistakes' => array(
				'Using a generic shipper that lets glossy cosmetic cartons rub during delivery.',
				'Designing the insert after artwork instead of planning the unboxing layout first.',
				'Printing soft beauty colors without testing them on the chosen corrugated liner.',
				'Placing QR codes or routine steps where products or tissue will cover them.',
			),
			'duplicate_risk' => '4/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED ELECTRONICS MAILER BOX',
			'slug' => 'custom-corrugated-electronics-mailer-box',
			'keyword' => 'corrugated electronics mailer box',
			'buyer' => 'electronics accessory brands, phone accessory sellers, charger suppliers, cable brands, repair kit companies, and ecommerce distributors',
			'inside' => 'charging cables, adapters, phone cases, small gadgets, repair tools, electronic accessories, warranty cards, and instruction sheets',
			'problem' => 'protecting small electronic items from crushing, rubbing, and mixed SKU confusion while keeping warranty and compatibility information visible',
			'problem_copy' => 'Electronics mailers often carry small parts that can shift, scratch, or be confused with similar models if the box does not control layout and labeling.',
			'structure_copy' => 'An electronics mailer can use a compact corrugated mailer, die-cut insert box, accessory subscription shipper, or small gadget carton with paperboard retainers.',
			'protection_copy' => 'For electronics, insert planning should separate cables, adapters, cards, and accessories while leaving enough room for warranty documents and anti-scratch wrap.',
			'artwork_copy' => 'Electronics mailer artwork should include compatibility information, model code, warranty note, QR support link, barcode, and a clean shipping label zone.',
			'workflow_copy' => 'Electronics fulfillment needs strong SKU discipline, because many items differ only by cable type, connector, color, or device compatibility.',
			'qc_copy' => 'Electronics mailer QC should test product movement, model-code accuracy, small text readability, barcode scan, insert fit, and whether accessories scratch each other.',
			'structure_options' => 'compact corrugated gadget mailers, electronics accessory shippers, mailers with die-cut paperboard inserts, cable kit boxes, and phone accessory ecommerce cartons',
			'materials' => 'white E-flute board, kraft corrugated board, micro-flute board, coated liner, folded paperboard inserts, anti-scratch paper, and card stock',
			'finish_strategy' => 'Electronics mailers usually need precise information hierarchy, model coding, and scuff-resistant surfaces more than heavy premium finishing.',
			'feature' => 'Small electronics protection, SKU label planning, accessory insert, custom printed mailer',
			'industrial' => 'Electronics Accessories, Phone Accessories, Cables, Small Gadgets',
			'paper' => 'White E-Flute / Kraft Corrugated Board / Micro-Flute / Coated Liner',
			'box_type' => 'Corrugated Electronics Mailer Box',
			'shape' => 'Compact Rectangle / Accessory Kit / Customized Gadget Fit',
			'accessories' => 'Paper insert / Warranty card slot / QR card / Cable retainer / Sticker label optional',
			'liner' => 'Paperboard insert / Anti-scratch wrap / No liner / Molded pulp optional',
			'printing' => 'CMYK Printing, Pantone Printing, Model Code Printing, Barcode Printing, Matte Coating',
			'colors' => 'White / Kraft / Black / Technology color system / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'electronics-accessories-packaging', 'custom-paper-boxes' ),
			'tags' => array( 'electronics mailer box', 'phone accessory shipping box', 'cable packaging mailer', 'gadget ecommerce packaging' ),
			'images' => array( 'Custom-Corrugated-Electronics-Mailer-Box.webp', 'Custom-Corrugated-Electronics-Mailer-Box-2.webp', 'Custom-Corrugated-Electronics-Mailer-Box-3.webp', 'Custom-Corrugated-Electronics-Mailer-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated electronics mailer box for small gadget and accessory shipping.',
				'Electronics mailer box view showing compact corrugated structure.',
				'Detail view of electronics mailer panels for model and barcode information.',
				'Corrugated electronics mailer packaging set for accessories and ecommerce orders.',
			),
			'details' => array( 'product dimensions', 'packed weight', 'model versions', 'cable type', 'warranty card size', 'barcode area', 'anti-scratch need', 'fulfillment SKU code' ),
			'panel_details' => array( 'brand logo', 'model code', 'compatibility icons', 'warranty note', 'QR support link', 'barcode', 'shipping label zone', 'carton mark' ),
			'qc_points' => array( 'model code accuracy', 'product movement', 'insert fit', 'barcode scan', 'small text readability', 'corner compression', 'accessory rubbing', 'packing speed' ),
			'related' => array( array( '/product/custom-corrugated-subscription-box/', 'corrugated subscription box' ), array( '/product/custom-corrugated-book-mailer-box/', 'corrugated book mailer box' ) ),
			'headings' => array( 'Corrugated Electronics Mailer Box for Small Gadgets and Accessories', 'SKU Confusion and Accessory Damage Risks', 'Compact Mailer Structures for Electronics Fulfillment', 'Insert Planning for Cables, Cards, and Gadgets', 'Artwork for Compatibility, Barcode, and Warranty Details', 'Board and Finish Choices for Electronics Mailers', 'Packing Workflow for High-SKU Electronics Orders', 'Quality Checks for Corrugated Electronics Mailer Boxes', 'Common Mistakes With Electronics Mailer Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Electronics Mailer Boxes',
			'quote_details' => 'product size, accessory count, model versions, card size, insert style, barcode requirement, print colors, quantity, and fulfillment process',
			'mistakes' => array(
				'Using one generic mailer for too many model variants without clear SKU labeling.',
				'Letting cables or adapters move freely and scratch printed cards or product surfaces.',
				'Printing compatibility icons too small for fast warehouse checking.',
				'Forgetting warranty card and instruction sheet space until after the insert is approved.',
			),
			'duplicate_risk' => '4/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED FOOD DELIVERY BOX',
			'slug' => 'custom-corrugated-food-delivery-box',
			'keyword' => 'corrugated food delivery box',
			'buyer' => 'meal kit brands, bakery delivery teams, specialty food sellers, takeaway suppliers, grocery subscription programs, and premium food ecommerce companies',
			'inside' => 'meal kits, bakery items, snack packs, sauce jars, dry food sets, insulated pouches, food cards, and delivery-ready retail packs',
			'problem' => 'holding food items securely while managing ventilation, grease or moisture risk, stacking pressure, labels, and delivery presentation',
			'problem_copy' => 'Food delivery boxes must balance protection, cleanliness, airflow, food-safe contact decisions, and courier handling without making the pack hard to assemble.',
			'structure_copy' => 'A food delivery box can use a corrugated mailer, crash-lock delivery carton, handled carry box, divider shipper, or subscription-style food kit box.',
			'protection_copy' => 'For food, internal dividers, liner choice, airflow holes, grease-resistant primary wrap, and item sequence matter as much as the outer corrugated board.',
			'artwork_copy' => 'Food delivery artwork should include product name, preparation note, allergen or storage cue, QR menu link, delivery label zone, and disposal or recycling message.',
			'workflow_copy' => 'Food packing teams need fast forming, clear item order, stable stacking, and a box that works with labels, seals, cold packs, or inner food-safe wraps.',
			'qc_copy' => 'Food delivery box QC should test stacking, closure, ventilation, moisture exposure, label adhesion, divider stability, and whether food items arrive in order.',
			'structure_options' => 'food delivery mailers, meal kit corrugated boxes, bakery delivery cartons, divider food shippers, handled food boxes, and subscription food kit boxes',
			'materials' => 'kraft corrugated board, white corrugated board, food-grade liner options, grease-resistant paper, paperboard dividers, and recycled kraft liner',
			'finish_strategy' => 'Food delivery boxes should keep ink and coatings practical, especially when primary food contact, grease, humidity, or cold-chain labels are involved.',
			'feature' => 'Food delivery corrugated structure, divider planning, label zone, custom printed meal kit packaging',
			'industrial' => 'Food Delivery, Meal Kit, Bakery, Grocery, Premium Food',
			'paper' => 'Kraft Corrugated Board / White Corrugated Board / Food-Grade Liner Optional',
			'box_type' => 'Corrugated Food Delivery Box',
			'shape' => 'Rectangle / Delivery Carton / Customized Food Kit Layout',
			'accessories' => 'Paper divider / Food card / Tamper sticker / Vent holes / Handle optional',
			'liner' => 'Food-grade paper liner optional / Paper divider / Grease-resistant wrap / No direct food contact',
			'printing' => 'Flexo Printing, CMYK Printing, Pantone Printing, Food Handling Marks, Matte Coating',
			'colors' => 'Kraft / White / Fresh food color system / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'premium-food-beverage-packaging', 'food-paper-boxes' ),
			'tags' => array( 'food delivery box', 'meal kit packaging', 'corrugated food box', 'bakery delivery packaging' ),
			'images' => array( 'Custom-Corrugated-Food-Delivery-Box.webp', 'Custom-Corrugated-Food-Delivery-Box-2.webp', 'Custom-Corrugated-Food-Delivery-Box-3.webp', 'Custom-Corrugated-Food-Delivery-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated food delivery box for meal kits and premium food shipping.',
				'Food delivery box view showing corrugated structure and packing space.',
				'Detail view of corrugated food delivery panels and label area.',
				'Corrugated food delivery packaging set for takeaway, bakery, and grocery programs.',
			),
			'details' => array( 'food item count', 'packed weight', 'primary food wrap', 'temperature need', 'divider layout', 'label zone', 'delivery distance', 'tamper seal requirement' ),
			'panel_details' => array( 'brand logo', 'delivery label zone', 'storage note', 'allergen cue', 'QR menu', 'recycling mark', 'batch code', 'tamper seal area' ),
			'qc_points' => array( 'stacking strength', 'divider stability', 'closure lock', 'moisture exposure', 'label adhesion', 'vent placement', 'item order', 'carton cleanliness' ),
			'related' => array( array( '/product/custom-corrugated-bottle-shipping-box-with-dividers/', 'corrugated bottle shipping box with dividers' ), array( '/product/custom-corrugated-subscription-box/', 'corrugated subscription box' ) ),
			'headings' => array( 'Corrugated Food Delivery Box for Meal Kits and Premium Food Orders', 'Food Shipping Risks Around Moisture, Stacking, and Labels', 'Delivery Carton Structures for Food Packing', 'Divider and Liner Planning for Food Delivery Boxes', 'Artwork for Freshness, Storage Notes, and QR Menus', 'Board and Finish Choices for Food Delivery Packaging', 'Packing Workflow for Meal Kit and Takeaway Programs', 'Quality Checks for Corrugated Food Delivery Boxes', 'Common Mistakes With Food Delivery Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Food Delivery Boxes',
			'quote_details' => 'food item dimensions, packed weight, primary wrap type, temperature condition, divider need, label and seal plan, print colors, order quantity, and delivery route',
			'mistakes' => array(
				'Assuming the outer corrugated box can safely contact food without confirming the primary wrap and liner.',
				'Ignoring moisture, grease, or cold-pack condensation during sample review.',
				'Putting decorative artwork where delivery labels or tamper seals must go.',
				'Choosing a box that stacks well empty but collapses when packed with real food weight.',
			),
			'duplicate_risk' => '4/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED GIFT SET MAILER BOX',
			'slug' => 'custom-corrugated-gift-set-mailer-box',
			'keyword' => 'corrugated gift set mailer box',
			'buyer' => 'corporate gift buyers, ecommerce gift brands, seasonal campaign teams, influencer kit planners, subscription gift sellers, and promotional product suppliers',
			'inside' => 'multi-item gift sets, promotional kits, branded merchandise, candles, cards, snacks, beauty samples, and campaign launch bundles',
			'problem' => 'presenting several gift items in a shipping-ready box so the set looks intentional and each item stays in the right position',
			'problem_copy' => 'Gift set mailers need stronger presentation control than ordinary shippers because the recipient judges the campaign before touching any item.',
			'structure_copy' => 'A gift set mailer can use a printed corrugated mailer, deep subscription box, mailer with compartments, sleeve-style set box, or campaign kit shipper.',
			'protection_copy' => 'For gift sets, insert layout should control item order, card position, fragile pieces, and visual balance when the lid opens.',
			'artwork_copy' => 'Gift set mailer artwork should coordinate campaign message, sender logo, recipient note, item list, QR landing page, and a practical label zone.',
			'workflow_copy' => 'Campaign packing requires repeatable item placement, fast final checking, and clear version control when several recipients or gift tiers are produced together.',
			'qc_copy' => 'Gift set mailer QC should verify item sequence, insert fit, surface cleanliness, message card accuracy, lid reveal, and whether the outer box survives courier marks.',
			'structure_options' => 'deep corrugated gift mailers, campaign kit shippers, compartment mailers, subscription gift boxes, and corrugated PR kit boxes with paper inserts',
			'materials' => 'white corrugated board, kraft E-flute, micro-flute board, paperboard dividers, molded pulp inserts, card stock, and anti-scuff tissue',
			'finish_strategy' => 'Gift mailers can use stronger inside printing, campaign colors, and insert cards while keeping the outside ready for labels and handling.',
			'feature' => 'Multi-item gift mailer, campaign kit layout, corrugated shipping protection, custom inside printing',
			'industrial' => 'Corporate Gifts, Promotional Kits, Ecommerce Gifts, Subscription Box',
			'paper' => 'White Corrugated Board / Kraft E-Flute / Micro-Flute / Paperboard Divider',
			'box_type' => 'Corrugated Gift Set Mailer Box',
			'shape' => 'Rectangle / Multi-Item Gift Layout / Customized Kit Box',
			'accessories' => 'Paper divider / Gift card slot / Tissue wrap / Sticker seal / Ribbon optional',
			'liner' => 'Paperboard divider / Molded pulp / Tissue paper / Corrugated insert optional',
			'printing' => 'CMYK Printing, Pantone Printing, Inside Printing, Spot UV, Matte Coating',
			'colors' => 'White / Kraft / Campaign colors / Black / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'corporate-gift-packaging', 'gift-paper-boxes' ),
			'tags' => array( 'gift set mailer box', 'corporate gift packaging', 'PR kit mailer', 'subscription gift box' ),
			'images' => array( 'Custom-Corrugated-Gift-Set-Mailer-Box.webp', 'Custom-Corrugated-Gift-Set-Mailer-Box-2.webp', 'Custom-Corrugated-Gift-Set-Mailer-Box-3.webp', 'Custom-Corrugated-Gift-Set-Mailer-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated gift set mailer box for campaign and corporate gifts.',
				'Gift set mailer box view showing deep corrugated presentation structure.',
				'Detail view of corrugated gift mailer panels and insert planning.',
				'Corrugated gift set mailer packaging set for PR kits and subscription gifts.',
			),
			'details' => array( 'item count', 'largest item size', 'fragile item list', 'gift card size', 'campaign message', 'recipient version', 'insert map', 'delivery deadline' ),
			'panel_details' => array( 'campaign logo', 'gift message', 'item list', 'recipient note', 'QR landing page', 'shipping label zone', 'barcode', 'carton mark' ),
			'qc_points' => array( 'item sequence', 'insert fit', 'card accuracy', 'lid reveal', 'surface scuffing', 'closure lock', 'label placement', 'carton packing' ),
			'related' => array( array( '/product/custom-corrugated-subscription-box/', 'corrugated subscription box' ), array( '/product/custom-corrugated-candle-shipping-box/', 'corrugated candle shipping box' ) ),
			'headings' => array( 'Corrugated Gift Set Mailer Box for Campaign Kits', 'Multi-Item Gift Presentation and Shipping Risks', 'Mailer Structures for Gift Sets and PR Kits', 'Insert Planning for Gift Item Sequence and Reveal', 'Artwork for Campaign Messages, Notes, and QR Pages', 'Board and Finish Choices for Gift Mailers', 'Packing Workflow for Corporate Gift Programs', 'Quality Checks for Corrugated Gift Set Mailer Boxes', 'Common Mistakes With Gift Set Mailer Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Gift Set Mailer Boxes',
			'quote_details' => 'item list, item dimensions, total packed weight, campaign message, insert layout, gift card size, print colors, quantity, and delivery schedule',
			'mistakes' => array(
				'Designing the outer box before confirming every gift item and card size.',
				'Letting products move so the recipient opens a messy campaign kit.',
				'Using a premium inner layout but leaving no clean zone for shipping labels.',
				'Mixing recipient versions because item maps and message cards were not controlled.',
			),
			'duplicate_risk' => '4/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED SHOE MAILER BOX',
			'slug' => 'custom-corrugated-shoe-mailer-box',
			'keyword' => 'corrugated shoe mailer box',
			'buyer' => 'shoe brands, sneaker stores, footwear factories, marketplace footwear sellers, sportswear retailers, and direct-to-consumer shoe companies',
			'inside' => 'sneakers, sandals, casual shoes, footwear samples, socks, shoe care cards, and retail shoe orders',
			'problem' => 'protecting shoe shape and retail presentation while controlling box volume, pair orientation, label placement, and return workflow',
			'problem_copy' => 'Shoe mailer boxes have to protect shape and presentation because footwear can be crushed, scuffed, or returned if the pair arrives poorly arranged.',
			'structure_copy' => 'A shoe mailer box can use a deep corrugated mailer, foldable shoe shipper, reinforced ecommerce carton, or branded footwear delivery box.',
			'protection_copy' => 'For shoes, the internal length, toe-box clearance, tissue wrap, divider card, and return paperwork space should be planned around the real pair size.',
			'artwork_copy' => 'Shoe mailer artwork should include brand logo, size code area, return instruction space, shipping label zone, and optional inside-lid brand message.',
			'workflow_copy' => 'Footwear fulfillment needs clear size identification, quick packing, and a box that can handle returns without destroying the branded experience.',
			'qc_copy' => 'Shoe mailer QC should test pair fit, crush resistance, surface rubbing, label placement, size coding, closure security, and return-strip function.',
			'structure_options' => 'deep corrugated shoe mailers, foldable footwear shippers, returnable shoe boxes, reinforced ecommerce cartons, and mailers with divider cards',
			'materials' => 'kraft corrugated board, white E-flute, B-flute board, recycled liner, paperboard divider cards, tissue paper, and tear-strip components',
			'finish_strategy' => 'Shoe mailers should focus on board strength, return workflow, and size-code clarity, with inside printing used for brand message and care instructions.',
			'feature' => 'Footwear shipping protection, deep mailer structure, return-friendly closure, custom logo printing',
			'industrial' => 'Footwear, Sneakers, Sportswear, Fashion Ecommerce',
			'paper' => 'Kraft Corrugated Board / White E-Flute / B-Flute / Recycled Liner',
			'box_type' => 'Corrugated Shoe Mailer Box',
			'shape' => 'Deep Rectangle / Footwear Fit / Customized Shoe Box',
			'accessories' => 'Tear strip / Return strip / Size label area / Tissue wrap / Divider card optional',
			'liner' => 'No liner / Tissue paper / Paperboard divider / Corrugated support optional',
			'printing' => 'Flexo Printing, CMYK Printing, Pantone Printing, Inside Printing, Matte Coating',
			'colors' => 'Kraft / White / Black / Sportswear color system / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'fashion-sportswear-packaging', 'custom-paper-boxes' ),
			'tags' => array( 'shoe mailer box', 'footwear shipping box', 'sneaker packaging', 'returnable shoe mailer' ),
			'images' => array( 'Custom-Corrugated-Shoe-Mailer-Box.webp', 'Custom-Corrugated-Shoe-Mailer-Box-2.webp', 'Custom-Corrugated-Shoe-Mailer-Box-3.webp', 'Custom-Corrugated-Shoe-Mailer-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated shoe mailer box for footwear ecommerce orders.',
				'Shoe mailer box view showing deep corrugated shipping structure.',
				'Detail view of corrugated shoe mailer panels and closure design.',
				'Corrugated shoe mailer packaging set for sneakers and footwear brands.',
			),
			'details' => array( 'shoe size range', 'pair dimensions', 'packed height', 'return strip need', 'tissue wrap plan', 'size label zone', 'shipping market', 'seasonal order volume' ),
			'panel_details' => array( 'brand logo', 'shoe size code', 'return instruction', 'care note', 'shipping label zone', 'barcode', 'QR code', 'carton mark' ),
			'qc_points' => array( 'pair fit', 'toe-box clearance', 'crush resistance', 'closure lock', 'size code accuracy', 'label placement', 'return strip function', 'surface scuffing' ),
			'related' => array( array( '/product/custom-corrugated-apparel-mailer-box/', 'corrugated apparel mailer box' ), array( '/product/kraft-corrugated-mailer-box/', 'kraft corrugated mailer box' ) ),
			'headings' => array( 'Corrugated Shoe Mailer Box for Footwear Ecommerce', 'Shoe Shape, Pair Fit, and Return Risks', 'Deep Mailer Structures for Footwear Shipping', 'Internal Fit Planning for Shoes, Tissue, and Cards', 'Artwork for Size Codes, Returns, and Brand Message', 'Board and Finish Choices for Shoe Mailers', 'Packing Workflow for Footwear Orders', 'Quality Checks for Corrugated Shoe Mailer Boxes', 'Common Mistakes With Shoe Mailer Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Shoe Mailer Boxes',
			'quote_details' => 'shoe pair dimensions, size range, packed height, tissue or divider plan, return strip requirement, print colors, label zone, quantity, and shipping channel',
			'mistakes' => array(
				'Using one mailer depth for every shoe style without testing boots, sneakers, and sandals separately.',
				'Forgetting return strip or reseal planning in a category with frequent size exchanges.',
				'Letting shoe corners rub against printed panels during delivery.',
				'Printing size or barcode information too small for warehouse picking.',
			),
			'duplicate_risk' => '4/10',
		),
		array(
			'title' => 'CUSTOM CORRUGATED SUBSCRIPTION BOX',
			'slug' => 'custom-corrugated-subscription-box',
			'keyword' => 'corrugated subscription box',
			'buyer' => 'subscription box brands, monthly kit companies, ecommerce membership programs, sample set sellers, wellness clubs, and recurring campaign teams',
			'inside' => 'monthly product assortments, sample kits, lifestyle items, beauty minis, snacks, printed cards, promotional gifts, and recurring membership boxes',
			'problem' => 'making a repeatable corrugated box system that can handle changing monthly contents while keeping brand consistency and packing efficiency',
			'problem_copy' => 'Subscription boxes are difficult because contents change often, but the packaging must still feel consistent, protective, and efficient every month.',
			'structure_copy' => 'A subscription box can use a corrugated mailer, deep tuck-front box, inside-printed shipper, compartment mailer, or reusable campaign kit carton.',
			'protection_copy' => 'For subscriptions, inserts may need to be flexible: paper dividers, belly bands, card slots, tissue wrap, and modular cavities can support different monthly assortments.',
			'artwork_copy' => 'Subscription box artwork should keep the base brand system stable while allowing monthly themes, QR content, item guides, and campaign inserts to change.',
			'workflow_copy' => 'Subscription packing requires predictable assembly, fast version checking, inventory-friendly box sizes, and enough tolerance for a changing product mix.',
			'qc_copy' => 'Subscription box QC should test changing item loads, insert flexibility, inside-message accuracy, subscription card placement, closure strength, and repeat-order consistency.',
			'structure_options' => 'inside-printed subscription mailers, deep corrugated boxes, monthly kit shippers, modular insert mailers, and campaign subscription boxes with card slots',
			'materials' => 'white corrugated board, kraft E-flute, micro-flute board, recycled liner, modular paper dividers, tissue paper, and card stock inserts',
			'finish_strategy' => 'Subscription packaging can use a stable outside print with changeable inside cards, stickers, or color bands to reduce tooling pressure across monthly themes.',
			'feature' => 'Recurring subscription mailer, flexible insert planning, inside brand message, monthly kit packaging',
			'industrial' => 'Subscription Box, Ecommerce Membership, Sample Kits, Promotional Campaigns',
			'paper' => 'White Corrugated Board / Kraft E-Flute / Micro-Flute / Recycled Liner',
			'box_type' => 'Corrugated Subscription Box',
			'shape' => 'Rectangle / Monthly Kit Layout / Customized Subscription Mailer',
			'accessories' => 'Modular divider / Welcome card / QR card / Tissue wrap / Sticker seal optional',
			'liner' => 'Paperboard divider / Tissue paper / Modular insert / No liner optional',
			'printing' => 'CMYK Printing, Pantone Printing, Inside Printing, Variable Sticker System, Matte Coating',
			'colors' => 'White / Kraft / Monthly theme colors / Brand color system / Customized Color',
			'category_slugs' => array( 'corrugated-mailer-boxes', 'corporate-gift-packaging', 'home-lifestyle-packaging', 'custom-paper-boxes' ),
			'tags' => array( 'subscription box', 'monthly kit packaging', 'corrugated subscription mailer', 'inside printed mailer' ),
			'images' => array( 'Custom-Corrugated-Subscription-Box.webp', 'Custom-Corrugated-Subscription-Box-2.webp', 'Custom-Corrugated-Subscription-Box-3.webp', 'Custom-Corrugated-Subscription-Box-4.webp' ),
			'captions' => array(
				'Custom corrugated subscription box for recurring ecommerce kits.',
				'Subscription box view showing corrugated mailer structure and brand panels.',
				'Detail view of subscription box panels for monthly kit information.',
				'Corrugated subscription box set for sample kits and membership programs.',
			),
			'details' => array( 'monthly item range', 'maximum product size', 'packed weight range', 'insert flexibility', 'card size', 'theme version list', 'label zone', 'subscription volume' ),
			'panel_details' => array( 'brand logo', 'monthly theme', 'item guide', 'QR content', 'welcome message', 'shipping label zone', 'barcode', 'carton mark' ),
			'qc_points' => array( 'changing item fit', 'insert flexibility', 'inside message accuracy', 'closure lock', 'label placement', 'card fit', 'print consistency', 'repeat order standard' ),
			'related' => array( array( '/product/custom-corrugated-gift-set-mailer-box/', 'corrugated gift set mailer box' ), array( '/product/custom-corrugated-cosmetic-mailer-box/', 'corrugated cosmetic mailer box' ) ),
			'headings' => array( 'Corrugated Subscription Box for Monthly Kits and Membership Programs', 'Recurring Kit Packaging Risks', 'Mailer Structures for Subscription Fulfillment', 'Flexible Insert Planning for Changing Monthly Contents', 'Artwork for Monthly Themes, QR Content, and Item Guides', 'Board and Finish Choices for Subscription Boxes', 'Packing Workflow for Recurring Orders', 'Quality Checks for Corrugated Subscription Boxes', 'Common Mistakes With Subscription Boxes' ),
			'quote_heading' => 'Quote Details for Corrugated Subscription Boxes',
			'quote_details' => 'monthly item range, largest product size, packed weight range, insert flexibility, artwork system, card size, print colors, recurring quantity, and shipping route',
			'mistakes' => array(
				'Designing the first month beautifully but leaving no tolerance for future product mixes.',
				'Changing the box structure too often and losing packing speed or brand consistency.',
				'Printing monthly details directly on the box when a card or sticker system would be more flexible.',
				'Ignoring storage volume for recurring box inventory.',
			),
			'duplicate_risk' => '4/10',
		),
	);
}

$marker         = 'product-samples-corrugated-mailers-202607';
$products       = vpn_corrugated_products();
$category_names = array(
	'corrugated-mailer-boxes'             => 'Corrugated Mailer Boxes',
	'custom-paper-boxes'                  => 'Custom Paper Boxes',
	'fashion-sportswear-packaging'        => 'Fashion and Sportswear Packaging',
	'back-to-school-stationery-packaging' => 'Back-to-School and Stationery Packaging',
	'wine-premium-drink-packaging'        => 'Wine and Premium Drink Packaging',
	'premium-food-beverage-packaging'     => 'Premium Food and Beverage Packaging',
	'packaging-accessories'               => 'Packaging Accessories',
	'candle-packaging-boxes'              => 'Candle Packaging Boxes',
	'home-lifestyle-packaging'            => 'Home and Lifestyle Packaging',
	'beauty-skincare-packaging'           => 'Beauty and Skincare Packaging',
	'cosmetic-paper-boxes'                => 'Cosmetic Paper Boxes',
	'electronics-accessories-packaging'   => 'Electronics Accessories Packaging',
	'food-paper-boxes'                    => 'Food Paper Boxes',
	'corporate-gift-packaging'            => 'Corporate Gift Packaging',
	'gift-paper-boxes'                    => 'Gift Paper Boxes',
);
$term_cache     = array();

foreach ( $category_names as $slug => $name ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $term || is_wp_error( $term ) ) {
		$created = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		if ( is_wp_error( $created ) ) {
			fwrite( STDERR, 'Failed to create product category: ' . $slug . PHP_EOL );
			exit( 1 );
		}
		$term = get_term( (int) $created['term_id'], 'product_cat' );
	}
	$term_cache[ $slug ] = (int) $term->term_id;
}

$audit       = array( '# Corrugated Mailer Product Import Audit', '' );
$text_export = array( '# Corrugated Mailer Product Descriptions Text Only', '' );

foreach ( $products as $product ) {
	$image_ids = array();
	foreach ( $product['images'] as $index => $filename ) {
		$image_ids[] = vpn_corrugated_attachment_id(
			$filename,
			$product['keyword'] . ' for ecommerce shipping packaging, view ' . ( $index + 1 ),
			$product['captions'][ $index ],
			$product['captions'][ $index ]
		);
	}

	$missing = array();
	foreach ( $product['images'] as $index => $filename ) {
		if ( empty( $image_ids[ $index ] ) ) {
			$missing[] = $filename;
		}
	}

	if ( $missing ) {
		echo 'Missing images for ' . $product['title'] . ': ' . implode( ', ', $missing ) . PHP_EOL;
		continue;
	}

	$short   = vpn_corrugated_short_description( $product );
	$content = vpn_corrugated_content( $product, $image_ids );
	$existing = get_page_by_path( $product['slug'], OBJECT, 'product' );
	$postarr  = array(
		'post_type'    => 'product',
		'post_status'  => 'publish',
		'post_title'   => $product['title'],
		'post_name'    => $product['slug'],
		'post_excerpt' => $short,
		'post_content' => $content,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$product_id    = wp_update_post( $postarr, true );
	} else {
		$product_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $product_id ) || ! $product_id ) {
		echo 'Failed product: ' . $product['title'] . PHP_EOL;
		continue;
	}

	foreach ( $image_ids as $image_id ) {
		wp_update_post(
			array(
				'ID'          => (int) $image_id,
				'post_parent' => (int) $product_id,
			)
		);
	}

	$term_ids = array();
	foreach ( $product['category_slugs'] as $slug ) {
		if ( ! empty( $term_cache[ $slug ] ) ) {
			$term_ids[] = $term_cache[ $slug ];
		}
	}

	wp_set_object_terms( $product_id, $term_ids, 'product_cat', false );
	wp_set_object_terms( $product_id, 'simple', 'product_type', false );
	wp_set_object_terms(
		$product_id,
		array_merge(
			array( $product['keyword'], 'corrugated mailer box', 'ecommerce packaging', 'shipping box', 'custom paper box' ),
			$product['tags']
		),
		'product_tag',
		false
	);

	set_post_thumbnail( $product_id, $image_ids[0] );
	update_post_meta( $product_id, '_thumbnail_id', (int) $image_ids[0] );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $image_ids, 1 ) ) );
	update_post_meta( $product_id, '_sku', 'sample-corrugated-202607-' . $product['slug'] );
	update_post_meta( $product_id, '_regular_price', '' );
	update_post_meta( $product_id, '_price', '' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_corrugated_specs( $product ) );
	update_post_meta( $product_id, '_vpn_sample_import', $marker );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $product['keyword'] );
	update_post_meta( $product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER' );
	update_post_meta( $product_id, 'rank_math_description', substr( $product['title'] . ' for ecommerce and shipping buyers, customized with corrugated board, inserts, logo printing, label zones, and MOQ 1000 boxes.', 0, 154 ) );

	$saved_content = get_post_field( 'post_content', $product_id );
	$words         = str_word_count( wp_strip_all_tags( $saved_content ) );
	$figures       = substr_count( $saved_content, '<figure class="product-inline-figure' );
	$specs         = get_post_meta( $product_id, '_custom_box_product_specs', true );
	$gallery       = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product_id, '_product_image_gallery', true ) ) ) );

	$audit[] = '## ' . $product['title'];
	$audit[] = '- ID: ' . $product_id;
	$audit[] = '- URL: ' . get_permalink( $product_id );
	$audit[] = '- Categories: ' . implode( ', ', $product['category_slugs'] );
	$audit[] = '- Status: ' . get_post_status( $product_id );
	$audit[] = '- Focus keyword: ' . $product['keyword'];
	$audit[] = '- Words: ' . $words;
	$audit[] = '- Short description words: ' . str_word_count( wp_strip_all_tags( $short ) );
	$audit[] = '- Content H1 count: ' . preg_match_all( '/<h1\b/i', $saved_content );
	$audit[] = '- Specs rows: ' . ( is_array( $specs ) ? count( $specs ) : 0 );
	$audit[] = '- Gallery images: ' . count( $gallery );
	$audit[] = '- Inline figures: ' . $figures;
	$audit[] = '- Source files: ' . implode( ', ', $product['images'] );
	$audit[] = '- Missing image bases: none';
	$audit[] = '- Duplicate risk score: ' . $product['duplicate_risk'];
	$audit[] = '';

	$text_export[] = '## ' . $product['title'];
	$text_export[] = wp_strip_all_tags( $short . "\n\n" . $saved_content );
	$text_export[] = '';

	echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . ' images=' . count( $image_ids ) . ' figures=' . $figures . PHP_EOL;
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-corrugated-mailers-202607-audit.md', implode( PHP_EOL, $audit ) );
file_put_contents( dirname( __DIR__ ) . '/product-samples-corrugated-mailers-202607-descriptions-text-only.md', implode( PHP_EOL, $text_export ) );

echo 'Corrugated mailer product import complete.' . PHP_EOL;
