<?php
/**
 * Idempotent importer for the five Halloween box products.
 *
 * Source JPGs are bundled under the active theme so a Git pull followed by
 * Product Sample Deploy can reproduce products, media, SEO fields, specs and
 * FAQ markup without a manual media upload.
 */

if ( ! defined( 'ABSPATH' ) ) {
	$project_root = 'product-sample-deploy-tools' === basename( __DIR__ )
		? dirname( __DIR__, 5 )
		: dirname( __DIR__ );
	$wp_load = $project_root . '/wp-load.php';
	if ( file_exists( $wp_load ) ) {
		require_once $wp_load;
	}
}

function vpn_halloween_box_202609_link( string $path, string $label ): string {
	$url = 0 === strpos( $path, 'http' ) ? $path : home_url( $path );

	return '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
}

function vpn_halloween_box_202609_figure( int $attachment_id, string $alt, string $caption, int $slot ): string {
	$image = wp_get_attachment_image(
		$attachment_id,
		'large',
		false,
		array(
			'alt'      => $alt,
			'loading'  => 'lazy',
			'decoding' => 'async',
			'class'    => 'product-inline-image',
		)
	);

	return '<!-- stable-product-image:slot_' . $slot . ' -->'
		. '<figure class="product-inline-figure product-inline-figure-slot-' . $slot . '">'
		. $image
		. '<figcaption>' . esc_html( $caption ) . '</figcaption>'
		. '</figure>';
}

function vpn_halloween_box_202609_specs( array $product ): array {
	return array(
		array( 'label' => 'Model', 'value' => $product['model'] ),
		array( 'label' => 'Product type', 'value' => $product['shape'] ),
		array( 'label' => 'Category', 'value' => 'Halloween Packaging; seasonal gift and treat packaging' ),
		array( 'label' => 'Main structure', 'value' => $product['structure'] ),
		array( 'label' => 'Closure', 'value' => $product['closure'] ),
		array( 'label' => 'Material direction', 'value' => $product['material'] ),
		array( 'label' => 'Board grade', 'value' => 'To be confirmed from packed product weight, dimensions and handling route' ),
		array( 'label' => 'Interior', 'value' => $product['liner'] ),
		array( 'label' => 'Insert', 'value' => 'Optional paperboard, molded-pulp or foam insert; design confirmed by product fit' ),
		array( 'label' => 'Printing', 'value' => $product['print'] ),
		array( 'label' => 'Colors', 'value' => $product['colors'] ),
		array( 'label' => 'Finish', 'value' => $product['finish'] ),
		array( 'label' => 'Edge and wrap', 'value' => 'Wrapped edges, corner treatment and tolerance reviewed against approved sample' ),
		array( 'label' => 'Accessories', 'value' => $product['accessories'] ),
		array( 'label' => 'Use', 'value' => $product['industrial'] ),
		array( 'label' => 'Origin', 'value' => 'Vietnam' ),
		array( 'label' => 'Production location', 'value' => 'Ho Chi Minh City, Vietnam' ),
		array( 'label' => 'Quality checks', 'value' => $product['quality'] ),
		array( 'label' => 'MOQ', 'value' => 'Available on request' ),
		array( 'label' => 'Price', 'value' => 'Request a quote' ),
		array( 'label' => 'Lead time', 'value' => 'Confirmed after artwork, sample, materials, quantity and destination are approved' ),
	);
}

function vpn_halloween_box_202609_short_description( array $product ): string {
	return '<p>'
		. '<strong>' . esc_html( $product['title'] ) . '</strong> is a made-to-order Halloween packaging option for '
		. esc_html( $product['buyer'] ) . '. Its ' . esc_html( $product['structure'] ) . ' supports '
		. esc_html( $product['contents'] ) . ', while the visual direction uses ' . esc_html( $product['visual'] ) . '. '
		. 'VPN can review dimensions, board, wrap paper, printing, finishing, inserts and shipping protection from your packed-product requirements. '
		. 'MOQ, board grade, food-contact suitability, sampling schedule and price are project-specific and available on request. '
		. 'Send the product size, weight, quantity, artwork direction, destination and target delivery date for a practical quotation.'
		. '</p>';
}

function vpn_halloween_box_202609_content( array $product, array $attachment_ids ): string {
	$category_link = vpn_halloween_box_202609_link( '/products/halloween-packaging/', 'Halloween Packaging category' );
	$rigid_link    = vpn_halloween_box_202609_link( '/products/rigid-boxes/', 'custom rigid box range' );
	$dieline_link  = vpn_halloween_box_202609_link( '/what-is-a-dieline-in-packaging/', 'packaging dieline guide' );
	$gift_link     = vpn_halloween_box_202609_link( '/products/gift-paper-boxes/', 'gift paper box range' );
	$quote_link    = vpn_halloween_box_202609_link( '/contact/#quote', 'request a Halloween box quote' );
	$title         = esc_html( $product['title'] );
	$structure     = esc_html( $product['structure'] );
	$visual        = esc_html( $product['visual'] );
	$buyer         = esc_html( $product['buyer'] );
	$contents      = esc_html( $product['contents'] );
	$material      = esc_html( $product['material'] );
	$closure       = esc_html( $product['closure'] );
	$fit           = esc_html( $product['fit'] );
	$print         = esc_html( $product['print'] );
	$finish        = esc_html( $product['finish'] );
	$operations    = esc_html( $product['operations'] );
	$quality       = esc_html( $product['quality'] );
	$feature       = esc_html( $product['feature'] );
	$shape         = esc_html( $product['shape'] );
	$accessories   = esc_html( $product['accessories'] );
	$liner         = esc_html( $product['liner'] );
	$colors        = esc_html( $product['colors'] );

	$sections = array(
		array(
			'title' => 'What this Halloween box is',
			'paragraphs' => array(
				'<strong>Quick answer:</strong> ' . $title . ' is a custom seasonal packaging structure for ' . $contents . '. The defining direction is ' . $visual . ', presented through ' . $structure . '. It gives a Halloween campaign a more deliberate unboxing moment than a generic carton while keeping the manufacturing discussion grounded in packed-product size, board performance and shipping conditions.',
				'Brands choose this format when the package itself needs to communicate a seasonal story at retail, in an event kit or in a delivered gift. The artwork can remain close to the supplied concept or be adapted for a brand system. Review the complete ' . $category_link . ' alongside the ' . $rigid_link . ' when comparing seasonal structures, opening styles and budget levels.',
			),
		),
		array(
			'title' => 'Who should consider this structure',
			'paragraphs' => array(
				'The natural buyer profile includes ' . $buyer . '. These teams usually need a visible Halloween identity, a repeatable opening experience and a specification that can be shared with procurement, artwork and packing operators. A custom box is most useful when the packed item, quantity and route are known early enough to validate the structure before production.',
				'The format can support ' . $operations . '. For a retail shelf, check footprint, stacking and handling. For a gifting program, check presentation, inserts and opening sequence. For ecommerce, check corner protection, outer shipping packaging and the risk of abrasion during delivery. The product should be evaluated as a complete pack, not only as a decorative lid.',
			),
		),
		array(
			'title' => 'Structure, shape and opening experience',
			'paragraphs' => array(
				'The planned product type is ' . $shape . '. The structure is ' . $structure . ', which means the working sample should confirm panel geometry, wall rigidity, lid registration, opening force and the way the packed product sits inside. For the gable model, scores, locking tabs, handle clearance and base behavior require particular attention because the box is intended to be carried.',
				'The opening style is ' . $closure . '. That description is a production direction, not a fixed promise of one universal tolerance. Board caliper, wrap material, adhesive, humidity and the packed load all affect the final fit. VPN can review a dieline, blank, prototype or approved sample before finalizing tooling and production tolerances. The ' . $dieline_link . ' explains the cut, crease, bleed and safe-area decisions that should be resolved before artwork sign-off.',
			),
		),
		array(
			'title' => 'What can the box hold',
			'paragraphs' => array(
				'The intended contents are ' . $contents . '. The correct internal dimensions depend on the actual packed item, protective wrap, insert, headspace and presentation goal. A useful request includes the product length, width, height, weight, quantity per box, orientation and any fragile points. These inputs allow the factory to discuss fit without guessing from an image alone.',
				'If the item moves inside the box, an insert can manage position and improve the unboxing sequence. If the contents are food, cosmetics, candles or liquids, the inner material and barrier route must be reviewed separately. Outer seasonal artwork does not automatically make the pack suitable for direct contact. Confirm the final application, destination rules and packing process before approving the production sample.',
			),
		),
		array(
			'title' => 'Paperboard and material direction',
			'paragraphs' => array(
				'The material direction is ' . $material . '. The board grade should be selected from the packed load, dimensions, stacking expectation, lid behavior and delivery route. A thicker board can improve rigidity but may change folds, wrap returns and cost. A lighter build can reduce material use but may not provide the desired edge feel or load performance.',
				'For a responsible specification, compare virgin fiber, recycled content, cover paper, adhesive and protective finish as a complete system. If the box will be exposed to moisture, oil, condensation or repeated handling, discuss the relevant barrier and rub requirements. VPN can present feasible board and cover-paper options after receiving the product brief, target quantity and market destination.',
			),
		),
		array(
			'title' => 'Printing and Halloween artwork control',
			'paragraphs' => array(
				'The supplied artwork direction is ' . $print . '. Halloween designs often contain deep blacks, saturated orange, fine linework, metallic accents and large areas of dark coverage. Those elements should be checked through a color proof or physical sample because screen appearance cannot confirm the result on a textured wrap, dark stock or laminated surface.',
				'Artwork preparation should include the dieline, bleed, quiet zones, panel orientation, barcode or legal copy and any inside print. For an octagonal box, the corner transitions deserve a separate review so the moon, manor, eclipse or linework does not drift across wrapped edges. For a gable box, confirm the handle opening and score lines before the artwork is released to production.',
			),
		),
		array(
			'title' => 'Finishes, tactile feel and durability',
			'paragraphs' => array(
				'Potential surface direction includes ' . $finish . '. The right finish depends on whether the priority is a soft-touch feel, dark color depth, metallic contrast, scuff resistance, recyclability direction or cost control. Foil and embossing can be visually effective, but the artwork, paper grain, edge proximity and production tolerance must be reviewed together.',
				'Finish approval should cover rub resistance, corner wear, lid friction, adhesive visibility and the way the box will be packed into an outer carton. A premium sample should be handled, opened repeatedly and packed with the real product before bulk approval. If the box will travel through parcel networks, protective shipping packaging may be more important than adding another decorative effect.',
			),
		),
		array(
			'title' => 'Interior, insert and presentation options',
			'paragraphs' => array(
				'The interior direction is ' . $liner . '. The accessory plan can include ' . $accessories . '. Inserts can hold a candle, bottle, confectionery assortment, accessory or gift set in a defined position, but the insert material must be selected for fit, friction, protection and packing speed. A paperboard insert is often a practical starting point when flat shipping and material efficiency matter.',
				'An effective seasonal pack gives the customer a clear opening sequence: identify the outer artwork, remove the lid or release the gable top, see the product, and access any card or accessory without damaging the box. Share product dimensions and packing photos so the insert proposal reflects real operations. The ' . $gift_link . ' offers related structures for brands comparing premium gift presentation.',
			),
		),
		array(
			'title' => 'Applications and sales channels',
			'paragraphs' => array(
				'This Halloween box is suited to ' . $fit . '. Common channels include ' . $operations . '. The same artwork can require different construction choices by channel: a boutique may prioritize tactile wrap and display height, while ecommerce may prioritize compression strength, corner protection and a compact outer shipper.',
				'For events and seasonal promotions, confirm delivery windows, quantity by artwork, replenishment plan and storage conditions. For retail, provide carton dimensions, barcode placement and case-pack information early. For gifts or confectionery, confirm whether the inner pack is sealed, lined or otherwise separated from the decorative outer box. Those details help the quote stay useful after the first sample.',
			),
		),
		array(
			'title' => 'Quality checkpoints before production',
			'paragraphs' => array(
				'Key checks for this product include ' . $quality . '. The approval sample should be inspected under normal retail lighting and handled with the intended packed product. Check corner symmetry, wrap returns, artwork position, opening force, adhesive cleanliness, surface marks and the way any insert supports the contents.',
				'Quality control is strongest when acceptance points are written before mass production: approved color reference, board and wrap, dimensions, tolerance, finish, accessory list, packing method and carton quantity. If the product is sensitive to moisture, grease or temperature, define the test or handling requirement rather than relying on a general material label. VPN can convert the brief into a production checklist for sampling and bulk inspection.',
			),
		),
		array(
			'title' => 'Customization and proofing workflow',
			'paragraphs' => array(
				'The main customization opportunity is ' . $feature . '. VPN can review size, board, wrap paper, artwork, color targets, foil or embossing, insert geometry and outer shipping protection. The production path normally moves from brief to dieline, artwork proof, structural sample, revised approval and bulk manufacturing. The exact number of revisions depends on the complexity of the project.',
				'Before approving a sample, confirm the product dimensions, packed weight, artwork version, logo position, finish, handle or lid behavior, insert and destination. Keep one approved reference sample and one signed artwork file for later inspection. This makes it easier to identify whether a variation comes from material, color, finishing, assembly or transport rather than treating every issue as a printing problem.',
			),
		),
		array(
			'title' => 'MOQ, price and lead-time planning',
			'paragraphs' => array(
				'MOQ and pricing for ' . $title . ' are not fixed catalog values. They depend on dimensions, board, wrap, quantity, number of artworks, tooling, inserts, finishing, sampling and delivery destination. A small seasonal order may need a different production route from a repeat retail program. Request a written quote so these assumptions are visible.',
				'Lead time should be confirmed after the artwork, sample, materials, quantity and destination are approved. A useful RFQ includes product dimensions and weight, contents, target quantity, packaging structure, artwork status, finish, insert requirements, destination and required date. You can ' . $quote_link . ' with those details so the response addresses the real production brief rather than a generic box price.',
			),
		),
	);

	$html = '<p>' . $sections[0]['paragraphs'][0] . '</p><p>' . $sections[0]['paragraphs'][1] . '</p>';

	foreach ( $sections as $index => $section ) {
		if ( 0 === $index ) {
			continue;
		}

		$html .= '<h2>' . esc_html( $section['title'] ) . '</h2>';
		foreach ( $section['paragraphs'] as $paragraph ) {
			$html .= '<p>' . $paragraph . '</p>';
		}

		if ( in_array( $index, array( 2, 4, 6, 8 ), true ) ) {
			$slot = (int) ( $index / 2 );
			$html .= vpn_halloween_box_202609_figure(
				(int) $attachment_ids[ $slot - 1 ],
				$product['captions'][ $slot - 1 ],
				$product['captions'][ $slot - 1 ],
				$slot
			);
		}
	}

	$html .= '<h2>Specification summary</h2><p>Use the specification table below as a starting point. Board grade, dimensions, tolerances, food-contact route, insert material and finish should be confirmed from the approved sample and written quotation.</p>';

	return $html;
}

function vpn_halloween_box_202609_faq_html( array $product ): string {
	$faq = array(
		array( 'Can this Halloween box be customized with our brand artwork?', 'Yes. Size, structure, board, cover paper, artwork, color, finish, insert and outer protection can be reviewed. Final feasibility depends on the packed product, approved dieline and production sample.' ),
		array( 'What products can fit in this Halloween packaging format?', 'The intended contents are ' . $product['contents'] . '. Confirm dimensions, weight, orientation, protective wrap and any insert before the internal size is finalized.' ),
		array( 'What paperboard or material should be specified?', 'The starting direction is ' . $product['material'] . '. VPN can compare board and cover-paper options against rigidity, print, finish, load, shipping and destination requirements.' ),
		array( 'Can the artwork, colors and finish be changed?', 'Yes. The artwork can be adapted for a brand system and the finish can be evaluated from matte, gloss, foil, embossing or protective options. Approve a physical sample for color and tactile decisions.' ),
		array( 'Can an insert or protective component be added?', 'Yes. ' . $product['accessories'] . ' can be considered after the product dimensions, packing process and shipping route are shared.' ),
		array( 'What are the MOQ, price and lead time?', 'MOQ, price and lead time are project-specific. Send quantity, dimensions, artwork, materials, finish, insert needs, destination and required date for a written quotation.' ),
	);

	$html = '<section class="product-faq halloween-box-faq" itemscope itemtype="https://schema.org/FAQPage"><h2>Frequently asked questions</h2>';
	foreach ( $faq as $item ) {
		$html .= '<details itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">'
			. '<summary itemprop="name">' . esc_html( $item[0] ) . '</summary>'
			. '<div itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer"><p itemprop="text">' . esc_html( $item[1] ) . '</p></div>'
			. '</details>';
	}

	return $html . '</section>';
}

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'vpn_halloween_box_202609_product_definitions' ) ) {
	define( 'VPN_HALLOWEEN_BOX_202609_MARKER', 'product-samples-halloween-boxes-202609' );

	function vpn_halloween_box_202609_product_definitions(): array {
		static $definitions = null;

		if ( null !== $definitions ) {
			return $definitions;
		}

		$definitions = array(
			array(
				'title'           => 'Custom Luxury Halloween Eclipse Octagonal Rigid Gift Box',
				'slug'            => 'custom-luxury-halloween-eclipse-octagonal-rigid-gift-box',
				'keyword'         => 'custom Halloween octagonal rigid gift box',
				'seo_title'       => 'Custom Halloween Octagonal Rigid Box | VPN Packaging',
				'seo_description' => 'Custom Halloween octagonal rigid gift box with eclipse artwork, lift-off lid, copper foil and made-to-size production for premium seasonal gifts.',
				'model'           => 'VPN-HALLOWEEN-ECLIPSE-OCTAGONAL-BOX',
				'folder'          => '01-luxury-halloween-eclipse-octagonal-rigid-box',
				'buyer'           => 'premium gift brands, candle makers, fragrance retailers, event merchandise teams and Halloween campaign owners',
				'contents'        => 'candles, fragrance sets, small bottles, confectionery, accessories and curated seasonal gifts',
				'visual'          => 'a charcoal-black octagonal lid with copper eclipse rings and a restrained dark premium presentation',
				'structure'       => 'a two-piece octagonal rigid box with a fitted lift-off lid, wrapped walls and a structured base',
				'closure'         => 'fitted lift-off lid with a controlled friction fit',
				'material'        => 'rigid paperboard wrapped with approved printed paper, specialty paper or another selected cover stock',
				'fit'             => 'premium seasonal gifting where geometry and restrained artwork carry the Halloween theme',
				'print'           => 'dark-base artwork with copper foil or approved metallic accents, subject to proof approval',
				'finish'          => 'matte wrap, selective gloss, copper foil, blind embossing or another evaluated premium finish',
				'operations'      => 'premium retail, candle and fragrance gifting, launch events, subscription kits and ecommerce fulfillment',
				'quality'         => 'octagonal corner alignment, lid fit, wrap tension, copper registration, edge cleanliness and base squareness',
				'feature'         => 'Eclipse-inspired copper artwork, octagonal rigid construction, fitted lift-off lid and premium dark wrap',
				'industrial'      => 'Premium gifts, candles, fragrance, accessories, boutiques, events and Halloween campaign packaging',
				'shape'           => 'Octagonal rigid gift box with lift-off lid / Customized',
				'accessories'     => 'Optional paperboard insert, ribbon pull, belly band, card or protective outer shipper',
				'liner'           => 'Paper-wrapped interior / Insert, lining or barrier evaluated by packed product',
				'colors'          => 'Charcoal, black and copper / CMYK, Pantone or foil specification customized',
				'categories'      => array( 'halloween-packaging', 'rigid-boxes', 'gift-paper-boxes' ),
				'tags'            => array( 'halloween boxes', 'octagonal rigid boxes', 'luxury gift boxes', 'copper foil packaging', 'seasonal packaging' ),
				'images'          => array(
					'01-luxury-halloween-octagonal-rigid-gift-box.jpg',
					'02-octagonal-rigid-box-lift-off-lid-open.jpg',
					'03-copper-hot-foil-blind-emboss-finish-detail.jpg',
					'04-octagonal-rigid-gift-box-rear-side-view.jpg',
					'05-halloween-octagonal-rigid-box-top-view.jpg',
					'06-halloween-octagonal-rigid-box-feature-callouts.jpg',
				),
				'captions'         => array(
					'Front view of a custom luxury Halloween eclipse octagonal rigid gift box with charcoal wrap and copper artwork.',
					'Open view showing the fitted lift-off lid and structured interior of the Halloween octagonal rigid box.',
					'Close-up of the copper foil, blind emboss and premium printed finish on the eclipse Halloween gift box.',
					'Rear and wrapped-edge view showing octagonal corner alignment and rigid box construction.',
					'Top view showing the eclipse artwork, lid geometry and balanced octagonal presentation.',
					'Feature callouts for the custom Halloween eclipse octagonal rigid gift box.',
				),
			),
			array(
				'title'           => 'Custom Midnight Manor Halloween Octagonal Rigid Box',
				'slug'            => 'custom-midnight-manor-halloween-octagonal-rigid-box',
				'keyword'         => 'custom midnight manor Halloween box',
				'seo_title'       => 'Custom Midnight Manor Halloween Box | VPN Packaging',
				'seo_description' => 'Custom Midnight Manor Halloween octagonal rigid box with haunted house artwork, lift-off lid and premium printed wrap for seasonal gifts.',
				'model'           => 'VPN-HALLOWEEN-MIDNIGHT-MANOR-BOX',
				'folder'          => '02-halloween-midnight-manor-octagonal-rigid-box',
				'buyer'           => 'theme parks, escape rooms, attraction stores, event merchandisers and specialty gift retailers',
				'contents'        => 'souvenirs, candles, apparel accessories, boxed sweets, small bottles and themed merchandise',
				'visual'          => 'a moonlit haunted manor scene with bats, bare trees, glowing windows and an orange copper accent line',
				'structure'       => 'a two-piece octagonal rigid gift box with a fitted lift-off lid and wrapped side walls',
				'closure'         => 'fitted lift-off lid with an easy-to-review friction fit',
				'material'        => 'rigid paperboard with printed paper wrap or approved specialty cover stock',
				'fit'             => 'story-led Halloween merchandise and gift programs where the package extends the event experience',
				'print'           => 'full-color haunted manor artwork with dark charcoal, orange, black and atmospheric gray tones',
				'finish'          => 'matte lamination, selective gloss, foil, embossing or other finish options evaluated against the artwork',
				'operations'      => 'attraction retail, event counters, themed gift shops, campaign kits and seasonal ecommerce orders',
				'quality'         => 'fine illustration registration, dark-area consistency, lid fit, edge wrap, corner geometry and scuff control',
				'feature'         => 'Moonlit haunted manor artwork, octagonal rigid format, fitted lift-off lid and atmospheric seasonal print',
				'industrial'      => 'Theme parks, attraction merchandise, gifts, events, confectionery and Halloween retail programs',
				'shape'           => 'Octagonal rigid gift box with lift-off lid / Customized',
				'accessories'     => 'Optional molded or paperboard insert, card, ribbon, belly band or protective shipper',
				'liner'           => 'Printed or natural paper interior / Insert and barrier options reviewed by application',
				'colors'          => 'Charcoal, black, orange and atmospheric gray / CMYK or Pantone customized',
				'categories'      => array( 'halloween-packaging', 'rigid-boxes', 'gift-paper-boxes' ),
				'tags'            => array( 'halloween boxes', 'haunted house packaging', 'octagonal rigid boxes', 'event packaging', 'seasonal gifts' ),
				'images'          => array(
					'01-luxury-halloween-midnight-manor-octagonal-rigid-box.jpg',
					'02-halloween-octagonal-rigid-box-lift-off-lid-open.jpg',
					'03-halloween-packaging-foil-emboss-print-detail.jpg',
					'04-octagonal-rigid-gift-box-rear-wrapped-edge.jpg',
					'05-midnight-manor-halloween-box-top-view.jpg',
					'06-halloween-octagonal-rigid-box-print-finish-callouts.jpg',
				),
				'captions'         => array(
					'Front view of the custom Midnight Manor Halloween octagonal rigid box with haunted house, moon and bat artwork.',
					'Open view showing the lift-off lid, rigid walls and usable interior of the Midnight Manor Halloween box.',
					'Foil, emboss and printed-detail close-up on the custom haunted manor seasonal gift box.',
					'Rear wrapped-edge view showing the octagonal form, side coverage and rigid construction.',
					'Top view of the Midnight Manor Halloween box showing lid alignment and atmospheric artwork.',
					'Feature callouts for the custom Midnight Manor Halloween octagonal rigid box.',
				),
			),
		);

		$definitions[] = array(
			'title'           => 'Custom Orange Halloween Octagonal Rigid Gift Box',
			'slug'            => 'custom-orange-halloween-octagonal-rigid-gift-box',
			'keyword'         => 'custom orange Halloween gift box',
			'seo_title'       => 'Custom Orange Halloween Gift Box | VPN Packaging',
			'seo_description' => 'Custom orange Halloween octagonal rigid gift box with haunted house print, lift-off lid and made-to-size production for candy, gifts and events.',
			'model'           => 'VPN-HALLOWEEN-ORANGE-OCTAGONAL-BOX',
			'folder'          => '03-orange-halloween-octagonal-rigid-box',
			'buyer'           => 'candy brands, Halloween events, family attractions, retail promotions and seasonal gift programs',
			'contents'        => 'candy assortments, small toys, candles, party favors, boxed treats and lightweight gift sets',
			'visual'          => 'a vivid orange octagonal box with a black haunted house silhouette, pumpkins, bats and pale moon',
			'structure'       => 'a two-piece octagonal rigid box with a fitted lift-off lid, wrapped edges and stable base',
			'closure'         => 'fitted lift-off lid selected for repeatable opening and closing',
			'material'        => 'rigid paperboard with printed paper wrap or another approved cover material matched to the project',
			'fit'             => 'high-visibility Halloween gifting and event programs that need a strong orange seasonal signal',
			'print'           => 'full-color orange, black, pale cream and atmospheric purple artwork with large front-panel contrast',
			'finish'          => 'matte, gloss, soft-touch, foil or selective coating selected after artwork and handling review',
			'operations'      => 'candy distribution, event booths, attraction retail, gift counters and seasonal online fulfillment',
			'quality'         => 'orange color consistency, silhouette registration, lid fit, wrap corners, edge cleanliness and base stability',
			'feature'         => 'Vivid orange Halloween artwork, haunted house scene, octagonal rigid form and fitted lift-off lid',
			'industrial'      => 'Candy, gifts, attractions, events, party favors, retail promotions and Halloween ecommerce campaigns',
			'shape'           => 'Octagonal rigid gift box with lift-off lid / Customized',
			'accessories'     => 'Optional candy insert, paperboard divider, card, belly band or protective shipping carton',
			'liner'           => 'Paper-wrapped interior / Food-contact or barrier requirements reviewed separately for the packed product',
			'colors'          => 'Orange, black, pale cream and atmospheric purple / CMYK or Pantone customized',
			'categories'      => array( 'halloween-packaging', 'rigid-boxes', 'gift-paper-boxes' ),
			'tags'            => array( 'halloween boxes', 'orange gift boxes', 'octagonal rigid boxes', 'candy packaging', 'event packaging' ),
			'images'          => array(
				'01-orange-halloween-octagonal-rigid-gift-box.jpg',
				'02-orange-halloween-rigid-box-lift-off-lid-open.jpg',
				'03-orange-halloween-cmyk-offset-print-detail.jpg',
				'04-orange-octagonal-rigid-box-rear-wrapped-edge.jpg',
				'05-orange-halloween-rigid-box-top-view.jpg',
				'06-orange-halloween-rigid-box-feature-callouts.jpg',
			),
			'captions'         => array(
				'Front view of the custom orange Halloween octagonal rigid gift box with haunted house, bats and pumpkins.',
				'Open view showing the fitted lift-off lid and interior of the orange Halloween octagonal gift box.',
				'Close-up of CMYK offset print detail on the orange haunted house rigid gift box.',
				'Rear wrapped-edge view showing the orange octagonal box structure and clean corner transitions.',
				'Top view showing the pale moon artwork, lid geometry and vivid orange seasonal presentation.',
				'Feature callouts for the custom orange Halloween octagonal rigid gift box.',
			),
		);

		$definitions[] = array(
			'title'           => 'Custom Halloween Offset Printed Octagonal Rigid Box',
			'slug'            => 'custom-halloween-offset-printed-octagonal-rigid-box',
			'keyword'         => 'custom Halloween offset printed rigid box',
			'seo_title'       => 'Custom Halloween Offset Printed Box | VPN Packaging',
			'seo_description' => 'Custom Halloween offset printed octagonal rigid box with haunted manor artwork, matte lamination and lift-off lid for seasonal gifts and retail.',
			'model'           => 'VPN-HALLOWEEN-OFFSET-PRINTED-OCTAGONAL-BOX',
			'folder'          => '04-halloween-offset-printed-octagonal-rigid-box',
			'buyer'           => 'retail brands, event merchandise teams, themed attractions, gift shops and Halloween campaign managers',
			'contents'        => 'gift sets, candles, confectionery, accessories, souvenirs and compact seasonal merchandise',
			'visual'          => 'a charcoal-purple octagonal lid with orange moon, haunted manor, pumpkins and bare-tree artwork',
			'structure'       => 'a two-piece octagonal rigid gift box with wrapped side walls, fitted lift-off lid and stable base',
			'closure'         => 'fitted lift-off lid with friction fit reviewed against the final board build',
			'material'        => 'rigid paperboard wrapped with offset-printed paper and a finish selected for the handling route',
			'fit'             => 'repeatable retail and event gifting where detailed offset print needs a structured premium format',
			'print'           => 'CMYK offset print with dark atmospheric tones, orange contrast and detailed Halloween illustration',
			'finish'          => 'matte lamination, selective gloss, foil, embossing or protective coating evaluated by artwork and use',
			'operations'      => 'retail counters, event merchandise, attraction shops, seasonal kits and ecommerce gift fulfillment',
			'quality'         => 'CMYK density, dark-area consistency, lamination clarity, lid fit, wrapped edges and octagonal symmetry',
			'feature'         => 'Detailed offset-printed haunted manor artwork, matte laminated wrap, octagonal rigid structure and lift-off lid',
			'industrial'      => 'Retail gifts, events, theme attractions, confectionery, candles and Halloween promotional packaging',
			'shape'           => 'Octagonal rigid gift box with lift-off lid / Customized',
			'accessories'     => 'Optional insert, card, ribbon, belly band, sleeve or protective outer shipper',
			'liner'           => 'Printed or natural paper interior / Insert and barrier requirements reviewed by product',
			'colors'          => 'Charcoal, purple, orange, black and gray / CMYK or Pantone customized',
			'categories'      => array( 'halloween-packaging', 'rigid-boxes', 'gift-paper-boxes' ),
			'tags'            => array( 'halloween boxes', 'offset printed boxes', 'octagonal rigid boxes', 'matte laminated packaging', 'seasonal packaging' ),
			'images'          => array(
				'01-halloween-offset-printed-octagonal-rigid-gift-box.jpg',
				'02-halloween-octagonal-rigid-box-lift-off-lid-open.jpg',
				'03-cmyk-offset-print-matte-lamination-detail.jpg',
				'04-octagonal-rigid-gift-box-rear-wrapped-edge.jpg',
				'05-halloween-offset-printed-box-top-view.jpg',
				'06-halloween-rigid-box-offset-print-feature-callouts.jpg',
			),
			'captions'         => array(
				'Front view of the custom Halloween offset printed octagonal rigid box with haunted manor and orange moon artwork.',
				'Open view showing the lift-off lid and structured interior of the offset printed Halloween rigid box.',
				'Close-up of CMYK offset print and matte lamination detail on the Halloween octagonal box.',
				'Rear wrapped-edge view showing corner alignment and continuous seasonal artwork coverage.',
				'Top view showing the haunted manor composition, lid fit and octagonal box geometry.',
				'Feature callouts for the custom Halloween offset printed octagonal rigid box.',
			),
		);

		$definitions[] = array(
			'title'           => 'Custom Halloween Gable Treat Box',
			'slug'            => 'custom-halloween-gable-treat-box',
			'keyword'         => 'custom Halloween gable treat box',
			'seo_title'       => 'Custom Halloween Gable Treat Box | VPN Packaging',
			'seo_description' => 'Custom Halloween gable treat box with die-cut carry handle, ghost and pumpkin print, fold-flat structure and made-to-size production for treats.',
			'model'           => 'VPN-HALLOWEEN-GABLE-TREAT-BOX',
			'folder'          => '05-halloween-gable-treat-box',
			'buyer'           => 'candy brands, bakeries, school events, party organizers, family attractions and Halloween retail promotions',
			'contents'        => 'wrapped candy, cookies, party favors, small toys, snack packs and lightweight event giveaways',
			'visual'          => 'an orange gable box with cheerful ghost, pumpkin, bat, candy and web artwork plus a contrasting dark handle panel',
			'structure'       => 'a folding paperboard gable box with a die-cut carry handle, scored panels and side locking construction',
			'closure'         => 'folding gable top with interlocking or tuck-style closure reviewed against the dieline',
			'material'        => 'folding carton paperboard or kraft board selected for print, score performance and packed-product weight',
			'fit'             => 'easy-to-carry Halloween treats and party favors that benefit from a visible seasonal front panel',
			'print'           => 'full-color orange, black, cream and purple graphics with repeat motifs across front, sides and handle',
			'finish'          => 'uncoated, matte, gloss or protective coating selected for scuff resistance and intended contact route',
			'operations'      => 'candy counters, school events, party handouts, attraction stores, bakery promotions and ecommerce kits',
			'quality'         => 'dieline score accuracy, handle strength, panel registration, clean folds, locking tabs and base stability',
			'feature'         => 'Fold-flat gable structure, die-cut carry handle, ghost-and-pumpkin artwork and treat-focused format',
			'industrial'      => 'Candy, bakery treats, party favors, school events, family attractions and Halloween promotions',
			'shape'           => 'Folding gable treat box with die-cut handle / Customized',
			'accessories'     => 'Optional food-safe liner, sticker seal, insert, hang tag, belly band or display tray',
			'liner'           => 'Paperboard interior / Direct-food-contact and barrier requirements reviewed separately',
			'colors'          => 'Orange, black, cream and purple / CMYK or Pantone customized',
			'categories'      => array( 'halloween-packaging', 'folding-carton-boxes', 'food-paper-boxes' ),
			'tags'            => array( 'halloween boxes', 'gable treat boxes', 'candy packaging', 'party favor boxes', 'folding cartons' ),
			'images'          => array(
				'01-custom-halloween-gable-treat-box.jpg',
				'02-halloween-gable-candy-box-open-interior.jpg',
				'03-die-cut-handle-score-line-detail.jpg',
				'04-halloween-gable-favor-box-rear-side-view.jpg',
				'05-halloween-paper-gable-box-side-angle.jpg',
				'06-halloween-gable-box-feature-callouts.jpg',
			),
			'captions'         => array(
				'Front view of the custom Halloween gable treat box with ghost, pumpkin, bat and candy artwork.',
				'Open interior view showing the gable top, carry handle and usable treat space.',
				'Close-up of the die-cut handle and score-line detail on the folding Halloween treat box.',
				'Rear and side view showing the gable construction, panel artwork and folding base.',
				'Side-angle view showing the carry profile, top handle and compact treat-box footprint.',
				'Feature callouts for the custom Halloween gable treat box.',
			),
		);

		return $definitions;
	}
}

function vpn_halloween_box_202609_attachment_id( string $relative ): int {
	global $wpdb;

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s ORDER BY post_id DESC LIMIT 1",
			$relative
		)
	);
}

function vpn_halloween_box_202609_attachment( array $product, string $filename, int $parent_id, string $alt, string $caption ): int {
	$asset_relative  = 'inc/product-sample-deploy-assets/uploads/2026/09/halloween-box-collection-5-skus/' . $product['folder'] . '/' . $filename;
	$upload_relative = '2026/09/halloween-box-collection-5-skus/' . $product['folder'] . '/' . $filename;
	$uploads         = wp_upload_dir();
	$bundle_path     = trailingslashit( get_template_directory() ) . $asset_relative;
	$upload_path     = trailingslashit( $uploads['basedir'] ) . $upload_relative;

	if ( ! empty( $uploads['error'] ) ) {
		throw new RuntimeException( 'Upload directory error: ' . $uploads['error'] );
	}
	if ( ! file_exists( $bundle_path ) ) {
		throw new RuntimeException( 'Bundled Halloween box image is missing: ' . $asset_relative );
	}
	if ( ! file_exists( $upload_path ) ) {
		if ( ! wp_mkdir_p( dirname( $upload_path ) ) || ! copy( $bundle_path, $upload_path ) ) {
			throw new RuntimeException( 'Could not copy Halloween box image: ' . $filename );
		}
	} elseif ( hash_file( 'sha256', $upload_path ) !== hash_file( 'sha256', $bundle_path ) ) {
		throw new RuntimeException( 'Refusing to overwrite a different uploads file: ' . $upload_relative );
	}

	$attachment_id = vpn_halloween_box_202609_attachment_id( $upload_relative );
	if ( ! $attachment_id ) {
		$filetype = wp_check_filetype( $filename, null );
		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'] ?: 'image/jpeg',
				'post_title'     => $product['title'] . ' - ' . $caption,
				'post_excerpt'   => $caption,
				'post_status'    => 'inherit',
				'post_parent'    => $parent_id,
			),
			$upload_path,
			$parent_id,
			true
		);

		if ( is_wp_error( $attachment_id ) ) {
			throw new RuntimeException( 'Could not create attachment: ' . $attachment_id->get_error_message() );
		}
	}

	$attachment_id = (int) $attachment_id;
	update_attached_file( $attachment_id, $upload_path );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	wp_update_post(
		array(
			'ID'           => $attachment_id,
			'post_title'   => $product['title'] . ' - ' . $caption,
			'post_excerpt' => $caption,
			'post_parent'  => $parent_id,
		)
	);

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$metadata = wp_generate_attachment_metadata( $attachment_id, $upload_path );
	if ( is_array( $metadata ) && $metadata ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	return $attachment_id;
}

function vpn_halloween_box_202609_category_id( string $slug ): int {
	$names = array(
		'halloween-packaging' => 'Halloween Packaging',
		'rigid-boxes'        => 'Rigid Boxes',
		'gift-paper-boxes'   => 'Gift Paper Boxes',
		'folding-carton-boxes' => 'Folding Carton Boxes',
		'food-paper-boxes'   => 'Food Paper Boxes',
	);
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		return (int) $term->term_id;
	}

	$parent = get_term_by( 'slug', 'custom-packaging-boxes', 'product_cat' );
	$created = wp_insert_term(
		$names[ $slug ] ?? ucwords( str_replace( '-', ' ', $slug ) ),
		'product_cat',
		array(
			'slug'   => $slug,
			'parent' => $parent && ! is_wp_error( $parent ) ? (int) $parent->term_id : 0,
		)
	);
	if ( is_wp_error( $created ) ) {
		throw new RuntimeException( 'Could not create category ' . $slug . ': ' . $created->get_error_message() );
	}

	return (int) $created['term_id'];
}

function vpn_halloween_box_202609_tag_ids( array $tags ): array {
	$ids = array();
	foreach ( $tags as $tag_name ) {
		$slug = sanitize_title( $tag_name );
		$term = get_term_by( 'slug', $slug, 'product_tag' );
		if ( ! $term || is_wp_error( $term ) ) {
			$created = wp_insert_term( $tag_name, 'product_tag', array( 'slug' => $slug ) );
			if ( is_wp_error( $created ) ) {
				throw new RuntimeException( 'Could not create product tag ' . $slug . ': ' . $created->get_error_message() );
			}
			$ids[] = (int) $created['term_id'];
		} else {
			$ids[] = (int) $term->term_id;
		}
	}

	return $ids;
}

function vpn_halloween_box_202609_find_product( array $product ): ?WP_Post {
	$found = get_page_by_path( $product['slug'], OBJECT, 'product' );
	if ( $found instanceof WP_Post ) {
		return $found;
	}

	$matches = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => '_vpn_halloween_box_202609_slug',
			'meta_value'     => $product['slug'],
		)
	);

	return ! empty( $matches[0] ) && $matches[0] instanceof WP_Post ? $matches[0] : null;
}

function vpn_halloween_box_202609_import_one( array $product ): array {
	if ( 6 !== count( $product['images'] ) || 6 !== count( $product['captions'] ) ) {
		throw new RuntimeException( 'Each Halloween box product must have six images and six captions.' );
	}

	$existing = vpn_halloween_box_202609_find_product( $product );
	$product_id = $existing ? (int) $existing->ID : (int) wp_insert_post(
		array(
			'post_type'   => 'product',
			'post_status' => 'draft',
			'post_title'  => $product['title'],
			'post_name'   => $product['slug'],
		),
		true
	);
	if ( is_wp_error( $product_id ) || ! $product_id ) {
		throw new RuntimeException( 'Could not create product ' . $product['slug'] );
	}

	$attachment_ids = array();
	foreach ( $product['images'] as $index => $filename ) {
		$attachment_ids[] = vpn_halloween_box_202609_attachment(
			$product,
			$filename,
			$product_id,
			$product['captions'][ $index ],
			$product['captions'][ $index ]
		);
	}
	if ( 6 !== count( array_unique( $attachment_ids ) ) ) {
		throw new RuntimeException( 'Halloween box image attachments are not unique for ' . $product['slug'] );
	}

	if ( function_exists( 'custom_box_sync_halloween_packaging_category' ) ) {
		custom_box_sync_halloween_packaging_category();
	}

	$category_ids = array();
	foreach ( $product['categories'] as $category_slug ) {
		$category_ids[] = vpn_halloween_box_202609_category_id( $category_slug );
	}
	$tag_ids = vpn_halloween_box_202609_tag_ids( $product['tags'] );
	$content = vpn_halloween_box_202609_content( $product, $attachment_ids );

	$updated = wp_update_post(
		array(
			'ID'           => $product_id,
			'post_type'    => 'product',
			'post_status'  => 'publish',
			'post_title'   => $product['title'],
			'post_name'    => $product['slug'],
			'post_excerpt' => vpn_halloween_box_202609_short_description( $product ),
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $updated ) ) {
		throw new RuntimeException( 'Could not save product ' . $product['slug'] . ': ' . $updated->get_error_message() );
	}

	wp_set_object_terms( $product_id, $category_ids, 'product_cat', false );
	wp_set_object_terms( $product_id, $tag_ids, 'product_tag', false );
	wp_set_object_terms( $product_id, array( 'simple' ), 'product_type', false );
	set_post_thumbnail( $product_id, $attachment_ids[0] );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $attachment_ids, 1 ) ) );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_halloween_box_202609_specs( $product ) );
	update_post_meta( $product_id, '_custom_box_product_hero_bullets', array( $product['feature'], 'Made-to-size ' . $product['shape'], 'Custom board, wrap, print, finish and insert options', 'Vietnam production; MOQ and price available on request' ) );
	update_post_meta( $product_id, '_custom_box_product_faq_html', vpn_halloween_box_202609_faq_html( $product ) );
	update_post_meta( $product_id, '_vpn_sample_import', VPN_HALLOWEEN_BOX_202609_MARKER );
	update_post_meta( $product_id, '_vpn_halloween_box_202609_slug', $product['slug'] );
	update_post_meta( $product_id, 'rank_math_primary_product_cat', $category_ids[0] );
	update_post_meta( $product_id, 'rank_math_title', $product['seo_title'] );
	update_post_meta( $product_id, 'rank_math_description', $product['seo_description'] );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $product['keyword'] );
	update_post_meta( $product_id, 'rank_math_canonical_url', get_permalink( $product_id ) );
	update_post_meta( $product_id, 'rank_math_robots', array( 'index', 'follow' ) );
	update_post_meta( $product_id, 'rank_math_facebook_title', $product['seo_title'] );
	update_post_meta( $product_id, 'rank_math_facebook_description', $product['seo_description'] );
	update_post_meta( $product_id, 'rank_math_facebook_image_id', $attachment_ids[0] );
	update_post_meta( $product_id, 'rank_math_facebook_image', wp_get_attachment_url( $attachment_ids[0] ) );
	update_post_meta( $product_id, 'rank_math_twitter_title', $product['seo_title'] );
	update_post_meta( $product_id, 'rank_math_twitter_description', $product['seo_description'] );
	update_post_meta( $product_id, 'rank_math_twitter_image_id', $attachment_ids[0] );
	update_post_meta( $product_id, 'rank_math_twitter_image', wp_get_attachment_url( $attachment_ids[0] ) );
	update_post_meta( $product_id, 'rank_math_twitter_card_type', 'summary_large_image' );

	return array(
		'id'          => $product_id,
		'title'       => $product['title'],
		'slug'        => $product['slug'],
		'url'         => get_permalink( $product_id ),
		'attachments' => $attachment_ids,
	);
}

function vpn_halloween_box_202609_run_import(): array {
	if ( ! function_exists( 'wp_insert_post' ) || ! function_exists( 'wp_upload_dir' ) ) {
		throw new RuntimeException( 'WordPress is not fully loaded.' );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}

	$results = array();
	foreach ( vpn_halloween_box_202609_product_definitions() as $product ) {
		$results[] = vpn_halloween_box_202609_import_one( $product );
	}

	return $results;
}

try {
	foreach ( vpn_halloween_box_202609_run_import() as $result ) {
		echo 'Imported: ' . $result['title'] . ' (#' . $result['id'] . ') images=' . count( $result['attachments'] ) . ' URL=' . $result['url'] . PHP_EOL;
	}
	echo 'Halloween box product import complete: 5 products.' . PHP_EOL;
} catch ( Throwable $error ) {
	fwrite( STDERR, $error->getMessage() . PHP_EOL );
	exit( 1 );
}
