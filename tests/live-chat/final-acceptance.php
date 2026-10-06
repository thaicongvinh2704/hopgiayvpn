<?php
// Isolated test database only. Never load the main site's wp-load.php here.
ob_start();
define('VPN_CHAT_TURNSTILE_SECRET','');
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
 $s=VPN_Chat_Settings::get();$original=$s;$s['accept_new']=true;$s['site_key']='';$s['short_limit']=100;$s['long_limit']=200;$s['conversation_limit']=20;update_option('vpn_chat_settings',$s,false);
 ac(VPN_Chat_Settings::ready(),'chat ready without site key or secret');ac(VPN_Chat_Settings::public_config()['site_key']==='','browser never requests Turnstile');
 $a=boot_fixture();$b=boot_fixture();ac($a['data']['customer']['code']!==$b['data']['customer']['code'],'anonymous customers have distinct identity');
 $cookies=$a['cookies'];$csrf=$a['data']['csrf'];$p=['message'=>'Final isolated acceptance','client_message_id'=>bin2hex(random_bytes(16)),'challenge'=>'valid-'.bin2hex(random_bytes(16))];
 ac(rest_chat('guest/start',$p,$cookies)->get_status()===403,'CSRF required');
 ac(rest_chat('guest/start',$p,[],0,$csrf)->get_status()===401,'session cookie required');
 ac(rest_chat('guest/start',$p,$cookies,0,$csrf,'POST','https://attacker.invalid')->get_status()===403,'cross-origin mutation rejected');
 unset($p['challenge']);
 $invalid=$p;$invalid['email']='bad-address';ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===400,'invalid optional email rejected');
 $invalid=$p;$invalid['message']='';ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===400,'empty message rejected');
 $invalid=$p;$invalid['message']=str_repeat('x',2001);ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===400,'oversized message rejected');
 $invalid=$p;$invalid['files']=[];ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===400,'attachment payload rejected');
 $r=rest_chat('guest/start',$p,$cookies,0,$csrf);ac($r->get_status()===200,'anonymous first message accepted without name/email');$id=$r->get_data()['id'];$firstReceipt=$r->get_data()['message'];ac($firstReceipt['body']===$p['message']&&(int)$firstReceipt['seq']===1&&!empty($firstReceipt['created_at']),'first receipt contains canonical stored message');
 $r=rest_chat('guest/start',$p,$cookies,0,$csrf);ac($r->get_status()===200,'first-message retry accepted idempotently');ac($r->get_data()['message']===$firstReceipt,'first retry returns identical receipt');
 ac((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages'))===1,'retry stores exactly one message');
 $invalid=$p;$invalid['message']='changed';ac(rest_chat('guest/start',$invalid,$cookies,0,$csrf)->get_status()===409,'same retry ID cannot change message');
 $sync=rest_chat('guest/sync',['id'=>$id,'cursor'=>0],$cookies,0,'','GET');ac($sync->get_status()===200&&count($sync->get_data()['messages'])===1,'saved message visible in sync');ac(rest_chat('guest/sync',['id'=>$id,'cursor'=>0],$cookies,0,$csrf)->get_status()===200,'POST sync supports uncached browser polling');ac(rest_chat('guest/sync',['id'=>$id,'cursor'=>0],$cookies)->get_status()===403,'POST sync requires CSRF');ac(rest_chat('guest/sync',['id'=>$id,'cursor'=>0],$b['cookies'],0,$b['data']['csrf'])->get_status()===404,'POST sync rejects other customer');
 ac(rest_chat('guest/sync',['id'=>$id,'cursor'=>0],$b['cookies'],0,'','GET')->get_status()===404,'other anonymous customer cannot read chat');
 ac(rest_chat('guest/send',['id'=>$id,'message'=>'Intrusion','client_message_id'=>bin2hex(random_bytes(16))],$b['cookies'],0,$b['data']['csrf'])->get_status()===404,'other customer cannot send to chat');
 ac(rest_chat('guest/contact',['id'=>$id,'email'=>'buyer@example.invalid'],$cookies,0,$csrf)->get_status()===200,'optional email saved');
 ac(rest_chat('guest/contact',['id'=>$id,'email'=>'invalid'],$cookies,0,$csrf)->get_status()===400,'invalid follow-up email rejected');
 ac(rest_chat('guest/contact',['id'=>$id,'email'=>''],$cookies,0,$csrf)->get_status()===200,'email may be removed');
 $restored=rest_chat('bootstrap',[],$cookies)->get_data();ac($restored['conversation']===$id,'bootstrap restores existing conversation');
 usleep(1100000);$body=['id'=>$id,'message'=>'Second message','client_message_id'=>bin2hex(random_bytes(16))];ac(rest_chat('guest/send',$body,$cookies,0,$csrf)->get_status()===200,'subsequent message accepted');
 $retryResponse=rest_chat('guest/send',$body,$cookies,0,$csrf);ac($retryResponse->get_status()===200,'subsequent retry accepted');ac($retryResponse->get_data()['message']['body']===$body['message']&&(int)$retryResponse->get_data()['message']['seq']===2,'subsequent receipt matches stored message');
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
 usleep(1100000);$new=$p;$new['client_message_id']=bin2hex(random_bytes(16));ac(rest_chat('guest/start',$new,$cookies,0,$csrf)->get_status()===200,'new chat permitted after closing');
 $disabled=$s;$disabled['accept_new']=false;update_option('vpn_chat_settings',$disabled,false);ac(!VPN_Chat_Settings::public_config()['accepting'],'admin may still disable new chat');
 $x=boot_fixture();$new['client_message_id']=bin2hex(random_bytes(16));ac(rest_chat('guest/start',$new,$x['cookies'],0,$x['data']['csrf'])->get_status()===503,'backend refuses unconfigured chat');
 update_option('vpn_chat_settings',$s,false);$expired=$cookies;$_COOKIE=$cookies;$session=VPN_Chat_Security::session();$wpdb->update(VPN_Chat_Store::table('sessions'),['expires_at'=>'2000-01-01 00:00:00'],['id'=>$session['id']]);ac(rest_chat('guest/contact',['id'=>$id,'email'=>'x@example.invalid'],$expired,0,$csrf)->get_status()===401,'expired session cannot mutate');
 // Rate limits apply to the same customer even when using another short session.
 $rate=boot_fixture();usleep((int)((1-fmod(microtime(true),1)+0.05)*1000000));
 $q=['message'=>'Burst one','client_message_id'=>bin2hex(random_bytes(16))];$r=rest_chat('guest/start',$q,$rate['cookies'],0,$rate['data']['csrf']);ac($r->get_status()===200,'first message needs no CAPTCHA token');$rid=$r->get_data()['id'];
 $q=['id'=>$rid,'message'=>'Burst two','client_message_id'=>bin2hex(random_bytes(16))];ac(rest_chat('guest/send',$q,$rate['cookies'],0,$rate['data']['csrf'])->get_status()===200,'two messages in a second allowed');
 $q['message']='Burst three';$q['client_message_id']=bin2hex(random_bytes(16));$r=rest_chat('guest/send',$q,$rate['cookies'],0,$rate['data']['csrf']);ac($r->get_status()===429,'third message in same second rejected');ac((int)($r->get_headers()['Retry-After']??0)===1,'burst limit provides one-second retry guidance');
 $count=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages').' WHERE conversation_id=%d',VPN_Chat_Store::conversation($rid)['id']));ac($count===2,'rejected spam creates no message');
 usleep(1100000);ac(rest_chat('guest/send',$q,$rate['cookies'],0,$rate['data']['csrf'])->get_status()===200,'sending resumes after cooldown');
 $rateCookies=$rate['cookies'];unset($rateCookies[VPN_Chat_Security::COOKIE]);$rb=rest_chat('bootstrap',[],$rateCookies)->get_data();$rateCookies=$_COOKIE;ac($rb['customer']['code']===$rate['data']['customer']['code'],'new short session retains same customer identity');
 $_COOKIE=$rateCookies;$same=VPN_Chat_Security::session();$key=hash('sha256','message:short:'.$same['customer_id'].':'.intdiv(time(),30));$wpdb->replace(VPN_Chat_Store::table('rate_limits'),['bucket'=>$key,'hits'=>100,'expires_at'=>gmdate('Y-m-d H:i:s',time()+30)]);$q['client_message_id']=bin2hex(random_bytes(16));ac(rest_chat('guest/send',$q,$rateCookies,0,$rb['csrf'])->get_status()===429,'new session cannot bypass customer short limit');
 VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table('rate_limits'));$key=hash('sha256','message:long:'.$same['customer_id'].':'.intdiv(time(),300));$wpdb->replace(VPN_Chat_Store::table('rate_limits'),['bucket'=>$key,'hits'=>200,'expires_at'=>gmdate('Y-m-d H:i:s',time()+300)]);ac(rest_chat('guest/send',$q,$rateCookies,0,$rb['csrf'])->get_status()===429,'long interval limit enforced');
 VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table('rate_limits'));$key=hash('sha256','message:ip:'.VPN_Chat_Security::ip().':'.intdiv(time(),300));$wpdb->replace(VPN_Chat_Store::table('rate_limits'),['bucket'=>$key,'hits'=>1000,'expires_at'=>gmdate('Y-m-d H:i:s',time()+300)]);ac(rest_chat('guest/send',$q,$rateCookies,0,$rb['csrf'])->get_status()===429,'IP-wide message limit enforced');
 VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table('rate_limits'));$key=hash('sha256','create:c:'.$same['customer_id'].':'.intdiv(time(),600));$wpdb->replace(VPN_Chat_Store::table('rate_limits'),['bucket'=>$key,'hits'=>20,'expires_at'=>gmdate('Y-m-d H:i:s',time()+600)]);$q=['message'=>'Too many chats','client_message_id'=>bin2hex(random_bytes(16))];ac(rest_chat('guest/start',$q,$rateCookies,0,$rb['csrf'])->get_status()===429,'new conversation quota enforced');
 $opts=VPN_Chat_Settings::get();$opts['widget']=true;$opts['accept_new']=false;update_option('vpn_chat_settings',$opts,false);delete_option('vpn_chat_rate_limits_version');VPN_Chat_Settings::upgrade_rate_limits();ac(VPN_Chat_Settings::get()['accept_new'],'upgrade enables approved chat on existing widget');$opts['accept_new']=false;update_option('vpn_chat_settings',$opts,false);VPN_Chat_Settings::upgrade_rate_limits();ac(!VPN_Chat_Settings::get()['accept_new'],'upgrade preserves later admin disable');
 $scope='rate-test-'.bin2hex(random_bytes(8));$barrier=__DIR__.'/runtime/'.$scope;$workers=[];
 try{
  for($i=0;$i<4;$i++){$pipes=[];$proc=proc_open([PHP_BINARY,__DIR__.'/rate-worker.php',$scope,$barrier,(string)$i],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$workers[]=[$proc,$pipes];}
  $until=microtime(true)+10;while(count(glob($barrier.'.ready*'))!==4){if(microtime(true)>$until)throw new RuntimeException('Worker barrier timed out');usleep(2000);}file_put_contents($barrier,'go');$accepted=0;$rejected=0;
  foreach($workers as [$proc,$pipes]){$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($proc)!==0)throw new RuntimeException($err);$result=json_decode($out,true)['result']??'';$accepted+=$result==='accepted';$rejected+=$result==='rate_limited';}
  ac($accepted===2&&$rejected===2,'concurrent requests cannot exceed atomic rate limit');
 }finally{foreach(glob($barrier.'.ready*') as $file)unlink($file);if(file_exists($barrier))unlink($barrier);}
}finally{
 if(isset($original))update_option('vpn_chat_settings',$original,false);
 // This is the dedicated test DB; leave it clean for real browser acceptance.
 foreach(VPN_Chat_Schema::TABLES as $t)VPN_Chat_Store::query('DELETE FROM '.VPN_Chat_Store::table($t));
 $report=['version'=>VPN_CHAT_VERSION,'checks'=>count($passed),'passed'=>$passed,'database'=>'isolated vpn_chat_test on port 3311','cleaned'=>true];
 file_put_contents(__DIR__.'/../../artifacts/vpn-live-chat/evidence/final-backend-report.json',wp_json_encode($report,JSON_PRETTY_PRINT));ob_end_clean();echo wp_json_encode($report,JSON_PRETTY_PRINT);
}
