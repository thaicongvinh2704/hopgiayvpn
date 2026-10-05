<?php
// CLI only. Install into the existing local site; never touch production or existing accounts.
if(PHP_SAPI!=='cli')exit(1);
require dirname(__DIR__).'/wp-load.php';
if(wp_get_environment_type()!=='local' || DB_NAME!=='hopgiayvpnmoi' || home_url()!=='http://localhost/hopgiayvpn')throw new RuntimeException('Unexpected environment');
require_once ABSPATH.'wp-admin/includes/plugin.php';
$result=activate_plugin('vpn-live-chat/vpn-live-chat.php');if(is_wp_error($result))throw new RuntimeException($result->get_error_message());
if(!class_exists('VPN_Chat_Settings'))require_once WP_PLUGIN_DIR.'/vpn-live-chat/vpn-live-chat.php';
VPN_Chat_Schema::activate();
$previous=get_option('vpn_chat_settings',null);
add_option('vpn_chat_before_local_demo',$previous,'',false);
$s=VPN_Chat_Settings::get();$base=wp_parse_url(home_url(),PHP_URL_PATH);
$s['widget']=true;$s['accept_new']=true;$s['site_key']='local-demo';$s['paths']=[trailingslashit($base),trailingslashit($base). 'contact/'];
$s['fallback_url']=home_url('/contact/#quote');$s['sales_email']='';$s['hours']=array_fill(1,7,[['00:00','23:59']]);
$s['offline_copy']='Our team is currently away. Leave a message and we’ll follow up during business hours.';
update_option('vpn_chat_settings',$s,false);
$user=get_user_by('login','vpn-chat-demo');
$created=false;
if(!$user){$id=wp_create_user('vpn-chat-demo',bin2hex(random_bytes(20)),'vpn-chat-demo@example.invalid');if(is_wp_error($id))throw new RuntimeException($id->get_error_message());$user=get_user_by('id',$id);$user->set_role('vpn_chat_manager');$created=true;}
if(!user_can($user,'vpn_chat_manage'))throw new RuntimeException('Existing demo username has unexpected role; refusing to modify it');
$path=dirname(__DIR__).'/tests/live-chat/runtime/local-demo-access.json';
if(!is_file($path)){
    if(!$created)throw new RuntimeException('Existing demo account credentials are not managed by this installer; refusing to reset password');
    $password=bin2hex(random_bytes(12));wp_set_password($password,$user->ID);
    file_put_contents($path,wp_json_encode(['login'=>'vpn-chat-demo','password'=>$password,'inbox'=>admin_url('admin.php?page=vpn-live-chat')]));
}
echo wp_json_encode(['active'=>is_plugin_active('vpn-live-chat/vpn-live-chat.php'),'health'=>VPN_Chat_Jobs::health(),'widget'=>home_url('/'),'inbox'=>admin_url('admin.php?page=vpn-live-chat'),'paths'=>$s['paths']],JSON_PRETTY_PRINT),"\n";
