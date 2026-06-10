<?php
/**
 * Deploy all generated WooCommerce product samples from Git-tracked scripts/assets.
 *
 * Usage after git pull on hosting:
 *   php tools/deploy-product-samples-all.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$root = dirname( __DIR__ );
chdir( $root );

function vpn_deploy_batch_products( string $marker ): array {
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

function vpn_deploy_batch_complete( string $marker, int $expected_count, int $min_words = 1500 ): bool {
	$products = vpn_deploy_batch_products( $marker );
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

		if ( $words < $min_words || preg_match( '/<h1\b/i', $content ) || ! is_array( $specs ) || count( $specs ) < 21 || '1000 boxes' !== $moq ) {
			return false;
		}
	}

	return true;
}

function vpn_deploy_run_script( string $relative_script ): void {
	global $root;

	$script = $root . '/' . $relative_script;
	if ( ! file_exists( $script ) ) {
		throw new RuntimeException(
			'Missing deploy script: ' . $relative_script
			. ' | Checked path: ' . $script
			. ' | Current working directory: ' . getcwd()
		);
	}

	echo PHP_EOL . '>> ' . $relative_script . PHP_EOL;

	if ( 'cli' === PHP_SAPI ) {
		$command = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $script );
		passthru( $command, $exit_code );

		if ( 0 !== $exit_code ) {
			throw new RuntimeException( 'Deploy script failed: ' . $relative_script );
		}

		return;
	}

	include $script;
}

$batches = array(
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
		'name'     => 'Fashion and Sportswear product samples',
		'marker'   => 'product-samples-fashion-sportswear',
		'expected' => 6,
		'min_words' => 900,
		'always'   => true,
		'scripts'  => array(
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
);

foreach ( $batches as $batch ) {
	echo PHP_EOL . '== ' . $batch['name'] . ' ==' . PHP_EOL;
	$min_words = $batch['min_words'] ?? 1500;

	if ( empty( $batch['always'] ) && vpn_deploy_batch_complete( $batch['marker'], $batch['expected'], $min_words ) ) {
		echo 'Already complete, skipped.' . PHP_EOL;
		continue;
	}

	foreach ( $batch['scripts'] as $script ) {
		vpn_deploy_run_script( $script );
	}

	if ( ! vpn_deploy_batch_complete( $batch['marker'], $batch['expected'], $min_words ) ) {
		throw new RuntimeException( 'Batch did not pass completion check: ' . $batch['name'] );
	}

	echo 'Completed: ' . $batch['name'] . PHP_EOL;
}

vpn_deploy_run_script( 'tools/import-final-category-products.php' );

if ( function_exists( 'custom_box_category_migration_apply_products_to_targets' ) ) {
	$published = function_exists( 'custom_box_product_sample_publish_category_balance_products' )
		? custom_box_product_sample_publish_category_balance_products()
		: 0;
	$updated = custom_box_category_migration_apply_products_to_targets();
	echo 'Published balanced sample products: ' . (int) $published . ' products.' . PHP_EOL;
	echo 'Product category migration updated: ' . (int) $updated . ' products.' . PHP_EOL;
}

vpn_deploy_run_script( 'tools/cleanup-final-category-products.php' );

echo PHP_EOL . 'Product sample deployment complete.' . PHP_EOL;
