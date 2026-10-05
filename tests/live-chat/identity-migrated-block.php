<?php
require dirname(__DIR__,2).'/wp-load.php';
$r=json_decode(file_get_contents(__DIR__.'/../../artifacts/vpn-live-chat/evidence/identity-report.json'),true);
$c=VPN_Chat_Store::conversation($r['created_fixture_ids'][0]);
$body=$wpdb->get_var($wpdb->prepare('SELECT body FROM '.VPN_Chat_Store::table('messages').' WHERE conversation_id=%d AND seq=1',$c['id']));
if(!str_starts_with($body,'Identity QA ')||!VPN_Chat_Settings::local())exit(1);
$id=VPN_Chat_Store::insert('blocks',['session_id'=>$c['session_id'],'reason'=>'Identity QA migrated block','actor_id'=>0,'expires_at'=>gmdate('Y-m-d H:i:s',time()+600),'created_at'=>gmdate('Y-m-d H:i:s')]);
try{VPN_Chat_Identity::check((int)$c['customer_id']);throw new Exception('Migrated block failed');}catch(VPN_Chat_Fault $e){if($e->status!==403)throw $e;echo 'PASS block from original session still applies after verified merge';}finally{$wpdb->delete(VPN_Chat_Store::table('blocks'),['id'=>$id]);}
