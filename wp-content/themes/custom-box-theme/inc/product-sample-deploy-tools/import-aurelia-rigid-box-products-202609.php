<?php
/**
 * Idempotent importer for three custom rigid-box products from the September 2026 image set.
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

if ( ! defined( 'VPN_AURELIA_RIGID_BOXES_202609_MARKER' ) ) {
	define( 'VPN_AURELIA_RIGID_BOXES_202609_MARKER', 'product-samples-aurelia-rigid-boxes-202609' );
}

function vpn_aurelia_rigid_boxes_202609_image( $file, $alt, $title, $caption ) {
	return compact( 'file', 'alt', 'title', 'caption' );
}

function vpn_aurelia_rigid_boxes_202609_definitions() {
	return array(
		array(
			'title'            => 'Custom Burgundy Magnetic Perfume Gift Box with Velvet Insert',
			'slug'             => 'custom-burgundy-magnetic-perfume-gift-box-velvet-insert',
			'content_file'     => 'custom-burgundy-magnetic-perfume-gift-box-velvet-insert.html',
			'focus_keyword'    => 'custom magnetic perfume gift box',
			'seo_title'        => 'Custom Magnetic Perfume Gift Box | Vietnam Manufacturer',
			'seo_description'  => 'Custom burgundy magnetic perfume gift boxes made in Vietnam with velvet inserts, rigid board, foil logos and OEM sizing for fragrance sets.',
			'excerpt'          => 'This custom magnetic perfume gift box uses a book-style rigid structure, concealed magnetic closure and fitted black velvet insert to present fragrance bottles and beauty gift-set components in an organized reveal. The burgundy wrap and gold foil branding shown are a premium design reference; box size, color, board, paper, insert cavities and artwork are developed for each OEM project. It is suitable for perfume launches, discovery sets, holiday collections, influencer kits and branded gifts that need both presentation and controlled product placement. VPN Packaging manufactures the box in Vietnam for international B2B buyers. Send the bottle dimensions, filled weight, set layout, required quantity, finish brief and destination for structural review, sampling and a project-specific quotation. A protective export carton can be planned separately for the selected distribution route.',
			'categories'       => array( 'perfume-packaging-boxes', 'magnetic-closure-boxes', 'rigid-boxes', 'beauty-skincare-packaging', 'gift-paper-boxes' ),
			'primary_category' => 'perfume-packaging-boxes',
			'tags'             => array( 'custom perfume packaging', 'magnetic rigid boxes', 'velvet insert boxes', 'luxury fragrance packaging', 'gold foil packaging' ),
			'specs'            => array(
				'Feature' => 'Book-style rigid presentation box with concealed magnetic closure and fitted velvet-lined insert',
				'Industrial Use' => 'Perfume, fragrance discovery sets, cosmetics, beauty gift sets and launch kits',
				'Paper Type' => 'Rigid greyboard with custom specialty-paper wrap; final grade confirmed by sample',
				'Box Type' => 'Magnetic closure rigid gift box',
				'Shape' => 'Square or rectangular book-style box, customized to the product set',
				'Accessories' => 'Concealed magnets and custom velvet-lined product insert',
				'Liner Type' => 'Black velvet or project-specific lining over the selected support insert',
				'Printing Handling' => 'Gold foil, embossing or debossing, Pantone/CMYK printing and custom finish options',
				'Color' => 'Burgundy, gold and black sample; custom colors accepted',
				'Model Number' => 'VPN-RB-MP-BURGUNDY',
			),
			'images'           => array(
				vpn_aurelia_rigid_boxes_202609_image( 'custom-burgundy-magnetic-perfume-gift-box-closed.webp', 'Custom magnetic perfume gift box in burgundy shown closed with gold foil logo', 'Burgundy Magnetic Perfume Gift Box - Closed View', 'Closed burgundy magnetic perfume gift box with a textured wrap and gold foil brand mark.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-burgundy-magnetic-perfume-gift-box-open-velvet-insert.webp', 'Open burgundy magnetic perfume gift box with black velvet fitted insert', 'Burgundy Magnetic Perfume Gift Box - Velvet Insert', 'Open book-style box showing the fitted black velvet tray for a fragrance or beauty set.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-burgundy-magnetic-perfume-gift-box-top-view.webp', 'Top view of burgundy rigid perfume gift box with centered gold foil branding', 'Burgundy Perfume Gift Box - Top View', 'Top view highlighting the centered foil logo and clean square proportions.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-burgundy-magnetic-perfume-gift-box-front-view.webp', 'Front view of burgundy magnetic perfume box with concealed closure and gold edge', 'Burgundy Magnetic Perfume Box - Front View', 'Front profile of the rigid magnetic box and its aligned closing edge.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-burgundy-magnetic-perfume-gift-box-gold-foil-detail.webp', 'Close-up of burgundy textured perfume box wrap with gold foil deboss detail', 'Burgundy Perfume Box - Gold Foil Detail', 'Macro view of the textured burgundy wrap and metallic gold branding detail.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-burgundy-magnetic-perfume-gift-box-boutique-display.webp', 'Burgundy magnetic fragrance gift box displayed with perfume bottle in boutique setting', 'Burgundy Perfume Gift Box - Boutique Display', 'Luxury retail display concept for a custom burgundy magnetic fragrance box.' ),
			),
			'faqs'             => array(
				array( 'Can the velvet insert be fitted to our perfume bottles?', 'Yes. The insert layout is developed from physical samples or accurate dimensions, filled weights and orientation requirements. Cavity material and lining are confirmed during sampling.' ),
				array( 'Is burgundy the only available color?', 'No. Burgundy, gold and black describe the photographed sample. The wrap, print colors, foil shade and interior can be customized to an approved brand brief.' ),
				array( 'What is the minimum order quantity?', 'The standard project reference is 1,000 boxes. The confirmed MOQ can depend on size, materials, tooling, artwork variants and finishing, so it is stated in the quotation.' ),
				array( 'Can you make a sample before production?', 'Yes. A structural sample can confirm bottle fit and opening action, followed by a decorated sample when color, foil and surface details need approval.' ),
				array( 'Where is the box manufactured?', 'VPN Packaging develops and manufactures custom paper packaging in Ho Chi Minh City, Vietnam, for domestic and international B2B projects.' ),
				array( 'What information is needed for a quote?', 'Send product dimensions and weights, set layout, target quantity, artwork, preferred materials and finishes, destination and delivery objective.' ),
			),
		),
		array(
			'title'            => 'Custom Emerald Lid and Base Rigid Gift Box',
			'slug'             => 'custom-emerald-lid-base-rigid-gift-box',
			'content_file'     => 'custom-emerald-lid-base-rigid-gift-box.html',
			'focus_keyword'    => 'custom lid and base rigid gift box',
			'seo_title'        => 'Custom Lid and Base Rigid Gift Box | Vietnam Factory',
			'seo_description'  => 'Custom emerald lid and base rigid gift boxes from Vietnam with fitted inserts, gold foil, embossed details and OEM sizes for luxury products.',
			'excerpt'          => 'This custom lid and base rigid gift box pairs a separate lift-off lid with a strong wrapped base and fitted black compartment insert. The emerald, gold foil and blind embossed sample demonstrates a premium two-piece format for cosmetics, jewelry, specialty retail collections and corporate gifts. Every production detail is customizable: dimensions, board, wrap paper, color, print, relief, foil coverage and insert geometry are confirmed against the buyer’s product and artwork. VPN Packaging manufactures the box in Vietnam for OEM and wholesale packaging projects. Provide product dimensions, filled weights, desired display orientation, order quantity, number of artwork variants and shipping destination. We can then evaluate lid clearance, insert support, decorative feasibility, sampling and export packing rather than treating the pictured sample as a fixed stock specification.',
			'categories'       => array( 'lid-and-base-boxes', 'rigid-boxes', 'gift-paper-boxes', 'custom-printed-paper-boxes', 'corporate-gift-packaging' ),
			'primary_category' => 'lid-and-base-boxes',
			'tags'             => array( 'luxury rigid gift boxes', 'two piece gift boxes', 'foil stamped boxes', 'embossed packaging', 'velvet insert boxes' ),
			'specs'            => array(
				'Feature' => 'Two-piece lift-off lid with ornate foil decoration, blind relief and fitted compartment insert',
				'Industrial Use' => 'Luxury goods, cosmetics, jewelry, specialty retail collections and corporate gifts',
				'Paper Type' => 'Rigid greyboard with custom printed or specialty-paper wrap; final grade confirmed by sample',
				'Box Type' => 'Two-piece lid and base rigid gift box',
				'Shape' => 'Square lift-off lid box, customized to the product layout',
				'Accessories' => 'Custom compartment tray or fitted presentation insert',
				'Liner Type' => 'Black velvet-style sample lining; custom paper, fabric or cushioning options',
				'Printing Handling' => 'Gold foil stamping, blind embossing/debossing, Pantone/CMYK and custom finishes',
				'Color' => 'Emerald green, gold and black sample; custom colors accepted',
				'Model Number' => 'VPN-RB-LB-EMERALD',
			),
			'images'           => array(
				vpn_aurelia_rigid_boxes_202609_image( 'custom-emerald-lid-base-rigid-gift-box-closed.webp', 'Custom emerald lid and base rigid gift box shown closed with gold foil border', 'Emerald Lid and Base Rigid Gift Box - Closed', 'Closed emerald two-piece gift box with an ornate gold foil border and embossed surface.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-emerald-lid-base-rigid-gift-box-open-velvet-insert.webp', 'Open emerald two-piece rigid gift box with black velvet compartment insert', 'Emerald Rigid Gift Box - Open Insert', 'Open lift-off lid box showing the dark fitted compartment tray.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-emerald-lid-base-rigid-gift-box-top-view-gold-foil.webp', 'Top view of emerald rigid gift box with ornate gold foil and blind embossed pattern', 'Emerald Rigid Gift Box - Gold Foil Top View', 'Top view of the emerald wrap, registered gold details and tactile background pattern.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-emerald-lid-base-rigid-gift-box-side-profile.webp', 'Side profile of emerald lid and base rigid gift box showing controlled lift-off lid fit', 'Emerald Lid and Base Box - Side Profile', 'Side profile illustrating the separate lid, base depth and controlled fit.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-emerald-lid-base-rigid-gift-box-embossed-foil-detail.webp', 'Close-up of emerald rigid gift box with embossed texture and gold foil detail', 'Emerald Rigid Gift Box - Embossed Foil Detail', 'Macro view of the textured green wrap, blind relief and metallic gold decoration.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-emerald-lid-base-rigid-gift-box-luxury-display.webp', 'Emerald luxury lid and base rigid gift box in premium retail display', 'Emerald Rigid Gift Box - Luxury Display', 'Retail presentation concept for a custom emerald two-piece rigid gift box.' ),
			),
			'faqs'             => array(
				array( 'What is a lid and base rigid gift box?', 'It is a two-piece setup box with a separate lift-off lid and rigid wrapped base. The lid depth and clearance are sampled to achieve the required opening feel.' ),
				array( 'Can the emerald and gold design be changed?', 'Yes. The sample is a visual reference. Color, paper, foil, print, embossed artwork and interior presentation can be developed to your brand specification.' ),
				array( 'Can the insert hold different products?', 'Yes. Compartments are engineered around the actual product dimensions, weights, orientation and removal method. Variant layouts should be listed in the RFQ.' ),
				array( 'What is the minimum order quantity?', 'The standard project reference is 1,000 boxes. Final MOQ is confirmed with the selected size, materials, tooling, finish and artwork split.' ),
				array( 'Do you provide production samples?', 'A structural sample can be used to approve lid fit and insert geometry. A decorated sample can then confirm color, foil and embossed details.' ),
				array( 'What should we send for pricing?', 'Send product data, required box size if known, target quantity, artwork, color and finish references, insert preference, destination and delivery objective.' ),
			),
		),
		array(
			'title'            => 'Custom Navy Shoulder Neck Jewelry Box with Velvet Insert',
			'slug'             => 'custom-navy-shoulder-neck-jewelry-box-velvet-insert',
			'content_file'     => 'custom-navy-shoulder-neck-jewelry-box-velvet-insert.html',
			'focus_keyword'    => 'custom shoulder neck jewelry box',
			'seo_title'        => 'Custom Shoulder Neck Jewelry Box | Vietnam Manufacturer',
			'seo_description'  => 'Custom navy shoulder neck jewelry boxes made in Vietnam with velvet inserts, gold neck reveals, foil logos and OEM sizing for luxury brands.',
			'excerpt'          => 'This custom shoulder neck jewelry box uses a separate rigid lid, lower base and raised inner neck to create an aligned opening action and visible gold reveal. The navy wrapped sample includes gold foil branding and a black velvet-lined insert suitable for a ring, earrings, pendant, small watch or keepsake when the cavity is engineered to fit. Size, board, wrap, neck color, logo finish and insert construction are customized for each OEM project. VPN Packaging manufactures shoulder boxes in Vietnam for jewelry brands, boutiques, corporate gifts and international packaging buyers. Send the item dimensions, weight, presentation angle, removal method, order quantity, artwork and destination for a project review. Sampling is used to confirm lid clearance, neck alignment, insert retention and surface details before production.',
			'categories'       => array( 'jewelry-paper-boxes', 'rigid-boxes', 'lid-and-base-boxes', 'gift-paper-boxes', 'custom-printed-paper-boxes' ),
			'primary_category' => 'jewelry-paper-boxes',
			'tags'             => array( 'custom jewelry boxes', 'shoulder neck boxes', 'rigid gift boxes', 'velvet jewelry inserts', 'gold foil boxes' ),
			'specs'            => array(
				'Feature' => 'Three-piece rigid shoulder-and-neck construction with gold reveal and velvet-lined insert',
				'Industrial Use' => 'Rings, earrings, pendants, cufflinks, small watches, keepsakes and luxury gifts',
				'Paper Type' => 'Rigid greyboard with custom specialty-paper wrap; final grade confirmed by sample',
				'Box Type' => 'Shoulder neck rigid jewelry box with removable lid',
				'Shape' => 'Compact square shoulder box, customized to the jewelry item',
				'Accessories' => 'Raised inner neck and custom velvet-lined jewelry insert',
				'Liner Type' => 'Black velvet-style sample lining; product-compatible material confirmed by project',
				'Printing Handling' => 'Gold foil stamping, embossing or debossing, Pantone/CMYK and custom finishes',
				'Color' => 'Navy blue, gold and black sample; custom colors accepted',
				'Model Number' => 'VPN-RB-SN-NAVY',
			),
			'images'           => array(
				vpn_aurelia_rigid_boxes_202609_image( 'custom-navy-shoulder-neck-jewelry-box-closed.webp', 'Custom navy shoulder neck jewelry box shown closed with exposed gold neck', 'Navy Shoulder Neck Jewelry Box - Closed', 'Closed navy shoulder box with an exposed gold neck and foil brand mark.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-navy-shoulder-neck-jewelry-box-gold-foil-perspective.webp', 'Navy rigid shoulder jewelry box perspective with gold foil logo and neck reveal', 'Navy Shoulder Jewelry Box - Gold Foil Perspective', 'Perspective view highlighting the navy wrap, foil artwork and gold structural reveal.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-navy-shoulder-neck-jewelry-box-open-velvet-insert.webp', 'Open navy shoulder neck jewelry box with black velvet fitted insert', 'Navy Shoulder Neck Jewelry Box - Open Insert', 'Open three-piece rigid box showing the raised gold neck and black velvet tray.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-navy-shoulder-neck-jewelry-box-top-view.webp', 'Top view of navy rigid jewelry gift box with gold foil branding', 'Navy Rigid Jewelry Box - Top View', 'Top view of the compact navy lid and centered metallic gold logo.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-navy-shoulder-neck-jewelry-box-front-gold-neck.webp', 'Front view of navy shoulder box showing even gold neck reveal', 'Navy Shoulder Box - Gold Neck Front View', 'Front profile showing the planned gold reveal between the navy lid and base.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-navy-shoulder-neck-jewelry-box-gold-foil-detail.webp', 'Close-up of navy jewelry box textured paper and gold foil logo detail', 'Navy Jewelry Box - Gold Foil Detail', 'Macro view of the textured navy wrap and precise metallic gold branding.' ),
				vpn_aurelia_rigid_boxes_202609_image( 'custom-navy-shoulder-neck-jewelry-box-stacked-display.webp', 'Stacked navy shoulder neck gift boxes for custom commercial packaging', 'Navy Shoulder Neck Boxes - Stacked Display', 'Stacked presentation concept showing consistent navy panels and gold neck reveals.' ),
			),
			'faqs'             => array(
				array( 'What is the neck in a shoulder jewelry box?', 'The neck is a raised inner rigid tray fixed inside the base. It guides the removable lid and can remain visible as a contrasting band when the box is closed.' ),
				array( 'Can the insert be customized for rings or other jewelry?', 'Yes. Ring slots, earring cards, pendant tabs, watch pillows and other retention details are developed around the real item and preferred presentation.' ),
				array( 'Can we change the navy and gold colors?', 'Yes. The photographs show one sample direction. Exterior wrap, neck color, foil, interior and printed artwork can be matched to an approved project brief.' ),
				array( 'What is the minimum order quantity?', 'The standard project reference is 1,000 boxes. Final MOQ is stated in the quote after size, materials, inserts, finish and variant quantities are reviewed.' ),
				array( 'How is lid fit approved?', 'A structural sample is used to check neck alignment, lid clearance, reveal height and insert fit before the decorated production standard is approved.' ),
				array( 'What information should a jewelry brand provide?', 'Send item dimensions and weight, orientation, removal method, target quantity, artwork, color and finish references, destination and packing requirements.' ),
			),
		),
	);
}

function vpn_aurelia_rigid_boxes_202609_spec_rows( $definition ) {
	$defaults = array(
		'Place of Origin' => 'Vietnam',
		'Brand Name' => 'VPN',
		'Province' => 'Ho Chi Minh City',
		'Custom Order' => 'Accept',
		'Logo Printing' => 'Custom logo',
		'Size' => 'Customized size',
		'Thickness' => 'Customized thickness',
		'Single Piece Price' => 'Request a quote',
		'Minimum Order Quantity (MOQ)' => '1000 boxes',
		'Product Name' => $definition['title'],
		'Design' => "Customer's Specific Requirement",
	);
	$order = array( 'Feature', 'Industrial Use', 'Paper Type', 'Box Type', 'Shape', 'Place of Origin', 'Model Number', 'Brand Name', 'Province', 'Accessories', 'Custom Order', 'Liner Type', 'Logo Printing', 'Printing Handling', 'Color', 'Size', 'Thickness', 'Single Piece Price', 'Minimum Order Quantity (MOQ)', 'Product Name', 'Design' );
	$values = array_merge( $defaults, $definition['specs'] );
	$rows = array();
	foreach ( $order as $label ) {
		$rows[] = array( 'label' => $label, 'value' => $values[ $label ] );
	}
	return $rows;
}

function vpn_aurelia_rigid_boxes_202609_exact_attachment_id( $base ) {
	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC", '%' . $wpdb->esc_like( $base ) . '%' ) );
	foreach ( $ids as $id ) {
		$attached = (string) get_post_meta( (int) $id, '_wp_attached_file', true );
		if ( $base === pathinfo( wp_basename( $attached ), PATHINFO_FILENAME ) ) {
			return (int) $id;
		}
	}
	return 0;
}

function vpn_aurelia_rigid_boxes_202609_attachment( $image, $product_id ) {
	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		throw new RuntimeException( 'Upload directory error: ' . $uploads['error'] );
	}
	$relative = '2026/09/' . $image['file'];
	$upload_path = trailingslashit( $uploads['basedir'] ) . $relative;
	$bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;
	$base = pathinfo( $image['file'], PATHINFO_FILENAME );

	if ( ! file_exists( $bundle_path ) ) {
		throw new RuntimeException( 'Bundled image is missing: ' . $image['file'] );
	}
	if ( filesize( $bundle_path ) >= 100000 ) {
		throw new RuntimeException( 'Bundled image is not under 100 KB: ' . $image['file'] );
	}
	if ( ! file_exists( $upload_path ) ) {
		if ( ! wp_mkdir_p( dirname( $upload_path ) ) || ! copy( $bundle_path, $upload_path ) ) {
			throw new RuntimeException( 'Could not copy bundled image: ' . $image['file'] );
		}
	} elseif ( hash_file( 'sha256', $upload_path ) !== hash_file( 'sha256', $bundle_path ) ) {
		throw new RuntimeException( 'Refusing to overwrite a different uploads file: ' . $relative );
	}

	$attachment_id = vpn_aurelia_rigid_boxes_202609_exact_attachment_id( $base );
	if ( ! $attachment_id ) {
		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/webp',
				'post_title' => $image['title'],
				'post_excerpt' => $image['caption'],
				'post_status' => 'inherit',
				'post_parent' => $product_id,
			),
			$upload_path,
			$product_id,
			true
		);
		if ( is_wp_error( $attachment_id ) ) {
			throw new RuntimeException( 'Could not create attachment ' . $image['file'] . ': ' . $attachment_id->get_error_message() );
		}
	}

	update_post_meta( $attachment_id, '_wp_attached_file', $relative );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $image['alt'] );
	wp_update_post( array( 'ID' => $attachment_id, 'post_title' => $image['title'], 'post_excerpt' => $image['caption'], 'post_parent' => $product_id ) );

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	$metadata = wp_get_attachment_metadata( $attachment_id );
	if ( empty( $metadata['width'] ) || empty( $metadata['height'] ) ) {
		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload_path );
		if ( $metadata ) {
			wp_update_attachment_metadata( $attachment_id, $metadata );
		}
	}
	return (int) $attachment_id;
}

function vpn_aurelia_rigid_boxes_202609_term_ids( $taxonomy, $slugs ) {
	$ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', sanitize_title( $slug ), $taxonomy );
		if ( ! $term || is_wp_error( $term ) ) {
			if ( 'product_cat' === $taxonomy ) {
				throw new RuntimeException( 'Required product category is missing: ' . $slug );
			}
			$created = wp_insert_term( ucwords( str_replace( '-', ' ', sanitize_title( $slug ) ) ), $taxonomy, array( 'slug' => sanitize_title( $slug ) ) );
			if ( is_wp_error( $created ) ) {
				throw new RuntimeException( 'Could not create term ' . $slug . ': ' . $created->get_error_message() );
			}
			$ids[] = (int) $created['term_id'];
		} else {
			$ids[] = (int) $term->term_id;
		}
	}
	return $ids;
}

function vpn_aurelia_rigid_boxes_202609_faq_html( $items ) {
	$html = '<section class="product-faq aurelia-rigid-box-faq" itemscope itemtype="https://schema.org/FAQPage"><div class="container"><h2>Frequently Asked Questions</h2>';
	foreach ( $items as $item ) {
		$html .= '<details class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"><summary itemprop="name">' . esc_html( $item[0] ) . '</summary><div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"><p itemprop="text">' . esc_html( $item[1] ) . '</p></div></details>';
	}
	return $html . '</div></section>';
}

function vpn_aurelia_rigid_boxes_202609_figure( $attachment_id, $image, $slot ) {
	$tag = wp_get_attachment_image( $attachment_id, 'large', false, array( 'alt' => $image['alt'], 'loading' => 'lazy', 'decoding' => 'async' ) );
	if ( ! $tag ) {
		throw new RuntimeException( 'Could not render inline image: ' . $image['file'] );
	}
	return '<!-- stable-product-image:slot_' . (int) $slot . ' --><figure class="product-inline-figure product-inline-figure-small">' . $tag . '<figcaption>' . esc_html( $image['caption'] ) . '</figcaption></figure>';
}

function vpn_aurelia_rigid_boxes_202609_insert_figures( $content, $attachment_ids, $images ) {
	$chosen = array( 1, 2, 4, 5 );
	$targets = array( 2, 4, 6, 8 );
	$h2 = 0;
	$slot = 0;
	$content = preg_replace_callback(
		'/<h2\b[^>]*>.*?<\/h2>/is',
		function ( $matches ) use ( &$h2, &$slot, $targets, $chosen, $attachment_ids, $images ) {
			++$h2;
			if ( ! in_array( $h2, $targets, true ) || $slot >= 4 ) {
				return $matches[0];
			}
		$image_index = $chosen[ $slot ];
			++$slot;
			return $matches[0] . vpn_aurelia_rigid_boxes_202609_figure( $attachment_ids[ $image_index ], $images[ $image_index ], $slot );
		},
		$content
	);
	if ( 4 !== $slot ) {
		throw new RuntimeException( 'Long content does not contain enough H2 sections for four figures.' );
	}
	return $content;
}

function vpn_aurelia_rigid_boxes_202609_normalize_internal_links( $content ) {
	return preg_replace_callback(
		'/href="\/([^"#][^"]*)"/i',
		static function ( $matches ) {
			return 'href="' . esc_url( home_url( '/' . ltrim( $matches[1], '/' ) ) ) . '"';
		},
		$content
	);
}

function vpn_aurelia_rigid_boxes_202609_find_product( $definition ) {
	$product = get_page_by_path( $definition['slug'], OBJECT, 'product' );
	if ( $product instanceof WP_Post ) {
		return $product;
	}
	$marked = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => '_vpn_aurelia_rigid_boxes_202609_slug', 'meta_value' => $definition['slug'] ) );
	return $marked ? $marked[0] : null;
}

function vpn_aurelia_rigid_boxes_202609_import_one( $definition ) {
	$content_path = get_template_directory() . '/inc/product-content/aurelia-rigid-boxes-202609/' . $definition['content_file'];
	$content = file_exists( $content_path ) ? file_get_contents( $content_path ) : false;
	if ( false === $content || preg_match( '/<h1\b/i', $content ) ) {
		throw new RuntimeException( 'Missing or invalid product content: ' . $definition['content_file'] );
	}
	$product = vpn_aurelia_rigid_boxes_202609_find_product( $definition );
	if ( ! $product ) {
		$conflict = get_posts( array( 'name' => $definition['slug'], 'post_type' => 'any', 'post_status' => 'any', 'posts_per_page' => 1 ) );
		if ( $conflict ) {
			throw new RuntimeException( 'Slug is already used by another object: ' . $definition['slug'] );
		}
		$product_id = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'draft', 'post_title' => $definition['title'], 'post_name' => $definition['slug'] ), true );
		if ( is_wp_error( $product_id ) ) {
			throw new RuntimeException( 'Could not create product: ' . $product_id->get_error_message() );
		}
	} else {
		$product_id = (int) $product->ID;
	}

	$attachment_ids = array();
	foreach ( $definition['images'] as $image ) {
		$attachment_ids[] = vpn_aurelia_rigid_boxes_202609_attachment( $image, $product_id );
	}
	$content = vpn_aurelia_rigid_boxes_202609_normalize_internal_links( $content );
	$content = vpn_aurelia_rigid_boxes_202609_insert_figures( $content, $attachment_ids, $definition['images'] );
	$updated = wp_update_post(
		array(
			'ID' => $product_id,
			'post_type' => 'product',
			'post_status' => 'publish',
			'post_title' => $definition['title'],
			'post_name' => $definition['slug'],
			'post_excerpt' => $definition['excerpt'],
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $updated ) || $definition['slug'] !== get_post_field( 'post_name', $product_id ) ) {
		throw new RuntimeException( 'Could not save the requested product slug: ' . $definition['slug'] );
	}

	$category_ids = vpn_aurelia_rigid_boxes_202609_term_ids( 'product_cat', $definition['categories'] );
	wp_set_object_terms( $product_id, $category_ids, 'product_cat', false );
	wp_set_object_terms( $product_id, vpn_aurelia_rigid_boxes_202609_term_ids( 'product_tag', $definition['tags'] ), 'product_tag', false );
	wp_set_object_terms( $product_id, array( 'simple' ), 'product_type', false );
	set_post_thumbnail( $product_id, $attachment_ids[0] );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $attachment_ids, 1 ) ) );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_aurelia_rigid_boxes_202609_spec_rows( $definition ) );
	update_post_meta( $product_id, '_custom_box_product_faq_html', vpn_aurelia_rigid_boxes_202609_faq_html( $definition['faqs'] ) );
	update_post_meta( $product_id, '_vpn_sample_import', VPN_AURELIA_RIGID_BOXES_202609_MARKER );
	update_post_meta( $product_id, '_vpn_aurelia_rigid_boxes_202609_slug', $definition['slug'] );
	update_post_meta( $product_id, 'rank_math_title', $definition['seo_title'] );
	update_post_meta( $product_id, 'rank_math_description', $definition['seo_description'] );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $definition['focus_keyword'] );
	$primary_category = get_term_by( 'slug', $definition['primary_category'], 'product_cat' );
	if ( ! $primary_category || is_wp_error( $primary_category ) ) {
		throw new RuntimeException( 'Primary category is missing: ' . $definition['primary_category'] );
	}
	update_post_meta( $product_id, 'rank_math_primary_product_cat', (int) $primary_category->term_id );
	update_post_meta( $product_id, 'rank_math_canonical_url', get_permalink( $product_id ) );
	update_post_meta( $product_id, 'rank_math_robots', array( 'index', 'follow' ) );
	foreach ( array( 'facebook', 'twitter' ) as $network ) {
		update_post_meta( $product_id, 'rank_math_' . $network . '_title', $definition['seo_title'] );
		update_post_meta( $product_id, 'rank_math_' . $network . '_description', $definition['seo_description'] );
		update_post_meta( $product_id, 'rank_math_' . $network . '_image_id', $attachment_ids[0] );
		update_post_meta( $product_id, 'rank_math_' . $network . '_image', wp_get_attachment_url( $attachment_ids[0] ) );
	}
	update_post_meta( $product_id, 'rank_math_twitter_card_type', 'summary_large_image' );

	return array( 'id' => $product_id, 'status' => get_post_status( $product_id ), 'slug' => $definition['slug'], 'url' => get_permalink( $product_id ), 'images' => count( $attachment_ids ) );
}

function vpn_aurelia_rigid_boxes_202609_run_import() {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}
	$results = array();
	foreach ( vpn_aurelia_rigid_boxes_202609_definitions() as $definition ) {
		$results[] = vpn_aurelia_rigid_boxes_202609_import_one( $definition );
	}
	return $results;
}

$vpn_aurelia_rigid_boxes_results = vpn_aurelia_rigid_boxes_202609_run_import();
echo wp_json_encode( $vpn_aurelia_rigid_boxes_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
