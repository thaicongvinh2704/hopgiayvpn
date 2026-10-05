<?php
define('WP_INSTALLING',true);
require __DIR__.'/runtime/wp/wp-load.php';
require_once ABSPATH.'wp-admin/includes/upgrade.php';
if(!is_blog_installed())wp_install('VPN Chat Local Tests','chat-test-manager','chat-test@example.invalid',false,'','test-password-local-only');
require_once ABSPATH.'wp-admin/includes/plugin.php';
activate_plugin('vpn-live-chat/vpn-live-chat.php');
if(!class_exists('VPN_Chat_Settings'))require_once WP_PLUGIN_DIR.'/vpn-live-chat/vpn-live-chat.php';
global $wpdb;
$wpdb->query('CREATE TABLE IF NOT EXISTS vct_test_tokens (hash char(64) PRIMARY KEY) ENGINE=InnoDB');
$wpdb->query('CREATE TABLE IF NOT EXISTS vct_test_mails (id bigint unsigned AUTO_INCREMENT PRIMARY KEY,recipient varchar(254)) ENGINE=InnoDB');
foreach(['agent-a'=>'vpn_chat_sales','agent-b'=>'vpn_chat_sales','viewer'=>'subscriber'] as $login=>$role){
    $user=get_user_by('login',$login);
    if(!$user){$id=wp_create_user($login,'test-password-local-only',$login.'@example.invalid');$user=get_user_by('id',$id);}
    $user->set_role($role);
}
$s=VPN_Chat_Settings::get();$s['widget']=true;$s['accept_new']=true;$s['paths']=['/'];$s['site_key']='test-only-site-key';$s['sales_email']='team@example.invalid';$s['fallback_url']='http://127.0.0.1:8091/contact/';
$s['hours']=array_fill(1,7,[['00:00','23:59']]);update_option('vpn_chat_settings',$s,false);
echo wp_json_encode(['installed'=>is_blog_installed(),'schema'=>VPN_Chat_Schema::healthy(),'theme'=>get_stylesheet(),'active_plugins'=>get_option('active_plugins')],JSON_PRETTY_PRINT),"\n";
