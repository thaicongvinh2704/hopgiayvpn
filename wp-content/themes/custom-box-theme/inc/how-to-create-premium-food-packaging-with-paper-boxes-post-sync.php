<?php
/**
 * Syncs the premium food packaging blog draft with its images and SEO fields.
 */

add_action( 'admin_init', 'custom_box_sync_premium_food_packaging_post' );
add_action( 'admin_notices', 'custom_box_premium_food_packaging_post_notice' );

function custom_box_sync_premium_food_packaging_post(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$post_data = custom_box_premium_food_packaging_post_data();
	$post      = custom_box_find_premium_food_packaging_post( $post_data['slug'], $post_data['title'] );

	if ( ! $post ) {
		update_option( 'custom_box_premium_food_packaging_missing_post', 'Draft post not found by slug or title.', false );
		return;
	}

	$post_id = (int) $post->ID;
	$status  = in_array( $post->post_status, array( 'publish', 'private' ), true ) ? $post->post_status : 'draft';
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_title'   => $post_data['title'],
			'post_name'    => $post_data['slug'],
			'post_excerpt' => $post_data['excerpt'],
			'post_status'  => $status,
		)
	);

	$category = get_term_by( 'slug', $post_data['category']['slug'], 'category' );
	if ( ! $category || is_wp_error( $category ) ) {
		$created = wp_insert_term( $post_data['category']['name'], 'category', array( 'slug' => $post_data['category']['slug'] ) );
		if ( ! is_wp_error( $created ) ) {
			$category = get_term( (int) $created['term_id'], 'category' );
		}
	}
	if ( $category && ! is_wp_error( $category ) ) {
		wp_set_post_categories( $post_id, array( (int) $category->term_id ), false );
	}
	wp_set_post_tags( $post_id, $post_data['tags'], false );
	update_post_meta( $post_id, 'rank_math_title', $post_data['seo_title'] );
	update_post_meta( $post_id, 'rank_math_description', $post_data['seo_description'] );
	update_post_meta( $post_id, 'rank_math_focus_keyword', $post_data['focus_keyword'] );

	custom_box_sync_premium_food_packaging_images( $post_id );
	update_post_meta( $post_id, '_custom_box_premium_food_packaging_synced', current_time( 'mysql' ) );
	update_option( 'custom_box_premium_food_packaging_missing_post', '', false );
}

function custom_box_premium_food_packaging_post_data(): array {
	return array(
		'title'           => 'How to Create Premium Food Packaging with Paper Boxes',
		'slug'            => 'how-to-create-premium-food-packaging-with-paper-boxes',
		'excerpt'         => 'Learn how to create premium food packaging with paper boxes by coordinating structure, paperboard, printing, finishing, samples, and quality control.',
		'seo_title'       => 'How to Create Premium Food Packaging with Paper Boxes',
		'seo_description' => 'Learn how to coordinate paperboard, box structure, printing, finishing, samples, and QC to create premium paper food packaging for B2B projects.',
		'focus_keyword'   => 'how to create premium food packaging with paper boxes',
		'category'        => array( 'name' => 'Packaging Guides', 'slug' => 'packaging-guides' ),
		'tags'            => array( 'Food Packaging', 'Paper Boxes', 'Packaging Materials', 'Printing', 'Finishing', 'Packaging Design' ),
	);
}

function custom_box_premium_food_packaging_images(): array {
	return array(
		'featured' => array(
			'base'    => 'how-to-create-premium-food-packaging-with-paper-boxes',
			'alt'     => 'Premium paper food packaging boxes with refined materials and restrained finishing',
			'title'   => 'Premium Food Packaging with Paper Boxes',
			'caption' => 'Premium presentation comes from coordinated structure, material, printing, and finishing.',
		),
		'slot_1' => array(
			'base'    => 'premium-food-packaging-architecture',
			'alt'     => 'Sealed food products arranged inside folding and rigid paper packaging systems',
			'title'   => 'Premium Food Packaging Architecture',
			'caption' => 'Primary packs, presentation boxes, inserts, and shipping protection perform different roles.',
		),
		'slot_2' => array(
			'base'    => 'paperboard-materials-for-premium-food-boxes',
			'alt'     => 'White paperboard kraft paper textured wrap and fine flute food box samples',
			'title'   => 'Paperboard Materials for Premium Food Boxes',
			'caption' => 'Paperboard should be evaluated for printing, converting, structure, and intended use.',
		),
		'slot_3' => array(
			'base'    => 'premium-food-box-printing-and-finishing',
			'alt'     => 'Close view of restrained foil embossing spot UV and matte surfaces on paper boxes',
			'title'   => 'Premium Food Box Printing and Finishing',
			'caption' => 'A controlled finishing hierarchy creates a clearer premium focal point.',
		),
		'slot_4' => array(
			'base'    => 'premium-paper-food-box-quality-control',
			'alt'     => 'Packaging team checking printed paper box color edges inserts and surface quality',
			'title'   => 'Premium Paper Food Box Quality Control',
			'caption' => 'Production-representative samples help convert subjective expectations into approval criteria.',
		),
	);
}

function custom_box_find_premium_food_packaging_post( string $slug, string $title ): ?WP_Post {
	$post = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $post && 'trash' !== $post->post_status ) {
		return $post;
	}

	global $wpdb;
	$post_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status <> 'trash' AND post_title = %s ORDER BY ID DESC LIMIT 1",
			$title
		)
	);
	return $post_id ? get_post( $post_id ) : null;
}

function custom_box_sync_premium_food_packaging_images( int $post_id ): void {
	$post           = get_post( $post_id );
	$content        = $post ? (string) $post->post_content : '';
	$missing_images = array();
	$missing_slots  = array();

	foreach ( custom_box_premium_food_packaging_images() as $key => $image ) {
		$attachment_id = custom_box_find_premium_food_packaging_attachment( $image['base'] );
		if ( ! $attachment_id || ! wp_get_attachment_url( $attachment_id ) ) {
			$missing_images[] = $image['base'];
			continue;
		}

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $image['alt'] );
		wp_update_post(
			array(
				'ID'           => $attachment_id,
				'post_parent'  => $post_id,
				'post_title'   => $image['title'],
				'post_excerpt' => $image['caption'],
			)
		);

		if ( 'featured' === $key ) {
			set_post_thumbnail( $post_id, $attachment_id );
			continue;
		}

		$marker       = '<!-- vpn-premium-food-packaging-image:' . $key . ' -->';
		$figure       = $marker . "\n" . custom_box_premium_food_packaging_figure( $attachment_id, $image );
		$slot_number  = (int) substr( $key, -1 );
		$slot         = '<!-- IMAGE_SLOT_' . $slot_number . ' -->';
		$marker_regex = '/' . preg_quote( $marker, '/' ) . '\s*<figure\b.*?<\/figure>/is';
		$slot_regex   = '/<span\b[^>]*>\s*' . preg_quote( $slot, '/' ) . '\s*<\/span>/i';

		if ( false !== strpos( $content, $marker ) ) {
			$content = preg_replace( $marker_regex, $figure, $content, 1 );
		} elseif ( false !== strpos( $content, $slot ) ) {
			$content = str_replace( $slot, $figure, $content );
		} elseif ( preg_match( $slot_regex, $content ) ) {
			$content = preg_replace( $slot_regex, $figure, $content, 1 );
		} else {
			$missing_slots[] = 'IMAGE_SLOT_' . $slot_number;
		}
	}

	if ( $post && $content !== (string) $post->post_content ) {
		wp_update_post( array( 'ID' => $post_id, 'post_content' => $content ) );
	}
	update_option( 'custom_box_premium_food_packaging_missing_images', $missing_images, false );
	update_option( 'custom_box_premium_food_packaging_missing_slots', $missing_slots, false );
}

function custom_box_find_premium_food_packaging_attachment( string $base ): int {
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array( array( 'key' => '_wp_attached_file', 'value' => $base, 'compare' => 'LIKE' ) ),
		)
	);
	foreach ( $ids as $id ) {
		$file = (string) get_post_meta( (int) $id, '_wp_attached_file', true );
		if ( 0 === strcasecmp( pathinfo( basename( $file ), PATHINFO_FILENAME ), $base ) ) {
			return (int) $id;
		}
	}
	return custom_box_create_premium_food_packaging_attachment( $base );
}

function custom_box_create_premium_food_packaging_attachment( string $base ): int {
	$uploads   = wp_get_upload_dir();
	$extensions = array( 'webp', 'jpg', 'jpeg', 'png' );
	$file_path = '';
	$relative  = '';
	foreach ( $extensions as $extension ) {
		$candidate = '2026/07/' . $base . '.' . $extension;
		$upload_candidate = trailingslashit( $uploads['basedir'] ) . $candidate;
		$bundle_candidate = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $candidate;
		if ( ! file_exists( $upload_candidate ) && file_exists( $bundle_candidate ) ) {
			wp_mkdir_p( dirname( $upload_candidate ) );
			copy( $bundle_candidate, $upload_candidate );
		}
		if ( file_exists( $upload_candidate ) ) {
			$file_path = $upload_candidate;
			$relative  = $candidate;
			break;
		}
	}
	if ( ! $file_path ) {
		return 0;
	}
	$filetype      = wp_check_filetype( $file_path );
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => trailingslashit( $uploads['baseurl'] ) . $relative,
			'post_mime_type' => $filetype['type'] ?: 'image/webp',
			'post_title'     => ucwords( str_replace( '-', ' ', $base ) ),
			'post_name'      => sanitize_title( $base ),
			'post_status'    => 'inherit',
		),
		$file_path
	);
	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$metadata = wp_generate_attachment_metadata( (int) $attachment_id, $file_path );
	if ( ! is_wp_error( $metadata ) && $metadata ) {
		wp_update_attachment_metadata( (int) $attachment_id, $metadata );
	}
	update_post_meta( (int) $attachment_id, '_wp_attached_file', $relative );
	return (int) $attachment_id;
}

function custom_box_premium_food_packaging_figure( int $attachment_id, array $image ): string {
	return sprintf(
		'<figure><img src="%s" alt="%s" style="width:100%%; height:auto;" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
		esc_url( wp_get_attachment_url( $attachment_id ) ),
		esc_attr( $image['alt'] ),
		esc_html( $image['caption'] )
	);
}

function custom_box_premium_food_packaging_post_notice(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$missing_post   = (string) get_option( 'custom_box_premium_food_packaging_missing_post', '' );
	$missing_images = (array) get_option( 'custom_box_premium_food_packaging_missing_images', array() );
	$missing_slots  = (array) get_option( 'custom_box_premium_food_packaging_missing_slots', array() );
	if ( ! $missing_post && empty( $missing_images ) && empty( $missing_slots ) ) {
		return;
	}
	$messages = array();
	if ( $missing_post ) {
		$messages[] = 'post: ' . $missing_post;
	}
	if ( $missing_images ) {
		$messages[] = 'images: ' . implode( ', ', $missing_images );
	}
	if ( $missing_slots ) {
		$messages[] = 'slots: ' . implode( ', ', $missing_slots );
	}
	echo '<div class="notice notice-warning"><p><strong>Premium food packaging post sync:</strong> ' . esc_html( implode( ' | ', $messages ) ) . '</p></div>';
}
