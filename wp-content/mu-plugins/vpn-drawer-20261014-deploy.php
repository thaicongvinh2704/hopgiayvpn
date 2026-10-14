<?php
/** Fixed, one-time Drawer category release. No request-controlled import input. */
defined('ABSPATH') || exit;

function vpn_drawer_deploy_meta(array $p): array {
    return array('rank_math_title'=>$p['title'], 'rank_math_description'=>$p['description'], 'rank_math_focus_keyword'=>$p['keyword'],
        'rank_math_facebook_title'=>$p['title'], 'rank_math_facebook_description'=>$p['description'],
        'rank_math_twitter_title'=>$p['title'], 'rank_math_twitter_description'=>$p['description']);
}
function vpn_drawer_deploy_specs($specs): array {
    $specs=is_array($specs)?$specs:array();$found=false;
    foreach($specs as &$row){if(is_array($row)&&'Minimum Order Quantity (MOQ)'===($row['label']??'')){$row['value']='1,000 units';$found=true;}}
    unset($row);if(!$found){$specs[]=array('label'=>'Minimum Order Quantity (MOQ)','value'=>'1,000 units');}return $specs;
}
function vpn_drawer_deploy_package(): array {
    $dir=__DIR__.'/vpn-drawer-20261014';
    $manifest=json_decode((string)file_get_contents($dir.'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
    if('drawer-20261014-v1'!==($manifest['version']??'')||!function_exists('vpn_drawer_data')||!function_exists('vpn_drawer_product_faq')){throw new RuntimeException('Drawer theme files are not ready. Deploy the complete release.');}
    foreach($manifest['theme_files'] as $relative=>$hash){$file=get_template_directory().'/'.$relative;$actual=is_file($file)?('webp'===pathinfo($file,PATHINFO_EXTENSION)?hash_file('sha256',$file):hash('sha256',str_replace(array("\r\n","\r"),"\n",(string)file_get_contents($file)))):'';if(!hash_equals($hash,$actual)){throw new RuntimeException('Missing or incomplete Drawer file: '.$relative);}}
    $body=str_replace(array("\r\n","\r"),"\n",(string)file_get_contents($dir.'/botanical.html'));
    if(!hash_equals($manifest['botanical']['content_sha256'],hash('sha256',$body))){throw new RuntimeException('Botanical content checksum failed.');}
    foreach($manifest['botanical']['images'] as $image){$file=$dir.'/images/'.basename($image['file']);$size=is_file($file)?getimagesize($file):false;
        if(!$size||$size[0]!==$image['width']||$size[1]!==$image['height']||'image/webp'!==$size['mime']||!hash_equals($image['sha256'],hash_file('sha256',$file))){throw new RuntimeException('Botanical image preflight failed: '.$image['file']);}}
    $data=vpn_drawer_data();if(18!==count($data['products']??array())){throw new RuntimeException('Expected 18 fixed Drawer profiles.');}
    return array($manifest,$data,$body);
}
function vpn_drawer_deploy_botanical(array $b,array $profile,string $body,int $term_id): int {
    $marker='drawer-20261014-v1';$post=get_page_by_path($b['slug'],OBJECT,'product');
    if(!$post){$id=wp_insert_post(wp_slash(array('post_type'=>'product','post_status'=>'draft','post_title'=>$profile['name'],'post_name'=>$b['slug'],
        'meta_input'=>array('_vpn_drawer_created'=>$marker))),true);if(is_wp_error($id)){throw new RuntimeException($id->get_error_message());}$post=get_post($id);}
    $id=(int)$post->ID;
    if($post->post_name!==$b['slug']||get_post_meta($id,'_vpn_drawer_created',true)!==$marker){throw new RuntimeException('Unrelated Botanical draft preserved.');}
    $saved=get_post_meta($id,'_vpn_drawer_partial_hash',true);$expected=get_post_meta($id,'_vpn_drawer_expected_partial_hash',true);$current_hash=hash('sha256',$post->post_content);
    if(trim($post->post_content)!==''&&!($saved&&hash_equals($saved,$current_hash))&&!($expected&&hash_equals($expected,$current_hash))){throw new RuntimeException('Edited partial Botanical content preserved.');}
    require_once ABSPATH.'wp-admin/includes/image.php';$uploads=wp_upload_dir();if($uploads['error']){throw new RuntimeException($uploads['error']);}
    $target=trailingslashit($uploads['basedir']).'2026/10/vpn-drawer-20261014/';if(!wp_mkdir_p($target)){throw new RuntimeException('Botanical upload directory unavailable.');}
    $ids=array();$replace=array('{{HOME}}'=>trailingslashit(home_url('/')));
    foreach($b['images'] as $index=>$image){$file=$target.basename($image['file']);
        if(is_file($file)&&!hash_equals($image['sha256'],hash_file('sha256',$file))){throw new RuntimeException('Existing different image preserved: '.$image['file']);}
        if(!is_file($file)){$temporary=$file.'.tmp-'.wp_generate_password(8,false);if(!copy(__DIR__.'/vpn-drawer-20261014/images/'.$image['file'],$temporary)||!hash_equals($image['sha256'],hash_file('sha256',$temporary))||!rename($temporary,$file)){if(is_file($temporary)){unlink($temporary);}throw new RuntimeException('Botanical asset copy failed.');}}
        $existing=get_posts(array('post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>1,'fields'=>'ids','meta_key'=>'_vpn_drawer_asset','meta_value'=>$marker.'/'.$image['file']));
        if($existing){$aid=(int)$existing[0];}else{$aid=wp_insert_attachment(array('post_mime_type'=>'image/webp','post_title'=>$image['title'],'post_excerpt'=>$image['caption'],'post_parent'=>$id,
            'meta_input'=>array('_vpn_drawer_asset'=>$marker.'/'.$image['file'])),$file,$id,true);if(is_wp_error($aid)){throw new RuntimeException($aid->get_error_message());}}
        update_attached_file($aid,$file);
        if(!wp_get_attachment_metadata($aid)){wp_update_attachment_metadata($aid,wp_generate_attachment_metadata($aid,$file));}
        if(!is_file(get_attached_file($aid))||!wp_get_attachment_url($aid)){throw new RuntimeException('Botanical attachment registration failed.');}
        update_post_meta($aid,'_wp_attachment_image_alt',$image['alt']);$ids[]=$aid;$replace['{{ASSET:'.$index.'}}']=wp_get_attachment_url($aid);
    }
    $html=wp_kses_post(strtr($body,$replace));if(str_contains($html,'{{')||str_contains($html,'localhost')){throw new RuntimeException('Unresolved Botanical URL token.');}
    update_post_meta($id,'_vpn_drawer_expected_partial_hash',hash('sha256',$html));
    $result=wp_update_post(wp_slash(array('ID'=>$id,'post_content'=>$html,'post_excerpt'=>$profile['short_description'])),true);
    if(is_wp_error($result)){throw new RuntimeException($result->get_error_message());}
    update_post_meta($id,'_vpn_drawer_partial_hash',hash('sha256',get_post_field('post_content',$id)));
    $categories=array($term_id);foreach($b['categories'] as $slug){$term=get_term_by('slug',$slug,'product_cat');if($term){$categories[]=(int)$term->term_id;}}
    wp_set_object_terms($id,array_values(array_unique($categories)),'product_cat');wp_set_object_terms($id,'simple','product_type');
    $tags=array();foreach($b['tags'] as $slug){$term=get_term_by('slug',$slug,'product_tag');if($term){$tags[]=(int)$term->term_id;}}if($tags){wp_set_object_terms($id,$tags,'product_tag');}
    update_post_meta($id,'_thumbnail_id',$ids[0]);update_post_meta($id,'_product_image_gallery',implode(',',array_slice($ids,1)));
    update_post_meta($id,'_custom_box_product_specs',$b['specs']);update_post_meta($id,'_custom_box_product_hero_bullets',$b['bullets']);
    if(7!==count($ids)||substr_count(get_post_field('post_content',$id),'<img')!==4||!get_post_thumbnail_id($id)){throw new RuntimeException('Botanical gallery/content readback failed.');}
    return $id;
}
function vpn_drawer_deploy_run(): array {
    [$manifest,$data,$body]=vpn_drawer_deploy_package();$term=get_term_by('slug','drawer-boxes','product_cat');
    if(!$term||is_wp_error($term)||!class_exists('WC_Product_Simple')){throw new RuntimeException('Drawer category or WooCommerce unavailable.');}
    $resolved=array();$before=array('category'=>array('id'=>$term->term_id,'description'=>$term->description,'meta'=>array()),'products'=>array());
    foreach(vpn_drawer_deploy_meta($data) as $key=>$value){$before['category']['meta'][$key]=get_term_meta($term->term_id,$key,true);}
    foreach($data['products'] as $profile){$post=get_page_by_path($profile['slug'],OBJECT,'product');$botanical=$profile['slug']===$manifest['botanical']['slug'];
        $own_draft=$post&&'draft'===$post->post_status&&'drawer-20261014-v1'===get_post_meta($post->ID,'_vpn_drawer_created',true);
        if((!$post&&!$botanical)||($post&&'publish'!==$post->post_status&&!($botanical&&$own_draft))){throw new RuntimeException('Missing or unpublished reviewed product: '.$profile['slug']);}
        $snapshot=array('slug'=>$profile['slug'],'id'=>$post?(int)$post->ID:null);
        if($post){$snapshot['title']=$post->post_title;$snapshot['excerpt']=$post->post_excerpt;$snapshot['categories']=wp_get_post_terms($post->ID,'product_cat',array('fields'=>'ids'));$snapshot['meta']=array();
            foreach(array_merge(array_keys(vpn_drawer_deploy_meta($profile)),array('_custom_box_product_specs','_custom_box_product_faq_html')) as $key){$snapshot['meta'][$key]=get_post_meta($post->ID,$key,true);}}
        $before['products'][]=$snapshot;$resolved[]=array($post,$profile,$botanical&&(!$post||$own_draft));
    }
    // Persist the complete before-state before the first content mutation, including a missing Botanical slug.
    if(false===get_option('vpn_drawer_20261014_before',false)&&!add_option('vpn_drawer_20261014_before',$before,'',false)){throw new RuntimeException('Cannot save Drawer before-state.');}
    $result=wp_update_term($term->term_id,'product_cat',array('description'=>$data['intro']));if(is_wp_error($result)){throw new RuntimeException($result->get_error_message());}
    foreach(vpn_drawer_deploy_meta($data) as $key=>$value){update_term_meta($term->term_id,$key,wp_slash($value));if(get_term_meta($term->term_id,$key,true)!==$value){throw new RuntimeException('Category meta readback failed: '.$key);}}
    $ids=array();$completed=(array)get_option('vpn_drawer_20261014_items',array());
    foreach($resolved as [$post,$profile,$create]){
        $id=$create?vpn_drawer_deploy_botanical($manifest['botanical'],$profile,$body,(int)$term->term_id):(int)$post->ID;
        if(isset($completed[$profile['slug']])){$ids[]=$id;continue;}
        $post=get_post($id);if($post->post_title!==$profile['name']||$post->post_excerpt!==$profile['short_description']){
            $result=wp_update_post(wp_slash(array('ID'=>$id,'post_title'=>$profile['name'],'post_excerpt'=>$profile['short_description'])),true);if(is_wp_error($result)){throw new RuntimeException($result->get_error_message());}}
        $meta=vpn_drawer_deploy_meta($profile);$meta['_custom_box_product_specs']=vpn_drawer_deploy_specs(get_post_meta($id,'_custom_box_product_specs',true));$meta['_custom_box_product_faq_html']=vpn_drawer_product_faq($profile);
        foreach($meta as $key=>$value){update_post_meta($id,$key,wp_slash($value));if(get_post_meta($id,$key,true)!==$value){throw new RuntimeException('Product meta readback failed: '.$profile['slug'].' '.$key);}}
        $added=wp_set_object_terms($id,array((int)$term->term_id),'product_cat',true);if(is_wp_error($added)){throw new RuntimeException($added->get_error_message());}
        if($create){$result=wp_update_post(array('ID'=>$id,'post_status'=>'publish'),true);if(is_wp_error($result)){throw new RuntimeException($result->get_error_message());}}
        clean_post_cache($id);$saved=get_post($id);
        if($saved->post_title!==$profile['name']||$saved->post_excerpt!==$profile['short_description']||'publish'!==$saved->post_status||!has_term('drawer-boxes','product_cat',$id)){throw new RuntimeException('Product readback failed: '.$profile['slug']);}
        $completed[$profile['slug']]=$id;update_option('vpn_drawer_20261014_items',$completed,false);$ids[]=$id;wc_delete_product_transients($id);
    }
    if(count(array_unique($ids))!==18){throw new RuntimeException('Expected 18 distinct Drawer products.');}
    clean_term_cache($term->term_id,'product_cat');return $ids;
}
function vpn_drawer_20261014_deploy(): void {
    $done='vpn_drawer_20261014_done';$lock='vpn_drawer_20261014_lock';
    if(get_option($done)||(defined('WP_INSTALLING')&&WP_INSTALLING)){return;}
    $host=strtolower((string)wp_parse_url(home_url('/'),PHP_URL_HOST));
    if(!in_array($host,array('hopgiayvpn.com','www.hopgiayvpn.com','localhost','127.0.0.1'),true)||get_template()!=='custom-box-theme'){return;}
    if((int)get_option('vpn_drawer_20261014_retry',0)>time()){return;}
    $started=(int)get_option($lock,0);if($started&&$started<time()-900){delete_option($lock);}
    if(!add_option($lock,time(),'',false)){return;}
    try{
        @set_time_limit(180);$ids=vpn_drawer_deploy_run();update_option($done,array('version'=>'drawer-20261014-v1','ids'=>$ids),false);
        if(!get_option($done)){throw new RuntimeException('Completion marker could not be saved.');}
        delete_option('vpn_drawer_20261014_error');delete_option('vpn_drawer_20261014_retry');
        if(!in_array($host,array('localhost','127.0.0.1'),true)){try{do_action('litespeed_purge_all');}catch(Throwable $e){error_log('Drawer cache purge: '.$e->getMessage());}}
    }catch(Throwable $e){update_option('vpn_drawer_20261014_error',$e->getMessage(),false);update_option('vpn_drawer_20261014_retry',time()+300,false);error_log('Drawer deploy: '.$e->getMessage());}
    finally{delete_option($lock);}
}
add_action('init','vpn_drawer_20261014_deploy',120);
add_action('admin_notices',static function(){if(!current_user_can('manage_options')){return;}$error=get_option('vpn_drawer_20261014_error');
    if($error){echo '<div class="notice notice-error"><p>Drawer Boxes deploy: '.esc_html($error).' Automatic retry in five minutes.</p></div>';}
});
