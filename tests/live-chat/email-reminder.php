<?php
require dirname(__DIR__,2).'/wp-load.php';
if(!VPN_Chat_Settings::local())exit(1);
$passed=[];
VPN_Chat_Store::query('START TRANSACTION');
try{
foreach(['available'=>true,'away'=>false,'offline'=>false] as $state=>$expected){
$wpdb->replace(VPN_Chat_Store::table('agent_presence'),['user_id'=>999999990,'state'=>$state,'heartbeat_at'=>gmdate('Y-m-d H:i:s')]);
$c=VPN_Chat_Settings::public_config(999999990);
if($c['agent_available']!==$expected||$c['presence']!=='online')throw new Exception('Incorrect effective availability');$passed[]=$state;
}
$wpdb->update(VPN_Chat_Store::table('agent_presence'),['state'=>'available','heartbeat_at'=>'2000-01-01 00:00:00'],['user_id'=>999999990]);
if(VPN_Chat_Settings::public_config(999999990)['agent_available'])throw new Exception('Expired heartbeat accepted');$passed[]='expired heartbeat';
echo wp_json_encode(['checks'=>count($passed),'passed'=>$passed]);
}finally{VPN_Chat_Store::query('ROLLBACK');}
