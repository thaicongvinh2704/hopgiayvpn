<?php
/**
 * Build the theme-bundled product sample deploy assets.
 *
 * This keeps the admin deploy tool self-contained on hosting without bundling
 * unrelated uploads.
 */

$root = dirname( __DIR__ );

$source_files = array(
	$root . '/product-samples-10.md',
	$root . '/tools/import-product-samples-batch-2-five.php',
	$root . '/tools/import-product-samples-batch-3-ten.php',
	$root . '/tools/import-product-samples-batch-4-remaining.php',
	$root . '/tools/import-fashion-sportswear-products.php',
	$root . '/tools/verify-fashion-sportswear-products.php',
	$root . '/tools/import-sports-packaging-products.php',
	$root . '/tools/verify-sports-packaging-products.php',
	$root . '/tools/import-candle-packaging-products.php',
	$root . '/tools/verify-candle-packaging-products.php',
);

$asset_root    = $root . '/wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets/root';
$asset_uploads = $root . '/wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets/uploads/2026/05';
$sports_uploads = $root . '/wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets/uploads/2026/06';
$tool_bundle     = $root . '/wp-content/themes/custom-box-theme/inc/product-sample-deploy-tools';

if ( ! is_dir( $asset_root ) && ! mkdir( $asset_root, 0777, true ) ) {
	fwrite( STDERR, "Unable to create asset root: {$asset_root}\n" );
	exit( 1 );
}

if ( ! is_dir( $asset_uploads ) && ! mkdir( $asset_uploads, 0777, true ) ) {
	fwrite( STDERR, "Unable to create upload asset directory: {$asset_uploads}\n" );
	exit( 1 );
}

if ( ! is_dir( $sports_uploads ) && ! mkdir( $sports_uploads, 0777, true ) ) {
	fwrite( STDERR, "Unable to create sports upload asset directory: {$sports_uploads}\n" );
	exit( 1 );
}

if ( ! is_dir( $tool_bundle ) && ! mkdir( $tool_bundle, 0777, true ) ) {
	fwrite( STDERR, "Unable to create tool bundle directory: {$tool_bundle}\n" );
	exit( 1 );
}

$real_asset_uploads = realpath( $asset_uploads );
$expected_prefix    = realpath( $root . '/wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets' );

if ( false === $real_asset_uploads || false === $expected_prefix || 0 !== strpos( $real_asset_uploads, $expected_prefix ) ) {
	fwrite( STDERR, "Refusing to clean unexpected asset directory: {$asset_uploads}\n" );
	exit( 1 );
}

$existing = glob( $asset_uploads . '/*' );
if ( false !== $existing ) {
	foreach ( $existing as $file ) {
		if ( is_file( $file ) && ! unlink( $file ) ) {
			fwrite( STDERR, "Unable to remove stale asset: {$file}\n" );
			exit( 1 );
		}
	}
}

if ( ! copy( $root . '/product-samples-10.md', $asset_root . '/product-samples-10.md' ) ) {
	fwrite( STDERR, "Unable to copy product-samples-10.md\n" );
	exit( 1 );
}

$paths = array();
foreach ( $source_files as $source_file ) {
	if ( ! file_exists( $source_file ) ) {
		fwrite( STDERR, "Missing source file: {$source_file}\n" );
		exit( 1 );
	}

	$content = file_get_contents( $source_file );
	if ( false === $content ) {
		fwrite( STDERR, "Unable to read source file: {$source_file}\n" );
		exit( 1 );
	}

	if ( preg_match_all( '#wp-content/uploads/2026/05/[A-Za-z0-9._-]+\.(?:webp|jpe?g|png)#i', $content, $matches ) ) {
		foreach ( $matches[0] as $match ) {
			$paths[ $match ] = true;
		}
	}
}

ksort( $paths );

$copied = 0;
foreach ( array_keys( $paths ) as $relative_path ) {
	$source = $root . '/' . $relative_path;
	$target = $asset_uploads . '/' . basename( $relative_path );

	if ( ! file_exists( $source ) ) {
		fwrite( STDERR, "Missing source image: {$relative_path}\n" );
		exit( 1 );
	}

	if ( ! copy( $source, $target ) ) {
		fwrite( STDERR, "Unable to copy image: {$relative_path}\n" );
		exit( 1 );
	}

	++$copied;
}

$sports_filenames = array(
	'custom-sports-shoe-packaging-box-01-hero.webp',
	'custom-sports-shoe-packaging-box-02-angle-view.webp',
	'custom-sports-shoe-packaging-box-03-open-box.webp',
	'custom-sports-shoe-packaging-box-04-detail-closeup.webp',
	'premium-pickleball-set-rigid-paper-box-01-hero.webp',
	'premium-pickleball-set-rigid-paper-box-02-angle-view.webp',
	'premium-pickleball-set-rigid-paper-box-03-open-box-with-foam-insert.webp',
	'premium-pickleball-set-rigid-paper-box-04-detail-closeup.webp',
	'custom-knee-support-packaging-box-front.webp',
	'custom-knee-support-packaging-box-front-1.webp',
	'custom-knee-support-packaging-box-front-3.webp',
	'custom-knee-support-packaging-box-front-4.webp',
	'custom-sports-underwear-packaging-box-front.webp',
	'custom-sports-underwear-packaging-box-front-2.webp',
	'custom-sports-underwear-packaging-box-front-3.webp',
	'custom-sports-underwear-packaging-box-front-4.webp',
	'custom-sports-underwear-packaging-box-front-5.webp',
	'custom-sports-underwear-packaging-box-front-6.webp',
);

$sports_copied = 0;
foreach ( $sports_filenames as $filename ) {
	$source = $root . '/wp-content/uploads/2026/06/' . $filename;
	$target = $sports_uploads . '/' . $filename;

	if ( ! file_exists( $source ) || ! copy( $source, $target ) ) {
		fwrite( STDERR, "Unable to bundle sports image: {$filename}\n" );
		exit( 1 );
	}

	++$sports_copied;
}

$tool_files = array(
	$root . '/tools/import-sports-packaging-products.php',
	$root . '/tools/verify-sports-packaging-products.php',
	$root . '/tools/deploy-product-samples-all.php',
	$root . '/tools/import-candle-packaging-products.php',
	$root . '/tools/verify-candle-packaging-products.php',
);

foreach ( $tool_files as $tool_file ) {
	if ( ! copy( $tool_file, $tool_bundle . '/' . basename( $tool_file ) ) ) {
		fwrite( STDERR, 'Unable to bundle tool: ' . basename( $tool_file ) . "\n" );
		exit( 1 );
	}
}

echo "Root assets: product-samples-10.md\n";
echo "Upload assets copied: {$copied}\n";
echo "Sports upload assets copied: {$sports_copied}\n";
echo 'Sports deploy tools bundled: ' . count( $tool_files ) . "\n";
