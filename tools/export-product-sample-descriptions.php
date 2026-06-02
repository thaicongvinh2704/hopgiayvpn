<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page' => 20,
		'meta_key'       => '_vpn_sample_import',
		'meta_value'     => 'product-samples-10',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

$output = "# Product Detailed Descriptions - Text Only\n\n";
$output .= "Exported from local WooCommerce sample products. Images and captions are removed for content review.\n\n";

foreach ( $products as $product ) {
	$content = $product->post_content;
	$content = preg_replace( '/<figure\b[^>]*>.*?<\/figure>/is', '', $content );
	$content = preg_replace( '/<img\b[^>]*>/is', '', $content );
	$content = preg_replace( '/<figcaption\b[^>]*>.*?<\/figcaption>/is', '', $content );
	$content = trim( preg_replace( "/\n{3,}/", "\n\n", $content ) );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );

	$output .= "## {$product->post_title}\n\n";
	$output .= "- Product ID: {$product->ID}\n";
	$output .= "- URL: " . get_permalink( $product->ID ) . "\n";
	$output .= "- Word count without images/captions: {$word_count}\n\n";
	$output .= $content . "\n\n";
	$output .= "---\n\n";
}

$file = dirname( __DIR__ ) . '/product-samples-10-descriptions-text-only.md';
file_put_contents( $file, $output );

echo "Exported " . count( $products ) . " products to {$file}\n";
