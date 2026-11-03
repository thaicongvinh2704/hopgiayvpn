<?php
/** Real WordPress/media lifecycle on fixed vpg03_ fixture tables, loopback port 3311. */
if(PHP_SAPI!=='cli'){exit;}
$root=dirname(__DIR__);$hooks=in_array('--hooks',$argv,true);
if(in_array('--reset',$argv,true)){
    $db=new mysqli('127.0.0.1','root','','vpn_chat_test',3311);
    foreach($db->query("SHOW TABLES LIKE 'vpg03\\_%'")->fetch_all() as [$table]){
        if(!preg_match('/^vpg03_[a-z0-9_]+$/',$table)){throw new RuntimeException('Unsafe fixture table.');}
        $db->query('DROP TABLE `'.$table.'`');
    }
    $db->close();
}
define('ABSPATH',__DIR__.'/live-chat/runtime/wp/');
define('DB_NAME','vpn_chat_test');define('DB_USER','root');define('DB_PASSWORD','');define('DB_HOST','127.0.0.1:3311');
define('DB_CHARSET','utf8mb4');define('DB_COLLATE','');
define('WP_HOME',$hooks?'https://hopgiayvpn.com':'http://127.0.0.1:8091/paper-guides-fixture');define('WP_SITEURL',WP_HOME);
define('WP_ENVIRONMENT_TYPE','local');define('DISABLE_WP_CRON',true);define('WP_DEBUG',false);define('WP_DISABLE_FATAL_ERROR_HANDLER',true);
if(!$hooks){define('WP_INSTALLING',true);define('VPN_PG03_SKIP_AUTO',true);}
foreach(['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $key){define($key,'paper-guides-fixture-only-'.$key);}
$table_prefix='vpg03_';require ABSPATH.'wp-settings.php';
if($wpdb->prefix!=='vpg03_'||DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311'){throw new RuntimeException('Wrong fixture DB.');}
add_filter('pre_wp_mail','__return_true',PHP_INT_MAX);
add_filter('pre_http_request',static fn()=>new WP_Error('offline_fixture','External requests blocked in fixture.'),PHP_INT_MAX);
require_once ABSPATH.'wp-admin/includes/upgrade.php';
if(!is_blog_installed()){wp_install('Paper guides fixture','paper-guides-fixture-admin','editorial@example.invalid',false,'','fixture-only-password');}
require_once ABSPATH.'wp-admin/includes/plugin.php';
if(!in_array('seo-by-rank-math/rank-math.php',(array)get_option('active_plugins'),true)){
    $a=activate_plugin('seo-by-rank-math/rank-math.php');if(is_wp_error($a)){throw new RuntimeException($a->get_error_message());}
}
update_option('upload_path',$root.'/tests/live-chat/runtime/paper-guides-deploy-uploads');
update_option('upload_url_path',WP_HOME.'/wp-content/uploads/paper-guides-fixture');
update_option('permalink_structure','/%postname%/');
require $root.'/wp-content/mu-plugins/vpn-paper-guides-20261103.php';
$checks=[];
function pg_assert(bool $ok,string $label):void{global $checks;if(!$ok){throw new RuntimeException($label);}$checks[]=$label;}
function pg_count():int{global $wpdb;return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}");}
function pg_assets():int{global $wpdb;return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment'");}
function pg_cleanup():void{
    global $wpdb;
    foreach($wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment'") as $id){wp_delete_attachment((int)$id,true);}
    foreach($wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('post','revision')") as $id){wp_delete_post((int)$id,true);}
    delete_option(VPN_PG03_OPTION);delete_option('vpn_pg03_results');delete_option('vpn_pg03_error');delete_option('vpn_pg03_retry');
}
$admin=get_user_by('login','paper-guides-fixture-admin');wp_set_current_user($admin->ID);
$package=vpn_pg03_package();$rows=$package['articles'];
$publication_events=[];
add_action('transition_post_status',static function($new,$old,$post) use (&$publication_events):void{
    if($new!=='publish'||$old==='publish'||get_post_meta($post->ID,'_vpn_editorial_batch',true)!==VPN_PG03_BATCH){return;}
    if(substr_count($post->post_content,'<figcaption class="wp-caption-text">')!==4||!get_post_thumbnail_id($post->ID)
        ||!get_post_meta($post->ID,'rank_math_title',true)||strpos($post->post_content,'IMAGE_SLOT')!==false){throw new RuntimeException('Incomplete article reached publication transition.');}
    $publication_events[]=$post->ID;
},10,3);
pg_assert(count($rows)===6&&array_sum(array_map(static fn($r)=>count($r['images']),$rows))===30,'Package validates six approved public articles and 30 WebP checksums');
if($hooks){
    pg_cleanup();wp_set_current_user(0);$n=pg_count();vpn_pg03_deploy();
    pg_assert(pg_count()===$n&&!get_option(VPN_PG03_OPTION),'Anonymous hosting-style admin hook cannot import');
    wp_set_current_user($admin->ID);$second=new mysqli('127.0.0.1','root','','vpn_chat_test',3311);$lock=$wpdb->prefix.'vpn_pg03_release';
    $second->query("SELECT GET_LOCK('".$lock."',0)");vpn_pg03_deploy();
    pg_assert(pg_count()===$n&&!get_option(VPN_PG03_OPTION),'Concurrent database session lock prevents a duplicate import');
    $second->query("SELECT RELEASE_LOCK('".$lock."')");$second->close();vpn_pg03_deploy();
    pg_assert(get_option(VPN_PG03_OPTION)===VPN_PG03_VERSION,'Hosting administrator hook completes fixed batch');
    $result=get_option('vpn_pg03_results');$media=[];
    foreach($rows as $row){$r=$result[$row['key']];vpn_pg03_verify($r['id'],$row,$r['media'],'publish');$media=array_merge($media,$r['media']);pg_assert(get_post_status($r['id'])==='publish','Hosting-style article is PUBLIC: '.$row['key']);}
    pg_assert(count(array_unique($media))===30,'Hosting-style deployment imports 30 distinct images');
    pg_assert(count($publication_events)===6,'All six hosting publication transitions already have content, featured image and SEO');
    $n=pg_count();vpn_pg03_deploy();pg_assert(pg_count()===$n,'Completed hook does not import again');
    pg_cleanup();
}else{
    pg_cleanup();wp_set_current_user(0);$n=pg_count();
    try{vpn_pg03_run();throw new RuntimeException('Anonymous import allowed.');}catch(RuntimeException $e){pg_assert($e->getMessage()==='Administrator with publishing permission required.'&&pg_count()===$n,'Anonymous import rejected before mutation');}
    wp_set_current_user($admin->ID);$active=get_option('active_plugins');update_option('active_plugins',[]);
    try{vpn_pg03_run();throw new RuntimeException('Missing SEO plugin accepted.');}catch(RuntimeException $e){pg_assert($e->getMessage()==='Active Rank Math required.'&&pg_count()===$n,'Missing Rank Math prevents mutation');}finally{update_option('active_plugins',$active);}
    $conflict=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_title'=>'Unrelated original','post_name'=>$rows[0]['slug'],'post_content'=>'Preserve unrelated content.']);
    $result=vpn_pg03_run();
    pg_assert(isset($result['container']['error'])&&get_post_field('post_content',$conflict)==='Preserve unrelated content.','Unrelated existing slug is never overwritten');
    pg_assert(count(array_filter($result,static fn($r)=>($r['status']??'')==='publish'))===5,'Other five articles still publish after one conflict');
    wp_delete_post($conflict,true);
    $row=$rows[0];$partial=wp_insert_post(wp_slash(['post_type'=>'post','post_status'=>'draft','post_name'=>$row['slug'],'post_title'=>$row['title'],
        'post_excerpt'=>$row['excerpt'],'post_content'=>'','post_author'=>$admin->ID,'meta_input'=>['_vpn_editorial_batch'=>VPN_PG03_BATCH,'_vpn_editorial_source_hash'=>$row['content_sha256']]]));
    $single=$row;$single['images']=[$row['images'][0]];$first=vpn_pg03_media($single,$partial)[0];$n=pg_assets();$result=vpn_pg03_run();
    pg_assert(($result['container']['id']??0)===$partial&&($result['container']['media'][0]??0)===$first&&pg_assets()===$n+4,'Interrupted import reuses its post and first image');
    $ids=[];$media=[];
    foreach($rows as $row){
        $p=vpn_pg03_find($row);$list=vpn_pg03_media($row,$p->ID);vpn_pg03_verify($p->ID,$row,$list,'publish');$ids[$row['key']]=$p->ID;$media=array_merge($media,$list);
        pg_assert(get_post_status($p->ID)==='publish'&&strpos($p->post_content,'IMAGE_SLOT')===false&&strpos($p->post_content,WP_HOME)!==false,
            'Public content, destination media URLs, SEO/category/featured verified: '.$row['key']);
    }
    pg_assert(count(array_unique($media))===30,'Exactly 30 independent imported media records');
    pg_assert(count($publication_events)===6,'No article becomes public before its content, images and SEO are saved');
    $n=pg_count();vpn_pg03_run();vpn_pg03_run();pg_assert(pg_count()===$n,'Repeated run creates no duplicate posts or images');
    $review=$ids['ribbon'];delete_post_meta($review,'_vpn_pg03_release_accepted');update_post_meta($review,'_vpn_editorial_source_hash',$rows[5]['review_source_sha256']);
    wp_update_post(['ID'=>$review,'post_status'=>'draft']);$n=pg_count();$result=vpn_pg03_run();
    pg_assert(($result['ribbon']['status']??'')==='publish'&&pg_count()===$n,'Existing completed review draft with original source checksum becomes public without duplication');
    wp_update_post(['ID'=>$ids['container'],'post_content'=>'<p>Owner revised content.</p>','post_status'=>'draft']);update_post_meta($ids['container'],'rank_math_title','Owner SEO title');vpn_pg03_run();
    pg_assert(get_post_field('post_content',$ids['container'])==='<p>Owner revised content.</p>'&&get_post_status($ids['container'])==='draft'&&get_post_meta($ids['container'],'rank_math_title',true)==='Owner SEO title','Later owner edits, SEO and unpublishing preserved');
    wp_update_post(['ID'=>$ids['qc'],'post_status'=>'trash']);vpn_pg03_run();pg_assert(get_post_status($ids['qc'])==='trash','Later owner trash decision preserved');
    $id=$ids['window'];delete_post_meta($id,'_vpn_editorial_import_complete');wp_update_post(['ID'=>$id,'post_status'=>'draft','post_content'=>'<p>Edited interrupted article.</p>']);$result=vpn_pg03_run();
    pg_assert(isset($result['window']['error'])&&get_post_status($id)==='draft'&&get_post_field('post_content',$id)==='<p>Edited interrupted article.</p>','Edited interrupted copy is preserved and not published');
    $id=$ids['greaseproof'];delete_post_meta($id,'_vpn_editorial_import_complete');wp_update_post(['ID'=>$id,'post_status'=>'draft']);
    $list=get_post_meta($id,'_vpn_pg03_media',true);update_post_meta($list[0],'_wp_attachment_image_alt','Changed fixture alt');$result=vpn_pg03_run();
    pg_assert(isset($result['greaseproof']['error'])&&get_post_status($id)==='draft','Invalid existing image metadata prevents publication');
    $n=pg_count();vpn_pg03_deploy();pg_assert(pg_count()===$n&&!get_option(VPN_PG03_OPTION),'Automatic deployment disabled on local fixture');
    pg_assert(get_current_user_id()===$admin->ID,'Importer never changes request identity');pg_cleanup();
}
$report=['passed'=>count($checks),'checks'=>$checks,'database'=>'vpn_chat_test:3311 / vpg03_ fixture tables','main_local_database_untouched'=>true,'fixture_records_removed'=>true];
$dir=$root.'/outputs/paper-guides-20261103';file_put_contents($dir.'/deploy-'.($hooks?'hook-':'').'verification.json',wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),PHP_EOL;
