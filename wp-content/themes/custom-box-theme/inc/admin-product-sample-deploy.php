<?php
/**
 * Admin button for deploying generated WooCommerce product samples without SSH.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CUSTOM_BOX_PRODUCT_SAMPLE_DEPLOY_VERSION', '2026-06-02-restore-path-2' );

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

	$restore_log = custom_box_product_sample_deploy_restore_tools();
	$restore_log .= custom_box_product_sample_deploy_restore_assets();
	$script = ABSPATH . 'tools/deploy-product-samples-all.php';

	$output = '';
	$error  = '';

	if ( ! file_exists( $script ) ) {
		$error = 'Missing deploy script: tools/deploy-product-samples-all.php';
	} else {
		try {
			ob_start();
			echo $restore_log;
			include $script;
			$output = ob_get_clean();
		} catch ( Throwable $e ) {
			$output = ob_get_clean();
			$output = $restore_log . $output;
			$error  = $e->getMessage();
		}
	}

	set_transient(
		'custom_box_product_sample_deploy_result_' . get_current_user_id(),
		array(
			'output' => $output,
			'error'  => $error,
			'time'   => current_time( 'mysql' ),
		),
		10 * MINUTE_IN_SECONDS
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'        => 'custom-box-product-sample-deploy',
				'deploy_done' => '1',
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_custom_box_product_sample_deploy', 'custom_box_product_sample_deploy_admin_post' );

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
			<?php else : ?>
				<div class="notice notice-success">
					<p><strong>Deploy finished.</strong> Completed at <?php echo esc_html( $result['time'] ); ?>.</p>
				</div>
			<?php endif; ?>

			<h2>Deploy Log</h2>
			<textarea readonly rows="22" style="width:100%;font-family:Consolas,Monaco,monospace;"><?php echo esc_textarea( $result['output'] ); ?></textarea>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="custom_box_product_sample_deploy">
			<?php wp_nonce_field( 'custom_box_product_sample_deploy' ); ?>
			<?php submit_button( 'Run Product Sample Deploy', 'primary large' ); ?>
		</form>
	</div>
	<?php
}
