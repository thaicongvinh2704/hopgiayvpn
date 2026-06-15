<?php
/**
 * Import the kraft corrugated mailer box product from images already uploaded to Media Library.
 *
 * Run:
 *   php tools/import-kraft-corrugated-mailer-product.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_mailer_file_base( string $filename ): string {
	return preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
}

function vpn_mailer_link( string $url, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_mailer_find_attachment_by_base( string $filename_base ): int {
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
		if ( vpn_mailer_file_base( $attached_file ) === $filename_base ) {
			return (int) $attachment_id;
		}
	}

	return 0;
}

function vpn_mailer_attachment_id( string $filename, string $alt, string $title, string $caption ): int {
	$attachment_id = vpn_mailer_find_attachment_by_base( vpn_mailer_file_base( $filename ) );
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

function vpn_mailer_clean_figure( int $attachment_id, string $alt, string $caption ): string {
	$url = wp_get_attachment_image_url( $attachment_id, 'large' );
	if ( ! $url ) {
		return '';
	}

	return '<figure><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" style="width:100%; height:auto;" loading="lazy" decoding="async"><figcaption>' . esc_html( $caption ) . '</figcaption></figure>';
}

function vpn_mailer_specs( array $product ): array {
	return array(
		array( 'label' => 'Feature', 'value' => 'Kraft corrugated protection, mailer locking tabs, natural paper finish, e-commerce shipping structure' ),
		array( 'label' => 'Industrial Use', 'value' => 'E-commerce, Subscription Box, Apparel, Gifts, Small Product Shipping' ),
		array( 'label' => 'Paper Type', 'value' => 'Kraft Corrugated Paper / E-flute / B-flute / Recycled Paperboard' ),
		array( 'label' => 'Box Type', 'value' => 'Corrugated Mailer Box' ),
		array( 'label' => 'Shape', 'value' => 'Rectangle / Foldable Mailer / Customized Shape' ),
		array( 'label' => 'Place of Origin', 'value' => 'Vietnam' ),
		array( 'label' => 'Model Number', 'value' => $product['title'] ),
		array( 'label' => 'Brand Name', 'value' => 'VPN' ),
		array( 'label' => 'Province', 'value' => 'Ho Chi Minh City' ),
		array( 'label' => 'Accessories', 'value' => 'Locking tab / Tear strip / Insert card / Paper divider / Sticker label' ),
		array( 'label' => 'Custom Order', 'value' => 'Accept' ),
		array( 'label' => 'Liner Type', 'value' => 'No liner / Corrugated insert / Paperboard divider / Kraft paper wrap' ),
		array( 'label' => 'Logo Printing', 'value' => 'Custom logo' ),
		array( 'label' => 'Printing Handling', 'value' => 'CMYK Printing, Pantone Printing, Black Ink Printing, White Ink Printing, Spot UV, Matte Lamination, Flexo Printing' ),
		array( 'label' => 'Color', 'value' => 'Natural Kraft / Black / White / CMYK / Pantone / Customized Color' ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, corrugated flute, printing, insert, finishing, and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $product['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement" ),
	);
}

function vpn_mailer_section( string $heading, array $paragraphs ): string {
	$html = '<h2>' . esc_html( $heading ) . '</h2>';
	foreach ( $paragraphs as $paragraph ) {
		$html .= '<p>' . $paragraph . '</p>';
	}
	return $html;
}

function vpn_mailer_content( array $product, array $image_ids ): string {
	$category_link = vpn_mailer_link( '/product-category/corrugated-mailer-boxes/', 'corrugated mailer boxes' );
	$material_link = vpn_mailer_link( '/paper-materials-for-custom-paper-boxes/', 'paper material options for custom boxes' );
	$quote_link    = vpn_mailer_link( '/contact/#quote', 'request a custom packaging quotation' );

	$html  = vpn_mailer_section(
		'Kraft Corrugated Mailer Box for E-commerce Shipping',
		array(
			$product['title'] . ' is designed for brands that need a practical shipping box with a natural kraft appearance, strong folding structure, and enough surface area for custom logo printing. It is suitable for e-commerce orders, subscription kits, apparel packing, small gift items, accessories, handmade products, and retail samples that need to arrive clean and organized without using a separate outer carton for every order.',
			'This product belongs to our ' . $category_link . ' category. The main packaging challenge is balancing protection, packing speed, freight cost, and brand presentation. A mailer box should fold quickly, lock securely, resist crushing during delivery, and still look intentional when the customer opens the package at home.',
		)
	);

	if ( ! empty( $image_ids[0] ) ) {
		$html .= vpn_mailer_clean_figure( $image_ids[0], $product['alt'], $product['captions'][0] );
	}

	$html .= vpn_mailer_section(
		'Mailer Structure and Locking Performance',
		array(
			'The kraft corrugated mailer can be produced with front tuck tabs, side locking wings, dust flaps, tear strip, return strip, or reinforced edge details depending on the sales channel. The front-left, front-right, side-angle, and top views help confirm how the panels fold together and how the box keeps its shape after packing.',
			'For online orders, the closure needs to survive courier handling without opening too easily. During sampling, buyers should check folding tolerance, tab strength, lid alignment, corner compression, internal clearance, and whether the packed product moves when the box is shaken. These tests are more useful than judging the box only from a flat dieline.',
		)
	);

	if ( ! empty( $image_ids[1] ) ) {
		$html .= vpn_mailer_clean_figure( $image_ids[1], $product['alt'], $product['captions'][1] );
	}

	$html .= vpn_mailer_section(
		'Kraft Corrugated Material Options',
		array(
			'Common material choices include natural kraft corrugated board, white kraft corrugated board, E-flute, B-flute, micro-flute board, recycled paperboard, and coated liner paper when higher print quality is needed. Buyers can compare board behavior in our ' . $material_link . ' guide before confirming final thickness.',
			'Natural kraft paper is popular for sustainable, handmade, organic, and minimalist brands because it has a warm paper texture and does not need heavy ink coverage to look finished. For heavier products, the flute type and board strength should be selected based on packed weight, shipping distance, stacking height, and whether the product needs an inner tray or divider.',
		)
	);

	$html .= vpn_mailer_section(
		'Custom Printing and Brand Details',
		array(
			'Customization can include one-color black printing, white ink, CMYK printing, Pantone logo color, inside lid message, QR code, barcode, care instructions, recycling mark, social media handle, shipping label zone, and product-size identification. The outside artwork should stay clear enough for shipping labels and handling marks.',
			'Many e-commerce brands use a simple kraft outside with a stronger inside printing moment. This keeps the package cost controlled while still creating a branded unboxing experience. If the order includes several box sizes, the artwork should be planned as a family so warehouse teams can identify sizes quickly and customers see one consistent brand language.',
		)
	);

	if ( ! empty( $image_ids[2] ) ) {
		$html .= vpn_mailer_clean_figure( $image_ids[2], $product['alt'], $product['captions'][2] );
	}

	$html .= vpn_mailer_section(
		'Product Fit, Inserts, and Shipping Protection',
		array(
			'The mailer can be paired with corrugated inserts, paperboard dividers, kraft wrap, tissue paper, molded pulp, or product cards. For cosmetics, small electronics, candles, stationery, apparel, or gift items, the inner fit should prevent movement without making packing slow. Oversized boxes increase shipping cost, while boxes that are too tight can crush product corners or create poor opening experience.',
			'Useful sampling details include product length, width, height, packed weight, fragile areas, number of items per box, preferred packing direction, and whether the box must fit a postal size standard. These details help define a structure that works for real fulfillment instead of only looking good in product photos.',
		)
	);

	$html .= vpn_mailer_section(
		'B2B Use Cases for Kraft Mailer Boxes',
		array(
			'This kraft mailer box is suitable for direct-to-consumer brands, marketplace sellers, subscription box companies, apparel suppliers, gift shops, beauty brands, sample kit programs, and small product distributors. It can support both retail unboxing and courier delivery when the board strength, size, and closure are specified correctly.',
			'Bulk production from 1000 boxes allows the buyer to balance unit cost and brand presentation. OEM and ODM buyers can request different sizes, printing versions, inserts, and shipping carton plans for the same packaging family. A consistent dieline and material specification also make repeat orders easier after the first sample is approved.',
		)
	);

	if ( ! empty( $image_ids[3] ) ) {
		$html .= vpn_mailer_clean_figure( $image_ids[3], $product['alt'], $product['captions'][3] );
	}

	$html .= vpn_mailer_section(
		'Quality Control Before Mass Production',
		array(
			'Quality control should cover board thickness, flute direction, folding accuracy, tab strength, print registration, color consistency, glue marks, die-cut edge quality, compression resistance, and packed-product appearance. For shipping boxes, drop testing or courier simulation can be useful when products are fragile or high value.',
			'For the most accurate quotation, send product size, packed weight, target quantity, shipping market, artwork, and any required postal size. VPN Paper Box Manufacturer can recommend mailer structure, corrugated material, printing method, insert option, and export packing for your custom kraft corrugated mailer box project.',
		)
	);

	$html .= vpn_mailer_section(
		'Quotation Details Buyers Should Prepare',
		array(
			'To quote this mailer box accurately, the supplier needs more than the outside box size. Buyers should prepare product dimensions, packed product weight, required internal clearance, shipping method, number of items per box, expected monthly or seasonal quantity, and whether the box needs to pass any courier or marketplace packaging requirements. Artwork format, print color count, inside printing, and label placement also affect cost and production planning.',
			'If the brand is moving from plain cartons to custom printed mailers, sampling can compare several board options before mass production. A lighter board may reduce freight and storage cost, while a stronger flute may protect better during long-distance delivery. The final choice should match the real fulfillment process, not only the lowest unit price, because damaged packages can cost more than a slightly stronger mailer structure.',
		)
	);

	$html .= '<h3>Request a Kraft Corrugated Mailer Box Quote</h3>';
	$html .= '<p>Send your product dimensions, order quantity, artwork, board preference, and packing requirements to ' . $quote_link . '. We can prepare a sample plan and production recommendation for your custom corrugated mailer packaging.</p>';

	return $html;
}

$product = array(
	'title'    => 'KRAFT CORRUGATED MAILER BOX',
	'slug'     => 'kraft-corrugated-mailer-box',
	'keyword'  => 'kraft corrugated mailer box',
	'alt'      => 'Kraft corrugated mailer box for e-commerce shipping packaging',
	'images'   => array(
		'kraft-corrugated-mailer-box-front-left-view.webp',
		'kraft-corrugated-mailer-box-front-right-view.webp',
		'kraft-corrugated-mailer-box-side-angle-view.webp',
		'kraft-corrugated-mailer-box-top-front-view.webp',
	),
	'captions' => array(
		'Kraft corrugated mailer box front-left view for e-commerce shipping.',
		'Kraft mailer box front-right view showing foldable closure structure.',
		'Side angle view of custom kraft corrugated mailer packaging.',
		'Top-front view of kraft corrugated mailer box for branded shipping.',
	),
);

$category_slug = 'corrugated-mailer-boxes';
$category_name = 'Corrugated Mailer Boxes';
$marker        = 'product-samples-kraft-corrugated-mailer';

$category = get_term_by( 'slug', $category_slug, 'product_cat' );
if ( ! $category || is_wp_error( $category ) ) {
	$created = wp_insert_term( $category_name, 'product_cat', array( 'slug' => $category_slug ) );
	if ( is_wp_error( $created ) ) {
		fwrite( STDERR, 'Missing product category and failed to create it: ' . $category_slug . PHP_EOL );
		exit( 1 );
	}
	$category = get_term( (int) $created['term_id'], 'product_cat' );
}

$image_ids = array();
foreach ( $product['images'] as $index => $filename ) {
	$image_ids[] = vpn_mailer_attachment_id(
		$filename,
		$product['alt'],
		$product['captions'][ $index ],
		$product['captions'][ $index ]
	);
}

$missing = array();
foreach ( $product['images'] as $index => $filename ) {
	if ( empty( $image_ids[ $index ] ) ) {
		$missing[] = vpn_mailer_file_base( $filename );
	}
}
if ( $missing ) {
	echo 'Missing images: ' . implode( ', ', $missing ) . PHP_EOL;
}

$valid_image_ids = array_values( array_filter( $image_ids ) );
$existing        = get_page_by_path( $product['slug'], OBJECT, 'product' );
$postarr         = array(
	'post_type'    => 'product',
	'post_status'  => $existing && 'publish' === $existing->post_status ? 'publish' : 'draft',
	'post_title'   => $product['title'],
	'post_name'    => $product['slug'],
	'post_excerpt' => 'KRAFT CORRUGATED MAILER BOX is a custom shipping packaging solution for e-commerce, subscription kits, apparel, gifts, and small product delivery. It supports custom size, kraft corrugated material, logo printing, mailer locking tabs, inserts, and bulk production from 1000 boxes.',
	'post_content' => vpn_mailer_content( $product, $valid_image_ids ),
);

if ( $existing ) {
	$postarr['ID'] = $existing->ID;
	$product_id    = wp_update_post( $postarr );
} else {
	$product_id = wp_insert_post( $postarr );
}

if ( is_wp_error( $product_id ) || ! $product_id ) {
	fwrite( STDERR, 'Failed product: ' . $product['title'] . PHP_EOL );
	exit( 1 );
}

wp_set_object_terms( $product_id, array( (int) $category->term_id ), 'product_cat', false );
wp_set_object_terms(
	$product_id,
	array( $product['keyword'], 'corrugated mailer box', 'kraft packaging', 'e-commerce packaging', 'custom paper box' ),
	'product_tag'
);

if ( ! empty( $valid_image_ids[0] ) ) {
	set_post_thumbnail( $product_id, $valid_image_ids[0] );
}
update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $valid_image_ids, 1 ) ) );
update_post_meta( $product_id, '_sku', 'sample-mailer-' . $product['slug'] );
update_post_meta( $product_id, '_regular_price', '' );
update_post_meta( $product_id, '_price', '' );
update_post_meta( $product_id, '_stock_status', 'instock' );
update_post_meta( $product_id, '_custom_box_product_specs', vpn_mailer_specs( $product ) );
update_post_meta( $product_id, '_vpn_sample_import', $marker );
update_post_meta( $product_id, 'rank_math_focus_keyword', $product['keyword'] );
update_post_meta( $product_id, 'rank_math_title', $product['title'] . ' | VPN PAPER BOX MANUFACTURER' );
update_post_meta( $product_id, 'rank_math_description', 'Kraft corrugated mailer box for e-commerce shipping, customized with size, kraft board, logo printing, inserts, locking tabs, and bulk production.' );

$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ) );
$audit = array(
	'# Kraft Corrugated Mailer Product Import Audit',
	'',
	'## ' . $product['title'],
	'- ID: ' . $product_id,
	'- URL: ' . get_permalink( $product_id ),
	'- Status: ' . get_post_status( $product_id ),
	'- Category: ' . $category_name,
	'- Focus keyword: ' . $product['keyword'],
	'- Words: ' . $words,
	'- Images: ' . count( $valid_image_ids ),
	'- Missing image bases: ' . ( $missing ? implode( ', ', $missing ) : 'none' ),
	'- Source files: ' . implode( ', ', $product['images'] ),
	'',
);

file_put_contents( dirname( __DIR__ ) . '/product-samples-kraft-corrugated-mailer-audit.md', implode( PHP_EOL, $audit ) );
echo 'Imported: ' . $product['title'] . ' (#' . $product_id . ') status=' . get_post_status( $product_id ) . ' words=' . $words . ' images=' . count( $valid_image_ids ) . PHP_EOL;
echo 'Kraft corrugated mailer product import complete.' . PHP_EOL;
