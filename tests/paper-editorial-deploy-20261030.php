<?php
/** Real WP/media import on isolated vpe30_ fixture tables, loopback database port 3311. */
if (PHP_SAPI !== 'cli') { exit; }
if (in_array('--reset', $argv, true)) {
    $fixture = new mysqli('127.0.0.1','root','','vpn_chat_test',3311);
    foreach ($fixture->query("SHOW TABLES LIKE 'vpe30\\_%'")->fetch_all() as [$table]) {
        if (!preg_match('/^vpe30_[a-z0-9_]+$/',$table)) { throw new RuntimeException('Unsafe fixture table.'); }
        $fixture->query('DROP TABLE `'.$table.'`');
    }
    $fixture->close();
}
$root = dirname(__DIR__);
$hook_test = in_array('--hooks',$argv,true);
if ($hook_test) {
    // A separate process starts this fresh pass after prior WordPress shutdown hooks.
    // These fixed fixture tables contain no primary local or production data.
    $fixture = new mysqli('127.0.0.1','root','','vpn_chat_test',3311);
    $fixture->query('DELETE FROM vpe30_postmeta');
    $fixture->query('DELETE FROM vpe30_posts');
    $fixture->close();
}
define('ABSPATH', __DIR__.'/live-chat/runtime/wp/');
define('DB_NAME','vpn_chat_test'); define('DB_USER','root'); define('DB_PASSWORD',''); define('DB_HOST','127.0.0.1:3311');
define('DB_CHARSET','utf8mb4'); define('DB_COLLATE','');
define('WP_HOME',$hook_test?'https://hopgiayvpn.com':'http://127.0.0.1:8091/editorial-fixture'); define('WP_SITEURL',WP_HOME);
define('WP_ENVIRONMENT_TYPE','local'); define('DISABLE_WP_CRON',true); define('WP_DEBUG',false);
if(!$hook_test){define('WP_INSTALLING',true);define('VPN_PE30_SKIP_AUTO',true);} define('WP_DISABLE_FATAL_ERROR_HANDLER',true);
foreach(['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $k){define($k,'editorial-test-only-'.$k);}
$table_prefix='vpe30_';
require ABSPATH.'wp-settings.php';
if($wpdb->prefix!=='vpe30_' || DB_NAME!=='vpn_chat_test' || DB_HOST!=='127.0.0.1:3311'){throw new RuntimeException('Wrong test database.');}
add_filter('pre_wp_mail','__return_true');
add_filter('pre_http_request',static function(){return new WP_Error('offline_test','No outbound requests in fixture.');});
require_once ABSPATH.'wp-admin/includes/upgrade.php';
if(!is_blog_installed()){wp_install('Editorial fixture','editorial-fixture-admin','editorial@example.invalid',false,'','fixture-only-password');}
require_once ABSPATH.'wp-admin/includes/plugin.php';
if(!in_array('seo-by-rank-math/rank-math.php',(array)get_option('active_plugins'),true)){
    $a=activate_plugin('seo-by-rank-math/rank-math.php');if(is_wp_error($a)){throw new RuntimeException($a->get_error_message());}
}
update_option('upload_path',$root.'/tests/live-chat/runtime/editorial-deploy-uploads');
update_option('permalink_structure','/%postname%/');
require $root.'/wp-content/mu-plugins/vpn-paper-editorial-20261030.php';
$checks=[];
function pe_assert(bool $ok,string $what):void{global $checks;if(!$ok){throw new RuntimeException($what);}$checks[]=$what;}
function pe_count():int{global $wpdb;return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}");}
function pe_asset_count():int{global $wpdb;return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment'");}
$admin=get_user_by('login','editorial-fixture-admin');wp_set_current_user($admin->ID);
if($hook_test){
    delete_option(VPN_PE30_OPTION);delete_option('vpn_pe30_retry');
    wp_set_current_user(0);$before=pe_count();vpn_pe30_deploy();
    pe_assert(pe_count()===$before && !get_option(VPN_PE30_OPTION),'Anonymous automatic trigger does nothing');
    wp_set_current_user($admin->ID);
    $connection=new mysqli('127.0.0.1','root','','vpn_chat_test',3311);
    $lock=$wpdb->prefix.'vpn_pe30_release';$connection->query("SELECT GET_LOCK('".$lock."',0)");
    vpn_pe30_deploy();pe_assert(pe_count()===$before && !get_option(VPN_PE30_OPTION),'Second database session holds deployment lock; no writes');
    $connection->query("SELECT RELEASE_LOCK('".$lock."')");$connection->close();
    vpn_pe30_deploy();
    pe_assert(get_option(VPN_PE30_OPTION)===VPN_PE30_VERSION,'Administrator deployment hook completes all three articles');
    $result=get_option('vpn_pe30_results');$media=[];
    foreach(vpn_pe30_package()['articles'] as $row){
        $r=$result[$row['key']];vpn_pe30_verify($r['id'],$row,$r['media']);
        pe_assert(get_post_status($r['id'])==='draft','Hosting-style deployment retains draft status: '.$row['key']);
        $media=array_merge($media,$r['media']);
    }
    pe_assert(count(array_unique($media))===14,'Hosting-style deployment imports 14 distinct images');
    $before=pe_count();vpn_pe30_deploy();pe_assert(pe_count()===$before,'Completed admin hook does not reimport');
    foreach($media as $id){wp_delete_attachment($id,true);}foreach($result as $r){wp_delete_post($r['id'],true);}
    delete_option(VPN_PE30_OPTION);delete_transient('vpn_pe30_notice');
    $report=['passed'=>count($checks),'checks'=>$checks,'database'=>'vpn_chat_test:3311 / vpe30_ fixture tables','fixture_records_removed'=>true];
    file_put_contents($root.'/outputs/paper-editorial-20261030/deploy-hook-verification.json',wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    echo wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),PHP_EOL;exit;
}
$package=vpn_pe30_package(); $rows=$package['articles'];
pe_assert(count($rows)===3 && array_sum(array_map(static function($r){return count($r['images']);},$rows))===14,'Package has three reviewed articles and 14 checksum-validated WebPs');
wp_set_current_user(0);$n=pe_count();
try{vpn_pe30_run();throw new RuntimeException('Anonymous import unexpectedly permitted.');}catch(RuntimeException $e){pe_assert($e->getMessage()==='Administrator required.' && pe_count()===$n,'Anonymous request cannot write');}
wp_set_current_user($admin->ID);
$conflict=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_title'=>'Unrelated original','post_name'=>$rows[0]['slug'],'post_content'=>'Preserve this article.']);
$result=vpn_pe30_run();
pe_assert(isset($result['supplier']['error']) && get_post_field('post_content',$conflict)==='Preserve this article.','Unrelated slug conflict preserved');
pe_assert(!isset($result['bakery']['error'],$result['approval']['error']) && $result['bakery']['status']==='draft' && $result['approval']['status']==='draft','Other two guides continue after one conflict');
wp_delete_post($conflict,true);
// Interrupted owned draft plus one already imported asset: resume without duplicating it.
$row=$rows[0];
$interrupted=wp_insert_post(wp_slash(['post_type'=>'post','post_status'=>'draft','post_name'=>$row['slug'],'post_title'=>$row['title'],'post_excerpt'=>$row['excerpt'],'post_content'=>'','post_author'=>$admin->ID,'meta_input'=>['_vpn_editorial_batch'=>VPN_PE30_BATCH,'_vpn_editorial_source_hash'=>$row['content_sha256']]]));
require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
$image=$row['images'][0];$temp=wp_tempnam($image['filename']);copy($root.'/wp-content/mu-plugins/vpn-paper-editorial-20261030/supplier/images/'.$image['filename'],$temp);
$first=media_handle_sideload(['name'=>$image['filename'],'tmp_name'=>$temp],$interrupted,null,['post_title'=>$image['title'],'post_excerpt'=>$image['caption'],'meta_input'=>['_wp_attachment_image_alt'=>$image['alt'],'_vpn_editorial_asset'=>VPN_PE30_BATCH.'/supplier/'.$image['filename']]]);
pe_assert(!is_wp_error($first),'Interrupted first attachment created in isolated uploads');
$before=pe_asset_count();$result=vpn_pe30_run();
pe_assert($result['supplier']['id']===$interrupted && $result['supplier']['media'][0]===$first && pe_asset_count()===$before+4,'Interrupted import reuses its post and first image');
$ids=[];$media=[];
foreach($rows as $row){
 $post=vpn_pe30_find($row);$ids[$row['key']]=$post->ID;
 $list=[];foreach($row['images'] as $image){$found=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_vpn_editorial_asset','meta_value'=>VPN_PE30_BATCH.'/'.$row['key'].'/'.$image['filename']]);$list[]=(int)$found[0]->ID;}
 vpn_pe30_verify($post->ID,$row,$list);$media=array_merge($media,$list);
 pe_assert(strpos($post->post_content,WP_HOME)!==false && strpos($post->post_content,'IMAGE_SLOT')===false,'Target URLs, SEO, content, captions and images verified: '.$row['key']);
}
pe_assert(count(array_unique($media))===14,'Exactly 14 distinct imported media records');
$before=pe_count();vpn_pe30_run();vpn_pe30_run();pe_assert(pe_count()===$before,'Repeated import does not create posts or images');
wp_update_post(['ID'=>$ids['supplier'],'post_content'=>'<p>Later owner revision.</p>','post_status'=>'publish']);
update_post_meta($ids['supplier'],'rank_math_title','Owner SEO title');vpn_pe30_run();
pe_assert(get_post_field('post_content',$ids['supplier'])==='<p>Later owner revision.</p>' && get_post_status($ids['supplier'])==='publish' && get_post_meta($ids['supplier'],'rank_math_title',true)==='Owner SEO title','Later edits, SEO and publishing are preserved');
wp_update_post(['ID'=>$ids['bakery'],'post_status'=>'trash']);vpn_pe30_run();pe_assert(get_post_status($ids['bakery'])==='trash','Owner trash/unpublishing is preserved');
$before=pe_count();vpn_pe30_deploy();pe_assert(!get_option(VPN_PE30_OPTION) && pe_count()===$before,'Local automatic deployment remains disabled');
pe_assert(get_current_user_id()===$admin->ID,'Importer does not change request identity');
// Only fixture-owned attachments/posts are removed; no source package or primary local DB touched.
foreach($media as $id){wp_delete_attachment($id,true);}foreach($ids as $id){wp_delete_post($id,true);}
$report=['passed'=>count($checks),'checks'=>$checks,'database'=>'vpn_chat_test:3311 / vpe30_ fixture tables','main_local_database_untouched'=>true,'fixture_records_removed'=>true];
file_put_contents($root.'/outputs/paper-editorial-20261030/deploy-verification.json',wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),PHP_EOL;
