<?php
// Isolated test database only. Never load the main site's wp-load.php here.
ob_start();
require __DIR__.'/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test'||DB_HOST!=='127.0.0.1:3311'||!VPN_Chat_Settings::local())exit(1);
global $wpdb;
VPN_Chat_Schema::activate();
$passed=[];
function ac($v,$name){global $passed;if(!$v)throw new RuntimeException('FAIL '.$name);$passed[]=$name;}
function rest_chat($path,$body=[],$cookies=[],$user=0,$csrf='', $method='POST',$origin='http://127.0.0.1:8091'){
 $_COOKIE=$cookies;wp_set_current_user($user);$r=new WP_REST_Request($method,'/vpn-chat/v1/'.$path);
 $r->set_header('origin',$origin);$r->set_header('content-type','application/json');$r->set_header('x-vpn-csrf',$csrf);if($user)$r->set_header('x-wp-nonce',wp_create_nonce('wp_rest'));
 if($method==='GET')$r->set_query_params($body);else $r->set_body(wp_json_encode($body));return rest_do_request($r);
}
function boot_fixture(){ $r=rest_chat('bootstrap');ac($r->get_status()===200,'bootstrap creates isolated guest session');return ['data'=>$r->get_data(),'cookies'=>$_COOKIE]; }
try{
 foreach(VPN_Chat_Schema::TABLES as $t)VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table($t));
 ac(VPN_Chat_Schema::healthy(),'schema 3 tables use InnoDB');
 $s=VPN_Chat_Settings::get();$original=$s;$s['accept_new']=true;$s['site_key']='test-only-site-key';$s['short_limit']=100;$s['long_limit']=200;$s['conversation_limit']=20;update_option('vpn_chat_settings',$s,false);
 $a=boot_fixture();$b=boot_fixture();ac($a['data']['customer']['code']!==$b['data']['customer']['code'],'anonymous customers have distinct identity');
 $cookies=$a['cookies'];$csrf=$a['data']['csrf'];$p=['message'=>'Final isolated acceptance','client_message_id'=>bin2hex(random_bytes(16)),'challenge'=>'valid-'.bin2hex(random_bytes(16))];
 ac(rest_chat('guest/start',$p,$cookies)->get_status()===403,'CSRF required');
 ac(rest_chat('guest/start',$p,[],0,$csrf)->get_status()===401,'session cookie required');
 ac(rest_chat('guest/start',$p,$cookies,0,$csrf,'POST','https://attacker.invalid')->get_status()===403,'cross-origin mutation rejected');
 foreach(['','forged','wrong-host-'.uniqid(),'wrong-action-'.uniqid(),'timeout-'.uniqid()] as $token){$invalid=$p;$invalid['challenge']=$token;ac(in_array(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status(),[403,503],true),'invalid verification rejected: '.explode('-',$token)[0]);}
 $invalid=$p;$invalid['email']='bad-address';ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===400,'invalid optional email rejected');
 $invalid=$p;$invalid['message']='';ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===400,'empty message rejected');
 $invalid=$p;$invalid['message']=str_repeat('x',2001);ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===400,'oversized message rejected');
 $invalid=$p;$invalid['files']=[];ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===400,'attachment payload rejected');
 $r=rest_chat('guest/start',$p,$cookies,0,$csrf);ac($r->get_status()===200,'anonymous first message accepted without name/email');$id=$r->get_data()['id'];
 $r=rest_chat('guest/start',$p,$cookies,0,$csrf);ac($r->get_status()===200,'first-message retry accepted idempotently');
 ac((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages'))===1,'retry stores exactly one message');
 $invalid=$p;$invalid['message']='changed';ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===409,'same retry ID cannot change message');
 $sync=rest_chat('guest/sync',['id'=>$id,'cursor'=>0],$cookies,0,'','GET');ac($sync->get_status()===200&&count($sync->get_data()['messages'])===1,'saved message visible in sync');
 ac(rest_chat('guest/sync',['id'=>$id,'cursor'=>0],$b['cookies'],0,'','GET')->get_status()===404,'other anonymous customer cannot read chat');
 ac(rest_chat('guest/send',['id'=>$id,'message'=>'Intrusion','client_message_id'=>bin2hex(random_bytes(16))],$b['cookies'],0,$b['data']['csrf'])->get_status()===404,'other customer cannot send to chat');
 ac(rest_chat('guest/contact',['id'=>$id,'email'=>'buyer@example.invalid'],$cookies,0,$csrf)->get_status()===200,'optional email saved');
 ac(rest_chat('guest/contact',['id'=>$id,'email'=>'invalid'],$cookies,0,$csrf)->get_status()===400,'invalid follow-up email rejected');
 ac(rest_chat('guest/contact',['id'=>$id,'email'=>''],$cookies,0,$csrf)->get_status()===200,'email may be removed');
 $restored=rest_chat('bootstrap',[],$cookies)->get_data();ac($restored['conversation']===$id,'bootstrap restores existing conversation');
 $body=['id'=>$id,'message'=>'Second message','client_message_id'=>bin2hex(random_bytes(16))];ac(rest_chat('guest/send',$body,$cookies,0,$csrf)->get_status()===200,'subsequent message accepted');
 ac(rest_chat('guest/send',$body,$cookies,0,$csrf)->get_status()===200,'subsequent retry accepted');
 ac((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages'))===2,'subsequent retry does not duplicate');
 $manager=(int)get_user_by('login','chat-test-manager')->ID;$agent=(int)get_user_by('login','agent-a')->ID;$viewer=(int)get_user_by('login','viewer')->ID;
 $reply=['id'=>$id,'message'=>'Reply from isolated sales','client_message_id'=>bin2hex(random_bytes(16))];
 ac(rest_chat('agent/send',$reply,[],$viewer)->get_status()===403,'subscriber cannot reply as sales');
 $before=VPN_Chat_Store::conversation($id);ac(rest_chat('agent/update',['id'=>$id,'action'=>'claim','version'=>(int)$before['version']],[],$agent)->get_status()===200,'sales can claim unassigned chat');
 ac(rest_chat('agent/send',$reply,[],$agent)->get_status()===200,'sales reply accepted after inbox claim');
 $sync=rest_chat('guest/sync',['id'=>$id,'cursor'=>2],$cookies,0,'','GET')->get_data();ac(count($sync['messages'])===1&&$sync['messages'][0]['sender']==='agent','sales reply delivered to correct guest');
 $note=$reply;$note['message']='Private note';$note['client_message_id']=bin2hex(random_bytes(16));$note['note']=true;ac(rest_chat('agent/send',$note,[],$agent)->get_status()===200,'sales private note saved');
 $sync=rest_chat('guest/sync',['id'=>$id,'cursor'=>0],$cookies,0,'','GET')->get_data();ac(count($sync['messages'])===3,'private notes never exposed to guest');
 $read=rest_chat('guest/read',['id'=>$id,'cursor'=>$sync['cursor']],$cookies,0,$csrf);ac($read->get_status()===200&&$read->get_data()['unread']===0,'guest read acknowledgement clears unread');
 $c=VPN_Chat_Store::conversation($id);$wpdb->update(VPN_Chat_Store::table('conversations'),['status'=>'closed'],['id'=>$c['id']]);
 $body['client_message_id']=bin2hex(random_bytes(16));ac(rest_chat('guest/send',$body,$cookies,0,$csrf)->get_status()===409,'closed chat rejects new messages');
 $new=$p;$new['client_message_id']=bin2hex(random_bytes(16));$new['challenge']='valid-'.bin2hex(random_bytes(16));ac(rest_chat('guest/start',$new,$cookies,0,$csrf)->get_status()===200,'new chat permitted after closing');
 $disabled=$s;$disabled['site_key']='';update_option('vpn_chat_settings',$disabled,false);ac(!VPN_Chat_Settings::public_config()['accepting'],'missing site key disables new chat');
 $x=boot_fixture();$new['client_message_id']=bin2hex(random_bytes(16));ac(rest_chat('guest/start',$new,$x['cookies'],0,$x['data']['csrf'])->get_status()===503,'backend refuses unconfigured chat');
 update_option('vpn_chat_settings',$s,false);$expired=$cookies;$_COOKIE=$cookies;$session=VPN_Chat_Security::session();$wpdb->update(VPN_Chat_Store::table('sessions'),['expires_at'=>'2000-01-01 00:00:00'],['id'=>$session['id']]);ac(rest_chat('guest/contact',['id'=>$id,'email'=>'x@example.invalid'],$expired,0,$csrf)->get_status()===401,'expired session cannot mutate');
}finally{
 if(isset($original))update_option('vpn_chat_settings',$original,false);
 // This is the dedicated test DB; leave it clean for real browser acceptance.
 foreach(VPN_Chat_Schema::TABLES as $t)VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table($t));
 $report=['version'=>VPN_CHAT_VERSION,'checks'=>count($passed),'passed'=>$passed,'database'=>'isolated vpn_chat_test on port 3311','cleaned'=>true];
 file_put_contents(__DIR__.'/../../artifacts/vpn-live-chat/evidence/final-backend-report.json',wp_json_encode($report,JSON_PRETTY_PRINT));ob_end_clean();echo wp_json_encode($report,JSON_PRETTY_PRINT);
}
