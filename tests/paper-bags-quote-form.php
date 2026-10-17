<?php
/** CLI integration checks. Mail/retries are intercepted; test leads and transients are cleaned up. */
if ('cli' !== PHP_SAPI) { http_response_code(403); exit; }
$case = $argv[1] ?? 'minimal';
$expected = array('minimal'=>'success', 'canada'=>'success', 'attribution'=>'success', 'mail-failure'=>'received',
    'missing-email'=>'missing', 'invalid-email'=>'missing', 'missing-country'=>'missing', 'consent'=>'consent',
    'invalid-nonce'=>'invalid', 'quantity'=>'quantity', 'fraction'=>'quantity', 'huge-quantity'=>'quantity',
    'duplicate'=>'duplicate', 'file-type'=>'file', 'file-size'=>'file', 'native'=>'success', 'other-source'=>'success');
if (!isset($expected[$case])) { exit(2); }
$_SERVER['HTTP_HOST']='localhost'; $_SERVER['SERVER_PORT']='80'; $_SERVER['REQUEST_METHOD']='POST';
$_SERVER['REMOTE_ADDR']='198.51.100.246'; $_SERVER['REQUEST_URI']='/hopgiayvpn/wp-admin/admin-post.php';
require dirname(__DIR__).'/wp-load.php';
wp_set_current_user(0);
add_filter('custom_box_quote_form_logging_enabled','__return_false');
$saved=array(); $errors=array(); $mail_calls=0; $retry_calls=0; $redirect='';
add_action('wp_insert_post',static function($id,$post)use(&$saved){ if('custom_box_quote'===$post->post_type){$saved[]=(int)$id;} },10,2);
add_filter('pre_wp_mail',static function($pre,$mail)use($case,&$mail_calls,&$errors){
    $mail_calls++;
    if('sales.vpn@hopgiayvpn.com'!==$mail['to']){$errors[]='Unexpected mail recipient';}
    if(false===strpos($mail['message'],'paper-bag-qa@example.invalid')){$errors[]='Missing email in message';}
    return 'mail-failure'!==$case;
},PHP_INT_MAX,2);
add_filter('pre_as_enqueue_async_action',static function()use(&$retry_calls){$retry_calls++;return 1;});
add_filter('pre_schedule_event',static function($pre,$event)use(&$retry_calls){if('custom_box_send_queued_quote_email'===$event->hook){$retry_calls++;return true;}return $pre;},10,2);
add_filter('wp_redirect',static function($url)use(&$redirect){$redirect=$url;return $url;});
$started=time()-10;
$_POST=array('action'=>'custom_box_quote_form','quote_source'=>'custom_paper_bags_manufacturer',
    'response_format'=>'paper_bag_json','form_location'=>'paper_bags_hero','form_anchor'=>'quote',
    '_wp_http_referer'=>custom_box_custom_paper_bags_url(),'current_page_url'=>custom_box_custom_paper_bags_url(),
    'product_name'=>'Custom paper bags','email'=>'paper-bag-qa@example.invalid','full_name'=>'','quantity'=>'1000',
    'country'=>'United States','privacy_consent'=>'yes','website_url'=>'','custom_box_form_context'=>'quote',
    'custom_box_form_started_at'=>$started,'custom_box_form_signature'=>custom_box_quote_form_timestamp_signature($started,'quote'),
    'custom_box_quote_nonce'=>wp_create_nonce('custom_box_quote_form'));
if('canada'===$case){$_POST['country']='Canada';}
if('attribution'===$case){foreach(array('utm_source'=>'google','utm_medium'=>'cpc','utm_campaign'=>'paper-bag-qa','utm_term'=>'custom paper bags','utm_content'=>'ad-a','gclid'=>'test-gclid','gbraid'=>'test-gbraid','wbraid'=>'test-wbraid','referrer_url'=>'https://example.invalid/')as$key=>$value){$_POST[$key]=$value;}}
if('missing-email'===$case){$_POST['email']='';}
if('invalid-email'===$case){$_POST['email']='invalid email';}
if('missing-country'===$case){$_POST['country']='';}
if('consent'===$case){unset($_POST['privacy_consent']);}
if('invalid-nonce'===$case){$_POST['custom_box_quote_nonce']='bad';}
if('quantity'===$case){$_POST['quantity']='999';}
if('fraction'===$case){$_POST['quantity']='1000.5';}
if('huge-quantity'===$case){$_POST['quantity']='100000001';}
if('file-type'===$case){$_FILES['artwork']=array('name'=>'bad.exe','size'=>4);}
if('file-size'===$case){$_FILES['artwork']=array('name'=>'large.pdf','size'=>11*1024*1024);}
if('native'===$case){$_POST['response_format']='';}
if('other-source'===$case){$_POST['quote_source']='custom_pizza_boxes_manufacturer';$_POST['full_name']='Paper Bag QA';}
$duplicate_key=custom_box_quote_form_duplicate_key($_POST['email'],'',$_POST['product_name'],$_POST['quantity']);
$prior_duplicate=get_transient($duplicate_key);delete_transient($duplicate_key);
if('duplicate'===$case){set_transient($duplicate_key,123,60);}
$rate_key='custom_box_form_rate_'.md5('quote|'.custom_box_quote_form_ip_hash());
$prior_rate=get_transient($rate_key);delete_transient($rate_key);
$captured='';
// WordPress flushes buffers in its earlier shutdown callback; capture before that flush.
ob_start(static function($buffer)use(&$captured){$captured.=$buffer;return '';});
register_shutdown_function(static function()use($case,$expected,&$saved,&$errors,&$mail_calls,&$retry_calls,&$redirect,$duplicate_key,$prior_duplicate,$rate_key,$prior_rate,&$captured){
    if(ob_get_level()){ob_end_flush();}
    $raw=$captured;$result=json_decode($raw,true);$query=array();parse_str((string)wp_parse_url($redirect,PHP_URL_QUERY),$query);
    $status=$result['data']['status']??($query['quote_status']??'');
    $should_save=in_array($expected[$case],array('success','received'),true);
    $saved=array_unique($saved);
    if($status!==$expected[$case]){$errors[]='Unexpected status: '.$status.' raw: '.substr($raw,0,150);}
    if(count($saved)!==($should_save?1:0)){$errors[]='Wrong lead count';}
    if($mail_calls!==($should_save?1:0)){$errors[]='Wrong mail count';}
    if('mail-failure'===$case&&1!==$retry_calls){$errors[]='Missing exactly one retry';}
    if(!in_array($case,array('native','other-source'),true)&&!is_array($result)){$errors[]='JSON not returned';}
    if(in_array($case,array('native','other-source'),true)&&is_array($result)){$errors[]='Legacy redirect changed';}
    if($should_save&&!in_array($case,array('native','other-source'),true)&&!preg_match('/^vpn-quote-\d+$/',$result['data']['receipt']??'')){$errors[]='Missing server receipt';}
    foreach($saved as$id){
        $data=get_post_meta($id,'_custom_box_quote_data',true);
        if('private'!==get_post_status($id)){$errors[]='Lead not private';}
        if('Paper bag quote enquiry'!==($data['full_name']??'')&&'other-source'!==$case){$errors[]='Optional name fallback failed';}
        if('yes'!==($data['privacy_consent']??'')){$errors[]='Consent not saved';}
        if('attribution'===$case){foreach(array('utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid','referrer_url')as$key){if(($data[$key]??'')!==$_POST[$key]){$errors[]='Attribution missing: '.$key;}}}
        if('mail-failure'===$case&&'failed'!==get_post_meta($id,'_custom_box_quote_mail_status',true)){$errors[]='Wrong queued mail state';}
        wp_clear_scheduled_hook('custom_box_send_queued_quote_email',array($id));wp_delete_post($id,true);
    }
    delete_transient($duplicate_key);if(false!==$prior_duplicate){set_transient($duplicate_key,$prior_duplicate,15*MINUTE_IN_SECONDS);}
    delete_transient($rate_key);if(false!==$prior_rate){set_transient($rate_key,$prior_rate,10*MINUTE_IN_SECONDS);}
    echo wp_json_encode(array('case'=>$case,'passed'=>!$errors,'status'=>$status,'saved'=>count($saved),'mail_calls'=>$mail_calls,'retry_calls'=>$retry_calls,'errors'=>$errors))."\n";
    if($errors){exit(1);}
});
custom_box_handle_quote_form();
