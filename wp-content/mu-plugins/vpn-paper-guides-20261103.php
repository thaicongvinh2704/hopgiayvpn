<?php
/**
 * Plugin Name: VPN Paper Bag Buyer Guides — Public Release
 * Description: Imports six approved public articles, 30 images and Rank Math metadata after deployment.
 * Version: 2026.11.03.1
 */
defined('ABSPATH') || exit;
const VPN_PG03_BATCH='paper-buyer-guides-20261103';
const VPN_PG03_VERSION='2026-11-03.v1';
const VPN_PG03_OPTION='vpn_paper_guides_20261103_done';

/** Fixed, checksum-verified package; no request-supplied paths, content or IDs. */
function vpn_pg03_package(): array {
    $dir=__DIR__.'/vpn-paper-guides-20261103';
    $data=json_decode((string)file_get_contents($dir.'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
    $expected=['container'=>'how-many-paper-bags-fit-in-a-container','qc'=>'paper-bag-quality-control',
        'window'=>'bread-bags-with-window-buying-guide','greaseproof'=>'greaseproof-bakery-bags-buying-guide',
        'baguette'=>'custom-printed-baguette-bags-buying-guide','ribbon'=>'ribbon-handle-gift-bags-buying-guide'];
    if(($data['batch_key']??'')!==VPN_PG03_BATCH||($data['revision']??'')!==VPN_PG03_VERSION
        ||($data['status']??'')!=='publish'||count($data['articles']??[])!==6){throw new RuntimeException('Unexpected buyer-guide release.');}
    $seen=[];
    foreach($data['articles'] as &$row){
        $key=(string)($row['key']??'');
        if(!isset($expected[$key])||isset($seen[$key])||$row['slug']!==$expected[$key]||$row['status']!=='publish'
            ||count($row['images']??[])!==5||empty($row['category']['name'])){throw new RuntimeException('Unexpected article.');}
        $seen[$key]=true;$source=str_replace(["\r\n","\r"],"\n",(string)file_get_contents($dir.'/'.$key.'/article.html'));
        if(!hash_equals($row['content_sha256'],hash('sha256',$source))||preg_match('~<h1\b|localhost|file://|C:[/\\\\]~i',$source)
            ||stripos($row['seo_title'],$row['focus_keyword'])===false||stripos($row['meta_description'],$row['focus_keyword'])===false){
            throw new RuntimeException('Article checksum/SEO validation failed: '.$key);
        }
        $slots=[];$featured=0;
        foreach($row['images'] as $i=>$image){
            $name=(string)$image['filename'];if(basename($name)!==$name){throw new RuntimeException('Invalid asset path.');}
            $file=$dir.'/'.$key.'/images/'.$name;$size=is_file($file)?wp_getimagesize($file):false;
            if(!$size||($size['mime']??'')!=='image/webp'||$size[0]!==$image['width']||$size[1]!==$image['height']
                ||filesize($file)!==$image['bytes']||!hash_equals($image['sha256'],hash_file('sha256',$file))){throw new RuntimeException('Asset failed preflight: '.$name);}
            if($image['role']==='featured'&&$image['slot']===null&&$i===0){$featured++;}
            elseif($image['role']==='inline'&&in_array($image['slot'],[1,2,3,4],true)&&!isset($slots[$image['slot']])
                &&substr_count($source,'<!-- IMAGE_SLOT_'.$image['slot'].' -->')===1){$slots[$image['slot']]=true;}
            else{throw new RuntimeException('Invalid image assignment.');}
        }
        if($featured!==1||count($slots)!==4||preg_match_all('/<!-- IMAGE_SLOT_\d+ -->/',$source)!==4){throw new RuntimeException('Incomplete image inventory.');}
        $row['source']=$source;
    }
    unset($row);return $data;
}

function vpn_pg03_find(array $row): ?WP_Post {
    global $wpdb;
    $ids=$wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type NOT IN ('attachment','revision')",$row['slug']));
    if(count($ids)>1){throw new RuntimeException('Duplicate slug requires review: '.$row['slug']);}
    $p=$ids?get_post((int)$ids[0]):null;
    if($p&&($p->post_type!=='post'||get_post_meta($p->ID,'_vpn_editorial_batch',true)!==VPN_PG03_BATCH)){
        throw new RuntimeException('Unrelated existing slug preserved: '.$row['slug']);
    }
    return $p;
}

function vpn_pg03_source_matches(int $id,array $row): bool {
    return in_array(get_post_meta($id,'_vpn_editorial_source_hash',true),[$row['content_sha256'],$row['review_source_sha256']],true);
}

function vpn_pg03_media(array $row,int $parent): array {
    $media=[];
    foreach($row['images'] as $image){
        $marker=VPN_PG03_BATCH.'/'.$row['key'].'/'.$image['filename'];
        $found=get_posts(['post_type'=>'attachment','post_status'=>['inherit','private','trash'],'numberposts'=>2,
            'meta_key'=>'_vpn_editorial_asset','meta_value'=>$marker,'fields'=>'ids']);
        if(count($found)>1){throw new RuntimeException('Duplicate media marker; review required.');}
        $mid=$found?(int)$found[0]:0;
        if(!$mid){
            $temp=wp_tempnam($image['filename']);
            if(!$temp||!copy(__DIR__.'/vpn-paper-guides-20261103/'.$row['key'].'/images/'.$image['filename'],$temp)){
                if($temp){wp_delete_file($temp);}throw new RuntimeException('Could not stage image.');
            }
            $mid=media_handle_sideload(['name'=>$image['filename'],'tmp_name'=>$temp],$parent,null,[
                'post_title'=>$image['title'],'post_excerpt'=>$image['caption'],'post_content'=>'Provenance: '.$image['origin'],
                'post_author'=>get_current_user_id(),'meta_input'=>['_vpn_editorial_asset'=>$marker,'_wp_attachment_image_alt'=>$image['alt']],
            ]);
            if(is_wp_error($mid)){wp_delete_file($temp);throw new RuntimeException($mid->get_error_message());}
        }
        $file=get_attached_file($mid);
        if((int)get_post_field('post_parent',$mid)!==$parent||get_post_status($mid)==='trash'||!$file||!is_file($file)
            ||!hash_equals($image['sha256'],hash_file('sha256',$file))||get_post_meta($mid,'_wp_attachment_image_alt',true)!==$image['alt']
            ||get_post_field('post_title',$mid)!==$image['title']||wp_get_attachment_caption($mid)!==$image['caption']||empty(wp_get_attachment_metadata($mid)['sizes'])){
            throw new RuntimeException('Existing/imported image changed or incomplete; preserved: '.$image['filename']);
        }
        $media[]=(int)$mid;
    }
    return $media;
}

function vpn_pg03_verify(int $id,array $row,array $media,string $status): void {
    $p=get_post($id);$term=get_term_by('slug',$row['category']['slug'],'category');
    if(!$p||$p->post_status!==$status||$p->post_name!==$row['slug']||$p->post_title!==$row['title']||$p->post_excerpt!==$row['excerpt']
        ||count($media)!==5||(int)get_post_thumbnail_id($id)!==$media[0]||!$term||!has_category((int)$term->term_id,$id)
        ||substr_count($p->post_content,'<figcaption class="wp-caption-text">')!==4||strpos($p->post_content,'IMAGE_SLOT')!==false
        ||preg_match('~file://|C:[/\\\\]~i',$p->post_content)){
        throw new RuntimeException('Post/media/category readback failed: '.$row['slug']);
    }
    if(!in_array(wp_parse_url(home_url(),PHP_URL_HOST),['localhost','127.0.0.1'],true)&&strpos($p->post_content,'localhost')!==false){throw new RuntimeException('Local URL escaped into hosting content.');}
    foreach(['rank_math_title'=>'seo_title','rank_math_description'=>'meta_description','rank_math_focus_keyword'=>'focus_keyword'] as $field=>$source){
        if(get_post_meta($id,$field,true)!==$row[$source]){throw new RuntimeException('SEO readback failed: '.$field);}
    }
    if(get_post_meta($id,'rank_math_primary_category',true)!=(string)$term->term_id
        ||get_post_meta($id,'rank_math_facebook_image',true)!==wp_get_attachment_url($media[0])){throw new RuntimeException('Primary category/social image readback failed.');}
    foreach(array_slice($media,1) as $mid){if(strpos($p->post_content,esc_url(wp_get_attachment_url($mid)))===false){throw new RuntimeException('Inline media not attached to article.');}}
}

function vpn_pg03_import_one(array $row): array {
    $p=vpn_pg03_find($row);
    if($p&&get_post_meta($p->ID,'_vpn_editorial_import_complete',true)){
        if(!vpn_pg03_source_matches($p->ID,$row)){throw new RuntimeException('Different completed revision preserved.');}
        // Once accepted, later edits, unpublishing and trash are the owner's decisions.
        if(get_post_meta($p->ID,'_vpn_pg03_release_accepted',true)===VPN_PG03_VERSION){return ['id'=>$p->ID,'status'=>$p->post_status,'result'=>'completed article preserved'];}
        if($p->post_status!=='publish'&&($p->post_status!=='draft'||get_post_meta($p->ID,'_vpn_pg03_render_hash',true)!==hash('sha256',$p->post_content))){
            throw new RuntimeException('Edited/unpublished review copy preserved.');
        }
        $media=vpn_pg03_media($row,(int)$p->ID);vpn_pg03_verify((int)$p->ID,$row,$media,$p->post_status);
        if($p->post_status==='draft'){$r=wp_update_post(['ID'=>$p->ID,'post_status'=>'publish'],true);if(is_wp_error($r)){throw new RuntimeException($r->get_error_message());}}
        vpn_pg03_verify((int)$p->ID,$row,$media,'publish');update_post_meta($p->ID,'_vpn_pg03_release_accepted',VPN_PG03_VERSION);
        return ['id'=>(int)$p->ID,'status'=>'publish','media'=>$media,'result'=>'reviewed article accepted as public'];
    }
    if($p&&(!in_array($p->post_status,['draft','publish'],true)||$p->post_title!==$row['title']||$p->post_excerpt!==$row['excerpt']||!vpn_pg03_source_matches($p->ID,$row)
        ||($p->post_content!==''&&hash('sha256',$p->post_content)!==get_post_meta($p->ID,'_vpn_pg03_render_hash',true))
        ||($p->post_status==='publish'&&!get_post_meta($p->ID,'_vpn_pg03_ready',true)))){throw new RuntimeException('Edited interrupted article preserved.');}
    $id=$p?(int)$p->ID:wp_insert_post(wp_slash(['post_type'=>'post','post_status'=>'draft','post_name'=>$row['slug'],
        'post_title'=>$row['title'],'post_excerpt'=>$row['excerpt'],'post_content'=>'','post_author'=>get_current_user_id(),
        'comment_status'=>'closed','ping_status'=>'closed','meta_input'=>['_vpn_editorial_batch'=>VPN_PG03_BATCH,'_vpn_editorial_source_hash'=>$row['content_sha256']]]),true);
    if(is_wp_error($id)){throw new RuntimeException($id->get_error_message());}
    $media=vpn_pg03_media($row,(int)$id);$html=$row['source'];
    foreach($row['images'] as $i=>$image){
        if($image['role']==='featured'){set_post_thumbnail($id,$media[$i]);continue;}
        $img=wp_get_attachment_image($media[$i],'full',false,['loading'=>'lazy','decoding'=>'async','sizes'=>'(max-width: 800px) 100vw, 850px']);
        $figure='<figure class="wp-caption alignnone"><a href="'.esc_url(wp_get_attachment_url($media[$i])).'" target="_blank" rel="noopener" aria-label="'.esc_attr('View full image: '.$image['title']).'">'.$img.'</a><figcaption class="wp-caption-text">'.esc_html($image['caption']).'</figcaption></figure>';
        $html=str_replace('<!-- IMAGE_SLOT_'.$image['slot'].' -->',$figure,$html);
    }
    // Canonical internal links are portable; actual media URLs use the destination installation.
    $html=wp_kses_post($html);if(strpos($html,'IMAGE_SLOT')!==false){throw new RuntimeException('Unresolved image slot.');}
    $term=get_term_by('slug',$row['category']['slug'],'category');
    if(!$term){$term=wp_insert_term($row['category']['name'],'category',['slug'=>$row['category']['slug']]);if(is_wp_error($term)){throw new RuntimeException($term->get_error_message());}$category=(int)$term['term_id'];}
    else{$category=(int)$term->term_id;}
    $meta=['rank_math_title'=>$row['seo_title'],'rank_math_description'=>$row['meta_description'],'rank_math_focus_keyword'=>$row['focus_keyword'],
        'rank_math_primary_category'=>(string)$category,'rank_math_facebook_title'=>$row['seo_title'],'rank_math_facebook_description'=>$row['meta_description'],
        'rank_math_facebook_image'=>wp_get_attachment_url($media[0]),'rank_math_facebook_image_id'=>(string)$media[0]];
    foreach($meta as $field=>$value){$old=get_post_meta($id,$field,true);if($old!==''&&$old!==$value){throw new RuntimeException('Interrupted SEO edited; preserved: '.$field);}update_post_meta($id,$field,wp_slash($value));}
    $saved=wp_update_post(wp_slash(['ID'=>$id,'post_content'=>$html,'post_category'=>[$category]]),true);
    if(is_wp_error($saved)||get_post_field('post_content',$id)!==$html){throw new RuntimeException('Content save/readback failed.');}
    update_post_meta($id,'_vpn_pg03_render_hash',hash('sha256',$html));
    vpn_pg03_verify((int)$id,$row,$media,get_post_status($id));update_post_meta($id,'_vpn_pg03_ready',1);
    if(get_post_status($id)!=='publish'){$saved=wp_update_post(['ID'=>$id,'post_status'=>'publish'],true);if(is_wp_error($saved)){throw new RuntimeException($saved->get_error_message());}}
    vpn_pg03_verify((int)$id,$row,$media,'publish');
    update_post_meta($id,'_vpn_pg03_media',$media);update_post_meta($id,'_vpn_editorial_import_complete',1);
    update_post_meta($id,'_vpn_pg03_release_accepted',VPN_PG03_VERSION);
    return ['id'=>(int)$id,'status'=>'publish','media'=>$media,'result'=>'imported, verified and published'];
}

function vpn_pg03_run(): array {
    if(!current_user_can('manage_options')||!current_user_can('publish_posts')){throw new RuntimeException('Administrator with publishing permission required.');}
    if(!defined('RANK_MATH_VERSION')||!in_array('seo-by-rank-math/rank-math.php',(array)get_option('active_plugins'),true)){throw new RuntimeException('Active Rank Math required.');}
    if(!wp_image_editor_supports(['mime_type'=>'image/webp'])){throw new RuntimeException('WebP image editor required.');}
    $data=vpn_pg03_package();require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
    $results=[];
    foreach($data['articles'] as $row){try{$results[$row['key']]=vpn_pg03_import_one($row);}catch(Throwable $e){$results[$row['key']]=['error'=>$e->getMessage()];}}
    return $results;
}

function vpn_pg03_deploy(): void {
    if(get_option(VPN_PG03_OPTION)===VPN_PG03_VERSION||!current_user_can('manage_options')||!current_user_can('publish_posts')||wp_doing_ajax()
        ||(defined('WP_INSTALLING')&&WP_INSTALLING)||(defined('VPN_PG03_SKIP_AUTO')&&VPN_PG03_SKIP_AUTO)){return;}
    if(!in_array(strtolower((string)wp_parse_url(home_url(),PHP_URL_HOST)),['hopgiayvpn.com','www.hopgiayvpn.com'],true)
        ||(int)get_option('vpn_pg03_retry',0)>time()){return;}
    global $wpdb;$lock=substr($wpdb->prefix.'vpn_pg03_release',0,64);
    if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',$lock))!=='1'){return;}
    try{
        @set_time_limit(180);$results=vpn_pg03_run();update_option('vpn_pg03_results',$results,false);
        if(count($results)!==6||array_filter($results,static fn($r)=>isset($r['error']))){throw new RuntimeException('Some articles need review; see per-article results.');}
        update_option(VPN_PG03_OPTION,VPN_PG03_VERSION,false);delete_option('vpn_pg03_error');delete_option('vpn_pg03_retry');set_transient('vpn_pg03_notice',true,120);
    }catch(Throwable $e){update_option('vpn_pg03_error',$e->getMessage(),false);update_option('vpn_pg03_retry',time()+300,false);}
    finally{$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
}
add_action('admin_init','vpn_pg03_deploy',100);
add_action('admin_notices',static function():void{
    if(!current_user_can('manage_options')){return;}
    if($error=get_option('vpn_pg03_error')){
        $details=[];foreach((array)get_option('vpn_pg03_results',[]) as $key=>$row){if(isset($row['error'])){$details[]=$key.': '.$row['error'];}}
        echo '<div class="notice notice-error"><p>'.esc_html('Paper bag buyer guides: '.$error.' '.implode(' | ',$details).' Retry after five minutes; existing posts are preserved.').'</p></div>';
    }elseif(get_transient('vpn_pg03_notice')){delete_transient('vpn_pg03_notice');echo '<div class="notice notice-success is-dismissible"><p>Six approved paper bag buyer guides synced. New articles are public. Later owner edits and status changes are preserved.</p></div>';}
});
