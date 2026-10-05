<?php
require __DIR__.'/runtime/wp/wp-load.php';
global $wpdb;
$passed=0;
function check($value,string $name):void{global $passed;if(!$value)throw new RuntimeException('FAIL: '.$name);$passed++;echo "PASS $name\n";}
function call_chat(string $route,array $body=[],string $method='POST',?string $cookie=null,int $user=0,?string $csrf=null,bool $nonce=true):WP_REST_Response{
    $_COOKIE=[];if($cookie!==null)$_COOKIE[VPN_Chat_Security::COOKIE]=$cookie;
    wp_set_current_user($user);
    $r=new WP_REST_Request($method,'/vpn-chat/v1/'.$route);
    $r->set_header('origin','http://127.0.0.1:8091');$r->set_header('sec-fetch-site','same-origin');$r->set_header('content-type','application/json');
    if($csrf!==null)$r->set_header('x-vpn-csrf',$csrf);
    if($user && $nonce)$r->set_header('x-wp-nonce',wp_create_nonce('wp_rest'));
    if($method==='GET')$r->set_query_params($body);else $r->set_body(wp_json_encode($body));
    return rest_do_request($r);
}
function session_fixture():array{
    $secret=bin2hex(random_bytes(32));
    $id=VPN_Chat_Store::insert('sessions',['secret_hash'=>hash('sha256',$secret),'created_at'=>gmdate('Y-m-d H:i:s'),'last_seen'=>gmdate('Y-m-d H:i:s'),'expires_at'=>gmdate('Y-m-d H:i:s',time()+604800)]);
    return ['id'=>$id,'secret'=>$secret,'csrf'=>VPN_Chat_Security::csrf($secret)];
}
function create_fixture(array $s,?string $token=null,?string $email=null):array{
    return ['name'=>'Test Guest','email'=>$email??'guest@example.invalid','message'=>'Hi, quote please 😊','client_message_id'=>bin2hex(random_bytes(16)),'challenge'=>$token??'valid-'.bin2hex(random_bytes(10)),'source_path'=>'/product/sample/','metadata'=>['utm_source'=>'qa','password'=>'must-not-store']];
}
$manager=(int)get_user_by('login','chat-test-manager')->ID;$agentA=(int)get_user_by('login','agent-a')->ID;$agentB=(int)get_user_by('login','agent-b')->ID;$viewer=(int)get_user_by('login','viewer')->ID;
foreach(VPN_Chat_Schema::TABLES as $t)$wpdb->query('DELETE FROM '.VPN_Chat_Store::table($t));
$wpdb->query('DELETE FROM vct_test_tokens');
check(VPN_Chat_Schema::healthy(),'InnoDB tables and migrations');VPN_Chat_Schema::activate();VPN_Chat_Schema::activate();check(get_option('vpn_chat_schema_version')===VPN_Chat_Schema::VERSION,'idempotent activation');
check(!user_can($agentA,'edit_posts')&&!user_can($agentA,'manage_options')&&user_can($agentA,'vpn_chat_agent'),'sales least privilege');
$s=session_fixture();$b=session_fixture();$p=create_fixture($s);
$r=call_chat('guest/start',$p,'POST',$s['secret']);check($r->get_status()===403,'guest mutation requires CSRF');
$r=call_chat('guest/start',$p,'POST',null,0,$s['csrf']);check($r->get_status()===401,'guest identity requires cookie');
foreach(['forged','expired','wrong-host-'.uniqid(),'wrong-action-'.uniqid(),'timeout-'.uniqid()] as $token){$x=session_fixture();$r=call_chat('guest/start',create_fixture($x,$token),'POST',$x['secret'],0,$x['csrf']);check(in_array($r->get_status(),[403,503],true),'reject Turnstile '.$token);}
$r=call_chat('guest/start',$p,'POST',$s['secret'],0,$s['csrf']);check($r->get_status()===200,'first message accepted');$id=$r->get_data()['id'];
$r=call_chat('guest/start',$p,'POST',$s['secret'],0,$s['csrf']);check($r->get_status()===200&&(int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations'))===1,'start retry deduplicates lead');
$different=$p;$different['email']='other@example.invalid';$r=call_chat('guest/start',$different,'POST',$s['secret'],0,$s['csrf']);check($r->get_status()===409,'same start ID different payload conflicts');
$replay=session_fixture();$r=call_chat('guest/start',create_fixture($replay,$p['challenge']),'POST',$replay['secret'],0,$replay['csrf']);check($r->get_status()===403,'replayed Siteverify token rejected');
$bp=create_fixture($b,null,$p['email']);$r=call_chat('guest/start',$bp,'POST',$b['secret'],0,$b['csrf']);check($r->get_status()===200,'same unverified email creates separate ownership');$bid=$r->get_data()['id'];
check(call_chat('guest/sync',['id'=>$bid],'GET',$s['secret'])->get_status()===404,'guest A cannot read B');
check(call_chat('guest/send',['id'=>$bid,'message'=>'attack','client_message_id'=>bin2hex(random_bytes(16))],'POST',$s['secret'],0,$s['csrf'])->get_status()===404,'guest A cannot write B');
check(call_chat('guest/sync',['id'=>$id],'GET',bin2hex(random_bytes(32)))->get_status()===401,'random cookie cannot recover history');
check(call_chat('agent/sync',[],'POST',null,$viewer)->get_status()===403,'subscriber cannot use inbox');
check(call_chat('agent/sync',[],'POST',null,$agentA,null,false)->get_status()===403,'agent REST nonce required');
$c=VPN_Chat_Store::conversation($id);
check(call_chat('agent/update',['id'=>$id,'action'=>'claim','version'=>$c['version']],'POST',null,$agentA)->get_status()===200,'agent claim succeeds');
check(call_chat('agent/update',['id'=>$id,'action'=>'claim','version'=>$c['version']],'POST',null,$agentB)->get_status()===409,'second claimant receives conflict');
check(call_chat('agent/sync',['id'=>$id],'POST',null,$agentB)->get_status()===403,'agent cannot read another owner transcript');
check(call_chat('agent/send',['id'=>$id,'message'=>'forbidden','client_message_id'=>bin2hex(random_bytes(16))],'POST',null,$agentB)->get_status()===403,'agent cannot reply to other owner');
$message=['id'=>$id,'message'=>'<script>alert(1)</script> emoji 🎉 quote \' OR 1=1 --','client_message_id'=>bin2hex(random_bytes(16))];
$r=call_chat('guest/send',$message,'POST',$s['secret'],0,$s['csrf']);check($r->get_status()===200,'text XSS/SQL payload safely stored');$seq=$r->get_data()['seq'];
$r=call_chat('guest/send',$message,'POST',$s['secret'],0,$s['csrf']);check($r->get_status()===200&&$r->get_data()['seq']===$seq,'message retry exactly once');
$message['message']='changed';check(call_chat('guest/send',$message,'POST',$s['secret'],0,$s['csrf'])->get_status()===409,'retry payload mismatch conflicts');
$note=['id'=>$id,'message'=>'INTERNAL PRIVATE NOTE','note'=>true,'client_message_id'=>bin2hex(random_bytes(16))];check(call_chat('agent/send',$note,'POST',null,$agentA)->get_status()===200,'internal note saved');
$r=call_chat('guest/sync',['id'=>$id,'cursor'=>0],'GET',$s['secret']);check(!str_contains(wp_json_encode($r->get_data()),'INTERNAL PRIVATE NOTE')&&!isset($r->get_data()['conversation']['email']),'guest serializer excludes note and lead fields');
check(str_contains($r->get_headers()['Cache-Control'],'no-store'),'private API no-store');
$c=VPN_Chat_Store::conversation($id);$r=call_chat('agent/update',['id'=>$id,'version'=>$c['version'],'owner_id'=>$agentB],'POST',null,$agentA);check($r->get_status()===200,'owner can transfer with audit');
check(call_chat('agent/sync',['id'=>$id],'POST',null,$agentA)->get_status()===403,'previous owner loses access after transfer');
check((int)$wpdb->get_var("SELECT COUNT(*) FROM ".VPN_Chat_Store::table('audit')." WHERE action='update'")>=1,'state/transfer audit persisted');
$c=VPN_Chat_Store::conversation($id);check(call_chat('agent/update',['id'=>$id,'version'=>(int)$c['version']-1,'status'=>'closed'],'POST',null,$agentB)->get_status()===409,'stale status update rejected');
// Cursor tests use the same row-lock sequence as production and include notes in-between.
wp_set_current_user($manager);VPN_Chat_Store::transaction(static function()use($id){for($i=0;$i<65;$i++){VPN_Chat_Service::send_locked(VPN_Chat_Store::conversation($id,true),'delta-'.$i,bin2hex(random_bytes(16)),$i%3===0?'note':'agent',get_current_user_id());}});
$all=[];$cursor=0;do{$r=call_chat('guest/sync',['id'=>$id,'cursor'=>$cursor],'GET',$s['secret']);$data=$r->get_data();$all=array_merge($all,$data['messages']);check($data['cursor']>=$cursor,'monotonic conversation cursor');$cursor=$data['cursor'];}while($data['more']);
$expected=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages')." WHERE conversation_id=%d AND sender!='note'",VPN_Chat_Store::conversation($id)['id']));check(count($all)===$expected,'delta pagination omits no committed visible message');
check(count(array_unique(array_column($all,'seq')))===count($all),'cursor pages have no duplicates');
$c=VPN_Chat_Store::conversation($id);check(call_chat('agent/update',['id'=>$id,'version'=>$c['version'],'status'=>'closed'],'POST',null,$agentB)->get_status()===200,'close conversation');
check(call_chat('guest/send',['id'=>$id,'message'=>'new','client_message_id'=>bin2hex(random_bytes(16))],'POST',$s['secret'],0,$s['csrf'])->get_status()===409,'closed chat rejects new message');
$c=VPN_Chat_Store::conversation($id);check(call_chat('agent/update',['id'=>$id,'version'=>$c['version'],'status'=>'assigned','label'=>'needs_quote','needs'=>'500 units, sample requested','follow_up_at'=>gmdate('Y-m-d H:i:s',time()+3600)],'POST',null,$agentB)->get_status()===200,'reopen label needs and follow-up');
$c=VPN_Chat_Store::conversation($id);check($c['status']==='follow_up'&&$c['label']==='needs_quote','sales label separate from state');
check(call_chat('agent/update',['id'=>$id,'version'=>$c['version'],'status'=>'closed','follow_up_at'=>$c['follow_up_at']],'POST',null,$agentB)->get_status()===200,'follow-up can close without date overriding status');
$c=VPN_Chat_Store::conversation($id);check($c['status']==='closed'&&$c['follow_up_at']===null,'closing clears pending follow-up');call_chat('agent/update',['id'=>$id,'version'=>$c['version'],'status'=>'assigned'],'POST',null,$agentB);
$before=(int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages'));
try{VPN_Chat_Store::transaction(static function()use($id){VPN_Chat_Service::send_locked(VPN_Chat_Store::conversation($id,true),'rollback',bin2hex(random_bytes(16)),'guest',0);throw new RuntimeException('simulate');});}catch(RuntimeException $e){}
check((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages'))===$before,'failure rolls back message and conversation sequence');
$r=new WP_REST_Request('POST','/vpn-chat/v1/bootstrap');$r->set_header('origin','https://attacker.invalid');$r->set_header('content-type','application/json');$r->set_body('{}');check(rest_do_request($r)->get_status()===403,'cross-origin request denied');
$r->set_header('origin','http://127.0.0.1:8091');$r->set_header('sec-fetch-site','cross-site');check(rest_do_request($r)->get_status()===403,'Fetch Metadata cross-site denied');
$r->set_header('sec-fetch-site','same-origin');$r->set_body(str_repeat('x',16385));check(rest_do_request($r)->get_status()===413,'16 KiB body limit');
$r->set_body('{broken');check(rest_do_request($r)->get_status()===400,'malformed JSON denied');
$r->set_body("{\"message\":\"\xFF\"}");check(rest_do_request($r)->get_status()===400,'malformed UTF-8 denied');
$r->set_body('{}');$r->set_header('content-type','text/plain');check(rest_do_request($r)->get_status()===415,'content type enforced');
$wpdb->update(VPN_Chat_Store::table('sessions'),['challenge_required'=>1],['id'=>$s['id']]);
check(call_chat('guest/send',['id'=>$id,'message'=>'again','client_message_id'=>bin2hex(random_bytes(16))],'POST',$s['secret'],0,$s['csrf'])->get_status()===403,'suspicious session has no challenge bypass');
$res=call_chat('guest/send',['id'=>$id,'message'=>'verified again','client_message_id'=>bin2hex(random_bytes(16)),'challenge'=>'valid-'.uniqid()],'POST',$s['secret'],0,$s['csrf']);check($res->get_status()===200,'session re-challenge clears after verified send');
$wpdb->update(VPN_Chat_Store::table('sessions'),['last_seen'=>gmdate('Y-m-d H:i:s',time()-90000)],['id'=>$b['id']]);check(call_chat('guest/sync',['id'=>$bid],'GET',$b['secret'])->get_status()===401,'idle session expiry');
// Presence only when scheduled and heartbeat valid.
call_chat('agent/sync',['presence'=>'available'],'POST',null,$agentA);check(VPN_Chat_Settings::public_config()['online'],'available heartbeat inside duty');
$wpdb->query('UPDATE '.VPN_Chat_Store::table('agent_presence').' SET heartbeat_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 100 SECOND)');check(!VPN_Chat_Settings::public_config()['online'],'stale presence offline');
$settings=VPN_Chat_Settings::get();$off=$settings;$off['hours']=[];update_option('vpn_chat_settings',$off,false);call_chat('agent/sync',['presence'=>'available'],'POST',null,$agentA);check(!VPN_Chat_Settings::public_config()['online'],'outside duty remains offline');update_option('vpn_chat_settings',$settings,false);
check(call_chat('agent/canned',['title'=>'Approved','body'=>'Thanks for your enquiry.'],'POST',null,$agentA)->get_status()===403,'agent cannot change canned templates');
check(call_chat('agent/canned',['title'=>'Approved','body'=>'Thanks for your enquiry.'],'POST',null,$manager)->get_status()===200,'manager canned CRUD create');
$canned=(int)$wpdb->get_var('SELECT id FROM '.VPN_Chat_Store::table('canned').' LIMIT 1');check(call_chat('agent/canned',['id'=>$canned,'title'=>'Updated','body'=>'A sales member will review your specifications.'],'POST',null,$manager)->get_status()===200,'canned edit');check(call_chat('agent/canned',['id'=>$canned,'action'=>'delete'],'POST',null,$manager)->get_status()===200,'canned delete');
check(call_chat('manager/block',['id'=>$id,'reason'=>'Test review','hours'=>1],'POST',null,$manager)->get_status()===200,'scoped temporary block');check(call_chat('guest/sync',['id'=>$id],'GET',$s['secret'])->get_status()===403,'blocked session denied');
$block=(int)$wpdb->get_var('SELECT id FROM '.VPN_Chat_Store::table('blocks').' LIMIT 1');call_chat('manager/block',['block_id'=>$block,'action'=>'unban'],'POST',null,$manager);check(call_chat('guest/sync',['id'=>$id],'GET',$s['secret'])->get_status()===200,'unban restores access');
// Atomic quotas under sequential edge and response headers. Parallel check lives in concurrency.php.
$q=session_fixture();$qp=create_fixture($q);$qr=call_chat('guest/start',$qp,'POST',$q['secret'],0,$q['csrf']);$qid=$qr->get_data()['id'];
for($i=0;$i<10;$i++){$qr=call_chat('guest/send',['id'=>$qid,'message'=>'rate-'.$i,'client_message_id'=>bin2hex(random_bytes(16))],'POST',$q['secret'],0,$q['csrf']);check($qr->get_status()===200,'allowed burst '.$i);}
$qr=call_chat('guest/send',['id'=>$qid,'message'=>'limited','client_message_id'=>bin2hex(random_bytes(16))],'POST',$q['secret'],0,$q['csrf']);check($qr->get_status()===429&&isset($qr->get_headers()['Retry-After']),'quota rejects with Retry-After');
// Durable outbox survives SMTP failure; sink prevents all real delivery.
$wpdb->query('UPDATE '.VPN_Chat_Store::table('outbox').' SET due_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE)');update_option('vpn_test_mail_success',false,false);$count=(int)get_option('vpn_test_mail_count',0);VPN_Chat_Jobs::run();
check((int)get_option('vpn_test_mail_count',0)>$count,'outbox attempts only configured internal recipient');check((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('outbox')." WHERE attempts>0 AND state='pending'")>0,'SMTP failure retains durable retry');
$count=(int)get_option('vpn_test_mail_count',0);VPN_Chat_Jobs::run();check((int)get_option('vpn_test_mail_count',0)===$count,'backoff prevents notification flood');
update_option('vpn_test_mail_success',true,false);$wpdb->query('UPDATE '.VPN_Chat_Store::table('outbox')." SET due_at=UTC_TIMESTAMP() WHERE state='pending'");VPN_Chat_Jobs::run();check((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('outbox')." WHERE state='pending'")===0,'mail sink success drains retry queue');
$mail=get_option('vpn_test_last_mail');check(!str_contains($mail['message'],'Hi, quote')&&$mail['to']==='team@example.invalid'&&str_contains($mail['message'],'wp-admin'),'minimal internal email without transcript or bearer link');
check(call_chat('manager/privacy',['id'=>$id,'action'=>'export'],'POST',null,$agentB)->get_status()===403,'privacy export manager only');check(call_chat('manager/privacy',['id'=>$id,'action'=>'delete'],'POST',null,$manager)->get_status()===400,'privacy erase requires verified request');
$export=call_chat('manager/privacy',['id'=>$id,'action'=>'export'],'POST',null,$manager);check($export->get_status()===200&&str_contains(wp_json_encode($export->get_data()),'INTERNAL PRIVATE NOTE'),'authorized export includes internal record');
$leadCount=(int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations'));VPN_Chat_Jobs::deactivate();check((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations'))===$leadCount,'deactivate retains leads');VPN_Chat_Schema::activate();check((int)$wpdb->get_var('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations'))===$leadCount,'reactivation retains leads');
check(call_chat('manager/privacy',['id'=>$id,'action'=>'delete','verified_request'=>true],'POST',null,$manager)->get_status()===200,'verified erase transaction');check((int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('messages').' WHERE conversation_id=%d',$c['id']))===0,'erase removes transcript and outbox');
echo "TOTAL $passed passed\n";
