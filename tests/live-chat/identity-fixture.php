<?php
require dirname(__DIR__,2).'/wp-load.php';
$action=$argv[1]??'';$id=$argv[2]??'';
if(!VPN_Chat_Settings::local()||DB_NAME!=='hopgiayvpnmoi'||!preg_match('/^[a-f0-9-]{36}$/D',$id))exit(1);
if($action==='expire-code'){$wpdb->update(VPN_Chat_Store::table('email_codes'),['expires_at'=>'2000-01-01 00:00:00'],['public_id'=>$id]);exit;}
$c=VPN_Chat_Store::conversation($id);$body=$wpdb->get_var($wpdb->prepare('SELECT body FROM '.VPN_Chat_Store::table('messages').' WHERE conversation_id=%d AND seq=1',$c['id']));
if(!str_starts_with((string)$body,'Identity QA '))exit(2);
if($action==='close')$wpdb->update(VPN_Chat_Store::table('conversations'),['status'=>'closed'],['id'=>$c['id']]);

if($action==='erase')VPN_Chat_Jobs::erase((int)$c['id']);

if($action==='block')VPN_Chat_Store::insert('blocks',['session_id'=>$c['session_id'],'reason'=>'Identity QA block','actor_id'=>0,'expires_at'=>gmdate('Y-m-d H:i:s',time()+3600),'created_at'=>gmdate('Y-m-d H:i:s')]);
if($action==='unblock')$wpdb->delete(VPN_Chat_Store::table('blocks'),['session_id'=>$c['session_id'],'reason'=>'Identity QA block']);
