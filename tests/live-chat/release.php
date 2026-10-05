<?php
define('WP_INSTALLING',true);
require __DIR__.'/runtime/release-wp/wp-load.php';
require_once ABSPATH.'wp-admin/includes/upgrade.php';
if(!is_blog_installed())wp_install('Release ZIP Tests','release-admin','release@example.invalid',false,'','test-password-local-only');
require_once ABSPATH.'wp-admin/includes/plugin.php';
function rcheck($value,string $name):void{if(!$value)throw new RuntimeException('FAIL '.$name);echo 'PASS '.$name."\n";}
$plugin='vpn-live-chat/vpn-live-chat.php';
rcheck(activate_plugin($plugin)===null,'ZIP plugin activates through WordPress API');
$s=VPN_Chat_Settings::get();rcheck(!$s['widget']&&!$s['accept_new']&&!$s['paths']&&!$s['hours'],'fresh ZIP install defaults disabled');
rcheck(VPN_Chat_Schema::healthy(),'fresh prefix release schema InnoDB');
$sid=VPN_Chat_Store::insert('sessions',['secret_hash'=>hash('sha256',random_bytes(32)),'created_at'=>gmdate('Y-m-d H:i:s'),'last_seen'=>gmdate('Y-m-d H:i:s'),'expires_at'=>gmdate('Y-m-d H:i:s',time()+3600)]);
$id=VPN_Chat_Store::insert('conversations',['public_id'=>wp_generate_uuid4(),'session_id'=>$sid,'name'=>'Release Lead','email'=>'release-lead@example.invalid','metadata'=>'{}','needs'=>'','created_at'=>gmdate('Y-m-d H:i:s'),'updated_at'=>gmdate('Y-m-d H:i:s')]);
global $wpdb;
deactivate_plugins($plugin);rcheck(!is_plugin_active($plugin)&&!wp_next_scheduled('vpn_chat_worker'),'WordPress deactivate stops plugin job');
rcheck((int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations').' WHERE id=%d',$id))===1,'WordPress deactivate preserves lead');
activate_plugin($plugin);rcheck(is_plugin_active($plugin)&&(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations').' WHERE id=%d',$id))===1,'WordPress reactivate preserves data');
deactivate_plugins($plugin);uninstall_plugin($plugin);rcheck((int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.VPN_Chat_Store::table('conversations').' WHERE id=%d',$id))===1,'WordPress uninstall hook preserves data by default');
echo "TOTAL 7 release lifecycle checks passed\n";
