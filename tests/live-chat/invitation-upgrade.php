<?php
require dirname(__DIR__,2).'/wp-load.php';
if(!VPN_Chat_Settings::local()||DB_NAME!=='hopgiayvpnmoi')exit(1);
$passed=[];VPN_Chat_Store::query('START TRANSACTION');
try{
$s=VPN_Chat_Settings::get();$s['greeting_enabled']=false;$s['support_avatar_id']=0;update_option('vpn_chat_settings',$s,false);delete_option('vpn_chat_invitation_version');
VPN_Chat_Settings::upgrade_invitation();if(!VPN_Chat_Settings::get()['greeting_enabled'])throw new Exception('migration did not enable invitation');$passed[]='upgrade enables approved invitation on existing settings';
$s=VPN_Chat_Settings::get();$s['greeting_enabled']=false;update_option('vpn_chat_settings',$s,false);VPN_Chat_Settings::upgrade_invitation();if(VPN_Chat_Settings::get()['greeting_enabled'])throw new Exception('later admin preference overwritten');$passed[]='upgrade runs once and respects later admin disable';
$profile=VPN_Chat_Profiles::support();if(!str_contains($profile['avatar'],'vpn-live-chat/assets/tho-nguyen.png'))throw new Exception('bundled avatar missing');$passed[]='avatar fallback independent of local Media Library ID';
echo wp_json_encode(['version'=>VPN_CHAT_VERSION,'checks'=>count($passed),'passed'=>$passed]);
}finally{VPN_Chat_Store::query('ROLLBACK');}
