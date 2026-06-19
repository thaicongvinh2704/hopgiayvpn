<?php
/**
 * Import bird nest packaging products from images already uploaded to Media Library.
 *
 * Run:
 *   php tools/import-bird-nest-packaging-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_bird_link( string $url, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_bird_file_base( string $filename ): string {
	return preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
}

function vpn_bird_find_attachment_by_base( string $filename_base ): int {
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
		if ( vpn_bird_file_base( $attached_file ) === $filename_base ) {
			return (int) $attachment_id;
		}
	}

	return 0;
}

function vpn_bird_attachment_id( string $filename, string $alt, string $title, string $caption ): int {
	$filename_base = vpn_bird_file_base( $filename );
	$attachment_id = vpn_bird_find_attachment_by_base( $filename_base );
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

function vpn_bird_clean_figure( int $attachment_id, string $alt, string $caption = '' ): string {
	$url = wp_get_attachment_image_url( $attachment_id, 'large' );
	if ( ! $url ) {
		return '';
	}

	$html  = '<figure>';
	$html .= '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" style="width:100%; height:auto;" loading="lazy" decoding="async">';
	if ( $caption ) {
		$html .= '<figcaption>' . esc_html( $caption ) . '</figcaption>';
	}
	$html .= '</figure>';

	return $html;
}

function vpn_bird_specs( array $product ): array {
	return array(
		array( 'label' => 'Feature', 'value' => $product['feature'] ),
		array( 'label' => 'Industrial Use', 'value' => 'Bird Nest, Health Food, Premium Gift, Retail Packaging' ),
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

function vpn_bird_section( string $heading, array $paragraphs ): string {
	$html = '<h2>' . esc_html( $heading ) . '</h2>';
	foreach ( $paragraphs as $paragraph ) {
		$html .= '<p>' . $paragraph . '</p>';
	}
	return $html;
}

function vpn_bird_content( array $product, array $image_ids ): string {
	$category_link = vpn_bird_link( '/product-category/bird-nest-packaging-boxes/', 'bird nest packaging boxes' );
	$material_link = vpn_bird_link( '/paper-materials-for-custom-paper-boxes/', 'paper material options for custom boxes' );
	$quote_link    = vpn_bird_link( '/contact/#quote', 'request a custom packaging quotation' );

	$html  = vpn_bird_section(
		$product['heading'],
		array(
			$product['title'] . ' is designed for bird nest brands, health food distributors, gift companies, hotel gift programs, and premium retail buyers that need packaging with a stronger sense of trust and ceremony. Bird nest products are often purchased as gifts, so the box has to protect jars or inner cartons while also communicating value, origin, care, and brand confidence before the customer opens it.',
			'This product belongs to our ' . $category_link . ' category. The structure can be customized for dried bird nest jars, bottled bird nest drinks, portioned gift sets, festival promotions, VIP customer gifts, distributor sample kits, and OEM health food projects. Instead of using a generic rigid box, the package should be planned around jar count, bottle height, insert depth, handle strength, label direction, and the expected retail or gifting channel.',
		)
	);

	if ( ! empty( $image_ids[0] ) ) {
		$html .= vpn_bird_clean_figure( $image_ids[0], $product['alt'], $product['captions'][0] );
	}

	$html .= vpn_bird_section(
		$product['structure_heading'],
		array(
			$product['structure_copy'] . ' The right construction depends on whether the buyer wants a drawer opening, a cabinet reveal, a carry handle, a matching paper bag, or a compact retail carton. Premium bird nest packaging should open smoothly, hold its shape after repeated handling, and keep each jar or accessory in the correct position during transport.',
			'During sampling, the real product should be tested inside the box. Important checks include cavity diameter, bottle or jar height, lid clearance, tray depth, drawer friction, handle pull strength, corner compression, and whether the customer can remove the product without tipping nearby pieces. These details are especially important when the order includes glass jars, ceramic jars, spoons, cards, or multi-piece gift layouts.',
		)
	);

	if ( ! empty( $image_ids[1] ) ) {
		$html .= vpn_bird_clean_figure( $image_ids[1], $product['alt'], $product['captions'][1] ?? $product['title'] );
	}

	$html .= vpn_bird_section(
		$product['insert_heading'],
		array(
			'Insert planning is the heart of bird nest gift packaging. Options include paperboard trays, EVA foam, EPE foam, molded pulp, corrugated partitions, satin lining, fabric-covered trays, and mixed-material supports. The insert should stop jars from knocking together, keep product labels facing the correct direction, and create a neat reveal when the box is opened.',
			'For multi-jar or cabinet-style packaging, every compartment needs a clear purpose. A tray can hold jars, a side pocket can hold a spoon or product card, and a drawer can separate accessories from the main product. The goal is to make packing faster for the factory while giving the end customer a premium, organized unboxing experience.',
		)
	);

	$html .= vpn_bird_section(
		$product['material_heading'],
		array(
			'Material options include ' . $product['materials'] . '. Rigid greyboard gives the box a premium hand feel, coated art paper supports accurate printing, specialty paper adds texture, and reinforced paper bags help carry heavier gift sets. Buyers can compare board and surface choices in our ' . $material_link . ' guide before confirming the sample.',
			'Bird nest packaging often uses deep blue, emerald green, gold accents, floral patterns, or clean health-product colors. The material should support those visual choices without making the package too fragile or too expensive for the target order quantity. For export orders, the outer carton and inner gift box should be considered together so the final packaging still looks clean after shipping.',
		)
	);

	if ( ! empty( $image_ids[2] ) ) {
		$html .= vpn_bird_clean_figure( $image_ids[2], $product['alt'], $product['captions'][2] ?? $product['title'] );
	}

	$html .= vpn_bird_section(
		$product['branding_heading'],
		array(
			'Customization can include brand logo placement, gold foil stamping, embossed marks, Pantone color matching, floral patterns, product story panels, QR code, barcode, nutrition or ingredient information, usage instructions, origin statement, and festival campaign artwork. The front panel should communicate premium value quickly, while the side and back panels can carry practical product information.',
			'If the brand sells several bird nest grades or gift levels, the same structure can be reused with controlled color changes, insert adjustments, or label areas. This helps distributors manage different price points while keeping a consistent packaging family. A clear artwork version list and approved dieline reduce errors when several SKUs are produced together.',
		)
	);

	$html .= vpn_bird_section(
		$product['application_heading'],
		array(
			'Typical applications include bird nest jars, bottled bird nest drinks, dried bird nest gift sets, health supplement gift boxes, Lunar New Year promotions, hotel welcome gifts, premium retail bundles, corporate gift programs, and distributor sample sets. The package can be adjusted for one jar, two jars, six jars, or a mixed set with accessories.',
			'For retail stores, the package needs strong front-facing recognition and clean shelf presence. For gifting, the box should feel substantial and ceremonial. For e-commerce, the structure needs better corner protection and insert stability. Defining the sales channel early helps balance presentation, protection, unit cost, and packing efficiency.',
		)
	);

	if ( ! empty( $image_ids[3] ) ) {
		$html .= vpn_bird_clean_figure( $image_ids[3], $product['alt'], $product['captions'][3] ?? $product['title'] );
	}

	$html .= vpn_bird_section(
		$product['quality_heading'],
		array(
			'Quality control should review board thickness, wrap alignment, foil position, print color, drawer movement, magnetic or closure performance, handle strength, insert fit, glue marks, edge finishing, and packed-product appearance. A physical sample is recommended because bird nest gift boxes rely on touch, opening action, and insert accuracy as much as printed artwork.',
			'For international B2B orders, buyers should confirm product dimensions, product weight, jar count, required insert type, artwork files, quantity, export carton plan, and target market. Clear specifications make quotation and sampling more accurate and reduce changes before mass production.',
		)
	);

	if ( ! empty( $image_ids[4] ) ) {
		$html .= vpn_bird_clean_figure( $image_ids[4], $product['alt'], $product['captions'][4] ?? $product['title'] );
	}

	$html .= '<h3>' . esc_html( $product['cta_heading'] ) . '</h3>';
	$html .= '<p>Send your product size, jar count, bottle weight, preferred structure, artwork, and target order quantity to ' . $quote_link . '. VPN Paper Box Manufacturer can recommend box construction, insert layout, paper material, printing, finishing, and export packing for your custom bird nest packaging order.</p>';

	return $html;
}

$category_slug = 'bird-nest-packaging-boxes';
$category_name = 'Bird Nest Packaging Boxes';
$marker        = 'product-samples-bird-nest-packaging';

$products = array(
	array(
		'title' => 'CUSTOM BLUE BIRD NEST DRAWER GIFT BOX',
		'slug' => 'custom-blue-bird-nest-drawer-gift-box',
		'keyword' => 'blue bird nest drawer gift box',
		'heading' => 'Blue Bird Nest Drawer Gift Box for Premium Health Food Gifts',
		'structure_heading' => 'Drawer Gift Box Structure for Blue Bird Nest Packaging',
		'insert_heading' => 'Insert Layout for Jars and Gift Accessories',
		'material_heading' => 'Blue Rigid Board and Gold Pattern Materials',
		'branding_heading' => 'Luxury Blue Branding and Gold Foil Details',
		'application_heading' => 'Blue Bird Nest Gift Box Applications',
		'quality_heading' => 'Quality Control for Drawer Bird Nest Boxes',
		'cta_heading' => 'Request a Blue Bird Nest Drawer Box Quote',
		'structure_copy' => 'This blue drawer gift box can be produced as a rigid sleeve-and-drawer structure, open display box, or premium presentation case with a fitted tray.',
		'materials' => 'rigid greyboard, coated art paper, specialty blue wrapping paper, EVA insert, paperboard tray, and gold foil surface finishing',
		'feature' => 'Blue drawer structure, gold pattern branding, jar insert, premium health gift presentation',
		'paper' => 'Rigid Greyboard / Art Paper / Specialty Paper / EVA Insert',
		'box_type' => 'Blue Bird Nest Drawer Gift Box',
		'shape' => 'Rectangle / Drawer / Customized Gift Set Shape',
		'accessories' => 'Drawer tray / EVA insert / Ribbon pull / Paper sleeve / Gift card pocket',
		'liner' => 'EVA insert / Paperboard tray / Foam insert / Satin lining',
		'colors' => 'Blue / Gold / White / CMYK / Pantone / Customized Color',
		'alt' => 'Blue bird nest drawer gift box with gold pattern and jar insert',
		'images' => array(
			'blue-bird-nest-gift-packaging-box-with-gold-pattern-front-view.webp',
			'blue-custom-bird-nest-packaging-box-with-drawer-closed-view.webp',
			'blue-luxury-bird-nest-gift-box-with-drawer-open-view.webp',
			'custom-blue-bird-nest-paper-gift-box-front-view.webp',
			'luxury-blue-bird-nest-gift-box-with-drawer-open-display.webp',
			'premium-blue-bird-nest-packaging-gift-box-open-view.webp',
		),
		'captions' => array(
			'Blue bird nest gift packaging box with gold pattern front view.',
			'Closed blue drawer bird nest packaging box for premium retail gifts.',
			'Open blue bird nest drawer gift box with organized product display.',
			'Custom blue bird nest paper gift box front view for brand presentation.',
			'Luxury blue bird nest drawer box with open display layout.',
			'Premium blue bird nest gift packaging box with inner jar arrangement.',
		),
	),
	array(
		'title' => 'CUSTOM GREEN BIRD NEST GIFT BOX WITH HANDLE',
		'slug' => 'custom-green-bird-nest-gift-box-with-handle',
		'keyword' => 'green bird nest gift box with handle',
		'heading' => 'Green Bird Nest Gift Box with Handle for Premium Carry-Out Sets',
		'structure_heading' => 'Handle Gift Box and Matching Bag Structure',
		'insert_heading' => 'Jar Support and Carry-Out Protection',
		'material_heading' => 'Green Rigid Paper and Reinforced Handle Materials',
		'branding_heading' => 'Green Health Gift Branding with Gold Logo',
		'application_heading' => 'Green Bird Nest Handle Box Applications',
		'quality_heading' => 'Quality Control for Handle and Bag Sets',
		'cta_heading' => 'Request a Green Bird Nest Handle Box Quote',
		'structure_copy' => 'This green gift packaging can be produced as a rigid box with handle, side-view gift box, or matching box-and-paper-bag set for retail and festival gifting.',
		'materials' => 'rigid board, coated green art paper, specialty paper, reinforced handle paper, rope handle, paperboard tray, and gold foil logo finishing',
		'feature' => 'Green carry handle, matching paper bag, gold logo, premium bird nest gift presentation',
		'paper' => 'Rigid Board / Art Paper / Specialty Paper / Reinforced Bag Paper',
		'box_type' => 'Green Bird Nest Gift Box with Handle',
		'shape' => 'Rectangle / Hand-Carry Gift Box / Customized Shape',
		'accessories' => 'Paper handle / Rope handle / Matching paper bag / EVA insert / Product card',
		'liner' => 'Paperboard tray / EVA insert / Foam insert / Corrugated support',
		'colors' => 'Green / Gold / Cream / CMYK / Pantone / Customized Color',
		'alt' => 'Green bird nest gift box with handle and gold logo',
		'images' => array(
			'custom-green-bird-nest-gift-box-with-handle-front-view.webp',
			'custom-green-bird-nest-packaging-box-with-gold-logo-front-view.webp',
			'green-rigid-bird-nest-paper-box-side-view-with-gold-accents.webp',
			'luxury-green-bird-nest-packaging-box-with-paper-bag-display.webp',
			'premium-green-bird-nest-gift-box-and-paper-bag-set.webp',
		),
		'captions' => array(
			'Green bird nest gift box with handle front view for premium carry-out.',
			'Custom green bird nest packaging box with gold logo front panel.',
			'Green rigid bird nest paper box side view with gold accents.',
			'Luxury green bird nest packaging box with matching paper bag display.',
			'Premium green bird nest gift box and paper bag set for retail gifting.',
		),
	),
	array(
		'title' => 'CUSTOM GREEN BIRD NEST CABINET GIFT BOX',
		'slug' => 'custom-green-bird-nest-cabinet-gift-box',
		'keyword' => 'green bird nest cabinet gift box',
		'heading' => 'Green Bird Nest Cabinet Gift Box for Luxury Jar Sets',
		'structure_heading' => 'Cabinet Door Structure for Premium Bird Nest Sets',
		'insert_heading' => 'Cabinet Compartments and Drawer Organization',
		'material_heading' => 'Rigid Cabinet Materials for Heavy Gift Sets',
		'branding_heading' => 'Premium Cabinet Branding and Front Panel Design',
		'application_heading' => 'Green Cabinet Bird Nest Box Applications',
		'quality_heading' => 'Quality Control for Cabinet Gift Packaging',
		'cta_heading' => 'Request a Green Bird Nest Cabinet Box Quote',
		'structure_copy' => 'This cabinet-style gift box can use double doors, drawers, a top handle, magnetic closure, and multi-level compartments for a stronger premium reveal.',
		'materials' => 'thick rigid greyboard, specialty green paper, coated art paper, EVA insert, foam tray, magnetic components, and reinforced handle materials',
		'feature' => 'Cabinet door opening, drawer compartments, top handle, premium green bird nest gift layout',
		'paper' => 'Rigid Greyboard / Specialty Paper / Art Paper / EVA Insert',
		'box_type' => 'Green Bird Nest Cabinet Gift Box',
		'shape' => 'Cabinet / Double Door / Drawer / Customized Gift Set Shape',
		'accessories' => 'Double doors / Drawer / Top handle / Magnetic closure / EVA tray',
		'liner' => 'EVA insert / Foam tray / Paperboard compartment / Fabric lining',
		'colors' => 'Green / Gold / Dark Green / CMYK / Pantone / Customized Color',
		'alt' => 'Green bird nest cabinet gift box with drawers and compartments',
		'images' => array(
			'realistic-premium-green-bird-nest-paper-cabinet-box-front-view.webp',
			'realistic-custom-green-bird-nest-cabinet-packaging-box-closed-view.webp',
			'realistic-green-bird-nest-gift-box-double-door-front-view.webp',
			'realistic-custom-green-bird-nest-cabinet-gift-box-with-drawers.webp',
			'realistic-luxury-green-bird-nest-cabinet-box-top-handle-view.webp',
			'realistic-luxury-green-bird-nest-cabinet-gift-box-open-display.webp',
		),
		'captions' => array(
			'Premium green bird nest paper cabinet box front view.',
			'Closed green bird nest cabinet packaging box with luxury structure.',
			'Green bird nest gift box with double-door front opening.',
			'Custom green bird nest cabinet gift box with drawers.',
			'Luxury green bird nest cabinet box with top handle view.',
			'Open green cabinet bird nest gift box display with compartments.',
		),
	),
	array(
		'title' => 'CUSTOM GREEN BIRD NEST COMPARTMENT GIFT BOX',
		'slug' => 'custom-green-bird-nest-compartment-gift-box',
		'keyword' => 'green bird nest compartment gift box',
		'heading' => 'Green Bird Nest Compartment Gift Box for Organized Jar Displays',
		'structure_heading' => 'Open Compartment Structure for Bird Nest Jar Sets',
		'insert_heading' => 'Lotus Detail and Multi-Jar Tray Planning',
		'material_heading' => 'Premium Green Paper Materials for Open Display Boxes',
		'branding_heading' => 'Logo, Side Panel, and Inner Detail Branding',
		'application_heading' => 'Compartment Bird Nest Gift Box Applications',
		'quality_heading' => 'Quality Control for Open Display Gift Boxes',
		'cta_heading' => 'Request a Green Bird Nest Compartment Box Quote',
		'structure_copy' => 'This open compartment gift box can be produced as a lid-and-base rigid box, hinged presentation box, or open display set with multiple jar cavities.',
		'materials' => 'rigid board, coated green paper, specialty wrapping paper, EVA foam, paperboard insert, satin lining, and gold foil detailing',
		'feature' => 'Open jar compartments, lotus detail, custom logo area, premium health food display',
		'paper' => 'Rigid Board / Art Paper / Specialty Paper / EVA Insert / Satin Lining',
		'box_type' => 'Green Bird Nest Compartment Gift Box',
		'shape' => 'Rectangle / Open Display / Multi-Compartment Shape',
		'accessories' => 'Jar cavities / Lotus detail insert / Product card / Ribbon / Logo panel',
		'liner' => 'EVA insert / Paper tray / Satin lining / Foam insert',
		'colors' => 'Green / Gold / White / CMYK / Pantone / Customized Color',
		'alt' => 'Green bird nest compartment gift box with open jar display',
		'images' => array(
			'luxury-green-bird-nest-gift-box-with-compartments-open-view.webp',
			'realistic-green-bird-nest-gift-box-open-set-with-jars.webp',
			'realistic-green-bird-nest-cabinet-box-inner-lotus-detail.webp',
			'realistic-green-bird-nest-gift-box-custom-logo-close-up.webp',
			'realistic-green-bird-nest-paper-gift-box-side-view.webp',
			'realistic-luxury-green-bird-nest-box-open-angle-view.webp',
		),
		'captions' => array(
			'Luxury green bird nest gift box with compartments open view.',
			'Green bird nest gift box open set with jars for premium display.',
			'Inner lotus detail for green bird nest cabinet box packaging.',
			'Custom logo close-up on green bird nest gift box.',
			'Green bird nest paper gift box side view for retail presentation.',
			'Luxury green bird nest box open angle view with organized layout.',
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

$audit = array( '# Bird Nest Packaging Product Import Audit', '' );

foreach ( $products as $product ) {
	$image_ids = array();
	foreach ( $product['images'] as $index => $filename ) {
		$image_ids[] = vpn_bird_attachment_id(
			$filename,
			$product['alt'],
			$product['captions'][ $index ] ?? $product['title'],
			$product['captions'][ $index ] ?? ''
		);
	}

	$missing = array();
	foreach ( $product['images'] as $index => $filename ) {
		if ( empty( $image_ids[ $index ] ) ) {
			$missing[] = vpn_bird_file_base( $filename );
		}
	}

	if ( $missing ) {
		echo 'Missing images for ' . $product['title'] . ': ' . implode( ', ', $missing ) . PHP_EOL;
	}

	$existing = get_page_by_path( $product['slug'], OBJECT, 'product' );
	$postarr  = array(
		'post_type'    => 'product',
		'post_status'  => $existing && 'publish' === $existing->post_status ? 'publish' : 'draft',
		'post_title'   => $product['title'],
		'post_name'    => $product['slug'],
		'post_excerpt' => $product['title'] . ' is a custom premium paper packaging solution for bird nest jars, bottled bird nest drinks, health food gift sets, and retail or corporate gifting programs. It supports custom size, rigid structure, insert layout, logo printing, gold foil, paper bag options, and bulk production from 1000 boxes.',
		'post_content' => vpn_bird_content( $product, array_values( array_filter( $image_ids ) ) ),
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
		array( $product['keyword'], 'bird nest packaging', 'custom gift box', 'custom paper box', 'custom packaging' ),
		'product_tag'
	);

	$valid_image_ids = array_values( array_filter( $image_ids ) );
	if ( ! empty( $valid_image_ids[0] ) ) {
		set_post_thumbnail( $product_id, $valid_image_ids[0] );
	}

	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $valid_image_ids, 1 ) ) );
	update_post_meta( $product_id, '_sku', 'sample-bird-nest-' . $product['slug'] );
	update_post_meta( $product_id, '_regular_price', '' );
	update_post_meta( $product_id, '_price', '' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_bird_specs( $product ) );
	update_post_meta( $product_id, '_vpn_sample_import', $marker );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $product['keyword'] );
	update_post_meta( $product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER' );
	update_post_meta( $product_id, 'rank_math_description', $product['title'] . ' for premium bird nest gift sets, customized with rigid structure, insert, logo printing, foil, and bulk production.' );

	$words   = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ) );
	$audit[] = '## ' . $product['title'];
	$audit[] = '- ID: ' . $product_id;
	$audit[] = '- URL: ' . get_permalink( $product_id );
	$audit[] = '- Status: ' . get_post_status( $product_id );
	$audit[] = '- Category: ' . $category_name;
	$audit[] = '- Focus keyword: ' . $product['keyword'];
	$audit[] = '- Words: ' . $words;
	$audit[] = '- Images: ' . count( $valid_image_ids );
	$audit[] = '- Missing image bases: ' . ( $missing ? implode( ', ', $missing ) : 'none' );
	$audit[] = '- Source files: ' . implode( ', ', $product['images'] );
	$audit[] = '';

	echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') status=' . get_post_status( $product_id ) . ' words=' . $words . ' images=' . count( $valid_image_ids ) . PHP_EOL;
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-bird-nest-packaging-audit.md', implode( PHP_EOL, $audit ) );
echo 'Bird nest packaging product import complete.' . PHP_EOL;
