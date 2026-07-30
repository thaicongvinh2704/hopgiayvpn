<?php
/**
 * Deploy the current Git-tracked product sample batches after git pull.
 *
 * Usage:
 *   php tools/deploy-product-samples-all.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

$source_dir = get_template_directory() . '/inc/product-sample-deploy-tools';
$target_dir = __DIR__;

if ( ! is_dir( $source_dir ) ) {
	fwrite( STDERR, 'Missing bundled product deploy tools: ' . $source_dir . PHP_EOL );
	exit( 1 );
}

foreach ( glob( $source_dir . '/*.php' ) ?: array() as $source_file ) {
	if ( 'deploy-product-samples-all.php' === basename( $source_file ) ) {
		continue;
	}
	$target_file = $target_dir . '/' . basename( $source_file );
	if ( ! copy( $source_file, $target_file ) ) {
		fwrite( STDERR, 'Could not restore deploy tool: ' . basename( $source_file ) . PHP_EOL );
		exit( 1 );
	}
}

require $target_dir . '/import-christmas-gift-box-with-ribbon-product.php';
require $target_dir . '/verify-christmas-gift-box-with-ribbon-product.php';

echo PHP_EOL . 'Current Christmas gift box product sample deployment complete.' . PHP_EOL;
