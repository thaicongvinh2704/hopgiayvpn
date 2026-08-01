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

$current_batches = array(
	array(
		'name'        => 'Pharmaceutical Packaging products August 2026',
		'source_dir'  => '2026/08',
		'image_bases' => array(
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
		'importer'    => 'import-pharmaceutical-packaging-products-202608.php',
		'verifier'    => 'verify-pharmaceutical-packaging-products-202608.php',
	),
	array(
		'name'        => 'Bird Nest Packaging products August 2026',
		'source_dir'  => '2026/08',
		'image_bases' => array(
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
		'importer'    => 'import-bird-nest-packaging-products-202608.php',
		'verifier'    => 'verify-bird-nest-packaging-products-202608.php',
	),
);

$missing_images = array();
foreach ( $current_batches as $batch ) {
	foreach ( $batch['image_bases'] as $base ) {
		for ( $image_number = 1; $image_number <= 4; ++$image_number ) {
			$relative = 'wp-content/uploads/' . $batch['source_dir'] . '/' . $base . '-' . $image_number . '.webp';
			if ( ! file_exists( trailingslashit( ABSPATH ) . $relative ) ) {
				$missing_images[] = $relative;
			}
		}
	}
}

if ( $missing_images ) {
	fwrite(
		STDERR,
		'Missing ' . count( $missing_images ) . ' of 80 required August 2026 source images.' . PHP_EOL
		. 'Upload all originals before deploying. First missing file: ' . $missing_images[0] . PHP_EOL
	);
	exit( 1 );
}

echo 'Source image preflight: 80/80 originals found.' . PHP_EOL;

foreach ( $current_batches as $batch ) {
	echo PHP_EOL . '== ' . $batch['name'] . ' ==' . PHP_EOL;
	require $target_dir . '/' . $batch['importer'];
	require $target_dir . '/' . $batch['verifier'];
}

echo PHP_EOL . '== Current product category thumbnails August 2026 ==' . PHP_EOL;
require $target_dir . '/sync-product-category-thumbnails-202608.php';

echo PHP_EOL . 'Current August 2026 deployment complete: 20 products and 3 category thumbnails verified.' . PHP_EOL;
