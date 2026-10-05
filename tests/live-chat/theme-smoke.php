<?php
require __DIR__.'/runtime/wp/wp-load.php';
if(($argv[1]??'')==='switch'){
    require_once ABSPATH.'wp-admin/includes/plugin.php';activate_plugin('woocommerce/woocommerce.php');activate_plugin('seo-by-rank-math/rank-math.php');
    update_option('rank_math_registration_skip',true,false); // Official offline setup choice; no account connection.
    global $wp_rewrite;$wp_rewrite->set_permalink_structure('/%postname%/');$wp_rewrite->flush_rules(false);
    switch_theme('custom-box-theme');
    $s=VPN_Chat_Settings::get();$s['paths']=['/','/contact/'];update_option('vpn_chat_settings',$s,false);
    $page=get_page_by_path('contact');if(!$page){$id=wp_insert_post(['post_title'=>'Contact','post_name'=>'contact','post_status'=>'publish','post_type'=>'page']);update_post_meta($id,'_wp_page_template','page-contact.php');}
    echo "Isolated test theme switched to custom-box-theme\n";exit;
}
if(($argv[1]??'')==='restore'){require_once ABSPATH.'wp-admin/includes/plugin.php';deactivate_plugins(['woocommerce/woocommerce.php','seo-by-rank-math/rank-math.php']);switch_theme('twentytwentyfive');echo "Default test theme restored\n";exit;}
// Exercise original quote security helpers without any production form submission.
if(!function_exists('custom_box_quote_form_verify_recaptcha'))require WP_CONTENT_DIR.'/themes/custom-box-theme/inc/quote-form-handler.php';
add_filter('custom_box_quote_form_recaptcha_secret_key',static fn()=>'test-only-recaptcha-secret');
add_filter('custom_box_quote_form_logging_enabled','__return_false');
function qcheck($value,string $name):void{if(!$value)throw new RuntimeException('FAIL '.$name);echo 'PASS '.$name."\n";}
$_POST=[];qcheck(!custom_box_quote_form_verify_recaptcha()['success'],'quote missing captcha denied');
$payload=['success'=>false];
add_filter('pre_http_request',static function($pre,$args,$url)use(&$payload){if($url==='https://www.google.com/recaptcha/api/siteverify')return ['response'=>['code'=>200],'body'=>wp_json_encode($payload),'headers'=>[],'cookies'=>[]];return $pre;},PHP_INT_MAX,3);
$_POST=['g-recaptcha-response'=>'synthetic-token'];qcheck(!custom_box_quote_form_verify_recaptcha()['success'],'quote invalid captcha denied');
$payload=['success'=>true,'action'=>'quote_submit','hostname'=>'127.0.0.1','score'=>0.9];qcheck(custom_box_quote_form_verify_recaptcha()['success'],'quote valid mock accepted');
$payload['action']='other';qcheck(!custom_box_quote_form_verify_recaptcha()['success'],'quote wrong action denied');$payload['action']='quote_submit';$payload['hostname']='evil.invalid';qcheck(!custom_box_quote_form_verify_recaptcha()['success'],'quote wrong hostname denied');$payload['hostname']='127.0.0.1';$payload['score']=0.1;qcheck(!custom_box_quote_form_verify_recaptcha()['success'],'quote low score denied');
$_SERVER['REMOTE_ADDR']='203.0.113.11';$key='custom_box_form_rate_'.md5('quote|'.custom_box_quote_form_ip_hash());delete_transient($key);for($i=0;$i<10;$i++)qcheck(custom_box_quote_form_rate_limit_check()['allowed'],'quote allowed burst '.$i);qcheck(!custom_box_quote_form_rate_limit_check()['allowed'],'quote eleventh request denied');delete_transient($key);
echo "TOTAL 17 quote regression checks passed (no form submitted)\n";
