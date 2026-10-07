<?php
define('WP_ADMIN',true);
ob_start();
require dirname(__DIR__).'/live-chat/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311'||wp_get_environment_type()!=='local')exit(1);
global $wpdb;
wp_set_current_user(get_user_by('login','chat-test-manager')->ID);
$passed=[];$created=[];$network=0;$mockStatus=503;$samples=[];
function adm_check($v,$label){global $passed;if(!$v)throw new RuntimeException($label);$passed[]=$label;}
function task_key($task,$file){return 'custom_box_admin_task_'.md5($task.':'.filemtime($file).':'.filesize($file));}
$version=custom_box_search_indexing_sync_version();$hook='custom_box_search_indexing_submit';$args=[$version];
$live=static function($url){return preg_replace('~^http://127\.0\.0\.1:8091~','https://hopgiayvpn.com',$url);};
try{
 $file=get_template_directory().'/inc/admin-maintenance.php';$key=task_key('admin-fixture',$file);delete_transient($key);
 adm_check(custom_box_admin_task_due('admin-fixture',$file),'maintenance initially runs for administrator');
 adm_check(!custom_box_admin_task_due('admin-fixture',$file),'repeat navigation backs off within maintenance interval');
 delete_transient($key);adm_check(custom_box_admin_task_due('admin-fixture',$file),'expired maintenance interval runs again');
 wp_set_current_user(get_user_by('login','agent-a')->ID);adm_check(!custom_box_admin_task_due('admin-fixture-other',$file),'sales cannot trigger shared maintenance');
 wp_set_current_user(get_user_by('login','chat-test-manager')->ID);
 $ajax=static fn()=>true;add_filter('wp_doing_ajax',$ajax);adm_check(!custom_box_admin_task_due('ajax',$file),'AJAX skips shared maintenance');remove_filter('wp_doing_ajax',$ajax);
 $_GET['custom_box_run_post_syncs']='1';adm_check(custom_box_admin_task_due('admin-fixture',$file),'explicit deployment trigger bypasses the interval');unset($_GET['custom_box_run_post_syncs']);

 $id=wp_insert_post(['post_title'=>'Admin performance self-parent fixture','post_type'=>'page','post_status'=>'draft']);$created[]=$id;
 $wpdb->update($wpdb->posts,['post_parent'=>$id],['ID'=>$id]);clean_post_cache($id);
 delete_option('custom_box_self_parent_repair_version');delete_transient(task_key('custom_box_repair_self_parent_posts_once',$file));
 custom_box_repair_self_parent_posts_once();adm_check((int)get_post_field('post_parent',$id)===0,'one-time repair fixes a corrupt self-parent record');
 $q=$wpdb->num_queries;custom_box_repair_self_parent_posts_once();adm_check($wpdb->num_queries===$q,'completed repair does not scan the posts table again in this request');
 wp_update_post(['ID'=>$id,'post_parent'=>$id]);adm_check((int)get_post_field('post_parent',$id)===0,'normal save cannot recreate a self-parent cycle');
 $parent=wp_insert_post(['post_title'=>'Admin performance legitimate parent','post_type'=>'page','post_status'=>'draft']);$created[]=$parent;
 wp_update_post(['ID'=>$id,'post_parent'=>$parent]);adm_check((int)get_post_field('post_parent',$id)===$parent,'legitimate parent relationship remains supported');

 add_filter('home_url',$live);
 $mock=static function($pre,$params,$url)use(&$network,&$mockStatus){
  if(str_starts_with($url,'https://api.indexnow.org/')){$network++;usleep(300000);return ['response'=>['code'=>$mockStatus,'message'=>'Test response'],'headers'=>[],'body'=>'','cookies'=>[]];}
  return $pre;
 };add_filter('pre_http_request',$mock,PHP_INT_MAX,3);
 wp_clear_scheduled_hook($hook,$args);delete_option('custom_box_search_indexing_submission_version');delete_option('custom_box_search_indexing_last_attempt');delete_option('custom_box_search_indexing_sync_version');
 delete_transient(task_key('custom_box_run_search_indexing_sync',get_template_directory().'/inc/search-indexing-health.php'));
 $start=hrtime(true);custom_box_run_search_indexing_sync();$samples['configuration_ms']=round((hrtime(true)-$start)/1e6,3);
 adm_check($network===0,'admin configuration never sends synchronous IndexNow HTTP');
 adm_check(get_option('custom_box_search_indexing_sync_version')===$version,'configuration marker completes independently of delivery');
 adm_check((bool)wp_next_scheduled($hook,$args),'admin queues one background submission');
 foreach(['index.php','edit.php','upload.php','plugins.php','options-general.php','edit.php?post_type=product'] as $page){
  $GLOBALS['pagenow']=$page;$start=hrtime(true);custom_box_run_search_indexing_sync();$samples[$page]=round((hrtime(true)-$start)/1e6,3);
  adm_check($network===0,'no synchronous IndexNow on '.$page);
 }
 $events=0;foreach(_get_cron_array() as $items)foreach(($items[$hook]??[]) as $event)if($event['args']===$args)$events++;
 adm_check($events===1,'repeated admin pages do not duplicate queued submissions');
 $stamp=wp_next_scheduled($hook,$args);wp_unschedule_event($stamp,$hook,$args);
 wp_set_current_user(0);$start=hrtime(true);do_action($hook,$version);$samples['worker_ms']=round((hrtime(true)-$start)/1e6,3);
 adm_check($network===1,'background worker sends once without an interactive admin user');
 adm_check(get_option('custom_box_search_indexing_sync_version')===$version,'failed submission does not reset completed configuration');
 adm_check(wp_next_scheduled($hook,$args)>=time()+880,'failed background submission backs off about fifteen minutes');
 $status=get_option('custom_box_search_indexing_sync_status');adm_check(!$status['indexnow']&&$status['indexnow_pending'],'failed delivery remains pending rather than marked successful');
 wp_set_current_user(get_user_by('login','chat-test-manager')->ID);custom_box_run_search_indexing_sync();adm_check($network===1,'admin navigation after failed worker never retries HTTP inline');
 wp_clear_scheduled_hook($hook,$args);delete_option('custom_box_search_indexing_last_attempt');$mockStatus=200;
 do_action($hook,$version);adm_check($network===2&&get_option('custom_box_search_indexing_submission_version')===$version,'successful worker records actual confirmed submission');
 custom_box_run_search_indexing_sync();adm_check(!wp_next_scheduled($hook,$args)&&$network===2,'completed submission is not queued again');
 do_action($hook,'stale-release');adm_check($network===2,'stale worker cannot submit a different release');
 remove_filter('home_url',$live);adm_check(!custom_box_queue_search_indexing_submission($version),'local preview never queues production submission');

 $GLOBALS['pagenow']='index.php';$_GET=[];foreach(custom_box_post_sync_registry() as $entry)update_option($entry['option'],$entry['version'],false);
 update_option('custom_box_post_sync_health_last_run',time(),false);
 $queries=[];$capture=static function($sql)use(&$queries){$queries[]=$sql;return $sql;};add_filter('query',$capture);
 adm_check(custom_box_post_sync_files_to_load()===[],'completed post bundles are absent from normal admin navigation');
 adm_check(!array_filter($queries,static fn($sql)=>stripos($sql,'UPDATE')===0&&str_contains($sql,'post_parent')),'post loader never runs a full-table parent repair');remove_filter('query',$capture);
 $registry=custom_box_post_sync_registry();$pendingFile=array_key_first($registry);$pending=$registry[$pendingFile];
 update_option($pending['option'],'pending-test-version',false);delete_transient(task_key('post-sync-'.$pendingFile,get_template_directory().'/'.$pendingFile));
 adm_check(custom_box_post_sync_files_to_load()===[$pendingFile],'new pending bundle remains eligible after deployment');
 adm_check(custom_box_post_sync_files_to_load()===[],'unsuccessful pending bundle is not retried on every admin click');
 wp_update_post(['ID'=>$id,'post_name'=>$pending['slug']]);$GLOBALS['pagenow']='post.php';$_GET['post']=$id;
 adm_check(in_array($pendingFile,custom_box_post_sync_files_to_load(),true),'editing the affected post bypasses routine retry backoff');
 $GLOBALS['pagenow']='index.php';$_GET=['custom_box_run_post_syncs'=>'1'];
 adm_check(count(custom_box_post_sync_files_to_load())===count($registry),'explicit deployment still loads all registered bundles');
 $_GET=[];update_option($pending['option'],$pending['version'],false);
}finally{
 remove_filter('home_url',$live);wp_clear_scheduled_hook($hook,$args);delete_option('custom_box_search_indexing_submission_version');delete_option('custom_box_search_indexing_last_attempt');
 foreach($created as $id)wp_delete_post($id,true);
 $report=['checks'=>count($passed),'passed'=>$passed,'samples'=>$samples,'mode'=>'actual WordPress, theme, WooCommerce, Rank Math on isolated DB; IndexNow responses mocked and delayed 300 ms; no production traffic'];
 file_put_contents(dirname(__DIR__,2).'/artifacts/admin-performance/maintenance-tests.json',wp_json_encode($report,JSON_PRETTY_PRINT));ob_end_clean();echo wp_json_encode($report,JSON_PRETTY_PRINT);
}
