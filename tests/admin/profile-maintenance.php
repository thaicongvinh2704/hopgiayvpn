<?php
// Only the dedicated test installation, never the primary/local/production DB.
define('WP_ADMIN',true);
ob_start();
require dirname(__DIR__).'/live-chat/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311'||wp_get_environment_type()!=='local')exit(1);
wp_set_current_user(get_user_by('login','chat-test-manager')->ID);
$GLOBALS['pagenow']='index.php';
global $wpdb;
$callbacks=array('custom_box_maybe_sync_corrugated_mailer_boxes_category','custom_box_maybe_sync_folding_cartons_vietnam_category','custom_box_maybe_sync_rigid_box_manufacturer_vietnam_category','custom_box_maybe_sync_pizza_boxes_category','custom_box_maybe_sync_halloween_packaging_category','custom_box_maybe_sync_christmas_packaging_category','custom_box_run_search_indexing_sync');
$samples=[];
foreach(array('first','repeat') as $pass){
 foreach($callbacks as $callback){
  if(!function_exists($callback))continue;
  $start=hrtime(true);$q=$wpdb->num_queries;$callback();
  $samples[$pass][$callback]=array('queries'=>$wpdb->num_queries-$q,'ms'=>round((hrtime(true)-$start)/1e6,3));
 }
 $q=$wpdb->num_queries;$start=hrtime(true);custom_box_post_sync_files_to_load();
 $samples[$pass]['post_loader']=array('queries'=>$wpdb->num_queries-$q,'ms'=>round((hrtime(true)-$start)/1e6,3));
}
$report=array('mode'=>'isolated DB with incomplete category fixtures; custom shared admin callbacks only, not total page latency','plugins'=>get_option('active_plugins'),'samples'=>$samples);
$label=($argv[1]??'before')==='after'?'after':'before';
$destination=dirname(__DIR__,2).'/artifacts/admin-performance';if(!is_dir($destination))mkdir($destination,0777,true);
file_put_contents($destination.'/maintenance-'.$label.'.json',wp_json_encode($report,JSON_PRETTY_PRINT));
ob_end_clean();echo wp_json_encode($report,JSON_PRETTY_PRINT);
