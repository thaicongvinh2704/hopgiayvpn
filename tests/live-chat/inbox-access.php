<?php
if(PHP_SAPI!=='cli')exit(1);
require dirname(__DIR__,2).'/wp-load.php';
if(!VPN_Chat_Settings::local()||DB_NAME!=='hopgiayvpnmoi')exit(1);
$count=0;function inbox_check($value,$label){global $count;if(!$value)throw new RuntimeException($label);$count++;echo "PASS $label\n";}
$user=get_user_by('login','vpn-chat-demo');wp_set_current_user($user->ID);
$manager=VPN_Chat_REST::sync(['filter'=>'inbox']);
inbox_check(count($manager['list'])>0,'manager inbox returns active conversations');
inbox_check(!array_filter($manager['list'],fn($c)=>in_array($c['status'],['closed','spam'],true)),'default inbox excludes closed and spam');
inbox_check(!array_filter($manager['list'],fn($c)=>str_contains((string)$c['preview'],'Private inbox note')),'list preview excludes internal notes');
$restriction=static function($allcaps,$caps,$args,$current)use($user){if($current->ID===$user->ID)$allcaps['vpn_chat_manage']=false;return $allcaps;};add_filter('user_has_cap',$restriction,20,4);
$agent=VPN_Chat_REST::sync(['filter'=>'inbox']);
inbox_check(!array_filter($agent['list'],fn($c)=>(int)$c['owner_id']!==$user->ID&&!($c['status']==='unassigned'&&!(int)$c['owner_id'])),'agent inbox retains owner scope');
try{VPN_Chat_REST::sync(['filter'=>'all']);inbox_check(false,'agent cannot request all');}catch(VPN_Chat_Fault $e){inbox_check($e->getMessage()==='invalid_filter','agent cannot request all');}
$fixture=array_values(array_filter($manager['list'],fn($c)=>str_starts_with($c['name'],'Inbox QA')))[0];
$selected=VPN_Chat_REST::sync(['filter'=>'inbox','id'=>$fixture['id'],'cursor'=>0]);
inbox_check($selected['selected']['public_id']===$fixture['id'],'accessible selected conversation remains available');
remove_filter('user_has_cap',$restriction,20);wp_set_current_user(0);
$request=new WP_REST_Request('POST','/vpn-chat/v1/agent/sync');$request->set_header('origin','http://localhost');$request->set_header('content-type','application/json');$request->set_body('{}');
$denied=VPN_Chat_REST::permission($request);inbox_check(is_wp_error($denied)&&$denied->get_error_data()['status']===403,'anonymous cannot access agent inbox');echo "TOTAL $count inbox access checks passed\n";
