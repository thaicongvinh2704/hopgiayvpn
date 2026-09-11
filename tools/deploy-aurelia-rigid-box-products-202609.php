<?php
/**
 * One-command deploy for the three September 2026 custom rigid-box products.
 *
 * Usage after git pull:
 *   php tools/deploy-aurelia-rigid-box-products-202609.php
 */

require_once dirname( __DIR__ ) . '/wp-load.php';

$bundle = get_template_directory() . '/inc/product-sample-deploy-tools';
$scripts = array(
	'import-aurelia-rigid-box-products-202609.php',
	'verify-aurelia-rigid-box-products-202609.php',
);

foreach ( $scripts as $script ) {
	$path = $bundle . '/' . $script;
	if ( ! file_exists( $path ) ) {
		fwrite( STDERR, 'Missing bundled deploy script: ' . $path . PHP_EOL );
		exit( 1 );
	}
	require $path;
}

echo 'Aurelia rigid-box product deployment complete.' . PHP_EOL;
