<?php
/**
 * Fixed deployment migration: five owner-approved paper bags and thirty WebP assets.
 * Runs once after pull/deploy; does not accept request-selected content or files.
 */
defined('ABSPATH') || exit;
function vpn_paper_bags_20261013_run(): array {
$dir=__DIR__.'/vpn-paper-bags-20261013';
$data=json_decode(file_get_contents($dir.'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
$marker=$data['marker'];$rows=$data['products'];$ids=[];$results=[];$pending=[];
$term=get_term_by('slug',$data['category_slug'],'product_cat');
if(!$term||is_wp_error($term)||count($rows)!==5||!class_exists('WC_Product_Simple')){throw new RuntimeException('Category or WooCommerce preflight failed.');}
$authors=get_users(['role'=>'administrator','number'=>1,'orderby'=>'ID','fields'=>'ID']);
if(!$authors){throw new RuntimeException('Local administrator not available.');}
wp_set_current_user((int)$authors[0]);
$routes=rest_get_server()->get_routes();
$guide=get_page_by_path('how-to-make-paper-bags-stronger',OBJECT,'post');
$contact=get_page_by_path('contact',OBJECT,'page');
if(!$guide||!$contact){throw new RuntimeException('Missing verified strength guide or Contact page.');}
$preflight=[];
foreach($rows as $p){
 $html=str_replace(["\r\n","\r"],"\n",file_get_contents($dir.'/'.$p['content_file']));
 if(!hash_equals($p['content_sha256'],hash('sha256',$html))||preg_match('/<h1\b/i',$html)||$p['wordcount']<1500||$p['wordcount']>2000||count($p['images'])!==6){throw new RuntimeException('Invalid content: '.$p['slug']);}
 if(stripos($p['seo_title'],$p['keyword'])===false||stripos($p['seo_description'],$p['keyword'])===false||strlen($p['seo_title'])>60||strlen($p['seo_description'])>155){throw new RuntimeException('SEO preflight: '.$p['slug']);}
 $post=get_page_by_path($p['slug'],OBJECT,'product');
 if($post && get_post_meta($post->ID,'_vpn_sample_import',true)!==$marker){throw new RuntimeException('Unrelated slug exists: '.$p['slug']);}
 if($post && get_post_meta($post->ID,'_vpn_paper_bags_complete',true)){$ids[$p['code']]=(int)$post->ID;continue;}
 if($post && trim($post->post_content)!==''){
  $previous=get_post_meta($post->ID,'_vpn_paper_bags_saved_content_hash',true);
  if(!$previous||!hash_equals($previous,hash('sha256',$post->post_content))){throw new RuntimeException('Edited partial import preserved: '.$p['slug']);}
 }
 if($post && ($post->post_status!=='draft'||$post->post_title!==$p['title'])){throw new RuntimeException('Edited interrupted draft preserved: '.$p['slug']);}
 $sku='VPN-PB-20261013-'.$p['code'];$sku_id=wc_get_product_id_by_sku($sku);
 if($sku_id && (!$post||(int)$post->ID!==$sku_id)){throw new RuntimeException('SKU conflict: '.$sku);}
 foreach($p['images'] as $img){$f=$dir.'/images/'.basename($img['file']);$size=getimagesize($f);
 if(!$size||$size[0]!==450||$size[1]!==570||$size['mime']!=='image/webp'||filesize($f)>=100000||!hash_equals($img['sha256'],hash_file('sha256',$f))){throw new RuntimeException('Image preflight: '.$img['file']);}}
 $pending[]=$p;
 $preflight[]=['slug'=>$p['slug'],'existing_id'=>$post?(int)$post->ID:null,'sku'=>$sku];
}
if(!$pending){return array_values($ids);}
add_option('vpn_paper_bags_20261013_before', ['home'=>home_url('/'),'products'=>$preflight], '', false);
require_once ABSPATH.'wp-admin/includes/image.php';
$uploads=wp_upload_dir();if($uploads['error']){throw new RuntimeException($uploads['error']);}
$relative='2026/10/vpn-paper-bags-20261013/';$target=trailingslashit($uploads['basedir']).$relative;
if(!wp_mkdir_p($target)){throw new RuntimeException('Upload folder unavailable');}
foreach($pending as $p){$post=get_page_by_path($p['slug'],OBJECT,'product');$wc=$post?new WC_Product_Simple((int)$post->ID):new WC_Product_Simple();
 $wc->set_name($p['title']);$wc->set_slug($p['slug']);$wc->set_status('draft');$wc->set_sku('VPN-PB-20261013-'.$p['code']);$wc->set_category_ids([(int)$term->term_id]);$wc->set_catalog_visibility('visible');
 $wc->set_regular_price('');$wc->set_sale_price('');$wc->set_price('');$wc->set_manage_stock(false);$wc->set_stock_status('onbackorder');
 $wc->update_meta_data('_vpn_sample_import',$marker);$id=$wc->save();if(!$id){throw new RuntimeException('Draft save failed');}$ids[$p['code']]=$id;
 wp_update_post(['ID'=>$id,'post_author'=>get_current_user_id()]);
}
$replace=['{{CATEGORY_URL}}'=>get_term_link($term),'{{GUIDE_URL}}'=>get_permalink($guide),'{{CONTACT_URL}}'=>get_permalink($contact)];
foreach($ids as $code=>$id){$link_post=clone get_post($id);$link_post->post_status='publish';$replace['{{'.$code.'_URL}}']=get_permalink($link_post);}
foreach($pending as $p){$id=$ids[$p['code']];$images=[];
 foreach($p['images'] as $img){$source=$dir.'/images/'.$img['file'];$destination=$target.$img['file'];
  if(is_file($destination)&&!hash_equals($img['sha256'],hash_file('sha256',$destination))){throw new RuntimeException('Different existing image preserved');}
  if(!is_file($destination)&&!copy($source,$destination)){throw new RuntimeException('Image copy failed');}
  $found=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_vpn_paper_bag_asset','meta_value'=>$marker.'/'.$img['file'],'fields'=>'ids']);
  if($found){$aid=(int)$found[0];}else{
   $aid=wp_insert_attachment(['post_mime_type'=>'image/webp','post_title'=>$img['title'],'post_excerpt'=>$img['caption'],'post_parent'=>$id,'post_status'=>'inherit'],$destination,$id,true);
   if(is_wp_error($aid)){throw new RuntimeException($aid->get_error_message());}
   update_post_meta($aid,'_vpn_paper_bag_asset',$marker.'/'.$img['file']);
   wp_update_attachment_metadata($aid,wp_generate_attachment_metadata($aid,$destination));
  }
  update_post_meta($aid,'_wp_attachment_image_alt',$img['alt']);$images[]=$aid;
 }
 $tokens=$replace;
 foreach([2,4,5] as $number){$img=$p['images'][$number-1];$tokens['{{FIGURE_'.$number.'}}']='<figure class="product-inline-figure product-inline-figure-small">'.wp_get_attachment_image($images[$number-1],'full',false,['loading'=>'lazy']).'<figcaption>'.esc_html($img['caption']).'</figcaption></figure>';}
 $html=strtr(str_replace(["\r\n","\r"],"\n",file_get_contents($dir.'/'.$p['content_file'])),$tokens);
 if(preg_match('/\{\{[A-Z0-9_]+\}\}/',$html)){throw new RuntimeException('Unresolved link or image token');}
 $wc=new WC_Product_Simple($id);$wc->set_description($html);$wc->set_short_description($p['short_description']);$wc->set_image_id($images[0]);$wc->set_gallery_image_ids(array_slice($images,1));
 $specs=['Feature'=>$p['handle'],'Industrial Use'=>$p['use'],'Paper Type'=>$p['paper'],'Box Type'=>'Custom paper bag','Shape'=>'Rectangular gusseted body with folded base','Place of Origin'=>'Vietnam','Model Number'=>'VPN-PB-20261013-'.$p['code'],'Brand Name'=>'VPN','Province'=>'Ho Chi Minh City','Accessories'=>'Additional inserts, closures or tissue confirmed separately','Custom Order'=>'Accept','Liner Type'=>'No specific barrier or liner confirmed; review by intended use','Logo Printing'=>'Custom logo','Printing Handling'=>'Process, colors and print panels confirmed with selected paper and artwork','Color'=>$p['color'],'Size'=>'Customized size','Thickness'=>'Customized thickness; GSM confirmed in the approved specification','Single Piece Price'=>'Written quotation based on specification and quantity','Minimum Order Quantity (MOQ)'=>'1,000 units planning minimum; confirm the product specification in the quote','Product Name'=>$p['title'],'Design'=>"Customer's Specific Requirement"];
 $formatted=[];foreach($specs as $label=>$value){$formatted[]=['label'=>$label,'value'=>$value];}
 $wc->update_meta_data('_custom_box_product_specs',$formatted);$wc->update_meta_data('_custom_box_product_faq_html','');$wc->update_meta_data('_vpn_paper_bags_source_hash',$p['content_sha256']);$wc->update_meta_data('_vpn_paper_bags_saved_content_hash',hash('sha256',$html));$wc->save();
 $meta=['rank_math_title'=>$p['seo_title'],'rank_math_description'=>$p['seo_description'],'rank_math_focus_keyword'=>$p['keyword'],'rank_math_primary_product_cat'=>(int)$term->term_id];
 if(isset($routes['/rankmath/v1/updateMeta'])){
  $request=new WP_REST_Request('POST','/rankmath/v1/updateMeta');$request->set_header('Content-Type','application/json');$request->set_body(wp_json_encode(['objectType'=>'post','objectID'=>$id,'meta'=>$meta]));
  $response=rest_do_request($request);if($response->is_error()){throw new RuntimeException('Rank Math write failed: '.wp_json_encode($response->get_data()));}
 }else{throw new RuntimeException('Expected Rank Math own write route missing');}
 foreach($meta as $k=>$v){if((string)get_post_meta($id,$k,true)!==(string)$v){throw new RuntimeException('SEO readback mismatch: '.$k);}}
 $saved=wc_get_product($id);$body=$saved->get_description();
 if(count($saved->get_gallery_image_ids())!==5||!$saved->get_image_id()||preg_match('/<h1\b/i',$body)||substr_count($body,'product-inline-figure-small')!==3||substr_count($body,'schema.org/Question')!==6){throw new RuntimeException('Saved content validation failed');}
 $results[]=['code'=>$p['code'],'id'=>$id,'url'=>get_permalink($id),'editor'=>admin_url('post.php?post='.$id.'&action=edit'),'image_ids'=>$images,'seo_title'=>$p['seo_title'],'meta_description'=>$p['seo_description'],'keyword'=>$p['keyword'],'wordcount'=>$p['wordcount']];
}
// Publish this fixed batch only after all pending products and metadata pass readback.
foreach($results as $r){$wc=new WC_Product_Simple($r['id']);$wc->set_status('publish');$wc->update_meta_data('_vpn_paper_bags_complete',true);$wc->save();wc_delete_product_transients($r['id']);clean_post_cache($r['id']);}

return array_values($ids);
}
function vpn_paper_bags_20261013_deploy(): void {
 $done='vpn_paper_bags_20261013_done';$lock='vpn_paper_bags_20261013_lock';
 if(get_option($done)||(defined('WP_INSTALLING')&&WP_INSTALLING)){return;}
 $host=strtolower((string)wp_parse_url(home_url('/'),PHP_URL_HOST));
 if(!in_array($host,['hopgiayvpn.com','www.hopgiayvpn.com','localhost','127.0.0.1'],true)){return;}
 if(!class_exists('WC_Product_Simple')||!function_exists('rest_get_server')){return;}
 if((int)get_option('vpn_paper_bags_20261013_retry',0)>time()){return;}
 $started=(int)get_option($lock,0);if($started&&$started<time()-900){delete_option($lock);}
 if(!add_option($lock,time(),'',false)){return;}
 $previous_user=get_current_user_id();
 try {
  @set_time_limit(300);
  $ids=vpn_paper_bags_20261013_run();
  if(count($ids)!==5){throw new RuntimeException('Expected five product IDs.');}
  update_option($done,$ids,false);
  delete_option('vpn_paper_bags_20261013_error');delete_option('vpn_paper_bags_20261013_retry');
  foreach($ids as $id){wc_delete_product_transients($id);clean_post_cache($id);}
  if(in_array($host,['hopgiayvpn.com','www.hopgiayvpn.com'],true)){
   try{foreach($ids as $id){do_action('litespeed_purge_post',$id);}
    do_action('litespeed_purge_url',home_url('/products/paper-bags-with-logo/'));
    do_action('litespeed_purge_url',home_url('/products/'));
   }catch(Throwable $cache_error){error_log('Paper bags imported; cache purge: '.$cache_error->getMessage());}
  }
 } catch(Throwable $e){
  update_option('vpn_paper_bags_20261013_error',$e->getMessage(),false);
  update_option('vpn_paper_bags_20261013_retry',time()+300,false);
  error_log('Paper bags deployment: '.$e->getMessage());
 } finally {wp_set_current_user($previous_user);delete_option($lock);}
}
add_action('init','vpn_paper_bags_20261013_deploy',110);
add_action('admin_notices',static function(){
 if(!current_user_can('manage_options')){return;}
 $error=get_option('vpn_paper_bags_20261013_error');
 if($error){echo '<div class="notice notice-error"><p>Paper bags import: '.esc_html($error).' Automatic retry in five minutes.</p></div>';}
});
