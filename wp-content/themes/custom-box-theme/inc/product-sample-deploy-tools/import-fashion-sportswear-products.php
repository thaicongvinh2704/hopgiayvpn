<?php
/**
 * Import Fashion and Sportswear Packaging products from the temporary fasion image folder.
 *
 * Usage:
 *   php tools/import-fashion-sportswear-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

function vpn_fashion_link( string $url, string $anchor ): string {
	return '<a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_fashion_upload_attachment_id( string $source_relative_path, string $alt, string $title ): int {
	$source_path = ABSPATH . $source_relative_path;

	if ( ! file_exists( $source_path ) ) {
		return 0;
	}

	$uploads    = wp_get_upload_dir();
	$target_dir = trailingslashit( $uploads['basedir'] ) . '2026/06/fashion-sportswear';

	if ( ! wp_mkdir_p( $target_dir ) ) {
		return 0;
	}

	$filename      = sanitize_file_name( basename( $source_path ) );
	$target_path   = trailingslashit( $target_dir ) . $filename;
	$attached_file = ltrim( str_replace( str_replace( '\\', '/', $uploads['basedir'] ), '', str_replace( '\\', '/', $target_path ) ), '/' );
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
		update_post_meta( (int) $existing[0], '_wp_attachment_image_alt', $alt );
		wp_update_post(
			array(
				'ID'         => (int) $existing[0],
				'post_title' => $title,
			)
		);
		return (int) $existing[0];
	}

	if ( ! file_exists( $target_path ) ) {
		copy( $source_path, $target_path );
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

function vpn_fashion_specs( array $p ): array {
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

function vpn_fashion_section( string $heading, array $paragraphs ): string {
	$html = '<h2>' . esc_html( $heading ) . '</h2>';
	foreach ( $paragraphs as $paragraph ) {
		$html .= '<p>' . $paragraph . '</p>';
	}
	return $html;
}

function vpn_fashion_inline_images( array $p, array $image_ids ): string {
	$html  = '';
	$limit = min( 4, count( $image_ids ) );

	for ( $i = 0; $i < $limit; $i++ ) {
		if ( empty( $image_ids[ $i ] ) ) {
			continue;
		}

		$image = wp_get_attachment_image( $image_ids[ $i ], 'large', false, array( 'loading' => 'lazy' ) );
		if ( ! $image ) {
			continue;
		}

		$html .= '<figure class="product-inline-figure product-inline-figure-small' . ( $i % 2 ? ' is-narrow' : '' ) . '">';
		$html .= $image;
		$html .= '<figcaption>' . esc_html( $p['captions'][ $i ] ?? $p['captions'][0] ) . '</figcaption>';
		$html .= '</figure>';
	}

	return $html;
}

function vpn_fashion_content( array $p, array $image_ids ): string {
	$category_link = vpn_fashion_link( '/packaging/fashion-sportswear-packaging/', 'fashion and sportswear packaging' );
	$quote_link    = vpn_fashion_link( '/contact/#quote', 'request a fashion packaging quotation' );
	$material_link = vpn_fashion_link( '/paper-materials-for-custom-paper-boxes/', 'paper material options for custom boxes' );
	$related_link  = vpn_fashion_link( $p['related_url'], $p['related_anchor'] );
	$difference    = $p['difference'] ?? strtolower( $p['keyword'] ) . ' product fit, retail presentation, and brand-specific packing details';

	$html  = vpn_fashion_section(
		$p['heading'],
		array(
			$p['title'] . ' is designed for ' . $p['audience'] . ' that need packaging matched to ' . $p['core_need'] . '. Fashion packaging is different from ordinary product boxes because the package must protect a soft or shaped item while also creating a clean retail presentation. The buyer usually checks opening style, insert fit, surface finish, folding strength, logo position, barcode area, and packing speed before approving mass production.',
			'This product belongs to our ' . $category_link . ' category. It can be used for brand launches, wholesale apparel programs, boutique retail, e-commerce fulfillment, distributor catalogs, and OEM/ODM projects where packaging needs to support several sizes or colorways without losing a consistent brand look.',
		)
	);

	$html .= vpn_fashion_inline_images( $p, array_slice( $image_ids, 0, 1 ) );
	$html .= vpn_fashion_section(
		$p['structure_heading'],
		array(
			'The structure can be produced as ' . $p['structures'] . '. The best choice depends on the product thickness, folding direction, how customers should open the box, whether the item needs tissue paper, and whether the package must stack neatly in retail storage. A drawer box creates a premium reveal, a lid-and-base box feels more gift-ready, and a folding carton is often efficient for higher-volume fashion programs.',
			'Sampling should always use the real product instead of only a size estimate. For ' . strtolower( $p['keyword'] ) . ', small differences in product thickness, edge shape, fabric compression, or accessory layout can affect the box size and insert design. Testing the sample helps confirm corner strength, closure tolerance, product movement, and whether the package still looks neat after packing.',
		)
	);
	$html .= vpn_fashion_section(
		$p['material_heading'],
		array(
			'Recommended materials include ' . $p['materials'] . '. Coated paper supports sharp printing and detailed logos, kraft paper works for natural fashion brands, rigid board gives a stronger gift feel, and corrugated paper can improve protection when the product is heavier or shipped through e-commerce channels. Buyers can compare board strength and surface options in our ' . $material_link . ' guide before confirming the final specification.',
			'The surface finish should match the brand position. Matte lamination can make the package feel calm and premium, gloss lamination can make darker colors stronger, foil stamping can highlight a logo, and spot UV can emphasize a pattern or product name. For export orders, the finish should also resist rubbing during packing and shipping.',
		)
	);
	$html .= vpn_fashion_inline_images( array_merge( $p, array( 'captions' => array_slice( $p['captions'], 1 ) ) ), array_slice( $image_ids, 1, 1 ) );
	$html .= vpn_fashion_section(
		$p['application_heading'],
		array(
			'This packaging is suitable for ' . $p['applications'] . '. The application details matter because apparel, shoes, belts, wallets, underwear, and sportswear do not behave the same way inside a box. Some products need flat folding, some need a cavity or divider, and some need a stronger lid because the item is heavier or presented as a gift.',
			'For buyers comparing related fashion products, this page can be reviewed together with ' . $related_link . '. Keeping each packaging page focused on its own item reduces duplicate content and makes the quote request clearer. It also helps the supplier recommend the right board, insert, paper wrapping, and printing layout.',
		)
	);
	$html .= vpn_fashion_section(
		$p['custom_heading'],
		array(
			'Customization can include ' . $p['customization'] . '. The dieline should reserve space for logo placement, product size, SKU label, barcode, care information, QR code, collection name, and any retail claims. If the product has multiple sizes or colorways, a small label area or color-coding system can make inventory easier for distributors and retailers.',
			'Before production, confirm at least eight details: product dimensions, product weight, folding method, preferred opening style, tissue or insert requirement, logo area, carton quantity, and shipping method. These details make the quotation more accurate and help avoid a box that looks good in a mockup but does not pack well in real production.',
		)
	);
	$html .= vpn_fashion_section(
		$p['b2b_heading'],
		array(
			'For B2B orders, the value is not only a nicer box. A clear packaging specification helps the buyer control repeat orders, reduce packing mistakes, improve shelf display, and keep one visual identity across different product lines. Bulk production from 1000 boxes can support seasonal launches, private label fashion lines, online stores, promotional campaigns, and export retail programs.',
			'VPN Paper Box Manufacturer can adjust size, paper material, insert, printing, finishing, and packing method according to the buyer project. Send your product photos, dimensions, quantity, logo artwork, and preferred box style to ' . $quote_link . ' so the team can recommend a practical structure and sample direction.',
		)
	);
	$html .= vpn_fashion_section(
		$p['difference_heading'],
		array(
			'This page focuses on ' . $difference . ', which keeps it different from other fashion packaging products in the same category. A shoe box needs different strength logic from a T-shirt box, a wallet box needs a smaller premium reveal, and underwear packaging often needs a front window or multi-pack display. The content, specs, captions, and SEO keyword are written around that specific packaging problem.',
			'The goal is to help international buyers describe what they need before asking for a quote. A specific product page makes it easier to discuss box type, insert style, artwork layout, sample cost, production quantity, and shipping requirements without starting from a generic paper box description.',
		)
	);
	$html .= vpn_fashion_inline_images( array_merge( $p, array( 'captions' => array_slice( $p['captions'], 2 ) ) ), array_slice( $image_ids, 2, 2 ) );
	$html .= '<h3>' . esc_html( $p['cta_heading'] ) . '</h3>';
	$html .= '<p>Share the product size, target quantity, image reference, artwork file, and preferred material to ' . $quote_link . '. We can help prepare a custom ' . esc_html( strtolower( $p['keyword'] ) ) . ' structure for sampling and bulk production.</p>';

	return $html;
}

$category_slug = 'fashion-sportswear-packaging';
$category_name = 'Fashion and Sportswear Packaging';
$marker        = 'product-samples-fashion-sportswear';

$products = array(
	array(
		'title' => 'CUSTOM SHOE PACKAGING BOX',
		'slug' => 'custom-shoe-packaging-box',
		'keyword' => 'shoe packaging box',
		'heading' => 'Shoe Packaging Box for Footwear Brands and Retail Programs',
		'structure_heading' => 'Footwear Box Structure and Opening Style',
		'material_heading' => 'Paperboard Materials for Shoe Boxes',
		'application_heading' => 'Footwear Packaging Applications',
		'custom_heading' => 'Custom Shoe Size, Tissue Paper, and Branding',
		'b2b_heading' => 'B2B Value for Footwear Packaging Buyers',
		'difference_heading' => 'Why Shoe Packaging Needs Separate Planning',
		'cta_heading' => 'Request a Custom Shoe Packaging Box Quote',
		'audience' => 'shoe brands, sneaker distributors, footwear retailers, sportswear suppliers, and private label factories',
		'core_need' => 'shoe pair protection, lid strength, tissue paper presentation, size labeling, and shelf-ready footwear branding',
		'structures' => 'lid-and-base shoe boxes, rigid footwear boxes, folding carton shoe boxes, drawer shoe boxes, and corrugated mailer shoe boxes',
		'materials' => 'duplex board, ivory paper, kraft paper, corrugated board, art paper, rigid greyboard, and specialty wrapping paper',
		'applications' => 'sneakers, leather shoes, sandals, sports shoes, kids shoes, boutique footwear, sample shoes, and promotional footwear packs',
		'customization' => 'shoe size label, tissue paper, finger hole, logo lid, side barcode, collection name, inner print, ventilation holes, and export carton marks',
		'related_url' => '/product/custom-sportswear-packaging-box/',
		'related_anchor' => 'sportswear packaging boxes for apparel lines',
		'feature' => 'Footwear box structure, tissue paper presentation, size label area, retail-ready logo printing',
		'industrial' => 'Footwear, Fashion, Sportswear, Retail Packaging',
		'paper' => 'Duplex Board / Ivory Paper / Kraft Paper / Corrugated Board / Rigid Board',
		'box_type' => 'Shoe Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Tissue paper / Finger hole / Paper insert / Sleeve / Custom label',
		'liner' => 'Tissue paper / Paperboard insert / Corrugated support',
		'colors' => 'Black / White / Kraft / CMYK / Pantone / Customized Color',
		'images' => array(
			'wp-content/themes/fasion/custom-shoe-packaging-box-images/custom-shoe-packaging-box-closed-lid-base-01.webp',
			'wp-content/themes/fasion/custom-shoe-packaging-box-images/custom-shoe-packaging-box-open-with-tissue-paper-02.webp',
			'wp-content/themes/fasion/custom-shoe-packaging-box-images/custom-shoe-packaging-box-folding-carton-finger-hole-03.webp',
			'wp-content/themes/fasion/custom-shoe-packaging-box-images/custom-luxury-shoe-packaging-box-rigid-lid-base-04.webp',
		),
		'captions' => array(
			'Custom shoe packaging box with closed lid-and-base footwear structure.',
			'Shoe box opened with tissue paper for premium footwear presentation.',
			'Folding carton shoe box with finger hole for easier opening.',
			'Luxury rigid shoe packaging box for boutique footwear brands.',
		),
		'alt' => 'Shoe packaging box for custom footwear retail display',
	),
	array(
		'title' => 'CUSTOM BELT PACKAGING BOX',
		'slug' => 'custom-belt-packaging-box',
		'keyword' => 'belt packaging box',
		'heading' => 'Belt Packaging Box for Leather Accessories and Gift Retail',
		'structure_heading' => 'Belt Box Structure and Coil Fit',
		'material_heading' => 'Rigid and Kraft Materials for Belt Packaging',
		'application_heading' => 'Belt and Fashion Accessory Applications',
		'custom_heading' => 'Custom Inserts, Logo, and Accessory Layout',
		'b2b_heading' => 'B2B Value for Belt Brands',
		'difference_heading' => 'Why Belt Packaging Needs Accessory-Specific Fit',
		'cta_heading' => 'Request a Custom Belt Packaging Box Quote',
		'audience' => 'belt brands, leather accessory suppliers, menswear retailers, promotional gift buyers, and private label factories',
		'core_need' => 'coiled belt presentation, buckle protection, premium accessory reveal, and compact retail storage',
		'structures' => 'rigid lid boxes, drawer belt boxes, kraft accessory boxes, sleeve boxes, and gift boxes with paper or EVA inserts',
		'materials' => 'rigid board, kraft paper, art paper, coated paper, black card, specialty paper, and EVA insert material',
		'applications' => 'leather belts, woven belts, formal menswear accessories, promotional belt gifts, boutique accessories, and retail gift sets',
		'customization' => 'belt coil diameter, buckle cavity, logo position, inner card, ribbon pull, drawer sleeve, barcode label, and brand color matching',
		'related_url' => '/product/custom-wallet-packaging-box/',
		'related_anchor' => 'wallet packaging boxes for leather accessories',
		'feature' => 'Coiled belt fit, buckle protection, rigid accessory box, custom logo finish',
		'industrial' => 'Fashion Accessories, Leather Goods, Gift Packaging',
		'paper' => 'Rigid Board / Kraft Paper / Art Paper / Specialty Paper / Coated Paper',
		'box_type' => 'Belt Packaging Box',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'EVA insert / Paper tray / Ribbon pull / Sleeve / Inner card',
		'liner' => 'EVA insert / Paperboard tray / Foam insert / Custom cavity',
		'colors' => 'Black / Kraft / Brown / CMYK / Pantone / Customized Color',
		'images' => array(
			'wp-content/themes/fasion/custom-belt-packaging-box-images/custom-belt-packaging-box-closed-lid-01.webp',
			'wp-content/themes/fasion/custom-belt-packaging-box-images/custom-belt-packaging-box-open-with-belt-02.webp',
			'wp-content/themes/fasion/custom-belt-packaging-box-images/kraft-belt-packaging-box-drawer-style-03.webp',
			'wp-content/themes/fasion/custom-belt-packaging-box-images/custom-rigid-belt-gift-box-with-logo-04.webp',
		),
		'captions' => array(
			'Custom belt packaging box with closed lid for leather accessory retail.',
			'Belt packaging box opened with coiled belt and fitted presentation.',
			'Kraft drawer style belt packaging box for natural accessory branding.',
			'Rigid belt gift box with custom logo for premium menswear accessories.',
		),
		'alt' => 'Belt packaging box for leather accessory gift presentation',
	),
	array(
		'title' => 'CUSTOM MEN UNDERWEAR PACKAGING BOX',
		'slug' => 'custom-men-underwear-packaging-box',
		'keyword' => 'men underwear packaging box',
		'heading' => 'Men Underwear Packaging Box for Retail Multi-Pack Display',
		'structure_heading' => 'Underwear Box Structure and Window Display',
		'material_heading' => 'Paper Materials for Apparel Window Boxes',
		'application_heading' => 'Men Underwear and Apparel Applications',
		'custom_heading' => 'Custom Size, Window, and SKU Information',
		'b2b_heading' => 'B2B Value for Underwear Packaging Buyers',
		'difference_heading' => 'Why Underwear Packaging Needs Retail Clarity',
		'cta_heading' => 'Request a Men Underwear Packaging Box Quote',
		'audience' => 'underwear brands, apparel factories, menswear retailers, supermarket buyers, and private label suppliers',
		'core_need' => 'folded garment visibility, size communication, multi-pack organization, and clean shelf display',
		'structures' => 'folding cartons with windows, sleeve boxes, tuck-end apparel boxes, multi-pack paper boxes, and retail cartons with hang options',
		'materials' => 'ivory board, duplex board, art paper, PET window film, kraft paper, and coated paperboard',
		'applications' => 'men underwear, boxer briefs, socks, undershirts, activewear basics, multi-pack apparel, and retail clothing sets',
		'customization' => 'clear window, size chart, fabric icons, pack count, color variant, barcode, QR code, care symbols, and UV logo details',
		'related_url' => '/product/custom-t-shirt-packaging-box/',
		'related_anchor' => 'T-shirt packaging boxes for folded apparel',
		'feature' => 'Window display, multi-pack apparel layout, size information, retail-ready folding carton',
		'industrial' => 'Underwear, Apparel, Menswear, Retail Packaging',
		'paper' => 'Ivory Board / Duplex Board / Art Paper / PET Window / Kraft Paper',
		'box_type' => 'Men Underwear Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'PET window / Hang tab / Insert card / Sleeve / Size label',
		'liner' => 'Paperboard divider / No liner / Custom folded garment support',
		'colors' => 'Black / Gray / White / CMYK / Pantone / Customized Color',
		'images' => array(
			'wp-content/themes/fasion/custom-men-underwear-packaging-box-images/custom-men-underwear-packaging-box-uv-gloss-window-01.webp',
			'wp-content/themes/fasion/custom-men-underwear-packaging-box-images/custom-men-underwear-packaging-box-dark-gray-window-02.webp',
			'wp-content/themes/fasion/custom-men-underwear-packaging-box-images/custom-men-underwear-packaging-box-multi-pack-window-04.webp',
		),
		'captions' => array(
			'Men underwear packaging box with UV gloss and window display.',
			'Dark gray underwear box with clear retail window for folded apparel.',
			'Multi-pack underwear packaging box with front window and size information.',
		),
		'alt' => 'Men underwear packaging box with retail window display',
	),
	array(
		'title' => 'CUSTOM SPORTSWEAR PACKAGING BOX',
		'slug' => 'custom-sportswear-packaging-box',
		'keyword' => 'sportswear packaging box',
		'heading' => 'Sportswear Packaging Box for Activewear and Fitness Apparel',
		'structure_heading' => 'Sportswear Box Structure for Folded Apparel',
		'material_heading' => 'Paperboard Choices for Activewear Packaging',
		'application_heading' => 'Sportswear and Fitness Apparel Applications',
		'custom_heading' => 'Custom Branding, Size Labels, and Product Claims',
		'b2b_heading' => 'B2B Value for Sportswear Brands',
		'difference_heading' => 'Why Sportswear Packaging Needs Performance Messaging',
		'cta_heading' => 'Request a Sportswear Packaging Box Quote',
		'audience' => 'sportswear brands, activewear suppliers, gym apparel retailers, event merchandise buyers, and private label factories',
		'core_need' => 'folded apparel protection, performance branding, size clarity, and efficient e-commerce packing',
		'structures' => 'folding apparel cartons, sleeve boxes, drawer boxes, corrugated mailer boxes, and rigid activewear gift boxes',
		'materials' => 'ivory board, kraft paper, coated paper, corrugated board, duplex board, and specialty athletic brand paper',
		'applications' => 'sportswear, gym shirts, leggings, team apparel, yoga wear, running accessories, fitness merchandise, and promotional activewear kits',
		'customization' => 'size labels, fabric feature icons, QR code, campaign graphics, colorway stickers, hang tag pocket, barcode panel, and brand pattern',
		'related_url' => '/product/custom-shoe-packaging-box/',
		'related_anchor' => 'shoe packaging boxes for footwear brands',
		'feature' => 'Activewear retail branding, folded apparel fit, size label area, performance product messaging',
		'industrial' => 'Sportswear, Activewear, Fashion, Retail Packaging',
		'paper' => 'Ivory Board / Kraft Paper / Coated Paper / Corrugated Board / Duplex Board',
		'box_type' => 'Sportswear Packaging Box',
		'shape' => 'Rectangle / Customized Shape',
		'accessories' => 'Sleeve / Label area / Insert card / Hang tag pocket / Tissue paper',
		'liner' => 'No liner / Tissue paper / Paperboard divider / Custom insert',
		'colors' => 'White / Black / Blue / CMYK / Pantone / Customized Color',
		'images' => array(
			'wp-content/themes/fasion/custom-sportswear-packaging-box-images/custom-sportswear-packaging-box-01.webp',
			'wp-content/themes/fasion/custom-sportswear-packaging-box-images/custom-sportswear-packaging-box-04.webp',
		),
		'captions' => array(
			'Custom sportswear packaging box for activewear retail programs.',
			'Activewear packaging box for e-commerce and retail display.',
		),
		'alt' => 'Sportswear packaging box for activewear and fitness apparel',
	),
	array(
		'title' => 'CUSTOM T-SHIRT PACKAGING BOX',
		'slug' => 'custom-t-shirt-packaging-box',
		'keyword' => 't-shirt packaging box',
		'heading' => 'T-Shirt Packaging Box for Folded Apparel and Retail Sets',
		'structure_heading' => 'T-Shirt Box Structure and Folded Garment Fit',
		'material_heading' => 'Paper Materials for T-Shirt Boxes',
		'application_heading' => 'T-Shirt and Apparel Product Applications',
		'custom_heading' => 'Custom Apparel Labels, Tissue, and Inner Presentation',
		'b2b_heading' => 'B2B Value for T-Shirt Brands',
		'difference_heading' => 'Why T-Shirt Packaging Needs Fold Control',
		'cta_heading' => 'Request a Custom T-Shirt Packaging Box Quote',
		'audience' => 'T-shirt brands, apparel retailers, fashion startups, event merchandise suppliers, and private label clothing factories',
		'core_need' => 'folded shirt presentation, size labeling, clean opening, and brand-ready garment protection',
		'structures' => 'folding cartons, lid-and-base apparel boxes, drawer boxes, sleeve boxes, and shallow rigid gift boxes',
		'materials' => 'ivory board, kraft paper, art paper, duplex board, rigid board, and specialty textured paper',
		'applications' => 'T-shirts, polo shirts, folded shirts, event apparel, fashion subscription boxes, corporate uniforms, and boutique clothing sets',
		'customization' => 'shirt size label, inner tissue, logo print, collection name, care card, QR code, barcode, color variant label, and sleeve opening',
		'related_url' => '/product/custom-men-underwear-packaging-box/',
		'related_anchor' => 'men underwear packaging boxes with window display',
		'feature' => 'Folded T-shirt fit, apparel retail box, size label area, clean brand presentation',
		'industrial' => 'T-Shirt, Apparel, Fashion, Retail Packaging',
		'paper' => 'Ivory Board / Kraft Paper / Art Paper / Duplex Board / Rigid Board',
		'box_type' => 'T-Shirt Packaging Box',
		'shape' => 'Rectangle / Shallow Rectangle / Customized Shape',
		'accessories' => 'Tissue paper / Sleeve / Care card / Size label / Insert card',
		'liner' => 'Tissue paper / No liner / Paperboard support',
		'colors' => 'White / Beige / Black / CMYK / Pantone / Customized Color',
		'images' => array(
			'wp-content/themes/fasion/custom-t-shirt-packaging-box-images/custom-t-shirt-packaging-box-open-white-shirt-01.webp',
			'wp-content/themes/fasion/custom-t-shirt-packaging-box-images/custom-t-shirt-packaging-box-half-open-dark-shirt-02.webp',
			'wp-content/themes/fasion/custom-t-shirt-packaging-box-images/custom-t-shirt-packaging-box-open-beige-polo-shirt-04.webp',
		),
		'captions' => array(
			'T-shirt packaging box opened with folded white shirt presentation.',
			'Half-open T-shirt box with dark garment for apparel retail display.',
			'Open T-shirt packaging box with beige polo shirt for boutique apparel.',
		),
		'alt' => 'T-shirt packaging box for folded apparel retail presentation',
	),
	array(
		'title' => 'CUSTOM WALLET PACKAGING BOX',
		'slug' => 'custom-wallet-packaging-box',
		'keyword' => 'wallet packaging box',
		'heading' => 'Wallet Packaging Box for Leather Goods and Premium Accessories',
		'structure_heading' => 'Wallet Box Structure and Insert Fit',
		'material_heading' => 'Premium Paper Materials for Wallet Packaging',
		'application_heading' => 'Wallet and Leather Accessory Applications',
		'custom_heading' => 'Custom Insert, Logo, and Gift Details',
		'b2b_heading' => 'B2B Value for Wallet Packaging Buyers',
		'difference_heading' => 'Why Wallet Packaging Needs Compact Premium Detail',
		'cta_heading' => 'Request a Wallet Packaging Box Quote',
		'audience' => 'wallet brands, leather goods suppliers, menswear retailers, gift shops, and private label accessory factories',
		'core_need' => 'compact product fit, premium reveal, insert support, and small accessory brand presentation',
		'structures' => 'rigid wallet boxes, lid-and-base boxes, drawer accessory boxes, kraft logo boxes, and gift boxes with custom inserts',
		'materials' => 'rigid board, kraft paper, art paper, black card, specialty paper, EVA, foam, and coated paper',
		'applications' => 'wallets, card holders, leather accessories, passport holders, small gift items, menswear accessories, and premium retail sets',
		'customization' => 'wallet insert depth, logo foil, inner card, ribbon pull, kraft logo print, barcode sticker, gift sleeve, and brand color matching',
		'related_url' => '/product/custom-belt-packaging-box/',
		'related_anchor' => 'belt packaging boxes for leather accessory sets',
		'feature' => 'Compact rigid wallet box, leather accessory insert, premium logo finish, gift-ready presentation',
		'industrial' => 'Wallet, Leather Goods, Fashion Accessories, Gift Packaging',
		'paper' => 'Rigid Board / Kraft Paper / Art Paper / Black Card / Specialty Paper',
		'box_type' => 'Wallet Packaging Box',
		'shape' => 'Rectangle / Square / Customized Shape',
		'accessories' => 'EVA insert / Paper tray / Ribbon / Inner card / Sleeve',
		'liner' => 'EVA insert / Foam insert / Paperboard tray / Custom cavity',
		'colors' => 'Black / Kraft / Brown / CMYK / Pantone / Customized Color',
		'images' => array(
			'wp-content/themes/fasion/custom-wallet-packaging-box-images/custom-wallet-packaging-box-closed-lid-01.webp',
			'wp-content/themes/fasion/custom-wallet-packaging-box-images/custom-wallet-packaging-box-open-with-wallet-02.webp',
			'wp-content/themes/fasion/custom-wallet-packaging-box-images/kraft-wallet-packaging-box-with-custom-logo-03.webp',
			'wp-content/themes/fasion/custom-wallet-packaging-box-images/custom-wallet-rigid-paper-box-with-insert-04.webp',
		),
		'captions' => array(
			'Custom wallet packaging box with closed lid for leather goods retail.',
			'Wallet packaging box opened with wallet and compact insert layout.',
			'Kraft wallet packaging box with custom logo for natural accessory branding.',
			'Rigid wallet paper box with insert for premium gift presentation.',
		),
		'alt' => 'Wallet packaging box for leather goods and premium accessories',
	),
);

$audit = array( '# Fashion and Sportswear Product Import Audit', '' );

foreach ( $products as $p ) {
	$image_ids = array();

	foreach ( $p['images'] as $i => $image ) {
		$image_ids[] = vpn_fashion_upload_attachment_id( $image, $p['alt'], $p['captions'][ $i ] ?? $p['title'] );
	}

	$existing = get_page_by_path( $p['slug'], OBJECT, 'product' );
	$postarr  = array(
		'post_type'    => 'product',
		'post_status'  => 'publish',
		'post_title'   => $p['title'],
		'post_name'    => $p['slug'],
		'post_excerpt' => $p['title'] . ' is a custom paper packaging solution for ' . $p['applications'] . '. It supports custom size, logo, material, insert, color, printing, finishing, and OEM/ODM bulk production from 1000 boxes.',
		'post_content' => vpn_fashion_content( $p, $image_ids ),
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

	$term = get_term_by( 'slug', $category_slug, 'product_cat' );
	if ( ! $term || is_wp_error( $term ) ) {
		$created = wp_insert_term( $category_name, 'product_cat', array( 'slug' => $category_slug ) );
		$term_id = is_wp_error( $created ) ? 0 : (int) $created['term_id'];
	} else {
		$term_id = (int) $term->term_id;
	}

	if ( $term_id ) {
		wp_set_object_terms( $product_id, array( $term_id ), 'product_cat', false );
	}

	wp_set_object_terms( $product_id, 'simple', 'product_type' );
	wp_set_object_terms( $product_id, array( $p['keyword'], 'fashion packaging', 'sportswear packaging', 'custom paper box', 'custom packaging' ), 'product_tag' );

	if ( ! empty( $image_ids[0] ) ) {
		set_post_thumbnail( $product_id, $image_ids[0] );
	}

	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_filter( array_slice( $image_ids, 1 ) ) ) );
	update_post_meta( $product_id, '_sku', 'sample-fashion-' . $p['slug'] );
	update_post_meta( $product_id, '_regular_price', '' );
	update_post_meta( $product_id, '_price', '' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_fashion_specs( $p ) );
	update_post_meta( $product_id, '_vpn_sample_import', $marker );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $p['keyword'] );
	update_post_meta( $product_id, 'rank_math_title', $p['title'] . ' | VPN PAPER BOX MANUFACTURER' );
	update_post_meta( $product_id, 'rank_math_description', $p['title'] . ' for ' . $p['applications'] . ', customized with logo, material, insert, printing, finishing, and bulk production.' );

	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ) );
	$audit[] = '## ' . $p['title'];
	$audit[] = '- URL: ' . get_permalink( $product_id );
	$audit[] = '- Category: ' . $category_name;
	$audit[] = '- Focus keyword: ' . $p['keyword'];
	$audit[] = '- Words: ' . $words;
	$audit[] = '- Images: ' . count( array_filter( $image_ids ) );
	$audit[] = '- Source files: ' . implode( ', ', array_map( 'basename', $p['images'] ) );
	$audit[] = '';

	echo 'Imported: ' . $p['title'] . ' (#' . $product_id . ') words=' . $words . PHP_EOL;
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-fashion-sportswear-audit.md', implode( PHP_EOL, $audit ) );
flush_rewrite_rules( false );

echo 'Fashion and sportswear product import complete.' . PHP_EOL;
