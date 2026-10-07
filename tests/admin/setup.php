<?php
require dirname(__DIR__).'/live-chat/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311'||wp_get_environment_type()!=='local')exit(1);
require_once ABSPATH.'wp-admin/includes/plugin.php';
$backup=dirname(__DIR__).'/live-chat/runtime/admin-original-environment.json';
if(($argv[1]??'')==='restore'){
 $state=json_decode(file_get_contents($backup),true);update_option('active_plugins',$state['plugins']);switch_theme($state['stylesheet']);echo "Restored dedicated test environment.\n";exit;
}
if(!file_exists($backup))file_put_contents($backup,wp_json_encode(['plugins'=>get_option('active_plugins'),'stylesheet'=>get_option('stylesheet')]));
foreach(['woocommerce/woocommerce.php','seo-by-rank-math/rank-math.php'] as $plugin){$r=activate_plugin($plugin);if(is_wp_error($r))throw new RuntimeException($r->get_error_message());}
update_option('rank_math_registration_skip',true,false);
switch_theme('custom-box-theme');echo "Prepared isolated admin environment with actual theme, WooCommerce and Rank Math.\n";
