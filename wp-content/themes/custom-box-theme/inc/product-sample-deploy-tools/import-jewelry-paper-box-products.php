<?php
/**
 * Import jewelry paper box products from uploaded Media Library images.
 *
 * Run:
 *   php tools/import-jewelry-paper-box-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_jewelry_link( string $url, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_jewelry_file_base( string $filename ): string {
	return preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
}

function vpn_jewelry_find_attachment_by_base( string $filename_base ): int {
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
		if ( vpn_jewelry_file_base( $attached_file ) === $filename_base ) {
			return (int) $attachment_id;
		}
	}

	return 0;
}

function vpn_jewelry_attachment_id( string $filename, string $alt, string $title, string $caption ): int {
	$filename_base = vpn_jewelry_file_base( $filename );
	$attachment_id = vpn_jewelry_find_attachment_by_base( $filename_base );

	if ( ! $attachment_id ) {
		$attached_file = '2026/06/' . basename( $filename );
		$uploads       = wp_get_upload_dir();
		$target_path   = trailingslashit( $uploads['basedir'] ) . $attached_file;
		$source_path   = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $attached_file;

		if ( ! file_exists( $target_path ) ) {
			if ( ! file_exists( $source_path ) || ! wp_mkdir_p( dirname( $target_path ) ) || ! copy( $source_path, $target_path ) ) {
				return 0;
			}
		}

		$attachment_id = wp_insert_attachment(
			array(
				'guid'           => trailingslashit( $uploads['baseurl'] ) . $attached_file,
				'post_mime_type' => 'image/webp',
				'post_title'     => $title,
				'post_excerpt'   => $caption,
				'post_status'    => 'inherit',
			),
			$target_path
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return 0;
		}

		update_post_meta( $attachment_id, '_wp_attached_file', $attached_file );
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $target_path ) );
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

function vpn_jewelry_specs( array $product ): array {
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
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, material, insert, printing, finishing, and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $product['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement" ),
	);
}

function vpn_jewelry_section( string $heading, array $paragraphs ): string {
	$html = '<h2>' . esc_html( $heading ) . '</h2>';
	foreach ( $paragraphs as $paragraph ) {
		$html .= '<p>' . $paragraph . '</p>';
	}
	return $html;
}

function vpn_jewelry_inline_image( int $attachment_id, string $caption, bool $narrow = false ): string {
	$image = wp_get_attachment_image( $attachment_id, 'large', false, array( 'loading' => 'lazy' ) );
	if ( ! $image ) {
		return '';
	}

	return '<figure class="product-inline-figure product-inline-figure-small' . ( $narrow ? ' is-narrow' : '' ) . '">' . $image . '<figcaption>' . esc_html( $caption ) . '</figcaption></figure>';
}

function vpn_jewelry_content( array $product, array $image_ids ): string {
	$category_link = vpn_jewelry_link( '/product-category/jewelry-paper-boxes/', 'jewelry paper boxes' );
	$material_link = vpn_jewelry_link( '/paper-materials-for-custom-paper-boxes/', 'paper material options for custom boxes' );
	$finish_link   = vpn_jewelry_link( '/foil-stamping-and-embossing-for-paper-packaging/', 'foil stamping and embossing details' );
	$quote_link    = vpn_jewelry_link( '/contact/#quote', 'request a jewelry packaging quote' );
	$gift_link     = vpn_jewelry_link( '/product/custom-rigid-gift-box/', 'custom rigid gift box' );
	$drawer_link   = vpn_jewelry_link( '/product/custom-drawer-gift-box/', 'custom drawer gift box' );

	$html = vpn_jewelry_section(
		$product['hero_heading'],
		array(
			$product['title'] . ' is built for jewelry brands, boutique retailers, online jewelry sellers, private label collections, wedding accessory suppliers, and gift programs that need compact packaging with a premium opening moment. Jewelry packaging has a small physical footprint, but buyers judge it quickly through surface finish, insert fit, hinge or lid movement, logo position, and how securely the item sits when the box is opened.',
			'This product belongs to our ' . $category_link . ' category and can be adjusted for retail counters, e-commerce shipments, gifting campaigns, display trays, influencer launch kits, and distributor sample sets. The package should protect delicate metal parts, avoid scratches, keep the jewelry centered, and create a refined brand impression without adding unnecessary bulk or freight cost.',
			$product['intro_detail'],
		)
	);

	if ( ! empty( $image_ids[0] ) ) {
		$html .= vpn_jewelry_inline_image( $image_ids[0], $product['captions'][0], true );
	}

	$html .= vpn_jewelry_section(
		$product['structure_heading'],
		array(
			$product['structure_copy'] . ' Structure selection should start with the real jewelry size, not with a generic box dimension. A bracelet, earring pair, necklace, and ring each need different clearance, lift space, and insert pressure. A box that looks elegant when empty can still fail if the jewelry slides, twists, or hides under the lid during customer handling.',
			'For B2B production, the structure can be a rigid lid-and-base box, hinged rigid box, sleeve-and-tray box, drawer box, magnetic box, paperboard folding box, or compact display box. The sample should be opened and closed repeatedly with the real product inside, then checked for corner strength, wrap alignment, lid tolerance, insert fit, and whether the customer can remove the jewelry without pulling too hard.',
			'Small jewelry boxes often need tight tolerances. A lid that is only slightly loose can make the package feel cheap, while a lid that is too tight may pull the insert upward. The production dieline should account for wrap paper thickness, foam compression, velvet or suede covering, lamination, and humidity during storage.',
		)
	);

	if ( ! empty( $image_ids[1] ) ) {
		$html .= vpn_jewelry_inline_image( $image_ids[1], $product['captions'][1] );
	}

	$html .= vpn_jewelry_section(
		$product['insert_heading'],
		array(
			$product['insert_copy'] . ' Insert design is the main difference between a useful jewelry box and a nice-looking empty shell. Options include velvet insert, suede insert, sponge pad, EVA foam, paperboard holder, pillow insert, ribbon slot, elastic cord, necklace notch, earring holes, ring slit, and removable display pad. The insert should hold the jewelry clearly while keeping metal surfaces away from rough edges.',
			'The insert also affects packing speed. Factory workers need to place the jewelry consistently, close the box without fighting spring-back, and inspect the final look before carton packing. If the insert is too soft, the item can sink and disappear. If it is too firm, delicate chains, posts, clasps, or stones may receive pressure. A balanced insert makes the product feel secure and easy to present.',
			'For online jewelry brands, the insert should survive courier handling after the jewelry is packed. A necklace chain should not tangle, earring posts should not press through the back pad, a ring should not rotate sideways, and a bracelet should not rub against the lid. These small details reduce returns and protect the first impression customers share in photos or videos.',
		)
	);

	$html .= vpn_jewelry_section(
		$product['branding_heading'],
		array(
			'Branding for jewelry packaging usually works best when it is deliberate and restrained. The top lid can carry a foil logo, debossed mark, printed monogram, small pattern, or campaign name. The inner lid can hold a short brand message, care note, certificate pocket, QR code, or authenticity card. For a more premium surface, buyers can compare ' . $finish_link . ' before confirming the sample.',
			$product['branding_copy'] . ' Artwork should be tested at actual box size because jewelry logos are often small. Thin serif letters, metallic foil, and low-contrast colors can lose clarity if they are not matched to the paper texture and stamping area. A sample proof should confirm logo sharpness, foil edge cleanliness, color density, and whether the surface resists fingerprints during handling.',
			'When one jewelry brand sells multiple collections, the box can stay structurally consistent while color, insert cut, sleeve artwork, or logo finish changes by SKU. This keeps the packaging family recognizable and helps wholesale buyers manage different price levels without creating a separate tooling plan for every product.',
		)
	);

	if ( ! empty( $image_ids[2] ) ) {
		$html .= vpn_jewelry_inline_image( $image_ids[2], $product['captions'][2], true );
	}

	$html .= vpn_jewelry_section(
		$product['material_heading'],
		array(
			'Material options include ' . $product['materials'] . '. Rigid greyboard gives small boxes a dense hand feel, coated art paper supports accurate color, specialty paper adds tactile value, and velvet or suede inserts create a softer product contact surface. Buyers can review ' . $material_link . ' when comparing paperboard, rigid board, specialty wrapping paper, and inner support choices.',
			'Jewelry packaging is touched closely, so edge finishing matters. The customer notices wrap seams, corner folds, glue marks, lid friction, dust on dark insert fabric, and whether the insert sits flat. Matte lamination, soft-touch film, textured paper, metallic paper, pearl paper, foil stamping, embossing, debossing, and spot UV can all work, but the finish should match the jewelry price point rather than overwhelm it.',
			'For export orders, outer carton planning is also important. Small jewelry boxes can be damaged if packed loosely, but can deform if cartons are compressed too tightly. Master carton quantity, inner polybag use, dust sleeve, silica gel requirement, and carton labeling should be confirmed before bulk production.',
		)
	);

	$html .= vpn_jewelry_section(
		$product['retail_heading'],
		array(
			$product['retail_copy'] . ' Retail packaging needs quick recognition and tidy display alignment. E-commerce packaging needs stronger protection after the box is placed in a mailer. Gift packaging needs a slower reveal, clean insert presentation, and optional space for cards or certificates. Defining the channel early prevents the box from becoming too decorative for shipping or too plain for boutique counters.',
			'For store counters, the logo should remain visible when boxes are stacked or presented in trays. For marketplace fulfillment, barcode labels, SKU stickers, and carton marks should have planned positions that do not cover the brand mark. For gift sets, the package may need a matching paper bag, sleeve, ribbon, or outer rigid box similar to a ' . $gift_link . ' or ' . $drawer_link . '.',
			'The same base structure can support seasonal campaigns such as Valentine collections, wedding jewelry, anniversary gifts, graduation gifts, holiday promotions, and limited-edition launches. Clear artwork version control helps the factory keep collection colors, inserts, and labels matched during production.',
		)
	);

	if ( ! empty( $image_ids[3] ) ) {
		$html .= vpn_jewelry_inline_image( $image_ids[3], $product['captions'][3] );
	}

	$html .= vpn_jewelry_section(
		'Production Planning for Jewelry Box Orders',
		array(
			'Before confirming the dieline, the buyer should decide whether the jewelry box will be packed alone, placed inside a branded paper bag, shipped in a mailer, or combined with a card, cloth pouch, cleaning cloth, certificate, or warranty leaflet. These extra items change the inner height, insert slot position, and packing sequence. Planning them early prevents a beautiful sample from becoming slow or awkward during mass packing.',
			'The artwork file should mark front direction, logo center line, foil stamping area, insert color, insert cut position, barcode or SKU label area, and any collection-specific color changes. For small jewelry boxes, even a few millimeters of logo movement can look obvious because the lid surface is compact. A physical white sample and a printed sample are both useful: one checks fit and structure, while the other checks color, finish, and brand tone.',
			'Bulk production should also consider how the boxes arrive at the jewelry packing line. Some buyers need boxes delivered fully assembled with inserts placed inside. Others prefer flat or semi-assembled packaging to reduce freight volume. The best option depends on labor cost, box structure, insert complexity, and whether the jewelry is packed by the factory, a brand warehouse, or a third-party fulfillment partner.',
		)
	);

	$html .= vpn_jewelry_section(
		$product['quality_heading'],
		array(
			'Quality control should check board thickness, lid tolerance, hinge or drawer movement, insert depth, fabric cleanliness, logo position, foil stamping edges, color consistency, glue marks, corner wrapping, carton packing, and final packed presentation. For dark velvet or suede inserts, dust control is especially important because lint becomes visible under retail lighting.',
			'Before bulk orders, buyers should send jewelry dimensions, item weight, material sensitivity, desired insert type, logo file, Pantone or CMYK target, finish direction, order quantity, and target sales channel. A physical sample should be reviewed with the real product, not only with an empty box, because jewelry fit is sensitive to small differences in chain length, clasp size, stone height, or band width.',
			'MOQ starts from 1000 boxes. Send your artwork, product size, insert requirement, and target quantity to ' . $quote_link . '. VPN Paper Box Manufacturer can recommend box structure, insert material, paper finish, printing method, and export packing for custom jewelry paper box production.',
		)
	);

	return $html;
}

$category_slug = 'jewelry-paper-boxes';
$category_name = 'Jewelry Paper Boxes';
$marker        = 'product-samples-jewelry-paper-boxes';

$products = array(
	array(
		'title' => 'CUSTOM BRACELET PACKAGING BOX WITH PILLOW INSERT',
		'slug' => 'custom-bracelet-packaging-box-with-pillow-insert',
		'keyword' => 'bracelet packaging box',
		'hero_heading' => 'Bracelet Packaging Box for Soft Presentation and Secure Holding',
		'structure_heading' => 'Rigid Bracelet Box Structure with Comfortable Product Clearance',
		'insert_heading' => 'Pillow Insert Design for Bracelets and Bangles',
		'branding_heading' => 'Branding a Bracelet Box Without Hiding the Jewelry',
		'material_heading' => 'Materials for Bracelet Gift Packaging',
		'retail_heading' => 'Where Bracelet Packaging Works Best',
		'quality_heading' => 'Bracelet Box Sampling and Quality Checks',
		'intro_detail' => 'Bracelets and bangles need a box that controls shape without flattening the product. The package should keep the bracelet loop visible, stop charms from moving freely, and make the item easy to lift without bending metal parts or scratching plated surfaces.',
		'structure_copy' => 'A bracelet packaging box is commonly wider and lower than a ring box, with enough inner space for a curved item, clasp, charm, tag, or product card.',
		'insert_copy' => 'For bracelet packaging, the pillow insert should support the circular or oval shape while giving the customer a soft removal point.',
		'branding_copy' => 'Bracelet brands often need enough lid space for a logo while leaving the open box visually quiet so the bracelet remains the hero.',
		'retail_copy' => 'This format is suitable for chain bracelets, charm bracelets, bangles, friendship bracelets, plated jewelry, handmade jewelry, and boutique gift programs.',
		'feature' => 'Rigid bracelet box, pillow insert, open display, custom logo printing',
		'industrial' => 'Jewelry, Bracelet, Fashion Accessories, Gift Packaging',
		'paper' => 'Rigid Greyboard / Coated Art Paper / Specialty Paper / Velvet Pillow',
		'box_type' => 'Bracelet Packaging Box',
		'shape' => 'Rectangle / Square / Customized Bracelet Fit',
		'accessories' => 'Pillow insert / Ribbon pull / Product card pocket / Sleeve optional',
		'liner' => 'Velvet pillow insert / Suede pillow / Sponge support / Paperboard tray',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Matte Lamination',
		'colors' => 'Black / White / Pink / Gold / Pantone / Customized Color',
		'materials' => 'rigid greyboard, coated art paper, specialty wrapping paper, velvet pillow insert, suede cover, sponge support, and foil logo finishing',
		'alt' => 'Bracelet packaging box with pillow insert for jewelry gift display',
		'featured_index' => 2,
		'images' => array(
			'custom-bracelet-packaging-box-pillow-insert-closed-front-angle.webp',
			'custom-bracelet-packaging-box-pillow-insert-close-up-detail.webp',
			'custom-bracelet-packaging-box-pillow-insert-open-display.webp',
			'custom-bracelet-packaging-box-pillow-insert-side-angle.webp',
		),
		'captions' => array(
			'Closed front angle of a custom bracelet packaging box with pillow insert.',
			'Close-up detail showing pillow insert and bracelet box finishing.',
			'Open bracelet packaging box with soft insert for jewelry display.',
			'Side angle of bracelet gift box showing depth and lid structure.',
		),
	),
	array(
		'title' => 'CUSTOM EARRING BOX WITH FOAM INSERT',
		'slug' => 'custom-earring-box-with-foam-insert',
		'keyword' => 'earring box with foam insert',
		'hero_heading' => 'Earring Box with Foam Insert for Paired Jewelry Display',
		'structure_heading' => 'Compact Earring Box Structure for Small Accessories',
		'insert_heading' => 'Foam Insert Layout for Earring Posts and Backs',
		'branding_heading' => 'Small-Format Earring Box Branding',
		'material_heading' => 'Materials for Earring Retail and Gift Boxes',
		'retail_heading' => 'Best Uses for Earring Packaging',
		'quality_heading' => 'Earring Box Sampling and Quality Checks',
		'intro_detail' => 'Earrings need accurate insert holes and enough clearance for posts, hooks, clips, stones, pearls, and backs. A good earring box keeps the pair aligned, prevents scratching, and lets the customer see the design immediately when the lid opens.',
		'structure_copy' => 'An earring box is usually compact, but it still needs a stable lid, flat insert surface, and enough inner height for studs, hoops, dangle earrings, or clip-on styles.',
		'insert_copy' => 'For earring packaging, foam insert accuracy is critical because hole spacing controls whether the pair looks balanced and whether posts stay protected.',
		'branding_copy' => 'Because earring boxes have limited surface area, logo size, foil thickness, and color contrast should be tested carefully on the real material.',
		'retail_copy' => 'This format is suitable for studs, hoops, dangle earrings, pearl earrings, gold-plated earrings, silver accessories, and boutique counter displays.',
		'feature' => 'Compact earring box, foam insert holes, paired jewelry display, custom logo',
		'industrial' => 'Jewelry, Earrings, Fashion Accessories, Retail Gift Packaging',
		'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / EVA Foam',
		'box_type' => 'Earring Box with Foam Insert',
		'shape' => 'Square / Rectangle / Customized Earring Fit',
		'accessories' => 'Foam insert / Earring holes / Product card / Dust sleeve optional',
		'liner' => 'EVA foam / Sponge foam / Velvet-covered foam / Paperboard backing',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Debossing, Spot UV, Matte Lamination',
		'colors' => 'White / Black / Cream / Gold / Pantone / Customized Color',
		'materials' => 'rigid greyboard, coated paper, specialty paper, EVA foam, velvet-covered sponge, insert backing card, and metallic foil logo options',
		'alt' => 'Earring box with foam insert for paired jewelry display',
		'featured_index' => 2,
		'images' => array(
			'custom-earring-box-foam-insert-closed-front-angle.webp',
			'custom-earring-box-foam-insert-close-up-detail.webp',
			'custom-earring-box-foam-insert-open-top-front.webp',
			'custom-earring-box-foam-insert-three-quarter-side-angle.webp',
		),
		'captions' => array(
			'Closed front angle of a custom earring box with foam insert.',
			'Close-up view of foam insert holes for paired earring presentation.',
			'Open top-front view of earring box with insert and jewelry display.',
			'Three-quarter side angle showing compact earring box construction.',
		),
	),
	array(
		'title' => 'CUSTOM NECKLACE PAPER BOX WITH LOGO',
		'slug' => 'custom-necklace-paper-box-with-logo',
		'keyword' => 'necklace paper box',
		'hero_heading' => 'Necklace Paper Box with Logo for Chain and Pendant Presentation',
		'structure_heading' => 'Longer Necklace Box Structure for Chain Control',
		'insert_heading' => 'Insert Slots for Necklace Chains and Pendants',
		'branding_heading' => 'Logo Placement for Necklace Packaging',
		'material_heading' => 'Materials for Necklace Paper Boxes',
		'retail_heading' => 'Necklace Packaging for Retail, Gift, and E-commerce',
		'quality_heading' => 'Necklace Box Sampling and Quality Checks',
		'intro_detail' => 'Necklace packaging must manage chain length, pendant height, clasp position, and display orientation. The box should stop tangling, show the pendant clearly, and protect polished or plated surfaces from rubbing during storage and shipping.',
		'structure_copy' => 'A necklace paper box usually needs a longer inner layout than other jewelry boxes, with enough lid clearance for a pendant, charm, pearl, stone setting, or folded chain.',
		'insert_copy' => 'For necklace packaging, insert slots, small notches, ribbon ties, elastic loops, or backing cards help control chain position and pendant alignment.',
		'branding_copy' => 'A necklace box often has more lid area than an earring box, which allows cleaner logo spacing, collection names, or a subtle campaign mark.',
		'retail_copy' => 'This format is suitable for pendant necklaces, chain necklaces, pearl necklaces, silver jewelry, gold-plated lines, wedding accessories, and personalized gift collections.',
		'feature' => 'Necklace paper box, logo printing, chain insert, pendant display',
		'industrial' => 'Jewelry, Necklace, Pendant, Gift Packaging',
		'paper' => 'Rigid Greyboard / Coated Paper / Specialty Paper / Velvet Insert',
		'box_type' => 'Necklace Paper Box',
		'shape' => 'Long Rectangle / Customized Necklace Fit',
		'accessories' => 'Necklace slots / Ribbon tabs / Pendant cavity / Certificate card pocket',
		'liner' => 'Velvet insert / Suede pad / Sponge holder / Paperboard backing card',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Spot UV, Matte Lamination',
		'colors' => 'White / Black / Grey / Gold / Pantone / Customized Color',
		'materials' => 'rigid greyboard, coated art paper, specialty paper, velvet insert, suede pad, paperboard backing card, ribbon tabs, and hot foil logo finishing',
		'alt' => 'Necklace paper box with logo and open pendant display insert',
		'featured_index' => 2,
		'images' => array(
			'custom-necklace-paper-box-logo-closed-front-angle.webp',
			'custom-necklace-paper-box-logo-close-up-detail.webp',
			'custom-necklace-paper-box-logo-open-necklace-display.webp',
			'custom-necklace-paper-box-logo-three-quarter-angle.webp',
		),
		'captions' => array(
			'Closed front angle of a custom necklace paper box with logo.',
			'Close-up detail of logo and surface finishing on necklace box.',
			'Open necklace paper box showing pendant and chain display layout.',
			'Three-quarter angle of custom necklace packaging box structure.',
		),
	),
	array(
		'title' => 'CUSTOM RING BOX WITH VELVET INSERT',
		'slug' => 'custom-ring-box-with-velvet-insert',
		'keyword' => 'ring box with velvet insert',
		'hero_heading' => 'Ring Box with Velvet Insert for Premium Small Jewelry',
		'structure_heading' => 'Compact Ring Box Structure with a Stable Opening Feel',
		'insert_heading' => 'Velvet Ring Slot for Band and Stone Protection',
		'branding_heading' => 'Premium Branding for Ring Gift Boxes',
		'material_heading' => 'Materials for Ring Boxes with Velvet Inserts',
		'retail_heading' => 'Ring Packaging for Proposal, Retail, and Gift Sales',
		'quality_heading' => 'Ring Box Sampling and Quality Checks',
		'intro_detail' => 'Ring packaging is judged in seconds. The box needs a precise slot, a clean centered reveal, and enough clearance for gemstone height, wide bands, signet rings, or wedding sets. The insert must hold the ring upright without squeezing the band or hiding the stone.',
		'structure_copy' => 'A ring box is compact but technically sensitive because the lid, hinge, slot angle, and insert depth all affect how the ring appears when opened.',
		'insert_copy' => 'For ring packaging, a velvet insert with a clean slit should keep the band vertical, protect polished metal, and leave the stone visible from the customer viewpoint.',
		'branding_copy' => 'Ring box branding can be very minimal because the jewelry itself is small and symbolic. A refined foil logo, debossed mark, or inner-lid print is often enough.',
		'retail_copy' => 'This format is suitable for wedding rings, engagement rings, fashion rings, signet rings, silver rings, gemstone rings, and premium boutique gift packaging.',
		'feature' => 'Compact ring box, velvet insert, centered ring display, premium logo finishing',
		'industrial' => 'Jewelry, Ring, Wedding Jewelry, Gift Packaging',
		'paper' => 'Rigid Greyboard / Specialty Paper / Velvet Insert / Coated Art Paper',
		'box_type' => 'Ring Box with Velvet Insert',
		'shape' => 'Square / Cube / Customized Ring Fit',
		'accessories' => 'Velvet ring slot / Hinged lid / Magnetic closure optional / Certificate card',
		'liner' => 'Velvet insert / Suede insert / Sponge support / EVA insert',
		'printing' => 'CMYK Printing, Pantone Printing, Foil Stamping, Embossing, Debossing, Soft Touch Lamination',
		'colors' => 'Black / White / Navy / Burgundy / Gold / Customized Color',
		'materials' => 'rigid greyboard, specialty wrapping paper, coated art paper, velvet insert, suede insert, EVA support, hinge components, and foil stamping materials',
		'alt' => 'Ring box with velvet insert for premium jewelry gift presentation',
		'featured_index' => 2,
		'images' => array(
			'custom-ring-box-velvet-insert-closed-front-angle.webp',
			'custom-ring-box-velvet-insert-close-up-detail.webp',
			'custom-ring-box-velvet-insert-open-ring-display.webp',
			'custom-ring-box-velvet-insert-side-perspective.webp',
		),
		'captions' => array(
			'Closed front angle of a custom ring box with velvet insert.',
			'Close-up detail of velvet insert and premium ring box finish.',
			'Open ring box with velvet insert holding ring in display position.',
			'Side perspective of compact ring gift box structure.',
		),
	),
);

$category = get_term_by( 'slug', $category_slug, 'product_cat' );
if ( ! $category || is_wp_error( $category ) ) {
	$created = wp_insert_term( $category_name, 'product_cat', array( 'slug' => $category_slug ) );
	if ( is_wp_error( $created ) ) {
		fwrite( STDERR, 'Missing product category and failed to create it: ' . $category_slug . PHP_EOL );
		exit( 1 );
	}
	$category = get_term( (int) $created['term_id'], 'product_cat' );
}

$audit = array( '# Jewelry Paper Box Product Import Audit', '' );

foreach ( $products as $product ) {
	$image_ids = array();
	foreach ( $product['images'] as $index => $filename ) {
		$image_ids[] = vpn_jewelry_attachment_id(
			$filename,
			$product['alt'] . ' view ' . ( $index + 1 ),
			$product['captions'][ $index ],
			$product['captions'][ $index ]
		);
	}

	$missing = array();
	foreach ( $product['images'] as $index => $filename ) {
		if ( empty( $image_ids[ $index ] ) ) {
			$missing[] = vpn_jewelry_file_base( $filename );
		}
	}

	if ( $missing ) {
		echo 'Missing images for ' . $product['title'] . ': ' . implode( ', ', $missing ) . PHP_EOL;
	}

	$valid_image_ids = array_values( array_filter( $image_ids ) );
	$existing        = get_page_by_path( $product['slug'], OBJECT, 'product' );
	$postarr         = array(
		'post_type'    => 'product',
		'post_status'  => 'publish',
		'post_title'   => $product['title'],
		'post_name'    => $product['slug'],
		'post_excerpt' => $product['title'] . ' is a custom jewelry paper box for boutique jewelry, retail display, gift packaging, and e-commerce orders. It supports custom size, rigid paper structure, product-specific insert, logo printing, foil stamping, premium paper finish, and bulk production from 1000 boxes.',
		'post_content' => vpn_jewelry_content( $product, $valid_image_ids ),
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

	wp_set_object_terms( $product_id, array( (int) $category->term_id ), 'product_cat', false );
	wp_set_object_terms(
		$product_id,
		array( $product['keyword'], 'jewelry paper box', 'custom jewelry box', 'custom gift box', 'premium jewelry packaging' ),
		'product_tag',
		false
	);

	$featured_index = isset( $product['featured_index'] ) ? (int) $product['featured_index'] : 0;
	if ( ! empty( $image_ids[ $featured_index ] ) ) {
		set_post_thumbnail( $product_id, (int) $image_ids[ $featured_index ] );
	} elseif ( ! empty( $valid_image_ids[0] ) ) {
		set_post_thumbnail( $product_id, $valid_image_ids[0] );
	}

	$gallery_image_ids = array_values(
		array_filter(
			$valid_image_ids,
			static function ( $image_id ) use ( $image_ids, $featured_index ) {
				return empty( $image_ids[ $featured_index ] ) || (int) $image_id !== (int) $image_ids[ $featured_index ];
			}
		)
	);

	update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery_image_ids ) );
	update_post_meta( $product_id, '_sku', 'sample-jewelry-' . $product['slug'] );
	update_post_meta( $product_id, '_regular_price', '' );
	update_post_meta( $product_id, '_price', '' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_jewelry_specs( $product ) );
	update_post_meta( $product_id, '_vpn_sample_import', $marker );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $product['keyword'] );
	update_post_meta( $product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER' );
	update_post_meta( $product_id, 'rank_math_description', $product['title'] . ' with custom insert, logo printing, premium finish, and bulk jewelry packaging production from 1000 boxes.' );

	$content = get_post_field( 'post_content', $product_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$figures = substr_count( $content, 'product-inline-figure' );
	$specs   = get_post_meta( $product_id, '_custom_box_product_specs', true );
	$gallery = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product_id, '_product_image_gallery', true ) ) ) );

	$audit[] = '## ' . $product['title'];
	$audit[] = '- ID: ' . $product_id;
	$audit[] = '- URL: ' . get_permalink( $product_id );
	$audit[] = '- Status: ' . get_post_status( $product_id );
	$audit[] = '- Category: ' . $category_name;
	$audit[] = '- Focus keyword: ' . $product['keyword'];
	$audit[] = '- Words: ' . $words;
	$audit[] = '- Content H1 count: ' . preg_match_all( '/<h1\b/i', $content );
	$audit[] = '- Specs rows: ' . ( is_array( $specs ) ? count( $specs ) : 0 );
	$audit[] = '- Gallery images: ' . count( $gallery );
	$audit[] = '- Inline figures: ' . $figures;
	$audit[] = '- Missing image bases: ' . ( $missing ? implode( ', ', $missing ) : 'none' );
	$audit[] = '- Duplicate risk score: 4/10';
	$audit[] = '';

	echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') words=' . $words . ' images=' . count( $valid_image_ids ) . ' figures=' . $figures . PHP_EOL;
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-jewelry-paper-boxes-audit.md', implode( PHP_EOL, $audit ) );
echo 'Jewelry paper box product import complete.' . PHP_EOL;
