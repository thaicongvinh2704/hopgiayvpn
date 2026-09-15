<?php
/**
 * Idempotent importer for the September 2026 rigid paperboard magazine file holder set.
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

if ( ! defined( 'VPN_MAGAZINE_FILE_HOLDER_202609_MARKER' ) ) {
	define( 'VPN_MAGAZINE_FILE_HOLDER_202609_MARKER', 'product-samples-magazine-file-holder-202609' );
}

function vpn_magazine_file_holder_202609_definition() {
	return array(
		'title'            => 'Custom Rigid Paperboard Magazine File Holder Set',
		'slug'             => 'custom-rigid-paperboard-magazine-file-holder-set',
		'content_file'     => 'custom-rigid-paperboard-magazine-file-holder-set.html',
		'focus_keyword'    => 'rigid paperboard magazine file holder',
		'seo_title'        => 'Rigid Paperboard Magazine File Holder | Vietnam Factory',
		'seo_description'  => 'Custom rigid paperboard magazine file holders made in Vietnam with wrapped board, label panels, finger pulls, custom sizes, colors and printing.',
		'excerpt'          => 'This custom rigid paperboard magazine file holder set uses thick wrapped board, an open top, sloped access sides, a writable front label and a metal-rimmed finger pull. The teal, mustard and gray units shown are design references for organizing magazines, catalogs, exercise books, folders and stationery collections on desks, shelves or retail displays. VPN Packaging manufactures made-to-order paperboard products in Ho Chi Minh City, Vietnam, for brands, schools, publishers, office-supply programs and international B2B buyers. Dimensions, board, wrap paper, colors, print, label format, retrieval opening and export packing can be developed for each project. Send the maximum content dimensions, expected filled weight, number of units per set, target quantity, artwork and destination for structural review, sampling and a project-specific quotation. Final materials, MOQ, pricing and schedule are confirmed against the approved specification.',
		'categories'       => array( 'back-to-school-stationery-packaging', 'rigid-boxes', 'home-lifestyle-packaging' ),
		'primary_category' => 'back-to-school-stationery-packaging',
		'tags'             => array( 'magazine file holder', 'paperboard desk organizer', 'stationery storage', 'custom office accessories', 'rigid paperboard products' ),
		'specs'            => array(
			'Feature' => 'Open-top rigid holder with sloped access side, front label area and finger pull',
			'Industrial Use' => 'Magazines, catalogs, workbooks, folders, stationery sets, publishing and office organization',
			'Paper Type' => 'Rigid greyboard wrapped with custom printed, dyed or textured paper; final grade confirmed by sample',
			'Box Type' => 'Upright rigid paperboard magazine file and document holder',
			'Shape' => 'Tall rectangular holder with low front and sloped side profile',
			'Accessories' => 'Writable or printed front label and optional metal-rimmed or reinforced paper finger pull',
			'Liner Type' => 'Custom interior paper wrap or lining selected for the approved specification',
			'Printing Handling' => 'Pantone or CMYK printing, foil, embossing, debossing and custom wrap options',
			'Color' => 'Teal, mustard and gray sample set; custom colors and artwork accepted',
			'Model Number' => 'VPN-ST-MFH-202609',
		),
		'images'           => array(
			array(
				'file' => '01-rigid-paperboard-magazine-file-holder-set-hero.webp',
				'alt' => 'Custom rigid paperboard magazine file holder set in teal mustard and gray',
				'title' => 'Rigid Paperboard Magazine File Holder Set',
				'caption' => 'Three coordinated rigid paperboard magazine file holders with front labels and finger pulls.',
			),
			array(
				'file' => '02-rigid-magazine-file-holders-in-use.webp',
				'alt' => 'Rigid magazine file holders organizing books folders and stationery',
				'title' => 'Rigid Magazine File Holders in Use',
				'caption' => 'Upright holders organizing slim books, folders and document collections.',
			),
			array(
				'file' => '03-teal-rigid-magazine-file-holder-interior.webp',
				'alt' => 'Teal rigid paperboard magazine file holder open interior view',
				'title' => 'Teal Rigid Magazine Holder Interior',
				'caption' => 'Single teal holder showing the open top, wrapped interior and sloped access sides.',
			),
			array(
				'file' => '04-rigid-magazine-file-holder-label-finger-pull-closeup.webp',
				'alt' => 'Magazine file holder front label and metal rimmed finger pull close-up',
				'title' => 'Magazine Holder Label and Finger Pull',
				'caption' => 'Close-up of the writable label panel and circular retrieval opening.',
			),
			array(
				'file' => '05-rigid-magazine-file-holder-set-rear-side-view.webp',
				'alt' => 'Rigid paperboard magazine file holder set rear and sloped side view',
				'title' => 'Magazine File Holder Rear and Side View',
				'caption' => 'Rear view of three freestanding holders with aligned walls and sloped side profiles.',
			),
			array(
				'file' => '06-rigid-magazine-file-holder-feature-callouts.webp',
				'alt' => 'Rigid paperboard magazine holder features open top sloped side label and finger pull',
				'title' => 'Rigid Magazine File Holder Features',
				'caption' => 'Visual reference identifying the rigid board, open top, sloped side, label area and finger pull.',
			),
		),
		'faqs'             => array(
			array( 'What is a rigid paperboard magazine file holder?', 'It is an upright open storage case made from thick board wrapped with decorative paper. Sloped sides, a front label and a finger pull make magazines, catalogs, folders and workbooks easier to organize and retrieve.' ),
			array( 'Can the holder dimensions be customized?', 'Yes. Internal width, depth, front height, rear height and slope are developed around the largest intended contents and the available shelf, desk or retail space.' ),
			array( 'Can we change the colors, labels and finger pull?', 'Yes. The teal, mustard, gray, white labels and metal-look rims are sample references. Wrap color, printing, label method and retrieval opening can be adapted to the approved brief.' ),
			array( 'What is the minimum order quantity?', 'The standard reference for this product is 1,000 sets. The confirmed MOQ depends on dimensions, materials, number of colors or artwork versions, accessories and packing requirements.' ),
			array( 'Can a sample be made before production?', 'Yes. A structural sample can confirm content fit, stability and access. A decorated sample can then confirm wrap paper, color, print, labels and surface details before production.' ),
			array( 'Where are the magazine file holders manufactured?', 'VPN Packaging develops and manufactures custom paper packaging and paperboard products in Ho Chi Minh City, Vietnam, for domestic and international B2B programs.' ),
		),
	);
}

function vpn_magazine_file_holder_202609_spec_rows( $definition ) {
	$defaults = array(
		'Place of Origin' => 'Vietnam',
		'Brand Name' => 'VPN',
		'Province' => 'Ho Chi Minh City',
		'Custom Order' => 'Accept',
		'Logo Printing' => 'Custom logo',
		'Size' => 'Customized size',
		'Thickness' => 'Customized thickness',
		'Single Piece Price' => 'Request a quote',
		'Minimum Order Quantity (MOQ)' => '1000 sets',
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

function vpn_magazine_file_holder_202609_exact_attachment_id( $base ) {
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

function vpn_magazine_file_holder_202609_attachment( $image, $product_id ) {
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

	$attachment_id = vpn_magazine_file_holder_202609_exact_attachment_id( $base );
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

function vpn_magazine_file_holder_202609_term_ids( $taxonomy, $slugs ) {
	$ids = array();
	foreach ( $slugs as $slug ) {
		$slug = sanitize_title( $slug );
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $term || is_wp_error( $term ) ) {
			if ( 'product_cat' === $taxonomy ) {
				throw new RuntimeException( 'Required product category is missing: ' . $slug );
			}
			$created = wp_insert_term( ucwords( str_replace( '-', ' ', $slug ) ), $taxonomy, array( 'slug' => $slug ) );
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

function vpn_magazine_file_holder_202609_faq_html( $items ) {
	$html = '<section class="product-faq magazine-file-holder-faq" itemscope itemtype="https://schema.org/FAQPage"><div class="container"><h2>Frequently Asked Questions</h2>';
	foreach ( $items as $item ) {
		$html .= '<details class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"><summary itemprop="name">' . esc_html( $item[0] ) . '</summary><div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"><p itemprop="text">' . esc_html( $item[1] ) . '</p></div></details>';
	}
	return $html . '</div></section>';
}

function vpn_magazine_file_holder_202609_figure( $attachment_id, $image, $slot ) {
	$tag = wp_get_attachment_image( $attachment_id, 'large', false, array( 'alt' => $image['alt'], 'loading' => 'lazy', 'decoding' => 'async' ) );
	if ( ! $tag ) {
		throw new RuntimeException( 'Could not render inline image: ' . $image['file'] );
	}
	return '<!-- stable-product-image:slot_' . (int) $slot . ' --><figure class="product-inline-figure product-inline-figure-small">' . $tag . '<figcaption>' . esc_html( $image['caption'] ) . '</figcaption></figure>';
}

function vpn_magazine_file_holder_202609_insert_figures( $content, $attachment_ids, $images ) {
	$chosen = array( 1, 2, 3, 5 );
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
			return $matches[0] . vpn_magazine_file_holder_202609_figure( $attachment_ids[ $image_index ], $images[ $image_index ], $slot );
		},
		$content
	);
	if ( 4 !== $slot ) {
		throw new RuntimeException( 'Long content does not contain enough H2 sections for four figures.' );
	}
	return $content;
}

function vpn_magazine_file_holder_202609_normalize_internal_links( $content ) {
	return preg_replace_callback(
		'/href="\/([^"#][^"]*)"/i',
		static function ( $matches ) {
			return 'href="' . esc_url( home_url( '/' . ltrim( $matches[1], '/' ) ) ) . '"';
		},
		$content
	);
}

function vpn_magazine_file_holder_202609_find_product( $definition ) {
	$product = get_page_by_path( $definition['slug'], OBJECT, 'product' );
	if ( $product instanceof WP_Post ) {
		return $product;
	}
	$marked = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => '_vpn_magazine_file_holder_202609_slug', 'meta_value' => $definition['slug'] ) );
	return $marked ? $marked[0] : null;
}

function vpn_magazine_file_holder_202609_import() {
	$definition = vpn_magazine_file_holder_202609_definition();
	$content_path = get_template_directory() . '/inc/product-content/magazine-file-holder-202609/' . $definition['content_file'];
	$content = file_exists( $content_path ) ? file_get_contents( $content_path ) : false;
	if ( false === $content || preg_match( '/<h1\b/i', $content ) ) {
		throw new RuntimeException( 'Missing or invalid product content: ' . $definition['content_file'] );
	}

	$product = vpn_magazine_file_holder_202609_find_product( $definition );
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
		$attachment_ids[] = vpn_magazine_file_holder_202609_attachment( $image, $product_id );
	}
	$content = vpn_magazine_file_holder_202609_normalize_internal_links( $content );
	$content = vpn_magazine_file_holder_202609_insert_figures( $content, $attachment_ids, $definition['images'] );
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

	$category_ids = vpn_magazine_file_holder_202609_term_ids( 'product_cat', $definition['categories'] );
	wp_set_object_terms( $product_id, $category_ids, 'product_cat', false );
	wp_set_object_terms( $product_id, vpn_magazine_file_holder_202609_term_ids( 'product_tag', $definition['tags'] ), 'product_tag', false );
	wp_set_object_terms( $product_id, array( 'simple' ), 'product_type', false );
	set_post_thumbnail( $product_id, $attachment_ids[0] );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $attachment_ids, 1 ) ) );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_magazine_file_holder_202609_spec_rows( $definition ) );
	update_post_meta( $product_id, '_custom_box_product_faq_html', vpn_magazine_file_holder_202609_faq_html( $definition['faqs'] ) );
	update_post_meta( $product_id, '_vpn_sample_import', VPN_MAGAZINE_FILE_HOLDER_202609_MARKER );
	update_post_meta( $product_id, '_vpn_magazine_file_holder_202609_slug', $definition['slug'] );
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

if ( function_exists( 'set_time_limit' ) ) {
	@set_time_limit( 0 );
}

$vpn_magazine_file_holder_202609_result = vpn_magazine_file_holder_202609_import();
echo wp_json_encode( $vpn_magazine_file_holder_202609_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
