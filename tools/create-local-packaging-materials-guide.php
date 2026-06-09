<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$page = get_page_by_path( 'paper-materials-for-custom-paper-boxes', OBJECT, 'page' );

if ( !$page ) {
	echo 'Obsolete support page is already absent.' . PHP_EOL;
	exit( 0 );
}

if ( !wp_delete_post( $page->ID, true ) ) {
	fwrite( STDERR, 'Could not delete the obsolete support page.' . PHP_EOL );
	exit( 1 );
}

echo 'Deleted obsolete support page: paper-materials-for-custom-paper-boxes' . PHP_EOL;
