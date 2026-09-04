<?php
/**
 * Import the eight bread-bag product pages from the Git-tracked SEO package.
 *
 * The source TXT files and original WebP files live in the active theme so a
 * pull-deploy can restore the same content and media without manual uploads.
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

const VPN_BREAD_BAG_202609_MARKER = 'product-samples-bread-bags-202609';
const VPN_BREAD_BAG_202609_SOURCE_DIR = 'inc/product-content/bread-bag-202609';
const VPN_BREAD_BAG_202609_UPLOAD_DIR = '2026/09';

function vpn_bread_bag_202609_products(): array {
	return array(
		array(
			'title'         => 'Custom Printed Flat Bread Paper Bags',
			'slug'          => 'custom-printed-flat-bread-paper-bags',
			'folder'        => '01-custom-printed-flat-bread-paper-bags',
			'content_file'  => 'custom-printed-flat-bread-paper-bags-seo-content.txt',
			'categories'    => array( 'paper-bags-with-logo', 'bakery-packaging-boxes' ),
			'feature'       => 'Flat paper bag format for slim bread and bakery items',
			'industrial'    => 'Bakeries, cafes, food counters and food-service distribution',
			'paper'         => 'Kraft or white paper; grease-resistant option by use',
			'bag_type'      => 'Custom flat paper bag',
			'shape'         => 'Flat rectangular sleeve',
			'model'         => 'VPN-BAG-FLAT-BREAD',
			'accessories'   => 'Optional folded top, inner liner or closure',
			'liner'         => 'Paper interior selected for the intended application',
			'printing'      => 'Flexographic or digital logo printing; CMYK or Pantone by project',
			'colors'        => 'Natural kraft, white or custom printed',
		),
		array(
			'title'         => 'Custom Kraft Side Gusset Bread Bags',
			'slug'          => 'custom-kraft-side-gusset-bread-bags',
			'folder'        => '02-custom-kraft-side-gusset-bread-bags',
			'content_file'  => 'custom-kraft-side-gusset-bread-bags-seo-content.txt',
			'categories'    => array( 'paper-bags-with-logo', 'bakery-packaging-boxes' ),
			'feature'       => 'Expandable side gusset for thicker loaves and bakery items',
			'industrial'    => 'Bakeries, cafes, food counters and food-service distribution',
			'paper'         => 'Natural kraft or white paper; barrier option by use',
			'bag_type'      => 'Custom side gusset bread paper bag',
			'shape'         => 'Rectangular bag with expandable side gussets',
			'model'         => 'VPN-BAG-GUSSET-BREAD',
			'accessories'   => 'Optional top fold, label, closure or inner liner',
			'liner'         => 'Paper interior selected for the intended application',
			'printing'      => 'Flexographic or digital logo printing; CMYK or Pantone by project',
			'colors'        => 'Natural kraft, white or custom printed',
		),
		array(
			'title'         => 'Custom Printed Baguette Paper Bags',
			'slug'          => 'custom-printed-baguette-paper-bags',
			'folder'        => '03-custom-printed-baguette-paper-bags',
			'content_file'  => 'custom-printed-baguette-paper-bags-seo-content.txt',
			'categories'    => array( 'paper-bags-with-logo', 'bakery-packaging-boxes' ),
			'feature'       => 'Long paper-bag format for baguettes and other narrow loaves',
			'industrial'    => 'Bakeries, cafes, artisan bread shops and food distributors',
			'paper'         => 'Kraft or white paper; grease-resistant option by use',
			'bag_type'      => 'Custom baguette paper bag',
			'shape'         => 'Long rectangular bread sleeve',
			'model'         => 'VPN-BAG-BAGUETTE',
			'accessories'   => 'Optional window, ventilation, fold or closure',
			'liner'         => 'Paper interior selected for the intended application',
			'printing'      => 'Flexographic or digital logo printing; CMYK or Pantone by project',
			'colors'        => 'Natural kraft, white or custom printed',
		),
		array(
			'title'         => 'Custom Square Bottom Bakery Paper Bags',
			'slug'          => 'custom-square-bottom-bakery-paper-bags',
			'folder'        => '04-custom-square-bottom-bakery-paper-bags',
			'content_file'  => 'custom-square-bottom-bakery-paper-bags-seo-content.txt',
			'categories'    => array( 'paper-bags-with-logo', 'bakery-packaging-boxes', 'food-paper-boxes' ),
			'feature'       => 'Self-standing square bottom for wide bakery loading and display',
			'industrial'    => 'Bakeries, cafes, food counters and food-service distribution',
			'paper'         => 'Kraft or white paper; barrier option by use',
			'bag_type'      => 'Custom square bottom bakery paper bag',
			'shape'         => 'Square or rectangular standing bag',
			'model'         => 'VPN-BAG-SQUARE-BAKERY',
			'accessories'   => 'Optional top fold, label, closure or inner liner',
			'liner'         => 'Paper interior selected for the intended application',
			'printing'      => 'Flexographic or digital logo printing; CMYK or Pantone by project',
			'colors'        => 'Natural kraft, white or custom printed',
		),
		array(
			'title'         => 'Custom Paper Bread Bags With Window',
			'slug'          => 'custom-paper-bread-bags-with-window',
			'folder'        => '05-custom-paper-bread-bags-with-window',
			'content_file'  => 'custom-paper-bread-bags-with-window-seo-content.txt',
			'categories'    => array( 'paper-bags-with-logo', 'bakery-packaging-boxes', 'food-paper-boxes' ),
			'feature'       => 'Clear viewing window for product visibility before opening',
			'industrial'    => 'Bakeries, cafes, retail counters and food-service distribution',
			'paper'         => 'Kraft or white paper with clear window film by project',
			'bag_type'      => 'Custom window bread paper bag',
			'shape'         => 'Flat rectangular bag with viewing window',
			'model'         => 'VPN-BAG-WINDOW-BREAD',
			'accessories'   => 'Clear window; optional closure or inner liner',
			'liner'         => 'Paper interior selected for the intended application',
			'printing'      => 'Flexographic or digital logo printing; CMYK or Pantone by project',
			'colors'        => 'Natural kraft, white or custom printed',
		),
		array(
			'title'         => 'Custom Greaseproof Bakery Paper Bags',
			'slug'          => 'custom-greaseproof-bakery-paper-bags',
			'folder'        => '06-custom-greaseproof-bakery-paper-bags',
			'content_file'  => 'custom-greaseproof-bakery-paper-bags-seo-content.txt',
			'categories'    => array( 'paper-bags-with-logo', 'bakery-packaging-boxes', 'food-paper-boxes' ),
			'feature'       => 'Grease-resistant paper for buttery or oily bakery products',
			'industrial'    => 'Bakeries, pastry shops, cafes and food-service distribution',
			'paper'         => 'Grease-resistant paper selected for the intended application',
			'bag_type'      => 'Custom greaseproof bakery paper bag',
			'shape'         => 'Flat or gusseted rectangular paper bag',
			'model'         => 'VPN-BAG-GREASEPROOF-BAKERY',
			'accessories'   => 'Optional window, fold, label or closure',
			'liner'         => 'Grease-resistant paper interior selected by product testing',
			'printing'      => 'Flexographic or digital logo printing; CMYK or Pantone by project',
			'colors'        => 'Natural kraft, white or custom printed',
		),
		array(
			'title'         => 'Custom Die Cut Handle Bakery Paper Bags',
			'slug'          => 'custom-die-cut-handle-bakery-paper-bags',
			'folder'        => '07-custom-die-cut-handle-bakery-paper-bags',
			'content_file'  => 'custom-die-cut-handle-bakery-paper-bags-seo-content.txt',
			'categories'    => array( 'paper-bags-with-logo', 'bakery-packaging-boxes', 'food-paper-boxes' ),
			'feature'       => 'Integrated die-cut handle for bakery takeaway carrying',
			'industrial'    => 'Bakeries, cafes, takeaway counters and food-service distribution',
			'paper'         => 'Kraft or white paper; reinforcement selected by load testing',
			'bag_type'      => 'Custom die-cut handle bakery paper bag',
			'shape'         => 'Rectangular carry bag with integrated handle',
			'model'         => 'VPN-BAG-DIECUT-BAKERY',
			'accessories'   => 'Integrated die-cut handle with optional reinforcement',
			'liner'         => 'Paper interior selected for the intended application',
			'printing'      => 'Flexographic or digital logo printing; CMYK or Pantone by project',
			'colors'        => 'Natural kraft, white or custom printed',
		),
		array(
			'title'         => 'Custom Twisted Handle Bakery Paper Bags',
			'slug'          => 'custom-twisted-handle-bakery-paper-bags',
			'folder'        => '08-custom-twisted-handle-bakery-paper-bags',
			'content_file'  => 'custom-twisted-handle-bakery-paper-bags-seo-content.txt',
			'categories'    => array( 'paper-bags-with-logo', 'bakery-packaging-boxes', 'food-paper-boxes' ),
			'feature'       => 'Twisted paper handles for multi-item bakery takeaway orders',
			'industrial'    => 'Bakeries, cafes, retail counters and food-service distribution',
			'paper'         => 'Kraft or white paper; handle reinforcement selected by testing',
			'bag_type'      => 'Custom twisted handle bakery paper bag',
			'shape'         => 'Gusseted rectangular carry bag',
			'model'         => 'VPN-BAG-TWISTED-BAKERY',
			'accessories'   => 'Twisted paper handles and pasted reinforcement patches',
			'liner'         => 'Paper interior selected for the intended application',
			'printing'      => 'Flexographic or digital logo printing; CMYK or Pantone by project',
			'colors'        => 'Natural kraft, white or custom printed',
		),
	);
}

function vpn_bread_bag_202609_source_path( array $product ): string {
	return trailingslashit( get_template_directory() )
		. VPN_BREAD_BAG_202609_SOURCE_DIR . '/'
		. $product['folder'] . '/' . $product['content_file'];
}

function vpn_bread_bag_202609_meta( string $source, string $label ): string {
	$pattern = '/^' . preg_quote( $label, '/' ) . '\s*:\s*(.+)$/mi';
	return preg_match( $pattern, $source, $match ) ? trim( $match[1] ) : '';
}

function vpn_bread_bag_202609_parse_source( string $path ): array {
	$source = file_get_contents( $path );
	if ( false === $source ) {
		throw new RuntimeException( 'Unable to read source content: ' . $path );
	}

	$source = str_replace( array( "\r\n", "\r" ), "\n", $source );
	$copy_parts = preg_split( '/^PRODUCT PAGE COPY\s*$/mi', $source, 2 );
	$copy       = isset( $copy_parts[1] ) ? $copy_parts[1] : '';
	$copy_parts = preg_split( '/^INTERNAL LINK RECOMMENDATIONS\s*$/mi', $copy, 2 );
	$copy       = trim( $copy_parts[0] ?? '' );

	$links = array();
	if ( preg_match_all( '/^Anchor:\s*(.+)\n\s*URL:\s*(.+)$/mi', $source, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $match ) {
			$links[] = array(
				'anchor' => trim( $match[1] ),
				'url'    => trim( $match[2] ),
			);
		}
	}

	$images = array();
	if ( preg_match_all( '/^(\d+)\.\s+Filename:\s*(.+)\n\s+Alt:\s*(.+)$/mi', $source, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $match ) {
			$images[] = array(
				'number'   => (int) $match[1],
				'filename' => trim( $match[2] ),
				'alt'      => trim( $match[3] ),
			);
		}
	}
	usort( $images, static function ( $left, $right ) {
		return $left['number'] <=> $right['number'];
	} );

	return array(
		'title'       => vpn_bread_bag_202609_meta( $source, 'SEO Product Name / H1' ),
		'primary'     => vpn_bread_bag_202609_meta( $source, 'Primary Keyword' ),
		'seo_title'   => vpn_bread_bag_202609_meta( $source, 'SEO Title' ),
		'meta'        => vpn_bread_bag_202609_meta( $source, 'Meta Description' ),
		'copy'        => $copy,
		'links'       => $links,
		'images'      => $images,
	);
}

function vpn_bread_bag_202609_inline_text( string $text ): string {
	$text = preg_replace( '/\*\*(.+?)\*\*/s', '$1', $text );
	$text = preg_replace( '/\*(.+?)\*/s', '$1', $text );
	return esc_html( trim( preg_replace( '/\s+/u', ' ', $text ) ) );
}

function vpn_bread_bag_202609_flush_paragraph( string &$html, array &$paragraph ): void {
	if ( empty( $paragraph ) ) {
		return;
	}

	$html       .= '<p>' . vpn_bread_bag_202609_inline_text( implode( ' ', $paragraph ) ) . '</p>';
	$paragraph = array();
}

function vpn_bread_bag_202609_close_list( string &$html, string &$list_type ): void {
	if ( '' === $list_type ) {
		return;
	}

	$html      .= '</' . $list_type . '>';
	$list_type = '';
}

function vpn_bread_bag_202609_render_markdown( string $copy ): array {
	$lines      = preg_split( '/\n/', $copy );
	$html       = '';
	$paragraph  = array();
	$list_type  = '';
	$intro      = array();
	$seen_h2    = false;

	foreach ( $lines as $line ) {
		$line  = trim( $line );
		$is_h2  = preg_match( '/^##\s+(.+)$/u', $line, $h2 );
		$is_h3  = preg_match( '/^###\s+(.+)$/u', $line, $h3 );
		$is_ul  = preg_match( '/^[-*]\s+(.+)$/u', $line, $ul );
		$is_ol  = preg_match( '/^\d+[.)]\s+(.+)$/u', $line, $ol );

		if ( '' === $line ) {
			vpn_bread_bag_202609_flush_paragraph( $html, $paragraph );
			vpn_bread_bag_202609_close_list( $html, $list_type );
			continue;
		}

		if ( $is_h2 ) {
			vpn_bread_bag_202609_flush_paragraph( $html, $paragraph );
			vpn_bread_bag_202609_close_list( $html, $list_type );
			$html    .= '<h2>' . vpn_bread_bag_202609_inline_text( $h2[1] ) . '</h2>';
			$seen_h2 = true;
			continue;
		}

		if ( $is_h3 ) {
			vpn_bread_bag_202609_flush_paragraph( $html, $paragraph );
			vpn_bread_bag_202609_close_list( $html, $list_type );
			$html .= '<h3>' . vpn_bread_bag_202609_inline_text( $h3[1] ) . '</h3>';
			continue;
		}

		if ( $is_ul || $is_ol ) {
			vpn_bread_bag_202609_flush_paragraph( $html, $paragraph );
			$wanted = $is_ul ? 'ul' : 'ol';
			if ( $list_type !== $wanted ) {
				vpn_bread_bag_202609_close_list( $html, $list_type );
				$html      .= '<' . $wanted . '>';
				$list_type = $wanted;
			}
			$item = $is_ul ? $ul[1] : $ol[1];
			$html .= '<li>' . vpn_bread_bag_202609_inline_text( $item ) . '</li>';
			continue;
		}

		// The source H1 is intentionally omitted because the product template
		// renders the page H1 above the description.
		if ( preg_match( '/^#\s+/', $line ) ) {
			continue;
		}

		if ( $seen_h2 ) {
			vpn_bread_bag_202609_close_list( $html, $list_type );
		}
		$paragraph[] = $line;
		if ( count( $intro ) < 2 && ! $seen_h2 ) {
			$intro[] = $line;
		}
	}

	vpn_bread_bag_202609_flush_paragraph( $html, $paragraph );
	vpn_bread_bag_202609_close_list( $html, $list_type );

	return array(
		'html'  => $html,
		'short' => implode( ' ', $intro ),
	);
}

function vpn_bread_bag_202609_local_url( string $url ): string {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	return $path ? home_url( $path ) : $url;
}

function vpn_bread_bag_202609_canonical_url( string $slug ): string {
	$structure = function_exists( 'wc_get_permalink_structure' ) ? wc_get_permalink_structure() : array();
	$base      = $structure['product_rewrite_slug'] ?? $structure['product_base'] ?? 'product';
	$base      = trim( (string) $base, '/' );
	return home_url( user_trailingslashit( $base . '/' . $slug ) );
}

function vpn_bread_bag_202609_related_links( array $links ): string {
	$rendered = array();
	foreach ( $links as $link ) {
		$rendered[] = '<a href="' . esc_url( vpn_bread_bag_202609_local_url( $link['url'] ) ) . '">' . esc_html( $link['anchor'] ) . '</a>';
	}

	if ( count( $rendered ) < 2 ) {
		return empty( $rendered ) ? '' : '<p>For related sourcing, see ' . $rendered[0] . '.</p>';
	}

	$last = array_pop( $rendered );
	return '<p>For related sourcing, review ' . implode( ', ', $rendered ) . ', and ' . $last . ' when planning the next bakery packaging project.</p>';
}

function vpn_bread_bag_202609_figure( int $image_id, string $alt, int $slot ): string {
	$image = wp_get_attachment_image(
		$image_id,
		'large',
		false,
		array(
			'alt'      => $alt,
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);
	if ( ! $image ) {
		return '';
	}

	return '<!-- stable-product-image:slot_' . $slot . ' -->'
		. '<figure class="product-inline-figure product-inline-figure-small">'
		. $image
		. '<figcaption>' . esc_html( $alt ) . '</figcaption>'
		. '</figure>';
}

function vpn_bread_bag_202609_add_figures( string $html, array $inline_images ): string {
	$h2_total = preg_match_all( '/<h2\b/i', $html );
	$positions = array_values( array_unique( array( 1, 3, 5, max( 1, $h2_total - 1 ) ) ) );
	sort( $positions );
	$position_index = 0;
	$h2_index       = 0;

	return preg_replace_callback(
		'/<h2\b[^>]*>.*?<\/h2>/is',
		static function ( $match ) use ( &$position_index, &$h2_index, $positions, $inline_images ) {
			++$h2_index;
			if ( ! isset( $positions[ $position_index ] ) || $h2_index !== $positions[ $position_index ] ) {
				return $match[0];
			}

			$image = $inline_images[ $position_index ] ?? null;
			++$position_index;
			return $match[0] . ( $image ? vpn_bread_bag_202609_figure( $image['id'], $image['alt'], $position_index ) : '' );
		},
		$html
	);
}

function vpn_bread_bag_202609_add_faq_microdata( string $content ): string {
	$heading  = '<h2>Frequently Asked Questions</h2>';
	$position = stripos( $content, $heading );
	if ( false === $position ) {
		return $content;
	}

	$before = substr( $content, 0, $position );
	$faq    = substr( $content, $position );
	$faq    = preg_replace(
		'/^<h2>Frequently Asked Questions<\/h2>/i',
		'<section class="product-faq" itemscope itemtype="https://schema.org/FAQPage"><h2>Frequently Asked Questions</h2>',
		$faq,
		1
	);
	$faq = preg_replace_callback(
		'/<h3>(.*?)<\/h3>\s*<p>(.*?)<\/p>/is',
		static function ( $match ) {
			return '<div itemprop="mainEntity" itemscope itemtype="https://schema.org/Question">'
				. '<h3 itemprop="name">' . $match[1] . '</h3>'
				. '<div itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">'
				. '<p itemprop="text">' . $match[2] . '</p></div></div>';
		},
		$faq
	);
	return $before . $faq . '</section>';
}

function vpn_bread_bag_202609_specs( array $product ): array {
	return array(
		array( 'label' => 'Feature', 'value' => $product['feature'] ),
		array( 'label' => 'Industrial Use', 'value' => $product['industrial'] ),
		array( 'label' => 'Paper Type', 'value' => $product['paper'] ),
		array( 'label' => 'Box Type', 'value' => $product['bag_type'] ),
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
		array( 'label' => 'Color', 'value' => $product['colors'] ),
		array( 'label' => 'Size', 'value' => 'Customized size' ),
		array( 'label' => 'Thickness', 'value' => 'Customized thickness' ),
		array( 'label' => 'Single Piece Price', 'value' => 'Price based on size, paper, printing, finishing and quantity' ),
		array( 'label' => 'Minimum Order Quantity (MOQ)', 'value' => 'Project-based quotation; confirm quantity by RFQ' ),
		array( 'label' => 'Product Name', 'value' => $product['title'] ),
		array( 'label' => 'Design', 'value' => "Customer's Specific Requirement" ),
	);
}

function vpn_bread_bag_202609_attachment( string $filename, int $parent_id, string $alt, string $title ): int {
	global $wpdb;

	$base = pathinfo( $filename, PATHINFO_FILENAME );
	$ids  = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
			'%' . $wpdb->esc_like( $base ) . '.%'
		)
	);

	foreach ( $ids as $id ) {
		$attached = (string) get_post_meta( (int) $id, '_wp_attached_file', true );
		if ( $base !== pathinfo( wp_basename( $attached ), PATHINFO_FILENAME ) ) {
			continue;
		}

		$attachment_id = (int) $id;
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		wp_update_post(
			array(
				'ID'          => $attachment_id,
				'post_title'  => $title,
				'post_parent' => $parent_id,
			)
		);
		return $attachment_id;
	}

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		return 0;
	}

	$filename = wp_basename( $filename );
	$relative = VPN_BREAD_BAG_202609_UPLOAD_DIR . '/' . $filename;
	$path     = trailingslashit( $uploads['basedir'] ) . $relative;
	$bundle   = trailingslashit( get_template_directory() )
		. 'inc/product-sample-deploy-assets/uploads/' . $relative;

	if ( ! file_exists( $path ) && file_exists( $bundle ) ) {
		if ( ! wp_mkdir_p( dirname( $path ) ) || ! copy( $bundle, $path ) ) {
			return 0;
		}
	}
	if ( ! file_exists( $path ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$filetype = wp_check_filetype( $filename, null );
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
		return 0;
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $path );
	if ( $metadata ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}
	update_post_meta( $attachment_id, '_wp_attached_file', $relative );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	return (int) $attachment_id;
}

function vpn_bread_bag_202609_exact_attachment_ids( string $base ): array {
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array( 'key' => '_wp_attached_file', 'value' => $base, 'compare' => 'LIKE' ),
			),
		)
	);
	$exact = array();
	foreach ( $ids as $id ) {
		$attached = (string) get_post_meta( (int) $id, '_wp_attached_file', true );
		if ( $base === pathinfo( wp_basename( $attached ), PATHINFO_FILENAME ) ) {
			$exact[] = (int) $id;
		}
	}
	return $exact;
}

function vpn_bread_bag_202609_find_product( array $product ): ?WP_Post {
	$post = get_page_by_path( $product['slug'], OBJECT, 'product' );
	if ( $post instanceof WP_Post ) {
		return $post;
	}

	$by_marker = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'private', 'trash' ),
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'DESC',
			'meta_query'     => array(
				array( 'key' => '_vpn_bread_bag_202609_slug', 'value' => $product['slug'] ),
			),
		)
	);
	if ( ! empty( $by_marker[0] ) ) {
		return $by_marker[0];
	}

	global $wpdb;
	$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_title = %s AND post_status <> 'trash' ORDER BY ID DESC LIMIT 1", $product['title'] ) );
	return $id ? get_post( (int) $id ) : null;
}

function vpn_bread_bag_202609_term_ids( array $slugs ): array {
	$names = array(
		'paper-bags-with-logo'   => 'Paper Bags with Logo',
		'bakery-packaging-boxes' => 'Bakery Packaging Boxes',
		'food-paper-boxes'       => 'Food Paper Boxes',
	);
	$ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term ) {
			$created = wp_insert_term( $names[ $slug ] ?? ucwords( str_replace( '-', ' ', $slug ) ), 'product_cat', array( 'slug' => $slug ) );
			if ( is_wp_error( $created ) ) {
				continue;
			}
			$ids[] = (int) $created['term_id'];
			continue;
		}
		$ids[] = (int) $term->term_id;
	}
	return $ids;
}

function vpn_bread_bag_202609_import_product( array $product ): array {
	$source_path = vpn_bread_bag_202609_source_path( $product );
	$parsed      = vpn_bread_bag_202609_parse_source( $source_path );
	if ( $parsed['title'] !== $product['title'] || count( $parsed['images'] ) !== 7 ) {
		throw new RuntimeException( 'Source title/image count mismatch for ' . $product['slug'] );
	}

	$existing = vpn_bread_bag_202609_find_product( $product );
	// Publishing was explicitly requested for this batch. Promote existing
	// private products as well as new/draft products to publish.
	$status = 'publish';
	$product_id = $existing ? (int) $existing->ID : 0;

	if ( ! $product_id ) {
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
	}

	$image_ids = array();
	foreach ( $parsed['images'] as $image ) {
		$attachment_id = vpn_bread_bag_202609_attachment(
			$image['filename'],
			$product_id,
			$image['alt'],
			$product['title'] . ' – ' . $image['alt']
		);
		if ( ! $attachment_id ) {
			throw new RuntimeException( 'Unable to create or locate image: ' . $image['filename'] );
		}
		$image_ids[] = $attachment_id;
	}

	$rendered = vpn_bread_bag_202609_render_markdown( $parsed['copy'] );
	$inline_images = array();
	foreach ( array_slice( $parsed['images'], 1, 4 ) as $index => $image ) {
		$inline_images[] = array(
			'id'  => $image_ids[ $index + 1 ],
			'alt' => $image['alt'],
		);
	}
	$content = vpn_bread_bag_202609_add_figures( $rendered['html'], $inline_images );
	$links   = vpn_bread_bag_202609_related_links( $parsed['links'] );
	$faq_pos = stripos( $content, '<h2>Frequently Asked Questions</h2>' );
	if ( false === $faq_pos ) {
		$content .= $links;
	} else {
		$content = substr_replace( $content, $links, $faq_pos, 0 );
	}
	$content = vpn_bread_bag_202609_add_faq_microdata( $content );

	$updated = wp_update_post(
		array(
			'ID'           => $product_id,
			'post_type'    => 'product',
			'post_status'  => $status,
			'post_title'   => $product['title'],
			'post_name'    => $product['slug'],
			'post_excerpt' => '<p>' . vpn_bread_bag_202609_inline_text( $rendered['short'] ) . '</p>',
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $updated ) ) {
		throw new RuntimeException( $updated->get_error_message() );
	}

	wp_set_object_terms( $product_id, vpn_bread_bag_202609_term_ids( $product['categories'] ), 'product_cat', false );
	wp_set_object_terms( $product_id, 'simple', 'product_type', false );
	set_post_thumbnail( $product_id, $image_ids[0] );
	update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $image_ids, 1 ) ) );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_custom_box_product_specs', vpn_bread_bag_202609_specs( $product ) );
	update_post_meta( $product_id, '_vpn_sample_import', VPN_BREAD_BAG_202609_MARKER );
	update_post_meta( $product_id, '_vpn_bread_bag_202609_slug', $product['slug'] );
	update_post_meta( $product_id, 'rank_math_focus_keyword', $parsed['primary'] );
	update_post_meta( $product_id, 'rank_math_title', $parsed['seo_title'] );
	update_post_meta( $product_id, 'rank_math_description', $parsed['meta'] );
	update_post_meta( $product_id, 'rank_math_canonical_url', vpn_bread_bag_202609_canonical_url( $product['slug'] ) );
	update_post_meta( $product_id, 'rank_math_robots', array( 'index', 'follow' ) );

	return array(
		'id'      => $product_id,
		'status'  => get_post_status( $product_id ),
		'words'   => vpn_bread_bag_202609_word_count( $content ),
		'short'   => vpn_bread_bag_202609_word_count( $rendered['short'] ),
		'images'  => count( $image_ids ),
		'figures' => substr_count( $content, 'stable-product-image:' ),
		'url'     => get_permalink( $product_id ),
	);
}

function vpn_bread_bag_202609_word_count( string $text ): int {
	$plain = wp_strip_all_tags( $text );
	return preg_match_all( '/[\p{L}\p{N}][\p{L}\p{N}\-’]*/u', $plain, $matches );
}

$audit = array();
foreach ( vpn_bread_bag_202609_products() as $product ) {
	$result = vpn_bread_bag_202609_import_product( $product );
	$audit[] = $product['title'] . ' (#' . $result['id'] . ') status=' . $result['status']
		. ' words=' . $result['words'] . ' short=' . $result['short']
		. ' images=' . $result['images'] . ' inline_figures=' . $result['figures'];
	echo 'Imported: ' . end( $audit ) . ' URL=' . $result['url'] . PHP_EOL;
}

echo 'Bread bag product import complete: ' . count( $audit ) . ' products.' . PHP_EOL;
