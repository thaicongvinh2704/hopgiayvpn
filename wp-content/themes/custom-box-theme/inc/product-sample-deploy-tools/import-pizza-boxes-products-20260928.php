<?php
/**
 * Import and publish five concept-based pizza box product pages in WooCommerce.
 * Source descriptions and original WebP artwork are bundled with the theme so
 * the reviewed batch can be reproduced on a deployment target.
 *
 * Run from the WordPress root with:
 *   php wp-content/themes/custom-box-theme/inc/product-sample-deploy-tools/import-pizza-boxes-products-20260928.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__, 5 ) . '/wp-load.php';
}

defined( 'ABSPATH' ) || exit;

const VPN_PIZZA_BOXES_20260928_MARKER = 'product-samples-pizza-boxes-20260928';
const VPN_PIZZA_BOXES_20260928_CONTENT_DIR = 'inc/product-content/pizza-boxes-20260928';
const VPN_PIZZA_BOXES_20260928_BUNDLE_DIR = 'inc/product-sample-deploy-assets/uploads/2026/09';
const VPN_PIZZA_BOXES_20260928_UPLOAD_DIR = '2026/09';

function vpn_pizza_boxes_20260928_products(): array {
	return array(
		array(
			'title'       => 'Kraft Pizza Delivery Box',
			'slug'        => 'kraft-pizza-delivery-box',
			'keyword'     => 'kraft pizza delivery box',
			'seo_title'   => 'Kraft Pizza Delivery Box | VPN PAPER BOX MANUFACTURER',
			'meta'        => 'Custom kraft pizza delivery boxes with tailored fit, closure and print. Specify board, food contact and delivery requirements.',
			'content'     => 'kraft-pizza-delivery-box.html',
			'short'       => 'The Kraft Pizza Delivery Box is a made-to-order carton concept for pizzerias, restaurants and delivery kitchens that need a branded pack sized around the finished pizza and the route to the customer. Choose the internal dimensions, board construction, closure and print layout after sharing pizza measurements, topping height, stack plan and delivery-bag limits. The supplied images show a kraft appearance and placeholder “CUSTOM LOGO” artwork; they are concept visualizations, not verified production samples. A paper grade, barrier, vent pattern or food-contact status is not established by the images. Buyers should approve the dieline, proposed materials, printed proof and a filled-box trial for the intended market before production. Custom orders are accepted, with a listed minimum quantity of 1,000 boxes. Pricing depends on the final size, material, print, finish, quantity and destination.',
			'categories'  => array( 'food-paper-boxes', 'custom-printed-paper-boxes', 'folding-carton-boxes', 'pizza-boxes' ),
			'feature'     => 'Kraft-look pizza carton concept with custom fit, hinged lid and printed top panel',
			'industrial'  => 'Pizzerias, restaurants, delivery kitchens and food-service operators',
			'paper'       => 'Paperboard or corrugated option; grade, barrier and food-contact details to be confirmed for the application',
			'box_type'    => 'Custom hinged-lid pizza delivery carton',
			'shape'       => 'Square or rectangular format based on the finished pizza dimensions',
			'model'       => 'VPN-PIZZA-KRAFT-DELIVERY',
			'accessories' => 'Optional liner, separator or order label when specified and approved',
			'liner'       => 'Selected for intended use and destination requirements; not established by the concept image',
			'printing'    => 'Custom logo and artwork on an approved dieline; process and finish confirmed by sample',
			'color'       => 'Natural kraft appearance; print colors subject to approved material and proof',
			'hero'        => array( 'Custom internal dimensions based on the finished pizza', 'Kraft-look concept with printed lid and hinged opening', 'Material and food-contact details require project approval', 'Minimum order quantity: 1,000 boxes' ),
			'link_texts'  => array( 'food_category' => 'custom food paper boxes', 'print_category' => 'custom printed paper box range', 'guide' => 'food paper packaging material guide', 'pizza' => 'custom pizza packaging box', 'delivery' => 'fold-flat pizza delivery box', 'contact' => 'request a custom pizza-box quotation' ),
		),
		array(
			'title'       => 'Personal Kraft Pizza Box',
			'slug'        => 'personal-kraft-pizza-box',
			'keyword'     => 'personal kraft pizza box',
			'seo_title'   => 'Personal Kraft Pizza Box | VPN PAPER BOX MANUFACTURER',
			'meta'        => 'Custom personal kraft pizza boxes sized for individual menu items, with print, material and fit confirmed by sample.',
			'content'     => 'personal-kraft-pizza-box.html',
			'short'       => 'The Personal Kraft Pizza Box is a custom packaging concept for individual-format pizzas, mini pies and single-order pizza programs. Its compact footprint should be developed from the actual baked item, topping height, liner and service method rather than an assumed standard size. The six supplied views show a kraft appearance, hinged opening and placeholder “CUSTOM LOGO” artwork. They are concept visualizations, not verified production samples, and do not establish paper grade, coating, barrier or food-contact status. Share the menu range, required size split, storage plan and delivery setup so VPN can prepare a dieline and sample for review. Buyers can specify custom artwork, paperboard or corrugated construction and optional accessories after intended use is clear. The listed minimum order quantity is 1,000 boxes; the written quotation confirms final specifications, quantity and price.',
			'categories'  => array( 'food-paper-boxes', 'custom-printed-paper-boxes', 'folding-carton-boxes', 'pizza-boxes' ),
			'feature'     => 'Individual-format kraft-look pizza carton concept sized to the measured menu item',
			'industrial'  => 'Pizzerias, cafes, food halls, takeaway counters and individual meal programs',
			'paper'       => 'Paperboard or corrugated option selected after product-fit and route review',
			'box_type'    => 'Custom personal-format pizza carton with hinged lid',
			'shape'       => 'Square or rectangular footprint customized to the item and service setup',
			'model'       => 'VPN-PIZZA-KRAFT-PERSONAL',
			'accessories' => 'Optional liner, separator, product label or carrier coordination by project',
			'liner'       => 'To be selected for the actual product, contact arrangement and destination',
			'printing'    => 'Custom logo and menu identification arranged on the approved dieline',
			'color'       => 'Kraft-look appearance with customized print after material sampling',
			'hero'        => array( 'Fit based on measured individual menu items', 'Optional size and artwork versions by approved specification', 'Concept imagery uses placeholder “CUSTOM LOGO” artwork', 'Minimum order quantity: 1,000 boxes' ),
			'link_texts'  => array( 'food_category' => 'food paper boxes for takeaway service', 'print_category' => 'custom printed packaging boxes', 'guide' => 'food packaging material and design guide', 'pizza' => 'custom pizza packaging box', 'delivery' => 'custom fold-flat pizza delivery carton', 'contact' => 'send an individual-pizza packaging brief' ),
		),
		array(
			'title'       => 'Rectangular Flatbread Pizza Box',
			'slug'        => 'rectangular-flatbread-pizza-box',
			'keyword'     => 'rectangular flatbread pizza box',
			'seo_title'   => 'Rectangular Flatbread Pizza Box | VPN PAPER BOX MANUFACTURER',
			'meta'        => 'Custom rectangular flatbread pizza boxes designed around product length, width, serving method, print and delivery fit.',
			'content'     => 'rectangular-flatbread-pizza-box.html',
			'short'       => 'The Rectangular Flatbread Pizza Box is a custom carton concept for elongated pizzas, flatbreads and similar food-service items. Its internal length, width and depth should be set from the maximum finished product, including irregular edges, toppings and any serving board or liner. The supplied images show a long shallow format with printed artwork, but they do not establish dimensions, board construction, vents or test results. Treat them as concept visualizations, not verified production samples. Share the menu range, portioning method, carrier dimensions, stack plan and destination so the structure can be reviewed around actual handling. Paperboard or corrugated options, print, accessories and food-contact materials require project-specific approval. The listing MOQ is 1,000 boxes; a final quote follows the approved dieline, materials, quantity and delivery details.',
			'categories'  => array( 'food-paper-boxes', 'custom-printed-paper-boxes', 'folding-carton-boxes', 'pizza-boxes' ),
			'feature'     => 'Long shallow carton concept for elongated pizza and flatbread formats',
			'industrial'  => 'Pizzerias, flatbread cafes, bakeries, food halls and catering operators',
			'paper'       => 'Paperboard or corrugated construction selected for product span and intended route',
			'box_type'    => 'Custom rectangular hinged-lid flatbread carton',
			'shape'       => 'Elongated rectangular footprint with customized clear internal dimensions',
			'model'       => 'VPN-PIZZA-RECT-FLATBREAD',
			'accessories' => 'Optional liner, serving board or separator only when specified and approved',
			'liner'       => 'Selected for the intended product contact and destination requirements',
			'printing'    => 'Custom logo and horizontal panel artwork on the approved dieline',
			'color'       => 'Customized print and paper appearance subject to approved material sample',
			'hero'        => array( 'Elongated footprint for measured flatbread products', 'Internal length, width and depth confirmed on the dieline', 'Optional liner or serving board specified separately', 'Minimum order quantity: 1,000 boxes' ),
			'link_texts'  => array( 'food_category' => 'food paper packaging formats', 'print_category' => 'custom printed paper cartons', 'guide' => 'food paper packaging material guide', 'pizza' => 'round-format custom pizza packaging box', 'delivery' => 'custom pizza delivery carton reference', 'contact' => 'request a flatbread-box quotation' ),
		),
		array(
			'title'       => 'White Kraft Pizza Box',
			'slug'        => 'white-kraft-pizza-box',
			'keyword'     => 'white kraft pizza box',
			'seo_title'   => 'White Kraft Pizza Box | VPN PAPER BOX MANUFACTURER',
			'meta'        => 'Custom white-and-kraft pizza boxes with panel colors, fit, tray components and print confirmed in the approved specification.',
			'content'     => 'white-kraft-pizza-box.html',
			'short'       => 'The White Kraft Pizza Box is a custom appearance concept combining a light-colored printed lid with kraft-look surfaces or an optional tray direction. The supplied feature callout does not confirm whether a tray is a separate component, integrated base or visual reference, so the final bill of materials must specify it. The images are concept visualizations, not verified production samples, and do not establish board grade, coating, dimensions, color tolerance or food-contact status. Buyers should map white and kraft panels on the dieline, share product measurements and approve physical material and print samples. Paper or corrugated construction, liners and other accessories depend on the intended menu and destination. A minimum quantity of 1,000 boxes is listed; price and final scope depend on dimensions, component count, artwork, quantity and delivery requirements.',
			'categories'  => array( 'food-paper-boxes', 'custom-printed-paper-boxes', 'folding-carton-boxes', 'pizza-boxes' ),
			'feature'     => 'White-and-kraft visual direction with custom panel mapping and optional tray specification',
			'industrial'  => 'Pizzerias, restaurants, takeaway counters and food-service brand programs',
			'paper'       => 'White-faced paperboard and kraft-look components; exact construction confirmed by sample',
			'box_type'    => 'Custom hinged-lid pizza carton; tray included only when specified',
			'shape'       => 'Square or rectangular footprint sized to the measured product',
			'model'       => 'VPN-PIZZA-WHITE-KRAFT',
			'accessories' => 'Optional separate tray, liner, separator or order label as stated in the specification',
			'liner'       => 'Material and contact status to be confirmed for the intended application',
			'printing'    => 'Custom artwork coordinated across white and kraft-look surfaces after proof approval',
			'color'       => 'White and natural kraft appearance by panel; print subject to approved physical sample',
			'hero'        => array( 'White-and-kraft panel combination defined on the dieline', 'Tray or liner included only when stated in the quote', 'Approve stock and print together under project lighting', 'Minimum order quantity: 1,000 boxes' ),
			'link_texts'  => array( 'food_category' => 'custom food paper packaging', 'print_category' => 'custom printed paper box styles', 'guide' => 'food packaging material selection guide', 'pizza' => 'custom pizza packaging box', 'delivery' => 'pizza delivery box with full-color print', 'contact' => 'request a white-and-kraft pizza box quote' ),
		),
		array(
			'title'       => 'White Printed Pizza Box',
			'slug'        => 'white-printed-pizza-box',
			'keyword'     => 'white printed pizza box',
			'seo_title'   => 'White Printed Pizza Box | VPN PAPER BOX MANUFACTURER',
			'meta'        => 'Custom white printed pizza boxes with artwork, fit, board and finish reviewed on an approved dieline and sample.',
			'content'     => 'white-printed-pizza-box.html',
			'short'       => 'The White Printed Pizza Box is a custom carton concept for pizza brands that want a light-colored printed surface for logos, menu identity or campaign artwork. The six supplied views show a white exterior, printed lid and front closure. They do not identify paper grade, print method, finish, size or production status. Use them to discuss the design direction, then share the pizza dimensions, dieline requirements, artwork files, delivery setup and destination market. Print colors and surface behavior must be reviewed on the proposed material and physical sample. Any liner, vent, barrier or food-contact requirement should be specified separately for the actual application. The listing MOQ is 1,000 boxes; the final quotation depends on size, board, print coverage, finish, quantity by version, accessories and shipping details.',
			'categories'  => array( 'food-paper-boxes', 'custom-printed-paper-boxes', 'folding-carton-boxes', 'pizza-boxes' ),
			'feature'     => 'White-surface pizza carton concept for custom printed brand and menu artwork',
			'industrial'  => 'Pizzerias, restaurant groups, delivery kitchens and food-service campaigns',
			'paper'       => 'White-surface paperboard or corrugated option confirmed for the selected structure',
			'box_type'    => 'Custom printed hinged-lid pizza carton',
			'shape'       => 'Square or rectangular format customized to internal fit and service needs',
			'model'       => 'VPN-PIZZA-WHITE-PRINTED',
			'accessories' => 'Optional liner, separator, vent or order label only when specified and approved',
			'liner'       => 'Selected for the actual product contact, operating conditions and destination',
			'printing'    => 'Custom logo, menu and campaign artwork based on approved dieline and proof',
			'color'       => 'White surface with customized print subject to physical color approval',
			'hero'        => array( 'Printed panel layout built on the production dieline', 'Review color and finish on the selected stock', 'Custom fit and optional components confirmed by sample', 'Minimum order quantity: 1,000 boxes' ),
			'link_texts'  => array( 'food_category' => 'food paper box options', 'print_category' => 'custom printed paper packaging', 'guide' => 'food paper box material planning guide', 'pizza' => 'custom pizza packaging box', 'delivery' => 'fold-flat printed pizza delivery carton', 'contact' => 'share artwork for a printed-box quotation' ),
		),
	);
}

function vpn_pizza_boxes_20260928_source_path( array $product ): string {
	return trailingslashit( get_template_directory() )
		. VPN_PIZZA_BOXES_20260928_CONTENT_DIR . '/' . $product['content'];
}

function vpn_pizza_boxes_20260928_bundle_path( string $filename ): string {
	return trailingslashit( get_template_directory() )
		. VPN_PIZZA_BOXES_20260928_BUNDLE_DIR . '/' . wp_basename( $filename );
}

function vpn_pizza_boxes_20260928_category_id( string $slug ): int {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $term || is_wp_error( $term ) ) {
		throw new RuntimeException( 'Required product category does not exist: ' . $slug );
	}
	return (int) $term->term_id;
}

function vpn_pizza_boxes_20260928_published_url( string $slug, string $post_type ): string {
	$post = get_page_by_path( $slug, OBJECT, $post_type );
	if ( ! $post || 'publish' !== $post->post_status ) {
		// Older catalogue entries and guides are optional on another installation.
		// Render their references as plain text until those entries are published.
		echo 'Optional link target unavailable, omitted: ' . $slug . PHP_EOL;
		return '';
	}
	return get_permalink( $post );
}

function vpn_pizza_boxes_20260928_canonical_url( string $slug ): string {
	$structure = function_exists( 'wc_get_permalink_structure' ) ? wc_get_permalink_structure() : array();
	$base      = $structure['product_rewrite_slug'] ?? $structure['product_base'] ?? 'product';
	$base      = trim( (string) $base, '/' );
	return home_url( user_trailingslashit( $base . '/' . $slug ) );
}

function vpn_pizza_boxes_20260928_link( string $url, string $anchor ): string {
	if ( '' === $url ) {
		return esc_html( $anchor );
	}
	return '<a href="' . esc_url( $url ) . '">' . esc_html( $anchor ) . '</a>';
}

function vpn_pizza_boxes_20260928_attachment( string $filename, int $parent_id, string $alt, string $title ): int {
	global $wpdb;

	$filename = wp_basename( $filename );
	$base     = pathinfo( $filename, PATHINFO_FILENAME );
	$ids      = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
			'%' . $wpdb->esc_like( $filename )
		)
	);
	foreach ( $ids as $id ) {
		$attached = (string) get_post_meta( (int) $id, '_wp_attached_file', true );
		if ( $filename !== wp_basename( $attached ) ) {
			continue;
		}
		update_post_meta( (int) $id, '_wp_attachment_image_alt', $alt );
		wp_update_post( array( 'ID' => (int) $id, 'post_title' => $title, 'post_parent' => $parent_id ) );
		return (int) $id;
	}

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		throw new RuntimeException( 'WordPress uploads directory error: ' . $uploads['error'] );
	}
	$relative = VPN_PIZZA_BOXES_20260928_UPLOAD_DIR . '/' . $filename;
	$path     = trailingslashit( $uploads['basedir'] ) . $relative;
	$bundle   = vpn_pizza_boxes_20260928_bundle_path( $filename );
	if ( ! file_exists( $bundle ) ) {
		throw new RuntimeException( 'Bundled source image is missing: ' . $filename );
	}
	if ( file_exists( $path ) ) {
		if ( hash_file( 'sha256', $path ) !== hash_file( 'sha256', $bundle ) ) {
			throw new RuntimeException( 'An unrelated uploads file already uses this image filename: ' . $filename );
		}
	} else {
		if ( ! wp_mkdir_p( dirname( $path ) ) || ! copy( $bundle, $path ) ) {
			throw new RuntimeException( 'Unable to copy bundled image into uploads: ' . $filename );
		}
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$filetype      = wp_check_filetype( $filename, null );
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'] ?: 'image/webp',
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_parent'    => $parent_id,
		),
		$path,
		$parent_id,
		true
	);
	if ( is_wp_error( $attachment_id ) ) {
		throw new RuntimeException( $attachment_id->get_error_message() );
	}
	$metadata = wp_generate_attachment_metadata( (int) $attachment_id, $path );
	if ( $metadata ) {
		wp_update_attachment_metadata( (int) $attachment_id, $metadata );
	}
	update_post_meta( (int) $attachment_id, '_wp_attached_file', $relative );
	update_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', $alt );
	return (int) $attachment_id;
}

function vpn_pizza_boxes_20260928_figure( int $attachment_id, string $alt, string $title, int $slot ): string {
	$image = wp_get_attachment_image(
		$attachment_id,
		'large',
		false,
		array( 'alt' => $alt, 'loading' => 'lazy', 'decoding' => 'async' )
	);
	if ( ! $image ) {
		throw new RuntimeException( 'Unable to render inline image for ' . $title );
	}
	return '<!-- stable-product-image:slot_' . $slot . ' -->'
		. '<figure class="product-inline-figure product-inline-figure-small">'
		. $image
		. '<figcaption>' . esc_html( $title . ': concept visualization only; not a verified production sample.' ) . '</figcaption>'
		. '</figure>';
}

function vpn_pizza_boxes_20260928_specs( array $product ): array {
	return array(
		array( 'label' => 'Feature', 'value' => $product['feature'] ),
		array( 'label' => 'Industrial Use', 'value' => $product['industrial'] ),
		array( 'label' => 'Paper Type', 'value' => $product['paper'] ),
		array( 'label' => 'Box Type', 'value' => $product['box_type'] ),
		array( 'label' => 'Shape', 'value' => $product['shape'] ),
		array( 'label' => 'Place of Origin', 'value' => 'Vietnam' ),
		array( 'label' => 'Model Number', 'value' => $product['model'] ),
		array( 'label' => 'Brand Name', 'value' => 'VPN' ),
		array( 'label' => 'Province', 'value' => 'Ho Chi Minh City' ),
		array( 'label' => 'Accessories', 'value' => $product['accessories'] ),
		array( 'label' => 'Custom Order', 'value' => 'Accept' ),
		array( 'label' => 'Liner Type', 'value' => $product['liner'] ),
		array( 'label' => 'Logo Printing', 'value' => 'Custom logo' ),
		array( 'label' => 'Printing Handling', 'value' => $product['printing'] ),
		array( 'label' => 'Color', 'value' => $product['color'] ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Quote after approved size, materials, print, quantity and delivery details' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes' ),
		array( 'label' => 'Product Name', 'value' => $product['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement; supplied images are concept visualizations, not verified production samples" ),
	);
}

function vpn_pizza_boxes_20260928_product_post( array $product ): int {
	$existing = get_page_by_path( $product['slug'], OBJECT, 'product' );
	$product_id = 0;
	if ( $existing instanceof WP_Post ) {
		$marker_slug = (string) get_post_meta( $existing->ID, '_vpn_pizza_boxes_20260928_slug', true );
		if ( $marker_slug !== $product['slug'] ) {
			throw new RuntimeException( 'Product slug is already in use by another record: ' . $product['slug'] );
		}
		$product_id = (int) $existing->ID;
	} else {
		$product_id = wp_insert_post(
			array(
				'post_type'   => 'product',
				'post_status' => 'draft',
				'post_title'  => $product['title'],
				'post_name'   => $product['slug'],
			),
			true
		);
		if ( is_wp_error( $product_id ) ) {
			throw new RuntimeException( $product_id->get_error_message() );
		}
		$product_id = (int) $product_id;
		// Record ownership before image/content work so an interrupted import can resume.
		update_post_meta( $product_id, '_vpn_pizza_boxes_20260928_slug', $product['slug'] );
	}
	return $product_id;
}

function vpn_pizza_boxes_20260928_word_count( string $html ): int {
	$plain = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return preg_match_all( "/[\\p{L}\\p{N}][\\p{L}\\p{N}'-]*/u", $plain, $matches );
}

function vpn_pizza_boxes_20260928_import_product( array $product, array $category_ids, string $guide_url, string $custom_pizza_url, string $fold_flat_url, string $contact_url ): array {
	$source_path = vpn_pizza_boxes_20260928_source_path( $product );
	if ( ! file_exists( $source_path ) ) {
		throw new RuntimeException( 'Product content file is missing: ' . $source_path );
	}
	$content = file_get_contents( $source_path );
	if ( false === $content ) {
		throw new RuntimeException( 'Unable to read product content: ' . $source_path );
	}
	if ( preg_match( '/<h1\b/i', $content ) ) {
		throw new RuntimeException( 'Product long description must not contain an H1: ' . $product['slug'] );
	}

	$product_id = vpn_pizza_boxes_20260928_product_post( $product );
	$prefix     = $product['slug'];
	$suffixes   = array( '01-closed-hero', '02-open-interior', '03-printed-detail', '04-side-profile', '05-top-view', '06-feature-callouts' );
	$alt_labels = array(
		'closed printed lid and hinged front',
		'open interior and hinged lid',
		'printed panel and apparent paper surface',
		'side profile and shallow box form',
		'top artwork and panel layout',
		'visual feature callouts for design discussion',
	);
	$image_ids = array();
	$image_alts = array();
	foreach ( $suffixes as $index => $suffix ) {
		$filename = $prefix . '-' . $suffix . '.webp';
		$alt = $product['title'] . ' for food-service use, ' . $alt_labels[ $index ] . '; concept visualization.';
		$title = $product['title'] . ' — ' . $alt_labels[ $index ] . ' (concept visualization)';
		$image_ids[] = vpn_pizza_boxes_20260928_attachment( $filename, $product_id, $alt, $title );
		$image_alts[] = $alt;
	}

	$figure_tokens = array(
		'{{FIGURE_02}}' => vpn_pizza_boxes_20260928_figure( $image_ids[1], $image_alts[1], $product['title'] . ' open interior and hinged lid', 1 ),
		'{{FIGURE_03}}' => vpn_pizza_boxes_20260928_figure( $image_ids[2], $image_alts[2], $product['title'] . ' printed panel detail', 2 ),
		'{{FIGURE_04}}' => vpn_pizza_boxes_20260928_figure( $image_ids[3], $image_alts[3], $product['title'] . ' side profile', 3 ),
		'{{FIGURE_06}}' => vpn_pizza_boxes_20260928_figure( $image_ids[5], $image_alts[5], $product['title'] . ' concept feature callouts', 4 ),
	);
	$content = str_replace( array_keys( $figure_tokens ), array_values( $figure_tokens ), $content );

	$food_category_url  = get_term_link( $category_ids['food-paper-boxes'], 'product_cat' );
	$print_category_url = get_term_link( $category_ids['custom-printed-paper-boxes'], 'product_cat' );
	if ( is_wp_error( $food_category_url ) || is_wp_error( $print_category_url ) ) {
		throw new RuntimeException( 'Unable to resolve pizza product category URLs.' );
	}
	$link_tokens = array(
		'{{CATEGORY_FOOD}}' => vpn_pizza_boxes_20260928_link( $food_category_url, $product['link_texts']['food_category'] ),
		'{{CATEGORY_PRINT}}' => vpn_pizza_boxes_20260928_link( $print_category_url, $product['link_texts']['print_category'] ),
		'{{GUIDE_FOOD}}' => vpn_pizza_boxes_20260928_link( $guide_url, $product['link_texts']['guide'] ),
		'{{EXISTING_CUSTOM_PIZZA}}' => vpn_pizza_boxes_20260928_link( $custom_pizza_url, $product['link_texts']['pizza'] ),
		'{{EXISTING_FOLD_FLAT}}' => vpn_pizza_boxes_20260928_link( $fold_flat_url, $product['link_texts']['delivery'] ),
		'{{CONTACT}}' => vpn_pizza_boxes_20260928_link( $contact_url, $product['link_texts']['contact'] ),
	);
	$content = str_replace( array_keys( $link_tokens ), array_values( $link_tokens ), $content );
	if ( preg_match( '/\{\{[^}]+\}\}/', $content ) ) {
		throw new RuntimeException( 'Unresolved source placeholder in ' . $product['slug'] );
	}

	$short = trim( (string) $product['short'] );
	$long_words = vpn_pizza_boxes_20260928_word_count( $content );
	$short_words = vpn_pizza_boxes_20260928_word_count( $short );
	if ( $long_words < 1500 || $long_words > 2000 ) {
		throw new RuntimeException( 'Long description word count must be 1500-2000 for ' . $product['slug'] . '; found ' . $long_words );
	}
	if ( $short_words < 120 || $short_words > 180 ) {
		throw new RuntimeException( 'Short description word count must be 120-180 for ' . $product['slug'] . '; found ' . $short_words );
	}

	$updated = wp_update_post(
		array(
			'ID'           => $product_id,
			'post_type'    => 'product',
			'post_title'   => $product['title'],
			'post_name'    => $product['slug'],
			'post_excerpt' => '<p>' . esc_html( $short ) . '</p>',
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $updated ) ) {
		throw new RuntimeException( $updated->get_error_message() );
	}
	$assigned_categories = array_map( static function ( $slug ) use ( $category_ids ) { return $category_ids[ $slug ]; }, $product['categories'] );
	wp_set_object_terms( $product_id, $assigned_categories, 'product_cat', false );
	wp_set_object_terms( $product_id, 'simple', 'product_type', false );
	set_post_thumbnail( $product_id, $image_ids[0] );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $image_ids, 1 ) ) );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_pizza_boxes_20260928_specs( $product ) );
	update_post_meta( $product_id, '_custom_box_product_hero_bullets', $product['hero'] );
	update_post_meta( $product_id, '_custom_box_hide_auto_description_heading', '1' );
	update_post_meta( $product_id, '_vpn_sample_import', VPN_PIZZA_BOXES_20260928_MARKER );
	update_post_meta( $product_id, '_vpn_pizza_boxes_20260928_slug', $product['slug'] );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $product['keyword'] );
	update_post_meta( $product_id, 'rank_math_title', $product['seo_title'] );
	update_post_meta( $product_id, 'rank_math_description', $product['meta'] );
	update_post_meta( $product_id, 'rank_math_primary_product_cat', $category_ids['food-paper-boxes'] );
	update_post_meta( $product_id, 'rank_math_canonical_url', vpn_pizza_boxes_20260928_canonical_url( $product['slug'] ) );
	update_post_meta( $product_id, 'rank_math_robots', array( 'index', 'follow' ) );
	$published = wp_update_post(
		array(
			'ID'          => $product_id,
			'post_status' => 'publish',
		),
		true
	);
	if ( is_wp_error( $published ) ) {
		throw new RuntimeException( $published->get_error_message() );
	}
	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $product_id );
	}
	clean_post_cache( $product_id );

	return array(
		'id'      => $product_id,
		'status'  => get_post_status( $product_id ),
		'words'   => vpn_pizza_boxes_20260928_word_count( get_post_field( 'post_content', $product_id ) ),
		'short'   => $short_words,
		'images'  => count( $image_ids ),
		'figures' => substr_count( get_post_field( 'post_content', $product_id ), 'stable-product-image:' ),
		'url'     => get_permalink( $product_id ),
	);
}

// Check the complete bundle before creating any draft products.
foreach ( vpn_pizza_boxes_20260928_products() as $product ) {
	if ( ! is_readable( vpn_pizza_boxes_20260928_source_path( $product ) ) ) {
		throw new RuntimeException( 'Bundled Pizza Boxes content is missing: ' . $product['content'] );
	}
	foreach ( array( '01-closed-hero', '02-open-interior', '03-printed-detail', '04-side-profile', '05-top-view', '06-feature-callouts' ) as $suffix ) {
		$filename = $product['slug'] . '-' . $suffix . '.webp';
		if ( ! is_readable( vpn_pizza_boxes_20260928_bundle_path( $filename ) ) ) {
			throw new RuntimeException( 'Bundled Pizza Boxes image is missing: ' . $filename );
		}
	}
}

$category_ids = array();
if ( function_exists( 'custom_box_sync_pizza_boxes_category' ) ) {
	$category_sync = custom_box_sync_pizza_boxes_category();
	if ( ! empty( $category_sync['error'] ) ) {
		throw new RuntimeException( $category_sync['error'] );
	}
}
foreach ( array( 'food-paper-boxes', 'custom-printed-paper-boxes', 'folding-carton-boxes', 'pizza-boxes' ) as $category_slug ) {
	$category_ids[ $category_slug ] = vpn_pizza_boxes_20260928_category_id( $category_slug );
}
$guide_url       = vpn_pizza_boxes_20260928_published_url( 'how-to-create-premium-food-packaging-with-paper-boxes', 'post' );
$custom_pizza_url = vpn_pizza_boxes_20260928_published_url( 'custom-pizza-packaging-box', 'product' );
$fold_flat_url    = vpn_pizza_boxes_20260928_published_url( 'custom-fold-flat-pizza-delivery-box-full-color-print', 'product' );
$contact_url      = home_url( '/contact/#quote' );

$audit = array();
foreach ( vpn_pizza_boxes_20260928_products() as $product ) {
	$result = vpn_pizza_boxes_20260928_import_product( $product, $category_ids, $guide_url, $custom_pizza_url, $fold_flat_url, $contact_url );
	$audit[] = array_merge( array( 'title' => $product['title'], 'slug' => $product['slug'] ), $result );
	echo 'Imported: ' . $product['title'] . ' (#' . $result['id'] . ')'
		. ' status=' . $result['status'] . ' words=' . $result['words'] . ' short=' . $result['short']
		. ' images=' . $result['images'] . ' inline_figures=' . $result['figures']
		. ' URL=' . $result['url'] . PHP_EOL;
}

if ( function_exists( 'custom_box_sync_pizza_boxes_category' ) ) {
	$category_sync = custom_box_sync_pizza_boxes_category();
	if ( ! empty( $category_sync['error'] ) || empty( $category_sync['complete'] ) ) {
		throw new RuntimeException( $category_sync['error'] ?? 'Pizza Boxes category assignment did not complete.' );
	}
}

echo wp_json_encode( array( 'scope' => 'Pizza Boxes Product Sample Deploy', 'marker' => VPN_PIZZA_BOXES_20260928_MARKER, 'imported' => $audit ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
