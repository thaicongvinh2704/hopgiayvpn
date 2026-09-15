<?php
/**
 * One-command deploy for the September 2026 rigid paperboard magazine file holder product.
 *
 * Usage after git pull:
 *   php tools/deploy-rigid-paperboard-magazine-file-holder-202609.php
 */

require_once dirname( __DIR__ ) . '/wp-load.php';

$bundle = get_template_directory() . '/inc/product-sample-deploy-tools';
$scripts = array(
	'import-rigid-paperboard-magazine-file-holder-202609.php',
	'verify-rigid-paperboard-magazine-file-holder-202609.php',
);

foreach ( $scripts as $script ) {
	$path = $bundle . '/' . $script;
	if ( ! file_exists( $path ) ) {
		fwrite( STDERR, 'Missing bundled deploy script: ' . $path . PHP_EOL );
		exit( 1 );
	}
	require $path;
}

echo 'Rigid paperboard magazine file holder deployment complete.' . PHP_EOL;
