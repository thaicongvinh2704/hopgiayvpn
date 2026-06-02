<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$products = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'   => '_vpn_sample_import',
				'value' => 'product-samples-batch-2-five',
			),
		),
	)
);

$out = array();
$out[] = '# Batch 2 Five Product Descriptions Text Only';
$out[] = '';
$out[] = 'Generated from local WooCommerce products. Images are removed here so the writing can be reviewed for duplicate risk.';
$out[] = '';

foreach ( $products as $post ) {
	$content = preg_replace( '/<figure\b[^>]*>.*?<\/figure>/is', '', (string) $post->post_content );
	$text    = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $content ) ) );
	$words   = str_word_count( $text );

	$out[] = '## ' . $post->post_title;
	$out[] = '';
	$out[] = '- URL: ' . get_permalink( $post );
	$out[] = '- Word count without in-content images: ' . $words;
	$out[] = '- Duplicate risk score: ' . get_post_meta( $post->ID, '_vpn_duplicate_risk_score', true ) . '/10';
	$details = get_post_meta( $post->ID, '_vpn_product_specific_details', true );
	if ( is_array( $details ) ) {
		$details = implode( ', ', array_filter( array_map( 'strval', $details ) ) );
	}
	$out[] = '- Product-specific details: ' . $details;
	$out[] = '';
	$out[] = '### Short Description';
	$out[] = '';
	$out[] = trim( wp_strip_all_tags( (string) $post->post_excerpt ) );
	$out[] = '';
	$out[] = '### Long Description';
	$out[] = '';
	$out[] = $text;
	$out[] = '';
}

file_put_contents( dirname( __DIR__ ) . '/product-samples-batch-2-five-descriptions-text-only.md', implode( PHP_EOL, $out ) );

echo 'Exported product-samples-batch-2-five-descriptions-text-only.md' . PHP_EOL;
