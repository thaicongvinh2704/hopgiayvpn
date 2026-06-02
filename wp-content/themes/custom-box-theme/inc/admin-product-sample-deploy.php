<?php
/**
 * Admin button for deploying generated WooCommerce product samples without SSH.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

	$script = ABSPATH . 'tools/deploy-product-samples-all.php';
	if ( ! file_exists( $script ) ) {
		custom_box_product_sample_deploy_restore_tools();
	}

	$script = ABSPATH . 'tools/deploy-product-samples-all.php';

	$output = '';
	$error  = '';

	if ( ! file_exists( $script ) ) {
		$error = 'Missing deploy script: tools/deploy-product-samples-all.php';
	} else {
		try {
			ob_start();
			include $script;
			$output = ob_get_clean();
		} catch ( Throwable $e ) {
			$output = ob_get_clean();
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

	if ( ! is_dir( $source_dir ) ) {
		return;
	}

	if ( ! is_dir( $target_dir ) && ! wp_mkdir_p( $target_dir ) ) {
		return;
	}

	$files = glob( $source_dir . '/*.php' );
	if ( ! $files ) {
		return;
	}

	foreach ( $files as $file ) {
		$target = trailingslashit( $target_dir ) . basename( $file );
		if ( file_exists( $target ) ) {
			continue;
		}

		copy( $file, $target );
	}
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
