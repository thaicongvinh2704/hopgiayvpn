<?php
defined('ABSPATH') || exit;
if (DB_NAME !== 'vpn_chat_test' || wp_get_environment_type() !== 'local') { throw new RuntimeException('Wrong test environment'); }
add_filter('pre_wp_mail',static function($pre,$atts){
    global $wpdb;$wpdb->insert('vct_test_mails',['recipient'=>is_array($atts['to'])?implode(',',$atts['to']):$atts['to']]);
    update_option('vpn_test_mail_count',(int)get_option('vpn_test_mail_count',0)+1,false);
    update_option('vpn_test_last_mail',$atts,false);
    return (bool)get_option('vpn_test_mail_success',false);
},PHP_INT_MAX,2);
add_filter('pre_http_request',static function($pre,$args,$url){
    if($url!=='https://challenges.cloudflare.com/turnstile/v0/siteverify')return new WP_Error('test_outbound_disabled','No external HTTP in tests');
    global $wpdb;
    $token=(string)($args['body']['response']??'');
    if(str_starts_with($token,'timeout'))return new WP_Error('timeout','Simulated timeout');
    $valid=str_starts_with($token,'valid-')||str_starts_with($token,'wrong-host-')||str_starts_with($token,'wrong-action-');
    if($valid){$insert=$wpdb->query($wpdb->prepare('INSERT IGNORE INTO vct_test_tokens (hash) VALUES (%s)',hash('sha256',$token)));$valid=$insert===1;}
    return ['headers'=>[],'response'=>['code'=>200,'message'=>'OK'],'body'=>wp_json_encode(['success'=>$valid,'hostname'=>str_starts_with($token,'wrong-host-')?'attacker.invalid':'127.0.0.1','action'=>str_starts_with($token,'wrong-action-')?'other':'vpn_chat_start']),'cookies'=>[]];
},PHP_INT_MAX,3);
