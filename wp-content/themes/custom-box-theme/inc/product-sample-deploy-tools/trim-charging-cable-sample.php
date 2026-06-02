<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$post = get_page_by_path( 'custom-charging-cable-packaging-box', OBJECT, 'product' );

if ( ! $post ) {
	exit( 1 );
}

$content = $post->post_content;
$remove  = '<p>For a 1000-box starting order, buyers should focus first on structure and artwork accuracy, then decide which premium finish is worth adding. Once the structure works, upgrades such as foil stamping, embossing, special paper, windows, ribbons, or reinforced inserts can be added with much lower risk.</p>';
$content = str_replace( $remove, '', $content );
$content = str_replace(
	'This avoids a common problem where a quotation is based on a beautiful reference image but the real product requires a different dieline, insert, or packing method.',
	'This prevents quoting from a reference image that does not match the real cable, insert, or packing method.',
	$content
);
$content = preg_replace(
	'/<p>A cable brand may sell the same structure in one-meter, two-meter, and three-meter versions, or in black, white, and braided color lines\..*?<\/p>/s',
	'',
	$content,
	1
);

wp_update_post(
	array(
		'ID'           => $post->ID,
		'post_content' => $content,
	)
);

echo 'Trimmed charging cable sample.' . PHP_EOL;
