<?php
/**
 * Admin button for deploying generated WooCommerce product samples without SSH.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CUSTOM_BOX_PRODUCT_SAMPLE_DEPLOY_VERSION', '2026-09-04-bread-bags' );

function custom_box_product_sample_deploy_can_run() {
	return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' );
}

function custom_box_product_sample_deploy_admin_menu() {
	add_management_page(
		'Product Sample Deploy',
		'Product Sample Deploy',
		'manage_options',
		'custom-box-product-sample-deploy',
		'custom_box_product_sample_deploy_page'
	);
}
add_action( 'admin_menu', 'custom_box_product_sample_deploy_admin_menu' );

function custom_box_product_sample_deploy_admin_post() {
	if ( ! custom_box_product_sample_deploy_can_run() ) {
		wp_die( esc_html__( 'You do not have permission to deploy product samples.', 'custom-box-theme' ) );
	}

	check_admin_referer( 'custom_box_product_sample_deploy' );

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 );
	}
	@ignore_user_abort( true );

	$result_key = 'custom_box_product_sample_deploy_result_' . get_current_user_id();
	$state_key  = 'custom_box_product_sample_deploy_state_' . get_current_user_id();

	if ( ! empty( $_POST['reset'] ) ) {
		delete_transient( $state_key );
	}

	$state = get_transient( $state_key );
	if ( ! is_array( $state ) ) {
		$scope = isset( $_POST['deploy_scope'] ) ? sanitize_key( wp_unslash( $_POST['deploy_scope'] ) ) : 'latest';
		if ( ! in_array( $scope, custom_box_product_sample_deploy_allowed_scopes(), true ) ) {
			$scope = 'latest';
		}

		$state = array(
			'log'          => '',
			'batch_index'  => 0,
			'script_index' => 0,
			'restored'     => false,
			'guide_done'   => false,
			'complete'     => false,
			'scope'        => $scope,
		);
	}

	$error  = '';
	$status = 'running';

	try {
		ob_start();

		if ( empty( $state['restored'] ) ) {
			echo custom_box_product_sample_deploy_restore_tools();
			echo custom_box_product_sample_deploy_restore_assets();
			$state['restored'] = true;
		}

		custom_box_product_sample_deploy_run_next_step( $state );
		$state['log'] .= ob_get_clean();
		$status        = ! empty( $state['complete'] ) ? 'complete' : 'running';
	} catch ( Throwable $e ) {
		$state['log'] .= ob_get_clean();
		$error         = $e->getMessage();
		$status        = 'error';
	}

	if ( 'running' === $status ) {
		set_transient( $state_key, $state, HOUR_IN_SECONDS );
	} else {
		delete_transient( $state_key );
	}

	set_transient(
		$result_key,
		array(
			'output' => $state['log'],
			'error'  => $error,
			'status' => $status,
			'time'   => current_time( 'mysql' ),
		),
		10 * MINUTE_IN_SECONDS
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'        => 'custom-box-product-sample-deploy',
				'deploy_done' => '1',
				'deploy_status' => $status,
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_custom_box_product_sample_deploy', 'custom_box_product_sample_deploy_admin_post' );

function custom_box_product_sample_cleanup_old_batches_admin_post() {
	if ( ! custom_box_product_sample_deploy_can_run() ) {
		wp_die( esc_html__( 'You do not have permission to clean product samples.', 'custom-box-theme' ) );
	}

	check_admin_referer( 'custom_box_product_sample_cleanup_old_batches' );

	$old_markers = array(
		'product-samples-10',
		'product-samples-batch-2-five',
	);
	$keep_markers = array(
		'product-samples-batch-3-ten',
		'product-samples-batch-4-remaining',
	);
	$duplicate_slugs = array(
		'custom-printed-corrugated-pet-food-packaging-box',
		'custom-paper-tube-packaging',
		'custom-paper-tube-packaging-box',
	);
	$duplicate_titles = array(
		'Custom Printed Corrugated Pet Food Packaging Box',
		'Custom Paper Tube Packaging',
		'Custom Paper Tube Packaging Box',
	);

	delete_transient( 'custom_box_product_sample_deploy_state_' . get_current_user_id() );

	$old_batch_products = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending', 'future' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_vpn_sample_import',
					'value'   => $old_markers,
					'compare' => 'IN',
				),
			),
		)
	);
	$duplicate_products = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending', 'future' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$products = array_map( 'intval', $old_batch_products );

	foreach ( $duplicate_products as $product_id ) {
		$product_id = (int) $product_id;
		$post       = get_post( $product_id );

		if ( ! $post ) {
			continue;
		}

		if ( in_array( (string) get_post_meta( $product_id, '_vpn_sample_import', true ), $keep_markers, true ) ) {
			continue;
		}

		if ( in_array( $post->post_name, $duplicate_slugs, true ) || in_array( $post->post_title, $duplicate_titles, true ) ) {
			$products[] = $product_id;
		}
	}

	$products = array_values( array_unique( $products ) );

	$trashed = 0;
	$failed  = array();

	foreach ( $products as $product_id ) {
		$result = wp_trash_post( (int) $product_id );
		if ( $result ) {
			++$trashed;
		} else {
			$failed[] = (int) $product_id;
		}
	}

	$output = 'Cleanup scope: old sample batches and known duplicate product concepts' . PHP_EOL;
	$output .= 'Kept markers: ' . implode( ', ', $keep_markers ) . PHP_EOL;
	$output .= 'Duplicate slugs checked: ' . implode( ', ', $duplicate_slugs ) . PHP_EOL;
	$output .= 'Trashed products: ' . $trashed . PHP_EOL;
	if ( $failed ) {
		$output .= 'Failed product IDs: ' . implode( ', ', $failed ) . PHP_EOL;
	}

	set_transient(
		'custom_box_product_sample_deploy_result_' . get_current_user_id(),
		array(
			'output' => $output,
			'error'  => '',
			'status' => 'complete',
			'time'   => current_time( 'mysql' ),
		),
		10 * MINUTE_IN_SECONDS
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'         => 'custom-box-product-sample-deploy',
				'cleanup_done' => '1',
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_custom_box_product_sample_cleanup_old_batches', 'custom_box_product_sample_cleanup_old_batches_admin_post' );

function custom_box_product_sample_category_balance_admin_post() {
	if ( ! custom_box_product_sample_deploy_can_run() ) {
		wp_die( esc_html__( 'You do not have permission to balance product categories.', 'custom-box-theme' ) );
	}

	check_admin_referer( 'custom_box_product_sample_category_balance' );

	$output = '';
	$error  = '';

	delete_transient( 'custom_box_product_sample_deploy_state_' . get_current_user_id() );

	if ( ! function_exists( 'custom_box_category_migration_apply_products_to_targets' ) ) {
		$error = 'Product category migration function is not available.';
	} else {
		$published = custom_box_product_sample_publish_category_balance_products();
		$updated = custom_box_category_migration_apply_products_to_targets();
		$output .= 'Published balanced sample products: ' . (int) $published . ' products.' . PHP_EOL;
		$output .= 'Product category balance updated: ' . (int) $updated . ' products.' . PHP_EOL . PHP_EOL;
		$output .= custom_box_product_sample_category_balance_report();
	}

	set_transient(
		'custom_box_product_sample_deploy_result_' . get_current_user_id(),
		array(
			'output' => $output,
			'error'  => $error,
			'status' => $error ? 'error' : 'complete',
			'time'   => current_time( 'mysql' ),
		),
		10 * MINUTE_IN_SECONDS
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'                  => 'custom-box-product-sample-deploy',
				'category_balance_done' => '1',
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_custom_box_product_sample_category_balance', 'custom_box_product_sample_category_balance_admin_post' );

function custom_box_product_sample_custom_vial_sync_admin_post() {
	if ( ! custom_box_product_sample_deploy_can_run() ) {
		wp_die( esc_html__( 'You do not have permission to sync this product.', 'custom-box-theme' ) );
	}

	check_admin_referer( 'custom_box_product_sample_custom_vial_sync' );

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 );
	}

	$output = '';
	$error  = '';

	delete_transient( 'custom_box_product_sample_deploy_state_' . get_current_user_id() );

	if ( ! function_exists( 'custom_box_sync_custom_vial_boxes_product' ) ) {
		$error = 'Custom vial boxes sync helper is not available.';
	} else {
		$product_id = custom_box_sync_custom_vial_boxes_product( true );

		if ( is_wp_error( $product_id ) ) {
			$error = $product_id->get_error_message();
		} elseif ( function_exists( 'custom_box_custom_vial_boxes_sync_report' ) ) {
			$output = custom_box_custom_vial_boxes_sync_report( (int) $product_id );
		} else {
			$output = 'Custom Vial Boxes product synced. Product ID: ' . (int) $product_id . PHP_EOL;
		}
	}

	set_transient(
		'custom_box_product_sample_deploy_result_' . get_current_user_id(),
		array(
			'output' => $output,
			'error'  => $error,
			'status' => $error ? 'error' : 'complete',
			'time'   => current_time( 'mysql' ),
		),
		10 * MINUTE_IN_SECONDS
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'                  => 'custom-box-product-sample-deploy',
				'custom_vial_sync_done' => '1',
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_custom_box_product_sample_custom_vial_sync', 'custom_box_product_sample_custom_vial_sync_admin_post' );

function custom_box_product_sample_category_balance_product_slugs(): array {
	if ( ! function_exists( 'custom_box_category_migration_explicit_product_map' ) ) {
		return array();
	}

	$slugs = array();

	foreach ( custom_box_category_migration_explicit_product_map() as $product_slug => $target_slugs ) {
		if ( in_array( 'fashion-sportswear-packaging', $target_slugs, true ) ) {
			continue;
		}

		$slugs[] = $product_slug;
	}

	return array_values( array_unique( $slugs ) );
}

function custom_box_product_sample_publish_category_balance_products(): int {
	$published = 0;

	foreach ( custom_box_product_sample_category_balance_product_slugs() as $slug ) {
		$products = get_posts(
			array(
				'name'           => $slug,
				'post_type'      => 'product',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $products[0] ) ) {
			continue;
		}

		$product_id = (int) $products[0];
		if ( 'publish' === get_post_status( $product_id ) ) {
			continue;
		}

		$updated = wp_update_post(
			array(
				'ID'          => $product_id,
				'post_status' => 'publish',
			),
			true
		);

		if ( ! is_wp_error( $updated ) && $updated ) {
			++$published;
		}
	}

	return $published;
}

function custom_box_product_sample_category_balance_targets(): array {
	if ( ! function_exists( 'custom_box_category_migration_targets' ) ) {
		return array();
	}

	$targets = custom_box_category_migration_targets();
	$balance_slugs = array(
		'pharmaceutical-packaging-boxes',
		'supplement-packaging-boxes',
		'beauty-skincare-packaging',
		'premium-food-beverage-packaging',
		'electronics-accessories-packaging',
		'wine-premium-drink-packaging',
		'corporate-gift-packaging',
		'home-lifestyle-packaging',
		'back-to-school-stationery-packaging',
	);

	return array_intersect_key( $targets, array_flip( $balance_slugs ) );
}

function custom_box_product_sample_category_balance_counts(): array {
	$counts = array();

	foreach ( custom_box_product_sample_category_balance_targets() as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );

		if ( ! $term || is_wp_error( $term ) ) {
			$counts[ $slug ] = array(
				'name'    => $name,
				'count'   => 0,
				'missing' => true,
			);
			continue;
		}

		$products = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => array( (int) $term->term_id ),
					),
				),
				'meta_query'     => array(
					array(
						'key'     => '_vpn_sample_import',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$counts[ $slug ] = array(
			'name'    => $name,
			'count'   => count( $products ),
			'missing' => false,
		);
	}

	return $counts;
}

function custom_box_product_sample_category_balance_report(): string {
	$lines = array( 'Category balance report, excluding Fashion and Sportswear Packaging:' );

	foreach ( custom_box_product_sample_category_balance_counts() as $slug => $data ) {
		$status  = $data['count'] >= 4 ? 'OK' : 'Needs more products';
		$status .= ! empty( $data['missing'] ) ? ' / category missing' : '';
		$lines[] = sprintf(
			'- %s (%s): %d sample products - %s',
			$data['name'],
			$slug,
			(int) $data['count'],
			$status
		);
	}

	return implode( PHP_EOL, $lines ) . PHP_EOL;
}

function custom_box_product_sample_deploy_batch_products( string $marker ): array {
	return get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'private', 'trash' ),
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => '_vpn_sample_import',
					'value' => $marker,
				),
			),
		)
	);
}

function custom_box_product_sample_deploy_batch_complete( string $marker, int $expected_count, int $min_words = 1500, string $expected_moq = '1000 boxes', string $expected_status = 'publish' ): bool {
	$products = custom_box_product_sample_deploy_batch_products( $marker );
	if ( count( $products ) !== $expected_count ) {
		return false;
	}

	foreach ( $products as $product ) {
		$content = (string) $product->post_content;
		$words   = str_word_count( wp_strip_all_tags( $content ) );
		$specs   = get_post_meta( $product->ID, '_custom_box_product_specs', true );
		$moq     = '';

		if ( is_array( $specs ) ) {
			foreach ( $specs as $row ) {
				if ( isset( $row['label'], $row['value'] ) && 'Minimum Order Quantity (MOQ)' === $row['label'] ) {
					$moq = $row['value'];
					break;
				}
			}
		}

		$status_ok = 'any' === $expected_status || $expected_status === get_post_status( $product->ID );
		if ( ! $status_ok || $words < $min_words || preg_match( '/<h1\b/i', $content ) || ! is_array( $specs ) || count( $specs ) < 21 || $expected_moq !== $moq || ! has_post_thumbnail( $product->ID ) ) {
			return false;
		}
	}

	return true;
}

function custom_box_product_sample_deploy_missing_source_images( array $batch ): array {
	if ( empty( $batch['source_dir'] ) || empty( $batch['source_image_bases'] ) || ! is_array( $batch['source_image_bases'] ) ) {
		return array();
	}

	$source_dir = trim( (string) $batch['source_dir'], '/\\' );
	$missing    = array();

	foreach ( $batch['source_image_bases'] as $base ) {
		for ( $image_number = 1; $image_number <= 4; ++$image_number ) {
			$relative = 'wp-content/uploads/' . $source_dir . '/' . $base . '-' . $image_number . '.webp';
			if ( ! file_exists( trailingslashit( ABSPATH ) . $relative ) ) {
				$missing[] = $relative;
			}
		}
	}

	return $missing;
}

function custom_box_product_sample_deploy_run_script( string $relative_script ): void {
	$script = trailingslashit( ABSPATH ) . ltrim( $relative_script, '/\\' );

	if ( ! file_exists( $script ) ) {
		throw new RuntimeException(
			'Missing deploy script: ' . $relative_script
			. ' | Checked path: ' . $script
			. ' | ABSPATH: ' . ABSPATH
			. ' | Current working directory: ' . getcwd()
		);
	}

	echo PHP_EOL . '>> ' . $relative_script . PHP_EOL;

	$previous_cwd = getcwd();
	chdir( ABSPATH );
	include $script;
	if ( $previous_cwd ) {
		chdir( $previous_cwd );
	}
}

function custom_box_product_sample_deploy_batches(): array {
	return array(
		array(
			'name'     => 'Batch 1 product samples',
			'marker'   => 'product-samples-10',
			'expected' => 10,
			'scripts'  => array(
				'tools/import-product-samples-10.php',
				'tools/update-product-samples-rich-content.php',
				'tools/rewrite-product-samples-batch-1.php',
				'tools/expand-product-samples-batch-1.php',
				'tools/top-up-product-samples-batch-1.php',
				'tools/trim-charging-cable-sample.php',
				'tools/verify-product-samples-10.php',
				'tools/verify-rich-product-samples-10.php',
				'tools/verify-product-samples-content-shape.php',
			),
		),
		array(
			'name'     => 'Batch 2 product samples',
			'marker'   => 'product-samples-batch-2-five',
			'expected' => 5,
			'scripts'  => array(
				'tools/import-product-samples-batch-2-five.php',
				'tools/top-up-product-samples-batch-2-five.php',
				'tools/top-up-product-samples-batch-2-five-final.php',
				'tools/top-up-product-samples-batch-2-two-text-floor.php',
				'tools/verify-product-samples-batch-2-five.php',
			),
		),
		array(
			'name'     => 'Batch 3 product samples',
			'marker'   => 'product-samples-batch-3-ten',
			'expected' => 10,
			'scripts'  => array(
				'tools/import-product-samples-batch-3-ten.php',
				'tools/top-up-product-samples-batch-3-ten.php',
				'tools/top-up-product-samples-batch-3-final.php',
				'tools/top-up-product-samples-batch-3-text-floor.php',
				'tools/fix-batch-3-inline-image-classes.php',
				'tools/verify-product-samples-batch-3-ten.php',
				'tools/verify-batch-3-inline-image-classes.php',
			),
		),
		array(
			'name'     => 'Batch 4 remaining product samples',
			'marker'   => 'product-samples-batch-4-remaining',
			'expected' => 17,
			'min_words' => 1000,
			'scripts'  => array(
				'tools/import-product-samples-batch-4-remaining.php',
				'tools/repair-product-samples-batch-4-featured-images.php',
				'tools/verify-product-samples-batch-4-remaining.php',
			),
		),
		array(
			'name'      => 'Fashion and Sportswear product samples',
			'marker'    => 'product-samples-fashion-sportswear',
			'expected'  => 6,
			'min_words' => 900,
			'always'    => true,
			'scripts'   => array(
				'tools/import-fashion-sportswear-products.php',
				'tools/verify-fashion-sportswear-products.php',
			),
		),
		array(
			'name'      => 'Sports Packaging product samples',
			'marker'    => 'product-samples-sports-packaging',
			'expected'  => 4,
			'min_words' => 900,
			'always'    => true,
			'scripts'   => array(
				'tools/import-sports-packaging-products.php',
				'tools/verify-sports-packaging-products.php',
			),
		),
		array(
			'name'      => 'Paper Egg Packaging product',
			'marker'    => 'paper-egg-packaging-product',
			'expected'  => 1,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-paper-egg-packaging-product.php',
			),
		),
		array(
			'name'      => 'Bird Nest Packaging products',
			'marker'    => 'product-samples-bird-nest-packaging',
			'expected'  => 4,
			'min_words' => 900,
			'scripts'   => array(
				'tools/import-bird-nest-packaging-products.php',
				'tools/verify-bird-nest-packaging-products.php',
			),
		),
		array(
			'name'      => 'Kraft Corrugated Mailer product',
			'marker'    => 'product-samples-kraft-corrugated-mailer',
			'expected'  => 1,
			'min_words' => 900,
			'scripts'   => array(
				'tools/import-kraft-corrugated-mailer-product.php',
				'tools/verify-kraft-corrugated-mailer-product.php',
			),
		),
		array(
			'name'      => 'Candle Packaging products',
			'marker'    => 'product-samples-candle-packaging',
			'expected'  => 3,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-candle-packaging-products.php',
				'tools/verify-candle-packaging-products.php',
			),
		),
		array(
			'name'      => 'Latest June 2026 product image samples',
			'marker'    => 'product-samples-latest-20260624',
			'expected'  => 7,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-latest-product-images.php',
				'tools/verify-latest-product-images.php',
			),
		),
		array(
			'name'      => 'Jewelry Paper Box products',
			'marker'    => 'product-samples-jewelry-paper-boxes',
			'expected'  => 4,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-jewelry-paper-box-products.php',
				'tools/verify-jewelry-paper-box-products.php',
			),
		),
		array(
			'name'      => 'Skincare Packaging products',
			'marker'    => 'product-samples-skincare-packaging',
			'expected'  => 10,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-skincare-packaging-products.php',
				'tools/verify-skincare-packaging-products.php',
			),
		),
		array(
			'name'      => 'Magnetic Closure Box products',
			'marker'    => 'product-samples-magnetic-closure-boxes',
			'expected'  => 10,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-magnetic-closure-products.php',
				'tools/verify-magnetic-closure-products.php',
			),
		),
		array(
			'name'      => 'Perfume Packaging products July 2026',
			'marker'    => 'product-samples-perfume-packaging-202607',
			'expected'  => 10,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-perfume-packaging-products.php',
				'tools/verify-perfume-packaging-products.php',
			),
		),
		array(
			'name'      => 'Corrugated Mailer products July 2026',
			'marker'    => 'product-samples-corrugated-mailers-202607',
			'expected'  => 10,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-corrugated-mailer-products.php',
				'tools/verify-corrugated-mailer-products.php',
			),
		),
		array(
			'name'      => 'Toy, Tea and Coffee, and Pet products July 2026',
			'marker'    => 'product-samples-three-new-categories-202607',
			'expected'  => 21,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-three-new-category-products.php',
				'tools/verify-three-new-category-products.php',
			),
		),
		array(
			'name'      => 'Paper Shopping Bag products July 2026',
			'marker'    => 'product-samples-paper-shopping-bags-202607',
			'expected'  => 6,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-paper-shopping-bag-products-202607.php',
				'tools/verify-paper-shopping-bag-products-202607.php',
			),
		),
		array(
			'name'      => 'Christmas Gift Box With Ribbon product July 2026',
			'marker'    => 'product-samples-christmas-gift-box-202607',
			'expected'  => 1,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-christmas-gift-box-with-ribbon-product.php',
				'tools/verify-christmas-gift-box-with-ribbon-product.php',
			),
		),
		array(
			'name'               => 'Pharmaceutical Packaging products August 2026',
			'marker'             => 'product-samples-pharmaceutical-packaging-202608',
			'expected'           => 10,
			'min_words'          => 1500,
			'source_dir'         => '2026/08',
			'source_image_bases' => array(
				'custom-autoinjector-pen-box',
				'custom-blister-pack-medicine-box',
				'custom-eye-drop-packaging-box',
				'custom-inhaler-packaging-box',
				'custom-liquid-medicine-bottle-box',
				'custom-nasal-spray-packaging-box',
				'custom-pharmaceutical-tube-box',
				'custom-prefilled-syringe-box',
				'custom-sachet-stick-pack-carton',
				'custom-transdermal-patch-box',
			),
			'scripts'            => array(
				'tools/import-pharmaceutical-packaging-products-202608.php',
				'tools/verify-pharmaceutical-packaging-products-202608.php',
			),
		),
		array(
			'name'               => 'Bird Nest Packaging products August 2026',
			'marker'             => 'product-samples-bird-nest-packaging-202608',
			'expected'           => 10,
			'min_words'          => 1500,
			'source_dir'         => '2026/08',
			'source_image_bases' => array(
				'custom-2-bottle-bird-nest-beverage-box',
				'custom-6-jar-bird-nest-magnetic-gift-box',
				'custom-8-jar-bird-nest-lid-and-base-gift-box',
				'custom-12-jar-double-layer-bird-nest-gift-box',
				'custom-bird-nest-bowl-and-spoon-gift-box',
				'custom-bird-nest-paper-tube-packaging',
				'custom-bird-nest-rock-sugar-gift-box',
				'custom-bird-nest-sachet-packaging-box',
				'custom-dried-bird-nest-window-display-box',
				'custom-single-jar-bird-nest-window-box',
			),
			'scripts'            => array(
				'tools/import-bird-nest-packaging-products-202608.php',
				'tools/verify-bird-nest-packaging-products-202608.php',
			),
		),
		array(
			'name'      => 'Custom Lunar New Year Gift Boxes product August 2026',
			'marker'    => 'product-samples-custom-lunar-new-year-gift-box-202608',
			'expected'  => 1,
			'min_words' => 1500,
			'scripts'   => array(
				'tools/import-custom-lunar-new-year-gift-box-product.php',
				'tools/verify-custom-lunar-new-year-gift-box-product.php',
			),
		),
		array(
			'name'            => 'Bread Bag SEO products September 2026',
			'marker'          => 'product-samples-bread-bags-202609',
			'expected'        => 8,
			'min_words'       => 800,
			'expected_moq'    => 'Project-based quotation; confirm quantity by RFQ',
			'expected_status'  => 'publish',
			'scripts'         => array(
				'tools/import-bread-bag-products-202609.php',
				'tools/verify-bread-bag-products-202609.php',
			),
		),
		array(
			'name'      => 'Current product category thumbnails August 2026',
			'marker'    => 'product-category-thumbnails-202608',
			'expected'  => 0,
			'min_words' => 0,
			'always'    => true,
			'scripts'   => array(
				'tools/sync-product-category-thumbnails-202608.php',
			),
		),
	);
}

function custom_box_product_sample_deploy_selected_batches( string $scope ): array {
	$batches = custom_box_product_sample_deploy_batches();

	if ( 'all' === $scope ) {
		return $batches;
	}

	if ( 'perfume_202607' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-perfume-packaging-202607' === $batch['marker'];
				}
			)
		);
	}

	if ( 'corrugated_202607' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-corrugated-mailers-202607' === $batch['marker'];
				}
			)
		);
	}

	if ( 'three_categories_202607' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-three-new-categories-202607' === $batch['marker'];
				}
			)
		);
	}

	if ( 'paper_bags_202607' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-paper-shopping-bags-202607' === $batch['marker'];
				}
			)
		);
	}

	if ( 'christmas_gift_box_202607' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-christmas-gift-box-202607' === $batch['marker'];
				}
			)
		);
	}

	if ( 'pharmaceutical_202608' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-pharmaceutical-packaging-202608' === $batch['marker'];
				}
			)
		);
	}

	if ( 'bird_nest_202608' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-bird-nest-packaging-202608' === $batch['marker'];
				}
			)
		);
	}

	if ( 'lunar_new_year_202608' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-custom-lunar-new-year-gift-box-202608' === $batch['marker'];
				}
			)
		);
	}

	if ( 'bread_bags_202609' === $scope ) {
		return array_values(
			array_filter(
				$batches,
				static function ( $batch ) {
					return isset( $batch['marker'] ) && 'product-samples-bread-bags-202609' === $batch['marker'];
				}
			)
		);
	}

	// The default button must deploy the complete current release. Keep
	// historical batches available through the explicit "all" scope, but do
	// not make a new release depend on an incomplete legacy batch.
	return array_values(
		array_filter(
			$batches,
			static function ( $batch ) {
				return isset( $batch['marker'] ) && in_array(
					$batch['marker'],
					array(
						'product-samples-pharmaceutical-packaging-202608',
						'product-samples-bird-nest-packaging-202608',
						'product-samples-custom-lunar-new-year-gift-box-202608',
						'product-samples-bread-bags-202609',
						'product-category-thumbnails-202608',
					),
					true
				);
			}
		)
	);
}

function custom_box_product_sample_deploy_allowed_scopes(): array {
	return array( 'latest', 'all', 'perfume_202607', 'corrugated_202607', 'three_categories_202607', 'paper_bags_202607', 'christmas_gift_box_202607', 'pharmaceutical_202608', 'bird_nest_202608', 'lunar_new_year_202608', 'bread_bags_202609' );
}

function custom_box_product_sample_deploy_run_next_step( array &$state ): void {
	$scope = isset( $state['scope'] ) ? sanitize_key( (string) $state['scope'] ) : 'latest';
	if ( ! in_array( $scope, custom_box_product_sample_deploy_allowed_scopes(), true ) ) {
		$scope = 'latest';
	}
	$batches = custom_box_product_sample_deploy_selected_batches( $scope );

	if ( empty( $state['scope_logged'] ) ) {
		echo 'Deploy scope: ' . $scope . PHP_EOL;
		$state['scope_logged'] = true;
	}

	while ( isset( $batches[ (int) $state['batch_index'] ] ) ) {
		$batch = $batches[ (int) $state['batch_index'] ];

		if ( 0 === (int) $state['script_index'] ) {
			echo PHP_EOL . '== ' . $batch['name'] . ' ==' . PHP_EOL;
			$min_words = $batch['min_words'] ?? 1500;

			if ( empty( $batch['always'] ) && custom_box_product_sample_deploy_batch_complete( $batch['marker'], $batch['expected'], $min_words, $batch['expected_moq'] ?? '1000 boxes', $batch['expected_status'] ?? 'publish' ) ) {
				echo 'Already complete, skipped.' . PHP_EOL;
				++$state['batch_index'];
				$state['script_index'] = 0;
				continue;
			}

			$missing_images = custom_box_product_sample_deploy_missing_source_images( $batch );
			if ( $missing_images ) {
				throw new RuntimeException(
					'Missing ' . count( $missing_images ) . ' required source image(s). '
					. 'Upload all August 2026 original WebP files before deploying. First missing file: '
					. $missing_images[0]
				);
			}

			if ( ! empty( $batch['source_image_bases'] ) ) {
				echo 'Source image preflight: ' . ( count( $batch['source_image_bases'] ) * 4 ) . '/' . ( count( $batch['source_image_bases'] ) * 4 ) . ' originals found.' . PHP_EOL;
			}
		}

		if ( isset( $batch['scripts'][ (int) $state['script_index'] ] ) ) {
			$script = $batch['scripts'][ (int) $state['script_index'] ];
			++$state['script_index'];
			custom_box_product_sample_deploy_run_script( $script );
			echo 'Step complete. Continuing in the next request.' . PHP_EOL;
			return;
		}

		echo 'Completed: ' . $batch['name'] . PHP_EOL;
		++$state['batch_index'];
		$state['script_index'] = 0;
	}

	if ( 'all' === $scope && empty( $state['guide_done'] ) ) {
		$state['guide_done'] = true;
		custom_box_product_sample_deploy_run_script( 'tools/import-final-category-products.php' );
		custom_box_product_sample_deploy_run_script( 'tools/create-local-packaging-materials-guide.php' );
		echo 'Step complete. Continuing in the next request.' . PHP_EOL;
		return;
	}

	if ( 'all' === $scope && empty( $state['category_migration_done'] ) && function_exists( 'custom_box_category_migration_apply_products_to_targets' ) ) {
		$state['category_migration_done'] = true;
		$published = custom_box_product_sample_publish_category_balance_products();
		$updated = custom_box_category_migration_apply_products_to_targets();
		echo 'Published balanced sample products: ' . (int) $published . ' products.' . PHP_EOL;
		echo 'Product category migration updated: ' . (int) $updated . ' products.' . PHP_EOL;
		echo 'Step complete. Continuing in the next request.' . PHP_EOL;
		return;
	}

	if ( 'all' === $scope && empty( $state['final_cleanup_done'] ) ) {
		$state['final_cleanup_done'] = true;
		custom_box_product_sample_deploy_run_script( 'tools/cleanup-final-category-products.php' );
		echo 'Step complete. Continuing in the next request.' . PHP_EOL;
		return;
	}

	$state['complete'] = true;
	echo PHP_EOL . 'Product sample deployment complete.' . PHP_EOL;
}

function custom_box_product_sample_deploy_run_batches(): void {
	$batches = custom_box_product_sample_deploy_selected_batches( 'latest' );

	foreach ( $batches as $batch ) {
		echo PHP_EOL . '== ' . $batch['name'] . ' ==' . PHP_EOL;
		$min_words = $batch['min_words'] ?? 1500;

		if ( empty( $batch['always'] ) && custom_box_product_sample_deploy_batch_complete( $batch['marker'], $batch['expected'], $min_words, $batch['expected_moq'] ?? '1000 boxes', $batch['expected_status'] ?? 'publish' ) ) {
			echo 'Already complete, skipped.' . PHP_EOL;
			continue;
		}

		$missing_images = custom_box_product_sample_deploy_missing_source_images( $batch );
		if ( $missing_images ) {
			throw new RuntimeException(
				'Missing ' . count( $missing_images ) . ' required source image(s). '
				. 'Upload all August 2026 original WebP files before deploying. First missing file: '
				. $missing_images[0]
			);
		}

		foreach ( $batch['scripts'] as $script ) {
			custom_box_product_sample_deploy_run_script( $script );
		}

		echo 'Completed: ' . $batch['name'] . PHP_EOL;
	}

	custom_box_product_sample_deploy_run_script( 'tools/import-final-category-products.php' );
	custom_box_product_sample_deploy_run_script( 'tools/create-local-packaging-materials-guide.php' );

	if ( function_exists( 'custom_box_category_migration_apply_products_to_targets' ) ) {
		$published = custom_box_product_sample_publish_category_balance_products();
		$updated = custom_box_category_migration_apply_products_to_targets();
		echo 'Published balanced sample products: ' . (int) $published . ' products.' . PHP_EOL;
		echo 'Product category migration updated: ' . (int) $updated . ' products.' . PHP_EOL;
	}

	custom_box_product_sample_deploy_run_script( 'tools/cleanup-final-category-products.php' );

	echo PHP_EOL . 'Product sample deployment complete.' . PHP_EOL;
}

function custom_box_product_sample_deploy_restore_tools() {
	$source_dir = get_template_directory() . '/inc/product-sample-deploy-tools';
	$target_dir = ABSPATH . 'tools';
	$source_required = $source_dir . '/import-product-samples-10.php';
	$required   = $target_dir . '/import-product-samples-10.php';
	$latest_scripts = array(
		'import-pharmaceutical-packaging-products-202608.php',
		'verify-pharmaceutical-packaging-products-202608.php',
		'import-bird-nest-packaging-products-202608.php',
		'verify-bird-nest-packaging-products-202608.php',
		'import-custom-lunar-new-year-gift-box-product.php',
		'verify-custom-lunar-new-year-gift-box-product.php',
		'sync-product-category-thumbnails-202608.php',
		'import-bread-bag-products-202609.php',
		'verify-bread-bag-products-202609.php',
	);
	$log        = array();

	if ( ! is_dir( $source_dir ) ) {
		return "Product deploy restore version: " . CUSTOM_BOX_PRODUCT_SAMPLE_DEPLOY_VERSION . PHP_EOL
			. "ABSPATH: " . ABSPATH . PHP_EOL
			. "Source bundle missing: " . $source_dir . PHP_EOL;
	}

	if ( ! is_dir( $target_dir ) && ! wp_mkdir_p( $target_dir ) ) {
		return "Product deploy restore version: " . CUSTOM_BOX_PRODUCT_SAMPLE_DEPLOY_VERSION . PHP_EOL
			. "ABSPATH: " . ABSPATH . PHP_EOL
			. "Cannot create target tools directory: " . $target_dir . PHP_EOL;
	}

	$files = glob( $source_dir . '/*.php' );
	if ( ! $files ) {
		return "Product deploy restore version: " . CUSTOM_BOX_PRODUCT_SAMPLE_DEPLOY_VERSION . PHP_EOL
			. "ABSPATH: " . ABSPATH . PHP_EOL
			. "No bundled PHP tools found in: " . $source_dir . PHP_EOL;
	}

	$log[] = 'Product deploy restore version: ' . CUSTOM_BOX_PRODUCT_SAMPLE_DEPLOY_VERSION;
	$log[] = 'ABSPATH: ' . ABSPATH;
	$log[] = 'Source bundle: ' . $source_dir;
	$log[] = 'Target tools: ' . $target_dir;
	$log[] = 'Target writable: ' . ( wp_is_writable( $target_dir ) ? 'yes' : 'no' );
	$log[] = 'Bundled tool count: ' . count( $files );
	$log[] = 'Required source script exists: ' . ( file_exists( $source_required ) ? 'yes' : 'no' );
	foreach ( $latest_scripts as $latest_script ) {
		$log[] = 'Latest source script exists (' . $latest_script . '): ' . ( file_exists( $source_dir . '/' . $latest_script ) ? 'yes' : 'no' );
	}

	foreach ( $files as $file ) {
		$target = trailingslashit( $target_dir ) . basename( $file );
		if ( file_exists( $target ) ) {
			$log[] = 'Already present: ' . basename( $file );
			continue;
		}
		if ( copy( $file, $target ) ) {
			$log[] = 'Copied: ' . basename( $file );
		} else {
			$log[] = 'Copy failed: ' . basename( $file ) . ' -> ' . $target;
		}
	}

	if ( ! file_exists( $required ) && file_exists( $source_required ) ) {
		if ( copy( $source_required, $required ) ) {
			$log[] = 'Required script copied explicitly: import-product-samples-10.php';
		} else {
			$log[] = 'Required script explicit copy failed: ' . $required;
		}
	}

	// Always refresh the current release scripts so a Git pull cannot leave an
	// older root tools copy active on hosting.
	foreach ( $latest_scripts as $latest_script ) {
		$source = $source_dir . '/' . $latest_script;
		$target = trailingslashit( $target_dir ) . $latest_script;
		if ( ! file_exists( $source ) ) {
			$log[] = 'Latest script missing from bundle: ' . $latest_script;
			continue;
		}
		if ( copy( $source, $target ) ) {
			$log[] = 'Latest script refreshed: ' . $latest_script;
		} else {
			$log[] = 'Latest script refresh failed: ' . $latest_script;
		}
	}

	$log[] = 'Required script exists after restore: ' . ( file_exists( $required ) ? 'yes' : 'no' );
	foreach ( $latest_scripts as $latest_script ) {
		$log[] = 'Latest script exists after restore (' . $latest_script . '): ' . ( file_exists( trailingslashit( $target_dir ) . $latest_script ) ? 'yes' : 'no' );
	}
	$log[] = '';

	return implode( PHP_EOL, $log ) . PHP_EOL;
}

function custom_box_product_sample_deploy_restore_assets() {
	$source_root         = get_template_directory() . '/inc/product-sample-deploy-assets/root';
	$source_uploads_root = get_template_directory() . '/inc/product-sample-deploy-assets/uploads';
	$target_uploads_root = ABSPATH . 'wp-content/uploads';
	$log                 = array();

	$log[] = 'Asset restore source root: ' . $source_root;
	$log[] = 'Asset restore source uploads root: ' . $source_uploads_root;
	$log[] = 'Asset restore target uploads root: ' . $target_uploads_root;

	if ( is_dir( $source_root ) ) {
		$root_files = glob( $source_root . '/*' );
		$log[]      = 'Root asset count: ' . ( $root_files ? count( $root_files ) : 0 );

		if ( $root_files ) {
			foreach ( $root_files as $file ) {
				$target = ABSPATH . basename( $file );
				if ( file_exists( $target ) ) {
					$log[] = 'Root asset already present: ' . basename( $file );
					continue;
				}
				if ( copy( $file, $target ) ) {
					$log[] = 'Copied root asset: ' . basename( $file );
				} else {
					$log[] = 'Copy root asset failed: ' . basename( $file );
				}
			}
		}
	} else {
		$log[] = 'Root asset source missing.';
	}

	if ( is_dir( $source_uploads_root ) ) {
		$image_count = 0;
		$copied      = 0;
		$failed      = array();
		$year_dirs   = glob( $source_uploads_root . '/*', GLOB_ONLYDIR );

		foreach ( $year_dirs ? $year_dirs : array() as $year_dir ) {
			$year       = basename( $year_dir );
			$month_dirs = glob( $year_dir . '/*', GLOB_ONLYDIR );

			foreach ( $month_dirs ? $month_dirs : array() as $month_dir ) {
				$month  = basename( $month_dir );
				$target = trailingslashit( $target_uploads_root ) . $year . '/' . $month;

				if ( ! is_dir( $target ) ) {
					wp_mkdir_p( $target );
				}

					$image_files = glob( $month_dir . '/*' );
					foreach ( $image_files ? $image_files : array() as $file ) {
					if ( ! is_file( $file ) ) {
						continue;
					}

					++$image_count;
					$target_file = trailingslashit( $target ) . basename( $file );
					if ( file_exists( $target_file ) ) {
						continue;
					}
					if ( copy( $file, $target_file ) ) {
						++$copied;
					} else {
						$failed[] = $year . '/' . $month . '/' . basename( $file );
					}
				}
			}
		}

		$log[] = 'Upload asset count: ' . $image_count;
		$log[] = 'Upload assets copied: ' . $copied;
		$log[] = 'Upload target root writable: ' . ( is_dir( $target_uploads_root ) && wp_is_writable( $target_uploads_root ) ? 'yes' : 'no' );
		if ( $failed ) {
			$log[] = 'Upload copy failures: ' . implode( ', ', array_slice( $failed, 0, 20 ) );
		}
	} else {
		$log[] = 'Upload asset root source missing.';
	}

	$log[] = 'Required sample data exists after restore: ' . ( file_exists( ABSPATH . 'product-samples-10.md' ) ? 'yes' : 'no' );
	$log[] = 'Required sample image exists after restore: ' . ( file_exists( ABSPATH . 'wp-content/uploads/2026/05/custom-ampoule-packaging-box-1.webp' ) ? 'yes' : 'no' );
	$log[] = 'Required magnetic image exists after restore: ' . ( file_exists( ABSPATH . 'wp-content/uploads/2026/07/custom-perfume-magnetic-closure-box-main.webp' ) ? 'yes' : 'no' );
	$log[] = 'Required three-category image exists after restore: ' . ( file_exists( ABSPATH . 'wp-content/uploads/2026/07/board-game-packaging-box-1.webp' ) ? 'yes' : 'no' );
	$log[] = 'Required paper bag image exists after restore: ' . ( file_exists( ABSPATH . 'wp-content/uploads/2026/07/custom-white-paper-shopping-bag-brown-rope-vpn240724-a-01.webp' ) ? 'yes' : 'no' );
	$log[] = 'Required Christmas gift box image exists after restore: ' . ( file_exists( ABSPATH . 'wp-content/uploads/2026/07/green-christmas-gift-box-with-ribbon.webp' ) ? 'yes' : 'no' );
	$august_found = 0;
	$august_total = 0;
	foreach ( custom_box_product_sample_deploy_selected_batches( 'latest' ) as $latest_batch ) {
		if ( empty( $latest_batch['source_image_bases'] ) ) {
			continue;
		}
		$august_total += count( $latest_batch['source_image_bases'] ) * 4;
		$august_found += ( count( $latest_batch['source_image_bases'] ) * 4 ) - count( custom_box_product_sample_deploy_missing_source_images( $latest_batch ) );
	}
	$log[] = 'Current August 2026 original images present: ' . $august_found . '/' . $august_total;
	$log[] = '';

	return implode( PHP_EOL, $log ) . PHP_EOL;
}

function custom_box_product_sample_deploy_page() {
	if ( ! custom_box_product_sample_deploy_can_run() ) {
		wp_die( esc_html__( 'You do not have permission to deploy product samples.', 'custom-box-theme' ) );
	}

	$result_key = 'custom_box_product_sample_deploy_result_' . get_current_user_id();
	$result     = get_transient( $result_key );

	if ( $result ) {
		delete_transient( $result_key );
	}
	?>
	<div class="wrap">
		<h1>Product Sample Deploy</h1>
		<p><strong>Tool version:</strong> <?php echo esc_html( CUSTOM_BOX_PRODUCT_SAMPLE_DEPLOY_VERSION ); ?></p>
		<p>This tool imports or updates the generated WooCommerce product sample batches from the Git-tracked deploy scripts and uploaded or bundled images.</p>
		<p><strong>Current latest release:</strong> 21 August 2026 products and 3 category thumbnails. Upload all 80 original WebP files to <code>wp-content/uploads/2026/08/</code> before running <strong>Latest batch only</strong>.</p>
		<p>It skips completed batches automatically, so it can be run after every deploy without creating duplicate products.</p>

		<?php if ( $result ) : ?>
			<?php if ( ! empty( $result['error'] ) ) : ?>
				<div class="notice notice-error">
					<p><strong>Deploy failed:</strong> <?php echo esc_html( $result['error'] ); ?></p>
				</div>
			<?php elseif ( ! empty( $result['status'] ) && 'running' === $result['status'] ) : ?>
				<div class="notice notice-info">
					<p><strong>Deploy is running.</strong> The next step will start automatically.</p>
				</div>
			<?php else : ?>
				<div class="notice notice-success">
					<p><strong>Deploy finished.</strong> Completed at <?php echo esc_html( $result['time'] ); ?>.</p>
				</div>
			<?php endif; ?>

			<h2>Deploy Log</h2>
			<textarea readonly rows="22" style="width:100%;font-family:Consolas,Monaco,monospace;"><?php echo esc_textarea( $result['output'] ); ?></textarea>
		<?php endif; ?>

		<h2>Custom Vial Boxes Sync</h2>
		<p>Use this after a Git deploy to update the Custom Vial Boxes product content, SEO meta, FAQ, schema, image alt text, and product page display settings without terminal access.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px;">
			<input type="hidden" name="action" value="custom_box_product_sample_custom_vial_sync">
			<?php wp_nonce_field( 'custom_box_product_sample_custom_vial_sync' ); ?>
			<?php submit_button( 'Sync Custom Vial Boxes Product', 'primary large', 'submit', false ); ?>
		</form>

		<h2>Perfume Packaging Sync</h2>
		<p>Use this after a Git deploy to import or update the 10 July 2026 perfume packaging products from the 40 uploaded source images.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px;">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<input type="hidden" name="reset" value="1">
			<input type="hidden" name="deploy_scope" value="perfume_202607">
			<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			<?php submit_button( 'Sync Perfume Packaging Products', 'primary large', 'submit', false ); ?>
		</form>

		<h2>Corrugated Mailer Sync</h2>
		<p>Use this after a Git deploy to import or update the 10 July 2026 corrugated mailer and shipping box products from the 40 uploaded source images.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px;">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<input type="hidden" name="reset" value="1">
			<input type="hidden" name="deploy_scope" value="corrugated_202607">
			<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			<?php submit_button( 'Sync Corrugated Mailer Products', 'primary large', 'submit', false ); ?>
		</form>

		<h2>Three New Product Categories Sync</h2>
		<p>Imports or updates the 21 July 2026 products for Toy and Game, Tea and Coffee, and Pet Product Packaging from 84 bundled source images.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px;">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<input type="hidden" name="reset" value="1">
			<input type="hidden" name="deploy_scope" value="three_categories_202607">
			<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			<?php submit_button( 'Sync 21 New Category Products', 'primary large', 'submit', false ); ?>
		</form>

		<h2>Paper Shopping Bag Sync</h2>
		<p>Imports or updates the 6 July 2026 paper shopping bag products from 27 bundled source images.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px;">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<input type="hidden" name="reset" value="1">
			<input type="hidden" name="deploy_scope" value="paper_bags_202607">
			<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			<?php submit_button( 'Sync 6 Paper Shopping Bag Products', 'primary large', 'submit', false ); ?>
		</form>

		<h2>Christmas Gift Box With Ribbon Sync</h2>
		<p>Imports or updates the July 2026 Christmas rigid gift box product from four bundled seasonal colorway images.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px;">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<input type="hidden" name="reset" value="1">
			<input type="hidden" name="deploy_scope" value="christmas_gift_box_202607">
			<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			<?php submit_button( 'Sync Christmas Gift Box Product', 'primary large', 'submit', false ); ?>
		</form>

		<h2>Custom Lunar New Year Gift Box Sync</h2>
		<p>Imports or updates the Custom Lunar New Year Gift Boxes product from five bundled WebP images and the approved SEO content package.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px;">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<input type="hidden" name="reset" value="1">
			<input type="hidden" name="deploy_scope" value="lunar_new_year_202608">
			<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			<?php submit_button( 'Sync Lunar New Year Gift Box Product', 'primary large', 'submit', false ); ?>
		</form>


		<h2>V2 Content Package Sync (11 Products)</h2>
		<p>Imports or updates the 11 SEO/GEO/AIO V2 product content pages securely.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px;">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy_v2">
			<?php wp_nonce_field( 'custom_box_product_sample_deploy_v2' ); ?>
			<?php submit_button( 'Sync 11 V2 Content Products', 'primary large', 'submit', false ); ?>
		</form>
		<hr>

		<?php $category_counts = custom_box_product_sample_category_balance_counts(); ?>
		<?php if ( $category_counts ) : ?>
			<h2>Sample Product Category Balance</h2>
			<p>Fashion and Sportswear Packaging is excluded. Each category below should have at least 4 recently imported sample products.</p>
			<table class="widefat striped" style="max-width:980px;">
				<thead>
					<tr>
						<th>Category</th>
						<th>Slug</th>
						<th>Sample products</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $category_counts as $slug => $data ) : ?>
						<?php $is_ok = (int) $data['count'] >= 4 && empty( $data['missing'] ); ?>
						<tr>
							<td><?php echo esc_html( $data['name'] ); ?></td>
							<td><code><?php echo esc_html( $slug ); ?></code></td>
							<td><?php echo esc_html( (string) (int) $data['count'] ); ?></td>
							<td>
								<?php if ( $is_ok ) : ?>
									<strong style="color:#008a20;">OK</strong>
								<?php else : ?>
									<strong style="color:#b32d2e;">Needs balance</strong>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;">
				<input type="hidden" name="action" value="custom_box_product_sample_category_balance">
				<?php wp_nonce_field( 'custom_box_product_sample_category_balance' ); ?>
				<?php submit_button( 'Run Product Category Balance', 'secondary large', 'submit', false ); ?>
			</form>
		<?php endif; ?>

		<hr>

		<?php
		$latest_batch_names = array_map(
			static function ( $batch ) {
				return isset( $batch['name'] ) ? $batch['name'] : '';
			},
			custom_box_product_sample_deploy_selected_batches( 'latest' )
		);
		$latest_batch_names = array_filter( $latest_batch_names );
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<input type="hidden" name="reset" value="1">
			<p>
				<label>
					<input type="radio" name="deploy_scope" value="latest" checked>
					Latest batch only<?php echo $latest_batch_names ? ' (' . esc_html( implode( ' + ', $latest_batch_names ) ) . ')' : ''; ?>
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="deploy_scope" value="all">
					All registered product batches
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="deploy_scope" value="bread_bags_202609">
					Bread Bag SEO products September 2026 only
				</label>
			</p>
			<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			<?php submit_button( 'Run Product Sample Deploy', 'primary large' ); ?>
		</form>

		<hr>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="custom_box_product_sample_cleanup_old_batches">
			<?php wp_nonce_field( 'custom_box_product_sample_cleanup_old_batches' ); ?>
			<p>This moves Batch 1, Batch 2, and known duplicate old product concepts to Trash, keeping the latest Batch 3 and Batch 4 products.</p>
			<?php submit_button( 'Move Old/Duplicate Sample Products to Trash', 'delete' ); ?>
		</form>

		<?php if ( $result && empty( $result['error'] ) && ! empty( $result['status'] ) && 'running' === $result['status'] ) : ?>
			<form id="custom-box-product-sample-deploy-next" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="custom_box_product_sample_deploy">
				<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			</form>
			<script>
				window.setTimeout(function () {
					var form = document.getElementById('custom-box-product-sample-deploy-next');
					if (form) {
						form.submit();
					}
				}, 1500);
			</script>
		<?php endif; ?>
	</div>
	<?php
}

add_action('admin_post_custom_box_product_sample_deploy_v2', 'custom_box_handle_v2_deploy');
function custom_box_handle_v2_deploy() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    check_admin_referer('custom_box_product_sample_deploy_v2');
    
    // Include WordPress admin header
    require_once ABSPATH . 'wp-admin/admin-header.php';
    
    // Simulate POST for the script
    $_POST['execute_v2_import'] = '1';
    
    echo '<div class="wrap">';
    echo '<h1>V2 Content Sync Results</h1>';
    
    // Capture script output
    ob_start();
    require_once ABSPATH . 'tools/deploy-v2-full.php';
    $output = ob_get_clean();
    
    echo $output;
    echo '<p><a href="' . admin_url('tools.php?page=custom-box-product-sample-deploy') . '" class="button">Back to Deploy Menu</a></p>';
    echo '</div>';
    
    // Include WordPress admin footer to make it look native
    require_once ABSPATH . 'wp-admin/admin-footer.php';
    exit;
}
