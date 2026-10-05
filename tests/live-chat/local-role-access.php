<?php
// Read-only checks against local installation; no customer messages or user role changes.
if(PHP_SAPI!=='cli')exit(1);
require dirname(__DIR__,2).'/wp-load.php';
if(wp_get_environment_type()!=='local' || DB_NAME!=='hopgiayvpnmoi')exit(1);
function verify_access(bool $ok,string $name):void {if(!$ok)throw new RuntimeException($name);echo "PASS $name\n";}
$user=get_user_by('login','vpn-chat-demo');
wp_set_current_user($user->ID);$pagenow='admin.php';$_GET['page']='vpn-live-chat';
verify_access(apply_filters('woocommerce_prevent_admin_access',true)===false,'chat role can open inbox');
verify_access(!current_user_can('edit_posts') && !current_user_can('manage_options') && !current_user_can('manage_woocommerce'),'chat role gains no editorial/store/admin permissions');
$_GET['page']='vpn-chat-settings';verify_access(apply_filters('woocommerce_prevent_admin_access',true)===false,'chat manager can open chat settings');
$_GET['page']='other-plugin';verify_access(apply_filters('woocommerce_prevent_admin_access',true)===true,'WooCommerce restriction retained on unrelated admin page');
$pagenow='index.php';verify_access(apply_filters('woocommerce_prevent_admin_access',true)===true,'WooCommerce restriction retained on admin dashboard');
verify_access(apply_filters('login_redirect',admin_url(),'',$user)===admin_url('admin.php?page=vpn-live-chat'),'chat-only login defaults to inbox');
verify_access(apply_filters('login_redirect',home_url('/my-account/'),home_url('/my-account/'),$user)===home_url('/my-account/'),'explicit login destination preserved');
wp_set_current_user(0);$pagenow='admin.php';$_GET['page']='vpn-live-chat';verify_access(apply_filters('woocommerce_prevent_admin_access',true)===true,'non-agent receives no exception');
echo "TOTAL 8 local role access checks passed\n";
