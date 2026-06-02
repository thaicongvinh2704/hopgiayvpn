<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$slug = 'paper-materials-for-custom-paper-boxes';
$page = get_page_by_path( $slug, OBJECT, 'page' );

$content = <<<HTML
<h1>Paper Materials for Custom Paper Boxes</h1>
<p>This local guide supports product pages that mention paper material selection for custom packaging. It explains common options such as ivory paper, art paper, kraft paper, duplex board, corrugated paper, rigid greyboard, and specialty paper.</p>
<h2>Choosing the Right Paper</h2>
<p>Different products require different paper performance. Lightweight retail boxes often use ivory paper or duplex board. Premium gift boxes usually need rigid greyboard mounted with printed art paper. Eco-focused brands may prefer kraft paper, while fragile or export products can use corrugated structures and protective inserts.</p>
<h2>Production Considerations</h2>
<p>When choosing material, buyers should consider product weight, printing detail, finishing process, insert requirement, shipping method, and target order quantity. VPN Paper Box Manufacturer can recommend a suitable paper structure after reviewing the product size, artwork, and packaging goal.</p>
HTML;

$postarr = array(
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_title'   => 'Paper Materials for Custom Paper Boxes',
	'post_name'    => $slug,
	'post_content' => $content,
);

if ( $page ) {
	$postarr['ID'] = $page->ID;
	$page_id       = wp_update_post( $postarr, true );
} else {
	$page_id = wp_insert_post( $postarr, true );
}

if ( is_wp_error( $page_id ) ) {
	fwrite( STDERR, $page_id->get_error_message() . PHP_EOL );
	exit( 1 );
}

update_post_meta( $page_id, '_vpn_sample_import', 'product-samples-10-support-page' );

echo 'Support page ready: ' . get_permalink( $page_id ) . PHP_EOL;
