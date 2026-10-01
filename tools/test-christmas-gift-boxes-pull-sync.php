<?php
// Exercise authenticated pull sync without running unrelated historical syncs.
define('WP_ADMIN', true);
require dirname(__DIR__) . '/wp-load.php';
if (!in_array(parse_url(home_url(), PHP_URL_HOST), array('localhost', '127.0.0.1'), true)) { throw new RuntimeException('Run the mutation/repair test only on local WordPress.'); }
$admins = get_users(array('role'=>'administrator','number'=>1,'fields'=>'ID'));
wp_set_current_user((int)$admins[0]);
require_once get_template_directory().'/inc/christmas-gift-boxes-20261001-product-sync.php';
$entry=custom_box_post_sync_registry()['inc/christmas-gift-boxes-20261001-product-sync.php'];
delete_option(VPN_XMAS_20261001_OPTION);
if(custom_box_post_sync_files_to_load()[0] !== 'inc/christmas-gift-boxes-20261001-product-sync.php')throw new RuntimeException('New release is not selected first.');
custom_box_sync_christmas_20261001_products();
if(get_option(VPN_XMAS_20261001_OPTION)!==VPN_XMAS_20261001_RELEASE)throw new RuntimeException('Pull sync did not finish.');
// Same process, same functions: repeated sync must be safe and avoid duplicates.
custom_box_sync_christmas_20261001_products();
if(custom_box_christmas_20261001_validate())throw new RuntimeException('Saved release incomplete.');
foreach($entry['slugs'] as $slug){$p=get_page_by_path($slug,OBJECT,'product');$GLOBALS['pagenow']='post.php';$_GET['post']=$p->ID;if(custom_box_post_sync_requested_slug(custom_box_post_sync_registry())!==$slug)throw new RuntimeException('Editing slug not recognized.');}
// Integrity guard catches corruption; restore canonical content after the check.
$p=get_page_by_path($entry['slug'],OBJECT,'product');$original=$p->post_content;
try{wp_update_post(array('ID'=>$p->ID,'post_content'=>$original.'<p>Integrity probe.</p>'));if(!custom_box_christmas_20261001_validate())throw new RuntimeException('Corruption was not detected.');custom_box_sync_christmas_20261001_products();if(custom_box_christmas_20261001_validate())throw new RuntimeException('Repair did not finish.');}finally{if(get_post_field('post_content',$p->ID)!==$original){wp_update_post(array('ID'=>$p->ID,'post_content'=>$original));update_post_meta($p->ID,'_vpn_christmas_20261001_content_hash',hash('sha256',(string)get_post_field('post_content',$p->ID)));}}
echo 'PASS: first authenticated pull sync, repeat, five edit slugs, corruption detection and repair.',PHP_EOL;
