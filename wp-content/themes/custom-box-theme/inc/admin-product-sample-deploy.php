<?php
/**
 * Admin button for deploying generated WooCommerce product samples without SSH.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CUSTOM_BOX_PRODUCT_SAMPLE_DEPLOY_VERSION', '2026-06-03-category-balance-4' );

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
		@set_time_limit( 120 );
	}

	$result_key = 'custom_box_product_sample_deploy_result_' . get_current_user_id();
	$state_key  = 'custom_box_product_sample_deploy_state_' . get_current_user_id();

	if ( ! empty( $_POST['reset'] ) ) {
		delete_transient( $state_key );
	}

	$state = get_transient( $state_key );
	if ( ! is_array( $state ) ) {
		$scope = isset( $_POST['deploy_scope'] ) ? sanitize_key( wp_unslash( $_POST['deploy_scope'] ) ) : 'latest';
		if ( 'all' !== $scope ) {
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
	unset( $targets['fashion-sportswear-packaging'] );

	return $targets;
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
			'post_status'    => array( 'publish', 'draft', 'private' ),
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

function custom_box_product_sample_deploy_batch_complete( string $marker, int $expected_count, int $min_words = 1500 ): bool {
	$products = custom_box_product_sample_deploy_batch_products( $marker );
	if ( count( $products ) < $expected_count ) {
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

		if ( $words < $min_words || preg_match( '/<h1\b/i', $content ) || ! is_array( $specs ) || count( $specs ) < 21 || '1000 boxes' !== $moq || ! has_post_thumbnail( $product->ID ) ) {
			return false;
		}
	}

	return true;
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
			'scripts'  => array(
				'tools/import-product-samples-batch-4-remaining.php',
				'tools/repair-product-samples-batch-4-featured-images.php',
				'tools/verify-product-samples-batch-4-remaining.php',
			),
		),
	);
}

function custom_box_product_sample_deploy_selected_batches( string $scope ): array {
	$batches = custom_box_product_sample_deploy_batches();

	if ( 'all' === $scope ) {
		return $batches;
	}

	return array_slice( $batches, -1 );
}

function custom_box_product_sample_deploy_run_next_step( array &$state ): void {
	$scope  = isset( $state['scope'] ) && 'all' === $state['scope'] ? 'all' : 'latest';
	$batches = custom_box_product_sample_deploy_selected_batches( $scope );

	if ( empty( $state['scope_logged'] ) ) {
		echo 'Deploy scope: ' . $scope . PHP_EOL;
		$state['scope_logged'] = true;
	}

	while ( isset( $batches[ (int) $state['batch_index'] ] ) ) {
		$batch = $batches[ (int) $state['batch_index'] ];

		if ( 0 === (int) $state['script_index'] ) {
			echo PHP_EOL . '== ' . $batch['name'] . ' ==' . PHP_EOL;

			if ( custom_box_product_sample_deploy_batch_complete( $batch['marker'], $batch['expected'] ) ) {
				echo 'Already complete, skipped.' . PHP_EOL;
				++$state['batch_index'];
				$state['script_index'] = 0;
				continue;
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
		custom_box_product_sample_deploy_run_script( 'tools/create-local-packaging-materials-guide.php' );
		echo 'Step complete. Continuing in the next request.' . PHP_EOL;
		return;
	}

	if ( empty( $state['category_migration_done'] ) && function_exists( 'custom_box_category_migration_apply_products_to_targets' ) ) {
		$state['category_migration_done'] = true;
		$published = custom_box_product_sample_publish_category_balance_products();
		$updated = custom_box_category_migration_apply_products_to_targets();
		echo 'Published balanced sample products: ' . (int) $published . ' products.' . PHP_EOL;
		echo 'Product category migration updated: ' . (int) $updated . ' products.' . PHP_EOL;
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

		if ( custom_box_product_sample_deploy_batch_complete( $batch['marker'], $batch['expected'] ) ) {
			echo 'Already complete, skipped.' . PHP_EOL;
			continue;
		}

		foreach ( $batch['scripts'] as $script ) {
			custom_box_product_sample_deploy_run_script( $script );
		}

		echo 'Completed: ' . $batch['name'] . PHP_EOL;
	}

	custom_box_product_sample_deploy_run_script( 'tools/create-local-packaging-materials-guide.php' );

	if ( function_exists( 'custom_box_category_migration_apply_products_to_targets' ) ) {
		$published = custom_box_product_sample_publish_category_balance_products();
		$updated = custom_box_category_migration_apply_products_to_targets();
		echo 'Published balanced sample products: ' . (int) $published . ' products.' . PHP_EOL;
		echo 'Product category migration updated: ' . (int) $updated . ' products.' . PHP_EOL;
	}

	echo PHP_EOL . 'Product sample deployment complete.' . PHP_EOL;
}

function custom_box_product_sample_deploy_restore_tools() {
	$source_dir = get_template_directory() . '/inc/product-sample-deploy-tools';
	$target_dir = ABSPATH . 'tools';
	$source_required = $source_dir . '/import-product-samples-10.php';
	$required   = $target_dir . '/import-product-samples-10.php';
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

	foreach ( $files as $file ) {
		$target = trailingslashit( $target_dir ) . basename( $file );
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

	$log[] = 'Required script exists after restore: ' . ( file_exists( $required ) ? 'yes' : 'no' );
	$log[] = '';

	return implode( PHP_EOL, $log ) . PHP_EOL;
}

function custom_box_product_sample_deploy_restore_assets() {
	$source_root    = get_template_directory() . '/inc/product-sample-deploy-assets/root';
	$source_uploads = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/05';
	$target_uploads = ABSPATH . 'wp-content/uploads/2026/05';
	$log            = array();

	$log[] = 'Asset restore source root: ' . $source_root;
	$log[] = 'Asset restore source uploads: ' . $source_uploads;
	$log[] = 'Asset restore target uploads: ' . $target_uploads;

	if ( is_dir( $source_root ) ) {
		$root_files = glob( $source_root . '/*' );
		$log[]      = 'Root asset count: ' . ( $root_files ? count( $root_files ) : 0 );

		if ( $root_files ) {
			foreach ( $root_files as $file ) {
				$target = ABSPATH . basename( $file );
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

	if ( is_dir( $source_uploads ) ) {
		if ( ! is_dir( $target_uploads ) ) {
			wp_mkdir_p( $target_uploads );
		}

		$image_files = glob( $source_uploads . '/*' );
		$log[]       = 'Upload asset count: ' . ( $image_files ? count( $image_files ) : 0 );
		$log[]       = 'Upload target writable: ' . ( is_dir( $target_uploads ) && wp_is_writable( $target_uploads ) ? 'yes' : 'no' );

		if ( $image_files ) {
			foreach ( $image_files as $file ) {
				$target = trailingslashit( $target_uploads ) . basename( $file );
				if ( copy( $file, $target ) ) {
					$log[] = 'Copied upload asset: ' . basename( $file );
				} else {
					$log[] = 'Copy upload asset failed: ' . basename( $file );
				}
			}
		}
	} else {
		$log[] = 'Upload asset source missing.';
	}

	$log[] = 'Required sample data exists after restore: ' . ( file_exists( ABSPATH . 'product-samples-10.md' ) ? 'yes' : 'no' );
	$log[] = 'Required sample image exists after restore: ' . ( file_exists( ABSPATH . 'wp-content/uploads/2026/05/custom-ampoule-packaging-box-1.webp' ) ? 'yes' : 'no' );
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
		<p>This tool imports or updates the generated WooCommerce product sample batches from the Git-tracked deploy scripts and images.</p>
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

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<input type="hidden" name="reset" value="1">
			<p>
				<label>
					<input type="radio" name="deploy_scope" value="latest" checked>
					Latest batch only (Batch 4, 17 remaining products)
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="deploy_scope" value="all">
					All batches (42 sample products)
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
