<?php
$dir = dirname( __DIR__ ) . '/wp-content/uploads/2026/05';

$imported = array(
	'custom-ampoule-packaging-box',
	'custom-charging-cable-packaging-box',
	'custom-colored-pencil-packaging-box',
	'custom-corporate-gift-set-packaging-boxes',
	'custom-cosmetic-packaging-box',
	'custom-cosmetic-paper-bag',
	'custom-crayon-packaging-box',
	'custom-dinnerware-packaging-box',
	'custom-phone-case-packaging-box',
	'custom-supplement-drawer-packaging-box',
	'custom-double-wine-bottle-gift-box',
	'custom-drawer-gift-box',
	'custom-fountain-pen-gift-box',
	'custom-knife-set-packaging-box',
	'custom-kraft-paper-bag-for-supplement-packaging',
	'custom-luxury-gift-box-with-paper-bag',
	'custom-magnetic-gift-box',
	'custom-medical-kit-packaging-box',
	'custom-medical-kit-packaging-box-detail',
	'custom-medical-kit-packaging-box-inside',
	'custom-medical-kit-packaging-box-open',
	'custom-mug-packaging-box-with-window',
	'custom-paper-tube-food-packaging-box',
	'custom-paper-tube-food-packaging-box-detail',
	'custom-paper-tube-food-packaging-box-inside',
	'custom-paper-tube-food-packaging-box-open',
	'custom-phone-packaging-box-webp',
	'custom-pill-packaging-box',
	'custom-printed-corrugated-pet-food-box',
	'custom-rigid-gift-box',
	'custom-single-wine-bottle-gift-box',
);

$groups = array();

foreach ( glob( $dir . '/*' ) as $file ) {
	$name = basename( $file );
	if ( ! preg_match( '/\.(webp|jpg|jpeg|png)$/i', $name ) ) {
		continue;
	}

	$base = preg_replace( '/\.[^.]+$/', '', $name );
	if ( preg_match( '/-\d{2,4}x\d{2,4}$/', $base ) || preg_match( '/-scaled$/', $base ) ) {
		continue;
	}

	if ( 0 !== strpos( $base, 'custom-' ) ) {
		continue;
	}

	$slug = preg_replace( '/-\d+$/', '', $base );
	$groups[ $slug ][] = $name;
}

ksort( $groups );

$unused = array_values( array_diff( array_keys( $groups ), $imported ) );
$used   = array_values( array_intersect( array_keys( $groups ), $imported ) );

$unused_image_count = 0;
foreach ( $unused as $slug ) {
	$unused_image_count += count( $groups[ $slug ] );
}

echo 'Custom product groups found: ' . count( $groups ) . PHP_EOL;
echo 'Already imported groups: ' . count( $used ) . PHP_EOL;
echo 'Unused custom product groups: ' . count( $unused ) . PHP_EOL;
echo 'Unused original images in those groups: ' . $unused_image_count . PHP_EOL;
echo PHP_EOL;
echo 'Unused groups:' . PHP_EOL;

foreach ( $unused as $slug ) {
	echo '- ' . $slug . ' (' . count( $groups[ $slug ] ) . ' images)' . PHP_EOL;
}
