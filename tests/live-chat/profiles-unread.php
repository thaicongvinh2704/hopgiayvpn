<?php
require __DIR__.'/runtime/wp/wp-load.php';
if(DB_NAME!=='vpn_chat_test' || $GLOBALS['table_prefix']!=='vct_')exit(1);
$passed=0;
function pcheck(bool $ok,string $name):void {global $passed;if(!$ok)throw new RuntimeException($name);$passed++;echo "PASS $name\n";}
function pc(string $route,array $body=[],?array $session=null,int $user=0,bool $csrf=true):WP_REST_Response {
    $_COOKIE=[];if($session)$_COOKIE[VPN_Chat_Security::COOKIE]=$session['secret'];wp_set_current_user($user);
    $method=$route==='guest/sync'?'GET':'POST';$r=new WP_REST_Request($method,'/vpn-chat/v1/'.$route);
    $r->set_header('origin',home_url());$r->set_header('sec-fetch-site','same-origin');$r->set_header('content-type','application/json');
    if($user)$r->set_header('x-wp-nonce',wp_create_nonce('wp_rest'));
    if($session&&$csrf)$r->set_header('x-vpn-csrf',VPN_Chat_Security::csrf($session['secret']));
    if($method==='GET')$r->set_query_params($body);else $r->set_body(wp_json_encode($body));return rest_do_request($r);
}
function ps():array {$secret=bin2hex(random_bytes(32));$id=VPN_Chat_Store::insert('sessions',['secret_hash'=>hash('sha256',$secret),'created_at'=>gmdate('Y-m-d H:i:s'),'last_seen'=>gmdate('Y-m-d H:i:s'),'expires_at'=>gmdate('Y-m-d H:i:s',time()+3600)]);return compact('secret','id');}
function pm(string $id,string $text,int $agent,bool $note=false):WP_REST_Response{return pc('agent/send',['id'=>$id,'message'=>$text,'client_message_id'=>bin2hex(random_bytes(16)),'note'=>$note,'profile'=>['name'=>'IMPOSTOR'],'actor_id'=>99999],null,$agent);}
$a=(int)get_user_by('login','agent-a')->ID;$b=(int)get_user_by('login','agent-b')->ID;$manager=(int)get_user_by('login','chat-test-manager')->ID;
update_user_meta($a,'vpn_chat_profile',['name'=>'Tho Nguyen','avatar_id'=>0]);update_user_meta($b,'vpn_chat_profile',['name'=>'Linh Ngo','avatar_id'=>0]);
$settings=VPN_Chat_Settings::get();$s=$settings;$s['shared_identity']=false;update_option('vpn_chat_settings',$s,false);
try{
 $guest=ps();$other=ps();$created=pc('guest/start',['name'=>'Profile QA','email'=>'profile-qa@example.invalid','message'=>'Hello 😊','client_message_id'=>bin2hex(random_bytes(16)),'challenge'=>'valid-'.uniqid()],$guest);
 pcheck($created->get_status()===200,'profile fixture uses real protected guest start');$id=$created->get_data()['id'];$c=VPN_Chat_Store::conversation($id);
 pc('agent/update',['id'=>$id,'action'=>'claim','version'=>$c['version']],null,$a);
 pcheck(pm($id,'First reply from Tho',$a)->get_status()===200,'agent reply stored with authenticated actor');
 $c=VPN_Chat_Store::conversation($id);pc('agent/update',['id'=>$id,'owner_id'=>$b,'version'=>$c['version']],null,$manager);pm($id,'Reply after transfer',$b);pm($id,'Private note',$b,true);
 update_user_meta($a,'vpn_chat_profile',['name'=>'Changed later','avatar_id'=>0]);
 $data=pc('guest/sync',['id'=>$id],$guest)->get_data();$agents=array_values(array_filter($data['messages'],fn($m)=>$m['sender']==='agent'));
 pcheck($agents[0]['profile']['name']==='Tho Nguyen','snapshot survives later profile rename');
 pcheck($agents[1]['profile']['name']==='Linh Ngo','second sales retains own identity');
 pcheck(!str_contains(wp_json_encode($data),'IMPOSTOR'),'client cannot spoof sender profile or actor');
 pcheck(!str_contains(wp_json_encode($data),'Private note'),'profile serializer preserves internal-note privacy');
 pcheck($data['conversation']['unread']===2,'unread counts only real agent replies');
 pcheck(pc('guest/sync',['id'=>$id],$guest)->get_data()['conversation']['unread']===2,'polling never resets unread');
 pcheck(pc('guest/read',['id'=>$id,'cursor'=>100],$guest,0,false)->get_status()===403,'read acknowledgement requires CSRF');
 pcheck(pc('guest/read',['id'=>$id,'cursor'=>100],$other)->get_status()===404,'read acknowledgement requires conversation ownership');
 $read=pc('guest/read',['id'=>$id,'cursor'=>(int)$agents[0]['seq']],$guest)->get_data();pcheck($read['unread']===1,'partial read leaves newer reply unread');
 pcheck(pc('guest/read',['id'=>$id,'cursor'=>0],$guest)->get_data()['unread']===1,'read cursor cannot move backwards');
 pcheck(pc('guest/sync',['id'=>$id],$guest)->get_data()['conversation']['unread']===1,'read state survives subsequent sync');
 pcheck(pc('guest/read',['id'=>$id,'cursor'=>1000],$guest)->get_data()['unread']===0,'read cursor clamps to committed transcript');
 $c=VPN_Chat_Store::conversation($id);pc('agent/update',['id'=>$id,'version'=>$c['version'],'read_seq'=>$c['seq']],null,$b);
 pcheck(pc('guest/sync',['id'=>$id],$guest)->get_data()['conversation']['agent_read_seq']===(int)$c['seq'],'guest read receipt uses real sales acknowledgement');
 $s['shared_identity']=true;$s['support_name']='Shared VPN Team';update_option('vpn_chat_settings',$s,false);pm($id,'Shared-policy reply',$b);$agents=array_values(array_filter(pc('guest/sync',['id'=>$id],$guest)->get_data()['messages'],fn($m)=>$m['sender']==='agent'));
 pcheck($agents[2]['profile']['name']==='Shared VPN Team'&&$agents[0]['profile']['name']==='Tho Nguyen','explicit shared policy affects new replies only');
 update_option('vpn_chat_settings',$settings,false);
 pc('agent/sync',['presence'=>'available'],null,$b);pcheck(VPN_Chat_Settings::public_config($b)['presence']==='online','assigned-agent online presence from heartbeat');
 pc('agent/sync',['presence'=>'away'],null,$b);pcheck(VPN_Chat_Settings::public_config($b)['presence']==='away','away distinct from offline');
 pc('agent/sync',['presence'=>'offline'],null,$b);pcheck(VPN_Chat_Settings::public_config($b)['presence']==='offline','offline state is server-authored');
 pcheck(VPN_Chat_Profiles::make('Tho Nguyen',0)['initials']==='TN'&&VPN_Chat_Profiles::avatar(999999999)==='','missing avatar uses genuine initials, no invented portrait');
 pcheck(!user_can($a,'edit_posts')&&!user_can($a,'upload_files'),'profile support does not grant broader WordPress permissions');
}finally{update_option('vpn_chat_settings',$settings,false);update_user_meta($a,'vpn_chat_profile',['name'=>'Tho Nguyen','avatar_id'=>0]);}
echo "TOTAL $passed profile/unread checks passed\n";
